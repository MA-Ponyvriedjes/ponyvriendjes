<?php
// No direct access.
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHTML::_('behavior.mootools');

JHTML::script('f2c_lists.js','components/com_form2content/js/');
JHTML::script('f2c_frmval.js','components/com_form2content/js/');
JHTML::script('f2c_util.js','components/com_form2content/js/');

JForm::addFieldPath(JPATH_COMPONENT_SITE . DS . 'models' . DS . 'fields');

$document =& JFactory::getDocument();
?>
<script type="text/javascript">
<!--	
var jTextUp = '<?php echo JText::_('COM_FORM2CONTENT_UP', true); ?>';
var jTextDown = '<?php echo JText::_('COM_FORM2CONTENT_DOWN', true); ?>';
var jTextAdd = '<?php echo JText::_('COM_FORM2CONTENT_ADD', true); ?>';
var jTextDelete = '<?php echo JText::_('COM_FORM2CONTENT_DELETE', true); ?>';
var jImagePath = '<?php echo JURI::root(true).'/media/com_form2content/images/'; ?>';
var dateFormat = '<?php echo $this->dateFormat; ?>';
<?php
echo $this->jsScripts['validation'];
echo $this->jsScripts['editorinit'];

$geoInit = false;

foreach($this->fields as $field)
{
	if($field->fieldtypeid == F2C_FIELDTYPE_GEOCODER)
	{
		if(!$geoInit)
		{
			JHTML::script('f2c_google.js','components/com_form2content/js/');
			JHTML::script('js?sensor=false','http://maps.google.com/maps/api/');
			
			echo "var geocoder;\n";
			$js =	'window.addEvent(\'load\', function() {
					 geocoder = new google.maps.Geocoder();
			  		 });';
			  		
			$document->addScriptDeclaration($js);		
			$geoInit = true;
		}
		
		echo "var t".$field->id."_map=null;\n";	
		echo "var t".$field->id."_marker=null;\n";			
	}
}
?>
Joomla.submitbutton = function(task) 
{
	if (task == 'form.cancel')
	{
		Joomla.submitform(task, document.getElementById('item-form'));
		return true;
	}

	if(!document.formvalidator.isValid(document.id('item-form')))
	{
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		return false;
	}

	var form = document.id('item-form');

	<?php if($this->contentTypeSettings->get('title_front_end')) : ?>		
	if(form.jform_title.value == '')
	{
		alert('<?php echo sprintf(JText::_('COM_FORM2CONTENT_ERROR_FIELD_X_REQUIRED', true), $this->form->getFieldAttribute('title', 'label')); ?>');
		return false;
	}
	<?php endif; ?>
	<?php echo $this->jsScripts['fieldval']; ?>
	if(!F2C_CheckRequiredFields(arrValidation)) return false;
	<?php 
	echo $this->jsScripts['editorsave'];
	echo $this->submitForm;
	?>
}
-->
</script>
<div class="f2c-article<?php echo htmlspecialchars($this->params->get('pageclass_sfx')); ?>">
	<h1><?php echo $this->pageTitle; ?></h1>
	<div id="f2c_form" class="content_type_<?php echo $this->item->projectid; ?>">
		<form action="<?php echo JRoute::_('index.php?option=com_form2content&view=form&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
		<?php if(!$this->contentTypeSettings->get('use_form_template', 0)) 
		{
		?>
			<table style="width:100%;">
			<tr class="f2c_buttons">
				<td>
					<div style="float: right;">
						<button type="button" class="f2c_button f2c_save" onclick="javascript:Joomla.submitbutton('form.save')"><?php echo JText::_('COM_FORM2CONTENT_TOOLBAR_SAVE'); ?></button>
						<button type="button" class="f2c_button f2c_apply" onclick="javascript:Joomla.submitbutton('form.apply')"><?php echo JText::_('COM_FORM2CONTENT_TOOLBAR_APPLY'); ?></button>
						<?php if($this->item->id == 0) { ?>
							<button type="button" class="f2c_button f2c_cancel" onclick="javascript:Joomla.submitbutton('form.cancel')"><?php echo JText::_('COM_FORM2CONTENT_TOOLBAR_CANCEL'); ?></button>
						<?php } else { ?>
							<button type="button" class="f2c_button f2c_cancel" onclick="javascript:Joomla.submitbutton('form.cancel')"><?php echo JText::_('COM_FORM2CONTENT_TOOLBAR_CLOSE'); ?></button>
						<?php } ?>
					</div>
				</td>
			</tr>
			</table>
		
			<div class="width-60 fltlft">
				<fieldset class="adminform">
				<table class="adminform" width="100%">
				<?php if($this->contentTypeSettings->get('id_front_end', 1)) : ?>				
				<tr class="f2c_field f2c_id">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('id'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('id'); ?></td>
				</tr>
				<?php endif; ?>				
				<?php if($this->contentTypeSettings->get('title_front_end')) : ?>
				<tr class="f2c_field f2c_title">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('title'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('title'); ?></td>
				</tr>
				<?php endif; ?>
				<?php if($this->contentTypeSettings->get('title_alias_front_end')) : ?>
				<tr class="f2c_field f2c_title_alias">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('alias'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('alias'); ?></td>
				</tr>
				<?php endif; ?>
				<?php if($this->contentTypeSettings->get('metadesc_front_end')) : ?>
				<tr class="f2c_field f2c_metadesc">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('metadesc'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('metadesc'); ?></td>
				</tr>
				<?php endif; ?>
				<?php if($this->contentTypeSettings->get('metakey_front_end')) : ?>
				<tr class="f2c_field f2c_metakey">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('metakey'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('metakey'); ?></td>
				</tr>
				<?php endif; ?>
				<?php if($this->contentTypeSettings->get('frontend_catsel')) : ?>
				<tr class="f2c_field f2c_catid">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('catid'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('catid'); ?></td>
				</tr>
				<?php endif; ?>
				<?php if($this->contentTypeSettings->get('author_front_end')) : ?>
				<tr class="f2c_field f2c_created_by">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('created_by'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('created_by'); ?></td>
				</tr>
				<?php endif; ?>
				<?php if($this->contentTypeSettings->get('author_alias_front_end')) : ?>
				<tr class="f2c_field f2c_created_by_alias">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('created_by_alias'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('created_by_alias'); ?></td>
				</tr>
				<?php endif; ?>			
				<?php if($this->contentTypeSettings->get('access_level_front_end')) : ?>
				<tr class="f2c_field f2c_access">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('access'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('access'); ?></td>
				</tr>
				<?php endif; ?>			
				<?php if($this->contentTypeSettings->get('frontend_templsel')) : ?>
				<tr class="f2c_field f2c_intro_template">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('intro_template'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('intro_template'); ?></td>
				</tr>
				<tr class="f2c_field f2c_main_template">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('main_template'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('main_template'); ?></td>
				</tr>
				<?php endif; ?>
				<?php if($this->contentTypeSettings->get('date_created_front_end')) : ?>
				<tr class="f2c_field f2c_created">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('created'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('created'); ?></td>
				</tr>
				<?php endif; ?>						
				<?php if($this->contentTypeSettings->get('frontend_pubsel')) : ?>
				<tr class="f2c_field f2c_publish_up">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('publish_up'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('publish_up'); ?></td>
				</tr>
				<tr class="f2c_field f2c_publish_down">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('publish_down'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('publish_down'); ?></td>
				</tr>
				<?php endif; ?>			
				<?php if($this->contentTypeSettings->get('state_front_end')) : ?>
				<tr class="f2c_field f2c_state">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('state'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('state'); ?></td>
				</tr>
				<?php endif; ?>
				<?php if($this->contentTypeSettings->get('language_front_end')) : ?>
				<tr class="f2c_field f2c_language">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('language'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('language'); ?></td>
				</tr>
				<?php endif; ?>			
				
				<?php if($this->contentTypeSettings->get('featured_front_end')) : ?>
				<tr class="f2c_field f2c_featured">
					<td valign="top" class="f2c_field_label"><?php echo $this->form->getLabel('featured'); ?></td>
					<td valign="top" class="f2c_field_value"><?php echo $this->form->getInput('featured'); ?></td>
				</tr>
				<?php endif; ?>
				<?php
				// User defined fields
				if(count($this->fields))
				{
					foreach ($this->fields as $field) 
					{
						// skip processing of hidden fields
						if(!$field->frontvisible) continue;
																				
						switch($field->fieldtypeid)
						{
							case F2C_FIELDTYPE_SINGLELINE:
								$parms = array(50, 100);
								break;
							case F2C_FIELDTYPE_IMAGE:
								$parms = array(50, 100);
								break;				
							case F2C_FIELDTYPE_MULTILINETEXT:
								$parms = array('cols="50" rows="5" style="width:500px; height:120px"');
								break;
							case F2C_FIELDTYPE_MULTILINEEDITOR:	
								$parms = array('100%', '400', '70', '15');
								break;
							default:
								$parms = array();
								break;
						}				
						?>
						<tr class="f2c_field <?php echo 'f2c_' . $field->fieldname; ?>">
							<td width="100" align="left" class="key f2c_field_label" valign="top">
								<?php echo $this->renderer->renderFieldLabel($field); ?>
							</td>
							<td valign="top" class="f2c_field_value">
								<?php echo $this->renderer->renderField($field, $parms); ?>			
							</td>
						</tr>
						<?php
					}
				}
								
				echo $this->renderCaptcha;
				?>		
				</table>						
				</fieldset>
			</div>
			<div class="clr"></div>			
			<table style="width:100%;">
			<tr class="f2c_buttons">
				<td>
					<div style="float: right;">
						<button type="button" class="f2c_button f2c_save" onclick="javascript:Joomla.submitbutton('form.save')"><?php echo JText::_('COM_FORM2CONTENT_TOOLBAR_SAVE'); ?></button>
						<button type="button" class="f2c_button f2c_apply" onclick="javascript:Joomla.submitbutton('form.apply')"><?php echo JText::_('COM_FORM2CONTENT_TOOLBAR_APPLY'); ?></button>
						<?php if($this->item->id == 0) { ?>
							<button type="button" class="f2c_button f2c_cancel" onclick="javascript:Joomla.submitbutton('form.cancel')"><?php echo JText::_('COM_FORM2CONTENT_TOOLBAR_CANCEL'); ?></button>
						<?php } else { ?>
							<button type="button" class="f2c_button f2c_cancel" onclick="javascript:Joomla.submitbutton('form.cancel')"><?php echo JText::_('COM_FORM2CONTENT_TOOLBAR_CLOSE'); ?></button>
						<?php } ?>
					</div>
				</td>
			</tr>
			</table>
		<?php 
		}
		else 
		{
			$this->renderFormTemplate();
		}
		?>		
		<div>
			<?php echo $this->form->getInput('projectid'); ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
			<input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid'); ?>" />			
			<?php echo JHtml::_('form.token'); ?>
		</div>		
		</form>
	</div>
</div>