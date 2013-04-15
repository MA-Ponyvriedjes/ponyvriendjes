<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

class Form2ContentModelProjects extends JModelList
{
	protected function populateState()
	{
		// Initialise variables.
		$search = $this->getUserStateFromRequest($this->context.'.contenttypes.filter.search', 'contenttypes_filter_search');
		$this->setState('contenttypes.filter.search', $search);

		// List state information.
		parent::populateState('a.title', 'asc');
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('contenttypes.filter.search');

		return parent::getStoreId($id);
	}
	
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->from('`#__f2c_project` AS a');
		
		// Join over the users for the author.
		$query->select('u.name AS username');
		$query->join('LEFT', '`#__users` u ON a.created_by = u.id');

		// Filter by search in title.
		$search = $this->getState('contenttypes.filter.search');
		
		if(!empty($search)) 
		{
			$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
			$query->where('(a.title LIKE '.$search.')');
		}

		$query->order('a.title ASC');

		return $query;
	}	
}
?>
