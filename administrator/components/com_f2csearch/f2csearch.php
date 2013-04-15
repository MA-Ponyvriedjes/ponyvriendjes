<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'query.php' );

$controller = JRequest::getCmd('controller', 'query');

switch ($controller) {
	
	case 'subquery':
		require_once( JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php' );
		$controllerName = 'F2CSearchController'.$controller;
		$controller = new $controllerName();
		$controller->execute( JRequest::getCmd('task') );
		$controller->redirect();
		break;	
		
	case 'filter':
		require_once( JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php' );
		$controllerName = 'F2CSearchController'.$controller;
		$controller = new $controllerName();
		$controller->execute( JRequest::getCmd('task') );
		$controller->redirect();
		break;	
	
	default :
		
		$controller = new F2CSearchControllerQuery();
		$controller->execute( JRequest::getCmd('task') );
		$controller->redirect();
		break;
}






