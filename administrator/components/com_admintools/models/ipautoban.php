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

class AdmintoolsModelIpautoban extends AdmintoolsModelBase
{
	public function buildQuery($overrideLimits = false)
	{
		$where = array();

		$fltIP			= $this->getState('ip', null, 'string');
		$fltReason		= $this->getState('reason', null, 'cmd');

		$db = $this->getDBO();
		if($fltIP) {
			$where[] = '`ip` LIKE "%'.$db->getEscaped($fltIP).'%"';
		}
		if($fltReason) {
			$where[] = '`reason` LIKE "%'.$db->getEscaped($fltReason).'%"';
		}

		$query = 'SELECT * FROM `#__admintools_ipautoban`';

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
				$order = $app->getUserStateFromRequest($hash.'filter_order', 'filter_order', 'ip');
			}
			if(empty($dir)) {
				$dir = $app->getUserStateFromRequest($hash.'filter_order_Dir', 'filter_order_Dir', 'DESC');
				$dir = in_array(strtoupper($dir),array('DESC','ASC')) ? strtoupper($dir) : "ASC";
			}
			
			if($order == 'id') $order = 'ip';

			$query .= ' ORDER BY '.$db->nameQuote($order).' '.$dir;
		}

		return $query;
	}
	
	public function setIDsFromRequest()
	{
		// Get the ID or list of IDs from the request or the configuration
		$cid = JRequest::getVar('cid', null, 'DEFAULT', 'array');
		$id = JRequest::getString('id', 0);

		if(is_array($cid) && !empty($cid))
		{
			$this->id_list = array();
			foreach($cid as $an_id) {
				$this->id_list[] = $an_id;
			}
		}
		else
		{
			$this->id_list = array($id);
		}
	}
}