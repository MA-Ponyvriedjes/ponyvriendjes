<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 3.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
 * Archive discovery view - Controller
 */
class AkeebaControllerDiscover extends JController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		if(AKEEBA_JVERSION=='16')
		{
			// Access check, Joomla! 1.6 style.
			$user = JFactory::getUser();
			if (!$user->authorise('akeeba.download', 'com_akeeba')) {
				$this->setRedirect('index.php?option=com_akeeba');
				return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
				$this->redirect();
			}
		} else {
			// Custom ACL for Joomla! 1.5
			$aclModel = JModel::getInstance('Acl','AkeebaModel');
			if(!$aclModel->authorizeUser('download')) {
				$this->setRedirect('index.php?option=com_akeeba');
				return JError::raiseWarning(403, JText::_('Access Forbidden'));
				$this->redirect();
			}
		}
		$base_path = JPATH_COMPONENT_ADMINISTRATOR.'/plugins';
		$model_path = $base_path.'/models';
		$view_path = $base_path.'/views';
		$this->addModelPath($model_path);
		$this->addViewPath($view_path);
	}
	
	/**
	 * Allows you to select a directory to scan for files. Defaults to the current
	 * profile's output directory.
	 */
	public function display()
	{
		parent::display();
	}
	
	/**
	 * Discovers JPA, JPS and ZIP files in the selected profile's directory and
	 * lets you select them for inclusion in the import process.
	 */
	public function discover()
	{
		// CSRF prevention
		if(!JRequest::getVar(JUtility::getToken(), false, 'POST')) {
			JError::raiseError('403', JText::_('Request Forbidden'));
		}
		
		$directory = JRequest::getString('directory','');

		if(empty($directory)) {
			$url = 'index.php?option=com_akeeba&view=discover';
			$msg = JText::_('DISCOVER_ERROR_NODIRECTORY');
			$this->setRedirect($url, $msg, 'error');
			return;
		}
		
		parent::display();
	}
	
	/**
	 * Performs the actual import
	 */
	public function import()
	{
		// CSRF prevention
		if(!JRequest::getVar(JUtility::getToken(), false, 'POST')) {
			JError::raiseError('403', JText::_('Request Forbidden'));
		}
		
		$directory = JRequest::getString('directory','');
		$files = JRequest::getVar('files',array(),'default','array');
		
		if(empty($files)) {
			$url = 'index.php?option=com_akeeba&view=discover';
			$msg = JText::_('DISCOVER_ERROR_NOFILESSELECTED');
			$this->setRedirect($url, $msg, 'error');
			return;
		}
		
		$model = JModel::getInstance('Discover','AkeebaModel');
		foreach($files as $file)
		{
			$model->import($directory, $file);
		}
		$url = 'index.php?option=com_akeeba';
		$msg = JText::_('DISCOVER_LABEL_IMPORTDONE');
		$this->setRedirect($url, $msg);
	}
}