<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DS.'utils.form2content.php');
require_once(JPATH_COMPONENT_SITE.DS.'shared.form2content.php');
require_once(JPATH_COMPONENT_SITE.DS.'class.form2content.php');

jimport('joomla.application.component.modeladmin');

class Form2ContentModelProjectField extends JModelAdmin
{
	protected $text_prefix = 'COM_FORM2CONTENT';

	public function getTable($type = 'ProjectField', $prefix = 'Form2ContentTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) 
		{
			// Convert the settings field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->settings);			
			$item->settings = $registry->toArray();
			
			if(!$item->projectid)
			{
				$item->projectid = JRequest::getInt('projectid');
			}
		}

		return $item;
	}
	
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_form2content.projectfield', 'projectfield', array('control' => 'jform', 'load_data' => $loadData));
		
		if ($id = (int)$this->getState('projectfield.id')) 
		{
			// Existing record. Can't change field type anymore'.
			$form->setFieldAttribute('fieldtypeid', 'readonly', 'true');
		}
		
		if (empty($form)) 
		{
			return false;
		}

		return $form;
	}	

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_form2content.edit.projectfield.data', array());

		if (empty($data)) 
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function save($data, $useRequestData = true)
	{
		$fieldPrefix = array(	F2C_FIELDTYPE_SINGLELINE => 'slt',
								F2C_FIELDTYPE_MULTILINETEXT => 'mlt',
								F2C_FIELDTYPE_MULTILINEEDITOR => 'mle',
								F2C_FIELDTYPE_CHECKBOX => 'chk',
								F2C_FIELDTYPE_SINGLESELECTLIST => 'ssl', 
								F2C_FIELDTYPE_IMAGE => 'img',
								F2C_FIELDTYPE_IFRAME => 'ifr', 
								F2C_FIELDTYPE_EMAIL => 'eml', 
								F2C_FIELDTYPE_HYPERLINK => 'lnk', 
								F2C_FIELDTYPE_MULTISELECTLIST => 'msl', 
								F2C_FIELDTYPE_INFOTEXT => 'inf', 
								F2C_FIELDTYPE_DATEPICKER => 'dat', 
								F2C_FIELDTYPE_DISPLAYLIST => 'dsp', 
								F2C_FIELDTYPE_FILE => 'ful', 
								F2C_FIELDTYPE_DATABASE_LOOKUP => 'dbl', 
								F2C_FIELDTYPE_GEOCODER => 'gcd',
								F2C_FIELDTYPE_DB_LOOKUP_MULTI => 'dlm');

		$settingsCount = count($data['settings']);
				
		foreach($data['settings'] as $key => $value)
		{
			// TODO: why doesn't $key == 'requiredfield' work?
			if(strstr($key, 'requiredfield') || $key == 'error_message_required')
			{
				continue;
			}
			
			if(!(strpos($key, $fieldPrefix[(int)$data['fieldtypeid']]) === 0))
			{
				unset($data['settings'][$key]);
			}
		}

		switch((int)$data['fieldtypeid'])
		{
			case F2C_FIELDTYPE_INFOTEXT:
				if($useRequestData)
				{
					$tmpData = JRequest::getVar('jform', array(), 'post', 'array');
					
					if(count($tmpData))
					{
						$data['settings']['inf_text'] = $tmpData['settings']['inf_text'];
					}
				}				
				break;
				
			case F2C_FIELDTYPE_MULTILINETEXT:
				if((int)$data['settings']['mlt_max_num_chars'] == 0)
				{
					$data['settings']['mlt_max_num_chars'] = '';
				}
				break;
				
			case F2C_FIELDTYPE_SINGLESELECTLIST:
				if($useRequestData)
				{
					$data['settings']['ssl_options'] = $this->getOptionsArray('tblSingleSelectKvpRowKey', true);
				}
				break;
			
			case F2C_FIELDTYPE_MULTISELECTLIST:
				if($useRequestData)
				{
					$data['settings']['msl_options'] 			= $this->getOptionsArray('tblMultiSelectKvpRowKey', true);
					$data['settings']['msl_pre_list_tag'] 		= $_POST['jform']['settings']['msl_pre_list_tag'];
					$data['settings']['msl_post_list_tag'] 		= $_POST['jform']['settings']['msl_post_list_tag'];
					$data['settings']['msl_pre_element_tag'] 	= $_POST['jform']['settings']['msl_pre_element_tag'];					
					$data['settings']['msl_post_element_tag'] 	= $_POST['jform']['settings']['msl_post_element_tag'];
				}
				break;
				
			case F2C_FIELDTYPE_DATABASE_LOOKUP:
				if($useRequestData)
				{
					$tmpData = JRequest::getVar('jform', array(), 'post', 'array');
					$data['settings']['dbl_query'] = $tmpData['settings']['dbl_query'];
				}
				break;
				
			case F2C_FIELDTYPE_FILE:
				if($useRequestData)
				{	
					$data['settings']['ful_whitelist'] = $this->getOptionsArray('tblFileWhiteListRowKey');
					$data['settings']['ful_blacklist'] = $this->getOptionsArray('tblFileBlackListRowKey');
				}
				break;
			case F2C_FIELDTYPE_DB_LOOKUP_MULTI:
				if($useRequestData)
				{
					$tmpData = JRequest::getVar('jform', array(), 'post', 'array');
					$data['settings']['dlm_query'] = $tmpData['settings']['dlm_query'];
				}
				break;
		}
		
		if(parent::save($data))
		{
			if(F2cFactory::getConfig()->get('generate_sample_template'))
			{
				F2C_AdminHelper::generateSampleTemplate((int)$data['projectid']);
			}			
		}
		else
		{
			return false;
		}	
		
		return true;
	}
	
	public function copy()
	{
		$cids 					= JRequest::getVar('cid', array(0), 'post', 'array' );
		$contentTypeId 			= JRequest::getInt('projectid');
		$contentTypeFieldRow 	=& $this->getTable('ProjectField');
		
		if(count($cids))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
	
			$query->select('*');
			$query->from('`#__f2c_projectfields`');
			$query->where('id IN (' . implode(',', $cids) . ')');
			
			$db->setQuery($query->__toString());	
			$contentTypeFields = $db->loadObjectList();
			
			foreach($contentTypeFields as $contentTypeField)
			{
				if (!$contentTypeFieldRow->bind((array)$contentTypeField)) 
				{
					$this->setError($contentTypeFieldRow->getError());
					return false;
				}

				$contentTypeFieldRow->id 		= 0; // force insert
				$contentTypeFieldRow->fieldname = $contentTypeFieldRow->fieldname . '_copy';
				$contentTypeFieldRow->projectid = $contentTypeId;
				$contentTypeFieldRow->ordering 	= 0;
			
				if(!$contentTypeFieldRow->store())
				{
					$this->setError($contentTypeFieldRow->getError());
					return false;
				}								
			}
			
			if(F2cFactory::getConfig()->get('generate_sample_template'))
			{
				F2C_AdminHelper::generateSampleTemplate((int)$contentTypeId);
			}			
			
			$cache = & JFactory::getCache('com_form2content');
			$cache->clean();			
		}
		
		return true;		
	}
	
	protected function prepareTable($table)
	{
		// Reorder the Content Type fields within the Content Type so the new article is Content Type field
		if (empty($table->id)) 
		{
			$table->reorder('projectid = '.(int) $table->projectid);
		}
	}
	
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'projectid = '.(int) $table->projectid;
		return $condition;
	}
	
	private function getOptionsArray($tableId, $hasValue = false)
	{
		$options = array();
		
		$rowKeys = JRequest::getVar($tableId, array(), 'post', 'array');
		
		if(count($rowKeys))
		{
			foreach($rowKeys as $rowKey)
			{
				$key = JRequest::getString($rowKey . 'key');

				if($key != '')
				{
					if(!$options || !array_key_exists($key, $options))
					{	
						if($hasValue)
						{
							$options[$key] = JRequest::getVar($rowKey . 'val', '', 'post', 'string', JREQUEST_ALLOWRAW);							
						}
						else
						{
							$options[$key] = $key;
						}
					}
				}						
			}
		}

		return $options;		
	}
	
	public function delete(&$pks)
	{
		// Initialise variables.
		$user			=& JFactory::getUser();
		$f2cConfig 		=& F2cFactory::getConfig();
		$pks			= (array)$pks;
		$table			= $this->getTable();
		$contentTypeId	= -1;
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) 
		{
			if ($table->load($pk)) 
			{
				if ($this->canDelete($table)) 
				{
					$context = $this->option.'.'.$this->name;

					if($table->fieldtypeid == F2C_FIELDTYPE_IMAGE)
					{
						F2C_Image::deleteContentTypeFieldImages($pk);	
					}

					if($table->fieldtypeid == F2C_FIELDTYPE_FILE)
					{
						F2C_FileUpload::deleteContentTypeFieldFiles($pk);	
					}
					
					if($f2cConfig->get('generate_sample_template'))
					{
						$contentTypeId = $table->projectid;
					}			

					$query = $this->_db->getQuery(true);
	
					$query->delete('#__f2c_translation');
					$query->where('reference_id=' . (int)$pk);
					
					$this->_db->setQuery($query->__toString());
						
					if(!$this->_db->query())
					{
						$this->setError($this->_db->getError());
						return false;
					}					
					
					$query = $this->_db->getQuery(true);
	
					$query->delete('#__f2c_fieldcontent');
					$query->where('fieldid=' . (int)$pk);
					
					$this->_db->setQuery($query->__toString());
						
					if(!$this->_db->query())
					{
						$this->setError($this->_db->getError());
						return false;
					}					
					
					if (!$table->delete($pk)) 
					{
						$this->setError($table->getError());
						return false;
					}
					
					if($f2cConfig->get('generate_sample_template'))
					{
						F2C_AdminHelper::generateSampleTemplate((int)$contentTypeId);
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
				$this->setError($table->getError());
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