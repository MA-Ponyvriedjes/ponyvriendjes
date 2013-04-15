<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'shared.form2content.php');

class F2C_Validation
{
	function createDatePickerValidation($fieldId, $fieldLabel, $format, $displayFormat, $userField = true)
	{
		$script = 'if(!F2C_ValDateField(\''.$fieldId.'\', \''.$format.'\'))';
		$script .= '{ ';
		$script .= 'alert(\'' . sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE', true), $fieldLabel, $displayFormat) . '\'); ';
		$script .= 'return false; }';

		return $script;
	}
	
	function createFileUploadClientSideScript($field)
	{
		$whiteList 		= 'new Array()';
		$blackList 		= 'new Array()';
		
		if(count($field->settings->get('ful_whitelist')))
		{
			$whiteList = 'new Array(\'' . implode('\',\'', (array)$field->settings->get('ful_whitelist')) .'\')';			
		}

		if(count($field->settings->get('ful_blacklist')))
		{
			$blackList = 'new Array(\'' . implode('\',\'', (array)$field->settings->get('ful_blacklist')) .'\')';			
		}
		
		$msgWhiteList = JText::_('FIELD') . ' \\\'' . HtmlHelper::stringHTMLSafe($field->title) . '\\\': ' . JText::_('COM_FORM2CONTENT_ERROR_FILE_UPLOAD_EXTENSION_NOT_ALLOWED');
		$msgBlackList = JText::_('FIELD') . ' \\\'' . HtmlHelper::stringHTMLSafe($field->title) . '\\\': ' . JText::_('COM_FORM2CONTENT_ERROR_FILE_UPLOAD_EXTENSION_NOT_ALLOWED');
		return 'if (!F2C_checkExtension(\'t'. $field->id . '_fileupload\', ' . $whiteList . ','. $blackList . ',\''.$msgWhiteList.'\',\''.$msgBlackList.'\')) return false; ';	
	}
	
	function valReqField($field, $elementName, $data)
	{
		$F2C_FIELD_FUNCTION_MAPPING = array(F2C_FIELDTYPE_SINGLELINE => 'SingleLineText',
									F2C_FIELDTYPE_MULTILINETEXT => 'MultiLineText',
									F2C_FIELDTYPE_MULTILINEEDITOR => 'MultiLineEditor',
									F2C_FIELDTYPE_CHECKBOX => 'CheckBox',
									F2C_FIELDTYPE_SINGLESELECTLIST => 'SingleSelectList', 
									F2C_FIELDTYPE_IMAGE => 'Image',
									F2C_FIELDTYPE_IFRAME => 'IFrame', 
									F2C_FIELDTYPE_EMAIL => 'Email', 
									F2C_FIELDTYPE_HYPERLINK => 'Hyperlink', 
									F2C_FIELDTYPE_MULTISELECTLIST => 'MultiSelectList', 
									F2C_FIELDTYPE_INFOTEXT => 'InfoText', 
									F2C_FIELDTYPE_DATEPICKER => 'DatePicker', 
									F2C_FIELDTYPE_DISPLAYLIST => 'DisplayList', 
									F2C_FIELDTYPE_FILE => 'FileUpload', 
									F2C_FIELDTYPE_DATABASE_LOOKUP => 'DatabaseLookup', 
									F2C_FIELDTYPE_GEOCODER => 'GeoCoder',
									F2C_FIELDTYPE_DB_LOOKUP_MULTI => 'DatabaseLookupMulti');

		$functionName = '_valReq'.$F2C_FIELD_FUNCTION_MAPPING[$field->fieldtypeid];

		return $this->$functionName($elementName, $data);
	}
	
	function _valReqSingleLineText($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getVar($elementName, '', 'post', 'string', JREQUEST_ALLOWRAW));
		return (trim($content) != '');
	}
	
	function _valReqMultiLineText($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getVar($elementName, '', 'post', 'string', JREQUEST_ALLOWRAW));
		return (trim($content) != '');
	}
	
	function _valReqMultiLineEditor($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getVar($elementName, '', 'post', 'string', JREQUEST_ALLOWRAW));
		return (trim($content) != '');
	}

	function _valReqCheckBox($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getString($elementName));
		return $content;
	}
	
	function _valReqSingleSelectList($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getString($elementName));
		return ($content != '');
	}
	
	function _valReqImage($elementName, $data)
	{
		$uploadfile = JRequest::getVar($elementName . '_fileupload', '', 'files', 'array');
		
		if(JRequest::getVar($elementName . '_del')) return false;
		if($uploadfile['size']) return true;
		
		// check if an image exists
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select('content');
		$query->from('#__f2c_fieldcontent');
		$query->where('id='.JRequest::getInt('hid'.$elementName));
		
		$db->setQuery($query->__toString());
		
		$content = $db->loadResult();
		
		if($content)
		{
			$imageData = new JRegistry();
			$imageData->loadString($content);
			return ($imageData->get('filename') != '');
		}

		return false;		
	}
	
	function _valReqIFrame($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getString($elementName));
		return (trim($content) != '');
	}
	
	function _valReqEmail($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getString($elementName));
		return (trim($content) != '');
	}
	
	function _valReqHyperlink($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getString($elementName));
		return (trim($content) != '');
	}
	
	function _valReqMultiSelectList($elementName, $data)
	{
		$selections = JRequest::getVar($elementName);
					
		if($selections)
		{
			foreach($selections as $value)
			{
				if(trim($value) != '') return true;
			}
		}
	
		return false;
	}
	
	function _valReqDatePicker($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getString($elementName));
		return (trim($content) != '');
	}
	
	function _valReqDisplayList($elementName, $data)
	{
		$listNew = null;
		
		if(array_key_exists($elementName.'RowKey', $_POST) && count($_POST[$elementName.'RowKey']))
		{
			foreach($_POST[$elementName.'RowKey'] as $rowKey)
			{ 
				// fix up special html field
				$val = JRequest::getVar($rowKey.'val', '', 'post', 'string', JREQUEST_ALLOWRAW);	
				if($val != '') return true;
			}
		}

		return false;		
	}
	
	function _valReqFileUpload($elementName, $data)
	{
		$uploadfile = JRequest::getVar($elementName . '_fileupload', '', 'files', 'array');
		
		if(JRequest::getVar($elementName . '_del')) return false;
		if($uploadfile['size']) return true;
		
		// check if a file exists
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select('content');
		$query->from('#__f2c_fieldcontent');
		$query->where('id='.JRequest::getInt('hid'.$elementName));
		
		$db->setQuery($query->__toString());

		$content = $db->loadResult();
		
		return($content);
	}
	
	function _valReqDatabaseLookup($elementName, $data)
	{
		$content = HtmlHelper::unquoteData(JRequest::getString($elementName));
		return ($content != '');
	}
	
	function _valReqGeoCoder($elementName, $data)
	{
		$addressValue 	= HtmlHelper::unquoteData(JRequest::getString($elementName.'_address'));
		$latValue		= HtmlHelper::unquoteData(JRequest::getString($elementName.'_hid_lat'));
		$lonValue 		= HtmlHelper::unquoteData(JRequest::getString($elementName.'_hid_lon'));
		return(trim($addressValue) && $latValue && $lonValue);		
	}
	
	function _valReqDatabaseLookupMulti($elementName, $data)
	{
		$listNew = null;
	
		if(array_key_exists($elementName.'RowKey',$_POST) && count($_POST[$elementName.'RowKey']))
		{
			foreach($_POST[$elementName.'RowKey'] as $rowKey)
			{ 
				// fix up special html field
				$val = JRequest::getVar($rowKey.'val', '', 'post', 'string', JREQUEST_ALLOWRAW);	
				if($val != '') return true;
			}
		}

		return false;		
	}
	
	function valSizeImage($field)
	{
		$f2cConfig	=& F2cFactory::getConfig();
		$uploadfile = JRequest::getVar('t'.$field->id.'_fileupload', '', 'files', 'array');
		
		if(array_key_exists('size', $uploadfile) && $uploadfile['size'])
		{
			$maxImageUploadSize = (int)$f2cConfig->get('max_image_upload_size');
		
			if($maxImageUploadSize != 0 && (int)($uploadfile['size']/1024) > $maxImageUploadSize)
			{
				return(JText::_('COM_FORM2CONTENT_ERROR_IMAGE_UPLOAD_MAX_SIZE_F2C_CONFIG'));
			}			
		}
		else if(array_key_exists('error', $uploadfile) && $uploadfile['error'] == 1)
		{
			return JText::_('COM_FORM2CONTENT_ERROR_IMAGE_UPLOAD_MAX_SIZE');
		}
		
		return '';		
	}

	function valSizeFileUpload($field)
	{
		$uploadfile = JRequest::getVar('t'.$field->id.'_fileupload', '', 'files', 'array');
		
		$fieldSettings = new JRegistry();
		$fieldSettings->loadString($field->settings);
		
		if(array_key_exists('size', $uploadfile) && $uploadfile['size'])
		{
			$maxUploadSize = (int)$fieldSettings->get('ful_max_file_size');
			
			if($maxUploadSize != 0 && (int)($uploadfile['size']/1024) > $maxUploadSize)
			{
				return(JText::_('COM_FORM2CONTENT_ERROR_FILE_UPLOAD_MAX_SIZE_F2C_CONFIG'));
			}						
		}
		else if(array_key_exists('error', $uploadfile) && $uploadfile['error'] == 1)
		{
			return JText::_('COM_FORM2CONTENT_ERROR_FILE_UPLOAD_MAX_SIZE');			
		}
		
		return '';
	}
}
?>
