<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'project.php');
require_once JPATH_COMPONENT.DS.'helpers'.DS.'form2content.php';
require_once JPATH_COMPONENT.DS.'utils.form2content.php';

jimport('joomla.application.component.view');

class Form2ContentViewForms extends JView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $nullDate;
	protected $menuParms;
	protected $activeMenu;
	protected $params;
	protected $categoryOptions = array();
	
	function display($tpl = null)
	{
		$app				=& JFactory::getApplication();
		$menu				=& JSite::getMenu();
		$this->activeMenu	= $menu->getActive();
		$db					= $this->get('Dbo');
		$this->state		= $this->get('State');
		$this->params		= $app->getParams();
		$this->nullDate		= $db->getNullDate();
		
		$this->getMenuParameters();
		
		$contentTypeId	= $this->activeMenu->params->get('contenttypeid');
		$model 			=& $this->getModel();
		
		$model->setState('ContentTypeId', $contentTypeId);		
		
		// Verify that the Content Type exists
		$contentTypeModel = new Form2ContentModelProject();
		
		if(!($contentType = $contentTypeModel->getItem($contentTypeId)))
		{
			JError::raiseWarning(800, sprintf(JText::_('COM_FORM2CONTENT_ERROR_ARTICLE_MANAGER_UNKNOWN_CONTENT_TYPE'), $contentTypeId));
			return false;
		}		
		
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');		
		
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true) . '/media/com_form2content/css/f2cfrontend.css');
		
		$modelContentType = new Form2ContentModelProject();
		$contentType = $modelContentType->getItem($this->menuParms->get('contenttypeid'));
		$contentTypeSettings = new JRegistry();
		$contentTypeSettings->loadArray($contentType->settings);
		
		// get the category Options
		$defaultCategoryId = (int)$contentTypeSettings->get('catid');
		
		if($defaultCategoryId != -1)
		{
			if((int)$contentTypeSettings->get('cat_behaviour') == 0)
			{
				// The category is fixed
				$this->categoryOptions = Form2ContentHelper::getCategoryList(2, 'com_content', $defaultCategoryId);
			}
			else
			{
				// The category is the root category
				$this->categoryOptions = Form2ContentHelper::getCategoryList(1, 'com_content', $defaultCategoryId);
			}
		}
		else
		{
			// Get all categories
			$this->categoryOptions = Form2ContentHelper::getCategoryList();
		}		
		
		// Set the page title
		$document->setTitle(HtmlHelper::getPageTitle($this->params->get('page_title', '')));
		
		parent::display($tpl);
	}
	
	protected function addToolbar()
	{
		$canDo	= Form2ContentHelper::getActions($this->state->get('filter.category_id'));
		
		JToolBarHelper::title(JText::_('COM_FORM2CONTENT_FORM2CONTENT') . ': ' . JText::_('COM_FORM2CONTENT_FORMS'), 'generic.png');		
	}
	
	private function getMenuParameters()
	{
		$this->menuParms	= new JRegistry();
		$contentTypeId		= $this->activeMenu->params->get('contenttypeid');	
		$canDo				= Form2ContentHelper::getActions($contentTypeId);		
		
		$this->menuParms->set('show_published_filter', $this->activeMenu->params->get('show_published_filter', 0));
		$this->menuParms->set('show_category_filter', $this->activeMenu->params->get('show_category_filter', 1));
		$this->menuParms->set('show_search_filter', $this->activeMenu->params->get('show_search_filter', 1));
		$this->menuParms->set('contenttypeid', $contentTypeId);
		
		switch($this->activeMenu->params->get('show_category_ordering',1))
		{
			case 0:
				$this->menuParms->set('show_category', 0);
				$this->menuParms->set('show_ordering', 0);
				break;
			case 1:
				$this->menuParms->set('show_category', 1);
				$this->menuParms->set('show_ordering', 1);
				break;
			case 2:
				$this->menuParms->set('show_category', 1);
				$this->menuParms->set('show_ordering', 0);
				break;
		}
		
		if ($canDo->get('core.create') && $this->activeMenu->params->get('show_new_button',1)) 
		{
			$this->menuParms->set('show_new_button', 1);
		}
		else
		{
			$this->menuParms->set('show_new_button', 0);
		}
		
		if ($canDo->get('core.create') && $this->activeMenu->params->get('show_copy_button',1)) 
		{
			$this->menuParms->set('show_copy_button', 1);
		}
		else
		{
			$this->menuParms->set('show_copy_button', 0);
		}
		
		if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) 
		{
			if($this->activeMenu->params->get('show_edit_button',1))
			{
				$this->menuParms->set('show_edit_button', 1);
			}
			else 
			{
				$this->menuParms->set('show_edit_button', 0);
			}
		}
		else
		{
			$this->menuParms->set('show_edit_button', 0);
		}
		
		if ($canDo->get('core.edit.state') || $canDo->get('form2content.edit.state.own'))
		{
			$this->menuParms->set('show_publish_button', 1);
		}
		else
		{
			$this->menuParms->set('show_publish_button', 0);
		}
				
		if($this->activeMenu->params->get('show_delete_button',1))
		{ 
			if ((int)$this->state->get('forms.filter.published') == (int)F2C_STATE_TRASH)
			{
					$this->menuParms->set('show_empty_trash_button', $canDo->get('core.delete') || $canDo->get('form2content.delete.own'));
					$this->menuParms->set('show_delete_button', 0);			
			}
			else
			{
				$this->menuParms->set('show_empty_trash_button', 0);
				$this->menuParms->set('show_delete_button', $canDo->get('form2content.trash') || $canDo->get('form2content.trash.own'));				
			}
		}
		else 
		{
			$this->menuParms->set('show_empty_trash_button', 0);
			$this->menuParms->set('show_delete_button', 0);						
		}
		
		$this->menuParms->set('show_created_column', $this->activeMenu->params->get('show_created', 1));
		$this->menuParms->set('show_modified_column', $this->activeMenu->params->get('show_modified', 1));
		$this->menuParms->set('show_author_column', $this->activeMenu->params->get('show_author', 1));
		$this->menuParms->set('show_published_column', $this->activeMenu->params->get('show_published', 1));
		$this->menuParms->set('show_language_column', $this->activeMenu->params->get('show_language', 1));
	}
}
?>