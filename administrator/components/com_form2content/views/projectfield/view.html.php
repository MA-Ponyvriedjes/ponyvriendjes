<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

require_once(JPATH_COMPONENT.DS.'models'.DS.'project.php');
require_once(JPATH_COMPONENT_SITE.DS.'shared.form2content.php');
require_once(JPATH_COMPONENT_SITE.DS.'class.form2content.php');

class Form2ContentViewProjectField extends JView
{
	protected $form;
	protected $item;	
	protected $state;
	
	function display($tpl = null)
	{
		$this->form			= $this->get('Form');
		$this->item			= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$document = JFactory::getDocument();
		$document->addStyleSheet('../media/com_form2content/css/f2cfields.css');
		
		$this->addToolbar();
		
		parent::display($tpl);		
	}
	
	protected function addToolbar()
	{
		$modelContentType = new Form2ContentModelProject();
		$contentType = $modelContentType->getItem($this->item->projectid);
		
		$isNew = ($this->item->id == 0);
		$formTitle = JText::_('COM_FORM2CONTENT_CONTENTTYPE_FIELDS_MANAGER') . ' : ';
		$formTitle .= $isNew ? JText::_('COM_FORM2CONTENT_NEW') : JText::_('COM_FORM2CONTENT_EDIT') . ' ';
		$formTitle .= JText::_('COM_FORM2CONTENT_PROJECTFIELD') . ' - ' . $contentType->title;
		
		JToolBarHelper::title($formTitle);
		JToolBarHelper::save('projectfield.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::apply('projectfield.apply', 'JTOOLBAR_APPLY');
		
		if ($isNew)  
		{
			JToolBarHelper::cancel('projectfield.cancel', 'JTOOLBAR_CANCEL');
		} 
		else 
		{
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel('projectfield.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
?>