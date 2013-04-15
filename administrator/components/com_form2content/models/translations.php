<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

class Form2ContentModelTranslations extends JModelList
{
	protected function populateState()
	{
		// Initialise variables.
		$search = $this->getUserStateFromRequest($this->context.'.translations.filter.search', 'translations_filter_search');
		$this->setState('translations.filter.search', $search);

		$contentTypeId = $this->getUserStateFromRequest($this->context.'.translations.filter.contenttype_id', 'translations_filter_contenttype_id');
		$this->setState('translations.filter.contenttype_id', $contentTypeId);

		$language = $this->getUserStateFromRequest($this->context.'.translations.filter.language', 'translations_filter_language');
		$this->setState('translations.filter.language', $language);

		$translationState = $this->getUserStateFromRequest($this->context.'.translations.filter.translationstate', 'translations_filter_translationstate');
		$this->setState('translations.filter.translationstate', $translationState);
		
		// List state information.
		parent::populateState('a.title', 'asc');
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('translations.filter.search');
		$id	.= ':'.$this->getState('translations.filter.contenttype_id');
		$id	.= ':'.$this->getState('translations.filter.language');
		$id	.= ':'.$this->getState('translations.filter.translationstate');
		
		return parent::getStoreId($id);
	}
	
	protected function getListQuery()
	{
		$db 				= $this->getDbo();
		$query 				= $db->getQuery(true);
		
		$query->select('f.id as fieldid, f.fieldname, f.title as fieldtitle');
		$query->from('#__f2c_projectfields f');
		
		$query->select('p.title as projecttitle');
		$query->join('INNER', '#__f2c_project p on f.projectid = p.id');
		
		$query->select('l.lang_code');
		$query->join('LEFT', '#__languages l on 1 = 1');

		$query->select('t.id as translation_id, t.title_translation, t.modified');
		$query->join('LEFT', ' #__f2c_translation t on (f.id = t.reference_id AND l.lang_code = t.language_id)');
		
		$query->select('u.name AS modifier');
		$query->join('LEFT', '#__users AS u ON u.id = t.modified_by');
		$query->order('p.title, l.lang_code, f.title, f.fieldname ASC');
		
		if((int)$this->getState('translations.filter.contenttype_id') > 0)
		{
			$query->where('f.projectid = ' . (int)$this->getState('translations.filter.contenttype_id'));
		}
		
		if($this->getState('translations.filter.language') != '')
		{
			$query->where('l.lang_code = ' . $db->quote($this->getState('translations.filter.language')));
		}
		
		if($this->getState('translations.filter.translationstate') != '')
		{
			if($this->getState('translations.filter.translationstate') == '1')
			{
				$query->where('t.title_translation IS NOT NULL');	
			}
			else 
			{
				$query->where('t.title_translation IS NULL');					
			}
		}
		
		// Filter by search in title.
		$search = $this->getState('translations.filter.search');
		
		// Search filter
		if(!empty($search)) 
		{
			$query->where('LOWER(f.title) LIKE '.$db->Quote('%'.$db->getEscaped( $search, true ).'%', false));
		}

		return $query;
	}
	
	function _buildContentWhere()
	{
		$search				= $app->getUserStateFromRequest($option.'searchtranslations', 'search', '', 'string');
		$search				= JString::strtolower($search);

				if ($search) 
		{
			$where[] = 'LOWER(f.title) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		}
	}
	
	public function getContentTypes() 
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		// Construct the query
		$query->select('id AS value, title AS text');
		$query->from('#__f2c_project');
		$query->order('title');
		
		// Setup the query
		$db->setQuery($query->__toString());
		
		// Return the result
		return $db->loadObjectList();
	}

	public function getTranslationStateOptions()
	{
		$options = array();
		
		$options[] = JHTML::_('select.option',  '0', JText::_('COM_FORM2CONTENT_UNTRANSLATED_FIELDS' ), 'value', 'text');
		$options[] = JHTML::_('select.option',  '1', JText::_('COM_FORM2CONTENT_TRANSLATED_FIELDS' ), 'value', 'text');
		
		return $options;
	}
}
?>
