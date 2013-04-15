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
	dl { display: none; }
</style>

<div id="backup-percentage">
	<div id="progressbar-inner" class="color-overlay"></div>
</div>

<div id="backup-status">
	<div id="backup-substep">
		<?php echo JText::sprintf('REMOTEFILES_LBL_DOWNLOADEDSOFAR', $this->done, $this->total, $this->percent); ?>
	</div>
</div>

<form action="index.php" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="com_akeeba" />
	<input type="hidden" name="view" value="remotefiles" />
	<input type="hidden" name="task" value="dltoserver" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="id" value="<?php echo JRequest::getInt('id', 0) ?>" />
	<input type="hidden" name="part" value="<?php echo JRequest::getInt('part', -1) ?>" />
	<input type="hidden" name="frag" value="<?php echo JRequest::getInt('frag', -1) ?>" />
</form>

</div>