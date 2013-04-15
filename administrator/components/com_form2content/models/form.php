<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_form2content'.DS.'models'.DS.'project.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'models'.DS.'formbase.php');

class Form2ContentModelForm extends Form2ContentModelFormBase
{
	public function save($data, $saveFormOnly = false)
	{
		if($data['created'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['created'], '%Y-%m-%d'))
			{
				$data['created'] = $date->toMySQL();			
			}
			else
			{
				$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_CREATED_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		if($data['publish_up'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['publish_up'], '%Y-%m-%d'))
			{
				$data['publish_up'] = $date->toMySQL();			
			}
			else
			{
				$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_PUBLISH_UP_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		if($data['publish_down'])
		{
			if($date = F2cDateTimeHelper::ParseDate($data['publish_down'], '%Y-%m-%d'))
			{
				$data['publish_down'] = $date->toMySQL();			
			}
			else
			{
				$this->setError(sprintf(JText::_('COM_FORM2CONTENT_ERROR_DATE_FIELD_INCORRECT_DATE'), JText::_('COM_FORM2CONTENT_FIELD_PUBLISH_DOWN_LABEL'), $translatedDateFormat));
				return false;
			}
		}

		return parent::save($data, $saveFormOnly);
	}

	public function getJArticle($id) 
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		// Construct the query
		$query->select('*');
		$query->from('#__content');
		$query->where('id = ' . (int)$id);		
		// Setup the query
		$db->setQuery($query->__toString());
		
		$obj = $db->loadObject();
		
		if(!$obj)
		{
			$obj = new JObject();
			$obj->hits = 0;
			$obj->version = 0;
		}
		
		return $obj;
	}
	
	public function export($cid)
	{
		require_once(JPATH_COMPONENT_SITE.DS.'libraries'.DS.'SimpleXMLExtended.php');

		$db 				= JFactory::getDbo();
		$f2cConfig 			= F2cFactory::getConfig();
		$query				= $db->getQuery(true);
		$nullDate			= $db->getNullDate();
		$exportDir			= $f2cConfig->get('export_dir');
		$exportFileMode		= $f2cConfig->get('export_file_mode', 0);
		$exportImageMode	= $f2cConfig->get('export_images_mode', 0);
		$xml 				= new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><forms xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.form2content.com/forms f2c_forms_1_0_0.xsd" xmlns="http://schemas.form2content.com/forms"></forms>');
		
		// Build the Category Alias lookup lists
		$this->InitXmlImport();
		
		if(empty($exportDir))
		{
			JError::raiseWarning(500, JText::_('COM_FORM2CONTENT_ERROR_EXPORT_DIR_EMPTY'));
			return false;
		}

		if(!JFolder::exists($exportDir))
		{
			JError::raiseWarning(500, JText::_('COM_FORM2CONTENT_ERROR_EXPORT_DIR_DOES_NOT_EXIST'));
			return false;
		}
		
		foreach($cid as $id)
		{
			$form 		= $this->getItem($id);
			$fields 	= $this->loadFieldData($form->id, $form->projectid);
			$xmlForm 	= $xml->addChild('form');
			
			$xmlForm->addChild('id', $form->id);	
			$xmlForm->addChild('contenttype', $this->dicContentTypeId[$form->projectid]);				
			$xmlForm->addChild('title', $form->title);					
			$xmlForm->addChild('alias', $form->alias);					
			$xmlForm->addChild('created_by_username', $this->resolveUserid($form->created_by));					
			$xmlForm->addChild('created_by_alias', $form->created_by_alias);					
			$xmlForm->addChild('created', $this->dateToIso8601OrEmpty($form->created));					
			$xmlForm->addChild('modified', $this->dateToIso8601OrEmpty($form->modified));					
			$xmlForm->addChild('metakey', $form->metakey);					
			$xmlForm->addChild('metadesc', $form->metadesc);					
			$xmlForm->addChild('cat_alias_path', $this->dicCatId[$form->catid]);
			$xmlForm->addChild('intro_template', $form->intro_template);					
			$xmlForm->addChild('main_template', $form->main_template);					
			$xmlForm->addChild('ordering', $form->ordering);					
			$xmlForm->addChild('publish_up', $this->dateToIso8601OrEmpty($form->publish_up));					
			$xmlForm->addChild('publish_down', $this->dateToIso8601OrEmpty($form->publish_down));

			switch($form->state)
			{
				case F2C_STATE_TRASH:
					$xmlForm->addChild('state', 'trashed');
					break;
				case F2C_STATE_UNPUBLISHED:
					$xmlForm->addChild('state', 'unpublished');
					break;
				case F2C_STATE_PUBLISHED:
					$xmlForm->addChild('state', 'published');
					break;	
			}
			
			$xmlForm->addChild('featured', $form->featured ? "yes" : "no");		
			$xmlForm->addChild('access', $this->dicViewingAccessLevelId[$form->access]);
			$xmlForm->addChild('language', $form->language);
			
      		$xmlFieldAttribs = $xmlForm->addChild('attribs');
      			
      		if($form->attribs)
      		{
      			$this->addArrayToXml($xmlFieldAttribs, $form->attribs);
      		}

      		$xmlFieldMetadata = $xmlForm->addChild('metadata');
      			
      		if($form->metadata)
      		{
      			$this->addArrayToXml($xmlFieldMetadata, $form->metadata);
      		}

      		$xmlFields = $xmlForm->addChild('fields');
      		
      		if(count($fields))
      		{
      			foreach($fields as $field)
      			{
      				if($field->fieldtypeid == F2C_FIELDTYPE_INFOTEXT)
      				{
      					// skip this field type
      					continue;
      				}
      				
      				$xmlField = $xmlFields->addChild('field');
      				$xmlField->addChild('fieldname', $field->fieldname);
					
      				switch($field->fieldtypeid)
      				{
      					case F2C_FIELDTYPE_FILE:
      						$xmlFieldContent = $xmlField->addChild('contentFileUpload');
      						$xmlFieldContent->addChild('filename', $field->values['FILENAME']);
      						
      						if($field->values['FILENAME'])
      						{	      						
	      						switch($exportFileMode)
	      						{
	      							case F2C_EXPORT_FILEMODE_ENCAPSULATE:
	      								$fileLocation 	= Path::Combine(F2C_FileUpload::GetFilePath($form->projectid, $form->id, $field->id), $field->values['FILENAME']);
	      								$xmlFile 		= $xmlFieldContent->addChild('file');
								      	$xmlFile->addCData(base64_encode($this->getFileContents($fileLocation)));      							
	      								$xmlFile->addAttribute('includemode', 'include');
								      	break;
	      								
	      							case F2C_EXPORT_FILEMODE_LOCAL:
	      								$fileLocation 	= Path::Combine(F2C_FileUpload::GetFilePath($form->projectid, $form->id, $field->id), $field->values['FILENAME']);
	      								$xmlFile 		= $xmlFieldContent->addChild('file', $fileLocation);
	      								$xmlFile->addAttribute('includemode', 'path');
	      								break;
	      								
	      							case F2C_EXPORT_FILEMODE_REMOTE:
	      								$fileLocation 	= Path::Combine(F2C_FileUpload::GetFileUrl($form->projectid, $form->id, $field->id), $field->values['FILENAME']);
	      								$xmlFile 		= $xmlFieldContent->addChild('file', $fileLocation);
	      								$xmlFile->addAttribute('includemode', 'url');
	      								break;
	      						}
      						}
      						break;

      					case F2C_FIELDTYPE_IMAGE:
      						$xmlFieldContent = $xmlField->addChild('contentImage');
      						$xmlFieldContent->addChild('filename', $field->values['FILENAME']);
      						$xmlFieldContent->addChild('alt', $field->values['ALT']);
      						$xmlFieldContent->addChild('title', $field->values['TITLE']);
      						$xmlFieldContent->addChild('width', $field->values['WIDTH']);
      						$xmlFieldContent->addChild('height', $field->values['HEIGHT']);
      						$xmlFieldContent->addChild('width_thumbnail', $field->values['WIDTH_THUMBNAIL']);
      						$xmlFieldContent->addChild('height_thumbnail', $field->values['HEIGHT_THUMBNAIL']);
      						
      						if($field->values['FILENAME'])
      						{	      						
	      						switch($exportImageMode)
	      						{
	      							case F2C_EXPORT_FILEMODE_ENCAPSULATE:
	      								$imageLocation 	= Path::Combine(F2C_Image::GetImagesPath($form->projectid, $form->id), $field->values['FILENAME']);
	      								$thumbLocation 	= Path::Combine(F2C_Image::GetThumbnailsPath($form->projectid, $form->id), $field->values['FILENAME']);
	      								$xmlImage 		= $xmlFieldContent->addChild('image');
								      	$xmlThumb 		= $xmlFieldContent->addChild('thumbnail');
	      								$xmlImage->addCData(base64_encode($this->getFileContents($imageLocation)));  
								      	$xmlImage->addAttribute('includemode', 'include');								      	
								      	$xmlThumb->addCData(base64_encode($this->getFileContents($thumbLocation)));      							
	      								break;
	      								
	      							case F2C_EXPORT_FILEMODE_LOCAL:
	      								$imageLocation 	= Path::Combine(F2C_Image::GetImagesPath($form->projectid, $form->id), $field->values['FILENAME']);
	      								$thumbLocation 	= Path::Combine(F2C_Image::GetThumbnailsPath($form->projectid, $form->id), $field->values['FILENAME']);
	      								$xmlImage 		= $xmlFieldContent->addChild('image', $imageLocation);
	      								$xmlThumb 		= $xmlFieldContent->addChild('thumbnail', $thumbLocation);
	      								$xmlImage->addAttribute('includemode', 'path');
	      								break;
	      								
	      							case F2C_EXPORT_FILEMODE_REMOTE:
	      								$imageLocation 	= Path::Combine(F2C_Image::GetImagesUrl($form->projectid, $form->id), $field->values['FILENAME']);
	      								$thumbLocation 	= Path::Combine(F2C_Image::GetThumbnailsUrl($form->projectid, $form->id), $field->values['FILENAME']);
	      								$xmlImage 		= $xmlFieldContent->addChild('image', $imageLocation);
	      								$xmlThumb		= $xmlFieldContent->addChild('thumbnail', $thumbLocation);
	      								$xmlImage->addAttribute('includemode', 'url');
	      								break;
	      						}
      						}      						
      						break;

      					case F2C_FIELDTYPE_DISPLAYLIST:
      					case F2C_FIELDTYPE_MULTISELECTLIST:
      					case F2C_FIELDTYPE_DB_LOOKUP_MULTI:
      						$xmlFieldContent = $xmlField->addChild('contentMultipleTextValue');
      						$xmlFieldValues = $xmlFieldContent->addChild('values');
      						
      						if(count($field->values['VALUE']))
      						{
      							foreach($field->values['VALUE'] as $item)
      							{
      								$xmlFieldValues->addChild('value', $item);
      							}
      						}
      						break;
      					      						
      					case F2C_FIELDTYPE_DATABASE_LOOKUP:
      					case F2C_FIELDTYPE_MULTILINEEDITOR:
      					case F2C_FIELDTYPE_MULTILINETEXT:
      					case F2C_FIELDTYPE_SINGLELINE:
      					case F2C_FIELDTYPE_SINGLESELECTLIST:
      						$xmlFieldContent = $xmlField->addChild('contentSingleTextValue');
      						$xmlFieldContent->addChild('value', $field->values['VALUE']);
      						break;
      						
      					case F2C_FIELDTYPE_CHECKBOX:
      						$xmlFieldContent = $xmlField->addChild('contentBoolean');
      						$xmlFieldContent->addChild('value', $field->values['VALUE']);
      						break;
      						
      					case F2C_FIELDTYPE_DATEPICKER:
      						$xmlFieldContent = $xmlField->addChild('contentDate');
      						$xmlFieldContent->addChild('value', $field->values['VALUE']);
      						break;
      						
      					case F2C_FIELDTYPE_EMAIL:
      						$xmlFieldContent = $xmlField->addChild('contentEmail');
      						$xmlFieldContent->addChild('email', $field->values['EMAIL']);
      						$xmlFieldContent->addChild('display_as', $field->values['DISPLAY_AS']);
      						break;
      						
      					case F2C_FIELDTYPE_GEOCODER:
      						$xmlFieldContent = $xmlField->addChild('contentGeocoder');
      						$xmlFieldContent->addChild('address', $field->values['ADDRESS']);
      						$xmlFieldContent->addChild('lat', $field->values['LAT']);
      						$xmlFieldContent->addChild('lon', $field->values['LON']);
      						break;
      						
      					case F2C_FIELDTYPE_HYPERLINK:
      						$xmlFieldContent = $xmlField->addChild('contentHyperlink');
      						$xmlFieldContent->addChild('url', $field->values['URL']);
      						$xmlFieldContent->addChild('display_as', $field->values['DISPLAY_AS']);
      						$xmlFieldContent->addChild('title', $field->values['TITLE']);
      						$xmlFieldContent->addChild('target', $field->values['TARGET']);
      						break;
      						
      					case F2C_FIELDTYPE_IFRAME:
      						$xmlFieldContent = $xmlField->addChild('contentIframe');
      						$xmlFieldContent->addChild('url', $field->values['URL']);
      						$xmlFieldContent->addChild('width', $field->values['WIDTH']);
      						$xmlFieldContent->addChild('height', $field->values['HEIGHT']);
      						break;
      				}
      			}
      		}
		}
		
		// Write the export file
		$timestamp = new JDate();
		$fileName = Path::Combine($exportDir, $timestamp->format('YmdHis'). ' F2C Export.xml'); 
		JFile::write($fileName, $xml->asXML());	
		
		JFactory::getApplication()->enqueueMessage(sprintf(JText::_('COM_FORM2CONTENT_ARTICLE_EXPORT_COMPLETE'), count($cid), $fileName));
		return true;
	}
	
	/*
	 * Convert an array to an XML structure
	 */
	private function addArrayToXml($node, $array, $keyIsElement = true)
	{
		if(count($array))
		{
			foreach($array as $key => $value)
			{
				if($keyIsElement)
				{					
					if(is_array($value))
					{
						// The array key is the element name
						$xmlElement = $node->addChild($key);
						self::addArrayToXml($xmlElement, $value, false);
					}
					else 
					{
						$node->addChild($key, self::valueReplace($value));
					}
				}
				else
				{
					// 'key' is the element name. Use this when $key might 
					// not be a valid XML element name
					$xmlArrayElement	= $node->addChild('arrayelement');
					
					$xmlArrayElement->addChild('key', $key);
					$xmlArrayElement->addChild('value', self::valueReplace($value));
				}
			}
		}
	}
		
	private function addRegistryToXml($node, $registry)
	{
		$this->addArrayToXml($node, $registry->toArray());
	}
	
	private function valueReplace($value)
	{
		$value = str_replace('&nbsp;', '&amp;nbsp;', $value);
		$value = str_replace('&gt;', '&amp;gt;', $value);
		$value = str_replace('&lt;', '&amp;lt;', $value);
		$value = str_replace('&apos;', '&amp;apos;', $value);
		
		return $value;
	}
	
	private function dateToIso8601OrEmpty($date)
	{
		if($date != JFactory::getDbo()->getNullDate())
		{
			$formattedDate = new JDate($date);
			return $formattedDate->toISO8601();
		}
		else 
		{
			return '';				
		}
		
		return $formattedDate;
	}
	
	private function getFileContents($filename)
	{
      	$contents = '';

      	if(JFile::exists($filename))
      	{
      		$contents = JFile::read($filename);
      	}
		
      	return $contents;
	}
	
	private function resolveUserid($userid)
	{
		static $usernames = array();
		
		if(array_key_exists($userid, $usernames))
		{
			return $usernames[$userid];
		}
		else 
		{
			$user = JUser::getInstance($userid);
			return $user->username;			
		}
	}
}
?>