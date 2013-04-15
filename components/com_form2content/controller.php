<?php
// No direct access
defined('_JEXEC') or die;

require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'utils.form2content.php');

jimport('joomla.application.component.controller');

class Form2ContentController extends JController
{
	protected $default_view = 'forms';
	protected $menuParms = null;

	public function __construct($config = array())
	{
		$app				=& JFactory::getApplication();
		$menu				=& JSite::getMenu();
		$activeMenu			= $menu->getActive();
		
		if($activeMenu)
		{
			$this->menuParms	= $menu->getActive()->params;		
		}		
		
		parent::__construct($config);
	}
		
	public function display($cachable = false, $urlparams = false)
	{		
		$app		=& JFactory::getApplication();
		$context	= 'com_form2content.edit';
		$editMode 	= $this->menuParms->get('editmode', -1);

		if($editMode != -1)
		{
			$model 			=& $this->getModel('form');
			$contentTypeId	= $this->menuParms->get('contenttypeid');
						
			switch($this->menuParms->get('redirectmode'))
			{
				case 0:
					// custom url
					$errorRedirect = $this->menuParms->get('redirectaftersave');
					break;
				case 1:
					// newly created or modified article not possible in case of a security error -> redirect to home page
					$errorRedirect = 'index.php';
					break;
			}
			
			// Feed the model with the parameters
			$model->contentTypeId = $contentTypeId;
			
			if($editMode == 1)
			{
				// edit existing form or create a new one
				$formId = $model->getDefaultArticleId((int)$contentTypeId);
			}
			else
			{
				$formId = 0;
			}

			// Check if the operations are allowed
			$permissionCheck 				= array();
			$permissionCheck['projectid'] 	= $contentTypeId;
	
			if($formId == 0)
			{
				// Has the user exceeded the maximum number of forms?
				if(!$model->canSubmitArticle($contentTypeId, -1))
				{
					$this->setMessage($model->getError(), 'error');
					$this->setRedirect($errorRedirect);
					return false;
				}
				
				// Access check.
				if (!$this->allowAdd($permissionCheck)) 
				{
					// Set the internal error and also the redirect error.
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
					$this->setMessage($this->getError(), 'error');
					$this->setRedirect($errorRedirect);
		
					return false;
				}
				
				// Clear the record edit information from the session.
				$app->setUserState($context.'.data', null);
			}
			else 
			{
				// Access check.
				$key = 'id';
				$recordId = $formId;
				
				if (!$this->allowEdit(array($key => $recordId), $key)) 
				{
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
					$this->setMessage($this->getError(), 'error');
					$this->setRedirect($errorRedirect);
		
					return false;
				}
			}	
		}

		parent::display();
		return $this;
	}
	
	public function checkCaptcha()
	{
		if(!function_exists('recaptcha_check_answer'))
		{
			require_once(JPATH_COMPONENT_SITE.DS.'libraries'.DS.'recaptcha'.DS.'recaptchalib.php');
		}
		
		$app			=& JFactory::getApplication();
		$challengeField = JRequest::getString('challenge','');
		$responseField 	= JRequest::getString('response','');		
		$resp 			= recaptcha_check_answer(F2cFactory::getConfig()->get('recaptcha_private_key'), $_SERVER["REMOTE_ADDR"], $challengeField, $responseField);	

		if($resp->is_valid)
		{
			$app->setUserState('F2cCaptchaState', '1');
			echo 'VALID';
		}
		else
		{
			$app->setUserState('F2cCaptchaState', '0');
			echo $resp->error;
		}		
	}
	
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array	An array of input data.
	 *
	 * @return	boolean
	 * @since	3.0.0
	 */
	protected function allowAdd($data = array())
	{
		// Initialise variables.
		$user				= JFactory::getUser();
		//$categoryId			= JArrayHelper::getValue($data, 'catid', JRequest::getInt('forms_filter_category_id'), 'int');
		$contentTypeId		= JArrayHelper::getValue($data, 'projectid', JRequest::getInt('forms_filter_contenttype_id'), 'int');
		$allow				= null;
		$allowContentType	= null;
		$allowCategory		= null;

		if($contentTypeId)
		{
			// If the category has been passed in the data or URL check it.
			$allow	= $user->authorise('core.create', 'com_form2content.project.'.$contentTypeId);
		}
		
		if(is_bool($allow))
		{
			return $allow;			
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

		// In the absense of better information, revert to the component permissions.
		return ($user->authorise('core.create', 'com_form2content'));
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	3.0.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialise variables.
		$recordId		= (int)isset($data[$key]) ? $data[$key] : 0;
		$user			= JFactory::getUser();
		$userIsAuthor	= false;

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_form2content.form.'.$recordId)) 
		{
			return true;
		}

		// If this is a new record, check if the user can create new records
		if(empty($recordId) && $this->allowAdd($data))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', 'com_form2content.form.'.$recordId)) 
		{
			// Now test the owner is the user.
			$ownerId	= (int) isset($data['created_by']) ? $data['created_by'] : 0;
			
			if (empty($ownerId) && $recordId) 
			{
				// Need to do a lookup from the model.
				$record		= $this->getModel('Form')->getItem($recordId);

				if (empty($record)) 
				{
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $user->get('id')) 
			{
				return true;
			}
		}
		
		// Since there is no asset tracking, revert to the component permissions.
		return $user->authorise('core.edit', 'com_form2content');
	}

	public function ArticleImportCron()
	{
		$app		= JFactory::getApplication();
		$logFile	= Path::Combine(JFactory::getConfig()->get('log_path'), JFactory::getDate()->format('Ymd') . '_f2c_import.log');
		$log		= '';
				
		$model 	= $this->getModel('Form');
		
		$model->import();
		
		$queue = $app->getMessageQueue();

		if(JFile::exists($logFile))
		{
			$log = JFile::read($logFile);
		}
		
		if(count($queue))
		{
			foreach($queue as $queueItem) 
			{
				$log .= $queueItem['message'] . PHP_EOL;
			}
		}
		
		JFile::write($logFile, $log);
		die();
	}
}