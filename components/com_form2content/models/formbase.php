<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'class.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'shared.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'utils.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'storage.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'validations.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'helpers'.DS.'form2content.php');

jimport('joomla.application.component.modeladmin');
jimport('joomla.form.form');
jimport('joomla.user.helper');

class Form2ContentModelFormBase extends JModelAdmin
{
	protected $text_prefix 					= 'COM_FORM2CONTENT';
	protected $event_before_save 			= 'onBeforeF2cContentSave';
	protected $event_after_save 			= 'onAfterF2cContentSave';
	protected $event_before_delete 			= 'onBeforeF2cContentDelete';
	protected $event_after_delete 			= 'onAfterF2cContentDelete';
	protected $event_before_parse 			= 'onBeforeF2cContentParse';
	protected $event_after_parse 			= 'onAfterF2cContentParse';	
	protected $f2cConfig 					= null;
	protected $parsedIntroContent 			= null;
	protected $parsedMainContent 			= null;
	public 	  $contentTypeId				= 0;
	protected $dicContentTypeTitle			= array();
	protected $dicContentTypeId				= array();
	protected $dicCatAliasPath				= array();
	protected $dicCatId						= array();
	protected $dicViewingAccessLevelTitle	= array();
	protected $dicViewingAccessLevelId		= array();

		function __construct($config = array())
	{
		parent::__construct($config);
	
		// Load the component parameters
		$this->f2cConfig =& F2cFactory::getConfig();
		
		// try to load the contentType
		$this->contentTypeId = JRequest::getInt('projectid', JRequest::getInt('contenttypeid'));
		
		if($this->contentTypeId == 0)
		{
			if(array_key_exists('jform', $_POST))
			{
				$this->contentTypeId = (int)$_POST['jform']['projectid'];
			}
		}
	}

	public function getTable($type = 'Form', $prefix = 'Form2ContentTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) 
		{
			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->attribs);
			$item->attribs = $registry->toArray();

			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();
			
			// Add the category title and alias
			$query = $this->_db->getQuery(true);
			
			$query->select('title, alias');
			$query->from('`#__categories`');		
			$query->where('id = '.(int)$item->catid);
			
			$this->_db->setQuery($query->__toString());

			if($category = $this->_db->loadObject())
			{
				$item->catTitle = $category->title;
				$item->catAlias = $category->alias;
			}
			else
			{
				$item->catTitle = '';
				$item->catAlias = '';
			}
		}

		if(!$item->id)
		{	
			// new Form: initialize some values	
			$date					= JFactory::getDate();
			$timestamp				= $date->toMySQL();
			$contentTypeId 			= $this->contentTypeId;
			$user 					=& JFactory::getUser();
			$modelContentType 		= new Form2ContentModelProject();
			$contentType			= $modelContentType->getItem($contentTypeId);
			$contentTypeSettings 	= new JRegistry();
		
			$contentTypeSettings->loadArray($contentType->settings);
			
			$item->title 			= $contentTypeSettings->get('title_default') ? $contentTypeSettings->get('title_default') : '';
			$item->projectid 		= $contentTypeId; 
			$item->created_by		= $user->id;
			$item->created			= $timestamp;				
			$item->metakey 			= $contentType->metakey;
			$item->metadesc			= $contentType->metadesc;
			$item->catid			= $contentTypeSettings->get('catid') ? $contentTypeSettings->get('catid') : 0;
			$item->intro_template	= $contentTypeSettings->get('intro_template') ? $contentTypeSettings->get('intro_template') : '';
			$item->main_template	= $contentTypeSettings->get('main_template') ? $contentTypeSettings->get('main_template') : '';
			$item->publish_up		= $timestamp;
			$item->publish_down		= JFactory::getDbo()->getNullDate();
			$item->state			= $contentTypeSettings->get('state_default') ? $contentTypeSettings->get('state_default') : 0;
			$item->language			= $contentTypeSettings->get('language_default') ? $contentTypeSettings->get('language_default') : '*';
			$item->featured			= $contentTypeSettings->get('featured_default') ? $contentTypeSettings->get('featured_default') : 0;
			$item->access			= $contentTypeSettings->get('access_default') ? $contentTypeSettings->get('access_default') : 0;
			$item->attribs			= $contentType->attribs;
			$item->metadata			= $contentType->metadata;	
		}		

		return $item;
	}

	public function getForm($data = array(), $loadData = true)
	{
		$app =& JFactory::getApplication();
		
		// Get the form.
		$form = $this->loadForm('com_form2content.form', 'form', array('control' => 'jform', 'load_data' => $loadData));
						
		if (empty($form)) 
		{
			return false;
		}

		$modelContentType 		= new Form2ContentModelProject();
		
		if((int)$form->getValue('projectid'))
		{
			$contentTypeId = (int)$form->getValue('projectid');
		}
		else
		{
			$contentTypeId = $data['projectid'];			
		}
		
		$contentType			= $modelContentType->getItem($contentTypeId);
		$contentTypeSettings 	= new JRegistry();
		
		$contentTypeSettings->loadArray($contentType->settings);
		
		// set the css attributes for the title field
		if($contentTypeSettings->get('title_attributes'))
		{
			$form->setFieldAttribute('title', 'attributes', $contentTypeSettings->get('title_attributes'));
		}
		
		// Check if a default category is selected
		$defaultCategoryId = (int)$contentTypeSettings->get('catid');

		if($defaultCategoryId != -1)
		{
			if((int)$contentTypeSettings->get('cat_behaviour') == 0)
			{
				// The category is fixed
				$form->setFieldAttribute('catid', 'readonly', 'true');
				$form->setFieldAttribute('catid', 'rootCategoryId', $defaultCategoryId);
				$form->setFieldAttribute('catid', 'value', $defaultCategoryId);
				$form->setFieldAttribute('catid', 'behaviour', 2);
			}
			else
			{
				// The category is the root category
				$form->setFieldAttribute('catid', 'rootCategoryId', $defaultCategoryId);
				$form->setFieldAttribute('catid', 'behaviour', 1);
			}
		}
		
		// Determine correct permissions to check.
		if ($id = (int)$this->getState('form.id')) 
		{
			// Existing record. Can only edit in selected categories.
			//$form->setFieldAttribute('catid', 'action', 'core.edit');
			// Existing record. Can only edit own articles in selected categories.
			//$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else 
		{
			// New record. Can only create in selected categories.
			//$form->setFieldAttribute('catid', 'action', 'core.create');
		}
		
		$dataObject 			= new JObject();
		$dataObject->id 		= $form->getValue('id');
		$dataObject->projectid 	= $form->getValue('projectid');
		$dataObject->created_by = $form->getValue('created_by');
		
		// Modify the form based on Edit State access controls.
		if (!$this->canEditState($dataObject))
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}
		
		// Set the correct formats of the date fields
		$dateFormat = $this->f2cConfig->get('date_format');
		$form->setFieldAttribute('created', 'format', $dateFormat);
		$form->setFieldAttribute('modified', 'format', $dateFormat);
		$form->setFieldAttribute('publish_up', 'format', $dateFormat);
		$form->setFieldAttribute('publish_down', 'format', $dateFormat);
		
		return $form;
	}

	public function save($data, $saveFormOnly = false, $preparedFieldData = null)
	{
		$dispatcher 	=& JDispatcher::getInstance();
		$config 		=& JFactory::getConfig();
		$fields 		= $this->loadFieldDefinitions($data['projectid']);
		$isNew			= empty($data['id']);
		$table			= $this->getTable();
		$key			= $table->getKeyName();
		$pk				= (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName().'.id');
		
		// Get the current date and time
		$dateNow = JFactory::getDate('now', 'UTC');
		$dateNow->setTimezone(new DateTimeZone($config->get('offset')));
		$dateNow = $dateNow->toMySQL();								

		if ($pk > 0) 
		{
			$table->load($pk);
		}
		
		$categoryInfo = $this->getCategoryInfo($data['catid']);
		
		if(empty($data['created']))
		{
			// no created date was provided => set to current date and time
			$data['created'] = $dateNow;
		}
		
		if(empty($data['publish_up']))
		{
			// no publish up date was provided => set to current date and time
			$data['publish_up'] = $dateNow;
		}
		
		if(empty($data['publish_down']))
		{
			// no publish down date was provided => set to null date
			$data['publish_down'] = $this->_db->getNullDate();
		}
		
		$data['modified'] = $dateNow;
		$data['reference_id'] = $table->reference_id;
		$data['catAlias'] = $categoryInfo->alias;
		$data['catTitle'] = $categoryInfo->title;
		
		if(!$saveFormOnly && !$this->validateFields($data, $fields))
		{
			return false;
		}
				
		JPluginHelper::importPlugin('form2content');
		
		// create new Storage object
		$storage = new F2cStorage($data['id'], JFactory::getApplication()->isAdmin());
		
		if($preparedFieldData)
		{
			$storage->preparedData = $preparedFieldData;
		}
		else 
		{
			$storage->prepareSubmittedData($fields);
		}
		
		$newFormData = $this->convertArrayToObject($data);		
		
		$eventDataBeforeSave			= new F2cEventArgs();
		$eventDataBeforeSave->action	= 'save';
		$eventDataBeforeSave->isNew		= $isNew;		
		$eventDataBeforeSave->formNew 	=& $newFormData;
		$eventDataBeforeSave->fieldsNew	=& $storage->preparedData;
		
		if($eventDataBeforeSave->isNew)
		{
			$eventDataBeforeSave->formOld	= null;
			$eventDataBeforeSave->fieldsOld	= null;
		}
		else
		{
			// Load the existing form data
			$eventDataBeforeSave->formOld	= $this->getItem($data['id']);
			$eventDataBeforeSave->fieldsOld	= $this->loadFieldData($data['id'], $data['projectid']);
		}		
				
		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($eventDataBeforeSave));
		
		if (in_array(false, $result, true)) 
		{
			$this->setError($eventDataBeforeSave->getError());
			return false;
		}
		
		// Merge the changed data passed to the event back into the data structure
		$this->mergeObjectWithArray($newFormData, $data);
		
		unset($data['reference_id']);
		unset($data['catAlias']);
		unset($data['catTitle']);
		
		// Allow an exception to be throw.
		try
		{
			// Load the row if saving an existing record.
			/*
			if ($pk > 0) 
			{
				$table->load($pk);
			}
			*/
			
			// Bind the data.
			if (!$table->bind($data)) 
			{
				$this->setError($table->getError());
				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check()) 
			{
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store()) 
			{
				$this->setError($table->getError());
				return false;
			}

			// Clean the cache.
			$cache = JFactory::getCache($this->option);
			$cache->clean();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		
		$this->setState($this->getName().'.new', $isNew);
		
		// sync id for new forms
		$storage->formId = $this->getState('form.id');
		
		$storage->storeFields($fields);
		
		if(!$this->parse($table->state, $isNew))
		{
			return false;
		}
		
		$eventDataAfterSave 					= new F2cEventArgs();
		$eventDataAfterSave->action				= $eventDataBeforeSave->action;
		$eventDataAfterSave->isNew				= $eventDataBeforeSave->isNew;
		$eventDataAfterSave->formNew 			= $this->getItem($storage->formId);
		$eventDataAfterSave->fieldsNew 			= $this->loadFieldData($storage->formId, $this->contentTypeId);
		$eventDataAfterSave->formOld			= $eventDataBeforeSave->formOld;
		$eventDataAfterSave->fieldsOld			= $eventDataBeforeSave->fieldsOld;
		$eventDataAfterSave->parsedIntroContent	= $this->parsedIntroContent;
		$eventDataAfterSave->parsedMainContent	= $this->parsedMainContent;

		// Trigger the onContentAfterSave event.
		$result = $dispatcher->trigger($this->event_after_save, array($eventDataAfterSave));
		
		if (in_array(false, $result, true)) 
		{
			$this->setError($eventDataAfterSave->getError());
			return false;
		}

		return true;		
	}

	public function delete(&$pks, $batchImportMode = false)
	{
		// Initialise variables.
		$dispatcher		= JDispatcher::getInstance();
		$pks			= (array)$pks;
		$f2cFormTable 	= $this->getTable();

		// Include the content plugins for the events.				
		JPluginHelper::importPlugin('form2content');
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) 
		{
			if($f2cFormTable->load($pk)) 
			{
				if($this->canDelete($f2cFormTable) || $batchImportMode)
				{
					$eventData				= new F2cEventArgs();
					$eventData->action		= 'delete';
					$eventData->isNew		= false;		
					$eventData->formOld 	= $f2cFormTable;
					$eventData->fieldsOld	= $this->loadFieldData($f2cFormTable->id, $f2cFormTable->projectid);
					$eventData->formNew 	= null; // no form after delete
					$eventData->fieldsNew	= null; // no fields after delete
					
					$result = $dispatcher->trigger($this->event_before_delete, array($eventData));
					
					if (in_array(false, $result, true)) 
					{
						$this->setError($eventData->getError());
						return false;
					}
															
					if($f2cFormTable->reference_id)
					{
						// Delete the Joomla article referenced by the F2C Article
						if(!self::deleteJoomlaArticle($f2cFormTable->reference_id))
						{
							return false;
						}
					}
					
					// Delete the article images and the file uploads
					Path::Remove((Path::Combine(F2C_Image::GetImagesRootPath(), 'p'.$f2cFormTable->projectid.'/f'.$pk)));
					Path::Remove((Path::Combine(F2C_FileUpload::GetFilesRootPath(), 'c'.$f2cFormTable->projectid.'/a'.$pk)));
					
					// Delete the field contents	
					$query = $this->_db->getQuery(true);
					
					$query->delete();
					$query->from('#__f2c_fieldcontent');
					$query->where('formid='.(int)$pk);
					
					$this->_db->setQuery($query->__toString());
					
					if(!$this->_db->query())
					{
						$this->setError($this->_db->getErrorMsg());
						return false;
					}
						
					// Delete the form			
					if (!$f2cFormTable->delete($pk)) 
					{
						$this->setError($f2cFormTable->getError());
						return false;
					}
					
					$result = $dispatcher->trigger($this->event_after_delete, array($eventData));
					
					if (in_array(false, $result, true)) 
					{
						$this->setError($eventData->getError());
						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					
					if ($error) 
					{
						JError::raiseWarning(500, $error);
					}
					else 
					{
						JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
					}
				}			
			}
			else 
			{
				$this->setError($f2cFormTable->getError());
				return false;
			}
		}

		// Clear the component's cache
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		return true;
	}
	
	private function deleteJoomlaArticle($id)
	{
		JPluginHelper::importPlugin('content');
		
		$dispatcher	=& JDispatcher::getInstance();
		$table		=& JTable::getInstance('Content');
		$context 	= 'com_content.article';

		if(!$table->load($id))
		{
			// no corresponding Joomla article (anymore)
			return true;
		}
		
		// Trigger the onContentBeforeDelete event.
		$result = $dispatcher->trigger('onContentBeforeDelete', array($context, $table));
		
		if (in_array(false, $result, true)) 
		{
			$this->setError($table->getError());
			return false;
		}

		if (!$table->delete($id)) 
		{
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onContentAfterDelete event.
		$dispatcher->trigger('onContentAfterDelete', array($context, $table));
		
		return true;
	}
	
	public function publish(&$pks, $value = 1, $batchImportMode = false)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();
		$table		= $this->getTable();
		$pks		= (array)$pks;
		$joomlaIds	= array();
		
		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk) 
		{
			$table->reset();

			if ($table->load($pk)) 
			{
				if($value == F2C_STATE_TRASH)
				{
					if($this->canTrash($table) || $batchImportMode)
					{
						if($table->reference_id)
						{
							$joomlaIds[] = $table->reference_id;
						}
					}
					else 
					{
						// Prune items that you can't change.
						unset($pks[$i]);
						JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					}
				}
				else 
				{
					if ($this->canEditState($table) || $batchImportMode) 
					{
						if($table->reference_id)
						{
							$joomlaIds[] = $table->reference_id;
						}
					}
					else 
					{
						// Prune items that you can't change.
						unset($pks[$i]);
						JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					}
				}				
			}
		}

		// Attempt to change the state of the records.
		foreach ($pks as $i => $pk) 
		{ 	
			$this->setState($this->getName().'.id', $pk);

			if(!$this->parse($value, false))
			{
				return false;	
			}
		}
		
		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger('onContentChangeState', array('com_content.article', $joomlaIds, $value));

		if (in_array(false, $result, true)) 
		{
			$this->setError($table->getError());
			return false;
		}

		// Clear the F2C and content cache
		$cache = JFactory::getCache($this->option);
		$cache->clean();
		$cache = JFactory::getCache('com_content');
		$cache->clean();

		return true;
	}
	
	public function copy(&$pks)
	{
		$dispatcher =& JDispatcher::getInstance();
		$dateNow 	=& JFactory::getDate();
		$timestamp 	= $dateNow->toMySQL();
		$data 		= array();
		
		JPluginHelper::importPlugin('form2content');
		
		// Attempt to copy the forms.
		foreach ($pks as $i => $pk) 
		{
			$this->setState($this->getName().'.id', $pk);
			
			// load the form data
			$form 				= $this->getItem();
			$fieldDataList		= $this->loadFieldData($form->id, $form->projectid);
			$fieldDefinitions	= $this->loadFieldDefinitions($form->projectid);

			$eventData				= new F2cEventArgs();
			$eventData->action		= 'copy';
			$eventData->isNew		= true;
			$eventData->formOld		= $form;
			$eventData->fieldsOld	= $this->loadFieldData($form->id, $form->projectid);			
			
			// convert the form to a saveable structure...
			foreach($fieldDataList as $fieldData)
			{
				switch($fieldData->fieldtypeid)
				{					
					case F2C_FIELDTYPE_SINGLELINE:
					case F2C_FIELDTYPE_MULTILINETEXT:
					case F2C_FIELDTYPE_MULTILINEEDITOR:
					case F2C_FIELDTYPE_CHECKBOX:
					case F2C_FIELDTYPE_SINGLESELECTLIST:
					case F2C_FIELDTYPE_IFRAME:
					case F2C_FIELDTYPE_EMAIL:
					case F2C_FIELDTYPE_HYPERLINK:
					case F2C_FIELDTYPE_MULTISELECTLIST:
					case F2C_FIELDTYPE_DATEPICKER:
					case F2C_FIELDTYPE_DISPLAYLIST:
					case F2C_FIELDTYPE_DATABASE_LOOKUP:
						$fieldData->internal['fieldcontentid'] = 0;
						break;

					case F2C_FIELDTYPE_GEOCODER:
						$fieldData->internal['addressid'] = 0;
						$fieldData->internal['latid'] = 0;
						$fieldData->internal['lonid'] = 0;
						break;
																		
					case F2C_FIELDTYPE_IMAGE:
					case F2C_FIELDTYPE_FILE:
						$fieldData->internal['fieldcontentid'] = 0;
						$fieldData->internal['method'] = 'copy';
						break;						
				}
			}
			
			$formTable 	= $this->getTable();

			if(!$formTable->load($pk))
			{
				$this->setError($formTable->getError());
				return false;
			}

			// Add the category title and alias
			$query = $this->_db->getQuery(true);
			
			$query->select('title, alias');
			$query->from('`#__categories`');		
			$query->where('id = '.(int)$formTable->catid);
			
			$this->_db->setQuery($query->__toString());

			if($category = $this->_db->loadObject())
			{
				$formTable->catTitle = $category->title;
				$formTable->catAlias = $category->alias;
			}
			else
			{
				$formTable->catTitle = '';
				$formTable->catAlias = '';
			}
						
			$formTable->title = JText::_('COM_FORM2CONTENT_COPY_OF') . ' ' . $formTable->title;
			$formTable->alias = '';
			$formTable->id = 0; // force insert
			$formTable->created = $timestamp;
			$formTable->modified = null;
			$formTable->state = 0; // default unpublished
			$formTable->reference_id = null; // no article yet
			
			$eventData->formNew 	=& $formTable;
			$eventData->fieldsNew	=& $fieldDataList;
			
			$result = $dispatcher->trigger($this->event_before_save, array(&$eventData));

			if (in_array(false, $result, true)) 
			{
				$this->setError($eventData->getError());
				return false;
			}

			// Perform a check, because this will create the title alias
			if(!$formTable->check())
			{
				$this->setError($formTable->getError());
				return false;
			}
			
			// remove helper items that are nog part of the table
			unset($formTable->catTitle);
			unset($formTable->catAlias);
			
			if(!$formTable->store())
			{
				$this->setError($formTable->getError());
				return false;
			}
	
			$storage = new F2cStorage($formTable->id, true);
			$storage->preparedData = $fieldDataList;
			$storage->storeFields($fieldDefinitions);
	
			// Sync stored data with model
			$this->setState($this->getName().'.id', $pk);
			
			// reload the form data
			$eventData->formNew 	= $this->getItem();
			$eventData->fieldsNew 	= $this->loadFieldData($form->id, $form->projectid);
			
			$result = $dispatcher->trigger($this->event_after_save, array($eventData));
			
			if (in_array(false, $result, true)) 
			{
				$this->setError($eventData->getError());
				return false;
			}			
		}
				
		return true;
	}
	
	protected function validateFields($data, $fields)
	{
		$validator = new F2C_Validation();
		
		// Check required fields		
		foreach($fields as $field)
		{
			$fieldSettings = new JRegistry();
			$fieldSettings->loadString($field->settings);			
			
			if($fieldSettings->get('requiredfield'))
			{
				if(!$validator->valReqField($field, 't'.$field->id, $data))
				{
					$this->setError($fieldSettings->get('error_message_required', sprintf(JText::_('COM_FORM2CONTENT_ERROR_FIELD_X_REQUIRED'), $field->title)));
					return false;
				}
			}
			
			switch($field->fieldtypeid)
			{
				case F2C_FIELDTYPE_IMAGE:
					$msg = $validator->valSizeImage($field);					
					break;
				case F2C_FIELDTYPE_FILE:
					$msg = $validator->valSizeFileUpload($field);
					break;
				default:
					$msg = '';
					break;
			}
			
			if($msg)
			{
				$this->setError($msg);
				return false;
			}						
		}
		
		return true;		
	} 

	public function loadFieldDefinitions($projectid)
	{
		// get all the field definitions for this form
		$query = $this->_db->getQuery(true);
		
		$query->select('f.*');
		$query->from('`#__f2c_projectfields` AS f');		
		$query->where('f.projectid = '.(int)$projectid);
		$query->order('f.ordering');		

		$this->_db->setQuery($query->__toString());

		$fields = $this->_db->loadObjectList();
		 
		if(count($fields))
		{
			foreach($fields as $field)
			{
				$settings = new JRegistry();
				$settings->loadString($field->settings);
				$field->settings = $settings;				
			}
		}
		
	  	return $fields;		
	}
	
	function loadFieldData($formId, $contentTypeId)
	{
		$fieldData 	= array();
		$fldData 	= null;
		
		// Load the fields data
		$query = $this->_db->getQuery(true);
		
		// original
		$query->select('pf.*');
		$query->from('#__f2c_projectfields pf');
		$query->where('pf.projectid = '.(int)$contentTypeId);
		$query->select('fc.attribute, fc.content, fc.id AS fieldcontentid, IFNULL(fc.formid, ' . (int)$formId . ') as formid');
		$query->join('LEFT', '#__f2c_fieldcontent fc ON fc.fieldid = pf.id AND fc.formid = '.(int)$formId);
		$query->order('pf.ordering, pf.fieldtypeid');

		$this->_db->setQuery($query);
		
		$fieldContentList = $this->_db->loadObjectList();

		if(count($fieldContentList))
		{
			$formsData = $this->createFormDataObjects($fieldContentList);

			return $formsData[(int)$formId];
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Method to load the translations for the Content Type fields
	 * 
	 * @param	int			$contentTypeId		The Id of the Content Type
	 * @param	string		$languageId			The language Id			
	 * @return	list		Array of translations indexed by Content Type Field Id		
	 * @since	4.0.0
	 */
	function loadFieldTranslations($contentTypeId, $languageId)
	{
		$query = $this->_db->getQuery(true);
		
		$query->select('f.id, t.title_translation, t.description_translation');
		$query->from('#__f2c_projectfields f');
		$query->join('INNER', '#__f2c_translation t ON f.id = t.reference_id and t.language_id = ' . $this->_db->quote($languageId));
		$query->where('f.projectid =' . (int)$contentTypeId);
		
		$this->_db->setQuery($query->__toString());
		
		return $this->_db->loadObjectList('id');		
	}
	
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_form2content.edit.form.data', array());

		if (empty($data)) 
		{
			$data 					= $this->getItem();			
			$modelContentType 		= new Form2ContentModelProject();			
			$contentTypeId 			= $data->projectid;						
			$contentType			= $modelContentType->getItem($contentTypeId);
			$contentTypeSettings 	= new JRegistry();
			
			$contentTypeSettings->loadArray($contentType->settings);
			
			// Check if a default category is selected
			$defaultCategoryId = (int)$contentTypeSettings->get('catid');
	
			if($defaultCategoryId != -1)
			{
				if((int)$contentTypeSettings->get('cat_behaviour') == 0)
				{
					// The category is fixed
					$data->set('catid', $defaultCategoryId);
				}
			}
		}

		return $data;
	}
	
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		
		if($user->authorise('core.delete', 'com_form2content.form.'.(int)$record->id))
		{
			return true;
		}
		
		if($user->authorise('form2content.delete.own', 'com_form2content.form.'.(int)$record->id))
		{
			// Now test the owner is the user.
			$ownerId	= (int)isset($record->created_by) ? $record->created_by : 0;
			if (empty($ownerId) && $record) 
			{
				// Need to do a lookup from the model.
				$record		= $this->getModel()->getItem($record->id);

				if (empty($record)) 
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $user->id) 
			{
				return true;
			}			
		}
		
		return false;
	}

	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing article.
		if (!empty($record->id)) 
		{
			if($user->authorise('core.edit.state', 'com_form2content.form.'.(int) $record->id))
			{
				return true;
			}

			if($user->authorise('form2content.edit.state.own', 'com_form2content.form.'.(int) $record->id))
			{
				// Now test the owner is the user.
				$ownerId	= (int)isset($record->created_by) ? $record->created_by : 0;
				if (empty($ownerId) && $record) 
				{
					// Need to do a lookup from the model.
					$record	= $this->getItem($record->id);

					if (empty($record)) 
					{
						return false;
					}
	
					$ownerId = $record->created_by;
				}
	
				// If the owner matches 'me' then do the test.
				if ($ownerId == $user->id) 
				{
					return true;
				}			
			}			
		}
		else 
		{
			return parent::canEditState($record);
		}
		
		return false;
	}
	
	private function canTrash($record)
	{
		$user = JFactory::getUser();

		if($user->authorise('form2content.trash', 'com_form2content.form.'.(int) $record->id))
		{
			return true;
		}
			
		if($user->authorise('form2content.trash.own', 'com_form2content.form.'.(int) $record->id))
		{
			// Now test the owner is the user.
			$ownerId	= (int)isset($record->created_by) ? $record->created_by : 0;
			if (empty($ownerId) && $record) 
			{
				// Need to do a lookup from the model.
				$record		= $this->getModel()->getItem($record->id);

				if (empty($record)) 
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $user->id) 
			{
				return true;
			}			
		}

		return false;
	}
	
	protected function prepareTable($table)
	{
		// Set the publish date to now
		if($table->state == 1 && intval($table->publish_up) == 0) 
		{
			$table->publish_up = JFactory::getDate()->toMySQL();
		}

		// Reorder the articles within the category so the new article is first
		if (empty($table->id)) 
		{
			$table->reorder('catid = '.(int) $table->catid.' AND state >= 0');
		}
	}
	
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'catid = '.(int) $table->catid;
		return $condition;
	}
	
	public function getContentTypeSelectList($publishedOnly = true, $authorizedOnly = true)
	{
		$db 	=& JFactory::getDBO();
		$query 	= $db->getQuery(true);
		$user 	= JFactory::getUser();
		
		$query->select('id AS value, title AS text');
		$query->from('`#__f2c_project`');				
		if($publishedOnly) $query->where('published = 1');
		$query->order('title'); 
		$db->setQuery($query->__toString());
		
		$contentTypeList = $db->loadObjectList();
		
		foreach($contentTypeList as $i => $contentType)
		{
			if ($user->authorise('core.create', 'com_form2content.project.'.$contentType->value) != true ) 
			{
				unset($contentTypeList[$i]);
			}
		}
		
		return $contentTypeList;			
	}
	
	public function parse($publishState, $isNew = false)
	{
		JPluginHelper::importPlugin('form2content');
		
		$dispatcher 		=& JDispatcher::getInstance();
		$context			= $this->option.'.'.$this->name;	
		$form 				= $this->getItem();
		$formData 			= $this->loadFieldData($form->id, $form->projectid);	

		$eventData				= new F2cEventArgs();
		$eventData->action		= 'parse';
		$eventData->isNew		= $isNew;
		$eventData->formOld		= $form;
		$eventData->fieldsOld	= $formData;			
		$eventData->formNew		= $form;
		$eventData->fieldsNew	= $formData;			
		
		$performParse 		= true;
		
		$result = $dispatcher->trigger($this->event_before_parse, array($eventData));
		
		if (in_array(false, $result, true)) 
		{
			$this->setError($eventData->getError());
			return false;
		}
		
		if($performParse)
		{	
			// Resync data
			$form = $this->getItem();
			$formData = $this->loadFieldData($form->id, $form->projectid);
			$translatedFields = $this->loadFieldTranslations($form->projectid, $form->language);

			// Fill the translations for the fields
			if(count($formData))
			{
				foreach($formData as $contentTypeField)
				{
					if(array_key_exists($contentTypeField->id, $translatedFields))
					{
						$translatedField = $translatedFields[$contentTypeField->id];
						
						if($translatedField->title_translation)
						{
							$contentTypeField->title = $translatedField->title_translation;
						}

						if($translatedField->description_translation)
						{
							$contentTypeField->description = $translatedField->description_translation;
						}						
					}
				}
			}

			if(!$this->parseSingleForm($form, $formData, $publishState))
			{
				return false;
			}
			
			$eventData->parsedIntroContent = $this->parsedIntroContent;
			$eventData->parsedMainContent = $this->parsedMainContent;
			
			$result = $dispatcher->trigger($this->event_after_parse, array($eventData));
			
			if (in_array(false, $result, true)) 
			{
				$this->setError($eventData->getError());
				return false;
			}
		}
				
		return true;
	}
	
	public function parseSingleForm($form, $formData, $publishState)
	{
		$user 			=& JFactory::getUser();
		$nullDate 		= $this->_db->getNullDate();
		$datenow 		=& JFactory::getDate();
		$dispatcher 	=& JDispatcher::getInstance();
		$row			= null;
		$errorMsgPrefix	= JText::_('COM_FORM2CONTENT_FORM_ID') . ' ' . $form->id . ': ';
	
		JPluginHelper::importPlugin('content');
		
		$parser = new F2cParser();
		
		if(!$parser->addTemplate($form->intro_template, F2C_TEMPLATE_INTRO))
		{
			$this->setError($errorMsgPrefix . $parser->getError());
			return false;				
		}
		
		if($form->main_template)
		{
			if(!$parser->addTemplate($form->main_template, F2C_TEMPLATE_MAIN))
			{
				$this->setError($errorMsgPrefix . $parser->getError());
				return false;				
			}
		}

		$categoryAlias	= $form->catAlias; // for use in re-parsing
		$joomlaId 		= $form->reference_id;
		$usrTmp 		= JFactory::getUser($form->created_by);
		$form->fields 	= $formData;
		
		$parser->addVars($form);
		
		$row =& $this->getTable('content');
		$row->load((int)$form->reference_id);
		$isNew = false;
		
		$query = $this->_db->getQuery(true);

		$query->select('count(*)');
		$query->from('`#__content`');				
		$query->where('id=' . (int)$form->reference_id);
		$this->_db->setQuery($query->__toString());
		
		if($this->_db->loadResult())
		{
			$isNew = false;			
			// fail if checked out not by 'me'
			if ($row->checked_out && $row->checked_out != $user->id) 
			{
				$this->setError($errorMsgPrefix . JText::_('COM_FORM2CONTENT_ERROR_FORM_CHECKED_OUT'));
				return false;
			}
			
			$row->modified 		= $datenow->toMySQL();
			$row->modified_by 	= $user->get('id');			
		}
		else
		{
			// deleted / archived
			$isNew = true;
			$form->reference_id = null;
		}

		if($isNew)
		{
			// init new content item
			$row =& $this->getTable('content');
			$row->load(0);
			//if $publishState is set to F2C_STATE_RETAIN, set to unpublished
			$row->state		= ($publishState == F2C_STATE_RETAIN) ? F2C_STATE_PUBLISHED : $form->state;
			$row->state		= $form->state;
			$row->modified 	= $nullDate;
		}
		
		$row->created_by 	= $usrTmp->id;
		$row->created 		= $form->created;

		if($form->publish_up == $nullDate)
		{	
			$row->publish_up = $datenow->toFormat('%Y-%m-%d') . ' 00:00:00';
		}	
		else
		{
			$row->publish_up = $form->publish_up;
		}
		
		$row->publish_down = $form->publish_down;
				
		if($this->f2cConfig->get('autosync_article_order'))
		{
			$row->ordering = $form->ordering;
		}
	
		$this->parsedIntroContent = $parser->parseIntro();
		
		if($parser->getError())
		{
			$this->setError($errorMsgPrefix . $parser->getError());
			return false;
		}
		
		$this->parsedMainContent = $parser->parseMain();
		
		if($parser->getError())
		{
			$this->setError($errorMsgPrefix . $parser->getError());
			return false;
		}
	
		$attribs = new JRegistry();
		$attribs->loadArray($form->attribs);
		
		$metadata = new JRegistry();
		$metadata->loadArray($form->metadata);
		
		$row->introtext 		= $this->parsedIntroContent;	
		$row->fulltext 			= $this->parsedMainContent;
		$row->title 			= $form->title;
		$row->alias 			= $form->alias;
		$row->metadesc 			= $form->metadesc;
		$row->metakey			= $form->metakey;
		$row->sectionid 		= $form->sectionid;
		$row->catid 			= $form->catid;
		$row->state 			= ($publishState == F2C_STATE_RETAIN) ? $form->state : $publishState;
		$row->featured			= $form->featured;
		$row->created_by_alias 	= $form->created_by_alias;
		$row->access			= $form->access;
		$row->attribs			= $attribs->toString();
		$row->metadata			= $metadata->toString();
		$row->language			= $form->language;
		
		// Make sure the data is valid
		if (!$row->check()) 
		{
			$this->setError($errorMsgPrefix . $row->getError());
			return false;
		}

		// Increment the content version number
		$row->version++;

		// Trigger the onContentBeforeSave event.
		$result = $dispatcher->trigger('onContentBeforeSave', array('com_content.article', &$row, $isNew));
		
		if (in_array(false, $result, true)) 
		{
			$this->setError($errorMsgPrefix . $row->getError());
			return false;
		}
	
		// Store the content to the database
		if (!$row->store()) 
		{
			$this->setError($errorMsgPrefix . $row->getError());
			return false;
		}
	
		if($isNew || $joomlaId != $row->id || !$form->alias)
		{
			// reparse to get the correct Article Id and/or title alias
			$slug = ($row->alias) ? $row->id.':'.$row->alias : $row->id;
			$catslug = ($categoryAlias) ? $form->catid.':'.$categoryAlias : $form->catid;
			$seflink = 'index.php?option=com_content&view=article&id='. $slug . '&catid=' . $catslug;
			
			$parser->clearVar('JOOMLA_ID');
			$parser->addVar('JOOMLA_ID', $row->id);
			$parser->clearVar('JOOMLA_TITLE_ALIAS');
			$parser->addVar('JOOMLA_TITLE_ALIAS', $row->alias);
			$parser->clearVar('JOOMLA_ARTICLE_LINK');		
			$parser->addVar('JOOMLA_ARTICLE_LINK', $seflink);
			$parser->clearVar('JOOMLA_ARTICLE_LINK_SEF');
			$parser->addVar('JOOMLA_ARTICLE_LINK_SEF', '{plgContentF2cSef}'.$slug.','.$catslug.'{/plgContentF2cSef}');	 		
			$parser->clearTemplates();
	
			$this->parsedIntroContent = $parser->parseIntro();
			
			if($parser->getError())
			{
				$this->setError($errorMsgPrefix . $parser->getError());
				return false;
			}
		
			$this->parsedMainContent = $parser->parseMain();
			
			if($parser->getError())
			{
				$this->setError($errorMsgPrefix . $parser->getError());
				return false;
			}
	
			$row->introtext	= $this->parsedIntroContent;
			$row->fulltext 	= $this->parsedMainContent;
	
			// Make sure the data is valid
			if (!$row->check()) 
			{
				$this->setError($errorMsgPrefix . $row->getError());
				return false;
			}
	
			// Store the content to the database
			if (!$row->store()) 
			{
				$this->setError($errorMsgPrefix . $db->stderr());
				return false;
			}
		}
	
		$row->checkin();
		
		if($this->f2cConfig->get('autosync_article_order'))
		{
			F2cContentHelper::syncArticleOrder($row->catid);	
		}
		
		// Set the featured option of the Joomla article
		$this->featured($form->reference_id, $form->featured);
		
		// Clean the cache.
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		// Trigger the onContentAfterSave event.
		$result = $dispatcher->trigger('onContentAfterSave', array('com_content.article', &$row, $isNew));
		
		if (in_array(false, $result, true)) 
		{
			$this->setError($errorMsgPrefix . $row->getError());
			return false;
		}
		
	 	// Sync F2C Article with data from Joomla Article
	 	$rowForm = $this->getTable('form');
	 	$rowForm->load($form->id);
	 	$rowForm->alias = $row->alias;
	 	$rowForm->state = $row->state;
		$rowForm->reference_id = $row->id;
		
		if (!$rowForm->store()) 
		{
			$this->setError($errorMsgPrefix . $rowForm->getError());
			return false;
		}
		
		return true;
	}
	
	/*
	 * Toggle the featured option of a Joomla Article
	 */
	public function featured($pk, $value = 0)
	{
		JTable::addIncludePath(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_content'.DS.'tables');
		
		$table = $this->getTable('Featured', 'ContentTable');

		try 
		{
			$db =& $this->getDbo();

			// Adjust the mapping table.
			// Clear the existing features settings.
			$query = $db->getQuery(true);
			$query->delete('#__content_frontpage');
			$query->where('content_id = ' . (int)$pk);
					
			$db->setQuery($query->__toString());

			if (!$db->query()) 
			{
				throw new Exception($db->getErrorMsg());
			}

			if($value == 1) 
			{
				// Featuring.
				$query = $db->getQuery(true);
				$query->insert('#__content_frontpage');
				$query->set('content_id='.(int)$pk);
				$query->set('ordering=1');
				
				$db->setQuery($query->__toString());

				if (!$db->query()) 
				{
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
		} 
		catch (Exception $e) 
		{
			$this->setError($e->getMessage());
			return false;
		}

		$table->reorder();

		$cache = JFactory::getCache('com_content');
		$cache->clean();

		return true;
	}
	
	public function featuredList($pks, $value)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();
		$table		= $this->getTable();
		$pks		= (array)$pks;
		
		JArrayHelper::toInteger($pks);

		if (empty($pks)) 
		{
			$this->setError(JText::_('COM_CONTENT_NO_ITEM_SELECTED'));
			return false;
		}

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk) 
		{
			$table->reset();

			if ($table->load($pk)) 
			{
				if (!$this->canEditState($table)) 
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
				
				$table->featured = $value;
				
				if(!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}
				
				// Set the correct PK for the model
				$this->setState($this->getName().'.id', $pk);
				
				if(!$this->parse($table->state, false))
				{
					return false;	
				}				
			}
		}
		
		// Clear the F2C and content cache
		$cache = JFactory::getCache($this->option);
		$cache->clean();
		$cache = JFactory::getCache('com_content');
		$cache->clean();

		return true;
	}
	
	public function reorder($pks, $delta)
	{
		// reorder the f2c_form table
		if(parent::reorder($pks, $delta))
		{
			if($this->f2cConfig->get('autosync_article_order'))
			{
				$categories = $this->getCategories($pks);
				
				foreach($categories as $catid)
				{
					F2cContentHelper::syncArticleOrder($catid);						
				}				
			}			
		}
		else
		{
			return false;
		}	
	}
	
	public function saveorder($pks, $order)
	{
		// reorder the f2c_form table
		if(parent::saveorder($pks, $order))
		{
			if($this->f2cConfig->get('autosync_article_order'))
			{
				$categories = $this->getCategories($pks);
				
				foreach($categories as $catid)
				{
					F2cContentHelper::syncArticleOrder($catid);						
				}
			}			
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * Load a list of unique category Id's for a set of F2C Articles
	 */
	private function getCategories($pks)
	{
		$db =& $this->getDbo();
	
		$query = $db->getQuery(true);
		$query->select('DISTINCT catid');
		$query->from('#__f2c_form');
		$query->where('id IN ('. implode(',', $pks) . ')');

		$db->setQuery($query->__toString());
		return $db->loadResultArray();		
	}
	
	/**
	 * Method to validate the form data.
	 *
	 * @param	object		$form		The form to validate against.
	 * @param	array		$data		The data to validate.
	 * @return	mixed		Array of filtered data if valid, false otherwise.
	 * @since	4.0.0
	 */
	function validate($form, $data)
	{
		$app = JFactory::getApplication();
		
		// override the required setting for fields that are not shown on the form
		if($app->isSite())
		{
			$modelContentType 		= new Form2ContentModelProject();
			$contentTypeId 			= (int)$data['projectid'];
			$contentType			= $modelContentType->getItem($contentTypeId);
			$contentTypeSettings 	= new JRegistry();
					
			$contentTypeSettings->loadArray($contentType->settings);
			
			if(!$contentTypeSettings->get('frontend_catsel'))
			{
				$form->setFieldAttribute('catid', 'required', 'false');
			}			
		}
		
		return parent::validate($form, $data);
	}
	
	function convertArrayToObject($array)
	{
		$object = new JObject();
		
		foreach($array as $key => $value)
		{
			$object->$key = $value;
		}
		
		return $object;
	}
	
	function mergeObjectWithArray($object, &$array)
	{
		foreach(get_object_vars($object) as $property => $value)
		{
			// Skip properties starting with underscore
			if(strpos($property, '_') !== 0)
			{
				// overwrite the array value
				$array[$property] = $value;
			}
		}
	}
	
	private function getCategoryInfo($catId)
	{
		$query = $this->_db->getQuery(true);
		
		$query->select('title, alias');
		$query->from('`#__categories`');		
		$query->where('id = '.(int)$catId);
		
		$this->_db->setQuery($query->__toString());

		return $this->_db->loadObject();		
	}
	
	public function createFormDataObjects($fieldContentList)
	{
		$formData  = null;
		$fldData 	= null;
		$forms		= array();

		if(count($fieldContentList))
		{
			foreach($fieldContentList as $fieldContent)
			{
				if(!array_key_exists($fieldContent->formid, $forms))
				{
					$forms[$fieldContent->formid] = array();
				}
				
				$formData =& $forms[$fieldContent->formid];

				if(!array_key_exists($fieldContent->fieldname, $formData))
				{	
					$settings = new JRegistry();
					$settings->loadString($fieldContent->settings);
					
					$fldData 				= new F2cFieldData();
					$fldData->id 			= $fieldContent->id;
					$fldData->fieldtypeid 	= $fieldContent->fieldtypeid;
					$fldData->title 		= $fieldContent->title;
					$fldData->fieldname 	= $fieldContent->fieldname;
					$fldData->description	= $fieldContent->description;
					$fldData->projectid		= $fieldContent->projectid;
					$fldData->ordering		= $fieldContent->ordering;
					$fldData->frontvisible	= $fieldContent->frontvisible;								
					$fldData->settings 		= $settings;
					
					// provide default values
					switch ($fldData->fieldtypeid)
					{
						case F2C_FIELDTYPE_SINGLELINE:
						case F2C_FIELDTYPE_MULTILINETEXT:
						case F2C_FIELDTYPE_MULTILINEEDITOR:
						case F2C_FIELDTYPE_CHECKBOX:
						case F2C_FIELDTYPE_SINGLESELECTLIST:
						case F2C_FIELDTYPE_DATEPICKER:
						case F2C_FIELDTYPE_DATABASE_LOOKUP:
							$fldData->values['VALUE']				= '';
							$fldData->internal['fieldcontentid']	= 0;
							break;
							
						case F2C_FIELDTYPE_IMAGE:
							$fldData->values['FILENAME'] 			= '';
							$fldData->values['ALT'] 				= '';
							$fldData->values['TITLE'] 				= '';
							$fldData->values['WIDTH'] 				= null;
							$fldData->values['HEIGHT'] 				= null;
							$fldData->values['WIDTH_THUMBNAIL'] 	= null;
							$fldData->values['HEIGHT_THUMBNAIL'] 	= null;
							$fldData->internal['method'] 			= '';
							$fldData->internal['delete'] 			= '';
							$fldData->internal['currentfilename']	= '';
							$fldData->internal['imagelocation']		= '';
							$fldData->internal['thumblocation']		= '';
							$fldData->internal['fieldcontentid']	= 0;
							break;
	
						case F2C_FIELDTYPE_IFRAME:
							$fldData->values['URL'] 				= '';
							$fldData->values['WIDTH'] 				= null;
							$fldData->values['HEIGHT'] 				= null;
							$fldData->internal['fieldcontentid']	= 0;
							break;
							
						case F2C_FIELDTYPE_EMAIL:
							$fldData->values['EMAIL'] 				= '';
							$fldData->values['DISPLAY_AS'] 			= '';
							$fldData->internal['fieldcontentid']	= 0;
							break;
							
						case F2C_FIELDTYPE_HYPERLINK:
							$fldData->values['URL'] 				= '';
							$fldData->values['DISPLAY_AS'] 			= '';
							$fldData->values['TITLE'] 				= '';
							$fldData->values['TARGET'] 				= '';
							$fldData->internal['fieldcontentid']	= 0;
							break;
							
						case F2C_FIELDTYPE_MULTISELECTLIST:
						case F2C_FIELDTYPE_DISPLAYLIST:
							$fldData->values['VALUE'] 				= array();
							$fldData->internal['fieldcontentid']	= 0;
							break;		
	
						case F2C_FIELDTYPE_FILE:
							$fldData->values['FILENAME'] 			= '';
							$fldData->internal['method'] 			= '';
							$fldData->internal['delete'] 			= '';
							$fldData->internal['filelocation']		= '';
							$fldData->internal['fieldcontentid']	= 0;	
							break;
							
						case F2C_FIELDTYPE_GEOCODER:
							$fldData->values['ADDRESS']				= '';
							$fldData->values['LAT']					= '';
							$fldData->values['LON']					= '';						
							$fldData->internal['addressid']			= 0;
							$fldData->internal['latid']				= 0;
							$fldData->internal['lonid']				= 0;
							break;
							
						case F2C_FIELDTYPE_DB_LOOKUP_MULTI:
							$fldData->values['VALUE'] = array();
							break;
							
						case F2C_FIELDTYPE_IMAGE_GALLERY:
							$fldData->values['VALUE']				= array();
							$fldData->internal['fieldcontentid']	= 0;
							break;
							
					}
					
					$formData[$fieldContent->fieldname] = $fldData;
				}
				
				switch ($fldData->fieldtypeid)
				{
					case F2C_FIELDTYPE_SINGLELINE:
					case F2C_FIELDTYPE_MULTILINETEXT:
					case F2C_FIELDTYPE_MULTILINEEDITOR:
					case F2C_FIELDTYPE_CHECKBOX:
					case F2C_FIELDTYPE_SINGLESELECTLIST:
					//case F2C_FIELDTYPE_INFOTEXT:
					case F2C_FIELDTYPE_DATEPICKER:
					case F2C_FIELDTYPE_DATABASE_LOOKUP:
						if($fieldContent->attribute)
						{
							$fldData->values[$fieldContent->attribute] 	= $fieldContent->content;
							$fldData->internal['fieldcontentid'] 		= $fieldContent->fieldcontentid;
						}
						break;
						
					case F2C_FIELDTYPE_IMAGE:					
						$fldData->internal['fieldcontentid']	= $fieldContent->fieldcontentid;
						$values 								= new JRegistry($fieldContent->content);
						$fldData->values['FILENAME'] 			= $values->get('filename');
						$fldData->values['ALT'] 				= $values->get('alt');
						$fldData->values['TITLE'] 				= $values->get('title');					
						$fldData->values['WIDTH'] 				= ($values->get('width') != -1) ? $values->get('width') : null;
						$fldData->values['HEIGHT'] 				= ($values->get('height') != -1) ? $values->get('height') : null;
						$fldData->values['WIDTH_THUMBNAIL'] 	= ($values->get('widthThumbnail') != -1) ? $values->get('widthThumbnail') : null;
						$fldData->values['HEIGHT_THUMBNAIL'] 	= ($values->get('heightThumbnail') != -1) ? $values->get('heightThumbnail') : null;						
						$fldData->internal['method'] 			= '';
						$fldData->internal['delete'] 			= '';
						$fldData->internal['currentfilename']	= $values->get('filename');
						
						if($values->get('filename'))
						{
							$fldData->internal['imagelocation']		= Path::Combine(F2C_Image::GetImagesPath($fieldContent->projectid, $fieldContent->formid, false), $values->get('filename'));
							$fldData->internal['thumblocation']		= Path::Combine(F2C_Image::GetThumbnailsPath($fieldContent->projectid, $fieldContent->formid), $values->get('filename'));
						}										
						break;
						
					case F2C_FIELDTYPE_IFRAME:
						$fldData->internal['fieldcontentid']	= $fieldContent->fieldcontentid;
						$values 								= new JRegistry($fieldContent->content);
						$fldData->values['URL'] 				= $values->get('url');
						$fldData->values['WIDTH'] 				= $values->get('width');
						$fldData->values['HEIGHT'] 				= $values->get('height');
						break;
	
					case F2C_FIELDTYPE_EMAIL:
						$fldData->internal['fieldcontentid']	= $fieldContent->fieldcontentid;					
						$values 								= new JRegistry($fieldContent->content);
						$fldData->values['EMAIL'] 				= $values->get('email');
						$fldData->values['DISPLAY_AS'] 			= $values->get('display');
						break;
						
					case F2C_FIELDTYPE_HYPERLINK:					
						$fldData->internal['fieldcontentid']	= $fieldContent->fieldcontentid;
						$values 								= new JRegistry($fieldContent->content);
						$fldData->values['URL'] 				= $values->get('url');
						$fldData->values['DISPLAY_AS'] 			= $values->get('display');
						$fldData->values['TITLE'] 				= $values->get('title');
						$fldData->values['TARGET'] 				= $values->get('target');
						break;					
						
					case F2C_FIELDTYPE_DISPLAYLIST:
						$fldData->internal['fieldcontentid']	= $fieldContent->fieldcontentid;
						$values 								= new JRegistry($fieldContent->content);											
						$fldData->values['VALUE'] 				= $values->toArray();
						break;
						
					case F2C_FIELDTYPE_MULTISELECTLIST:
						$fldData->values[$fieldContent->attribute][] = $fieldContent->content;
						break;
						
					case F2C_FIELDTYPE_FILE:
						$fldData->values['FILENAME'] 			= $fieldContent->content;
						$fldData->internal['method'] 			= '';
						$fldData->internal['delete'] 			= '';
						$fldData->internal['fieldcontentid']	= $fieldContent->fieldcontentid;
	
						if($fieldContent->content)
						{
							$fldData->internal['filelocation'] = Path::Combine(F2C_FileUpload::GetFilePath($fieldContent->projectid, $fieldContent->formid, $fieldContent->id), $fieldContent->content);						
						}
						break;
	
					case F2C_FIELDTYPE_GEOCODER:
												
						$fldData->values[$fieldContent->attribute] 	= $fieldContent->content;
						
						switch($fieldContent->attribute)
						{
							case 'ADDRESS':
								$fldData->internal['addressid']	= $fieldContent->fieldcontentid;
								break;
							case 'LAT':
								$fldData->internal['latid']	= $fieldContent->fieldcontentid;
								break;
							case 'LON':
								$fldData->internal['lonid']	= $fieldContent->fieldcontentid;
								break;
						}						
						break;
											
					case F2C_FIELDTYPE_DB_LOOKUP_MULTI:
						$fldData->values[$fieldContent->attribute][] = $fieldContent->content;
						break;	
						
					case F2C_FIELDTYPE_IMAGE_GALLERY:
						$values 								= new JRegistry($fieldContent->content);
						$fldData->values['VALUE'] 				= $values->toArray();
						$fldData->internal['fieldcontentid']	= $fieldContent->fieldcontentid;
						$fldData->internal['basedir']			= JPATH_SITE.DS.'images'.DS.'stories'.DS.'com_form2content'.DS.'p'.$fieldContent->projectid.DS.'f'.$fieldContent->formid.DS.'gallery'.$fieldContent->id;
						break;				
				}			
			}
		}

		return $forms;		
	}
	
	public function import()
	{
		$app			= JFactory::getApplication();
		$f2cConfig 		= F2cFactory::getConfig();
		$importDir 		= $f2cConfig->get('import_dir');
		$archiveDir		= $f2cConfig->get('import_archive_dir');
		$errorDir		= $f2cConfig->get('import_error_dir');
		$postAction 	= $f2cConfig->get('import_post_action', 1);
		$importTmpPath	= Path::Combine(JFactory::getConfig()->get('tmp_path'), 'f2c_import');

		$this->AddLogEntry(JText::_('COM_FORM2CONTENT_IMPORT_STARTED'));
		
		if(empty($importDir))
		{
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_DIR_EMPTY'));
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_IMPORT_ENDED'));
			return false;
		}

		if(!JFolder::exists($importDir))
		{
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_DIR_DOES_NOT_EXIST'));
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_IMPORT_ENDED'));
			return false;
		}
		
		if(empty($errorDir))
		{
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_ERROR_ERROR_DIR_EMPTY'));
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_IMPORT_ENDED'));
			return false;
		}

		if(!JFolder::exists($errorDir))
		{
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_ERROR_ERROR_DIR_DOES_NOT_EXIST'));
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_IMPORT_ENDED'));
			return false;
		}
		
		if($postAction == F2C_IMPORT_POSTACTION_ARCHIVE)
		{
			if(empty($archiveDir))
			{
				$this->AddLogEntry(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_ARCHIVE_DIR_EMPTY'));
				$this->AddLogEntry(JText::_('COM_FORM2CONTENT_IMPORT_ENDED'));
				return false;
			}
			
			if(!JFolder::exists($archiveDir))
			{
				$this->AddLogEntry(JText::_('COM_FORM2CONTENT_ERROR_ARCHIVE_DIR_DOES_NOT_EXIST'));
				$this->AddLogEntry(JText::_('COM_FORM2CONTENT_IMPORT_ENDED'));
				return false;
			}			
		}
		
		if(JFolder::exists($importTmpPath))
		{
			JFolder::delete($importTmpPath);			
		}
		
		// Create a temporary folder for files and images
		JFolder::create($importTmpPath);
		
		$importFiles = JFolder::files($importDir, '.', false, true);
		
		// only handle XML files
		if(count($importFiles))
		{
			// Initialize the dictionaries
			$this->InitXmlImport();
			
			foreach($importFiles as $importFile)
			{
				if(strtolower(JFile::getExt($importFile)) == 'xml')
				{
					$this->AddLogEntry(sprintf(JText::_('COM_FORM2CONTENT_IMPORTING_FILE'), JFile::getName($importFile)));
					
					// Import this XML file
					$result = $this->importXmlFile($importFile);
					
					if($result['res'] == 1)
					{
						// Clean up after a successful import
						if($postAction == F2C_IMPORT_POSTACTION_DELETE)
						{
							JFile::delete($importFile);
						}
						else
						{
							$timestamp = new JDate();
							$fileName = $timestamp->format('YmdHis'). ' ' . JFile::getName($importFile);
							JFile::move($importFile, Path::Combine($archiveDir, $fileName));
						}
					}
					else
					{
						// something went wrong
						$timestamp = new JDate();
						$fileName = $timestamp->format('YmdHis'). ' ' . JFile::getName($importFile);
						JFile::move($importFile, Path::Combine($errorDir, $fileName));
					}

					$errors = $this->getErrors();
					
					if(count($errors))
					{
						foreach($errors as $error)
						{
							$this->AddLogEntry($error);			
						}
					}
					
					$this->AddLogEntry(sprintf(JText::_('COM_FORM2CONTENT_IMPORT_TOTALS'), $result['ins'], $result['upd'], $result['del']));
				}		
			}
		}
		else
		{
			$this->AddLogEntry(JText::_('COM_FORM2CONTENT_NO_IMPORT_FILES'));
		}

		// Clean up the temporary folder for files and images
		JFolder::delete($importTmpPath);
		
		$this->AddLogEntry(JText::_('COM_FORM2CONTENT_IMPORT_ENDED'));
	}
	
	private function importXmlFile($filename)
	{
		$f2cConfig 			= F2cFactory::getConfig();
		$nullDate			= $this->_db->getNullDate();
		$importTmpPath		= Path::Combine(JFactory::getConfig()->get('tmp_path'), 'f2c_import');
		$importWriteMode 	= $f2cConfig->get('import_write_action', 1);
		$results			= array('ins' => 0, 'upd' => 0, 'del' => 0, 'res' => 0);
		
		if(!($xml = $this->loadImportFile($filename)))
		{
			return $results;
		}
		
		foreach ($xml as $xmlForm)
		{
			$form 			= new Form2ContentModelForm(array('ignore_request' => true));
			$attribs 		= array();
			$metadata		= array();
			$rules			= array();
			$data 			= array();
			$fieldData 		= array();
			$tmpFiles		= array();

			// Resolve the Content Type
			$contentTypeId = $this->dicContentTypeTitle[(string)$xmlForm->contenttype];

			if(!$contentTypeId)
			{
				$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_CONTENTTYPE_NOT_RESOLVED'), (string)$xmlForm->contenttype));
				return $results;
			}
			
			if($importWriteMode == F2C_IMPORT_WRITEMODE_CREATENEW)
			{
				// Always create a new F2C Article
				$formId = 0;
			}
			else 
			{
				// Check if an alternate key was specified
				if($xmlForm->id->attributes()->fieldname)
				{
					// Find the id of the form through the alternate key
					$query = $this->_db->getQuery(true);
					
					$query->select('frm.id');
					$query->from('#__f2c_form frm');
					$query->join('inner', '#__f2c_fieldcontent flc on frm.id = flc.formid');
					$query->join('inner', '#__f2c_projectfields pfl on flc.fieldid = pfl.id');
					$query->where('flc.content = ' . $this->_db->quote((string)$xmlForm->id));						
					$query->where('pfl.fieldname = ' . $this->_db->quote((string)$xmlForm->id->attributes()->fieldname));
					$query->where('frm.state in (0,1)');

					$this->_db->setQuery($query);
					$result = $this->_db->loadResult();
					// If the alternate key was found use that form id						
					$formId = $result ? $result : 0;
				}
				else 
				{
					// verify that this article exists for the specified content type
					$formTemp = $this->getItem((int)$xmlForm->id);
					
					if($formTemp->projectid != $contentTypeId)
					{
						// there's no such form...create a new one
						$formId = 0;
					}
					else 
					{
						$formId = (int)$xmlForm->id;
					}
				}
			}

			// Check the state of the article
			switch((string)$xmlForm->state)
			{
				case 'deleted':
					$pks = array((int)$xmlForm->id);
					$this->delete($pks, true);
					$results['del'] += 1;
					break;
				case 'trashed':
					// Trash the article
					$pks = array((int)$xmlForm->id);
					$form->publish($pks, F2C_STATE_TRASH, true);
					$results['del'] += 1;
					break;
				case 'unpublished':
					$data['state'] = 0;
					break;
				case 'published':
					$data['state'] = 1;
					break;
			}
			
			if((string)$xmlForm->state == 'trashed' || (string)$xmlForm->state == 'deleted')
			{
				// stop further execution for this form
				break;	
			}
			
			if(count($xmlForm->attribs->children()))
			{
				foreach ($xmlForm->attribs->children() as $attribName => $xmlAttrib)
				{
					$attribs[$attribName] = (string)$xmlAttrib;
				}
			}
			
			if(count($xmlForm->metadata->children()))
			{
				foreach ($xmlForm->metadata->children() as $metadataName => $xmlMetadata)
				{
					$metadata[$metadataName] = (string)$xmlMetadata;
				}
			}
			
			$data['id'] 				= $formId;
			$data['projectid'] 			= $contentTypeId;
			$data['title'] 				= (string)$xmlForm->title;
			$data['alias'] 				= (string)$xmlForm->alias;
			$data['intro_template'] 	= (string)$xmlForm->intro_template;
			$data['main_template'] 		= (string)$xmlForm->main_template;	
			$data['catid'] 				= $this->dicCatAliasPath[(string)$xmlForm->cat_alias_path];
			
			if($formId)
			{
				// update of an existing form, maintain the created date
				$existingForm = $form->getItem($formId);
				$data['created'] = $existingForm->created;
			}
			else 
			{
				$data['created'] = $this->emptyOrIso8601DateToMySQL((string)$xmlForm->created);	
			}			
			
			$data['created_by'] 		= $this->resolveUsername((string)$xmlForm->created_by_username);
			$data['created_by_alias'] 	= (string)$xmlForm->created_by_alias;
			$data['modified'] 			= $this->emptyOrIso8601DateToMySQL((string)$xmlForm->modified);
			$data['publish_up'] 		= $this->emptyOrIso8601DateToMySQL((string)$xmlForm->publish_up);
			$data['publish_down'] 		= $this->emptyOrIso8601DateToMySQL((string)$xmlForm->publish_down);
			$data['metakey'] 			= (string)$xmlForm->metakey;
			$data['metadesc'] 			= (string)$xmlForm->metadesc;
			$data['access'] 			= (int)$this->dicViewingAccessLevelTitle[(string)$xmlForm->access];
			$data['language'] 			= (string)$xmlForm->language;
			$data['featured'] 			= ((string)$xmlForm->featured == "yes") ? 1 : 0;
			$data['attribs'] 			= $attribs;
			$data['metadata'] 			= $metadata;
			
			//$form->saveCron($data, true);

			$fieldDefinitions 	= $form->loadFieldDefinitions($contentTypeId);
			$existingFieldData	= $form->loadFieldData($formId, $contentTypeId);

			foreach($fieldDefinitions as $fieldDefinition)
			{
				$f2cFieldData 								= new F2cFieldData();
				$f2cFieldData->id 							= $fieldDefinition->id;
				$f2cFieldData->fieldtypeid 					= $fieldDefinition->fieldtypeid;
				$f2cFieldData->title 						= $fieldDefinition->title;
				$f2cFieldData->fieldname 					= $fieldDefinition->fieldname;
				$f2cFieldData->ordering 					= $fieldDefinition->ordering;
				$f2cFieldData->frontvisible 				= $fieldDefinition->frontvisible;				
				$f2cFieldData->settings 					= $fieldDefinition->settings;
				$f2cFieldData->projectid					= $fieldDefinition->projectid;
				$f2cFieldData->internal['fieldcontentid'] 	= null;				
				$fieldData[$f2cFieldData->fieldname] 		= $f2cFieldData;
				
				// create defaults
				switch($fieldDefinition->fieldtypeid)
				{
					case F2C_FIELDTYPE_IMAGE:
						$f2cFieldData->internal['delete']	= '';
						$f2cFieldData->internal['method']	= '';
      					$f2cFieldData->values['ALT'] 		= '';
      					$f2cFieldData->values['TITLE'] 		= '';
						break;
				}
			}

			if(count($xmlForm->fields->children()))
			{
				foreach($xmlForm->fields->children() as $xmlField)
				{
					$f2cField =& $fieldData[(string)$xmlField->fieldname];

					switch($f2cField->fieldtypeid)
					{
						case F2C_FIELDTYPE_CHECKBOX:
							$f2cField->values['VALUE'] = (string)$xmlField->contentBoolean->value;
							$f2cField->internal['fieldcontentid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
							break;
							
						case F2C_FIELDTYPE_DATEPICKER:
							$f2cField->values['VALUE'] = (string)$xmlField->contentDate->value;
							$f2cField->internal['fieldcontentid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
							break;
							
						case F2C_FIELDTYPE_DATABASE_LOOKUP:
						case F2C_FIELDTYPE_MULTILINEEDITOR:
						case F2C_FIELDTYPE_MULTILINETEXT:
						case F2C_FIELDTYPE_SINGLELINE:
						case F2C_FIELDTYPE_SINGLESELECTLIST:
							$f2cField->values['VALUE'] = (string)$xmlField->contentSingleTextValue->value;
							$f2cField->internal['fieldcontentid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
							break;
															
						case F2C_FIELDTYPE_DB_LOOKUP_MULTI:
						case F2C_FIELDTYPE_MULTISELECTLIST:
      						$f2cField->values['VALUE'] = array();
      						
      						if(count($xmlField->contentMultipleTextValue->values->children()))
      						{
      							foreach($xmlField->contentMultipleTextValue->values->children() as $xmlValue)
      							{
      								$f2cField->values['VALUE'][] = (string)$xmlValue;
      							}
      						}
							break;
							
						case F2C_FIELDTYPE_DISPLAYLIST:
      						$f2cField->values['VALUE'] = array();
      						
      						if(count($xmlField->contentMultipleTextValue->values->children()))
      						{
								$f2cField->internal['fieldcontentid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
      								      							
      							foreach($xmlField->contentMultipleTextValue->values->children() as $xmlValue)
      							{
      								$f2cField->values['VALUE'][] = (string)$xmlValue;
      							}
      						}
							break;
							
						case F2C_FIELDTYPE_EMAIL:
							$f2cField->values['EMAIL'] = (string)$xmlField->contentEmail->email;
							$f2cField->values['DISPLAY_AS'] = (string)$xmlField->contentEmail->display_as;
							$f2cField->internal['fieldcontentid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
							break;
							
						case F2C_FIELDTYPE_FILE:
      						$f2cField->values['FILENAME'] 			= (string)$xmlField->contentFileUpload->filename;
      						$f2cField->internal['fieldcontentid'] 	= $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
      						$f2cField->internal['method'] 			= 'copy';
      						$f2cField->internal['delete']			= (string)$xmlField->contentFileUpload->file == '' ? 1 : 0;
      						
      						switch((string)$xmlField->contentFileUpload->file->attributes()->includemode)
      						{
      							case 'url':
      								$f2cField->internal['filelocation'] = (string)$xmlField->contentFileUpload->file;
      								$f2cField->internal['method'] = 'remote';
      								break;
      							case 'path':
      								$f2cField->internal['filelocation'] = (string)$xmlField->contentFileUpload->file;
      								break;
      							case 'include':
	      							// encapsulated file
	      							$tmpFolder = Path::Combine($importTmpPath, 'c'.$contentTypeId.DS.'a'.$formId.DS.'f'.$f2cField->id);
	      							
	      							if(!JFolder::exists($tmpFolder))
	      							{
	      								JFolder::create($tmpFolder);
	      							}
	      							
	      							$tmpFile = Path::Combine($tmpFolder, $f2cField->values['FILENAME']);
	      							
	      							JFile::write($tmpFile, base64_decode((string)$xmlField->contentFileUpload->file));      							
	      							$f2cField->internal['filelocation'] = $tmpFile;
      								break;
      						}
      						break;
							
						case F2C_FIELDTYPE_GEOCODER:
							$f2cField->values['ADDRESS'] = (string)$xmlField->contentGeocoder->address;
							$f2cField->values['LAT'] = (string)$xmlField->contentGeocoder->lat;
							$f2cField->values['LON'] = (string)$xmlField->contentGeocoder->lon;
							$f2cField->internal['addressid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['addressid'] : 0;
							$f2cField->internal['latid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['latid'] : 0;
							$f2cField->internal['lonid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['lonid'] : 0;
							break;
							
						case F2C_FIELDTYPE_HYPERLINK:
							$f2cField->values['URL'] = (string)$xmlField->contentHyperlink->url;
							$f2cField->values['DISPLAY_AS'] = (string)$xmlField->contentHyperlink->display_as;
							$f2cField->values['TITLE'] = (string)$xmlField->contentHyperlink->title;
							$f2cField->values['TARGET'] = (string)$xmlField->contentHyperlink->target;
							$f2cField->internal['fieldcontentid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
							break;
							
						case F2C_FIELDTYPE_IMAGE:
      						$f2cField->values['FILENAME'] 			= (string)$xmlField->contentImage->filename;
      						$f2cField->values['ALT'] 				= (string)$xmlField->contentImage->alt;
      						$f2cField->values['TITLE'] 				= (string)$xmlField->contentImage->title;
      						$f2cField->values['WIDTH'] 				= (string)$xmlField->contentImage->width;
      						$f2cField->values['HEIGHT'] 			= (string)$xmlField->contentImage->height;
      						$f2cField->values['WIDTH_THUMBNAIL'] 	= (string)$xmlField->contentImage->width_thumbnail;
      						$f2cField->values['HEIGHT_THUMBNAIL'] 	= (string)$xmlField->contentImage->height_thumbnail;	      						
      						$f2cField->internal['fieldcontentid'] 	= $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
      						$f2cField->internal['method'] 			= 'copy';
      						$f2cField->internal['delete']			= (string)$xmlField->contentImage->image == '' ? 1 : 0;
      						
      						switch((string)$xmlField->contentImage->image->attributes()->includemode)
      						{
      							case 'url':
      								$f2cField->internal['imagelocation'] 	= (string)$xmlField->contentImage->image;
      								$f2cField->internal['thumblocation'] 	= (string)$xmlField->contentImage->thumbnail;
      								$f2cField->internal['method'] 			= 'remote';
      								break;
      							case 'path':
      								$f2cField->internal['imagelocation'] 	= (string)$xmlField->contentImage->image;
      								$f2cField->internal['thumblocation'] 	= (string)$xmlField->contentImage->thumbnail;
      								break;
      							case 'include':
	      							// encapsulated image
	      							$tmpFolder = Path::Combine($importTmpPath, 'c'.$contentTypeId.DS.'a'.$formId.DS.'f'.$f2cField->id);
	      							$tmpThumbs = Path::Combine($tmpFolder, 'thumbs');
	      							
	      							if(!JFolder::exists($tmpThumbs))
	      							{
	      								JFolder::create($tmpThumbs);
	      							}
	      							
	      							$tmpImage = Path::Combine($tmpFolder, $f2cField->values['FILENAME']);
	      							$tmpThumb = Path::Combine($tmpThumbs, $f2cField->values['FILENAME']);
	      							
	      							JFile::write($tmpImage, base64_decode((string)$xmlField->contentImage->image));
	      							JFile::write($tmpThumb, base64_decode((string)$xmlField->contentImage->thumbnail));
	      							 							
	      							$f2cField->internal['imagelocation'] = $tmpImage;
	      							$f2cField->internal['thumblocation'] = $tmpThumb;
      								break;
      						}
							break;
							
						case F2C_FIELDTYPE_IFRAME:
							$f2cField->values['URL'] = (string)$xmlField->contentIframe->url;
							$f2cField->values['WIDTH'] = (string)$xmlField->contentIframe->width;
							$f2cField->values['HEIGHT'] = (string)$xmlField->contentIframe->height;
							$f2cField->internal['fieldcontentid'] = $formId ? $existingFieldData[$f2cField->fieldname]->internal['fieldcontentid'] : 0;
							break;
							
						case F2C_FIELDTYPE_INFOTEXT:
							// This field does not need to be handled	
							break;
					}
				}
				
				$form->saveCron($data, true, $fieldData);
				
				if($formId)
				{
					$results['upd'] += 1;
				}
				else
				{
					$results['ins'] += 1;
				}
			}
		}
		
		// Import succeeded
		$results['res'] = 1;
		
		return $results;
	}
	
	/**
	 * Method to convert a date into the ISO 8601 format when it's not empty
	 * 
	 * @param	object		$date		The date to be formatted.
	 * @return	string		Date in ISO 8601 format or empty string.
	 * @since	4.6.0
	 */
	private function emptyOrIso8601DateToMySQL($date)
	{
		if($date)
		{
			$formattedDate = new JDate($date);
			return $formattedDate->toMySQL();
		}
		else 
		{
			return '';	
		}
	}
	
	/*
	 * Find the ID of a give username
	 */
	private function resolveUsername($username)
	{
		static $usernames = array();
		
		if(array_key_exists($username, $usernames))
		{
			return $usernames[$username];
		}
		else 
		{
			$userId = JUserHelper::getUserId($username);

			if($userId)
			{
				$usernames[$username] = $userId;
				return $userId;
			}
			else 
			{
				return 0;
			}
		}
	}
	
	/*
	 * Load the import XML file and validate it against the XML Schema file.
	 */
	private function loadImportFile($filename)
	{
		$domXml = new DOMDocument();
		$domXml->load($filename);
	
		libxml_use_internal_errors(true);

		$schemaTag = $domXml->documentElement->getAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation');
		
		if(!$schemaTag)
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_INVALID_XML_FILE'). ': '.$filename);
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_NO_SCHEMA_FILE'));
			return false;
		}
		
        $pairs = preg_split('/\s+/', $schemaTag);
        $pairCount = count($pairs);
        
        if ($pairCount <= 1)
        {
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_INVALID_XML_FILE'). ': '.$filename);
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_INVALID_SCHEMA_LOCATION'));
			return false;         
        }
		
      	$schemaLocation = JPATH_SITE.DS.'media'.DS.'com_form2content'.DS.'schemas'.DS.$pairs[1];
      	
      	if(!JFile::exists($schemaLocation))
      	{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_INVALID_XML_FILE'). ': '.$filename);
			$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_SCHEMA_NOT_FOUND'), $schemaLocation)); 
			return false;              		
      	}
      	
		if(!$domXml->schemaValidate($schemaLocation))
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_INVALID_XML_FILE'). ': '.$filename);
			
			foreach(libxml_get_errors() as $error)
			{
				$this->setError($error->message);
			}			
			
			return false;
		}
		
		// We're dealing with a valid XML file, convert it for further handling to a SimpleXml document
		return simplexml_import_dom($domXml);
	}
	
	/*
	 * Load various dictionaries to convert some values into IDs and vice versa
	 */
	protected function InitXmlImport()
	{
		$pathList = array();
		
		$query = $this->_db->getQuery(true);
		
		$query->select('a.id, a.alias, a.level');
		$query->from('#__categories AS a');
		$query->where('a.parent_id > 0');
		$query->where('extension = ' . $this->_db->quote('com_content'));
		$query->order('a.lft');
		
		$this->_db->setQuery($query);
		
		$categories = $this->_db->loadObjectList();

		if(count($categories))
		{
			foreach($categories as $category)
			{
				if($category->level == 1)
				{
					// reset pathlist
					$pathList = array();
					$pathList[0] = '';
				}
				
				$pathList[$category->level] 						= $pathList[$category->level - 1] . '/' . $category->alias;
				$this->dicCatAliasPath[$pathList[$category->level]]	= $category->id;
				$this->dicCatId[$category->id] 						= $pathList[$category->level];
			}
		}
		
		$query = $this->_db->getQuery(true);
		
		$query->select('id, title');
		$query->from('#__viewlevels');
		$this->_db->setQuery($query);
		
		$this->dicViewingAccessLevelId		= $this->_db->loadAssocList('id', 'title');
		$this->dicViewingAccessLevelTitle	= $this->_db->loadAssocList('title', 'id');
		
		// Load a dictionary of Content Types
		$query = $this->_db->getQuery(true);
		
		$query->select('id, title');
		$query->from('#__f2c_project');
		
		$this->_db->setQuery($query);

		$this->dicContentTypeTitle 	= $this->_db->loadAssocList('title', 'id');
		$this->dicContentTypeId 	= $this->_db->loadAssocList('id', 'title');
		
	}

	private function AddLogEntry($msg)
	{
		JFactory::getApplication()->enqueueMessage(JFactory::getDate()->format('c') . ';' . $msg);
	}
}
?>