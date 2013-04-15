<?php
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldF2cCategory extends JFormFieldList
{
	public $type = 'F2cCategory';

	protected function getOptions()
	{
		// Initialise variables.
		$options		= array();
		$extension		= $this->element['extension'] ? (string) $this->element['extension'] : (string) $this->element['scope'];
		$published		= (string)$this->element['published'];
		$rootElementId	= $this->element['rootElementId'];

		// Load the category options for a given extension.
		if (!empty($extension)) 
		{
			// Filter over published state or not depending upon if it is present.
			if ($published) 
			{
				$options = $this->options($extension, $rootElementId, array('filter.published' => explode(',', $published)));
			}
			else 
			{
				$options = $this->options($extension, $rootElementId);
			}

			// Verify permissions.  If the action attribute is set, then we scan the options.
			if ($action	= (string) $this->element['action']) 
			{

				// Get the current user object.
				$user = JFactory::getUser();
			
				foreach($options as $i => $option)
				{
					// To take save or create in a category you need to have create rights for that category
					// unless the item is already in that category.
					// Unset the option if the user isn't authorised for it. In this field assets are always categories.
					if ($user->authorise('core.create', $extension.'.category.'.$option->value) != true ) {
						unset($options[$i]);
					}
				}
				
			}

			if (isset($this->element['show_root'])) {
				array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
			}
		}
		else 
		{
			JError::raiseWarning(500, JText::_('JLIB_FORM_ERROR_FIELDS_CATEGORY_ERROR_EXTENSION_EMPTY'));
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
	
	public function options($extension, $rootElementId, $config = array('filter.published' => array(0,1)))
	{
		$config		= (array)$config;
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$options	= array();
		
		$query->select('a.id, a.title, a.level');
		$query->from('#__categories AS a');
		$query->where('a.parent_id > 0');

		// Filter on extension.
		$query->where('extension = '.$db->quote($extension));

		// Filter on the published state
		if (isset($config['filter.published'])) 
		{
			if (is_numeric($config['filter.published'])) 
			{
				$query->where('a.published = '.(int) $config['filter.published']);
			} 
			else if (is_array($config['filter.published'])) 
			{
				JArrayHelper::toInteger($config['filter.published']);
				$query->where('a.published IN ('.implode(',', $config['filter.published']).')');
			}
		}

		if($rootElementId != '')
		{
			$queryRootElement = $db->getQuery(true);
			$queryRootElement->select('a.lft, a.rgt');
			$queryRootElement->from('#__categories AS a');
			$queryRootElement->where('a.id = ' . (int)$rootElementId);
			
			$db->setQuery($queryRootElement);
			$rootElement = $db->loadObject();
			
			$query->where('a.lft > ' . (int)$rootElement->lft);
			$query->where('a.rgt < ' . (int)$rootElement->rgt);
		}

		$query->order('a.lft');
		$db->setQuery($query);
		$items = $db->loadObjectList();

		// Assemble the list options.
		foreach ($items as &$item) 
		{
			$repeat = ( $item->level - 1 >= 0 ) ? $item->level - 1 : 0;
			$item->title = str_repeat('- ', $repeat).$item->title;
			$options[] = JHtml::_('select.option', $item->id, $item->title);
		}

		return $options;
	}	
}