<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2011 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id$
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');
?>
<div id="akeeba-container" style="width:100%">
	
<div id="restoration-progress" class="akeeba-restore">
	<div class="ui-state-highlight" style="padding: 0.3em; margin: 0.3em 0.2em; font-weight: bold;">
			<span class="ui-icon ui-icon-notice" style="float: left;"></span>
			<?php echo JText::_('RESTORE_LABEL_DONOTCLOSE'); ?>
	</div>
	<fieldset>
		<legend><?php echo JText::_('RESTORE_LABEL_INPROGRESS') ?></legend>
		<div id="extprogress">
			<div class="extprogrow">
				<span class="extlabel"><?php echo JText::_('RESTORE_LABEL_BYTESREAD'); ?></span>
				<span class="extvalue" id="extbytesin"></span>
			</div>
			<div class="extprogrow">
				<span class="extlabel"><?php echo JText::_('RESTORE_LABEL_BYTESEXTRACTED'); ?></span>
				<span class="extvalue" id="extbytesout"></span>
			</div>
			<div class="extprogrow">
				<span class="extlabel"><?php echo JText::_('RESTORE_LABEL_FILESEXTRACTED'); ?></span>
				<span class="extvalue" id="extfiles"></span>
			</div>
		</div>
		<div id="response-timer" class="ui-corner-all">
			<div class="color-overlay"></div>
			<div class="text"></div>
		</div>
	</fieldset>
</div>

<div id="restoration-error" style="display:none">
	<fieldset>
		<legend><?php echo JText::_('RESTORE_LABEL_FAILED'); ?></legend>
		<div id="errorframe">
			<p><?php echo JText::_('RESTORE_LABEL_FAILED_INFO'); ?></p>
			<p id="backup-error-message">
			</p>
		</div>
	</fieldset>
</div>

<div id="restoration-extract-ok" style="display:none">
	<fieldset>
		<legend><?php echo JText::_('RESTORE_LABEL_SUCCESS'); ?></legend>
		<p>
			<?php echo JText::_('RESTORE_LABEL_SUCCESS_INFO2'); ?>
		</p>
		<button id="restoration-runinstaller" onclick="return false;"><?php echo JText::_('RESTORE_LABEL_RUNINSTALLER'); ?></button>
		<button id="restoration-finalize" onclick="return false;"><?php echo JText::_('RESTORE_LABEL_FINALIZE'); ?></button>
		<p>
			<?php echo JText::_('RESTORE_LABEL_SUCCESS_INFO2B'); ?>
		</p>
	</fieldset>
</div>

<script type="text/javascript">
	var akeeba_restoration_password = '<?php echo $this->password; ?>';
	var akeeba_restoration_ajax_url = '<?php echo JURI::base() ?>/components/com_akeeba/restore.php';

	(function($){
		$(document).ready(function(){
			pingRestoration();
		});

		$('#restoration-runinstaller').click(runInstaller);
		$('#restoration-finalize').click(finalizeRestoration);
	})(akeeba.jQuery);
</script>

</div>