<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

class AdmintoolsModelLogs extends FOFModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();
		$query = FOFQueryAbstract::getNew($db)
			->select(array(
				$db->nameQuote('l').'.*',
				'IF('.$db->nameQuote('b').'.'.$db->nameQuote('ip').', '.$db->quote(1).', '.$db->quote(0).') AS '.$db->nameQuote('block')
			))
			->from($db->nameQuote('#__admintools_log').' AS '.$db->nameQuote('l'))
			->join('LEFT OUTER', 
				$db->nameQuote('#__admintools_ipblock').' AS '.$db->nameQuote('b').
				'ON ('.$db->nameQuote('b').'.'.$db->nameQuote('ip').' = '.
				$db->nameQuote('l').'.'.$db->nameQuote('ip').')'
			);

		jimport('joomla.utilities.date');

		$fltDateFrom			= $this->getState('datefrom', null, 'string');
		if($fltDateFrom) {
			$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
			if(!preg_match($regex, $fltDateFrom)) {
				$fltDateFrom = '2000-01-01 00:00:00';
				$this->setState('datefrom', '');
			}
			$date = new JDate($fltDateFrom);
			$query->where($db->nameQuote('logdate').' >= '.$db->Quote($date->toMySQL()));
		}
		
		$fltDateTo				= $this->getState('dateto', null, 'string');
		if($fltDateTo) {
			$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';
			if(!preg_match($regex, $fltDateTo)) {
				$fltDateTo = '2037-01-01 00:00:00';
				$this->setState('dateto', '');
			}
			$date = new JDate($fltDateTo);
			$query->where($db->nameQuote('logdate').' <= '.$db->Quote($date->toMySQL()));
		}
		
		$fltIP					= $this->getState('ip', null, 'string');
		if($fltIP) {
			$fltIP = '%'.$fltIP.'%';
			$query->where($db->nameQuote('l').'.'.$db->nameQuote('ip').' LIKE '.$db->quote($fltIP));
		}

		$fltURL					= $this->getState('url', null, 'string');
		if($fltURL) {
			$fltURL = '%'.$fltURL.'%';
			$query->where($db->nameQuote('url').' LIKE '.$db->Quote($fltURL));
		}

		$fltReason				= $this->getState('reason', null, 'cmd');
		if($fltReason) {
			$query->where($db->nameQuote('reason').' = '.$db->quote($fltReason));
		}

		if(!$overrideLimits) {
			$order = $this->getState('filter_order',null,'cmd');
			if(!in_array($order, array_keys($this->getTable()->getData()))) $order = 'id';
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($order.' '.$dir);
		}
		
		return $query;
	}
}