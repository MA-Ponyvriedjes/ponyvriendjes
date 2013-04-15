<?php
defined('_JEXEC') or die('Restricted acccess');

require_once JPATH_COMPONENT_SITE.DS.'const.form2content.php';
require_once JPATH_COMPONENT_SITE.DS.'factory.form2content.php';

// Include dependancies
jimport('joomla.application.component.controller');

$task 			= JRequest::getCmd('task');
$view 			= JRequest::getCmd('view');

if(empty($task) && $view == 'forms')
{
	JRequest::setVar('task', 'forms.display');
}

// Execute the task.
$controller	= JController::getInstance('Form2Content');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
?>