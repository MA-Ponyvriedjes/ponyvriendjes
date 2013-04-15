<?php 
defined('_JEXEC') or die('Restricted acccess');

JHtml::_('behavior.mootools');

require_once(JPATH_COMPONENT_SITE.DS.'shared.form2content.php');
require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');
?>
<script type="text/javascript">
<!--
Joomla.submitbutton = function(task) 
{
	if (task == 'template.delete')
	{
		if(!confirm('<?php echo JText::_('COM_FORM2CONTENT_CONFIRM_ITEMS_DELETE', true); ?>'))
		{
			return false;
		}
	}
	else if (task == 'template.upload')
	{
		var upload = document.getElementById('upload');
	
		if(!upload.value)
		{
			alert('<?php echo JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_FILE_UPLOAD_EMPTY'); ?>');
			return false;
		}		
	}
	
	Joomla.submitform(task, document.getElementById('item-form'));
}
-->
</script>
<form action="<?php echo JRoute::_('index.php?option=com_form2content&view=templates');?>" method="post" name="adminForm" id="item-form" enctype="multipart/form-data">
<fieldset>
    <legend><?php echo JText::_('COM_FORM2CONTENT_UPLOAD_TEMPLATE'); ?> [ <?php echo JText::_('COM_FORM2CONTENT_MAX_SIZE'); ?> =&nbsp;<?php echo ini_get('post_max_size'); ?> ]</legend>
    <fieldset class="actions">
        <input class="inputbox" type="file" name="upload" id="upload" size="63" />
    </fieldset>
</fieldset>
<div class="clr"></div>
<table class="adminlist">
<thead>
	<tr>
		<th width="1%">
			<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
		</th>		
		<th align="left">
			<?php echo JText::_('COM_FORM2CONTENT_FILE_NAME'); ?>
		</th>
		<th align="center" width="1%">
			<?php echo JText::_('COM_FORM2CONTENT_DOWNLOAD'); ?>
		</th>
		<th align="left">
			<?php echo JText::_('COM_FORM2CONTENT_FILE_SIZE'); ?>
		</th>
	</tr>			
</thead>
<tbody>
	<?php foreach ($this->items as $i => $item) : ?>
	<tr class="row<?php echo $i % 2; ?>">
		<td class="center">
			<?php echo JHtml::_('grid.id', $i, HtmlHelper::stringHTMLSafe(JFile::getName($item->id))); ?>
		</td>
		<td>
			<a href="<?php echo JRoute::_('index.php?option=com_form2content&task=template.edit&id='.urlencode($item->fileName));?>">
				<?php echo $this->escape($item->fileName); ?>
			</a>
		</td>		
		<td align="center">
			<a href="<?php echo JURI::root() . 'media/com_form2content/templates/' . HtmlHelper::stringHTMLSafe($item->fileName); ?>" target="_blank" title="<?php echo JText::_('COM_FORM2CONTENT_DOWNLOAD'); ?>">
				<img src="<?php echo JURI::root(); ?>media/com_form2content/images/save.png" width="16" height="16" alt="<?php echo JText::_('COM_FORM2CONTENT_DOWNLOAD'); ?>" />
			</a>
		</td>
		<td>
			<?php echo $item->fileSize; ?>
		</td>
	</tr>	
	<?php endforeach; ?>
</tbody>
</table>

<?php echo DisplayCredits(); ?>

<div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>