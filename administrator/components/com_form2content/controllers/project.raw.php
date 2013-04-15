<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.controllerform');

class Form2ContentControllerProject extends JControllerForm
{
	public function __construct($config = array())
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin')) 
		{
			return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		parent::__construct($config);
	}

	public function getModel($name = 'Project', $prefix = 'Form2ContentModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	function export()
	{
		$model = $this->getModel();
		$model->export();
		parent::display();
	}
	
	function createSampleFormTemplate()
	{
		$contentTypeId 	= JRequest::getInt('id');
		$overwrite 		= JRequest::getInt('overwrite', 0);		
		$model 			= $this->getModel();
		
		// clean the response
		ob_end_clean();
		echo $model->createSampleFormTemplate($contentTypeId, $overwrite);
		die();
	}
}
?>