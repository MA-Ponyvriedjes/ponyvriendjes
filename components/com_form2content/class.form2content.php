<?php
defined('_JEXEC') or die('Restricted acccess');

class F2C_FileUpload
{
	var $filename;
	
	function F2C_FileUpload()
	{
	}

	function GetFileUrl($contentTypeId, $articleId, $fieldId, $relative = false)
	{
		$path = ($relative) ? '' : F2cUri::GetClientRoot();
		$path.= 'media/com_form2content/documents/c'.$contentTypeId.'/a'.$articleId.'/f'.$fieldId;
		return $path;
	}

	function GetFilesRootPath($relative = false)
	{
		if($relative)
		{
			return 'media'.DS.'com_form2content'.DS.'documents'.DS;
		}
		else
		{
			return JPATH_SITE.DS.'media'.DS.'com_form2content'.DS.'documents'.DS;
		}				
	}
	
	function GetFilesPath($projectId, $formId, $relative = false)
	{
		return Path::Combine(F2C_FileUpload::GetFilesRootPath($relative), 'c'.$projectId.DS.'a'.$formId);
	}

	function GetFilePath($contentTypeId, $articleId, $fieldId, $relative = false)
	{
		return Path::Combine(F2C_FileUpload::GetFilesRootPath($relative), 'c'.$contentTypeId.DS.'a'.$articleId.DS.'f'.$fieldId);
	}

	function GetFilePathFormRoot($contentTypeId, $articleId, $relative = false)
	{
		return Path::Combine(F2C_FileUpload::GetFilesRootPath($relative), 'c'.$contentTypeId.DS.'a'.$articleId);
	}
	
	function DeleteFile($contentTypeId, $ArticleId, $fieldId)
	{
		if($this->filename)
		{
			$file = Path::Combine($this->GetFilePath($contentTypeId, $ArticleId, $fieldId), $this->filename);
		
			if(JFile::exists($file))
			{
				JFile::delete($file);
			}
		}
	}
	
	function deleteContentTypeFieldFiles($ContentTypeFieldId)
	{
		$db =& JFactory::getDBO(); 

		$sql =	'SELECT pfl.projectid, fct.formid, fct.content  
				 FROM #__f2c_projectfields pfl 
				 INNER JOIN #__f2c_fieldcontent fct ON pfl.id = fct.fieldid 
				 WHERE pfl.fieldtypeid = '. F2C_FIELDTYPE_FILE .' AND pfl.id = ' . (int)$ContentTypeFieldId;
				
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		
		for ($i=0, $n=count($rows); $i < $n; $i++) 
		{
	  		$row = &$rows[$i];
	  		$path = JPath::clean(Path::Combine(F2C_FileUpload::GetFilesRootPath(), "c$row->projectid/a$row->formid/f$ContentTypeFieldId"), DS);
	  		JFolder::delete($path);
		}					
	}
}

class F2C_Image
{
	function F2C_Image()
	{
	}
		
	function GetImagesUrl($projectId, $formId, $relative = false)
	{
		if($relative)
		{
			return "com_form2content/p$projectId/f$formId/" ;
		}
		else
		{
			return F2cUri::GetClientRoot()."images/stories/com_form2content/p$projectId/f$formId/" ;			
		}
	}
	
	function GetThumbnailsUrl($projectId, $formId, $relative = false)
	{
		return Path::Combine(F2C_Image::GetImagesUrl($projectId, $formId, $relative), 'thumbs');	
	}

	function GetImagesRootPath($relative = false)
	{
		if($relative)
		{
			return 'com_form2content/';
		}
		else
		{
			return JPATH_SITE.DS.'images/stories/com_form2content/';
		}				
	}
	
	function GetImagesPath($projectId, $formId, $relative = false)
	{
		return Path::Combine(F2C_Image::GetImagesRootPath($relative), 'p'.$projectId.'/'.'f'.$formId);
	}

	function GetThumbnailsPath($projectId, $formId, $relative = false)
	{
		return Path::Combine(F2C_Image::GetImagesPath($projectId, $formId, $relative), 'thumbs');	
	}

	function CreateFullImageName($uploadFilename, $fieldId)
	{
		if(!$uploadFilename)
		{
			return '';
		}	
		
		return $fieldId . '.' . JFile::getExt($uploadFilename);
	}
	
	function CreateThumbnailImageName($uploadFilename, $fieldId)
	{
		if(!$uploadFilename)
		{
			return '';
		}	
		
		return $fieldId . '.' . JFile::getExt($uploadFilename);		
	}	

	function Delete($projectId, $formId, $filename)
	{
		// delete thumbnail
		$img = Path::Combine($this->GetThumbnailsPath($projectId, $formId), $filename);
	
		if(JFile::exists($img))
		{
			JFile::delete($img);
		}

		// delete image
		$img = Path::Combine($this->GetImagesPath($projectId, $formId), $filename);
	
		if(JFile::exists($img))
		{
			JFile::delete($img);
		}
	}

	function deleteContentTypeFieldImages($ContentTypeFieldId)
	{
		$db =& JFactory::getDBO(); 
	
		$sql =	"SELECT pfl.projectid, fct.formid, fct.content " .
				"FROM #__f2c_projectfields pfl " .
				"INNER JOIN #__f2c_fieldcontent fct ON pfl.id = fct.fieldid " .
				"WHERE pfl.fieldtypeid = " . F2C_FIELDTYPE_IMAGE . " AND pfl.id = $ContentTypeFieldId";

		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		
		for ($i=0, $n=count($rows); $i < $n; $i++) 
		{
	  		$row = &$rows[$i];
			  		
	  		if($row->content)
	  		{
	  			$imageData = new JRegistry();
	  			$imageData->loadString($row->content);
	  		
	  			$imageFile = Path::Combine(F2C_Image::GetImagesPath($row->projectid, $row->formid), $imageData->get('filename'));
	  			$thumbNailFile = Path::Combine(F2C_Image::GetThumbnailsPath($row->projectid, $row->formid), $imageData->get('filename'));
	  		
	  			if(JFile::exists($imageFile)) JFile::delete($imageFile);
	  			if(JFile::exists($thumbNailFile)) JFile::delete($thumbNailFile);
	  		}
		}					
	}	
}

class F2cImageGallery
{
	private function getBase($url)
	{
		return ($url ? F2cUri::GetClientRoot() : JPATH_SITE) . 'images/stories/com_form2content/';
	}
	
	/*
	 * When there has no form been created yet, images are stored in a temp location.
	 * This is also the case for new images.
	 */
	function getGalleryBaseUrl($formId, $projectId, $fieldId, $newImage = false)
	{
		$base = $this->getBase(true);
		
		if($formId && !$newImage)
		{
			$base .= 'p'.$projectId.'/f'.$formId.'/gallery'.$fieldId;
		}
		else
		{
		}
		
		return $base . '/thumbs/';
	}
	
	function getGalleryTmpBase()
	{
		$session 	= JFactory::getSession();
		return $this->getBase(false) . 'gallerytmp' . DS . 'c' . $projectId . DS . $session->getId();  
	}
}

class F2cFieldData
{
	var $id;
	var $fieldname;
	var $title;
	var $fieldtypeid;
	var $settings;
	var $description;
	var $projectid;
	var $ordering;
	var $frontvisible;
	var $values = array();
	var $internal = array();
}

class F2cEventArgs extends JObject
{
	var $action 			= null;
	var $isNew 				= false;
	var $formOld 			= null;
	var $fieldsOld 			= null;
	var $formNew 			= null;
	var $fieldsNew 			= null;
	var $parsedIntroContent = null;
	var $parsedMainContent 	= null;
}
?>