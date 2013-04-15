<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'class.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'utils.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

defined('F2C_ENGINE_UNDEFINED')		or define('F2C_ENGINE_UNDEFINED', -1);
defined('F2C_ENGINE_PATTEMPLATE')	or define('F2C_ENGINE_PATTEMPLATE', 0);
defined('F2C_ENGINE_SMARTY')		or define('F2C_ENGINE_SMARTY', 1);

defined('F2C_TEMPLATE_INTRO')		or define('F2C_TEMPLATE_INTRO', 0);
defined('F2C_TEMPLATE_MAIN')		or define('F2C_TEMPLATE_MAIN', 1);

class F2cParser extends JObject
{
	var $_engineType = F2C_ENGINE_UNDEFINED;
	var $_parser = null;
	
	private function _detectEngine($templateFile)
	{
		$sample = JFile::Read($templateFile, false, 9);
		
		if(strtolower($sample) == '<mos:tmpl' ||
		   strtolower(substr($sample, 3)) == '<mos:t')
		{
			return F2C_ENGINE_PATTEMPLATE;
		}
		else
		{
			return F2C_ENGINE_SMARTY;
		}		
	}
	
	public function addTemplate($templateName, $templateType)
	{
		$filename = Path::Combine(F2cFactory::getConfig()->get('template_path'), $templateName);

		if(!JFile::exists($filename))
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_NOT_FOUND'));
			return false;
		}

		$engineType = $this->_detectEngine($filename);
		
		if($templateType == F2C_TEMPLATE_INTRO)
		{
			$this->_engineType = $engineType;
		}
		else
		{
			if($this->_engineType != $engineType)
			{
				$this->SetError('COM_FORM2CONTENT_ERROR_TEMPLATES_DIFFERENT_ENGINES');
				return false;
			}
		}		 

		if(!$this->_parser)
		{
			switch($this->_engineType)
			{
				case F2C_ENGINE_SMARTY:
					if (!class_exists('F2C_Smarty')) 
					{
						require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'smarty.form2content.php');
					}					
					$this->_parser = new F2C_Smarty();
					break;
				default:
					$this->SetError('COM_FORM2CONTENT_ERROR_UNSUPPORTED_TEMPLATE_ENGINE');
					return false;
					break;
			}
		}

		if(!$this->_parser->addTemplate($templateName, $templateType))
		{
			$this->setError($this->_parser->error);
			return false;
		}
		
		return true;	
	}
	
	public function clearTemplates()
	{
		$this->_parser->clearTemplates();
	}
	
	public function addVar($name, $value)
	{
		$this->_parser->addVar($name, $value);
	}

	public function clearVar($name)
	{
		$this->_parser->clearVar($name);
	}
	
	public function addVars($form)
	{
		// Clear all previous vars
		$this->_parser->clearAllVars();
		
		$this->_parser->form = $form;
		
		$app				= JFactory::getApplication();
		$usrTmp 			= JFactory::getUser($form->created_by);
		$db 				=& JFactory::getDBO();
		$dateFormat			= str_replace('%', '', F2cFactory::getConfig()->get('date_format') . ' H:i:s');
		$nullDate 			= $db->getNullDate();
		$joomlaId 			= $form->reference_id;
		$createdUnix		= ''; // Unix timestamp
		$modifiedUnix		= ''; // Unix timestamp
		$publishUpUnix		= ''; // Unix timestamp
		$publishDownUnix	= ''; // Unix timestamp
		$startPublishing 	= '';
		$finishPublishing 	= '';		
		$creationDate 		=  JHTML::_('date', $form->created, $dateFormat);
		$modifiedDate 		=  ($form->modified != '' && $form->modified != $nullDate) ? JHTML::_('date', $form->modified, $dateFormat) : '';
		$dateCreated		= new JDate($form->created);
		
		if($form->created != $nullDate)
		{
			$tmp 			= new JDate($form->created);
			$createdUnix 	= $tmp->toUnix(); 
		}
		
		if($form->modified != $nullDate)
		{
			$tmp 			= new JDate($form->modified);
			$modifiedUnix 	= $tmp->toUnix(); 
		}
		
		if($form->publish_up != $nullDate)
		{
			$startPublishing 	= JHTML::_('date', $form->publish_up, $dateFormat, true, true);
			$tmp 				= new JDate($form->publish_up);
			$publishUpUnix 		= $tmp->toUnix(); 
		}
	
		if($form->publish_down != $nullDate)
		{
			$finishPublishing 	= JHTML::_('date', $form->publish_down, $dateFormat);
			$tmp 				= new JDate($form->publish_down);
			$publishDownUnix	= $tmp->toUnix(); 
		}
		
		$slug 		= ($form->alias) ? $joomlaId.':'.$form->alias : $joomlaId;
		$catslug 	= ($form->catAlias) ? $form->catid.':'.$form->catAlias : $form->catid;
		$link 		= 'index.php?option=com_content&view=article&id='. $slug . '&catid=' . $catslug;
		
		$this->_parser->addVar('JOOMLA_ID', $joomlaId);
		$this->_parser->addVar('JOOMLA_ARTICLE_LINK', $link);
		$this->_parser->addVar('JOOMLA_ARTICLE_LINK_SEF', '{plgContentF2cSef}'.$slug.','.$catslug.'{/plgContentF2cSef}');
		$this->_parser->addVar('JOOMLA_CATEGORY_ID', $form->catid);
		$this->_parser->addVar('JOOMLA_CATEGORY_TITLE', $form->catTitle);
		$this->_parser->addVar('JOOMLA_CATEGORY_ALIAS', $form->catAlias);
		$this->_parser->addVar('JOOMLA_PUBLISH_UP', $startPublishing);
		$this->_parser->addVar('JOOMLA_PUBLISH_UP_RAW', $publishUpUnix);	
		$this->_parser->addVar('JOOMLA_PUBLISH_DOWN', $finishPublishing);	
		$this->_parser->addVar('JOOMLA_PUBLISH_DOWN_RAW', $publishDownUnix);	
		$this->_parser->addVar('JOOMLA_CREATED', $creationDate);
		$this->_parser->addVar('JOOMLA_CREATED_RAW', $createdUnix);	
		$this->_parser->addVar('JOOMLA_MODIFIED', $modifiedDate);			
		$this->_parser->addVar('JOOMLA_MODIFIED_RAW', $modifiedUnix);	
		$this->_parser->addVar('JOOMLA_TITLE', HtmlHelper::stringHTMLSafe($form->title));
		$this->_parser->addVar('JOOMLA_TITLE_RAW', $form->title);
		$this->_parser->addVar('JOOMLA_TITLE_ALIAS', HtmlHelper::stringHTMLSafe($form->alias));
		$this->_parser->addVar('JOOMLA_META_KEYWORDS', HtmlHelper::stringHTMLSafe($form->metakey));
		$this->_parser->addVar('JOOMLA_META_DESCRIPTION', HtmlHelper::stringHTMLSafe($form->metadesc));
		$this->_parser->addVar('JOOMLA_AUTHOR', HtmlHelper::stringHTMLSafe($usrTmp->name));
		$this->_parser->addVar('JOOMLA_AUTHOR_USERNAME', HtmlHelper::stringHTMLSafe($usrTmp->username));
		$this->_parser->addVar('JOOMLA_AUTHOR_EMAIL', $usrTmp->email);	
		$this->_parser->addVar('JOOMLA_AUTHOR_ID', $form->created_by);
		$this->_parser->addVar('JOOMLA_AUTHOR_ALIAS', $form->created_by_alias);
		$this->_parser->addVar('JOOMLA_LANGUAGE', $form->language);
		
		// Add F2C parameters to template
		$this->_parser->addVar('F2C_ID', $form->id);		
		$this->_parser->addVar('F2C_IMAGES_PATH_THUMBS_RELATIVE', F2C_Image::GetThumbnailsPath($form->projectid, $form->id, true)); 
		$this->_parser->addVar('F2C_IMAGES_PATH_THUMBS_ABSOLUTE', F2C_Image::GetThumbnailsPath($form->projectid, $form->id, false)); 
		$this->_parser->addVar('F2C_IMAGES_PATH_RELATIVE', F2C_Image::GetImagesPath($form->projectid, $form->id, true)); 
		$this->_parser->addVar('F2C_IMAGES_PATH_ABSOLUTE', F2C_Image::GetImagesPath($form->projectid, $form->id, false)); 
		
		foreach($form->fields as $field)
		{
			$this->_parser->addFormVar($field);
		}
		
		return true;
	}
	
	public function parseIntro()
	{
		$content = $this->_parser->parseIntro();
		$this->setError($this->_parser->error);
		return $content;
	}

	public function parseMain()
	{
		$content = $this->_parser->parseMain();
		$this->setError($this->_parser->error);
		return $content;
	}
	
	public function getTemplateVars($formVars, &$usedVars)
	{
		$this->_parser->getTemplateVars($formVars, $usedVars);
	}
	
	public function getPossibleTemplateVars($contentTypeFields)
	{
		return $this->_parser->getPossibleTemplateVars($contentTypeFields);
	}
}
?>