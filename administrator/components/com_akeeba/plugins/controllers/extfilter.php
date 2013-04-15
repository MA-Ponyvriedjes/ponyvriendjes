<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 2.1
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

/**
 * Extension Filter controller class
 *
 */
class AkeebaControllerExtfilter extends JController
{
	public function  __construct($config = array()) {
		parent::__construct($config);
		if(AKEEBA_JVERSION=='16')
		{
			// Access check, Joomla! 1.6 style.
			$user = JFactory::getUser();
			if (!$user->authorise('akeeba.configure', 'com_akeeba')) {
				$this->setRedirect('index.php?option=com_akeeba');
				return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
				$this->redirect();
			}
		} else {
			// Custom ACL for Joomla! 1.5
			$aclModel = JModel::getInstance('Acl','AkeebaModel');
			if(!$aclModel->authorizeUser('configure')) {
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
	 * Default viewing template, passes along execution to components template
	 *
	 */
	function display()
	{
		$this->components();
	}

	/**
	 * Components task, shows all non-core components
	 */
	function components()
	{
		JRequest::setVar('layout', 'default_components');
		parent::display();
	}

	/**
	 * Languages task, shows all languages except the default
	 */
	function languages()
	{
		JRequest::setVar('layout', 'default_languages');
		parent::display();
	}

	/**
	 * Modules task, shows all non-core modules
	 */
	function modules()
	{
		JRequest::setVar('layout', 'default_modules');
		parent::display();
	}

	/**
	 * Plugins task, shows all non-core plugins
	 */
	function plugins()
	{
		JRequest::setVar('layout', 'default_plugins');
		parent::display();
	}

	/**
	 * Templates task, shows all non-core templates
	 */
	function templates()
	{
		JRequest::setVar('layout', 'default_templates');
		parent::display();
	}

	/**
	 * Toggles the exclusion of a component
	 *
	 */
	function toggleComponent()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$root = JRequest::getString('root', 'default');
		$item = JRequest::getString('item', '');

		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model =& $this->getModel('extfilter');
		$components =& $model->getComponents();

		$found = false;
		$numRows = count($components);
		for($i=0;$i < $numRows; $i++)
		{
			$row =& $components[$i];
			if($row['item'] == $item) {
				$found = true;
				$name = $row['name'];
			}
		}

		$link = JURI::base().'index.php?option=com_akeeba&view=extfilter&task=components';
		if(!$found)
		{
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDCOMPONENT', $item);
			$this->setRedirect( $link, $msg, 'error' );
		}
		else
		{
			$model->toggleComponentFilter($root, $item);
			$link = JURI::base().'index.php?option=com_akeeba&view=extfilter&task=components';
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDCOMPONENT', $name);
			$this->setRedirect( $link, $msg );
		}

		parent::redirect();
	}

	/**
	 * Toggles the exclusion of a module
	 *
	 */
	function toggleModule()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$root = JRequest::getString('root', 'frontend');
		$item = JRequest::getString('item', '');

		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model =& $this->getModel('extfilter');
		$modules =& $model->getModules();

		$found = false;
		$numRows = count($modules);
		for($i=0; $i < $numRows; $i++)
		{
			$row =& $modules[$i];
			if( ($row['item'] == $item) && ($row['root'] == $root) ) {
				$found = true;
				$name = $row['name'];
				break;
			}
		}

		$link = JURI::base().'index.php?option=com_akeeba&view=extfilter&task=modules';
		if(!$found)
		{
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDMODULE', $item);
			$this->setRedirect( $link, $msg, 'error' );
		}
		else
		{
			$model->toggleModuleFilter($root, $item);
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDMODULE', $name);
			$this->setRedirect( $link, $msg );
		}

		parent::redirect();
	}

	/**
	 * Toggles the exclusion of a language
	 *
	 */
	function toggleLanguage()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$item = JRequest::getString('item', '');
		$root = JRequest::getString('root', '');

		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model =& $this->getModel('extfilter');
		$languages =& $model->getLanguages();

		$found = false;
		$numRows = count($languages);
		for($i=0; $i < $numRows; $i++)
		{
			$row =& $languages[$i];
			if( ($row['item'] == $item) && ($row['root'] == $root) ) {
				$found = true;
				$name = $row['name'];
				break;
			}
		}

		$link = JURI::base().'index.php?option=com_akeeba&view=extfilter&task=languages';
		if(!$found)
		{
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDLANGUAGE', $item);
			$this->setRedirect( $link, $msg, 'error' );
		}
		else
		{
			$model->toggleLanguageFilter($root, $item);
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDLANGUAGE', $name);
			$this->setRedirect( $link, $msg );
		}

		parent::redirect();
	}

	/**
	 * Toggles the exclusion of a plugin
	 *
	 */
	function togglePlugin()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$item = JRequest::getString('item', '');
		$root = JRequest::getString('root', '');

		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model =& $this->getModel('extfilter');
		$plugins =& $model->getPlugins();

		$found = false;
		$numRows = count($plugins);
		for($i=0; $i < $numRows; $i++)
		{
			$row =& $plugins[$i];
			if( ($row['item'] == $item) && ($row['root'] == $root) ) {
				$found = true;
				$name = $row['name'];
				break;
			}
		}

		if(!$found)
		{
			$link = JURI::base().'index.php?option=com_akeeba&view=extfilter&task=plugins';
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDPLUGIN', $item);
			$this->setRedirect( $link, $msg, 'error' );
		}
		else
		{
			$model->togglePluginFilter($root, $item);
			$link = JURI::base().'index.php?option=com_akeeba&view=extfilter&task=plugins';
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDPLUGIN', $name);
			$this->setRedirect( $link, $msg );
		}

		parent::redirect();
	}

	/**
	 * Toggles the exclusion of a template
	 *
	 */
	function toggleTemplate()
	{
		//JResponse::setHeader('Cache-Control','no-cache, must-revalidate',true); // HTTP 1.1 - Cache control
		//JResponse::setHeader('Expires','Sat, 26 Jul 1997 05:00:00 GMT',true); // HTTP 1.0 - Date in the past

		// Get the option passed along
		$item = JRequest::getString('item', '');
		$root = JRequest::getString('root', '');

		// Try to figure out if this component is allowed to be excluded (exists and is non-Core)
		$model =& $this->getModel('extfilter');
		$templates =& $model->getTemplates();

		$found = false;
		$numRows = count($templates);
		for($i=0; $i < $numRows; $i++)
		{
			$row =& $templates[$i];
			if( ($row['item'] == $item) && ($row['root'] == $root) ) {
				$found = true;
				$name = $row['name'];
				break;
			}
		}

		$link = JURI::base().'index.php?option=com_akeeba&view=extfilter&task=templates';
		if(!$found)
		{
			$msg = JText::sprintf('EXTFILTER_ERROR_INVALIDTEMPLATE', $item);
			$this->setRedirect( $link, $msg, 'error' );
		}
		else
		{
			$model->toggleTemplateFilter($root, $item);
			$msg = JText::sprintf('EXTFILTER_MSG_TOGGLEDTEMPLATE', $name);
			$this->setRedirect( $link, $msg );
		}

		parent::redirect();
	}

}