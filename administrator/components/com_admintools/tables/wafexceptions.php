<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

if(!class_exists('AdmintoolsTable'))
{
	require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'tables'.DS.'base.php';
}

class TableWafexceptions extends AdmintoolsTable
{
	var $id = 0;
	var $option = '';
	var $view = '';
	var $query = '';

	function __construct( &$db )
	{
		parent::__construct( '#__admintools_wafexceptions', 'id', $db );
	}

	function check()
	{
		if(!$this->option && !$this->view && !$this->query)
		{
			$this->setError(JText::_('ATOOLS_ERR_WAFEXCEPTIONS_ALLNULL'));
			return false;
		}

		return true;
	}
}
