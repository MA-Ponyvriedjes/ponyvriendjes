<?php
/**
 * @version		$Id: default_logout.php 22060 2011-09-12 14:14:55Z infograf768 $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

defined('_JEXEC') or die;
?>

<div class="com_users login logout">
	<div class="logout<?php echo $this->pageclass_sfx?>">

		<h1>Hier kunt u uitloggen.</h1>

		<?php if ($this->params->get('show_page_heading')) : ?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
		<?php endif; ?>

		<?php if (($this->params->get('logoutdescription_show') == 1 && str_replace(' ', '', $this->params->get('logout_description')) != '')|| $this->params->get('logout_image') != '') : ?>
		<div class="logout-description">
		<?php endif ; ?>
			

		<?php if (($this->params->get('logoutdescription_show') == 1 && str_replace(' ', '', $this->params->get('logout_description')) != '')|| $this->params->get('logout_image') != '') : ?>
		</div>
		<?php endif ; ?>

		<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.logout'); ?>" method="post">
			<div>
				<button type="submit" class="button">Uitloggen</button>
				<input type="hidden" name="return" value="<?php echo base64_encode($this->params->get('logout_redirect_url',$this->form->getValue('return'))); ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</form>
	</div>
</div>
