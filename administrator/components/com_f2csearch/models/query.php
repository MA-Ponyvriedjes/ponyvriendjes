<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.model');
class F2CSearchModelQuery extends JModel
{

	var $_categoryList = null;
	var $_subcategorys = null;
	var $_authorList = null;
	var $_f2cprojectList = null;
	
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
			$query = ' SELECT * FROM #__f2c_querys '.
					'  WHERE query_id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->query_id = 0;
			$this->_data->title = null;
		}
		return $this->_data;
	}
	
	function store()
	{
		
		$row =& $this->getTable('querys');
		$post = JRequest::get('post');
		
		if (!$row->bind($post)) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		

		if( is_array($post['form_id']) ){
			$row->form_id =	implode(',',$post['form_id']);
		}

		// 		 sure the hello record is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// Store the web link table to the database
		if (!$row->store(true)) {
			
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
		
		JRequest::setVar('query_id',$row->query_id);
		

		return true;
	}

	function delete()
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable('querys');

		if (count( $cids )) {
			foreach($cids as $cid) {
				if (!$row->delete( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}
			}
		}
		return true;
	}

	function publish()
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$publish = JRequest::getVar( 'publish', 0);
		
		$row =& $this->getTable('querys');
		if (count( $cids )) {
			//foreach($cids as $cid) {
				
				if(!$row->publish($cids, $publish))
				{
					//$this->setError( $this->_db->getErrorMsg() );
					JError::raiseError(500, $row->getError() );
					return false;
				}
			//}
		}
		
		return true;
	}
}