<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.controlleradmin');

class Form2ContentControllerTemplates extends JController
{
	protected $default_view = 'templates';

	public function &getModel($name = 'Template', $prefix = 'Form2ContentModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}		
}
?>