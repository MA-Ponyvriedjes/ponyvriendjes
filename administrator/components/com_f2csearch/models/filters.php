<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.model');
class F2CSearchModelFilters extends JModel
{

	
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the hello identifier
	 *
	 * @access	public
	 * @param	int Hello identifier
	 * @return	void
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a hello
	 * @return object with data
	 */
	function &getData()
	{
	
		// Load the data
		if (empty( $this->_data )) {
			/*$query = ' SELECT #__f2c_filters.* FROM #__f2c_filters,#__f2c_subquerys '.
					'  WHERE #__f2c_subquerys.subquery_id = #__f2c_filters.subquery_id AND #__f2c_subquerys.query_id = '.$this->_id;*/
			$query = 'SELECT #__f2c_filters.* FROM #__f2c_filters WHERE query_id = ' .  $this->_id;
			//dump($query);
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObjectList('filter_id');
		}
		return $this->_data;
	}

	function store()
	{
		//echo 'hello';	
		$row =& JTable::getInstance('filter', 'Table');
		$post = JRequest::get('post');
		//echo count($post['filters']);
		
		foreach($post['filters'] as $filter){
		 	
				if (!$row->bind($filter)) 
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				
				if( is_array($filter['key_id']) ){
					$row->key_id =	implode(', ',$filter['key_id']);
				}

				if( is_array($filter['value']) ){
					$row->value =	implode(',',$filter['value']);
				}

				if(!empty($filter['auto_menu'])){
					$row->auto_menu = 1;
				}
				else{
					$row->auto_menu = 0;
				}
				

				// Make sure the hello record is valid
				if (!$row->check()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}

				// Store the web link table to the database
				if (!$row->store()) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}

			
		}
		

		return true;
	}
	function delete()
		{
			$filterids = JRequest::getVar( 'filterids', array(0));

			$row =& $this->getTable('filter');

			if (count( $filterids )) {
				foreach($filterids as $filterid) {
					if (!$row->delete( $filterid )) {
						$this->setError( $row->getErrorMsg() );
						return false;
					}
				}
			}
			return true;
		}
}