<?php
defined('_JEXEC') or die('Restricted acccess');

require_once JPATH_COMPONENT_SITE.DS.'const.form2content.php';
require_once JPATH_COMPONENT_SITE.DS.'factory.form2content.php';

jimport('joomla.application.component.controller');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_form2content')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller = JController::getInstance('Form2Content');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();
?>