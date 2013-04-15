<?php
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');

JHtml::_('behavior.mootools');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) 
{
	if (task == 'translation.cancel') 
	{
		Joomla.submitform(task, document.getElementById('item-form'));
		return true;
	}
	
	if(!document.formvalidator.isValid(document.id('item-form')))
	{
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		return false;
	}
		
	Joomla.submitform(task, document.getElementById('item-form'));
	return true;
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_form2content&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_FORM2CONTENT_TRANSLATION_ADD') : JText::sprintf('COM_FORM2CONTENT_TRANSLATION_EDIT', $this->item->id); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('id') . $this->form->getInput('id'); ?></li>
				<li><?php echo $this->form->getLabel('language_id') . $this->form->getInput('language_id'); ?></li>
				<li><?php echo $this->form->getLabel('reference_id') . $this->form->getInput('reference_id'); ?></li>
				<li><?php echo $this->form->getLabel('title_original') . $this->form->getInput('title_original'); ?></li>
				<li><?php echo $this->form->getLabel('title_translation') . $this->form->getInput('title_translation'); ?></li>
				<li><?php echo $this->form->getLabel('description_translation') . $this->form->getInput('description_translation'); ?></li>
				<li><?php echo $this->form->getLabel('description_original') . $this->form->getInput('description_original'); ?></li>
			</ul>
		</fieldset>		
	</div>
	<div class="clr"></div>
	<?php echo DisplayCredits(); ?>	
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>	
</form>