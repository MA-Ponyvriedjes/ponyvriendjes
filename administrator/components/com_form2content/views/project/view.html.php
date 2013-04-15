<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

class Form2ContentViewProject extends JView
{
	protected $form;
	protected $item;
	protected $state;
	
	function display($tpl = null)
	{
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

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
		$isNew = ($this->item->id == 0);
	
		JRequest::setVar('hidemainmenu', true);

		JToolBarHelper::title(JText::_('COM_FORM2CONTENT_CONTENTTYPE_'.($isNew ? 'ADD' : 'EDIT')), 'article-add.png');
		
		// Built the actions for new and existing records.
		if ($isNew)  
		{
			JToolBarHelper::apply('project.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('project.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CANCEL');
		}
		else 
		{
			JToolBarHelper::apply('project.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('project.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::cancel('project.cancel', 'JTOOLBAR_CLOSE');
		}		
	}	
}
?>