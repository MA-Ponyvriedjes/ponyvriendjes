<?php
defined('_JEXEC') or die('Restricted acccess');

// Hack to ensure presence of select list class
require_once JPATH_SITE.DS.'libraries/joomla/html/html/select.php';

class F2C_Renderer
{
	var $config;
	var $formId;
	var $translatedFields;
	var $contentTypeSettings;
	
	function F2C_Renderer($formId, $translatedFields, $contentTypeSettings)
	{
		$this->config 				=& F2cFactory::getConfig();
		$this->formId 				= $formId;
		$this->translatedFields		= $translatedFields;
		$this->contentTypeSettings	= $contentTypeSettings;
	}
	
	function renderField($field, $parms)
	{
		$F2C_FIELD_FUNCTION_MAPPING = array(F2C_FIELDTYPE_SINGLELINE => 'SingleLineText',
									F2C_FIELDTYPE_MULTILINETEXT => 'MultiLineText',
									F2C_FIELDTYPE_MULTILINEEDITOR => 'MultiLineEditor',
									F2C_FIELDTYPE_CHECKBOX => 'CheckBox',
									F2C_FIELDTYPE_SINGLESELECTLIST => 'SingleSelectList', 
									F2C_FIELDTYPE_IMAGE => 'Image',
									F2C_FIELDTYPE_IFRAME => 'IFrame', 
									F2C_FIELDTYPE_EMAIL => 'Email', 
									F2C_FIELDTYPE_HYPERLINK => 'Hyperlink', 
									F2C_FIELDTYPE_MULTISELECTLIST => 'MultiSelectList', 
									F2C_FIELDTYPE_INFOTEXT => 'InfoText', 
									F2C_FIELDTYPE_DATEPICKER => 'DatePicker', 
									F2C_FIELDTYPE_DISPLAYLIST => 'DisplayList', 
									F2C_FIELDTYPE_FILE => 'FileUpload', 
									F2C_FIELDTYPE_DATABASE_LOOKUP => 'DatabaseLookup', 
									F2C_FIELDTYPE_GEOCODER => 'GeoCoder',
									F2C_FIELDTYPE_DB_LOOKUP_MULTI => 'DatabaseLookupMulti',
									F2C_FIELDTYPE_IMAGE_GALLERY => 'ImageGallery');

		$fieldName 			= 't' . $field->id;
		$fieldDescription	= $this->_getFieldDescription($field);
		$functionName 		= '_render'.$F2C_FIELD_FUNCTION_MAPPING[$field->fieldtypeid];
				
		$fieldSettings = new JRegistry;
		$fieldSettings->loadString($field->settings);
		$field->settings = $fieldSettings;
		
		return $this->$functionName($field, $fieldName, $fieldDescription, $parms);
	}
	
	function renderHiddenField($name, $value)
	{
		return '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.F2C_Renderer::stringHTMLSafe($value).'">';
	}
		
	function _renderSingleLineText($field, $fieldName, $fieldDescription, $parms)
	{
		$html			= '';
		$value 			= $field->values['VALUE'];
		$size			= $field->settings->get('slt_size', $parms[0]);	
		$maxLength		= $field->settings->get('slt_max_length', $parms[1]);	
		$attributes		= $field->settings->get('slt_attributes');	

		$html .= '<div class="f2c_field">';			
		$html .= $this->_renderTextBox($fieldName, $value, $size, $maxLength, $attributes);
		$html .= $this->_renderRequiredText($field);
		$html .= $fieldDescription;
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';		

		return $html;
	}
	
	function _renderMultiLineText($field, $fieldName, $fieldDescription, $parms)
	{
		$html 					= '';
		$fieldHtml 				= '';
		$attribs				= '';
		$maxNumChars 			= (int)$field->settings->get('mlt_max_num_chars');		
		$value 					= $field->values['VALUE'];
		
		if((int)$field->settings->get('mlt_num_rows')) $attribs .= ' rows="'.(int)$field->settings->get('mlt_num_rows').'"';
		if((int)$field->settings->get('mlt_num_cols')) $attribs .= ' cols="'.(int)$field->settings->get('mlt_num_cols').'"';		
		if($field->settings->get('mlt_attributes')) $attribs .= ' '.$field->settings->get('mlt_attributes');

		if(!$attribs)
		{
			$attribs = $parms[0];
			$attribs .= ' class="text_area"';			
		}
		
		$fieldHtml .= ' '.$attribs;
		
		if($maxNumChars)
		{
			if(function_exists('mb_substr_count') && function_exists('mb_substr') && function_exists('mb_strlen'))
			{
				$numNewLines = mb_substr_count($value, "\r\n", 'UTF-8');
				$charsRemaining = $maxNumChars + $numNewLines - mb_strlen($value, 'UTF-8');			
				$fieldValue = mb_substr($value, 0, $maxNumChars + $numNewLines, 'UTF-8');
			}
			else
			{
				$numNewLines = substr_count($value, "\r\n");
				$charsRemaining = $maxNumChars + $numNewLines - strlen($value);			
				$fieldValue = substr($value, 0, $maxNumChars + $numNewLines);
			}
			
			if($charsRemaining < 0)
			{
				$charsRemaining = 0;
			}
			
			$fieldHtml .= ' onKeyDown="F2C_limitTextArea(this.form.'.$fieldName.',this.form.'.$fieldName .'remLen,'.$maxNumChars.');" onKeyUp="F2C_limitTextArea(this.form.' . $fieldName . ',this.form.'.$fieldName .'remLen,'.$maxNumChars.');"';
		}

		$html .= '<div class="f2c_field">';	
		$html .= '<textarea name="'.$fieldName.'" id="'.$fieldName.'"'.$fieldHtml.'>'.$value.'</textarea>';
		
		if($maxNumChars)
		{
			$html .= '<div style="clear:both;"><input readonly type="text" name="'.$fieldName .'remLen" size="6" maxlength="6" value="'.$charsRemaining.'"> '.Jtext::_('COM_FORM2CONTENT_CHARACTERS_LEFT').'</div>';		
		}
		
		$html .= $this->_renderRequiredText($field);														
		$html .= $fieldDescription;
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
		
		return $html;
	}
	
	function _renderMultiLineEditor($field, $fieldName, $fieldDescription, $parms)
	{
		$editor	=& JFactory::getEditor();
		$value 	= $field->values['VALUE'];
		$html	= '';
		$width	= $parms[0];
		$height = $parms[1];
		$col	= $parms[2];
		$row	= $parms[3];
		
		if(	$field->settings->get('mle_num_rows') || 
			$field->settings->get('mle_num_cols') || 
			$field->settings->get('mle_height') || 
			$field->settings->get('mle_width'))
		{
			$width	= $field->settings->get('mle_width');
			$height = $field->settings->get('mle_height');
			$col	= $field->settings->get('mle_num_cols');
			$row	= $field->settings->get('mle_num_rows');
		}

		$html .= '<div class="f2c_field">';		
		$html .= $editor->display($fieldName, $value, $width, $height, $col, $row);
		$html .= $this->_renderRequiredText($field);																
		$html .= $fieldDescription;
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
				
		return $html;
	}
	
	function _renderCheckBox($field, $fieldName, $fieldDescription, $parms)
	{
		$html	= '';
		$value	= $field->values['VALUE'];
		$html 	.= '<div class="f2c_field">';				
		$html 	.= '<input type="checkbox" name="'.$fieldName.'" id="'.$fieldName.'" '.$field->settings->get('chk_attributes').' value="true"'.(($value) ? ' checked' : '') . '>';		
		$html 	.= $this->_renderRequiredText($field);
		$html 	.= $fieldDescription;
		$html 	.= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html 	.= '</div>';
			
		return $html;
	}
	
	function _renderSingleSelectList($field, $fieldName, $fieldDescription, $parms)
	{ 
		$html			= '';
		$fieldValue		= $field->values['VALUE'];		
		$listOptions 	= null;

		$html .= '<div class="f2c_field">';				

		if($field->settings->get('ssl_show_empty_choice_text'))
		{ 
			$listOptions[] = JHTMLSelect::option('', $field->settings->get('ssl_empty_choice_text'));
		}
			     				
		if(count((array)$field->settings->get('ssl_options')))
		{
			foreach((array)$field->settings->get('ssl_options') as $key=>$value)
			{ 
				$listOptions[] = JHTMLSelect::option($key, $value);  	
			}			
		}
		
		if((int)$field->settings->get('ssl_display_mode') == 0)
		{
			$html .= JHTMLSelect::genericlist($listOptions, $fieldName, $field->settings->get('ssl_attributes'), 'value', 'text', $fieldValue);
		}
		else
		{  
			//$html .= JHTMLSelect::radioList($listOptions, $fieldName, $field->settings->get('ssl_attributes'), 'value', 'text', $fieldValue);
			$html .= '<fieldset class="radio">' . JHTML::_('select.radiolist', $listOptions, $fieldName, 'class="radiobutton"', 'value', 'text', $fieldValue) . '</fieldset>';
		}
		
		$html .= $this->_renderRequiredText($field);		
		$html .= $fieldDescription;
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
		
		return $html;
	}

	function _renderImage($field, $fieldName, $fieldDescription, $parms)
	{
		$html				= '';
		$imageHelper		= new F2C_Image();
		$uploadAttribs 		= $field->settings->get('img_attributes_image', 'class="inputbox"');
		$deleteAttribs 		= $field->settings->get('img_attributes_delete', 'class="inputbox"');		
		$widthAltText		= $field->settings->get('img_attributes_alt_text') ? '' : $parms[0];
		$maxLengthAltText	= $field->settings->get('img_attributes_alt_text') ? '' : $parms[1];
		$widthTitle			= $field->settings->get('img_attributes_title') ? '' : $parms[0];
		$maxLengthTitle		= $field->settings->get('img_attributes_title') ? '' : $parms[1];

		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr><td>&nbsp;</td><td>';
		$html .= '<input type="file" id="'.$fieldName.'_fileupload" name="'.$fieldName.'_fileupload" '.$uploadAttribs.'>&nbsp;';
		$html .= '<input type="button" onclick="clearUpload(\''.$fieldName.'_fileupload\');return false;" value="'.Jtext::_('COM_FORM2CONTENT_CLEAR_FIELD').'" />&nbsp;';
		
		// No need for a delete check box when the field is required
		if(!$field->settings->get('requiredfield'))
		{		
			$html .= '<div style="clear:both;"><input type="checkbox" id="'.$fieldName.'_del" name="'.$fieldName.'_del" '.$deleteAttribs.'>&nbsp;'.Jtext::_('COM_FORM2CONTENT_DELETE_IMAGE').'</div>';
		}
		
		$html .= $this->_renderRequiredText($field);										
		$html .= F2C_Renderer::renderHiddenField($fieldName . '_filename', $field->values['FILENAME']);
		$html .= $fieldDescription;
					
		if(!$field->settings->get('img_show_alt_tag'))
		{
			$html .= F2C_Renderer::renderHiddenField($fieldName.'_alt', '');
		}

		if(!$field->settings->get('img_show_title_tag'))
		{
			$html .= F2C_Renderer::renderHiddenField($fieldName.'_title', '');
		}
		
		$html .= '</td></tr>';
		
		if($field->settings->get('img_show_alt_tag'))
		{
			$html .= '<tr><td>'.Jtext::_('COM_FORM2CONTENT_ALT_TEXT').':</td>';
			$html .= '<td>'.$this->_renderTextBox($fieldName.'_alt', $field->values['ALT'], $widthAltText, $maxLengthAltText, $field->settings->get('img_attributes_alt_text')).'</td></tr>';
		}

		if($field->settings->get('img_show_title_tag'))
		{
			$html .= '<tr><td>'.Jtext::_('COM_FORM2CONTENT_TITLE').':</td>';
			$html .= '<td>'.$this->_renderTextBox($fieldName.'_title', $field->values['TITLE'], $widthTitle, $maxLengthTitle, $field->settings->get('img_attributes_title')).'</td></tr>';
		}

		$html .= '<tr><td valign="top">'.Jtext::_('COM_FORM2CONTENT_PREVIEW').':</td><td>';

		if($field->values['FILENAME'])
		{
			$thumbSrc = Path::Combine(F2C_Image::GetThumbnailsUrl($field->projectid, $this->formId), $imageHelper->CreateThumbnailImageName($field->values['FILENAME'], $field->id));
			$html .= '<img id="'.$fieldName.'_preview" src="' . $thumbSrc . '" style="border: 1px solid #000000;">';
		}

		$html .= '</td></tr></table>';
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
				
		return $html;		
	}
	
	function _renderMultiSelectList($field, $fieldName, $fieldDescription, $parms)
	{
		$html			= '';
		$valueList 		= array();
		
		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr><td>';

		if(count($field->values['VALUE']))
		{
			foreach($field->values['VALUE'] as $valueListItem)
			{
				$valueList[$valueListItem] = $valueListItem;
			}
		}

		foreach((array)$field->settings->get('msl_options') as $optionKey => $optionValue)
		{
			$html .= '<div class="checkbox_wrapper"><input type=checkbox name="' . $fieldName . '[]" value="' . F2C_Renderer::stringHTMLSafe($optionKey) . '" '.$field->settings->get('msl_attributes');
			
			if(array_key_exists($optionKey, $valueList))
			{
				$html .= ' checked';
			}
			
			$html .= '><div class="checkbox_label">' . F2C_Renderer::stringHTMLSafe($optionValue) . '</div></div><div style="clear:both;"></div>';
		}

		$html .= '</td><td valign="top">';
		$html .= $this->_renderRequiredText($field);												
		$html .= $fieldDescription.'</td></tr></table>';
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->id);
		$html .= '</div>';
						
		return $html;
	}
	
	function _renderIFrame($field, $fieldName, $fieldDescription, $parms)
	{
		$html = '';
		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr>';
		$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_URL').':</td>';
		$html .= '<td>'.$this->_renderTextBox($fieldName, $field->values['URL'], 65, 75, $field->settings->get('ifr_attributes_iframe'));								
		$html .= $this->_renderRequiredText($field);								
		$html .= $fieldDescription.'</td>';
		$html .= '</tr><tr>';
		$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_WIDTH').':</td>';
		$html .= '<td>'.$this->_renderTextBox($fieldName.'_width', $field->values['WIDTH'], 5, 4, $field->settings->get('ifr_attributes_width')).'&nbsp;';		      							
		$html .= Jtext::_('COM_FORM2CONTENT_HEIGHT').':&nbsp;';
		$html .= $this->_renderTextBox($fieldName.'_height', $field->values['HEIGHT'], 5, 4, $field->settings->get('ifr_attributes_height')).'</td>';		      							
		$html .= '</tr></table>';
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
		
		return $html;	
	}
			
	function _renderEmail($field, $fieldName, $fieldDescription, $parms)
	{
		$html 		= '';
		$email 		= $field->values['EMAIL'];
		$displayAs	= $field->values['DISPLAY_AS'];
		
		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr>';
		$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_EMAIL').':</td>';
		$html .= '<td>'.$this->_renderTextBox($fieldName, $email, 40, 100, $field->settings->get('eml_attributes_email'));
		$html .= $this->_renderRequiredText($field);
		$html .= $fieldDescription.'</td>';
		$html .= '</tr>';
		
		if($field->settings->get('eml_show_display_as'))
		{
			$html .= '<tr>';
			$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_DISPLAY_AS').':</td>';
			$html .= '<td>'.$this->_renderTextBox($fieldName.'_display', $displayAs, 40, 100, $field->settings->get('eml_attributes_display_as')).'</td>';
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
		if(!$field->settings->get('eml_show_display_as'))
		{
			$html .= $this->renderHiddenField($fieldName.'_display', $displayAs);
		}
		
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
		
		return $html;
	}
	
	function _renderHyperlink($field, $fieldName, $fieldDescription, $parms)
	{
		$html 			= '';
		$listTarget[] 	= JHTMLSelect::option('_top', 'Parent window');
		$listTarget[] 	= JHTMLSelect::option('_blank','New window');	

		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr><td>'.Jtext::_('COM_FORM2CONTENT_URL').':</td><td>';
		$html .= $this->_renderTextBox($fieldName, $field->values['URL'], 40, 300, $field->settings->get('lnk_attributes_url')); 
		$html .= $this->_renderRequiredText($field);								
		$html .= $fieldDescription;
		$html .= '</td></tr>';
	
		if($field->settings->get('lnk_show_display_as'))
		{
			$html .= '<tr>';
			$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_DISPLAY_AS').':</td>';
			$html .= '<td>'.$this->_renderTextBox($fieldName.'_display', $field->values['DISPLAY_AS'], 40, 100, $field->settings->get('lnk_attributes_display_as')).'</td>';
			$html .= '</tr>';
		}

		if($field->settings->get('lnk_show_title'))
		{
			$html .= '<tr>';
			$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_TITLE').':</td>';
			$html .= '<td>'.$this->_renderTextBox($fieldName.'_title', $field->values['TITLE'], 40, 100, $field->settings->get('lnk_attributes_title')).'</td>';		      							
			$html .= '</tr>';
		}
		
		if($field->settings->get('lnk_show_target'))
		{
			$html .= '<tr>';
			$html .= '<td>'.Jtext::_('COM_FORM2CONTENT_TARGET').':</td>';	      							
			$html .= '<td>'.JHTMLSelect::genericlist($listTarget, $fieldName . '_target',$field->settings->get('lnk_attributes_target') ,'value', 'text', $field->values['TARGET']).'</td>';
			$html .= '</tr>';
		}
		
		$html .= '</table>';
		
		if(!$field->settings->get('lnk_show_display_as'))
		{
			$html .= $this->renderHiddenField($fieldName.'_display', '');
		}

		if(!$field->settings->get('lnk_show_title'))
		{
			$html .= $this->renderHiddenField($fieldName.'_title', '');
		}
		
		if(!$field->settings->get('lnk_show_target'))
		{
			$html .= $this->renderHiddenField($fieldName.'_target', '');
		}
		
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';

		return $html;
	}
		
	function _renderDatePicker($field, $fieldName, $fieldDescription, $parms)
	{
		$html 			= '';
		$value 			= $field->values['VALUE'];
		$attributes 	= $field->settings->get('dat_attributes') ? $field->settings->get('dat_attributes') : 'class="inputbox"';
		
		if($value)
		{
			$date = new JDate($value);
			$value = $date->toFormat($this->config->get('date_format'));
		}

		$html .= '<div class="f2c_field">';	
		$html .= HtmlHelper::renderCalendar($value, $field->values['VALUE'], $fieldName, $fieldName, $this->config->get('date_format'), $attributes);	
		$html .= $this->_renderRequiredText($field);
		$html .= $fieldDescription;
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
		
		return $html;
	}
	
	function _renderInfoText($field, $fieldName, $fieldDescription, $parms)
	{
		$html = '';
	
		$html .= '<div class="f2c_field">';		
		$html .= $field->settings->get('inf_text') . $fieldDescription;		
		$html .= '</div>';
		
		return $html;
	}

	function _renderDisplayList($field, $fieldName, $fieldDescription, $parms)
	{
		$html 				= '';
		$fieldValue 		= $field->values['VALUE'];
		$attributesTable	= $field->settings->get('dsp_attributes_table') ? $field->settings->get('dsp_attributes_table') : 'border="1"';
		
		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr><td>';
		
		$html .= '<table '.$attributesTable.' id="'.$fieldName.'" cellspacing="0" cellpadding="0">
					<tr '.$field->settings->get('dsp_attributes_tr').'>
						<th '.$field->settings->get('dsp_attributes_th').' style="width:200px;">' . Jtext::_('COM_FORM2CONTENT_LIST_ITEM') . '</th>
						<th '.$field->settings->get('dsp_attributes_th').' style="width:41px;">&nbsp;</th>	
						<th '.$field->settings->get('dsp_attributes_th').' style="width:26px;">&nbsp;</th>			
						<th '.$field->settings->get('dsp_attributes_th').' style="width:26px;">&nbsp;</th>						
					</tr>';
									
		$rowcount = 0;
		$imgPath = JURI::root(true).'/media/com_form2content/images/';
						
		if($fieldValue && count($fieldValue) > 0)
		{
			foreach($fieldValue as $value)
			{
				$rowId = $fieldName.'_'.$rowcount;
				$rowcount++;
				$html .= '<tr id="'.$rowId.'" '.$field->settings->get('dsp_attributes_tr').'>
						  <td '.$field->settings->get('dsp_attributes_td').'>
						  	<input type="hidden" name="'.$fieldName.'RowKey[]" value="'.$rowId.'"/>
						  	<input type="text" id="'.$rowId.'val" name="'.$rowId.'val" size="40" value="' . htmlspecialchars($value) . '" maxlength="255" '.$field->settings->get('dsp_attributes_item_text').' />
						  </td>
						  <td '.$field->settings->get('dsp_attributes_td').'><a href="javascript:moveUp(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'uparrow.png" alt="' . Jtext::_('COM_FORM2CONTENT_UP') . '" /></a><a href="javascript:moveDown(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'downarrow.png" alt="' . Jtext::_('COM_FORM2CONTENT_DOWN') . '" /></a></td>
						  <td '.$field->settings->get('dsp_attributes_td').'><a href="javascript:removeRow(\''.$rowId.'\');"><img src="'.$imgPath.'remove.png" alt="' . Jtext::_('COM_FORM2CONTENT_DELETE') . '" /></a></td>
						  <td '.$field->settings->get('dsp_attributes_td').'><a href="javascript:addDisplayListRow(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'add.png" alt="' . Jtext::_('COM_FORM2CONTENT_ADD') . '" /></a></td>
						  </tr>';
			}
		}
		
		$html .= 	'</table>
					 <br/>
					 <input type="button" value="' . Jtext::_('COM_FORM2CONTENT_ADD_LIST_ITEM') . '" '.$field->settings->get('dsp_attributes_add_button').' onclick="addDisplayListRow(\''.$fieldName.'\',\'\');" />
					 <input type="hidden" name="'.$fieldName.'MaxKey" id="'.$fieldName.'MaxKey" value="'.$rowcount.'"/>';
					 		
		$html .= '</td><td valign="top">';
		
		$html .= $this->_renderRequiredText($field);
		$html .= $fieldDescription.'</td></tr></table>';
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
		
		return $html;		
	}	
	
	function _renderFileUpload($field, $fieldName, $fieldDescription, $parms)
	{
		$html				= '';
		$fieldValue			= $field->values['FILENAME'];
		$uploadAttributes	= $field->settings->get('ful_attributes_upload') ? $field->settings->get('ful_attributes_upload') : 'class="inputbox"';
		$deleteAttributes	= $field->settings->get('ful_attributes_delete') ? $field->settings->get('ful_attributes_delete') : 'class="inputbox"';
		
		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr><td>';
		$html .= '<input type="file" id="'.$fieldName.'_fileupload" name="'.$fieldName.'_fileupload" '.$uploadAttributes.'>&nbsp;';
		$html .= '<input type="button" onclick="clearUpload(\''.$fieldName.'_fileupload\');return false;" value="'.Jtext::_('COM_FORM2CONTENT_CLEAR_FIELD').'" />&nbsp;';		
		$html .= '<div style="clear:both;"><input type="checkbox" id="'.$fieldName.'_del" name="'.$fieldName.'_del" '.$deleteAttributes.'">&nbsp;'.Jtext::_('COM_FORM2CONTENT_DELETE_FILE').'</div>';
		$html .= $this->_renderRequiredText($field);								
		$html .= $this->renderHiddenField($fieldName . '_filename', $fieldValue);		
		$html .= $fieldDescription;
		$html .= '</td></tr><tr><td valign="top">'.Jtext::_('COM_FORM2CONTENT_PREVIEW').':&nbsp;';
				
		if($fieldValue)
		{
			$html .= '<a id="'.$fieldName.'_preview" href="'.Path::Combine(F2C_FileUpload::GetFileUrl($field->projectid, $this->formId, $field->id), $fieldValue).'" target="_blank">' . F2C_Renderer::stringHTMLSafe($fieldValue) . '</a>';
		}
				
		$html .= '</td></tr></table>';
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
			
		return $html;
		
	}
	
	function _renderDatabaseLookup($field, $fieldName, $fieldDescription, $parms)
	{
		$html			= '';
		$listOptions 	= array();
		$fieldValue		= $field->values['VALUE'];

		if($field->settings->get('dbl_show_empty_choice_text'))
		{
			$listOptions[] = JHTMLSelect::option('', $field->settings->get('dbl_empty_choice_text'),'key','value');
		}
			      				
		$db =& JFactory::getDBO();
		$db->setQuery($field->settings->get('dbl_query'));
		$rowList = $db->loadRowList();

		if(count($rowList))
		{
			foreach($rowList as $row)
			{
				$listOptions[] = JHTMLSelect::option($row[0], $row[1],'key','value');
			}
		}

		$html .= '<div class="f2c_field">';		

		if($field->settings->get('dbl_display_mode') == 0)
		{
			$html .= JHTMLSelect::genericlist($listOptions, $fieldName, $field->settings->get('dbl_attributes'), 'key', 'value', $fieldValue);
		}
		else
		{  
			$html .= JHTML::_('select.radiolist', $listOptions, $fieldName, $field->settings->get('dbl_attributes'), 'key', 'value', $fieldValue);	
		}
		
		$html .= $this->_renderRequiredText($field);
		$html .= $fieldDescription;
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
		
		return $html;
	}
	
	function _renderGeoCoder($field, $fieldName, $fieldDescription, $parms)
	{
		$defaultLat			= $field->settings->get('gcd_map_lat', '55.166085');
		$defaultLon			= $field->settings->get('gcd_map_lon', '10.712890');
		$mapWidth 			= $field->settings->get('gcd_map_width', '350');
		$mapHeight 			= $field->settings->get('gcd_map_height', '350');
		$attributesAddress 	= $field->settings->get('gcd_attributes_address', 'class="inputbox"');	
		$attributesLookup 	= $field->settings->get('gcd_attributes_lookup_lat_lon', 'class="inputbox"');		
		$attributesClear 	= $field->settings->get('gcd_attributes_clear_results', 'class="inputbox"');						
		$addressValue 		= $field->values['ADDRESS'];
		$addressId 			= $field->internal['addressid'];
		$latValue 			= $field->values['LAT'];
		$latId 				= $field->internal['latid'];
		$lonValue 			= $field->values['LON'];
		$lonId 				= $field->internal['lonid'];
		
		$latLonDisplay = ($latValue && $lonValue) ? '('.$latValue.', '.$lonValue.')' : '';
				
		if($field->settings->get('gcd_show_map'))
		{
			$document =& JFactory::getDocument();
		
			$latOnMap = $latLonDisplay ? $latValue : $defaultLat;
			$lonOnMap = $latLonDisplay ? $lonValue : $defaultLon;
		
			$js =	'window.addEvent(\'load\', function() {
					 var latlng = new google.maps.LatLng('.$latOnMap.', '.$lonOnMap.');
    				 var myOptions = { zoom: '.$field->settings->get('gcd_map_zoom').', center: latlng, mapTypeId: google.maps.MapTypeId.'.$field->settings->get('gcd_map_type').' }; ' .
    				$fieldName.'_map = new google.maps.Map(document.getElementById("'.$fieldName.'_map_canvas"), myOptions); ';

			if($latLonDisplay)
			{
				// initialize the marker
				$js .= 'eval('.$fieldName.'_marker = new google.maps.Marker({ map: '.$fieldName.'_map, position: latlng }));';
			}

			$js .=	' });';
			  		
			$document->addScriptDeclaration($js);
		}
										
		$html = '';
		$html .= '<div class="f2c_field">';		
		
		if($field->settings->get('gcd_show_map'))
		{
			$html .= '<div id="'.$fieldName.'_map_canvas" style="width: '.$mapWidth.'px; height: '.$mapHeight.'px;"></div><br/>';
		}
		
 		$html .= '<table>';
 		$html .= '<tr><td>'.Jtext::_('COM_FORM2CONTENT_ADDRESS_OF_LOCATION').': </td><td><input id="'.$fieldName.'_address" name="'.$fieldName.'_address" type="textbox" '.$attributesAddress.' value="'.$this->stringHTMLSafe($addressValue).'" style="width:300px;">';
 		$html .= '</td></tr>';
		$html .= '<tr><td colspan="2">';
 		$html .= '<input type="button" '.$attributesLookup.' value="'.Jtext::_('COM_FORM2CONTENT_LOOKUP_LAT_LON').'" onclick="F2C_GeoCoderConvertAddress(\''.$fieldName.'\');">';
 		$html .= '&nbsp;<input type="button" '.$attributesClear.' value="'.Jtext::_('COM_FORM2CONTENT_CLEAR_RESULTS').'" onclick="F2C_GeoCoderClearResults(\''.$fieldName.'\');">';
		$html .= $this->_renderRequiredText($field); 		
 		$html .= $fieldDescription;
 		$html .= $this->renderHiddenField($fieldName.'_hid_lat', $latValue);
 		$html .= $this->renderHiddenField($fieldName.'_hid_lon', $lonValue);
 		$html .= '</td></tr>';
 		$html .= '<tr><td>'.Jtext::_('COM_FORM2CONTENT_LAT_LON').': </td><td><span id="'.$fieldName.'_latlon" name="'.$fieldName.'_latlon">'.$latLonDisplay.'</span>';
 		$html .= '<span id="'.$fieldName.'_error" name="'.$fieldName.'_error" style="display: none;">'.Jtext::_('COM_FORM2CONTENT_ERROR_GEOCODER_PROCESS').'</span></td></tr>';
 		$html .= '</table>';
 		$html .= $this->renderHiddenField('hid'.$fieldName.'_lat', $latId);
 		$html .= $this->renderHiddenField('hid'.$fieldName.'_lon', $lonId);
 		$html .= $this->renderHiddenField('hid'.$fieldName.'_address', $addressId);
		$html .= '</div>';
		
		return $html;
	}
	
	function _renderDatabaseLookupMulti($field, $fieldName, $fieldDescription, $parms)
	{
		$html 				= '';
		$attributesTable	= $field->settings->get('dlm_attributes_table') ? $field->settings->get('dlm_attributes_table') : 'border="1"';
		$valueList			= array();

		// Prepare drop down list
		if($field->settings->get('dlm_show_empty_choice_text'))
		{
			$listOptions[] = JHTMLSelect::option('', $field->settings->get('dlm_empty_choice_text'),'key','value');
		}
		
		$db =& JFactory::getDBO();
		$db->setQuery($field->settings->get('dlm_query'));
		$rowList = $db->loadRowList(0);

		if(count($rowList))
		{
			foreach($rowList as $row)
			{
				$listOptions[] = JHTMLSelect::option($row[0], $row[1],'key','value');
			}
		}
		
		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr><td>';
		
		$html .= '<table '.$attributesTable.' id="'.$fieldName.'" cellspacing="0" cellpadding="0">		
					<tr '.$field->settings->get('dlm_attributes_tr').'>
						<th '.$field->settings->get('dlm_attributes_th').' style="width:200px;">' . Jtext::_('COM_FORM2CONTENT_LIST_ITEM') . '</th>
						<th '.$field->settings->get('dlm_attributes_th').' style="width:41px;">&nbsp;</th>	
						<th '.$field->settings->get('dlm_attributes_th').' style="width:26px;">&nbsp;</th>			
					</tr>';
		
		$rowcount = 0;
		$imgPath = JURI::root(true).'/media/com_form2content/images/';
						
		if(count($field->values['VALUE']))
		{
			foreach($field->values['VALUE'] as $value)
			{
				$rowId = $fieldName.'_'.$rowcount;
				$rowcount++;
				$html .= '<tr id="'.$rowId.'" '.$field->settings->get('dlm_attributes_tr').'>
						  <td '.$field->settings->get('dlm_attributes_td').'>
						  	<input type="hidden" name="'.$fieldName.'RowKey[]" value="'.$rowId.'"/>
						  	<input type="hidden" id="'.$rowId.'val" name="'.$rowId.'val" value="' . htmlspecialchars($value) . '" />'.
							F2C_Renderer::stringHTMLSafe($rowList[$value][1]).'
						  </td>
						  <td '.$field->settings->get('dlm_attributes_td').'><a href="javascript:moveUp(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'uparrow.png" alt="' . Jtext::_('COM_FORM2CONTENT_UP') . '" /></a><a href="javascript:moveDown(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'downarrow.png" alt="' . Jtext::_('COM_FORM2CONTENT_DOWN') . '" /></a></td>
						  <td '.$field->settings->get('dlm_attributes_td').'><a href="javascript:removeRow(\''.$rowId.'\');"><img src="'.$imgPath.'remove.png" alt="' . Jtext::_('COM_FORM2CONTENT_DELETE') . '" /></a></td>
						  </tr>';
			}
		}
		
		$html .= 	'</table>
					 <br/>				
					 <input type="hidden" name="'.$fieldName.'MaxKey" id="'.$fieldName.'MaxKey" value="'.$rowcount.'"/>';
					 		
		$html .= '</td><td valign="top">';
		
		$html .= $this->_renderRequiredText($field);
		$html .= $fieldDescription.'</td></tr></table>';
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->id);
		$html .= JHTMLSelect::genericlist($listOptions, $fieldName.'_lookup', $field->settings->get('dlm_attributes_select'), 'key', 'value', '').'&nbsp;';
		$html .= '<input type="button" value="' . Jtext::_('COM_FORM2CONTENT_ADD') . '" '.$field->settings->get('dlm_attributes_add_button').' onclick="addDbLookupkMultiRow(\''.$fieldName.'\',\'\');" />';
		$html .= '</div>';
		
		return $html;		
	}

	function _renderImageGallery($field, $fieldName, $fieldDescription, $parms)
	{
		$html 				= '';
		$fieldValue 		= $field->values['VALUE'];
		$attributesTable	= $field->settings->get('igl_attributes_table') ? $field->settings->get('igl_attributes_table') : 'border="1"';
		
		$html .= '<div class="f2c_field">';		
		$html .= '<table><tr><td>';
		
		$html .= '<table '.$attributesTable.' id="'.$fieldName.'" cellspacing="0" cellpadding="0">
					<tr '.$field->settings->get('igl_attributes_tr').'>
						<th '.$field->settings->get('igl_attributes_th').'>' . Jtext::_('COM_FORM2CONTENT_IMAGES') . '</th>
						<th '.$field->settings->get('igl_attributes_th').'>&nbsp;</th>	
						<th '.$field->settings->get('igl_attributes_th').'>&nbsp;</th>			
					</tr>';
		
		$rowcount = 0;
		$imgPath = JURI::root(true).'/media/com_form2content/images/';
		$thumbsgalleryUrl = F2cUri::GetClientRoot().'images/stories/com_form2content/p'.$field->projectid.'/f'.$this->formId.'/gallery'.$field->id.'/thumbs/';
				
		if($fieldValue && count($fieldValue) > 0)
		{
			foreach($fieldValue as $value)
			{
				$rowId 		= $fieldName.'_'.$rowcount;
				$thumbSize 	= ' ' . (((int)$value['WIDTH'] > (int)$value['HEIGHT']) ? 'width' : ' height') . '="100"';
				$rowcount++;
				
				$html .= '<tr id="'.$rowId.'" '.$field->settings->get('igl_attributes_tr').'>
							<td '.$field->settings->get('igl_attributes_td').'>
								<table cellspacing="1" cellpadding="1" border="0">
								<tr>
									<td rowspan="2"><img src="' . $thumbsgalleryUrl . $value['FILENAME'] . '"' . $thumbSize . ' /></td>
									<td>'.JText::_('COM_FORM2CONTENT_ALT_TEXT').'</td>
									<td><input type="text" id="'.$rowId.'alt" name="'.$rowId.'alt" size="40" value="' . htmlspecialchars($value['ALT']) . '" maxlength="255" '.$field->settings->get('igl_attributes_item_text').' /></td>
								</tr>
								<tr>
									<td>'.JText::_('COM_FORM2CONTENT_TITLE').'</td>
									<td><input type="text" id="'.$rowId.'title" name="'.$rowId.'title" size="40" value="' . htmlspecialchars($value['TITLE']) . '" maxlength="255" '.$field->settings->get('igl_attributes_item_text').' /></td>
								</tr>
								</table>
							  	<input type="hidden" name="'.$fieldName.'RowKey[]" value="'.$rowId.'"/>
							  	<input type="hidden" name="'.$rowId.'filename" id="'.$rowId.'filename" value="'.$value['FILENAME'].'"/>
	  							<input type="hidden" name="'.$rowId.'width" id="'.$rowId.'width" value="'.$value['WIDTH'].'"/>
	  							<input type="hidden" name="'.$rowId.'height" id="'.$rowId.'height" value="'.$value['HEIGHT'].'"/>
							  	<input type="hidden" name="'.$rowId.'thumbwidth" id="'.$rowId.'thumbwidth" value="'.$value['THUMBWIDTH'].'"/>
							  	<input type="hidden" name="'.$rowId.'thumbheight" id="'.$rowId.'thumbheight" value="'.$value['THUMBHEIGHT'].'"/>
							  	<input type="hidden" name="'.$rowId.'isnew" id="'.$rowId.'isnew" value="0"/>
							</td>
						  	<td '.$field->settings->get('igl_attributes_td').'><a href="javascript:moveUp(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'uparrow.png" alt="' . Jtext::_('COM_FORM2CONTENT_UP') . '" /></a><a href="javascript:moveDown(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'downarrow.png" alt="' . Jtext::_('COM_FORM2CONTENT_DOWN') . '" /></a></td>
						  	<td '.$field->settings->get('igl_attributes_td').'><a href="javascript:removeRow(\''.$rowId.'\');"><img src="'.$imgPath.'remove.png" alt="' . Jtext::_('COM_FORM2CONTENT_DELETE') . '" /></a></td>
						  </tr>';
											
				/*
				$html .= '<tr id="'.$rowId.'" '.$field->settings->get('igl_attributes_tr').'>
						  <td '.$field->settings->get('igl_attributes_td').'>
						  	<img src="' . $thumbsgalleryUrl . $value['FILENAME'] . '"' . $thumbSize . ' /><br/>					  	
						  	alt:<input type="text" id="'.$rowId.'alt" name="'.$rowId.'alt" size="40" value="' . htmlspecialchars($value['ALT']) . '" maxlength="255" '.$field->settings->get('igl_attributes_item_text').' /><br/>
						  	title:<input type="text" id="'.$rowId.'title" name="'.$rowId.'title" size="40" value="' . htmlspecialchars($value['TITLE']) . '" maxlength="255" '.$field->settings->get('igl_attributes_item_text').' />
						  	<input type="hidden" name="'.$fieldName.'RowKey[]" value="'.$rowId.'"/>
						  	<input type="hidden" name="'.$rowId.'filename" id="'.$rowId.'filename" value="'.$value['FILENAME'].'"/>
  							<input type="hidden" name="'.$rowId.'width" id="'.$rowId.'width" value="'.$value['WIDTH'].'"/>
  							<input type="hidden" name="'.$rowId.'height" id="'.$rowId.'height" value="'.$value['HEIGHT'].'"/>
						  	<input type="hidden" name="'.$rowId.'thumbwidth" id="'.$rowId.'thumbwidth" value="'.$value['THUMBWIDTH'].'"/>
						  	<input type="hidden" name="'.$rowId.'thumbheight" id="'.$rowId.'thumbheight" value="'.$value['THUMBHEIGHT'].'"/>
						  </td>
						  <td '.$field->settings->get('igl_attributes_td').'><a href="javascript:moveUp(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'uparrow.png" alt="' . Jtext::_('COM_FORM2CONTENT_UP') . '" /></a><a href="javascript:moveDown(\''.$fieldName.'\',\''.$rowId.'\');"><img src="'.$imgPath.'downarrow.png" alt="' . Jtext::_('COM_FORM2CONTENT_DOWN') . '" /></a></td>
						  <td '.$field->settings->get('igl_attributes_td').'><a href="javascript:removeRow(\''.$rowId.'\');"><img src="'.$imgPath.'remove.png" alt="' . Jtext::_('COM_FORM2CONTENT_DELETE') . '" /></a></td>
						  </tr>';
				*/
			}
		}
		
		$html .= 	'</table>
					 <br/>
					 <iframe id="uploadframe" width="300px;" height="50px;" src="http://f2c_pro_16.localhost:81/administrator/index.php?option=com_form2content&view=imagegallery&layout=control&tmpl=component&task=imagegallery.display&formid='.$this->formId.'&fieldid='.$field->id.'&projectid='.$field->projectid.'" scrolling="no"></iframe>
					 <input type="hidden" name="'.$fieldName.'MaxKey" id="'.$fieldName.'MaxKey" value="'.$rowcount.'"/>';
					 		
		$html .= '</td><td valign="top">';
		
		$html .= $this->_renderRequiredText($field);
		$html .= $fieldDescription.'</td></tr></table>';
		$html .= $this->renderHiddenField('hid'.$fieldName, $field->internal['fieldcontentid']);
		$html .= '</div>';
		
		
		/*
		$html = '';
		
		// determine the gallery directory
		$galleryDir			= JPATH_SITE.DS.'images'.DS.'stories'.DS.'com_form2content';
		$galleryThumbsDir	= '';
		
		if($this->formId)
		{
			$galleryDir = Path::Combine($galleryDir, 'p'.$contentTypeId.DS.'f'.$formId.DS.'gallery'.$field->id);
		}
		else 
		{
			$session 	= JFactory::getSession();
			$galleryDir = Path::Combine($galleryDir, 'gallerytmp'.DS.$session->getId());
		}
		
		if(JFolder::exists($galleryDir))
		{
			$arrFiles = JFolder::files($galleryDir);
			
			if(count($arrFiles))
			{
				foreach($arrFiles as $file)
				{
					echo $file . ' <a href="http://f2c_pro_16.localhost:81/administrator/index.php?option=com_form2content&view=imagegallery&layout=control&tmpl=component&task=imagegallery.delete&formid='.$this->formId.'&projectid='.$field->projectid.'&imageid='.$file.'">delete</a><br/>';
				}
			}
		}
				
		$html .= '<iframe id="uploadframe" width="300px;" height="200px;" src="http://f2c_pro_16.localhost:81/administrator/index.php?option=com_form2content&view=imagegallery&layout=control&tmpl=component&task=imagegallery.display&formid='.$this->formId.'&projectid='.$field->projectid.'">';
		$html .= '</iframe>';
		*/
		return $html;
	}
	
	function _renderTextBox($name, $value = '', $size = '', $maxlength = '', $tags = '')
	{
		$html 	= '';
		$class 	= ($tags) ? '' : 'class="inputbox"';
		
		$html .= '<input type="text" '.$class.' name="'.$name.'" id="'.$name.'"';
		$html .= ($value != '') ? ' value= "' . F2C_Renderer::stringHTMLSafe($value) . '"' : '';
		$html .= $size ? ' size= "' . $size . '"' : '';
		$html .= $maxlength ? ' maxlength= "' . $maxlength . '"' : '';
		$html .= $tags . '/>';
		
		return $html;
	}
	
	function _detectUTF8($string)
	{
	    return preg_match('%(?:
	        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
	        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
	        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	        )+%xs', 
	    $string);
	}

	function stringHTMLSafe($string)
	{
		if(F2C_Renderer::_detectUTF8($string))
		{
			$safeString = htmlentities($string, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			$safeString = htmlentities($string, ENT_COMPAT);
		}
		
		return $safeString;
	}
	
	function _getFieldValue($fieldValues, $attribute, $default = '')
	{
		return ($fieldValues && array_key_exists($attribute, $fieldValues)) ? $fieldValues[$attribute]->content : $default;
	}

	function _getFieldContentId($fieldValues, $attribute)
	{
		return ($fieldValues && array_key_exists($attribute, $fieldValues)) ? $fieldValues[$attribute]->id : 0;
	}

	function _getFieldDescription($field)
	{
		$fieldLabel = '';
		$fieldDescription = '';
		
		if(array_key_exists($field->id, $this->translatedFields))
		{
			$fieldLabel 		= $this->translatedFields[$field->id]->title_translation;
			$fieldDescription 	= $this->translatedFields[$field->id]->description_translation;
		}
		else
		{
			$fieldLabel = $field->title;
			$fieldDescription = $field->description;				
		}
		
		if($fieldDescription)
		{
			$fieldDescription = '&nbsp;' . JHTML::tooltip($fieldDescription, $fieldLabel);				
		}
		
		return $fieldDescription;		
	}
	
	function renderFieldLabel($field)
	{
		$label = '';
		
		if($field->fieldtypeid != F2C_FIELDTYPE_INFOTEXT)
		{ 
			$labelText = (array_key_exists($field->id, $this->translatedFields)) ? $this->translatedFields[$field->id]->title_translation : $field->title; 
			$label = '<label for="t'.$field->id.'">'.$labelText.'</label>';
		}
		
		return $label; 
	}
	
	function _renderRequiredText($field)
	{
		if($field->settings->get('requiredfield') && $this->contentTypeSettings['required_field_text'])
		{
			return '<span class="f2c_required">&nbsp;'.$this->contentTypeSettings['required_field_text'].'</span>';
		}		
	}
}
?>