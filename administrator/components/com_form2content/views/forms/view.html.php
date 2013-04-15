<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

class Form2ContentViewForms extends JView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $nullDate;
	protected $authors;
	
	function display($tpl = null)
	{
		$db					= $this->get('Dbo');		
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->contentTypes	= $this->get('ContentTypes');
		$this->authors		= $this->get('Authors');
		$this->nullDate		= $db->getNullDate();
		
		$this->addToolbar();
		
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		$canDo	= Form2ContentHelperAdmin::getActions($this->state->get('filter.category_id'));
		
		JToolBarHelper::title(JText::_('COM_FORM2CONTENT_FORM2CONTENT') . ': ' . JText::_('COM_FORM2CONTENT_FORMS'), 'generic.png');
		
		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('form.projectselect','JTOOLBAR_NEW');
		}
		
		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) 
		{
			JToolBarHelper::editList('form.edit','JTOOLBAR_EDIT');
		}
		
		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::customX('forms.copy', 'copy.png', 'copy_f2.png', 'Copy');
		}
			
		if ($canDo->get('core.edit.state') || $canDo->get('form2content.edit.state.own')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::custom('forms.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('forms.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::custom('forms.refresh', 'refresh', 'refresh', JText::_('COM_FORM2CONTENT_REFRESH'), true);
		}
		
		if ($this->state->get('forms.filter.published') == F2C_STATE_TRASH && ($canDo->get('core.delete') || $canDo->get('form2content.delete.own'))) 
		{
			JToolBarHelper::deleteList('', 'forms.delete','JTOOLBAR_EMPTY_TRASH');
		}
		else if ($canDo->get('form2content.trash') || $canDo->get('form2content.trash.own')) 
		{
			JToolBarHelper::trash('forms.trash','JTOOLBAR_TRASH');
		}
		
		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();		
			JToolBarHelper::preferences('com_form2content', 550, 800);			
			JToolBarHelper::custom('forms.export', 'export', 'export', JText::_('COM_FORM2CONTENT_EXPORT'), true);
		}		
	}
}
?>