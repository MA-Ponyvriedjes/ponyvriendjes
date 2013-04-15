<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php 
require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');

JHtml::_('behavior.mootools');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

JForm::addFieldPath(JPATH_COMPONENT . DS . 'models' . DS . 'fields');
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) 
{
	if (task == 'project.cancel') 
	{
		Joomla.submitform(task, document.getElementById('item-form'));
		return true;
	}
	
	if(!document.formvalidator.isValid(document.id('item-form')))
	{
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		return false;
	}
	
	var fldTitleFrontEnd = document.getElementById('jform_settings_title_front_end');
	var fldTitleDefault = document.getElementById('jform_settings_title_default');
	
	if(fldTitleFrontEnd.value == 0 && fldTitleDefault.value == '')
	{
		alert("<?php echo JText::_('COM_FORM2CONTENT_ERROR_PROJECT_TITLE_DEFAULT_EMPTY', true); ?>");
		return false;		
	}

	var fldTemplateFrontEnd = document.getElementById('jform_settings_frontend_templsel');
	var fldIntroTemplate = document.getElementById('jform_settings_intro_template_id');

	if(fldTemplateFrontEnd.value == 0 && fldIntroTemplate.value == '')
	{
		alert("<?php echo JText::_('COM_FORM2CONTENT_ERROR_PROJECT_INTRO_TEMPLATE_DEFAULT_EMPTY', true); ?>");
		return false;		
	}

	var fldCatFrontEnd = document.getElementById('jform_settings_frontend_catsel');
	var fldCat = document.getElementById('jform_settings_catid');

	if(fldCatFrontEnd.value == 0 && fldCat.value == -1)
	{
		alert("<?php echo JText::_('COM_FORM2CONTENT_ERROR_PROJECT_SECTION_CATEGORY_DEFAULT_EMPTY', true); ?>");
		return false;		
	}
	
	Joomla.submitform(task, document.getElementById('item-form'));
	return true;
}

function syncmetadata()
{
	if(confirm("<?php echo JText::_('COM_FORM2CONTENT_SYNC_METADATA_CONFIRM', true); ?>"))
	{
		Joomla.submitform('project.syncmetadata', document.getElementById('item-form'));
	}
	else
	{
		return false;
	}
}

function syncjadvparms()
{
	if(confirm("<?php echo JText::_('COM_FORM2CONTENT_SYNC_JADVPARMS_CONFIRM', true); ?>"))
	{
		Joomla.submitform('project.syncjadvparms', document.getElementById('item-form'));
	}
	else
	{
		return false;
	}
}

function generateDefaultFormTemplate(id, overwrite)
{
	var url = '<?php echo JURI::base(); ?>index.php?option=com_form2content&task=project.createsampleformtemplate&format=raw&view=project&id='+id+'&overwrite='+overwrite;
	var overwriteTemplate = '<?php echo JText::_('COM_FORM2CONTENT_OVERWRITE_DEFAULT_FORM_TEMPLATE', true); ?>';
	var writtenTemplate = '<?php echo JText::_('COM_FORM2CONTENT_FORM_TEMPLATE_WRITTEN', true); ?>';

	var x = new Request({
        url: url, 
        method: 'get', 
        onRequest: function()
        {
        },
        onSuccess: function(response)
        {
            result = response.split(';');

            if(result[0] == 0)
            {
                alert(writtenTemplate.replace('%s', result[1]));
            }
            else
            {
                if(confirm(overwriteTemplate.replace('%s', result[1])))
                {
                	generateDefaultFormTemplate(id, 1);
                }
            }
        	return true;
        },
        onFailure: function()
        {
             alert('Error generating template.');
        }                
    }).send();
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_form2content&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_FORM2CONTENT_CONTENTTYPE_ADD') : JText::sprintf('COM_FORM2CONTENT_CONTENTTYPE_EDIT', $this->item->id); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('id') . $this->form->getInput('id'); ?></li>
				<li><?php echo $this->form->getLabel('title') . $this->form->getInput('title'); ?></li>
				<li><?php echo $this->form->getLabel('article_caption', 'settings') . $this->form->getInput('article_caption', 'settings'); ?></li>
			</ul>
		</fieldset>		
		<?php 
		echo $this->form->getInput('published');		
		echo $this->form->getInput('created_by');
		echo $this->form->getInput('created');
		echo $this->form->getInput('modified');
		echo $this->form->getInput('version');
				
		echo JHtml::_('tabs.start','form2content-project-tabs-'.$this->item->id, array('useCookie'=>1));
		echo JHtml::_('tabs.panel',JText::_('COM_FORM2CONTENT_F2C_SETTINGS'), 'form2content-settings-details');
		 
		$fieldSets = $this->form->getFieldsets('settings');

		foreach ($fieldSets as $name => $fieldSet)
		{
			?>
			<fieldset class="adminform">
				<legend><?php echo !empty($fieldSet->description) ? $this->escape(JText::_($fieldSet->description)) : ''; ?></legend>			
				<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset($name) as $field) : ?>
					<li><?php echo $field->label; ?><?php echo $field->input; ?></li>
				<?php endforeach; ?>
				<?php if($fieldSet->name == 'form_template') : ?>
					<li>
						<label>&nbsp;</label>
						<input type="button" value="<?php echo JText::_('COM_FORM2CONTENT_GENERATE_DEFAULT_FORM_TEMPLATE');?>" onclick="generateDefaultFormTemplate(<?php echo $this->item->id; ?>, 0);" />
					</li>
				<?php endif; ?>
				</ul>
			</fieldset>
		<?php
		}
		
		echo JHtml::_('tabs.panel',JText::_('COM_FORM2CONTENT_JOOMLA_ADVANCED_ARTICLE_PARAMETERS'), 'form2content-settings-details'); ?>
		
		<?php $fieldSets = $this->form->getFieldsets('attribs');?>
		<?php foreach ($fieldSets as $name => $fieldSet) :?>
			<?php if (isset($fieldSet->description) && trim($fieldSet->description)) :?>
				<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
			<?php endif;?>
			<fieldset class="panelform">
				<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset($name) as $field) : ?>
					<li><?php echo $field->label; ?><?php echo $field->input; ?></li>
				<?php endforeach; ?>
				<li>
					<label id="jform_attribs_sync-lbl" for="jform_attribs_sync" class="hasTip" title="<?php echo JText::_('COM_FORM2CONTENT_SYNCHRONIZE'); ?>::<?php echo JText::_('COM_FORM2CONTENT_SYNC_JADVPARMS_DESC'); ?>"><?php echo JText::_('COM_FORM2CONTENT_SYNCHRONIZE'); ?></label>
					<input type="button" name="jform[attribs][sync]" id="jform_attribs_sync" value="<?php echo JText::_('COM_FORM2CONTENT_SYNC_EXISTING_ARTICLES'); ?>" class="button" onclick="syncjadvparms();" />
				</li>				
				</ul>
			</fieldset>
		<?php endforeach; ?>
		
		<?php echo JHtml::_('tabs.panel',JText::_('COM_FORM2CONTENT_METADATA_INFORMATION'), 'meta-options'); ?>
			<fieldset class="panelform">
				<?php echo $this->loadTemplate('metadata'); ?>
				<label id="jform_metadata_sync-lbl" for="jform_metadata_sync" class="hasTip" title="<?php echo JText::_('COM_FORM2CONTENT_SYNCHRONIZE'); ?>::<?php echo JText::_('COM_FORM2CONTENT_SYNC_METADATA_DESC'); ?>"><?php echo JText::_('COM_FORM2CONTENT_SYNCHRONIZE'); ?></label>
				<input type="button" name="jform[metadata][sync]" id="jform_metadata_sync" value="<?php echo JText::_('COM_FORM2CONTENT_SYNC_EXISTING_ARTICLES'); ?>" class="button" onclick="syncmetadata();" />
			</fieldset>	
		<?php echo JHtml::_('tabs.panel',JText::_('COM_FORM2CONTENT_FIELDSET_RULES_CONTENTTYPE'), 'access-rules'); ?>
			<fieldset class="panelform">
				<?php echo $this->form->getLabel('rules'); ?>
				<?php echo $this->form->getInput('rules'); ?>
			</fieldset>			
		<?php 
		echo JHtml::_('tabs.end'); 
		?>
	</div>
	<div class="clr"></div>
	<?php echo DisplayCredits(); ?>	
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>	
</form>