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
jimport('joomla.application.component.view');

/**
 * Extension Filter view class
 *
 */
class AkeebaViewExtfilter extends JView
{
	/**
	 * Modified constructor to enable loading layouts from the plug-ins folder
	 * @param $config
	 */
	public function __construct( $config = array() )
	{
		parent::__construct( $config );
		$tmpl_path = dirname(__FILE__).'/tmpl';
		$this->addTemplatePath($tmpl_path);
	}

	public function display()
	{
		$layout = JRequest::getCmd('layout','default');
		$task = JRequest::getCmd('task','components');

		// Add submenus (those nifty text links below the toolbar!)
		$link = JURI::base().'?option=com_akeeba&view=extfilter&task=components';
		JSubMenuHelper::addEntry(JText::_('EXTFILTER_COMPONENTS'), $link, ($task == 'components'));
		$link = JURI::base().'?option=com_akeeba&view=extfilter&task=modules';
		JSubMenuHelper::addEntry(JText::_('EXTFILTER_MODULES'), $link, ($task == 'modules'));
		$link = JURI::base().'?option=com_akeeba&view=extfilter&task=plugins';
		JSubMenuHelper::addEntry(JText::_('EXTFILTER_PLUGINS'), $link, ($task == 'plugins'));
		$link = JURI::base().'?option=com_akeeba&view=extfilter&task=languages';
		JSubMenuHelper::addEntry(JText::_('EXTFILTER_LANGUAGES'), $link, ($task == 'languages'));
		$link = JURI::base().'?option=com_akeeba&view=extfilter&task=templates';
		JSubMenuHelper::addEntry(JText::_('EXTFILTER_TEMPLATES'), $link, ($task == 'templates'));

		// Add toolbar buttons
		JToolBarHelper::title(JText::_('AKEEBA').': <small>'.JText::_('EXTFILTER').'</small>','akeeba');
		JToolBarHelper::back('AKEEBA_CONTROLPANEL', 'index.php?option='.JRequest::getCmd('option'));
		JToolBarHelper::spacer();

		$model =& $this->getModel();
		switch($task)
		{
			case 'components':
				// Pass along the list of components
				$this->assignRef('components', $model->getComponents());
				break;

			case 'modules':
				// Pass along the list of components
				$this->assignRef('modules', $model->getModules());
				break;

			case 'plugins':
				// Pass along the list of components
				$this->assignRef('plugins', $model->getPlugins());
				break;

			case 'templates':
				// Pass along the list of components
				$this->assignRef('templates', $model->getTemplates());
				break;

			case 'languages':
				// Pass along the list of components
				$this->assignRef('languages', $model->getLanguages());
				break;
		}

		// Add references to scripts and CSS
		AkeebaHelperIncludes::includeMedia(true);

		// Add live help
		AkeebaHelperIncludes::addHelp();

		// Get profile ID
		$profileid = AEPlatform::getInstance()->get_active_profile();
		$this->assign('profileid', $profileid);

		// Get profile name
		if(!class_exists('AkeebaModelProfiles')) JLoader::import('models.profiles', JPATH_COMPONENT_ADMINISTRATOR);
		$model = new AkeebaModelProfiles();
		$model->setId($profileid);
		$profile_data = $model->getProfile();
		$this->assign('profilename', $profile_data->description);

		parent::display();
	}
}