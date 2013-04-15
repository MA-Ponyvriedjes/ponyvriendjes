<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 3.2
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

$hasFiles = !empty($this->files);
?>
<div id="akeeba-container" style="width:100%">

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="view" value="discover" />
	<?php if($hasFiles): ?>
	<input type="hidden" name="task" value="import" />
	<input type="hidden" name="directory" value="<?php echo $this->directory ?>" />
	<?php else: ?>
	<input type="hidden" name="task" value="default" />
	<?php endif; ?>
	<input type="hidden" name="<?php echo JUtility::getToken();?>" value="1" />
	
	<?php if($hasFiles): ?>
	<fieldset>
		<legend><?php echo JText::_('DISCOVER_LABEL_DIRECTORY'); ?></legend>
		<table class="adminTable">
			<tr>
				<td><label for="directory2"><?php echo JText::_('DISCOVER_LABEL_DIRECTORY') ?></label></td>
				<td>
					<input type="text" name="directory2" id="directory2" value="<?php echo $this->directory ?>" disabled="disabled" size="70" />
				</td>
			</tr>
		</table>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('DISCOVER_LABEL_FILES'); ?></legend>
		<p><?php echo JText::_('DISCOVER_LABEL_SELECTFILES'); ?></p>
		<p>
			<button onclick="this.form.submit(); return false;"><?php echo JText::_('DISCOVER_LABEL_IMPORT') ?></button>
		</p>
		<select name="files[]" id="files" multiple="multiple">
		<?php foreach($this->files as $file): ?>
			<option value="<?php echo $this->escape(basename($file)); ?>"><?php echo $this->escape(basename($file)); ?></option>
		<?php endforeach; ?>
		</select>
		
	</fieldset>
	<?php else: ?>
	<p>
		<?php echo JText::_('DISCOVER_ERROR_NOFILES'); ?>
	</p>
	<p>
		<button onclick="this.form.submit(); return false;"><?php echo JText::_('DISCOVER_LABEL_GOBACK') ?></button>
	</p>
	<?php endif; ?>	
</form>
	
</div>