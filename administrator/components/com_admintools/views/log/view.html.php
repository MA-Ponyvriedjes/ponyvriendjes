<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2011 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'base.view.html.php';

class AdmintoolsViewLog extends AdmintoolsViewBase
{
	protected function onDisplay()
	{
		$app = JFactory::getApplication();
		$hash = $this->getHash();

		// ...filter states
		$this->lists->set('fltDateFrom',	$app->getUserStateFromRequest($hash.'filter_from',
			'datefrom', null));
		$this->lists->set('fltDateTo',		$app->getUserStateFromRequest($hash.'filter_to',
			'dateto', null));
		$this->lists->set('fltIP',			$app->getUserStateFromRequest($hash.'filter_ip',
			'ip', null));
		$this->lists->set('fltURL',			$app->getUserStateFromRequest($hash.'filter_url',
			'url', null));
		$this->lists->set('fltReason',		$app->getUserStateFromRequest($hash.'filter_reason',
			'reason', null));

		// Add toolbar buttons
		JToolBarHelper::deleteList();
		JToolBarHelper::divider();
		JToolBarHelper::back((ADMINTOOLS_JVERSION == '15') ? 'Back' : 'JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');

		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'select.php';

		// Run the parent method
		parent::onDisplay();
	}
}