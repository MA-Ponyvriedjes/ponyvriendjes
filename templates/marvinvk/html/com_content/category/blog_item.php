<?php
/**
 * @version		$Id: blog_item.php 22568 2011-12-20 18:25:22Z github_bot $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Create a shortcut for params.
$params = &$this->item->params;
$images = json_decode($this->item->images);
$canEdit	= $this->item->params->get('access-edit');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::core();

?>


<div class="item">
<?php echo $this->item->event->beforeDisplayContent; ?>


<?php if ($params->get('show_intro')):?>
	<?php echo $this->item->introtext; ?>
<?php else:?>
	<?php echo $this->item->fulltext; ?>
<?php endif; ?>

</div>

<?php echo $this->item->event->afterDisplayContent; ?>
