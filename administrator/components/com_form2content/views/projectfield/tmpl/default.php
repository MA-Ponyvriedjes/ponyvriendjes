<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
require_once(JPATH_COMPONENT.DS.'views'.DS.'viewhelper.form2content.php');

JHtml::_('behavior.mootools');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

JForm::addFieldPath(JPATH_COMPONENT . DS . 'models' . DS . 'fields');

JHTML::script('f2c_lists.js','components/com_form2content/js/');

$editor =& JFactory::getEditor();
$imgPath = JURI::root(false).'media/com_form2content/images/';
?>
<script type="text/javascript">
//<!--
function fieldTypeChanged(value)
{
	for(i=1;i<=17;i++)
	{
		var div = document.getElementById('FieldSettings_' + i); 
		
		if(div != null)
		{
			div.style.display = (i == value) ? 'block' : 'none';	
		}
	}

	// InfoText setting	
	var rowFieldCaption = document.getElementById('rowFieldCaption');	
	rowFieldCaption.style.display = (value==11) ? 'none' : '';
	var rowFieldRequired = document.getElementById('rowFieldRequired');
	rowFieldRequired.style.display = (value==11) ? 'none' : '';
	var rowFieldRequiredErrorMessage = document.getElementById('rowFieldRequiredErrorMessage');
	rowFieldRequiredErrorMessage.style.display = (value==11) ? 'none' : '';
}

function prepareRowSelectList(tableId, row)
{
	var cellLeft = row.insertCell(0);
	var el1 = document.createElement('input');
	el1.type = 'text';
	el1.name = row.id + 'key';
	el1.id = row.id + 'key';
	el1.size = 20;	
	el1.maxLength = 20;	  
	cellLeft.appendChild(el1);
	  
	var elHidden = document.createElement('input');
	elHidden.type = 'hidden';
	elHidden.name = tableId + 'RowKey[]';
	elHidden.value = row.id;
	cellLeft.appendChild(elHidden);
	  
	var cellRight = row.insertCell(1);
	var el2 = document.createElement('input');
	el2.type = 'text';
	el2.name = row.id + 'val';
	el2.id = row.id + 'val';
	el2.size = 40;	
	el2.maxLength = 50;	  
	cellRight.appendChild(el2);	
	  
	var cellMove = row.insertCell(2);
	var lnkUp = document.createElement('a');
	lnkUp.href = 'javascript:moveUp(\''+tableId+'\',\'' + row.id + '\');';
	lnkUp.innerHTML = '<img src="<?php echo $imgPath; ?>uparrow.png" alt="<?php echo JText::_('COM_FORM2CONTENT_UP'); ?>" />';
	cellMove.appendChild(lnkUp);	
	var lnkDwn = document.createElement('a');
	lnkDwn.href = 'javascript:moveDown(\''+tableId+'\',\'' + row.id + '\');';
	lnkDwn.innerHTML = '<img src="<?php echo $imgPath; ?>downarrow.png" alt="<?php echo JText::_('COM_FORM2CONTENT_DOWN'); ?>" />';
	cellMove.appendChild(lnkDwn);	
	  
	var cellDelete = row.insertCell(3);
	var lnkDel = document.createElement('a');
	lnkDel.href = 'javascript:removeRow(\'' + row.id + '\');';
	lnkDel.innerHTML = '<img src="<?php echo $imgPath; ?>remove.png" alt="<?php echo JText::_('COM_FORM2CONTENT_DELETE'); ?>" />';
	cellDelete.appendChild(lnkDel);	
	
	var cellAdd = row.insertCell(4);
	var lnkAdd = document.createElement('a');
	lnkAdd.href = 'javascript:addRow(\''+tableId+'\',\'' + row.id + '\',\'prepareRowSelectList\');';
	lnkAdd.innerHTML = '<img src="<?php echo $imgPath; ?>add.png" alt="<?php echo JText::_('COM_FORM2CONTENT_ADD_ROW'); ?>" />';
	cellAdd.appendChild(lnkAdd);		
}

function prepareRowExtensionList(tableId, row)
{
	var cellLeft = row.insertCell(0);
	var el1 = document.createElement('input');
	el1.type = 'text';
	el1.name = row.id + 'key';
	el1.id = row.id + 'key';
	el1.size = 20;	
	el1.maxLength = 5;	  
	cellLeft.appendChild(el1);
	  
	var elHidden = document.createElement('input');
	elHidden.type = 'hidden';
	elHidden.name = tableId + 'RowKey[]';
	elHidden.value = row.id;
	cellLeft.appendChild(elHidden);
	
	var cellDelete = row.insertCell(1);
	var lnkDel = document.createElement('a');
	lnkDel.href = 'javascript:removeRow(\'' + row.id + '\');';
	lnkDel.innerHTML = '<img src="<?php echo $imgPath; ?>remove.png" alt="<?php echo JText::_('COM_FORM2CONTENT_DELETE'); ?>" />';
	cellDelete.appendChild(lnkDel);	
	
	var cellAdd = row.insertCell(2);
	var lnkAdd = document.createElement('a');
	lnkAdd.href = 'javascript:addRow(\''+tableId+'\',\'' + row.id + '\',\'prepareRowExtensionList\');';
	lnkAdd.innerHTML = '<img src="<?php echo $imgPath; ?>add.png" alt="<?php echo JText::_('COM_FORM2CONTENT_ADD_ROW'); ?>" />';
	cellAdd.appendChild(lnkAdd);		
}

Joomla.submitbutton = function(task) 
{
	if (task == 'projectfield.cancel')
	{
		Joomla.submitform(task, document.getElementById('item-form'));
		return true;
	}
	
	if(!document.formvalidator.isValid(document.id('item-form')))
	{
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		return false;
	}

	var fldFieldname = document.getElementById('jform_fieldname');
	var fldTitle = document.getElementById('jform_title');
	var fldFieldTypeId = document.getElementById('jform_fieldtypeid');

	if(fldFieldname.value == '')
	{
		alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_PROJECTFIELD_FIELDNAME_EMPTY')); ?>');
		return false;
	}

	var re = new RegExp('^[A-Za-z0-9_]+$');
	var result = fldFieldname.value.match(re);

	if (result == null)
	{
		alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_PROJECTFIELD_FIELDNAME_INVALID_CHARS')); ?>');
		return false;
	}

	if(fldTitle.value == '' && (parseInt(fldFieldTypeId.value) != 11))
	{
		alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_FIELD_CAPTION_EMPTY', true)); ?>');
		return false;
	}

	switch(parseInt(fldFieldTypeId.value))
	{
		case 5:		
			var count = 0;
			var optionKeys = new Array();
			var tbl = document.getElementById('tblSingleSelectKvp');
			
			for(i=1;i<=tbl.rows.length-1;i++)
			{
				var row = tbl.rows[i];
				var key = document.getElementById(row.id+'key').value;
				var val = document.getElementById(row.id+'val').value;
				
				if(key == '')
				{
					if(val == '')
					{
						continue;
					}
					else
					{
						alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_OPTION_VALUE_EMPTY')); ?>');
						return false;
					}
				}
				else
				{
					var re = new RegExp('^[A-Za-z0-9_]+$');
					var result = key.match(re);

					if (result == null)
					{
						alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_OPTION_VALUE_INVALID_CHARS')); ?>');
						return false;
					}
					
					if(optionKeys.contains(key))
					{
						alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_OPTION_VALUE_DUPLICATE')); ?> ' + key);
						return false;
					}
					
					optionKeys.push(key);
					count++;							
				}
			}
			
			if(count == 0)
			{
				alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_OPTION_VALUE_AT_LEAST_ONE')); ?>');
				return false;
			}					
			break;
			
		case 10:
			var count = 0;
			var optionKeys = new Array();
			var tbl = document.getElementById('tblMultiSelectKvp');
			
			for(i=1;i<=tbl.rows.length-1;i++)
			{
				var row = tbl.rows[i];
				var key = document.getElementById(row.id+'key').value;
				var val = document.getElementById(row.id+'val').value;
				
				if(key == '')
				{
					if(val == '')
					{
						continue;
					}
					else
					{
						alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_OPTION_VALUE_EMPTY')); ?>');
						return false;
					}
				}
				else
				{
					var re = new RegExp('^[A-Za-z0-9_]+$');
					var result = key.match(re);

					if (result == null)
					{
						alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_OPTION_VALUE_INVALID_CHARS')); ?>');
						return false;
					}
					
					if(optionKeys.contains(key))
					{
						alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_OPTION_VALUE_DUPLICATE')); ?> ' + key);
						return false;
					}
					
					optionKeys.push(key);
					count++;							
				}
			}
			
			if(count == 0)
			{
				alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_OPTION_VALUE_AT_LEAST_ONE')); ?>');
				return false;
			}		
			break;

		case 14:
			var whiteList = new Array();
			var blackList = new Array();
			var tbl = document.getElementById('tblFileWhiteList');

			for(i=1;i<=tbl.rows.length-1;i++)
			{
				var row = tbl.rows[i];
				var key = document.getElementById(row.id+'key').value;

				if(key != '' && !(key in whiteList))
				{
					whiteList[key] = key;
				}
			}
			
			tbl = document.getElementById('tblFileBlackList');
			
			for(i=1;i<=tbl.rows.length-1;i++)
			{
				var row = tbl.rows[i];
				var key = document.getElementById(row.id+'key').value;
				
				if(key in whiteList)
				{
					alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_EXTENSION_IN_BOTH_LISTS')); ?>: ' + key);
					return false;
				}
			}							
			break;
				
		case 15:
			var fldDblQuery = document.getElementById('jform_settings_dbl_query');
			
			if(fldDblQuery.value.indexOf('*') != -1)
			{
				alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_QUERY_ASTERISK_NOT_ALLOWED')); ?>');
				return false;			
			}				
			break;
		
		case 16:
			var reWholeNumber = new RegExp('^\\d+$');
			var reLatLon = new RegExp('^-*\\d{1,3}\\.\\d{1,7}$');
			
			if(document.getElementById('jform_settings_gcd_map_width').value != '' && 
			   	!document.getElementById('jform_settings_gcd_map_width').value.match(reWholeNumber))
			{
				alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_MAP_WIDTH_INVALID_VALUE')); ?>');
				return false;
			}
		
			if(document.getElementById('jform_settings_gcd_map_height').value != '' &&
				!document.getElementById('jform_settings_gcd_map_height').value.match(reWholeNumber))
			{
				alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_MAP_HEIGHT_INVALID_VALUE')); ?>');
				return false;
			}

			if(document.getElementById('jform_settings_gcd_map_lat').value != '' &&
				!document.getElementById('jform_settings_gcd_map_lat').value.match(reLatLon))
			{
				alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_MAP_LAT_INVALID_VALUE')); ?>');
				return false;
			}

			if(document.getElementById('jform_settings_gcd_map_lon').value != '' &&
				!document.getElementById('jform_settings_gcd_map_lon').value.match(reLatLon))
			{
				alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_MAP_LON_INVALID_VALUE')); ?>');
				return false;
			}
			break;
		
		case 17:
			var fldDlmQuery = document.getElementById('jform_settings_dlm_query');
			
			if(fldDlmQuery.value.indexOf('*') != -1)
			{
				alert('<?php echo $this->escape(JText::_('COM_FORM2CONTENT_ERROR_QUERY_ASTERISK_NOT_ALLOWED')); ?>');
				return false;			
			}				
			break;
	}

	Joomla.submitform(task, document.getElementById('item-form'));
	return true;		
}
//-->	
</script>
<form action="<?php echo JRoute::_('index.php?option=com_form2content&view=projectfields&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
<div class="width-60 fltlft">
	<fieldset class="adminform">
		<legend><?php echo empty($this->item->id) ? JText::_('COM_FORM2CONTENT_NEW_CONTENTTYPEFIELD') : JText::sprintf('COM_FORM2CONTENT_EDIT_CONTENTTYPEFIELD', $this->item->id); ?></legend>
		<ul class="adminformlist">
			<li><?php echo $this->form->getLabel('id') . $this->form->getInput('id'); ?></li>
			<li><?php echo $this->form->getLabel('fieldname') . $this->form->getInput('fieldname'); ?></li>
			<li id="rowFieldCaption"><?php echo $this->form->getLabel('title') . $this->form->getInput('title'); ?></li>
			<li><?php echo $this->form->getLabel('description') . $this->form->getInput('description'); ?></li>
			<li><?php echo $this->form->getLabel('frontvisible') . $this->form->getInput('frontvisible'); ?></li>
			<li><?php echo $this->form->getLabel('fieldtypeid') . $this->form->getInput('fieldtypeid'); ?></li>
			<li id="rowFieldRequired"><?php echo $this->form->getLabel('requiredfield', 'settings') . $this->form->getInput('requiredfield', 'settings'); ?></li>
			<li id="rowFieldRequiredErrorMessage"><?php echo $this->form->getLabel('error_message_required', 'settings') . $this->form->getInput('error_message_required', 'settings'); ?></li>
		</ul>
	</fieldset>		
</div>
<div class="width-60 fltlft">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_FORM2CONTENT_ADDITIONAL_FIELD_SETTINGS'); ?></legend>
		<div id="FieldSettings_1" style="display:none" class="f2c_field">				
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('slt_size', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('slt_size', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('slt_max_length', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('slt_max_length', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('slt_attributes', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('slt_attributes', 'settings'); ?></td>
			</tr>
			</table>
		</div>
		<div id="FieldSettings_2" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('mlt_num_rows', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('mlt_num_rows', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('mlt_num_cols', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('mlt_num_cols', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('mlt_attributes', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('mlt_attributes', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('mlt_max_num_chars', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('mlt_max_num_chars', 'settings'); ?></td>
			</tr>
			</table>
		</div>			
		<div id="FieldSettings_3" style="display:none" class="f2c_field">			
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('mle_num_rows', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('mle_num_rows', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('mle_num_cols', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('mle_num_cols', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('mle_width', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('mle_width', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('mle_height', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('mle_height', 'settings'); ?></td>
			</tr>
			</table>
		</div>
		<div id="FieldSettings_4" style="display:none" class="f2c_field">
		
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('chk_attributes', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('chk_attributes', 'settings'); ?></td>
			</tr>
			</table>
		</div>				
		<div id="FieldSettings_5" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('ssl_display_mode', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ssl_display_mode', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('ssl_show_empty_choice_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ssl_show_empty_choice_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('ssl_empty_choice_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ssl_empty_choice_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td valign="top"><label id="tblSingleSelectKvp-lbl" for="tblSingleSelectKvp" class=""><?php echo JText::_('COM_FORM2CONTENT_OPTIONS'); ?></label></td>
				<td>
					<table border="1" id="tblSingleSelectKvp" cellspacing="0" cellpadding="0">
					<tr>
						<th style="width:120px;"><?php echo JText::_('COM_FORM2CONTENT_OPTION_VALUE'); ?></th>
						<th style="width:200px;"><?php echo JText::_('COM_FORM2CONTENT_OPTION_DISPLAY_TEXT'); ?></th>
						<th style="width:41px;">&nbsp;</th>	
						<th style="width:26px;">&nbsp;</th>			
						<th style="width:26px;">&nbsp;</th>						
					</tr>							
					<?php							
					$rowcount = 0;
					$options = array();
					
					if(array_key_exists('ssl_options', $this->item->settings))
					{
						$options = $this->item->settings['ssl_options'];
					}
					
					if(count($options))
					{															
						foreach($options as $key => $value)
						{
							$rowId = 'tblSingleSelectKvp_' . $rowcount;								
							$rowcount++;
							echo '<tr id="'.$rowId.'">';
							echo '<td><input type="text" id="'.$rowId.'key" name="'.$rowId.'key" size="20" value="' . $key . '" maxlength="20" /><input type="hidden" name="tblSingleSelectKvpRowKey[]" value="'.$rowId.'"/></td>';
							echo '<td><input type="text" id="'.$rowId.'val" name="'.$rowId.'val" size="40" value="' . htmlspecialchars($value) . '" maxlength="50" /></td>';
							echo '<td><a href="javascript:moveUp(\'tblSingleSelectKvp\',\''.$rowId.'\');"><img src="'.$imgPath.'uparrow.png" alt="' . JText::_('COM_FORM2CONTENT_UP') . '" /></a><a href="javascript:moveDown(\'tblSingleSelectKvp\',\''.$rowId.'\');"><img src="'.$imgPath.'downarrow.png" alt="' . JText::_('COM_FORM2CONTENT_DOWN') . '" /></a></td>';
							echo '<td><a href="javascript:removeRow(\''.$rowId.'\');"><img src="'.$imgPath.'remove.png" alt="' . JText::_('COM_FORM2CONTENT_DELETE') . '" /></a></td>';
							echo '<td><a href="javascript:addRow(\'tblSingleSelectKvp\',\''.$rowId.'\',\'prepareRowSelectList\');"><img src="'.$imgPath.'add.png" alt="' . JText::_('COM_FORM2CONTENT_ADD_ROW') . '" /></a></td>';
							echo '</tr>';							
						}
					}					
					?>
					</table>
					<br/>
					<label id="tblSingleSelectKvp-lbl2" for="tblSingleSelectKvp" class=""></label>
					<input type="button" value="<?php echo JText::_('COM_FORM2CONTENT_ADD_SELECT_OPTION'); ?>" onclick="addRow('tblSingleSelectKvp','','prepareRowSelectList');" />
					<input type="hidden" name="tblSingleSelectKvpMaxKey" id="tblSingleSelectKvpMaxKey" value="<?php echo $rowcount; ?>"/>
				</td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('ssl_attributes', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ssl_attributes', 'settings'); ?></td>
			</tr>					
			</table>
		</div>
		<div id="FieldSettings_6" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo JText::_('COM_FORM2CONTENT_SHRINK_IMAGE_WHEN'); ?></td>
				<td>
					<?php echo $this->form->getInput('img_max_width', 'settings'); ?> X <?php echo $this->form->getInput('img_max_height', 'settings'); ?><?php echo JText::_('COM_FORM2CONTENT_PIXELS'); ?> (<?php echo JText::_('COM_FORM2CONTENT_WIDTH_X_HEIGHT'); ?>)					
				</td>				
			</tr>
			<tr>
				<td><?php echo JText::_('COM_FORM2CONTENT_MAX_THUMBNAIL_SIZE'); ?></td>
				<td>
					<?php echo $this->form->getInput('img_thumb_width', 'settings'); ?> X
					<?php echo $this->form->getInput('img_thumb_height', 'settings'); ?> <?php echo JText::_('COM_FORM2CONTENT_PIXELS'); ?> (<?php echo JText::_('COM_FORM2CONTENT_WIDTH_X_HEIGHT'); ?>)
				</td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('img_output_mode', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('img_output_mode', 'settings');	?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('img_attributes_image', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('img_attributes_image', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('img_attributes_delete', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('img_attributes_delete', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('img_attributes_alt_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('img_attributes_alt_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('img_attributes_title', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('img_attributes_title', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('img_show_alt_tag', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('img_show_alt_tag', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('img_show_title_tag', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('img_show_title_tag', 'settings'); ?></td>
			</tr>										
			</table>
		</div>				
		<div id="FieldSettings_7" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('ifr_attributes_iframe', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ifr_attributes_iframe', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('ifr_attributes_width', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ifr_attributes_width', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('ifr_attributes_height', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ifr_attributes_height', 'settings'); ?></td>
			</tr>
			</table>
		</div>
		<div id="FieldSettings_8" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('eml_attributes_email', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('eml_attributes_email', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('eml_attributes_display_as', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('eml_attributes_display_as', 'settings'); ?></td>
			</tr>			
			<tr>
				<td><?php echo $this->form->getLabel('eml_show_display_as', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('eml_show_display_as', 'settings'); ?></td>
			</tr>			
			</table>
		</div>
		<div id="FieldSettings_9" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('lnk_output_mode', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_output_mode', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('lnk_attributes_url', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_attributes_url', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('lnk_attributes_display_as', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_attributes_display_as', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('lnk_attributes_title', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_attributes_title', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('lnk_attributes_target', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_attributes_target', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('lnk_show_display_as', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_show_display_as', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('lnk_show_title', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_show_title', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('lnk_show_target', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_show_target', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('lnk_add_http_prefix', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('lnk_add_http_prefix', 'settings'); ?></td>
			</tr>					
			</table>
		</div>
		<div id="FieldSettings_10" style="display:none" class="f2c_field">
			<table border="1" id="tblMultiSelectKvp" cellspacing="0" cellpadding="0">
			<tr>
				<th style="width:120px;"><?php echo JText::_('COM_FORM2CONTENT_OPTION_VALUE'); ?></th>
				<th style="width:200px;"><?php echo JText::_('COM_FORM2CONTENT_OPTION_DISPLAY_TEXT'); ?></th>
				<th style="width:41px;">&nbsp;</th>	
				<th style="width:26px;">&nbsp;</th>			
				<th style="width:26px;">&nbsp;</th>						
			</tr>
			<?php							
			$rowcount = 0;
			$options = array();
			
			if(array_key_exists('msl_options', $this->item->settings))
			{
				$options = $this->item->settings['msl_options'];
			}
			
			if(count($options))
			{															
				foreach($options as $key => $value)
				{
					$rowId = 'tblMultiSelectKvp_' . $rowcount;								
					$rowcount++;
					echo '<tr id="'.$rowId.'">';
					echo '<td><input type="text" id="'.$rowId.'key" name="'.$rowId.'key" size="20" value="' . $key . '" maxlength="20" /><input type="hidden" name="tblMultiSelectKvpRowKey[]" value="'.$rowId.'"/></td>';
					echo '<td><input type="text" id="'.$rowId.'val" name="'.$rowId.'val" size="40" value="' . htmlspecialchars($value) . '" maxlength="50" /></td>';
					echo '<td><a href="javascript:moveUp(\'tblMultiSelectKvp\',\''.$rowId.'\');"><img src="'.$imgPath.'uparrow.png" alt="' . JText::_('COM_FORM2CONTENT_UP') . '" /></a><a href="javascript:moveDown(\'tblMultiSelectKvp\',\''.$rowId.'\');"><img src="'.$imgPath.'downarrow.png" alt="' . JText::_('COM_FORM2CONTENT_DOWN') . '" /></a></td>';
					echo '<td><a href="javascript:removeRow(\''.$rowId.'\');"><img src="'.$imgPath.'remove.png" alt="' . JText::_('COM_FORM2CONTENT_DELETE') . '" /></a></td>';
					echo '<td><a href="javascript:addRow(\'tblMultiSelectKvp\',\''.$rowId.'\',\'prepareRowSelectList\');"><img src="'.$imgPath.'add.png" alt="' . JText::_('COM_FORM2CONTENT_ADD_ROW') . '" /></a></td>';
					echo '</tr>';					
				}
			}					
			?>									
			</table>
			<br/>
			<input type="button" value="<?php echo JText::_('COM_FORM2CONTENT_ADD_SELECT_OPTION'); ?>" onclick="addRow('tblMultiSelectKvp','','prepareRowSelectList');" />
			<input type="hidden" name="tblMultiSelectKvpMaxKey" id="tblMultiSelectKvpMaxKey" value="<?php echo $rowcount; ?>"/>
			<br/><br/>
			<table>			
			<tr>
				<td><?php echo $this->form->getLabel('msl_attributes', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('msl_attributes', 'settings'); ?></td>
			</tr>										
			</table>
			<p><strong><?php echo JText::_('COM_FORM2CONTENT_MULTISELECTLIST_CUSTOM_RENDERING'); ?></strong></p>
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('msl_pre_list_tag', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('msl_pre_list_tag', 'settings'); ?></td>
				<td><?php echo $this->form->getLabel('msl_post_list_tag', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('msl_post_list_tag', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('msl_pre_element_tag', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('msl_pre_element_tag', 'settings'); ?></td>
				<td><?php echo $this->form->getLabel('msl_post_element_tag', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('msl_post_element_tag', 'settings'); ?></td>
			</tr>
			</table>
		</div>
		<div id="FieldSettings_11" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td valign="top"><?php echo $this->form->getLabel('inf_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('inf_text', 'settings'); ?></td>
			</tr>
			</table>
		</div>				
		<div id="FieldSettings_12" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('dat_attributes', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dat_attributes', 'settings'); ?></td>
			</tr>
			</table>
		</div>				
		<div id="FieldSettings_13" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('dsp_output_mode', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dsp_output_mode', 'settings'); ?></td>
			</tr>			
			<tr>
				<td><?php echo $this->form->getLabel('dsp_attributes_table', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dsp_attributes_table', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dsp_attributes_tr', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dsp_attributes_tr', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dsp_attributes_th', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dsp_attributes_th', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dsp_attributes_td', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dsp_attributes_td', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dsp_attributes_item_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dsp_attributes_item_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dsp_attributes_add_button', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dsp_attributes_add_button', 'settings'); ?></td>
			</tr>			
			</table>
		</div>
		<div id="FieldSettings_14" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('ful_output_mode', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ful_output_mode', 'settings'); ?></td>
			</tr>					
			<tr>
				<td valign="top">
				<?php
				$text 	= JText::_('COM_FORM2CONTENT_EXTENSIONS_WHITE_LIST');
				$desc 	= JText::_('COM_FORM2CONTENT_EXTENSIONS_WHITE_LIST_DESC');				 	
				$label 	= '';
				$label .= '<label id="tblFileWhiteList-lbl" for="tblFileWhiteList" class="hasTip"';
				$label .= ' title="'.htmlspecialchars(trim($text, ':').'::' . JText::_($desc), ENT_COMPAT, 'UTF-8').'"';
				$label .= '>'.$text.'</label>';
				
				echo $label;
				?>
				</td>
				<td>
					<table border="1" id="tblFileWhiteList" cellspacing="0" cellpadding="0">
					<tr>
						<th style="width:120px;"><?php echo JText::_('COM_FORM2CONTENT_EXTENSION'); ?></th>
						<th style="width:26px;">&nbsp;</th>			
						<th style="width:26px;">&nbsp;</th>						
					</tr>
					<?php							
					$rowcount = 0;
					$whiteList = array();
					
					if(array_key_exists('ful_whitelist', $this->item->settings))
					{
						$whiteList = $this->item->settings['ful_whitelist'];
					}
					
					if(count($whiteList))
					{															
						foreach($whiteList as $key => $value)
						{
							$rowId = 'tblFileWhiteList_' . $rowcount;								
							$rowcount++;
							echo '<tr id="'.$rowId.'">';
							echo '<td>';
							//echo '<input type="hidden" name="'.$rowId.'key" value="'.$rowId.'"/>';
							echo '<input type="hidden" name="tblFileWhiteListRowKey[]" value="'.$rowId.'"/>';
					  		echo '<input type="text" id="'.$rowId.'key" name="'.$rowId.'key" size="20" value="' . htmlspecialchars($value) . '" maxlength="5" />';
							echo '</td>';									
							echo '<td><a href="javascript:removeRow(\''.$rowId.'\');"><img src="'.$imgPath.'remove.png" alt="' . JText::_('COM_FORM2CONTENT_DELETE') . '" /></a></td>';
							echo '<td><a href="javascript:addRow(\'tblFileWhiteList\',\''.$rowId.'\',\'prepareRowExtensionList\');"><img src="'.$imgPath.'add.png" alt="' . JText::_('COM_FORM2CONTENT_ADD_ROW') . '" /></a></td>';
							echo '</tr>';									
						}
					}					
					?>									
					</table>							
					 <br/>
					<input type="button" value="<?php echo JText::_('COM_FORM2CONTENT_ADD_EXTENSION'); ?>" onclick="addRow('tblFileWhiteList','','prepareRowExtensionList');" />
					<input type="hidden" name="tblFileWhiteListMaxKey" id="tblFileWhiteListMaxKey" value="<?php echo $rowcount; ?>"/>
				</td>
			</tr>
			<tr>
				<td valign="top">
				<?php
				$text 	= JText::_('COM_FORM2CONTENT_EXTENSIONS_BLACK_LIST');
				$desc 	= JText::_('COM_FORM2CONTENT_EXTENSIONS_BLACK_LIST_DESC');				 	
				$label 	= '';
				$label .= '<label id="tblFileBlackList-lbl" for="tblFileBlackList" class="hasTip"';
				$label .= ' title="'.htmlspecialchars(trim($text, ':').'::' . JText::_($desc), ENT_COMPAT, 'UTF-8').'"';
				$label .= '>'.$text.'</label>';
				
				echo $label;
				?>
				<td>
					<table border="1" id="tblFileBlackList" cellspacing="0" cellpadding="0">
					<tr>
						<th style="width:120px;"><?php echo JText::_('COM_FORM2CONTENT_EXTENSION'); ?></th>
						<th style="width:26px;">&nbsp;</th>			
						<th style="width:26px;">&nbsp;</th>						
					</tr>
					<?php							
					$rowcount = 0;
					$blackList = array();
					
					if(array_key_exists('ful_blacklist', $this->item->settings))
					{
						$blackList = $this->item->settings['ful_blacklist'];
					}
					
					if(count($blackList))
					{															
						foreach($blackList as $key=>$value)
						{
							$rowId = 'tblFileBlackList_' . $rowcount;								
							$rowcount++;
							echo '<tr id="'.$rowId.'">';
							echo '<td>';
							//echo '<input type="hidden" name="'.$rowId.'key" value="'.$rowId.'"/>';
							echo '<input type="hidden" name="tblFileBlackListRowKey[]" value="'.$rowId.'"/>';
					  		echo '<input type="text" id="'.$rowId.'key" name="'.$rowId.'key" size="20" value="' . htmlspecialchars($value) . '" maxlength="5" />';
							echo '</td>';									
							echo '<td><a href="javascript:removeRow(\''.$rowId.'\');"><img src="'.$imgPath.'remove.png" alt="' . JText::_('COM_FORM2CONTENT_DELETE') . '" /></a></td>';
							echo '<td><a href="javascript:addRow(\'tblFileBlackList\',\''.$rowId.'\',\'prepareRowExtensionList\');"><img src="'.$imgPath.'add.png" alt="' . JText::_('COM_FORM2CONTENT_ADD_ROW') . '" /></a></td>';
							echo '</tr>';									
						}
					}					
					?>									
					</table>
					 <br/>
					<input type="button" value="<?php echo JText::_('COM_FORM2CONTENT_ADD_EXTENSION'); ?>" onclick="addRow('tblFileBlackList','','prepareRowExtensionList');" />
					<input type="hidden" name="tblFileBlackListMaxKey" id="tblFileBlackListMaxKey" value="<?php echo $rowcount; ?>"/>
				</td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('ful_attributes_upload', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ful_attributes_upload', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('ful_attributes_delete', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ful_attributes_delete', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('ful_max_file_size', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('ful_max_file_size', 'settings'); ?></td>
			</tr>								
			</table>
		</div>
		<div id="FieldSettings_15" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('dbl_display_mode', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dbl_display_mode', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dbl_show_empty_choice_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dbl_show_empty_choice_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dbl_empty_choice_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dbl_empty_choice_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td valign="top"><?php echo $this->form->getLabel('dbl_query', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dbl_query', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dbl_attributes', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dbl_attributes', 'settings'); ?></td>
			</tr>					
			</table>
		</div>			
		<div id="FieldSettings_16" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('gcd_show_map', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('gcd_show_map', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('gcd_map_width', 'settings'); ?></td>
				<td>
					<?php echo $this->form->getInput('gcd_map_width', 'settings'); ?> X 
					<?php echo $this->form->getInput('gcd_map_height', 'settings'); ?> 
					<?php echo JText::_('COM_FORM2CONTENT_PIXELS'); ?> (<?php echo JText::_('COM_FORM2CONTENT_WIDTH_X_HEIGHT'); ?>)
				</td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('gcd_map_lat', 'settings'); ?></td>
				<td>
					<?php echo $this->form->getInput('gcd_map_lat', 'settings'); ?>&nbsp;,&nbsp;
					<?php echo $this->form->getInput('gcd_map_lon', 'settings'); ?> 
				</td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('gcd_map_zoom', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('gcd_map_zoom', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('gcd_map_type', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('gcd_map_type', 'settings'); ?></td>
			</tr>					
			<tr>
				<td><?php echo $this->form->getLabel('gcd_attributes_address', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('gcd_attributes_address', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('gcd_attributes_lookup_lat_lon', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('gcd_attributes_lookup_lat_lon', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('gcd_attributes_clear_results', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('gcd_attributes_clear_results', 'settings'); ?></td>
			</tr>					
			</table>
		</div>								
		<div id="FieldSettings_17" style="display:none" class="f2c_field">
			<table>
			<tr>
				<td><?php echo $this->form->getLabel('dlm_output_mode', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_output_mode', 'settings'); ?></td>
			</tr>			
			<tr>
				<td><?php echo $this->form->getLabel('dlm_attributes_table', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_attributes_table', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dlm_attributes_tr', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_attributes_tr', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dlm_attributes_th', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_attributes_th', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dlm_attributes_td', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_attributes_td', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dlm_attributes_item_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_attributes_item_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dlm_attributes_add_button', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_attributes_add_button', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dlm_attributes_select', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_attributes_select', 'settings'); ?></td>
			</tr>															
			<tr>
				<td><?php echo $this->form->getLabel('dlm_show_empty_choice_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_show_empty_choice_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->form->getLabel('dlm_empty_choice_text', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_empty_choice_text', 'settings'); ?></td>
			</tr>
			<tr>
				<td valign="top"><?php echo $this->form->getLabel('dlm_query', 'settings'); ?></td>
				<td><?php echo $this->form->getInput('dlm_query', 'settings'); ?></td>
			</tr>
			</table>
		</div>										
	</fieldset>		
</div>
<div class="clr"></div>
<?php echo DisplayCredits(); ?>
<div>
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('projectid'); ?>
	<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
	<?php echo JHtml::_('form.token'); ?>
</div>	
<script type="text/javascript">
fieldTypeChanged(document.adminForm.jform_fieldtypeid.value);
</script>
</form>