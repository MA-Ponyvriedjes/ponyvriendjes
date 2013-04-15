<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.model');
class F2CSearchModelQuerys extends JModel
{
	var $data = null;
	
	function getData()
	{
		if (empty($this->data)) {
			$query = "SELECT * FROM #__f2c_querys";
			$this->data = $this->_getList($query);
		}
		return $this->data;
	}

	
}
