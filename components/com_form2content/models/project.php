<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'class.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'shared.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'utils.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'models'.DS.'form.php');

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
		$configJoomla	=& JFactory::getConfig();
		$tzoffset 		= $configJoomla->get('config.offset');
		$dateNow		=& JFactory::getDate(null, $tzoffset); 
		$isNew			= empty($data['id']);

		if($isNew)
		{
			$user 				=& JFactory::getUser();
			$data['created_by']	= $user->id;		
			$data['created']	= $dateNow->toMySQL();
			
			if($configInfo = JApplicationHelper::parseXMLInstallFile(JPATH_COMPONENT.DS.'form2content.xml')) 
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
		if($isNew)
		{
			if(F2cFactory::getConfig()->get('generate_sample_template'))
			{
				F2C_AdminHelper::generateSampleTemplate($data['id']);
				$data['settings']['intro_template'] = 'default_intro_template_' . JFile::makeSafe($data['title']) . '.tpl';
				$data['settings']['main_template']  = 'default_main_template_' . JFile::makeSafe($data['title']) . '.tpl';

				if(!parent::save($data))
				{
					return false;
				}
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
				$this->setError($formTable->getError());
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
}
?>