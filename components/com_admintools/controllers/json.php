<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.controller');

class AdmintoolsControllerJson extends JController
{
	/**
	 * Starts a backup
	 * @return
	 */
	public function display()
	{
		// Use the model to parse the JSON message
		if(function_exists('ob_start')) @ob_start();
		$sourceJSON = JRequest::getVar('json', null, 'default', 'raw', 2);
		// On some !@#$%^& servers where magic_quotes_gpc is On we might get extra slashes added
		if(function_exists('get_magic_quotes_gpc')) {
			if(get_magic_quotes_gpc()) {
				$sourceJSON = stripslashes($sourceJSON);
			}
		}
		
		$model = JModel::getInstance('Json','AdmintoolsModel');
		$json = $model->execute($sourceJSON);
		if(function_exists('ob_clean')) @ob_clean();
		
		// Just dump the JSON and tear down the application, without plugins executing
		echo $json;	
		$app = JFactory::getApplication();
		$app->close();
	}

}

