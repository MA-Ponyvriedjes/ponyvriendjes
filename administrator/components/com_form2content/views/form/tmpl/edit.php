<?php
// No direct access.
defined('_JEXEC') or die;

require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');

JHtml::_('behavior.mootools');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

JHTML::script('f2c_lists.js','components/com_form2content/js/');
JHTML::script('f2c_frmval.js','components/com_form2content/js/');
JHTML::script('f2c_util.js','components/com_form2content/js/');

JForm::addFieldPath(JPATH_COMPONENT_SITE . DS . 'models' . DS . 'fields');

$document 	=& JFactory::getDocument();
?>

<script type="text/javascript">
<!--
var jTextUp = '<?php echo JText::_('UP', true); ?>';
var jTextDown = '<?php echo JText::_('DOWN', true); ?>';
var jTextAdd = '<?php echo JText::_('ADD', true); ?>';
var jTextDelete = '<?php echo JText::_('DELETE', true); ?>';
var jImagePath = '<?php echo JURI::root(true).'/media/com_form2content/images/'; ?>';
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

	<?php echo $this->jsScripts['fieldval']; ?>
	if(!F2C_CheckRequiredFields(arrValidation)) return false;

	<?php echo $this->jsScripts['editorsave']; ?>
	Joomla.submitform(task, document.getElementById('item-form'));		
}
-->
</script>

<form action="<?php echo JRoute::_('index.php?option=com_form2content&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate" enctype="multipart/form-data">
	<div class="width-60 fltlft">
	
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('COM_FORM2CONTENT_NEW_ARTICLE') : JText::sprintf('COM_FORM2CONTENT_EDIT_ARTICLE', $this->item->id); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('catid'); ?>
				<?php echo $this->form->getInput('catid'); ?></li>

				<li><?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>
				<?php if ($this->canDo->get('core.admin')): ?>
					<li><span class="faux-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>
						<div class="button2-left"><div class="blank">
							<button type="button" onclick="document.location.href='#access-rules';">
								<?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?>
							</button>
						</div></div>
					</li>
				<?php endif; ?>
				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<li><?php echo $this->form->getLabel('featured'); ?>
				<?php echo $this->form->getInput('featured'); ?></li>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
				
				<li><?php echo $this->form->getLabel('intro_template'); ?>
				<?php echo $this->form->getInput('intro_template'); ?></li>

				<li><?php echo $this->form->getLabel('main_template'); ?>
				<?php echo $this->form->getInput('main_template'); ?></li>				
			</ul>
			<div class="clr"></div>
			<?php echo $this->form->getInput('projectid'); ?>
			<table class="admintable">
			<?php
			// User defined fields
			if(count($this->fields))
			{
				foreach($this->fields as $field)
				{								
					switch($field->fieldtypeid)
					{
						case F2C_FIELDTYPE_SINGLELINE:
							$parms = array(100, 100);
							break;
						case F2C_FIELDTYPE_IMAGE:
							$parms = array(75, 100);
							break;
		      			case F2C_FIELDTYPE_MULTILINETEXT:
		      				$parms = array('cols="100" rows="6"');
		      				break;
		      			case F2C_FIELDTYPE_MULTILINEEDITOR:	
		      				$parms = array(500, 350, 50, 20);
		      				break;
		      			default:
							$parms = array();
		      				break;
					}				
					?>
					<tr>
						<td width="100" align="left" class="key" valign="top">
							<?php echo $this->renderer->renderFieldLabel($field); ?>
						</td>
						<td>
							<?php echo $this->renderer->renderField($field, $parms); ?>			
						</td>
					</tr>
					<?php
				}
			}
			?>		
			</table>						
		</fieldset>
	</div>

	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start','content-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

			<?php echo JHtml::_('sliders.panel',JText::_('COM_FORM2CONTENT_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
			<fieldset class="panelform">
				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('created_by'); ?>
					<?php echo $this->form->getInput('created_by'); ?></li>

					<li><?php echo $this->form->getLabel('created_by_alias'); ?>
					<?php echo $this->form->getInput('created_by_alias'); ?></li>
					
					<li><?php echo $this->form->getLabel('created'); ?>
					<?php echo $this->form->getInput('created'); ?></li>

					<li><?php echo $this->form->getLabel('publish_up'); ?>
					<?php echo $this->form->getInput('publish_up'); ?></li>

					<li><?php echo $this->form->getLabel('publish_down'); ?>
					<?php echo $this->form->getInput('publish_down'); ?></li>
					<!--
					<?php //if ($this->item->modified_by) : ?>
						<li><?php //echo $this->form->getLabel('modified_by'); ?>
						<?php //echo $this->form->getInput('modified_by'); ?></li>
					-->
					<?php if ($this->item->modified != $this->nullDate) : ?>
						<li><?php echo $this->form->getLabel('modified'); ?>
						<?php echo $this->form->getInput('modified'); ?></li>
					<?php endif; ?>

					<?php if ($this->jArticle->version) : ?>
						<li><?php echo $this->form->getLabel('version'); ?>
						<?php echo $this->form->getInput('version'); ?></li>
					<?php endif; ?>
					<?php if ($this->jArticle->hits) : ?>
						<li><?php echo $this->form->getLabel('hits'); ?>
						<?php echo $this->form->getInput('hits'); ?></li>
					<?php endif; ?>
				</ul>
			</fieldset>

			<?php $fieldSets = $this->form->getFieldsets('attribs');?>
			<?php foreach ($fieldSets as $name => $fieldSet) :?>
				<?php echo JHtml::_('sliders.panel',JText::_($fieldSet->label), $name.'-options');?>
				<?php if (isset($fieldSet->description) && trim($fieldSet->description)) :?>
					<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
				<?php endif;?>
				<fieldset class="panelform">
					<ul class="adminformlist">
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
						<li><?php echo $field->label; ?><?php echo $field->input; ?></li>
					<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endforeach; ?>

			<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
			<fieldset class="panelform">
				<?php echo $this->loadTemplate('metadata'); ?>
			</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<div class="clr"></div>
	<?php if ($this->canDo->get('core.admin')): ?>
		<div class="width-100 fltlft">
			<?php echo JHtml::_('sliders.start','permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

				<?php echo JHtml::_('sliders.panel',JText::_('COM_FORM2CONTENT_FIELDSET_RULES'), 'access-rules'); ?>

				<fieldset class="panelform">
					<?php echo $this->form->getLabel('rules'); ?>
					<?php echo $this->form->getInput('rules'); ?>
				</fieldset>

			<?php echo JHtml::_('sliders.end'); ?>
		</div>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?php echo DisplayCredits(); ?>