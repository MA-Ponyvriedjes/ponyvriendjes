<?php
/**
 * @version		$Id: default_core.php 22429 2011-12-02 20:34:43Z github_bot $
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

?>
<div class="com_users profile core column">
	<fieldset id="users-profile-core">
		<h2>
			<?php echo JText::_('COM_USERS_PROFILE_CORE_LEGEND'); ?>
		</h2>
		<div class="profile-gegevens">

			<div class="row">
				<div class="label name">
					<?php echo JText::_('COM_USERS_PROFILE_NAME_LABEL'); ?>
				</div>
				<div class="gegeven name">
					<?php echo $this->data->name; ?>
				</div>
			</div>

			<div class="row">
				<div class="label username">
					<?php echo JText::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?>
				</div>
				<div class="gegeven username">
					<?php echo $this->data->username; ?>
				</div>
			</div>

			<div class="row">
				<div class="label registerdate">
					<?php echo JText::_('COM_USERS_PROFILE_REGISTERED_DATE_LABEL'); ?>
				</div>
				<div class="gegeven registerdate">
					<?php echo JHtml::_('date',$this->data->registerDate); ?>
				</div>
			</div>

			<div class="row">
				<div class="label visitdate">
					<?php echo JText::_('COM_USERS_PROFILE_LAST_VISITED_DATE_LABEL'); ?>
				</div>

				<?php if ($this->data->lastvisitDate != '0000-00-00 00:00:00'){?>
					<div class="gegeven visitdate">
						<?php echo JHtml::_('date',$this->data->lastvisitDate); ?>
					</div>
				<?php }
				else {?>
					<div class="gegeven visitdate">
						<?php echo JText::_('COM_USERS_PROFILE_NEVER_VISITED'); ?>
					</div>
				<?php } ?>
			</div>

		</div>
	</fieldset>
</div>
