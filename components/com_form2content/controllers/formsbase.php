<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.controlleradmin');

class Form2ContentControllerFormsBase extends JControllerAdmin
{
	public function &getModel($name = 'Form', $prefix = 'Form2ContentModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
	
	public function copy()
	{
		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));
		
		// Get items to publish from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($cid)) 
		{
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		}
		else 
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Access check.
			foreach($cid as $id)
			{
				// load the form to see if the user has enough permissions to copy it
				$item = $model->getItem($id);
				
				$data = array();
				$data['catid'] = $item->catid;
				$data['projectid'] = $item->projectid;

				if (!$this->allowAdd($data)) 
				{
					// Set the internal error and also the redirect error.
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
					$this->setMessage($this->getError(), 'error');
					$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
					return false;
				}				
			}

			if (!$model->copy($cid)) 
			{
				JError::raiseWarning(500, $model->getError());
			}
			else 
			{
				$this->setMessage(JText::plural($this->text_prefix.'_N_ITEMS_COPIED', count($cid)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
	
	protected function allowAdd($data = array())
	{
		// Initialise variables.
		$user				= JFactory::getUser();
		$categoryId			= JArrayHelper::getValue($data, 'catid', JRequest::getInt('forms_filter_category_id'), 'int');
		$contentTypeId		= JArrayHelper::getValue($data, 'projectid', JRequest::getInt('forms_filter_contenttype_id'), 'int');
		$allow				= null;
		$allowContentType	= null;
		$allowCategory		= null;
		
		if($contentTypeId)
		{
			// If the content type has been passed in the data or URL check it.
			$allow	= $user->authorise('core.create', 'com_form2content.project.'.$contentTypeId);
		}
		/*
		if($categoryId) 
		{
			// If the category has been passed in the data or URL check it.
			$allowCategory	= $user->authorise('core.create', 'com_content.category.'.$categoryId);
			
			if($allow !== null)
			{
				$allow = $allow && 	$allowCategory;		
			}
			else
			{
				$allow = $allowCategory;
			}
		}
		*/
		if ($allow === null) 
		{
			// In the absense of better information, revert to the component permissions.
			//return parent::allowAdd();
			return false;
		}
		else 
		{
			return $allow;
		}
	}
}
?>