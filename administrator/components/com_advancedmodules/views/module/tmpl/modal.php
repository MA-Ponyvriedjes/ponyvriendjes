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
 * @version			$Id: modal.php 21097 2011-04-07 15:38:03Z dextercowley $
 * @package			Joomla.Administrator
 * @subpackage	com_modules
 * @copyright		Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined( '_JEXEC' ) or die();
?>
<div class="fltrt">
	<button type="button" onclick="Joomla.submitbutton('module.save');">
		<?php echo JText::_( 'JSAVE' );?></button>
	<button type="button" onclick="window.parent.SqueezeBox.close();">
		<?php echo JText::_( 'JCANCEL' );?></button>
</div>
<div class="clr"></div>

<?php
$this->setLayout( 'edit' );
echo $this->loadTemplate();