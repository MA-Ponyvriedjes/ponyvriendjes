<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

class Form2ContentViewProjects extends JView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $f2cConfig;
	
	function display($tpl = null)
	{
		if (!JFactory::getUser()->authorise('core.admin')) 
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->f2cConfig 	= F2cFactory::getConfig();
		
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
		JToolBarHelper::title(JText::_('COM_FORM2CONTENT_FORM2CONTENT') . ': ' . JText::_('COM_FORM2CONTENT_PROJECTS'), 'article.png');

		JToolBarHelper::addNew('project.add','JTOOLBAR_NEW');
		JToolBarHelper::editList('project.edit','JTOOLBAR_EDIT');
		JToolBarHelper::customX('projects.copy', 'copy.png', 'copy_f2.png', 'Copy');
		JToolBarHelper::divider();
		JToolBarHelper::custom('projects.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
		JToolBarHelper::custom('projects.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::divider();
		JToolBarHelper::trash('projects.delete','JTOOLBAR_TRASH');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_form2content', 550, 800);
		JToolBarHelper::custom('projects.syncorder', 'syncorder', 'syncorder','COM_FORM2CONTENT_SYNC_ORDER', false);		
		JToolBarHelper::custom('project.upload','upload','upload',JText::_('COM_FORM2CONTENT_UPLOAD'),false);
	}	
}
?>