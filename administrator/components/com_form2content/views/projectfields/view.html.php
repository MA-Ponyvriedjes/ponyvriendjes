<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

require_once(JPATH_COMPONENT.DS.'models'.DS.'project.php');

class Form2ContentViewProjectFields extends JView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $contentTypeId;

	function display($tpl = null)
	{
		if (!JFactory::getUser()->authorise('core.admin')) 
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		$this->items			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->contentTypeId	= JRequest::getInt('projectid');

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
		$modelContentType = new Form2ContentModelProject();
		$contentType = $modelContentType->getItem($this->contentTypeId);
		
		JToolBarHelper::title(JText::_('COM_FORM2CONTENT_FORM2CONTENT') . ': ' . JText::_('COM_FORM2CONTENT_PROJECTFIELDS') . ' - ' . $contentType->title, 'generic.png');
		JToolBarHelper::addNew('projectfield.add','JTOOLBAR_NEW');
		JToolBarHelper::custom('projectfield.projectselect', 'copy.png', 'copy_f2.png','COM_FORM2CONTENT_COPY', true);		
		JToolBarHelper::editList('projectfield.edit','JTOOLBAR_EDIT');
		JToolBarHelper::trash('projectfields.delete','JTOOLBAR_TRASH');
	}
}
?>