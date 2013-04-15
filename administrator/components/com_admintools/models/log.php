<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'base.php';

class AdmintoolsModelLog extends AdmintoolsModelBase
{
	public function buildQuery($overrideLimits = false)
	{
		$where = array();

		$fltDateFrom			= $this->getState('datefrom', null, 'string');
		$fltDateTo				= $this->getState('dateto', null, 'string');
		$fltIP					= $this->getState('ip', null, 'string');
		$fltURL					= $this->getState('url', null, 'string');
		$fltReason				= $this->getState('reason', null, 'cmd');

		$db = $this->getDBO();
		jimport('joomla.utilities.date');
		if($fltDateFrom) {
			$date = new JDate($fltDateFrom);
			$where[] = '`logdate` >= '.$db->Quote($date->toMySQL());
		}
		if($fltDateTo) {
			$date = new JDate($fltDateTo);
			$where[] = '`logdate` <= '.$db->Quote($date->toMySQL());
		}
		if($fltIP) {
			$where[] = '`l`.`ip` LIKE "%'.$db->getEscaped($fltIP).'%"';
		}
		if($fltURL) {
			$where[] = '`url` LIKE "%'.$db->getEscaped($fltURL).'%"';
		}
		if($fltReason) {
			$where[] = '`reason` = '.$db->Quote($fltReason);
		}

		$query = 'SELECT `l`.*, IF(`b`.`ip`,1,0) as `block` FROM `#__admintools_log` AS `l` LEFT OUTER JOIN `#__admintools_ipblock` AS `b` ON(`b`.`ip` = `l`.`ip`)';

		if(count($where) && !$overrideLimits)
		{
			$query .= ' WHERE (' . implode(') AND (',$where) . ')';
		}

		if(!$overrideLimits) {
			$order = $this->getState('order',null,'cmd');
			if($order === 'Array') $order = null;
			$dir = $this->getState('dir',null,'cmd');

			$app = JFactory::getApplication();
			$hash = $this->getHash();
			if(empty($order)) {
				$order = $app->getUserStateFromRequest($hash.'filter_order', 'filter_order', 'id');
			}
			if(empty($dir)) {
				$dir = $app->getUserStateFromRequest($hash.'filter_order_Dir', 'filter_order_Dir', 'DESC');
				$dir = in_array(strtoupper($dir),array('DESC','ASC')) ? strtoupper($dir) : "ASC";
			}

			$query .= ' ORDER BY '.$db->nameQuote($order).' '.$dir;
		}
		
		return $query;
	}
}