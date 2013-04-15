<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'class.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'shared.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'utils.form2content.php');

if(JFactory::getApplication()->isAdmin())
{
	require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_form2content'.DS.'models'.DS.'form.php');
}
else 
{
	require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'models'.DS.'form.php');	
}

jimport('joomla.application.component.modeladmin');

class Form2ContentModelProject extends JModelAdmin
{
	protected $text_prefix = 'COM_FORM2CONTENT';

	public function getTable($type = 'Project', $prefix = 'Form2ContentTable', $config = array())
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

			// Convert the settings field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->settings);			
			$item->settings = $registry->toArray();
		}
		
		return $item;
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_form2content.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form)) 
		{
			return false;
		}

		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_form2content.edit.project.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data)
	{
		$jConfig	=& JFactory::getConfig();
		$tzoffset 	= $jConfig->get('config.offset');
		$dateNow	=& JFactory::getDate(null, $tzoffset); 
		$isNew		= empty($data['id']);

		if($isNew)
		{
			$user 				=& JFactory::getUser();
			$data['created_by']	= $user->id;		
			$data['created']	= $dateNow->toMySQL();
			
			if($configInfo = JApplicationHelper::parseXMLInstallFile(JPATH_COMPONENT.DS.'manifest.xml')) 
			{
				$data['version'] = $configInfo['version'];
			}			
		}

		$data['modified'] = $dateNow->toMySQL();
				
		if(!parent::save($data))
		{
			return false;
		}
		
		$data['id'] = $this->getState('project.id');
		
		// check if we need to generate a default template
		if($isNew && F2cFactory::getConfig()->get('generate_sample_template'))
		{
			F2C_AdminHelper::generateSampleTemplate($data['id']);
			$data['settings']['intro_template'] = 'default_intro_template_' . JFile::makeSafe($data['title']) . '.tpl';
			$data['settings']['main_template']  = 'default_main_template_' . JFile::makeSafe($data['title']) . '.tpl';

			if(!parent::save($data))
			{
				return false;
			}
		}
		
		return true;
	}

	public function syncJoomlaAdvancedParms($id)
	{
		$query = 	'UPDATE #__f2c_form frm ';
		$query .= 	'INNER JOIN #__f2c_project prj ON frm.projectid = prj.id AND prj.id = ' . (int)$id . ' ';
		$query .=	'SET frm.attribs = prj.attribs';

		$this->_db->setQuery($query);
		
		if(!$this->_db->query())
		{			
			$this->setError($this->_db->getErrorMsg());
			return false; 
		}

		return true;
	}
	
	function syncMetadata($id)
	{
		$sql = 	'UPDATE #__f2c_form frm ' .
				'INNER JOIN #__f2c_project prj ON frm.projectid = prj.id AND prj.id = ' . (int)$id . ' ' . 
				'SET frm.metadata = prj.metadata, frm.metakey = prj.metakey, frm.metadesc = prj.metadesc';

		$this->_db->setQuery($sql);
		
		if(!$this->_db->query())
		{			
			$this->setError($this->_db->getErrorMsg());
			return false; 
		}
		else
		{
			return true;
		}
	}
	
	public function copy(&$pks)
	{
		$contentTypeTable		=& $this->getTable(); 				
		$contentTypeFieldRow	= JTable::getInstance('ProjectField','Form2ContentTable'); 	
		$dateNow 				=& JFactory::getDate();
		$timestamp 				= $dateNow->toMySQL();
		
		foreach ($pks as $i => $pk)
		{
			if(!$contentTypeTable->load($pk))
			{
				$this->setError($contentTypeTable->getError());
				return false;
			}
			
			$contentTypeTable->title 	= JText::_('COM_FORM2CONTENT_COPY_OF') . ' ' . $contentTypeTable->title;
			$contentTypeTable->id 		= null; // force insert
			$contentTypeTable->asset_id = null; // force insert
			$contentTypeTable->created 	= $timestamp;
			$contentTypeTable->modified = $this->_db->getNullDate();
			
			if(!$contentTypeTable->store())
			{
				$this->setError($contentTypeTable->getError());
				return false;
			}
			
			// copy the ContentType Fields
			$query = $this->_db->getQuery(true);
			$query->select('*');
			$query->from('#__f2c_projectfields');
			$query->where('projectid = ' . (int)$pk);
			
			$this->_db->setQuery($query->__toString());
			
			$contentTypeFields = $this->_db->loadAssocList();

			if(count($contentTypeFields))
			{
				foreach($contentTypeFields as $contentTypeField)
				{
					if (!$contentTypeFieldRow->bind($contentTypeField)) 
					{
						$this->setError($this->_db->getErrorMsg());
						return false;
					}

					$contentTypeFieldRow->id = 0; // force insert
					$contentTypeFieldRow->projectid = $contentTypeTable->id;
				
					if(!$contentTypeFieldRow->store())
					{
						$this->setError($contentTypeFieldRow->getError());
						return false;
					}
					
					// Inserting new Content Type fields generated new ordering
					// Resave the field with the original ordering
					$contentTypeFieldRow->ordering = $contentTypeField['ordering'];
					
					if(!$contentTypeFieldRow->store())
					{
						$this->setError($contentTypeFieldRow->getError());
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	public function delete(&$pks)
	{
		// Initialise variables.
		$dispatcher			= JDispatcher::getInstance();
		$pks				= (array)$pks;
		$context 			= $this->option.'.'.$this->name;
		$modelForm			= new Form2ContentModelForm();
		$contentTypeTable	=& $this->getTable();
		
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('form2content');
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) 
		{
			if($contentTypeTable->load($pk)) 
			{
				// Get the list of forms for this Content Type
				$query = $this->_db->getQuery(true);
				$query->select('id');
				$query->from('#__f2c_form');
				$query->where('projectid = ' . (int)$pk);
				
				$this->_db->setQuery($query->__toString());
				
				$formIds = $this->_db->loadResultArray();
				
				if(!$modelForm->delete($formIds))
				{
					$this->setError($modelForm->getError());
					return false;
				}
				
				// remove the base image dir
				if(JFolder::exists(Path::Combine(F2C_Image::GetImagesRootPath(), "p$pk")))
				{
					JFolder::delete(Path::Combine(F2C_Image::GetImagesRootPath(), "p$pk"));
				}
				
				// remove the base file dir
				if(JFolder::exists(Path::Combine(F2C_FileUpload::GetFilesRootPath(), "c$pk")))
				{
					JFolder::delete(Path::Combine(F2C_FileUpload::GetFilesRootPath(), "c$pk"));
				}
				
				// Delete the translations
				$this->_db->setQuery('DELETE tra.* FROM #__f2c_translation tra ' . 
									 'INNER JOIN #__f2c_projectfields pfl ON pfl.id = tra.reference_id ' .
									 'WHERE pfl.projectid ='.(int)$pk);
				
				if(!$this->_db->query())
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				
				// Delete the Content Type Field definitions
				$query = $this->_db->getQuery(true);
				$query->delete('#__f2c_projectfields');
				$query->where('projectid = ' . (int)$pk);
				
				$this->_db->setQuery($query->__toString());
				
				if(!$this->_db->query())
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
	
				// Delete the Content Type			
				if (!$contentTypeTable->delete($pk)) 
				{
					$this->setError($contentTypeTable->getError());
					return false;
				}
			}
			else
			{
				$this->setError($contentTypeTable->getError());
				return false;
			}						
		}

		// Clear the component's cache
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		return true;
	}
	
	function export()
	{ 
	}
	
	function upload()
	{
		require_once(JPATH_COMPONENT.DS.'models'.DS.'projectfield.php');
				
		$file 				= JRequest::getVar('upload', '', 'files', 'array');
		$f2cConfig 			=& F2cFactory::getConfig();
		$contentTypeData 	= array();
		$data				= array();
		
		if(!$xml = JFactory::getXML($file['tmp_name'], true))
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_NO_CONTENTTYPE'));
			return false;
		}

		$contentTypeTitle 	= $xml->title;
		$version 			= $xml->version;	
		$nodeSettings		=& $xml->settings;
		$introTemplate 		= $nodeSettings->intro_template;
		$mainTemplate 		= $nodeSettings->main_template;
		
		// Check if the version of the component is equal or higher
		// to the version of the imported Content Type
		$versionCheck = false;
		
		if(!$version)
		{
			$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_INCOMPATIBLE_VERSION'), $componentVersion, $version));
			return false;
		}
		
		list($importMajor, $importMinor, $importRevision) = explode('.', $version);
		
		$componentInfo = JApplicationHelper::parseXMLInstallFile(JPATH_COMPONENT.DS.'manifest.xml');
		$componentVersion = $componentInfo['version'];
		list($compMajor, $compMinor, $compRevision) = explode('.', $componentVersion);
		
		// Major versions must be the same
		if((int)$compMajor != (int)$importMajor)
		{
			$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_INCOMPATIBLE_VERSION'), $componentVersion, $version));
			return false;
		}
		
		if((int)$compMinor > (int)$importMinor)
		{
			$versionCheck = true;
		}
		else if(((int)$compMinor == (int)$importMinor) &&
				((int)$compRevision >= (int)$importRevision))
		{
			$versionCheck = true;
		}
				
		if(!$versionCheck)
		{
			$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_VERSION_TOO_LOW'), $componentVersion, $version));
			return false;
		}
		
		// Check if the Content Type doesn't exist yet
		$query = $this->_db->getQuery(true);
		$query->select('count(*)');
		$query->from('#__f2c_project');
		$query->where('title = ' . $this->_db->quote($contentTypeTitle));
		
		$this->_db->setQuery($query->__toString());
				
		if($this->_db->loadResult())
		{
			$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_EXISTS'), $contentTypeTitle));
			return false;			
		}
		
		// Check if the templates don't exist yet
      	$introTemplateFile = Path::Combine($f2cConfig->get('template_path'), $introTemplate);

      	if(JFile::exists($introTemplateFile))
      	{
			$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_TEMPLATE_EXISTS'), $introTemplate));
			return false;			
      	}
		
      	$mainTemplateFile = Path::Combine($f2cConfig->get('template_path'), $mainTemplate);
      	
      	if(JFile::exists($mainTemplateFile))
      	{
			$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_IMPORT_CONTENTTYPE_TEMPLATE_EXISTS'), $mainTemplate));
			return false;			
      	}

		$data['title'] 		= $xml->title->data();
		$data['id'] 		= null; // force insert
		$data['asset_id'] 	= null; // force insert
		$data['published']	= $xml->published->data();
		$data['metakey']	= $xml->metakey->data();
		$data['metadesc']	= $xml->metadesc->data();
				
		$settings = array();
		$attribs = array();
		$metadata = array();
		
		$this->xmlToArray($xml->settings, $settings);
		$this->xmlToArray($xml->attribs, $attribs);
		$this->xmlToArray($xml->metadata, $metadata);
		
		$data['settings'] = $settings;
		$data['attribs'] = $attribs;
		$data['metadata'] = $metadata;
				
		if(!$this->save($data))
		{
			return false;
		}	
		
		$contentTypeId =  $this->getState('project.id');
		
      	if($xml->fields->children())
      	{
      		foreach($xml->fields->children() as $field)
      		{
				$fld 						= new Form2ContentModelProjectField();				
				$fldSettings 				= array();
				
				$this->xmlToArray($field->settings, $fldSettings);

				$fldData 					= array();
				$fldData['projectid'] 		= $contentTypeId;
				$fldData['fieldname'] 		= $field->fieldname->data();
				$fldData['title'] 			= $field->title->data();
				$fldData['description'] 	= $field->description->data();
				$fldData['frontvisible']	= $field->frontvisible->data();
				$fldData['fieldtypeid'] 	= $field->fieldtypeid->data();
				$fldData['settings']		= $fldSettings;
				
				$fld->save($fldData, false);
      		}
      	}
		
		// Write the template files
		JFile::write($introTemplateFile, $xml->introtemplatefile->data());
		JFile::write($mainTemplateFile, $xml->maintemplatefile->data());
		
		return true;
	}	
	
	function getFieldDefinitions($contentTypeId)
	{
		$query = $this->_db->getQuery(true);
		$query->select('*');
		$query->from('#__f2c_projectfields');
		$query->where('projectid = ' . (int)$contentTypeId);
		$query->order('ordering ASC');
		
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
	
	function xmlToArray($node, &$array)
	{
		if(count($node->children()))
		{
			foreach($node->children() as $elementName => $child)
			{
				if($child->children())
				{
					if($elementName != 'arrayelement')
					{
						$childData = array();
						self::xmlToArray($child, $childData);
						$array[$elementName] = $childData;
					}
					else 
					{
						$array[$child->key->data()] = $child->value->data();
					}					
				}
				else
				{
					$array[$elementName] = $child->data();
				}
			}
		}
	}	
	
	function createSampleFormTemplate($id, $overwrite = 0)
	{
		$template 			= '';
		$contentType 		= $this->getItem($id); 
		$contentTypeFields 	= $this->getFieldDefinitions($id);
		$templateName 		= 'default_form_template_'.$contentType->title . '.tpl';
		$filename 			= Path::Combine(F2cFactory::getConfig()->get('template_path'), $templateName);
		
		if(JFile::exists($filename) && !$overwrite)
		{
			return '1;'.$templateName;
		}
		
		$buttons = '<table style="width:100%;">
					<tr class="f2c_buttons">
						<td><div style="float: right;">{$F2C_BUTTON_SAVE}{$F2C_BUTTON_APPLY}{$F2C_BUTTON_CANCEL}</div></td>
					</tr>
					</table>';
		
		$template .= $buttons;
		$template .= '<div class="width-60 fltlft"><fieldset class="adminform"><table class="adminform" width="100%">'.PHP_EOL;

		if($contentType->settings['id_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_id"><td valign="top" class="f2c_field_label">{$F2C_ID_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_ID}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['title_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_title"><td valign="top" class="f2c_field_label">{$F2C_TITLE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_TITLE}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['title_alias_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_title_alias"><td valign="top" class="f2c_field_label">{$F2C_TITLE_ALIAS_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_TITLE_ALIAS}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['metadesc_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_metadesc"><td valign="top" class="f2c_field_label">{$F2C_METADESC_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_METADESC}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['metakey_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_metakey"><td valign="top" class="f2c_field_label">{$F2C_METAKEY_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_METAKEY}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['frontend_catsel'])
		{
			$template .= '<tr class="f2c_field f2c_catid"><td valign="top" class="f2c_field_label">{$F2C_CATID_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_CATID}</td></tr>'.PHP_EOL;
		}	
		if($contentType->settings['author_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_created_by"><td valign="top" class="f2c_field_label">{$F2C_CREATED_BY_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_CREATED_BY}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['author_alias_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_created_by_alias"><td valign="top" class="f2c_field_label">{$F2C_CREATED_BY_ALIAS_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_CREATED_BY_ALIAS}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['access_level_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_access"><td valign="top" class="f2c_field_label">{$F2C_ACCESS_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_ACCESS}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['frontend_templsel'])
		{
			$template .= '<tr class="f2c_field f2c_intro_template"><td valign="top" class="f2c_field_label">{$F2C_INTRO_TEMPLATE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_INTRO_TEMPLATE}</td></tr>'.PHP_EOL;
			$template .= '<tr class="f2c_field f2c_main_template"><td valign="top" class="f2c_field_label">{$F2C_MAIN_TEMPLATE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_MAIN_TEMPLATE}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['date_created_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_created"><td valign="top" class="f2c_field_label">{$F2C_CREATED_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_CREATED}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['frontend_pubsel'])
		{
			$template .= '<tr class="f2c_field f2c_publish_up"><td valign="top" class="f2c_field_label">{$F2C_PUBLISH_UP_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_PUBLISH_UP}</td></tr>'.PHP_EOL;
			$template .= '<tr class="f2c_field f2c_publish_down"><td valign="top" class="f2c_field_label">{$F2C_PUBLISH_DOWN_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_PUBLISH_DOWN}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['state_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_state"><td valign="top" class="f2c_field_label">{$F2C_STATE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_STATE}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['language_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_language"><td valign="top" class="f2c_field_label">{$F2C_LANGUAGE_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_LANGUAGE}</td></tr>'.PHP_EOL;
		}
		if($contentType->settings['featured_front_end'])
		{
			$template .= '<tr class="f2c_field f2c_featured"><td valign="top" class="f2c_field_label">{$F2C_FEATURED_CAPTION}</td><td valign="top" class="f2c_field_value">{$F2C_FEATURED}</td></tr>'.PHP_EOL;
		}
		
		if(count($contentTypeFields))
		{
			foreach($contentTypeFields as $contentTypeField)
			{
				if($contentTypeField->frontvisible)
				{
					$fieldname = strtoupper($contentTypeField->fieldname);
					$template .= '<tr class="f2c_field"><td width="100" align="left" class="key f2c_field_label" valign="top">{$'.$fieldname.'_CAPTION}</td><td valign="top" class="f2c_field_value">{$'.$fieldname.'}</td></tr>'.PHP_EOL;
				}
			}
		}
		
		$template .= '</table></fieldset>{$F2C_CAPTCHA}</div><div class="clr"></div>'.PHP_EOL;			
		$template .= $buttons;
		
		JFile::write($filename, $template);
		
		return '0;'.$templateName;
		
	}
}
?>