<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

class Form2ContentViewImagegallery extends JView
{
	function display()
	{
		echo 'testerdetest';	

		$user = JFactory::getUser();
		
		echo $user->name;
	}
}