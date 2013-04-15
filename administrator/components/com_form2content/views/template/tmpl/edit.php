<?php 
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.mootools');

require_once(JPATH_COMPONENT_SITE.DS.'shared.form2content.php');
require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');
?>
<script type="text/javascript">
<!--
Joomla.submitbutton = function(task) 
{
	if (task == 'template.cancel') 
	{
		Joomla.submitform(task, document.getElementById('item-form'));
		return true;
	}
	
	Joomla.submitform(task, document.getElementById('item-form'));
	return true;
}
-->	
</script>
<form action="<?php echo JRoute::_('index.php?option=com_form2content&task=template.edit&layout=edit&id='.urlencode($this->id)); ?>" method="post" name="adminForm" id="item-form">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_FORM2CONTENT_DETAILS'); ?></legend>
		<table class="admintable" cellspacing="0" cellpadding="0" border="0" width="80%">
		<tr>
			<td width="150" align="right" class="key">			
				<label for="title">
					<?php echo JText::_('COM_FORM2CONTENT_TITLE'); ?>:
				</label>
			</td>
			<td>
				<?php echo HtmlHelper::stringHTMLSafe($this->id); ?>
			</td>
		</tr>
		<tr>
			<td width="150" align="right" class="key" valign="top">			
				<label for="template">
					<?php echo JText::_('COM_FORM2CONTENT_TEMPLATE'); ?>:
				</label>
			</td>
			<td>
				<?php echo '<textarea class="text_area" name="template" rows="30" style="width:750px;">' . HtmlHelper::stringHTMLSafe($this->item) . '</textarea>'; ?>
			</td>
		</tr>		
	</table>
	</fieldset>
</div>
<div class="clr"></div>
<?php echo DisplayCredits(); ?>
<div>
	<input type="hidden" name="id" value="<?php echo HtmlHelper::stringHTMLSafe($this->id); ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>

