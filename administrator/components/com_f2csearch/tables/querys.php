<?php defined( '_JEXEC' ) or die( 'Restricted access' ); 
class TableQuerys extends JTable
{
	var $query_id = null;
	var $title = null;
	var $fromdate = null;
	var $tilldate = null;
	var $form_id = null;
	var $published = null;
	var $attr = null;

	function __construct( &$db )
	{
		parent::__construct( '#__f2c_querys', 'query_id', $db );
	}
	
	
}