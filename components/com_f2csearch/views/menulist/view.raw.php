<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * Form2ContentCustomSearch View
 */
class  F2CSearchViewMenuList extends JView
{
	/**
	 *  Form2ContentCustomSearch view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		
		
		//$result = $this->searchResult;
		$menuparams = JRequest::getVar('menuparams');
		if(empty($menuparams)){
			$menuparams = array();
			$menuparams['type'] = 'f2c-formfield';
			$menuparams['withResult'] = 1;
			$menuparams['submenu'] = 1;
			$menuparams['order'] = 'content';
		}
		//var_dump($this->_models);
		$this->_models['list']->setMenuParams($menuparams);
		$data = &$this->get('MenuItems','list');

		
		
		// Get the document object.
		$document =& JFactory::getDocument();
 
		// Set the MIME type for JSON output.
		$document->setMimeEncoding('application/json');
		 
		// Change the suggested filename.
		JResponse::setHeader('Content-Disposition','attachment;filename="'.$view->getName().'.json"');
		 
		// Output the JSON data.
		echo json_encode($data);
		
	}
	
}

?>