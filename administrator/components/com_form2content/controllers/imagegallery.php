<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.controllerform');

require_once(JPATH_COMPONENT_SITE.DS.'utils.form2content.php');

class Form2ContentControllerImagegallery extends JControllerForm
{
	public function getModel($name = 'ImageGallery', $prefix = 'Form2ContentModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	public function upload()
	{
		$maxImageWidth		= 400;
		$maxImageHeight		= 400;
		$maxThumbHeight 	= 100;
		$maxThumbWidth 		= 100;
		$f2cConfig 			=& F2cFactory::getConfig();
		$upload				= JRequest::getVar('upload', array(), 'files', 'array');
		$formId				= JRequest::getInt('formid');
		$fieldId			= JRequest::getInt('fieldid');
		$contentTypeId		= JRequest::getInt('projectid');
		$galleryDir			= JPATH_SITE.DS.'images'.DS.'stories'.DS.'com_form2content';
		$galleryThumbsDir	= '';
		
		if($formId)
		{
			$galleryDir = Path::Combine($galleryDir, 'p'.$contentTypeId.DS.'f'.$formId.DS.'gallery'.$fieldId);
		}
		else 
		{
			$session 	= JFactory::getSession();
			$galleryDir = Path::Combine($galleryDir, 'gallerytmp'.DS.$session->getId());
		}
		
		$galleryThumbsDir = Path::Combine($galleryDir, 'thumbs');
		
		if(!JFolder::exists($galleryThumbsDir))
		{
			JFolder::create($galleryThumbsDir);
		}
		
		$fileName			= $upload['name'];
		$tmpFileLocation	= Path::Combine($galleryDir, '~'.$fileName);
		
		if(JFile::upload($upload['tmp_name'], $tmpFileLocation))
		{
			// Resize image
			if(!ImageHelper::ResizeImage($tmpFileLocation, Path::Combine($galleryDir ,$fileName), $maxImageWidth, $maxImageHeight, $f2cConfig->get('jpeg_quality', 75)))
			{
				JError::raiseError(401,JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
				return false;
			}
	
			// Resize thumbnail
			if(!ImageHelper::ResizeImage($tmpFileLocation, Path::Combine($galleryThumbsDir ,$fileName), $maxThumbWidth, $maxThumbHeight, $f2cConfig->get('jpeg_quality', 75)))
			{
				JError::raiseError(401,JText::_('COM_FORM2CONTENT_ERROR_IMAGE_RESIZE_FAILED'));
				return false;
			}
			
			// remove tmp image
			JFile::delete($tmpFileLocation);

			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration('window.addEvent(\'domready\', function()
										{ 
											addNewRow(\'t'.$fieldId.'\', \'\','.json_encode($fileName).','.$maxImageWidth.','.$maxImageHeight.','.$maxThumbWidth.','.$maxThumbHeight.'); 
										});');
		}
		
		parent::display();
	}
	
	function delete()
	{
		$f2cConfig 			=& F2cFactory::getConfig();
		$upload				= JRequest::getVar('upload', array(), 'files', 'array');
		$formId				= JRequest::getInt('formid');
		$contentTypeId		= JRequest::getInt('projectid');
		$fileName			= JRequest::getString('imageid');
		$galleryDir			= JPATH_SITE.DS.'images'.DS.'stories'.DS.'com_form2content';
		$galleryThumbsDir	= '';
		
		if($formId)
		{
			$galleryDir = Path::Combine($galleryDir, 'p'.$contentTypeId.DS.'f'.$formId.DS.'gallery'.$field->id);
		}
		else 
		{
			$session 	= JFactory::getSession();
			$galleryDir = Path::Combine($galleryDir, 'gallerytmp'.DS.$session->getId());
		}
		
		$galleryThumbsDir = Path::Combine($galleryDir, 'thumbs');
		
		if(JFile::exists(Path::Combine($galleryDir, $fileName)))
		{
			JFile::delete(Path::Combine($galleryDir, $fileName));
		}

		if(JFile::exists(Path::Combine($galleryThumbsDir, $fileName)))
		{
			JFile::delete(Path::Combine($galleryThumbsDir, $fileName));
		}		
	}
}
?>