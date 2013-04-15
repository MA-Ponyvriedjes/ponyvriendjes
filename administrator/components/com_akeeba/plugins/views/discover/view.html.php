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
jimport('joomla.application.component.view');

/**
 * Archive discovery view - HTML View
 */
class AkeebaViewDiscover extends JView
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

	public function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('AKEEBA').': <small>'.JText::_('DISCOVER').'</small>','akeeba');
		JToolBarHelper::back('AKEEBA_CONTROLPANEL', 'index.php?option='.JRequest::getCmd('option'));
		
		// Add references to scripts and CSS
		AkeebaHelperIncludes::includeMedia(true);
		$media_folder = JURI::base().'../media/com_akeeba/';
		
		$task = JRequest::getCmd('task');
		
		switch($task)
		{
			case 'discover':
				$tpl = 'discover';
				$directory = JRequest::getString('directory','');
				
				$model = JModel::getInstance('Discover','AkeebaModel');
				$files = $model->getFiles($directory);
				
				$this->assign('files', $files);
				$this->assign('directory', $directory);
				
				break;
				
			default:
				$directory = JRequest::getString('directory','');
				if(empty($directory)) {
					$config = AEFactory::getConfiguration();
					$this->assign('directory', $config->get('akeeba.basic.output_directory','[DEFAULT_OUTPUT]'));
				} else {
					$this->assign('directory');
				}
				
				break;
		}
		
		parent::display($tpl);
	}
}