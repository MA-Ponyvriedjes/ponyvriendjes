<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

class AdmintoolsModelRedirs extends FOFModel
{
	public function setRedirectionState($newState)
	{
		$params = JModel::getInstance('Storage','AdmintoolsModel');
		$params->setValue('urlredirection',$newState ? 1 : 0);
		$params->save();
	}
	
	public function getRedirectionState()
	{
		$params = JModel::getInstance('Storage','AdmintoolsModel');
		return $params->getValue('urlredirection', 1);
	}
	
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();
		$query = FOFQueryAbstract::getNew($db)
			->select(array('*'))
			->from($db->nameQuote('#__admintools_redirects'));
		
		$fltSource		= $this->getState('source', null, 'string');
		if($fltSource) {
			$fltSource = '%'.$fltSource.'%';
			$query->where($db->nameQuote('source').' LIKE '.$db->quote($fltSource));
		}

		$fltDest		= $this->getState('dest', null, 'string');
		if($fltDest) {
			$fltDest = '%'.$fltDest.'%';
			$query->where($db->nameQuote('dest').' LIKE '.$db->quote($fltDest));
		}
		
		$fltPublished	= $this->getState('published', null, 'cmd');
		if(!is_null($fltPublished) && ($fltPublished !== '')) {
			$query->where($db->nameQuote('published').' = '.$db->quote($fltPublished));
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