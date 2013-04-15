<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.controlleradmin');

class Form2ContentControllerProjectFields extends JControllerAdmin
{
	protected $default_view = 'projectfields';

	public function &getModel($name = 'ProjectField', $prefix = 'Form2ContentModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
	
	public function reorder()
	{
		parent::reorder();
		$this->redirect .= '&projectid='.JRequest::getInt('projectid');
	
	}
	
	function saveorder()
	{
		parent::saveorder();
		$this->redirect .= '&projectid='.JRequest::getInt('projectid');	
	}
	
	function delete()
	{
		parent::delete();
		$this->redirect .= '&projectid='.JRequest::getInt('projectid');	}
	}
?>