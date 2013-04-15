<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * Multiple databases definition View
 *
 */
class AkeebaViewRestore extends JView
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
		JToolBarHelper::title(JText::_('AKEEBA').': <small>'.JText::_('RESTORATION').'</small>','akeeba');
		JToolBarHelper::back('AKEEBA_CONTROLPANEL', 'index.php?option='.JRequest::getCmd('option'));
		
		// Add references to scripts and CSS
		AkeebaHelperIncludes::includeMedia(true);
		$media_folder = JURI::base().'../media/com_akeeba/';
		$document =& JFactory::getDocument();
		$document->addScript($media_folder.'plugins/js/encryption.js');

		$task = JRequest::getVar('task','');
		$model =& $this->getModel();
		if($task == 'start')
		{
			$password = JRequest::getVar('password','','default','none',2);
			$this->assign('password', $password );	
			$this->setLayout('restore');
		}
		else
		{
			$id					= $model->getId();
			$ftpparams			= $model->getFTPParams();
			$extractionmodes	= $model->getExtractionModes();
			
			$this->assign('id', $id);
			$this->assign('ftpparams', $ftpparams);
			$this->assign('extractionmodes', $extractionmodes);
		}

		// Add live help
		AkeebaHelperIncludes::addHelp();

		parent::display();
	}

}