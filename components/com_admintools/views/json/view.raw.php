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
jimport('joomla.application.component.view');

class AdmintoolsViewJson extends JView
{
	public function display($tpl = null)
	{
		if(function_exists('ob_start')) @ob_start();

		$sourceJSON = JRequest::getVar('json', null, 'default', 'raw', 2);
		$model = JModel::getInstance('Json','AdmintoolsModel');
		
		$json = $model->execute($sourceJSON);

		$this->assign('json',	$json);

		// # Fix 2.4: Drop the output buffer
		if(function_exists('ob_clean')) @ob_clean();
		parent::display('raw');
	}
}
?>