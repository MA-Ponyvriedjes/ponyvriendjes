<?php defined( '_JEXEC' ) or die( 'Restricted access' ); 
class TableFilter extends JTable
{
	var $filter_id = null;
	var $key_id = null;
	var $operator = null;
	var $value = null;
	var $required = null;
	var $auto_menu = null;
	var $type = null;
	var $query_id = null;
	
	function __construct( &$db )
	{
		parent::__construct( '#__f2c_filters', 'filter_id', $db );
	}
	
	
}