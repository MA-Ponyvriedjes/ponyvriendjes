<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');

class F2CSearchController extends JController
{
	function display()
	{
		
		$view = JRequest::getVar('view');
		
		
		if (!$view) {
			JRequest::setVar('view', 'list');
		}

		if($view == 'menulist'){
			$view = & $this->getView( 'menulist', 'raw' );
			$view->setModel( $this->getModel( 'list' ));
		}
		

        parent::display();
		return $this;
	}
	
}


?>
