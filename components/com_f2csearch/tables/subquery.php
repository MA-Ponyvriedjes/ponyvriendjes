<?php defined( '_JEXEC' ) or die( 'Restricted access' ); 
class TableSubquery extends JTable
{
	var $subquery_id = null;
	var $type = null;
	var $required = null;
	var $query_id = null;
	
	function __construct( &$db )
	{
		parent::__construct( '#__f2c_subquery', 'subquery_id', $db );
	}
	
	
}