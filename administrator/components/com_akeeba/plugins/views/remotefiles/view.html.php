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
 * Operations against remote files
 */
class AkeebaViewRemotefiles extends JView
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
		// Set the page title
		JToolBarHelper::title(JText::_('AKEEBA_REMOTEFILES'),'akeeba');

		// Add references to scripts and CSS
		AkeebaHelperIncludes::includeMedia(true);
		
		$model = JModel::getInstance('Remotefiles','AkeebaModel');
		$id = JRequest::getInt('id', 0);
		
		// Load the appropriate template based on the task
		$task = JRequest::getCmd('task');
		switch($task)
		{
			case 'listactions':
				$tpl = null;
				
				$actions = $model->getActions($id);
				
				$this->assign('actions',			$actions);
				break;
				
			case 'dltoserver':
				$tpl='dlprogress';
				
				// Get progress bar stats
				$session =& JFactory::getSession();
				$total = $session->get('dl_totalsize', 0, 'akeeba');
				$done = $session->get('dl_donesize', 0, 'akeeba');
				if($total <= 0) {
					$percent = 0;
				} else {
					$percent = (int)(100 * ($done/$total) );
					if($percent < 0) $percent = 0;
					if($percent > 100) $percent = 100; 
				}
				$this->assign('total', $total);
				$this->assign('done', $done);
				$this->assign('percent', $percent);
				
				// Render the progress bar
				$document = JFactory::getDocument();
		
				$script = "window.addEvent( 'domready' ,  function() {\n";
				$script .= "$('progressbar-inner').setStyle('width', '$percent%');\n";
				$script .= "document.forms.adminForm.submit();\n";
				$script .= "});\n";
				$document->addScriptDeclaration($script);
				
				break;
				
			default:
				JError::raiseError(500, 'This task must be handled by the RAW view');
				return false;
				break;
		}
		
		parent::display($tpl);
	}
}