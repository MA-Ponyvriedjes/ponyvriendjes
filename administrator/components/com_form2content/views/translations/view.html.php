<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DS.'shared.form2content.php');

jimport('joomla.language.helper');
jimport('joomla.application.component.view');

class Form2ContentViewTranslations extends JView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $translationStateOptions;
	protected $contentTypes;
	
	function display($tpl = null)
	{
		if (!JFactory::getUser()->authorise('core.admin')) 
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		$this->items					= $this->get('Items');
		$this->pagination				= $this->get('Pagination');
		$this->state					= $this->get('State');
		$this->translationStateOptions	= $this->get('translationStateOptions');
		$this->contentTypes				= $this->get('contentTypes');
		
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
		JToolBarHelper::title(JText::_('COM_FORM2CONTENT_FORM2CONTENT') . ': ' . JText::_('COM_FORM2CONTENT_TRANSLATIONS'), 'article.png');
		JToolBarHelper::editList('translation.edit','JTOOLBAR_EDIT');
		JToolBarHelper::trash('translations.delete','JTOOLBAR_TRASH');
	}
}
?>