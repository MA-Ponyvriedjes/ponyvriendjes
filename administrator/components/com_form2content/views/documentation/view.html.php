<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

class Form2ContentViewDocumentation extends JView
{
	function display($tpl = null)
	{
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		$title = JText::_('COM_FORM2CONTENT_FORM2CONTENT') . ': ' . JText::_('COM_FORM2CONTENT_DOCUMENTATION');			
		JToolBarHelper::title($title, 'generic.png');		
	}
}

?>