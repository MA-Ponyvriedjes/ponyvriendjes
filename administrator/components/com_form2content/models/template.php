<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DS.'class.form2content.php');
require_once(JPATH_COMPONENT_SITE.DS.'shared.form2content.php');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

class Form2ContentModelTemplate extends JModel
{
	protected $text_prefix = 'COM_FORM2CONTENT';
	protected $id;
	
	public function getItem()
	{
		$this->id = JRequest::getString('id');
		
		if(!$this->id)
		{
			$cid = JRequest::getVar('cid', array(), '', 'array');

			if (!is_array($cid) || count($cid) < 1) 
			{
				JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
			}
			
			$this->id = $cid[0];
		}
	
		$templateFile = Path::Combine(F2cFactory::getConfig()->get('template_path'), $this->id);
		return JFile::read($templateFile);		
	}
	
	function save()
	{
		$filename = Path::Combine(F2cFactory::getConfig()->get('template_path'), JRequest::getVar('id'));
		$template = HtmlHelper::unquoteData(JRequest::getVar('template', '', 'post', 'string', JREQUEST_ALLOWRAW));
		
		if(HtmlHelper::detectUTF8($template))
		{
			// check if BOM is present
			$utf8bom = "\xEF\xBB\xBF";
			$pos = strpos($template, $utf8bom);
			
			if($pos === false)
			{
				$template = $utf8bom . $template;
			}
		}		
		 
		return JFile::write($filename, $template);
	}
	
	function delete($cid)
	{
		$result = true;
	
		foreach($cid as $id)
		{
			$file = Path::Combine(F2cFactory::getConfig()->get('template_path'), $id);
		
			if(!JFile::exists($file))
			{
				$this->setError(JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_NOT_FOUND'). ': '. $id);
				$result = false;
				break;
			}
			
			if(!JFile::delete($file))
			{
				$this->setError(JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_DELETE'). ': '. $id);
				$result = false;
			}
		}
		
		return $result;
	}
	
	function upload()
	{
		$file = JRequest::getVar('upload', '', 'files', 'array');
		
		if(empty($file['name'])) 
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_FILE_UPLOAD_EMPTY'));
			return false;
		}

		$templateFile = Path::Combine(F2cFactory::getConfig()->get('template_path'), strtolower($file['name']));
		
		if(JFile::exists($templateFile)) 
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_FILE_UPLOAD_EXISTS'));
			return false;
		}

		if(strtolower(JFile::getExt($templateFile)) != 'tpl')
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_FILE_UPLOAD_INVALID_FILE_TYPE'));
			return false;			
		}
		
		if(!JFile::upload($file['tmp_name'], $templateFile))
		{
			$this->setError(JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_FILE_UPLOAD_FAILED'));
			return false;			
		}

		JPath::setPermissions($templateFile);			
		return true;
	}	
}
?>