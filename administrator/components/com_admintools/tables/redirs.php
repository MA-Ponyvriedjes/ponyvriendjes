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

class TableRedirs extends AdmintoolsTable
{
	var $id = 0;
	var $source = '';
	var $dest = '';
	var $ordering = '';
	var $published = 1;

	function __construct( &$db )
	{
		parent::__construct( '#__admintools_redirects', 'id', $db );
	}

	function check()
	{
		if(!$this->source)
		{
			$this->setError(JText::_('ATOOLS_ERR_REDIRS_NEEDS_SOURCE'));
			return false;
		}

		if(!$this->dest)
		{
			$this->setError(JText::_('ATOOLS_ERR_REDIRS_NEEDS_DEST'));
			return false;
		}

		if(empty($this->published) && ($this->published !== 0) )
		{
			$this->published = 0;
		}

		return true;
	}
}
