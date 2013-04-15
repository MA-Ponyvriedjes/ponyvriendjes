<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

class Form2ContentViewTemplates extends JView
{
	protected $items;
	
	function display($tpl = null)
	{
		// Authorization check
		if (!JFactory::getUser()->authorise('core.admin')) 
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		$this->items = $this->get('Items');
	
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
	
		$this->addToolbar();
		
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_FORM2CONTENT_FORM2CONTENT') . ': ' . JText::_('COM_FORM2CONTENT_TEMPLATE_MANAGER'), 'generic.png');
		JToolBarHelper::custom('template.upload','upload','upload',JText::_('COM_FORM2CONTENT_UPLOAD'),false);
		JToolBarHelper::editList('template.edit','JTOOLBAR_EDIT');			
		JToolBarHelper::trash('template.delete','JTOOLBAR_TRASH');
	}
}
?>