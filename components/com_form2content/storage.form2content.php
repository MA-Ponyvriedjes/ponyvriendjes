<?php
defined('_JEXEC') or die('Restricted acccess');

class F2cFieldContent
{
	var $id;
	var $attribute;
	var $content;
	var $action;
	
	function F2cFieldContent($id, $attribute, $content, $action)
	{
		$this->id 			= $id;
		$this->attribute 	= $attribute;
		$this->content 		= $content;
		$this->action		= $action;
	}
}

class F2cStorage
{
	var $formId;
	var $backEnd;
	var $db;
	var $F2C_FIELD_FUNCTION_MAPPING = array();
	var $preparedData = null;
	
	function F2cStorage($formId, $backEnd)
	{
		$this->formId 	= $formId;
		$this->backEnd 	= $backEnd;
		$this->db		=& JFactory::getDBO();

		$this->F2C_FIELD_FUNCTION_MAPPING = array(F2C_FIELDTYPE_SINGLELINE => 'SingleLineText',
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
												  F2C_FIELDTYPE_DB_LOOKUP_MULTI => 'DatabaseLookupMulti',
												  F2C_FIELDTYPE_IMAGE_GALLERY => 'ImageGallery');		
	}
	
	public function storeFields($fields)
	{
		foreach($fields as $field)
		{
			$this->_storeField($field, $this->preparedData);
		}		
	}
	
	private function _storeField($field, $data)
	{

		$elementName		= 't' . $field->id;
		$functionName 		= '_store'.$this->F2C_FIELD_FUNCTION_MAPPING[$field->fieldtypeid];				
		$content 			= $this->$functionName($elementName, $field, $data[$field->fieldname]);
		
		// Process the content if a value was provided.
		// In front-end skip fields that were not shown on the form
		if(($field->frontvisible || $this->backEnd) && count($content))
		{									
			foreach($content as $fieldContent)
			{
				switch($fieldContent->action)
				{
					case 'INSERT':
						$this->db->setQuery('INSERT INTO #__f2c_fieldcontent (formid, fieldid, attribute, content) VALUES ('.(int)$this->formId.','.(int)$field->id.','.$this->db->quote($fieldContent->attribute).','.$this->db->quote($fieldContent->content).')');
						$this->db->query();
						break;
					case 'UPDATE':
						$this->db->setQuery('UPDATE #__f2c_fieldcontent set content='.$this->db->quote($fieldContent->content).' WHERE id='.(int)$fieldContent->id);
						$this->db->query();
						break;
					case 'DELETE':
						$this->db->setQuery('DELETE FROM #__f2c_fieldcontent WHERE id='.(int)$fieldContent->id);
						$this->db->query();							
						break;
					default: 
						// do nothing
						break;
				}														
			}
		}
	}
	
	private function _storeSingleLineText($elementName, $field, $data)
	{
		$content 	= array();
		$value 		= isset($data->values['VALUE']) ? $data->values['VALUE'] : '';
		$fieldId 	= $data->internal['fieldcontentid'];
		$action 	= ($value != '') ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);

		return $content;		
	}
	
	private function _storeMultiLineText($elementName, $field, $data)
	{
		$content 	= array();				
		$value 		= $data->values['VALUE'];
		$fieldId 	= $data->internal['fieldcontentid'];
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		
		$settings = new JRegistry();
		$settings->loadString($field->settings);
		
		if((int)$settings->get('mlt_max_num_chars'))
		{
			if(function_exists('mb_substr_count') && function_exists('mb_substr'))
			{
				$numNewLines = mb_substr_count ($value, "\r\n", 'UTF-8');
				$value = mb_substr($value, 0, (int)$settings->get('mlt_max_num_chars') + $numNewLines, 'UTF-8');
			}
			else
			{
				$numNewLines = substr_count ($value, "\r\n");
				$value = substr($value, 0, (int)$settings->get('mlt_max_num_chars') + $numNewLines);
			}
		}
							
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	private function _storeMultiLineEditor($elementName, $field, $data)
	{
		$content 	= array();					
		$fieldId 	= $data->internal['fieldcontentid'];
		$value 		= $data->values['VALUE'];		
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	private function _storeCheckBox($elementName, $field, $data)
	{
		$content 	= array();
		$value 		= $data->values['VALUE'];
		$fieldId 	= $data->internal['fieldcontentid'];
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		return $content;		
	}
	
	private function _storeSingleSelectList($elementName, $field, $data)
	{
		$content 	= array();					
		$value 		= $data->values['VALUE'];
		$fieldId 	= $data->internal['fieldcontentid'];
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	private function _storeImage($elementName, $field, $data)
	{
		$content 				= array();
		$f2cConfig				= F2cFactory::getConfig();		
		$fieldId 				= $data->internal['fieldcontentid'];
		$imageContent 			= new JRegistry();
		$imageHelper			= new F2C_Image();
		$saveImage				= false;
		$imagePath 				= Path::Combine(Path::Combine(F2C_Image::GetImagesRootPath(), 'p'.$field->projectid), 'f'.$this->formId);				
		$thumbsPath				= Path::Combine($imagePath, 'thumbs');
		$maxImageWidth 			= $field->settings->get('img_max_width', 10000);
		$maxImageHeight 		= $field->settings->get('img_max_height', 10000);
		$defaultThumbnailWidth 	= $f2cConfig->get('default_thumbnail_width', F2C_DEFAULT_THUMBNAIL_WIDTH);
		$defaultThumbnailHeight = $f2cConfig->get('default_thumbnail_height', F2C_DEFAULT_THUMBNAIL_HEIGHT);
		$thumbnailWidth 		= $field->settings->get('img_thumb_width', $defaultThumbnailWidth);
		$thumbnailHeight 		= $field->settings->get('img_thumb_height', $defaultThumbnailHeight);

		// Check if the image is selected for deletion
		if($data->internal['delete'])
		{
			$imageHelper->delete($field->projectid, $this->formId, $data->internal['currentfilename']);
			$content[] 	= new F2cFieldContent($fieldId, '', '', 'DELETE');
			return $content;	
		}
		
		switch($data->internal['method'])
		{
			case 'upload':				
				if($data->internal['imagelocation'])
				{						
					// delete current image, if there is one
					$imageHelper->delete($field->projectid, $this->formId, $data->internal['currentfilename']);							
							
					// Store the uploaded image
					$uploadFileName 		= $data->values['FILENAME'];
					$imageFileName 			= F2C_Image::CreateFullImageName($uploadFileName, $field->id);
					$imageFileLocation 		= Path::Combine($imagePath, $imageFileName);
					$imageFileLocationTmp 	= Path::Combine($imagePath, '~'.$imageFileName);
					$thumbnailLocation 		= Path::Combine($thumbsPath, F2C_Image::CreateThumbnailImageName($uploadFileName, $field->id));						
	
					if(!JFolder::exists($thumbsPath)) JFolder::create($thumbsPath);
					
					if(JFile::upload($data->internal['imagelocation'], $imageFileLocationTmp))
					{			
						$imageContent->set('filename', $imageFileName);
						
						// resize image
						if(!ImageHelper::ResizeImage($imageFileLocationTmp, $imageFileLocation, $maxImageWidth, $maxImageHeight, $f2cConfig->get('jpeg_quality', 75)))
						{
							JError::raiseError(401,JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
							return false;
						}
						
						$imageContent->set('width', $maxImageWidth);
						$imageContent->set('height', $maxImageHeight);
												
						// create thumbnail image
						if(!ImageHelper::ResizeImage($imageFileLocationTmp, $thumbnailLocation, $thumbnailWidth, $thumbnailHeight, $f2cConfig->get('jpeg_quality', 75)))
						{
							JError::raiseError(401,JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
							return false;
						}
						
						$imageContent->set('widthThumbnail', $thumbnailWidth);
						$imageContent->set('heightThumbnail', $thumbnailHeight);
						
						JFile::delete($imageFileLocationTmp);
						
						// Save the image info to the F2C table
						$saveImage = true;								
					}				
				}
				else 
				{
					// no image was uploaded, but the alt and title tags could be modified
					if($data->internal['fieldcontentid'])
					{
						// Load the image field
						$query = $this->db->getQuery(true);
						$query->select('content');
						$query->from('#__f2c_fieldcontent');
						$query->where('id = ' . (int)$data->internal['fieldcontentid']);
						$this->db->setQuery($query);
						$result = $this->db->loadResult();
						
						if($result)
						{
							$imageContent->loadString($result);
							
							if($imageContent->get('alt') != $data->values['ALT'])
							{
								$imageContent->set('alt', $data->values['ALT']);
								$saveImage = true;
							}

							if($imageContent->get('title') != $data->values['TITLE'])
							{
								$imageContent->set('title', $data->values['TITLE']);
								$saveImage = true;
							}
						}
					}
				}
				break;
				
			case 'copy':				
				if($data->internal['imagelocation'])
				{
					$srcImage 				= $data->internal['imagelocation'];
					$srcThumb 				= $data->internal['thumblocation'];
					$filename				= $data->values['FILENAME'];						
					$imageFileName 			= F2C_Image::CreateFullImageName($filename, $field->id);
					$imageFileLocation 		= Path::Combine($imagePath, $imageFileName);
					$thumbnailLocation 		= Path::Combine($thumbsPath, F2C_Image::CreateThumbnailImageName($filename, $field->id));
					
					JFolder::create($thumbsPath);				
					JFile::copy($srcImage, $imageFileLocation);
					JFile::copy($srcThumb, $thumbnailLocation);
					
					list($width, $height, $type, $attr) = getimagesize($imageFileLocation);
					list($widthThumb, $heightThumb, $typeThumb, $attrThumb) = getimagesize($thumbnailLocation);
					
					$imageContent->set('filename', $filename);
					$imageContent->set('width', $width);
					$imageContent->set('height', $height);
					$imageContent->set('widthThumbnail', $widthThumb);
					$imageContent->set('heightThumbnail', $heightThumb);
				}
								
				// Save the image info to the F2C table
				$saveImage = true;													
				break;
				
			case 'remote':
				if($data->internal['imagelocation'])
				{
					$srcImage 				= $data->internal['imagelocation'];
					$srcThumb 				= $data->internal['thumblocation'];
					$filename				= $data->values['FILENAME'];						
					$imageFileName 			= F2C_Image::CreateFullImageName($filename, $field->id);
					$thumbFileName			= F2C_Image::CreateThumbnailImageName($filename, $field->id);
					$imageFileLocation 		= Path::Combine($imagePath, $imageFileName);
					$thumbnailLocation 		= Path::Combine($thumbsPath, $thumbFileName);
					$tmpImage				= Path::Combine($imagePath, '~'.$imageFileName);
					$tmpThumb				= Path::Combine($thumbsPath, '~'.$thumbFileName);;
					
					JFolder::create($thumbsPath);

					$this->downloadFile($srcImage, $tmpImage);
					
					if($srcThumb)
					{
						$this->downloadFile($srcThumb, $tmpThumb);
					}
					
					// resize image
					if(!ImageHelper::ResizeImage($tmpImage, $imageFileLocation, $maxImageWidth, $maxImageHeight, $f2cConfig->get('jpeg_quality', 75)))
					{
						JError::raiseError(401,JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
						return false;
					}
					
					// Check if we need to generate a thumbnail image
					if($srcThumb)
					{
						// copy the thumbnail image
						JFile::copy($tmpThumb, $thumbnailLocation);
						// determine the size
						list($thumbnailWidth, $thumbnailHeight, $typeThumb, $attrThumb) = getimagesize($thumbnailLocation);
					}
					else 
					{
						// create thumbnail image
						if(!ImageHelper::ResizeImage($tmpImage, $thumbnailLocation, $thumbnailWidth, $thumbnailHeight, $f2cConfig->get('jpeg_quality', 75)))
						{
							JError::raiseError(401,JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
							return false;
						}
					}
					
					$imageContent->set('filename', $field->id . '.' . JFile::getExt($tmpImage));
					$imageContent->set('width', $maxImageWidth);
					$imageContent->set('height', $maxImageHeight);
					$imageContent->set('widthThumbnail', $thumbnailWidth);
					$imageContent->set('heightThumbnail', $thumbnailHeight);
					
					JFile::delete($tmpImage);
					
					if($srcThumb)
					{
						JFile::delete($tmpThumb);
					}
				}
								
				// Save the image info to the F2C table
				$saveImage = true;													
				break;
		}
		
		$imageContent->set('alt', $data->values['ALT']);
		$imageContent->set('title', $data->values['TITLE']);
		
		if($saveImage)								
		{
			$value 		= $imageContent->toString();
			$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');				
			$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		}
				
		return $content;	
	}
	
	private function _storeIFrame($elementName, $field, $data)
	{
		$content 		= array();
		$iframe			= new JRegistry();
		$fieldId 		= $data->internal['fieldcontentid'];
		
		$iframe->set('url', $data->values['URL']);
		$iframe->set('width', $data->values['WIDTH']);
		$iframe->set('height', $data->values['HEIGHT']);
		
		$value 			= $iframe->toString();
		$action 		= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 		= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	private function _storeEmail($elementName, $field, $data)
	{
		$content 		= array();					
		$email 			= new JRegistry();
		$fieldId 		= $data->internal['fieldcontentid'];
				
		$email->set('email', $data->values['EMAIL']);
		$email->set('display', $data->values['DISPLAY_AS']);
		
		$value 			= $email->toString();
		$action 		= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 		= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	private function _storeHyperlink($elementName, $field, $data)
	{
		$content 		= array();					
		$link 			= new JRegistry();
		$fieldId 		= $data->internal['fieldcontentid'];
				
		$link->set('url', $data->values['URL']);
		$link->set('display', $data->values['DISPLAY_AS']);
		$link->set('title', $data->values['TITLE']);
		$link->set('target', $data->values['TARGET']);
		
		$value 			= $link->toString();
		$action 		= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 		= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}
	
	private function _storeMultiSelectList($elementName, $field, $data)
	{
		$content 		= array();
		$fieldId 		= $data->internal['fieldcontentid'];
		$selections		= $data->values['VALUE'];
		
		if($selections && count($selections))
		{
			foreach($selections as $value)
			{
				$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, 'INSERT');			
			}
		}
				
		// Remove all previous entries
		$this->db->setQuery('DELETE FROM #__f2c_fieldcontent WHERE formid='.$this->formId.' AND fieldid='.$fieldId);
		$this->db->query();		
		
		return $content;				
	}
	
	private function _storeInfoText($elementName, $field, $data)
	{
		$content = array();
		return $content;
	}
	
	private function _storeDatePicker($elementName, $field, $data)
	{
		$content	= array();
		$fieldId 	= $data->internal['fieldcontentid'];
		$value 		= $data->values['VALUE'];
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		return $content;		
	}

	private function _storeFileUpload($elementName, $field, $data)
	{
		$content 	= array();					
		$fileUpload = new F2C_FileUpload();
		$fieldId 	= $data->internal['fieldcontentid'];
		$value		= '';			
				
		$this->db->setQuery('SELECT * FROM #__f2c_fieldcontent WHERE id='.(int)$fieldId);
		$fieldContent = $this->db->loadObject();
		
		if($fieldContent) 
		{
			$fileUpload->filename = $fieldContent->content;
		}
									
		if($data->internal['delete'])
		{
			$fileUpload->DeleteFile($field->projectid, $this->formId, $field->id);
			$fileUpload->filename = '';
		}
		else
		{
			switch($data->internal['method'])
			{
				case 'upload':
					if($data->internal['filelocation'])
					{	
						$settings = new JRegistry();
						$settings->loadString($field->settings);
					
						// check extension
						$extension = strtolower(JFile::getExt($data->values['FILENAME']));
	
						if(count((array)$settings->get('ful_whitelist')))
						{
							if(!array_key_exists($extension, (array)$settings->get('ful_whitelist')))
							{
								JError::raiseError(401,sprintf(JText::_('ERROR_FILE_UPLOAD_EXTENSION_NOT_ALLOWED'), $extension));
								return false;
							}
						}
	
						if(count((array)$settings->get('ful_blacklist')))
						{
							if(array_key_exists($extension, (array)$settings->get('ful_blacklist')))
							{
								JError::raiseError(401,sprintf(JText::_('ERROR_FILE_UPLOAD_EXTENSION_NOT_ALLOWED'), $extension));
								return false;
							}
						}
											
						if($fieldId)
						{
							// delete previous file							
							$fileUpload->DeleteFile($field->projectid, $this->formId, $field->id);
						}
						
						$fileUpload->filename 	= $data->values['FILENAME'];
						$fileLocation 			= Path::Combine(F2C_FileUpload::GetFilePath($field->projectid, $this->formId, $field->id), $fileUpload->filename);
						
						if(JFile::upload($data->internal['filelocation'], $fileLocation))
						{			
						}						
					}	
					
					$value = $fileUpload->filename;
					break;
					
				case 'copy':
					$src = $data->internal['filelocation'];
					
					if(JFile::exists($src))
					{
						$dstFolder = F2C_FileUpload::GetFilePath($field->projectid, $this->formId, $field->id);
						$dst = Path::Combine($dstFolder, $data->values['FILENAME']);
						JFolder::create($dstFolder);					
						JFile::copy($src, $dst);
						$value = $data->values['FILENAME'];
					}
					break;
				
				case 'remote':
					$src = $data->internal['filelocation'];
					$dstFolder = F2C_FileUpload::GetFilePath($field->projectid, $this->formId, $field->id);
					$dst = Path::Combine($dstFolder, $data->values['FILENAME']);
					JFolder::create($dstFolder);	
					$this->downloadFile($src, $dst);			
					$value = $data->values['FILENAME'];
					break;
			}
						
		}				
		
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');				
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);		
		
		return $content;
	}
	
	private function _storeDatabaseLookup($elementName, $field, $data)
	{
		$content 	= array();
		$value 		= $data->values['VALUE'];
		$fieldId 	= $data->internal['fieldcontentid'];
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		return $content;
	}
	
	private function _storeDisplayList($elementName, $field, $data)
	{
		$content	= array();							
		$fieldId 	= $data->internal['fieldcontentid'];
		$listNew 	= null;
		$valueList	= new JRegistry();

		if(count($data->values['VALUE']))
		{
			foreach($data->values['VALUE'] as $displayItem)
			{ 
				$listNew[] = $displayItem;
			}
		}
		
		$valueList->loadArray($listNew);
				
		$value 		= $valueList->toString();		
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		return $content;
	}
	
	private function _storeGeoCoder($elementName, $field, $data)
	{
		$addressId		= $data->internal['addressid'];
		$addressValue 	= $data->values['ADDRESS'];		
		$latId			= $data->internal['latid'];
		$latValue		= $data->values['LAT'];
		$lonId			= $data->internal['lonid'];
		$lonValue 		= $data->values['LON'];		
				
		if($addressId)
		{
			// existing record
			$action = (!$addressValue && !$latValue && !$lonValue) ? 'DELETE' : 'UPDATE';
		}
		else
		{
			// new record
			$action = ($addressValue || $latValue || $lonValue) ? 'INSERT' : '';
		}
		
		$content 	= array();					
		$content[] 	= new F2cFieldContent($addressId, 'ADDRESS', $addressValue, $action);
		$content[] 	= new F2cFieldContent($latId, 'LAT', $latValue, $action);
		$content[] 	= new F2cFieldContent($lonId, 'LON', $lonValue, $action);
		
		return $content;					
	}
	
	private function _storeDatabaseLookupMulti($elementName, $field, $data)
	{
		$content	= array();							
		$fieldId 	= $data->internal['fieldcontentid'];
		
		if(count($data->values['VALUE']))
		{
			foreach($data->values['VALUE'] as $item)
			{ 
				$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $item, 'INSERT');
			}
		}
		
		// Remove all previous entries
		$this->db->setQuery('DELETE FROM #__f2c_fieldcontent WHERE formid='.$this->formId.' AND fieldid='.$fieldId);
		$this->db->query();		
				
		return $content;
	}
	
	private function _storeImageGallery($elementName, $field, $data)
	{
		$content				= array();
		$fileNames				= array();					
		$fieldId 				= $data->internal['fieldcontentid'];
		$listNew 				= null;
		$valueList				= new JRegistry();
		$galleryDir 			= F2cImageGallery::getGalleryBaseUrl($this->formId, $field->projectid, $field->id, false);
		$galleryDirTmpBase		= F2cImageGallery::getGalleryTmpBase();
		$galleryDirThumbs		= Path::Combine($galleryDir, 'thumbs');
		
		if(!JFolder::exists($galleryDirThumbs))
		{
			JFolder::create($galleryDirThumbs);
		}
		
		if(count($data->values['VALUE']))
		{
			foreach($data->values['VALUE'] as $imageInfo)
			{ 
				$listNew[] 							= $imageInfo;
				$fileNames[$imageInfo['FILENAME']] 	= $imageInfo['FILENAME'];
				
				if($imageInfo['FILEPATH'] != $galleryDir)
				{
					JFile::move(Path::Combine($imageInfo['FILEPATH'], $imageInfo['FILENAME']), Path::Combine($galleryDir, $imageInfo['FILENAME']));
					JFile::move(Path::Combine($imageInfo['FILEPATH'].DS.'thumbs', $imageInfo['FILENAME']), Path::Combine($galleryDirThumbs, $imageInfo['FILENAME']));
				}
			}
		}
		
		$valueList->loadArray($listNew);
				
		$value 		= $valueList->toString();		
		$action 	= ($value) ? (($fieldId) ? 'UPDATE' : 'INSERT') : (($fieldId) ? 'DELETE' : '');
		$content[] 	= new F2cFieldContent($fieldId, 'VALUE', $value, $action);
		
		
		// cleanup the temporary directory
		
		
		return $content;
	}
	
	public function prepareSubmittedData($fields)
	{
		$this->preparedData = array();
		
		foreach($fields as $field)
		{
			// add the field definition to the data
			$fldData 				= new F2cFieldData();
			$fldData->id 			= $field->id;
			$fldData->fieldtypeid 	= $field->fieldtypeid;
			$fldData->title 		= $field->title;
			$fldData->fieldname 	= $field->fieldname;
			$fldData->ordering 		= $field->ordering;
			$fldData->frontvisible 	= $field->frontvisible;				
			$fldData->settings 		= $field->settings;
			$fldData->projectid		= $field->projectid;
			
			$functionName = '_prepare'.$this->F2C_FIELD_FUNCTION_MAPPING[$field->fieldtypeid];
			$this->$functionName('t' . $field->id, $fldData);
					
			$this->preparedData[$field->fieldname] = $fldData;
		}
	}
	
	private function _prepareSingleLineText($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE'] = HtmlHelper::unquoteData(JRequest::getVar($elementName, '', 'post', 'string', JREQUEST_ALLOWRAW));
	}
	
	private function _prepareMultiLineText($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE'] = HtmlHelper::unquoteData(JRequest::getVar($elementName, '', 'post', 'string', JREQUEST_ALLOWRAW));
	}
	
	private function _prepareMultiLineEditor($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE'] = HtmlHelper::unquoteData(JRequest::getVar($elementName, '', 'post', 'string', JREQUEST_ALLOWRAW));
	}
	
	private function _prepareCheckBox($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE'] = HtmlHelper::unquoteData(JRequest::getString($elementName));
	}
	
	private function _prepareSingleSelectList($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE'] = HtmlHelper::unquoteData(JRequest::getVar($elementName, '', 'post', 'string', JREQUEST_ALLOWRAW));
	}
	
	private function _prepareImage($elementName, &$fieldData)
	{
		$upload									= JRequest::getVar($elementName . '_fileupload', '', 'files', 'array');
		$fieldData->internal['fieldcontentid'] 	= JRequest::getInt('hid'.$elementName);
		$fieldData->internal['method'] 			= 'upload';
		$fieldData->internal['delete'] 			= JRequest::getVar($elementName . '_del');
		$fieldData->internal['currentfilename']	= JRequest::getString($elementName . '_filename');
		$fieldData->internal['imagelocation']	= ($upload['size']) ? $upload['tmp_name'] : '';
		$fieldData->internal['thumblocation']	= '';					
		$fieldData->values['FILENAME']			= JFile::getName($upload['name']);
		$fieldData->values['ALT']				= JRequest::getString($elementName . '_alt');
		$fieldData->values['TITLE']				= JRequest::getString($elementName . '_title');		
		$fieldData->values['WIDTH']				= null;
		$fieldData->values['HEIGHT']			= null;
		$fieldData->values['WIDTH_THUMBNAIL']	= null;
		$fieldData->values['HEIGHT_THUMBNAIL']	= null;
	}
	
	private function _prepareIFrame($elementName,  &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);
		
		$fieldData->values['URL'] = HtmlHelper::unquoteData(JRequest::getString($elementName));
		$fieldData->values['WIDTH'] = JRequest::getInt($elementName . '_width');
		$fieldData->values['HEIGHT'] = JRequest::getInt($elementName . '_height');
	}
	
	private function _prepareEmail($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);
		
		$fieldData->values['EMAIL'] = HtmlHelper::unquoteData(JRequest::getString($elementName));
		$fieldData->values['DISPLAY_AS'] = HtmlHelper::unquoteData(JRequest::getString($elementName . '_display'));		
	}
	
	private function _prepareHyperlink($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);
		
		$fieldData->values['URL'] 			= HtmlHelper::unquoteData(JRequest::getString($elementName));
		$fieldData->values['DISPLAY_AS'] 	= HtmlHelper::unquoteData(JRequest::getString($elementName . '_display'));
		$fieldData->values['TITLE'] 		= HtmlHelper::unquoteData(JRequest::getString($elementName . '_title'));
		$fieldData->values['TARGET'] 		= HtmlHelper::unquoteData(JRequest::getString($elementName . '_target'));
	}
	
	private function _prepareMultiSelectList($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] = JRequest::getInt('hid'.$elementName);		
		$fieldData->values['VALUE'] = JRequest::getVar($elementName);
	}
	
	private function _prepareInfoText($elementName, &$fieldData)
	{
	}
	
	private function _prepareDatePicker($elementName, &$fieldData)
	{
		$f2cConfig 								= F2cFactory::getConfig();
		$fieldData->internal['fieldcontentid'] 	= JRequest::getInt('hid'.$elementName);		
		$fieldData->values['VALUE'] 			= '';		
		$value 									= HtmlHelper::unquoteData(JRequest::getString($elementName, '', 'post'));
		
		if($value)
		{
			$date = F2cDateTimeHelper::ParseDate($value, $f2cConfig->get('date_format'));
			$fieldData->values['VALUE'] = ($date) ? $date->toISO8601() : '';						
		}
	}

	private function _prepareFileUpload($elementName, &$fieldData)
	{
		$upload									= JRequest::getVar($elementName . '_fileupload', '', 'files', 'array');
		$fieldData->internal['fieldcontentid'] 	= JRequest::getInt('hid'.$elementName);
		$fieldData->internal['method'] 			= 'upload';
		$fieldData->internal['delete'] 			= JRequest::getVar($elementName . '_del');
		$fieldData->internal['filelocation']	= $upload['size'] ? $upload['tmp_name'] : '';	
		$fieldData->values['FILENAME']			= JFile::getName($upload['name']);		
	}
	
	private function _prepareDatabaseLookup($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] 	= JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE']				= HtmlHelper::unquoteData(JRequest::getVar($elementName, '', 'post', 'string', JREQUEST_ALLOWRAW));		
	}
	
	private function _prepareDisplayList($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] 	= JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE'] 			= array();
		$rowKeys 								= Jrequest::getVar($elementName.'RowKey', array());
		
		if(count($rowKeys))
		{
			foreach($rowKeys as $rowKey)
			{
				$value =  JRequest::getVar($rowKey . 'val', '', 'post', 'string', JREQUEST_ALLOWRAW);
				
				// prevent duplicate and empty entries
				if(!array_key_exists($value, $fieldData->values['VALUE']) && $value != '')
				{
					$fieldData->values['VALUE'][] = $value;
				}
			}
		}
	}
	
	private function _prepareGeoCoder($elementName, &$fieldData)
	{
		$fieldData->internal['addressid'] 	= JRequest::getInt('hid'.$elementName.'_address');
		$fieldData->internal['latid'] 		= JRequest::getInt('hid'.$elementName.'_lat');
		$fieldData->internal['lonid'] 		= JRequest::getInt('hid'.$elementName.'_lon');
		$fieldData->values['ADDRESS']		= HtmlHelper::unquoteData(JRequest::getString($elementName.'_address'));
		$fieldData->values['LAT']			= HtmlHelper::unquoteData(JRequest::getString($elementName.'_hid_lat'));
		$fieldData->values['LON']			= HtmlHelper::unquoteData(JRequest::getString($elementName.'_hid_lon'));
	}
	
	private function _prepareDatabaseLookupMulti($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] 	= JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE'] 			= array();
		$rowKeys 								= Jrequest::getVar($elementName.'RowKey', array());
		
		if(count($rowKeys))
		{
			foreach($rowKeys as $rowKey)
			{
				$value =  JRequest::getVar($rowKey . 'val', '', 'post', 'string', JREQUEST_ALLOWRAW);
				
				// prevent duplicate and empty entries
				if(!array_key_exists($value, $fieldData->values['VALUE']) && $value != '')
				{
					$fieldData->values['VALUE'][] = $value;
				}
			}
		}
	}

	private function _prepareImageGallery($elementName, &$fieldData)
	{
		$fieldData->internal['fieldcontentid'] 	= JRequest::getInt('hid'.$elementName);
		$fieldData->values['VALUE'] 			= array();
		$rowKeys 								= Jrequest::getVar($elementName.'RowKey', array());		
		$galleryDir 							= F2cImageGallery::getGalleryBaseUrl($this->formId, $field->projectid, $field->id, false);
		$galleryDirTmp							= F2cImageGallery::getGalleryBaseUrl($this->formId, $field->projectid, $field->id, true);
		
		if(count($rowKeys))
		{
			foreach($rowKeys as $rowKey)
			{
				$arrImage 					= array();
				$arrImage['ALT'] 			= JRequest::getString($rowKey . 'alt');
				$arrImage['TITLE'] 			= JRequest::getString($rowKey . 'title');
				$arrImage['FILENAME'] 		= JRequest::getString($rowKey . 'filename');
				$arrImage['WIDTH'] 			= JRequest::getInt($rowKey . 'width');
				$arrImage['HEIGHT'] 		= JRequest::getInt($rowKey . 'height');
				$arrImage['THUMBWIDTH'] 	= JRequest::getInt($rowKey . 'thumbwidth');
				$arrImage['THUMBHEIGHT'] 	= JRequest::getInt($rowKey . 'thumbheight');
				
				if(JFile::exists(Path::Combine($galleryDir, $arrImage['FILENAME'])))
				{
					$arrImage['FILEPATH'] = $galleryDir;
				}
				else if(JFile::exists(Path::Combine($galleryDirTmp, $arrImage['FILENAME'])))
				{
					$arrImage['FILEPATH'] = $galleryDirTmp;
				}
				else
				{
					// Image not found, discard the image information
					continue;
				}
				
				$fieldData->values['VALUE'][] = $arrImage;
			}
		}
		
		$fieldData->internal['basedir'] = $galleryDir;
	}
	
	private function downloadFile($srcUrl, $dstFile)
	{
		$config = JFactory::getConfig();

		// Capture PHP errors
		$php_errormsg = 'Error Unknown';
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		// Open the remote server socket for reading
		$inputHandle = @ fopen($srcUrl, "r");
		$error = strstr($php_errormsg,'failed to open stream:');
		if (!$inputHandle)
		{
			JError::raiseWarning(42, JText::sprintf('Error downloading file', $error));
			return false;
		}

		/*
		$meta_data = stream_get_meta_data($inputHandle);
		
		foreach ($meta_data['wrapper_data'] as $wrapper_data)
		{
			if (substr($wrapper_data, 0, strlen("Content-Disposition")) == "Content-Disposition")
			{
				$contentfilename = explode ("\"", $wrapper_data);
				$target = $contentfilename[1];
			}
		}

		// Set the target path if not given
		if (!$target) {
			$target = $config->get('tmp_path').DS.self::getFilenameFromURL($url);
		}
		else {
			$target = $config->get('tmp_path').DS.basename($target);
		}
		*/
		
		// Initialise contents buffer
		$contents = null;

		while (!feof($inputHandle))
		{
			$contents .= fread($inputHandle, 4096);
			
			if ($contents == false)
			{
				JError::raiseWarning(44, JText::sprintf('Error reading remote file', $php_errormsg));
				return false;
			}
		}

		// Write buffer to file
		JFile::write($dstFile, $contents);

		// Close file pointer resource
		fclose($inputHandle);

		// restore error tracking to what it was before
		ini_set('track_errors',$track_errors);
	}
}
?>
