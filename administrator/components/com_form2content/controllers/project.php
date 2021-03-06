﻿<?php
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

	function syncJadvparms()
	{
		$app		= JFactory::getApplication();
		$model 		= $this->getModel();
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$recordId	=	(int)$data['id'];
		$context	= "$this->option.edit.$this->context";
				
		// Force a save first
		if($this->save())
		{
			if(!$model->syncJoomlaAdvancedParms($recordId))
			{
				$this->setError($model->getError());
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId));
				return false;				
			}

			// Push the record id into the session.
			$this->holdEditId($context, $recordId);
			$app->setUserState($context.'.data', null);
			
			$this->setMessage(JText::_('COM_FORM2CONTENT_SYNC_OK'), 'information');		
			$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId));
			return true;
		}
	
		return false;
	}

	function syncMetadata()
	{
		$app		= JFactory::getApplication();
		$model 		= $this->getModel();
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$recordId	=	(int)$data['id'];
		$context	= "$this->option.edit.$this->context";
		
		// Force a save first
		if($this->save())
		{
			if(!$model->syncMetadata($recordId))
			{
				$this->setError($model->getError());
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId));
				return false;				
			}
			
			// Push the record id into the session.
			$this->holdEditId($context, $recordId);
			$app->setUserState($context.'.data', null);
			
			$this->setMessage(JText::_('COM_FORM2CONTENT_SYNC_OK'), 'information');		
			$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId));
			return true;
		}
	
		return false;
	}
	
	function upload()
	{
		$model = $this->getModel('Project');
		
		if($model->upload())
		{
			$message = JText::_('COM_FORM2CONTENT_CONTENTTYPE_UPLOAD_SUCCESSFUL');
		}
		else
		{
			// Check for errors.
			if (count($errors = $model->getErrors())) 
			{
				JError::raiseWarning(500, implode("\n", $errors));
			}
			
			$message = JText::_('COM_FORM2CONTENT_ERRORS_OCCURRED');
		}
		
		$this->setRedirect('index.php?option=com_form2content&view=projects', $message);
	}
}
?>