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

class TableCustomperms extends AdmintoolsTable
{
	var $id = 0;
	var $path = '';
	var $perms = '0644';

	function __construct( &$db )
	{
		parent::__construct( '#__admintools_customperms', 'id', $db );
	}
}
