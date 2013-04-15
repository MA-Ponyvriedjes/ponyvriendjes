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
?>
<div id="akeeba-container" style="width:100%">
	
<style type="text/css">
	dt.message { display: none; }
	dd.message { list-style: none; }
</style>

<h1><?php echo JText::_('AKEEBA_REMOTEFILES') ?></h1>

<?php if(empty($this->actions)): ?>
<div class="ui-state-error ui-corner-all">
	<h3><?php echo JText::_('REMOTEFILES_ERR_NOTSUPPORTED_HEADER') ?></h3>
	<p>
		<?php echo JText::_('REMOTEFILES_ERR_NOTSUPPORTED'); ?>
	</p>
</div>
<?php else: ?>

<div id="cpanel">
<?php foreach($this->actions as $action): ?>
<?php if($action['type'] == 'button'): ?>
	<button onclick="window.location = '<?php echo $action['link'] ?>'; return false;"><?php echo $action['label']; ?></button>
<?php endif; ?>
<?php endforeach; ?>
</div>
<div style="clear: both;"></div>

<h2><?php echo JText::_('REMOTEFILES_LBL_DOWNLOADLOCALLY')?></h2>
<?php $items = 0; foreach($this->actions as $action): ?>
<?php if($action['type'] == 'link'): ?>
<?php $items++ ?>
	<a href="<?php echo $action['link'] ?>"><?php echo $action['label'] ?></a> &bull;
<?php endif; ?>
<?php endforeach; ?>

<?php if(!$items): ?>
	<p>
		<?php echo JText::_('REMOTEFILES_LBL_NOTSUPPORTSLOCALDL') ?>
	</p>
<?php endif; ?>

<?php endif; ?>

</div>