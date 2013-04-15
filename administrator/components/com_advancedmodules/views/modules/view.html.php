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
 * @version			$Id: view.html.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright		Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined( '_JEXEC' ) or die();

jimport( 'joomla.application.component.view' );

/**
 * View class for a list of modules.
 *
 * @package			Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class AdvancedModulesViewModules extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display( $tpl = null )
	{
		$this->items = $this->get( 'Items' );
		$this->pagination = $this->get( 'Pagination' );
		$this->state = $this->get( 'State' );
		$this->getConfig();

		// Check for errors.
		if ( count( $errors = $this->get( 'Errors' ) ) ) {
			JError::raiseError( 500, implode( "\n", $errors ) );
			return false;
		}

		$this->addToolbar();
		parent::display( $tpl );
	}

	/**
	 * Function that gets the config settings
	 *
	 * @return	Object
	 */
	protected function getConfig()
	{
		if ( !isset( $this->config ) ) {
			require_once JPATH_PLUGINS.'/system/nnframework/helpers/parameters.php';
			$parameters = NNParameters::getInstance();
			$config = JComponentHelper::getParams( 'com_advancedmodules' );
			$this->config = $parameters->getParams( $config->toObject(), JPATH_ADMINISTRATOR.'/components/com_advancedmodules/config.xml' );
		}
		return $this->config;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get( 'State' );
		$canDo = AdvancedModulesHelper::getActions();

		JToolBarHelper::title( JText::_( 'AMM_ADVANCED_MODULES_MANAGER' ), 'module.png' );

		if ( $canDo->get( 'core.create' ) ) {
			//JToolBarHelper::addNew('module.add');
			$bar = JToolBar::getInstance( 'toolbar' );
			$bar->appendButton( 'Popup', 'new', 'JTOOLBAR_NEW', 'index.php?option=com_advancedmodules&amp;view=select&amp;tmpl=component', 850, 400 );
		}

		if ( $canDo->get( 'core.edit' ) ) {
			JToolBarHelper::editList( 'module.edit' );
		}

		if ( $canDo->get( 'core.create' ) ) {
			JToolBarHelper::custom( 'modules.duplicate', 'copy.png', 'copy_f2.png', 'JTOOLBAR_DUPLICATE', true );
		}

		if ( $canDo->get( 'core.edit.state' ) ) {
			JToolBarHelper::divider();
			JToolBarHelper::publish( 'modules.publish', 'JTOOLBAR_PUBLISH', true );
			JToolBarHelper::unpublish( 'modules.unpublish', 'JTOOLBAR_UNPUBLISH', true );
			JToolBarHelper::divider();
			if ( version_compare( JVERSION, '1.7.0', 'l' ) ) {
				JToolBarHelper::custom( 'modules.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true );
			} else {
				JToolBarHelper::checkin( 'modules.checkin' );
			}
		}

		if ( $state->get( 'filter.state' ) == -2 && $canDo->get( 'core.delete' ) ) {
			JToolBarHelper::deleteList( '', 'modules.delete', 'JTOOLBAR_EMPTY_TRASH' );
			JToolBarHelper::divider();
		} else if ( $canDo->get( 'core.edit.state' ) ) {
			JToolBarHelper::trash( 'modules.trash' );
			JToolBarHelper::divider();
		}

		if ( $canDo->get( 'core.admin' ) ) {
			JToolBarHelper::preferences( 'com_advancedmodules' );
			JToolBarHelper::divider();
		}
		JToolBarHelper::help( 'JHELP_EXTENSIONS_MODULE_MANAGER' );
	}
}
