

<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
?>

<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/marvinvk/js/libs/jquery.masonry.min.js"></script>
<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/marvinvk/js/com_users/default.js"></script>

<div class="com_users profile default">
	<div class="profile<?php echo $this->pageclass_sfx?>">

			<?php if ($this->params->get('show_page_heading')) : ?>
				<h1>
					<?php echo $this->escape($this->params->get('page_heading')); ?>
				</h1>
			<?php endif; ?>

		<div class="grid">

			<?php echo $this->loadTemplate('analytics'); ?>

			<?php echo $this->loadTemplate('core'); ?>

			<?php echo $this->loadTemplate('params'); ?>

			<?php echo $this->loadTemplate('custom'); ?>

			<?php echo $this->loadTemplate('cmsnews'); ?>

			<?php echo $this->loadTemplate('klant'); ?>

			<?php echo $this->loadTemplate('social'); ?>

			<?php echo $this->loadTemplate('video'); ?>

		</div>

		<!--
		<?php if (JFactory::getUser()->id == $this->data->id) : ?>
			<a href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&user_id='.(int) $this->data->id);?>">
			<?php echo JText::_('COM_USERS_Edit_Profile'); ?></a>
		<?php endif; ?>
		</div>
		-->

</div>
