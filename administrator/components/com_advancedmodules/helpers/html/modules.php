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
 * @version			$Id: modules.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright		Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined( '_JEXEC' ) or die();

/**
 * @package			Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
abstract class JHtmlModules
{
	/**
	 * @param	int $clientId	The client id
	 * @param	string $state	 The state of the template
	 */
	static public function templates( $clientId = 0, $state = '' )
	{
		$templates = AdvancedModulesHelper::getTemplates( $clientId, $state );
		foreach ( $templates as $template ) {
			$options[] = JHtml::_( 'select.option', $template->element, $template->name );
		}
		return $options;
	}

	/**
	 */
	static public function types()
	{
		$options = array();
		$options[] = JHtml::_( 'select.option', 'user', 'COM_MODULES_OPTION_POSITION_USER_DEFINED' );
		$options[] = JHtml::_( 'select.option', 'template', 'COM_MODULES_OPTION_POSITION_TEMPLATE_DEFINED' );
		return $options;
	}

	/**
	 */
	static public function templateStates()
	{
		$options = array();
		$options[] = JHtml::_( 'select.option', '1', 'JENABLED' );
		$options[] = JHtml::_( 'select.option', '0', 'JDISABLED' );
		return $options;
	}
}
