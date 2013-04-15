<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DS.'controllers'.DS.'formsbase.php');

class Form2ContentControllerForms extends Form2ContentControllerFormsBase
{
	public function __construct($config = array())
	{
		parent::__construct($config);		
		$this->registerTask('unfeatured', 'featured');		
	}

	function refresh()
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

			// Refresh the items -> publish with F2C_STATE_RETAIN retains the current state.
			if (!$model->publish($cid, F2C_STATE_RETAIN)) 
			{
				JError::raiseWarning(500, $model->getError());
			}
			else 
			{
				$this->setMessage(JText::plural($this->text_prefix.'_N_ITEMS_REFRESHED', count($cid)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return	void
	 * @since	3.0.0
	 */
	function featured()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('featured' => 1, 'unfeatured' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			$authorized = $user->authorise('core.edit.state', 'com_form2content.form.'.(int) $id) ||
						  $user->authorise('form2content.edit.state.own', 'com_form2content.form.'.(int) $id);
				
			if (!$authorized) 
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
		}

		if (empty($ids)) 
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else 
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featuredList($ids, $value)) 
			{
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}
	
	function export()
	{
		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = JRequest::getVar('cid', array(), '', 'array');

		if (empty($cid)) 
		{
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
			return false;
		}
		
		$model = $this->getModel();
		$model->export($cid);
		
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));		
	}	
}
?>