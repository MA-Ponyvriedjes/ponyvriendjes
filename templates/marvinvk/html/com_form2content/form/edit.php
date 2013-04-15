<?php
// No direct access.
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHTML::_('behavior.mootools');

JHTML::script('f2c_lists.js','components/com_form2content/js/');
JHTML::script('f2c_frmval.js','components/com_form2content/js/');
JHTML::script('f2c_util.js','components/com_form2content/js/');

JForm::addFieldPath(JPATH_COMPONENT_SITE . DS . 'models' . DS . 'fields');

$document =& JFactory::getDocument();
$user =& JFactory::getUser();
?>
<script type="text/javascript">
<!--	
var jTextUp = '<?php echo JText::_('COM_FORM2CONTENT_UP', true); ?>';
var jTextDown = '<?php echo JText::_('COM_FORM2CONTENT_DOWN', true); ?>';
var jTextAdd = '<?php echo JText::_('COM_FORM2CONTENT_ADD', true); ?>';
var jTextDelete = '<?php echo JText::_('COM_FORM2CONTENT_DELETE', true); ?>';
var jImagePath = '<?php echo JURI::root(true).'/media/com_form2content/images/'; ?>';
var dateFormat = '<?php echo $this->dateFormat; ?>'
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
<div>
<div class="com_form2content form edit">
	<div class="f2c-article<?php echo htmlspecialchars($this->params->get('pageclass_sfx')); if(in_array(4,$user->get('groups'))){echo " plugger";} ?> " id="f2c_form">

		<form action="<?php echo JRoute::_('index.php?option=com_form2content&view=form&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
			<div id="cms-top-block">
			<div class="top-title">
				<h1>Artikel</h1>
			</div>
			<div class="submitbuttons-top">
				<?php if($this->item->id == 0) { ?>
					<button class="cancelbutton" type="button" onclick="javascript:Joomla.submitbutton('form.cancel')">Annuleren</button>
				<?php } else { ?>
					<button class="cancelbutton" type="button" onclick="javascript:Joomla.submitbutton('form.cancel')">Annuleren</button>
				<?php } ?>
				<button class="applybutton" type="button" onclick="javascript:Joomla.submitbutton('form.apply')">Toepassen</button>
				<button class="savebutton" type="button" onclick="javascript:Joomla.submitbutton('form.save')">Opslaan</button>
			</div>
			
			</div>
			<div id="cms-middle-block-top"></div>

			<div id="cms-middle-block-middle">
				<fieldset class="adminform">

					<?php if($this->contentTypeSettings->get('id_front_end')) : ?>
						<div class="veld id hidden"><?php echo $this->form->getLabel('id'); ?>
							<?php echo $this->form->getInput('id'); ?>
						</div>
					<?php endif; ?>

					<?php if($this->contentTypeSettings->get('title_front_end')) : ?>
						<div class="veld title"><?php echo $this->form->getLabel('title'); ?>
							<?php echo $this->form->getInput('title'); ?>
						</div>
					<?php endif; ?>

					<?php if($this->contentTypeSettings->get('frontend_catsel')) : ?>
						<div class="veld category">
							<?php echo $this->form->getLabel('catid'); ?> 
							<?php echo $this->form->getInput('catid'); ?>
						</div>
					<?php endif; ?>

					<?php if($this->contentTypeSettings->get('date_created_front_end')) : ?>
						<div class="veld date-created">
							<?php echo $this->form->getLabel('created'); ?>
						</td>
						<td valign="top" class="cms-agenda">
							<?php echo $this->form->getInput('created'); ?>
						</div>
					<?php endif; ?>

					<?php if($this->contentTypeSettings->get('frontend_pubsel')) : ?>
						<div class="veld publish-date">
							<label>Publiceer vanaf</label>
						</td>
						<td valign="top" class="cms-agenda">
							<?php echo $this->form->getInput('publish_up'); ?>
						</div>
						<div class="veld unpublish-date">
							<label>Depubliceer op</label>
						</td>
						<td valign="top" class="cms-agenda">
							<?php echo $this->form->getInput('publish_down'); ?>
						</div>
					<?php endif; ?>			
					
					<?php if($this->contentTypeSettings->get('state_front_end')) : ?>
						<div class="veld publish-state">
							<label>Status</label>
							<?php echo $this->form->getInput('state'); ?>
						</div>
					<?php endif; ?>

					<?php if($this->contentTypeSettings->get('language_front_end')) : ?>
						<div class="veld language">
							<?php echo $this->form->getLabel('language'); ?> 
							<?php echo $this->form->getInput('language'); ?>
						</div>
					<?php endif; ?>			
					
					<?php if($this->contentTypeSettings->get('featured_front_end')) : ?>
						<div class="veld publish-frontpage">
							<?php echo $this->form->getLabel('featured'); ?> 
							<?php echo $this->form->getInput('featured'); ?>
						</div>
					<?php endif; ?>

					<?php
					// User defined fields
					$k = 0;
					
					for ($i=0, $n=count($this->fields); $i < $n; $i++) 
					{
						$field 			= $this->fields[$i];
						
						// skip processing of hidden fields
						if(!$field->frontvisible) continue;
										
						$parms 			= array();
						$fieldValues 	= (array_key_exists($field->id, $this->fieldValues)) ? $this->fieldValues[$field->id] : null;
										
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
						<div class="veld textarea">
								<?php echo $this->renderer->renderFieldLabel($field); ?>
								<?php echo $this->renderer->renderField($field, $fieldValues, $parms); ?>			
						</div>
						<?php
						$k = 1 - $k;			
					}
					
					echo $this->renderCaptcha;
					?>		
					<?php
						// User defined fields
						if(count($this->fields))
						{
							$p = 0;
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
								<div class="veld multitextarea">
										<?php echo $this->renderer->renderFieldLabel($field); ?>
										<?php echo $this->renderer->renderField($field, $parms); ?>			
								</div>
								<?php
								
								$p++;
							}
						}
										
						echo $this->renderCaptcha;
						?>						
				</fieldset>
			</div>
	<div id="cms-middle-block-bottom"> &nbsp;</div>	
			
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