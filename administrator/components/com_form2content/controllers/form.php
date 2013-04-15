<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DS.'controllers'.DS.'formbase.php');

class Form2ContentControllerForm extends Form2ContentControllerFormBase
{
	function projectselect()
	{
		$view =& $this->getView('projectselect', 'html');
		$view->setModel( $this->getModel('form'), true );
			
		if($contentTypeId = $view->display())
		{
			// If there's only one Content Type that the user is allowed to create,
			// redirect immediately to that Content Type
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend().'&projectid='.$contentTypeId, false));
		}
	}
	
	function add()
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$context	= "$this->option.edit.$this->context";

		// Access check.
		if (!$this->allowAdd()) 
		{
			// Set the internal error and also the redirect error.
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

			return false;
		}

		// Clear the record edit information from the session.
		$app->setUserState($context.'.data', null);
		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend().'&projectid='.JRequest::getInt('projectid'), false));

		return true;
	}
}
?>