<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DS.'controllers'.DS.'formbase.php');
require_once JPATH_COMPONENT_SITE.DS.'utils.form2content.php';

class Form2ContentControllerForm extends Form2ContentControllerFormBase
{
	private $savedFormId = 0;

	function add()
	{
		// Initialise variables.
		$app				= JFactory::getApplication();
		$context			= "$this->option.edit.$this->context";
		$model				= $this->getModel();
		$menu				=& JSite::getMenu();
		$this->activeMenu	= $menu->getActive();
		
		// get the Content Type from the menu we came from
		$contentTypeId = $this->activeMenu->params->get('contenttypeid');		

		$permissionCheck = array();
		$permissionCheck['projectid'] = $contentTypeId;

		// Has the user exceeded the maximum number of forms?
		if(!$model->canSubmitArticle($contentTypeId, -1))
		{
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
			return false;
		}
		
		// Access check.
		if (!$this->allowAdd($permissionCheck)) 
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
		$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend().'&projectid='.$contentTypeId . '&Itemid=' . JRequest::getInt('Itemid'));
		return true;
	}
	
	public function edit($key = null, $urlVar = null)
	{
		$cid 		= JRequest::getVar('cid', array(), 'post', 'array');
		$app		= JFactory::getApplication();
		$context	= "$this->option.edit.$this->context";
		
		if(count($cid) || JRequest::getInt('id'))
		{
			// clear the session data
			$app->setUserState($context . '.id', null);	
		}
		
		parent::edit($key, $urlVar);
		
		// get the form id
		$arrId		= $app->getUserState($context . '.id');		
		$id 		= $arrId[0];
		
		$this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend().'&id='.$id.'&Itemid=' . JRequest::getInt('Itemid'));	
		return true;
	}
		
	public function save($key = null, $urlVar = null)
	{
		$app		=& JFactory::getApplication();
		$menu		=& JSite::getMenu();
		$activeMenu	= $menu->getActive();
		
		// check if we came from the Single F2C Article menu item
		if($activeMenu->params->get('redirectmode') != '')
		{
			$formId = (int)$_POST['jform']['id'];

			if($formId)
			{
				$ids	= array();
				$ids[]	= $formId;
				$app->setUserState('com_form2content.edit.form.id', $ids);
			}					
		}

		if(parent::save($key, $urlVar))
		{
			// check if we came from the Single F2C Article menu item
			if($activeMenu->params->get('redirectmode') != '')
			{
				$redirectLink = 'index.php';
			
				switch((int)$activeMenu->params->get('redirectmode'))
				{
					case 0:
						// redirect to custom url
						$redirectLink = $activeMenu->params->get('redirectaftersave', 'index.php');
						break;
					case 1:
						// redirect to new or modified article if this is published
						$formId				= $this->savedFormId;
						$this->savedFormId	= 0; //reset saved form var
						$redirectLink 		= $this->getFormRedirect($formId);
						break;
				}
				
				$this->setRedirect($redirectLink);					
			}
			
			$app->setUserState('com_form2content.edit.form.new', null);			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function cancel($key = null)
	{
		$app		=& JFactory::getApplication();
		$menu		=& JSite::getMenu();
		$activeMenu	= $menu->getActive();
				
		$app->setUserState('com_form2content.edit.form.new', null);
		
		// check if we came from the Single F2C Article menu item
		if($activeMenu->params->get('redirectmode') != '')
		{
			$formId = (int)$_POST['jform']['id'];
			
			if($formId)
			{
				
				$ids	= array();
				$ids[]	= $formId;
				$app->setUserState('com_form2content.edit.form.id', $ids);
			}					
		}
		
		parent::cancel($key);
		
		// check if we came from the Single F2C Article menu item
		if($activeMenu->params->get('redirectmode') != '')
		{
			$redirectLink = 'index.php';
		
			if((int)$activeMenu->params->get('redirectmode') == 0)
			{
				// redirect to custom url
				$redirectLink = $activeMenu->params->get('redirectaftersave', 'index.php');
			}
			else
			{
				// redirect to new or modified article if this is published
				$redirectLink = $this->getFormRedirect((int)$_POST['jform']['id']);				
			}
			
			$this->setRedirect($redirectLink);
		}
	}
	
	protected function postSaveHook(&$model, $validData)
	{
		$this->savedFormId = $model->getState($model->getName().'.id');
	}
		
	private function getFormRedirect($formId)
	{
		$redirectLink = 'index.php';
		
		if($formId)
		{
			$model 	= $this->getModel();
			$item 	= $model->getItem($formId);
						
			if($item->id)
			{
				if ($item->publish_up || $item->publish_down)
				{
					$nullDate 		= JFactory::getDBO()->getNullDate();
					$nowDate 		= JFactory::getDate()->toUnix();
					$tz 			= new DateTimeZone(JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset')));
					$publish_up 	= ($item->publish_up != $nullDate) ? JFactory::getDate($item->publish_up, 'UTC')->setTimeZone($tz) : false;
					$publish_down 	= ($item->publish_down != $nullDate) ? JFactory::getDate($item->publish_down, 'UTC')->setTimeZone($tz) : false;
						
					// check if the item is published
					if(	$item->state == 1 &&
						($publish_up && $nowDate >= $publish_up->toUnix()) &&
						(($publish_down && $nowDate <= $publish_down->toUnix()) || !$item->publish_down || $item->publish_down == $nullDate))
					{
						$slug = $item->alias ? ($item->reference_id . ':' . $item->alias) : $item->reference_id;
						$redirectLink = ContentHelperRoute::getArticleRoute($slug, $item->catid);
					}
				}
			}
		}
				
		return 	$redirectLink;
	}	
}
?>