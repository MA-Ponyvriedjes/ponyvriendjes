<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2011 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id$
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

/**
 * Joomla! Templates exclusion filter
 */
class AEFilterTemplates extends AEAbstractFilter
{
	private $joomla16;

	public function __construct()
	{
		$this->object	= 'templates';
		$this->subtype	= 'all';
		$this->method	= 'direct';
		
		if(AEFactory::getKettenrad()->getTag() == 'restorepoint') $this->enabled = false;

		if(empty($this->filter_name)) $this->filter_name = strtolower(basename(__FILE__,'.php'));

		$this->joomla16 = ! @file_exists(JPATH_SITE.'/includes/joomla.php');

		parent::__construct();
	}

	public function &getExtraSQL($root)
	{
		$empty = '';
		if($root != '[SITEDB]') return $empty;

		$sql = '';
		$db =& AEFactory::getDatabase();
		$this->getFilters(null); // Forcibly reload the filter data
		// Loop all components and add SQL statements
		if(!empty($this->filter_data))
		{
			foreach($this->filter_data as $type => $items)
			{
				if(!empty($items))
				{
					// Make sure that DB only backups get the correct prefix
					$configuration =& AEFactory::getConfiguration();
					$abstract = AEUtilScripting::getScriptingParameter('db.abstractnames', 1);
					if($abstract) {
						$prefix = '#__';
					} else {
						$prefix = $db->getPrefix();
					}

					foreach($items as $item)
					{
						$client = ($type == 'frontend') ? 0 : 1;
						if(!$this->joomla16)
						{
							$sql .= 'DELETE FROM '.$db->nameQuote($prefix.'templates_menu').' WHERE '.
								'('.$db->nameQuote('template').' = '.$db->Quote($item).') AND '.
								'('.$db->nameQuote('client_id').' = '.$db->Quote($client).')'.
								";\n";
						}
						else
						{
							$sql .= 'DELETE FROM '.$db->nameQuote($prefix.'extensions').
								' WHERE '.$db->nameQuote('element').' = '.
								$db->Quote($item)." AND ".$db->nameQuote('type').' = '.
								$db->Quote('template').";\n";
						}
					}
				}
			}
		}

		return $sql;
	}

}