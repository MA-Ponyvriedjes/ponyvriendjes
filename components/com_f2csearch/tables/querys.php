<?php defined( '_JEXEC' ) or die( 'Restricted access' ); 
class TableQuerys extends JTable
{
	var $query_id = null;
	var $fromDate = null;
	var $tillDate = null;
	var $category_id = null;
	var $section_id = null;
	var $created_by = null;
	var $featured = null;
	var $form_id = null;
	
	function __construct( &$db )
	{
		parent::__construct( '#__f2c_querys', 'query_id', $db );
	}
	
	
}