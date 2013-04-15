<?php
/**
 * @package			Advanced Module Manager
 * @version			2.2.16
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2011 NoNumber! All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @version			$Id: modules.php 21097 2011-04-07 15:38:03Z dextercowley $
 * @package			Joomla.Administrator
 * @subpackage		com_modules
 * @copyright		Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined( '_JEXEC' ) or die();

// Access check.
if ( !JFactory::getUser()->authorise( 'core.manage', 'com_advancedmodules' ) ) {
	return JError::raiseWarning( 404, JText::_( 'JERROR_ALERTNOAUTHOR' ) );
}

$lang = JFactory::getLanguage();
$lang->load( 'com_modules', JPATH_ADMINISTRATOR );
if ( $lang->getTag() != 'en-GB' ) {
	// Loads English language file as fallback (for undefined stuff in other language file)
	$lang->load( 'com_advancedmodules', JPATH_ADMINISTRATOR, 'en-GB' );
}
$lang->load( 'com_advancedmodules', JPATH_ADMINISTRATOR, null, 1 );

jimport( 'joomla.filesystem.file' );
$app = JFactory::getApplication();

// return if NoNumber! Framework plugin is not installed
if ( !JFile::exists( JPATH_PLUGINS.'/system/nnframework/nnframework.php' ) ) {
	$app->set( '_messageQueue', '' );
	$app->enqueueMessage( JText::_( 'AMM_NONUMBER_FRAMEWORK_PLUGIN_NOT_INSTALLED' ), 'error' );
	return;
}

// give notice if NoNumber! Framework plugin is not enabled
$nnep = JPluginHelper::getPlugin( 'system', 'nnframework' );
if ( !isset( $nnep->name ) ) {
	$app->set( '_messageQueue', '' );
	$app->enqueueMessage( JText::_( 'AMM_NONUMBER_FRAMEWORK_PLUGIN_NOT_ENABLED' ), 'notice' );
	return;
}

// load the NoNumber! Framework language file
if ( $lang->getTag() != 'en-GB' ) {
	// Loads English language file as fallback (for undefined stuff in other language file)
	$lang->load( 'plg_system_nnframework', JPATH_ADMINISTRATOR, 'en-GB' );
}
$lang->load( 'plg_system_nnframework', JPATH_ADMINISTRATOR, null, 1 );

// Include dependancies
jimport( 'joomla.application.component.controller' );
$controller = JController::getInstance( 'AdvancedModules' );
$controller->execute( JRequest::getCmd( 'task' ) );

$controller->redirect();
