<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

class Form2ContentModelForms extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) 
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'catid', 'a.catid', 'cc.title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'modified', 'a.modified',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'p.title'
			);
		}

		parent::__construct($config);
	}
	
	protected function populateState()
	{
		// Initialise variables.
		$search = $this->getUserStateFromRequest($this->context.'.forms.filter.search', 'forms_filter_search');
		$this->setState('forms.filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.forms.filter.published', 'forms_filter_published');
		$this->setState('forms.filter.published', $published);
		
		$categoryId = $this->getUserStateFromRequest($this->context.'.forms.filter.category_id', 'forms_filter_category_id');
		$this->setState('forms.filter.category_id', $categoryId);

		// List state information.
		parent::populateState('a.title', 'asc');
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('forms.filter.search');
		$id	.= ':'.$this->getState('forms.filter.published');
		$id	.= ':'.$this->getState('forms.filter.category_id');

		return parent::getStoreId($id);
	}
	
	protected function getListQuery()
	{
		$user 		=& JFactory::getUser();
		$db 		= $this->getDbo();
		$query 		= $db->getQuery(true);
		$menu		=& JSite::getMenu();
		$activeMenu	= $menu->getActive();
		
		$query->select('a.*');
		$query->from('`#__f2c_form` AS a');
		
		// Join over the content for the Joomla article.
		$query->select('c.state, c.publish_up as publish_up_c, c.publish_down as publish_down_c');
		$query->join('LEFT', '`#__content` c ON a.reference_id = c.id');

		// Join over the users for the author.
		$query->select('u.username as creator');
		$query->join('LEFT', '`#__users` u ON a.created_by = u.id');

		// Join over the project to ensure only published projects.
		$query->join('INNER', '`#__f2c_project` p ON a.projectid = p.id AND p.published = 1');

		// Join over the category for the category information.
		$query->select('cc.title AS name');
		$query->join('LEFT', '`#__categories` AS cc ON a.catid = cc.id');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');
		
		// Filter by published state
		if($activeMenu->params->get('show_published_filter', 0))
		{
			// State filter is visible
			$published = $this->getState('forms.filter.published');
		
			if (is_numeric($published)) 
			{	
				$query->where('a.state = ' . (int) $published);
			}
			else if ($published == '') 
			{
				$query->where('(a.state = '.F2C_STATE_UNPUBLISHED.' OR a.state = '.F2C_STATE_PUBLISHED.')');
			}
		}
		else
		{
			// State filter is not visible
			$query->where('(a.state = '.F2C_STATE_UNPUBLISHED.' OR a.state = '.F2C_STATE_PUBLISHED.')');
		}	
					
		// Filter by search in title.
		$search = $this->getState('forms.filter.search');
		
		// Search filter
		if(!empty($search)) 
		{
			$query->where('(LOWER(a.title) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false ) .
						  ' OR c.id = ' . (int) $search . ')');
		}

		// Category filter
		$categoryId = $this->getState('forms.filter.category_id');
		
		if ($categoryId > 0) 
		{
			$query->where('(a.catid = '.(int)$categoryId.')');
		}

		// Content Type filter
		$query->where('(projectid = '.(int)$this->getState('ContentTypeId').')');

		// Own articles only?
		if($activeMenu->params->get('show_own_items_only', 0)) // own items
		{
			$query->where('a.created_by = ' . $user->id);
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');

		if ($orderCol == 'a.ordering' || $orderCol == 'name') // category_title
		{
			$orderCol = 'name '.$orderDirn.', a.ordering';
		}
		
		$query->order($db->getEscaped($orderCol.' '.$orderDirn));

		return $query;
	}
}
?>
