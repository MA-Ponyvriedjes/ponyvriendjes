<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 2.1
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

/**
 * Extension Filters model
 *
 */
class AkeebaModelExtfilter extends JModel
{
	private $jversion;

	public function __construct( $options = array() )
	{
		jimport('joomla.filesystem.file');
		if(AKEEBA_JVERSION == '16') {
			$this->jversion = '1.6';
		} else {
			$this->jversion = '1.5';
		}

		parent::__construct($options);
	}

	/**
	 * Gets a list of installed non-core components
	 * @param bool $reload When true, it forces reloading the list (bust cache)
	 * @return array An array holding component information
	 * @access public
	 */
	public function &getComponents($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			// Get a list of components
			$db =& JFactory::getDBO();

			if($this->jversion == '1.5')
			{
				$query = 'SELECT *' .
						' FROM #__components' .
						' WHERE parent = 0' .
						' AND iscore = 0' .
						' ORDER BY iscore, name';
			}
			else
			{
				$query = 'SELECT *, `element` as `option`, `extension_id` as `id`' .
						' FROM #__extensions' .
						' WHERE `type` = \'component\'' .
						' AND `protected` = 0' .
						' ORDER BY name';
			}
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// Get a list of applied filters
			$filter = AEFactory::getFilterObject('components');

			$_data = array();

			$numRows = count($rows);
			for($i=0;$i < $numRows; $i++)
			{
				$row =& $rows[$i];
				$_data[] = array(
					'name'		=> $row->name,
					'root'		=> 'default',
					'item'		=> $row->option,
					'status'	=> $filter->isFiltered($row->option, 'default', 'components', 'all')
				);
			}
		}

		return $_data;
	}

	/**
	 * Toggles the filtering status for a component
	 * @param	string	$root	Filter root (for components it's always 'default')
	 * @param	string	$item	Component's option name (e.g. 'com_foobar')
	 */
	public function toggleComponentFilter($root, $item)
	{
		$filter = AEFactory::getFilterObject('components');
		$filter->toggle($root, $item, $newStatus);
		$filters =& AEFactory::getFilters();
		$filters->save();
	}

	/**
	 * Gets an array of all installed modules.
	 *
	 * @param bool $reload Force reload of the list when true
	 * @return array
	 * @access public
	 */
	public function &getModules($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			$db = &JFactory::getDBO();

			if($this->jversion == '1.5')
			{
			$query = 'SELECT module, client_id, title' .
					' FROM #__modules' .
					' WHERE module LIKE "mod_%" ' .
					' AND iscore = 0'.
					' GROUP BY module, client_id' .
					' ORDER BY client_id, module';
			} else {
				$query = 'SELECT *, `name` as `title`, `element` as `module`, `extension_id` as `id`' .
						' FROM #__extensions' .
						' WHERE `type` = \'module\'' .
						' AND `protected` = 0' .
						' ORDER BY name';
			}
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$_data = array();
			$filter =& AEFactory::getFilterObject('modules');

			$n = count($rows);
			for ($i = 0; $i < $n; $i ++) {
				$row = & $rows[$i];
				$root = ($row->client_id == 0) ? 'frontend' : 'backend';
				$_data[] = array(
					'name'		=> $row->title,
					'root'		=> $root,
					'item'		=> $row->module,
					'status'	=> $filter->isFiltered($row->module, $root, 'modules', 'all')
				);
			}
		}

		return $_data;
	}

	/**
	 * Toggles the filtering status for a module
	 * @param	string	$root	Filter root (frontend|backend)
	 * @param	string	$item	Modules's option name (e.g. 'mod_foobar')
	 */
	public function toggleModuleFilter($root, $item)
	{
		$filter =& AEFactory::getFilterObject('modules');
		$filter->toggle($root, $item, $newStatus);
		$filters =& AEFactory::getFilters();
		$filters->save();
	}

	public function &getPlugins($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			$db = &JFactory::getDBO();

			if($this->jversion == '1.5')
			{
				$query = 'SELECT id, name, folder, element, client_id' .
					' FROM #__plugins' .
					' WHERE iscore = 0 '.
					' ORDER BY client_id, folder, name';
			} else {
				$query = 'SELECT *, `name` as `title`, `element` as `module`, `extension_id` as `id`' .
						' FROM #__extensions' .
						' WHERE `type` = \'plugin\'' .
						' AND `protected` = 0' .
						' ORDER BY name';
			}
			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			$_data = array();

			$filter =& AEFactory::getFilterObject('plugins');
			$n = count($rows);
			for ($i = 0; $i < $n; $i ++) {
				$row = & $rows[$i];
				$_data[] = array(
					'name'		=> $row->name,
					'root'		=> $row->folder,
					'item'		=> $row->element,
					'status'	=> $filter->isFiltered($row->element, $row->folder, 'plugins', 'all')
				);
			}
		}

		return $_data;
	}

	/**
	 * Toggles the filtering status for a plugin
	 * @param	string	$root	Filter root (plugin type)
	 * @param	string	$item	Modules's option name (e.g. 'plg_foobar')
	 */
	public function togglePluginFilter($root, $item)
	{
		$filter =& AEFactory::getFilterObject('plugins');
		$filter->toggle($root, $item, $newStatus);
		$filters =& AEFactory::getFilters();
		$filters->save();
	}


	// ========================================================================
	// Languages filter interface
	// ========================================================================

	/**
	 * Returns an annotated list of all front-end or back-end languages
	 *
	 * @param bool $frontend If true returns front-end languages, if false returns back-end languages
	 * @return array The annotated languages array
	 */
	private function &_getAllLanguages($frontend = true)
	{
		static $_feLanguages;
		static $_beLanguages;

		if($frontend)
		{
			if(!$_feLanguages)
			{
				$_feLanguages = array();
				jimport( 'joomla.filesystem.folder' );

				// Get the site languages
				$langBDir = JLanguage::getLanguagePath(JPATH_SITE);
				$langDirs = JFolder::folders($langBDir);

				for ($i=0; $i < count($langDirs); $i++)
				{
					// Try to find VALID languages, by scanning and parsing their XML files
					$row = array();
					$row['language'] = $langDirs[$i];
					$row['basedir'] = $langBDir;
					$files = JFolder::files( $langBDir.'/'.$langDirs[$i], '^([-_A-Za-z]*)\.xml$' );
					foreach ($files as $file)
					{
						$data = JApplicationHelper::parseXMLLangMetaFile($langBDir.'/'.$langDirs[$i].'/'.$file);

						// If we didn't get valid data from the xml file, move on...
						if (!is_array($data)) {
							continue;
						}

						// Populate the row from the xml meta file
						foreach($data as $key => $value)
						{
							$row[$key] = $value;
						}

						$clientVals =& JApplicationHelper::getClientInfo(0);
						$lang = JComponentHelper::getParams('com_languages');
						$row['default'] = ( $lang->get($clientVals->name, 'en-GB') == basename( $row['language'] ) );
					}
					if(isset($row['default'])) $_feLanguages[] = $row;
				}
			}

			return $_feLanguages;
		}
		else
		{
			if(!$_beLanguages)
			{
				$_beLanguages = array();
				jimport( 'joomla.filesystem.folder' );

				// Get the site languages
				$langBDir = JLanguage::getLanguagePath(JPATH_ADMINISTRATOR);
				$langDirs = JFolder::folders($langBDir);

				for ($i=0; $i < count($langDirs); $i++)
				{
					// Try to find VALID languages, by scanning and parsing their XML files
					$row = array();
					$row['language'] = $langDirs[$i];
					$row['basedir'] = $langBDir;
					$files = JFolder::files( $langBDir.'/'.$langDirs[$i], '^([-_A-Za-z]*)\.xml$' );
					foreach ($files as $file)
					{
						$data = JApplicationHelper::parseXMLLangMetaFile($langBDir.'/'.$langDirs[$i].'/'.$file);

						// If we didn't get valid data from the xml file, move on...
						if (!is_array($data)) {
							continue;
						}

						// Populate the row from the xml meta file
						foreach($data as $key => $value)
						{
							$row[$key] = $value;
						}

						$clientVals =& JApplicationHelper::getClientInfo(1);
						$lang = JComponentHelper::getParams('com_languages');
						$row['default'] = ( $lang->get($clientVals->name, 'en-GB') == basename( $row['language'] ) );
					}
					if(isset($row['default'])) $_beLanguages[] = $row;
				}
			}

			return $_beLanguages;
		}
	}

	public function &getLanguages($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			$_data = array();

			$filter =& AEFactory::getFilterObject('languages');

			// Add non-default front-end languages
			$feLang =& $this->_getAllLanguages(true);
			if(count($feLang) > 0)
			{
				foreach($feLang as $lang)
				{
					if(!$lang['default'])
					{
						$lang['name'] = $lang['language'];
						$lang['item'] = $lang['language'];
						$lang['root'] = 'frontend';
						$lang['status'] = $filter->isFiltered($lang['language'], 'frontend', 'languages', 'all');
						$_data[] = $lang;
					}
				}
			}

			// Add non-default back-end languages
			$beLang =& $this->_getAllLanguages(false);
			if(count($beLang) > 0)
			{
				foreach($beLang as $lang)
				{
					if(!$lang['default'])
					{
						$lang['name'] = $lang['language'];
						$lang['item'] = $lang['language'];
						$lang['root'] = 'backend';
						$lang['status'] = $filter->isFiltered($lang['language'], 'backend', 'languages', 'all');
						$_data[] = $lang;
					}
				}
			}
		}

		return $_data;
	}

	public function toggleLanguageFilter($root, $item)
	{
		$filter =& AEFactory::getFilterObject('languages');
		$filter->toggle($root, $item, $newStatus);
		$filters =& AEFactory::getFilters();
		$filters->save();
	}


	/**
	 * Returns an annotated list of all front-end or back-end templates
	 *
	 * @param bool $frontend If true returns front-end templates, if false returns back-end templates
	 * @return array The annotated templates array
	 */
	private function &_getAllTemplates($frontend = true)
	{
		static $_feTemplates;
		static $_beTemplates;

		if($frontend)
		{
			if(!$_feTemplates)
			{
				$_feTemplates = array();
				jimport( 'joomla.filesystem.folder' );

				// Get the site languages
				$tempBDir = JPATH_SITE.'/templates';
				$tempDirs = JFolder::folders($tempBDir);

				// Get a list of the currently active templates
				$db =& $this->getDBO();
				if($this->jversion == '1.5')
				{
					$query = 'SELECT template' .
							' FROM #__templates_menu' .
							' WHERE 1';
				}
				else
				{
					$query = 'SELECT *, `element` as `template`, `extension_id` as `id`' .
							' FROM #__extensions' .
							' WHERE `type` = \'template\'' .
							' AND `protected` = 0' .
							' AND `client_id` = 0' .
							' ORDER BY name';
				}
				$db->setQuery($query);
				$activeList = $db->loadResultArray();

				for ($i=0; $i < count($tempDirs); $i++)
				{
					// Try to find VALID templates, by scanning and parsing their XML files
					$row = array();
					$row['template'] = $tempDirs[$i];
					$row['basedir'] = $tempBDir;
					$files = JFolder::files( $tempBDir.'/'.$tempDirs[$i], '.xml$' );
					foreach ($files as $file)
					{
						$data = JApplicationHelper::parseXMLInstallFile($tempBDir.'/'.$tempDirs[$i].'/'.$file);

						// If we didn't get valid data from the xml file, move on...
						if (!is_array($data)) {
							continue;
						}

						// Populate the row from the xml meta file
						foreach($data as $key => $value)
						{
							$row[$key] = $value;
						}

						$row['client_id'] = 0;
						$row['default'] = ( in_array($row['template'], $activeList) );
					}
					if(isset($row['default'])) $_feTemplates[] = $row;
				}
			}

			return $_feTemplates;
		}
		else
		{
			if(!$_beTemplates)
			{
				$_beTemplates = array();
				jimport( 'joomla.filesystem.folder' );

				// Get the site languages
				$tempBDir = JPATH_ADMINISTRATOR.'/templates';
				$tempDirs = JFolder::folders($tempBDir);

				// Get a list of the currently active templates
				$db =& $this->getDBO();
				if($this->jversion == '1.5')
				{
					$query = 'SELECT template' .
							' FROM #__templates_menu' .
							' WHERE 1';
				}
				else
				{
					$query = 'SELECT *, `element` as `template`, `extension_id` as `id`' .
							' FROM #__extensions' .
							' WHERE `type` = \'template\'' .
							' AND `protected` = 0' .
							' AND `client_id` = 1' .
							' ORDER BY name';
				}
				$db->setQuery($query);
				$activeList = $db->loadResultArray();

				for ($i=0; $i < count($tempDirs); $i++)
				{
					// Try to find VALID languages, by scanning and parsing their XML files
					$row = array();
					$row['template'] = $tempDirs[$i];
					$row['basedir'] = $tempBDir;
					$files = JFolder::files( $tempBDir.'/'.$tempDirs[$i], '.xml$' );
					foreach ($files as $file)
					{
						$data = JApplicationHelper::parseXMLInstallFile($tempBDir.'/'.$tempDirs[$i].'/'.$file);

						// If we didn't get valid data from the xml file, move on...
						if (!is_array($data)) {
							continue;
						}

						// Populate the row from the xml meta file
						foreach($data as $key => $value)
						{
							$row[$key] = $value;
						}

						$row['client_id'] = 1;
						$row['default'] = ( in_array($row['template'], $activeList) );
					}
					if(isset($row['default'])) $_beTemplates[] = $row;
				}
			}

			return $_beTemplates;
		}
	}

	public function &getTemplates($reload = false)
	{
		static $_data;

		if(!$_data || $reload)
		{
			$_data = array();

			$filter =& AEFactory::getFilterObject('templates');

			// Add non-default front-end templates
			$feTemp =& $this->_getAllTemplates(true);
			if(count($feTemp) > 0)
			{
				foreach($feTemp as $temp)
				{
					if(!$temp['default'])
					{
						$temp['name'] = $temp['template'];
						$temp['item'] = $temp['template'];
						$temp['root'] = 'frontend';
						$temp['status'] = $filter->isFiltered($temp['template'], 'frontend', 'templates', 'all');
						$_data[] = $temp;
					}
				}
			}

			// Add non-default back-end templates
			$beTemp =& $this->_getAllTemplates(false);
			if(count($beTemp) > 0)
			{
				foreach($beTemp as $temp)
				{
					if(!$temp['default'])
					{
						$temp['name'] = $temp['template'];
						$temp['item'] = $temp['template'];
						$temp['root'] = 'backend';
						$temp['status'] = $filter->isFiltered($temp['template'], 'backend', 'templates', 'all');
						$_data[] = $temp;
					}
				}
			}
		}

		return $_data;
	}

	public function toggleTemplateFilter($root, $item)
	{
		$filter =& AEFactory::getFilterObject('templates');
		$filter->toggle($root, $item, $newStatus);
		$filters =& AEFactory::getFilters();
		$filters->save();
	}

}