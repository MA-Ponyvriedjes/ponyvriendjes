<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.controller');

class Form2ContentControllerTemplates extends JController
{
	function display()
	{
		JRequest::setVar('view', 'templates');
		parent::display();
	}
}
?>