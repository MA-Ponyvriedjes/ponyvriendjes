<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.controllerform');

class Form2ContentControllerProjectField extends JControllerForm
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

	public function projectselect()
	{
		$view =& $this->getView('copyfieldselect', 'html');
		$view->setModel( $this->getModel('projectfield'), true );
		$view->display();
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

			if (!$model->copy($cid)) 
			{
				JError::raiseWarning(500, $model->getError());
			}
			else 
			{
				$this->setMessage(JText::plural($this->text_prefix.'_N_ITEMS_COPIED', count($cid)));
			}
		}

		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&projectid='.JRequest::getInt('projectid'), false));
	}	
	
	protected function getRedirectToListAppend()
	{
		$tmpl		= JRequest::getString('tmpl');
		$append		= '';

		// Setup redirect info.
		if ($tmpl) 
		{
			$append .= '&tmpl='.$tmpl;
		}

		$jform = JRequest::getVar('jform', array(), 'post', 'array');
		$append .= '&projectid='.(int)$jform['projectid'];
		
		return $append;
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$redirect = parent::getRedirectToItemAppend($recordId, $urlVar);
	
		if($contentTypeId = JRequest::getInt('projectid'))
		{
			$redirect .= '&projectid='.$contentTypeId;
		}
		
		return $redirect;
	}
}
?>