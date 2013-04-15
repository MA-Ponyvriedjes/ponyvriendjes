<?php
defined('_JEXEC') or die('Restricted acccess');

if (!class_exists('Smarty')) 
{
	require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'libraries'.DS.'smarty'.DS.'Smarty.class.php');
}

class F2C_Smarty
{
	var $templates;
	var $form;
	var $error = '';
	var $smarty = null;
	
	public function F2C_Smarty()
	{
		$this->smarty 				= new Smarty();
		$this->smarty->template_dir = F2cFactory::getConfig()->get('template_path');
		$this->smarty->compile_dir 	= JFactory::getConfig()->get('tmp_path');
	}

	public function parseIntro()
	{
		$parsedContent = $this->smarty->fetch($this->templates[F2C_TEMPLATE_INTRO]);		
		return $parsedContent;
	}

	public function parseMain()
	{
		$parsedContent = '';
		
		if(array_key_exists(F2C_TEMPLATE_MAIN, $this->templates))
		{	
			$parsedContent = $this->smarty->fetch($this->templates[F2C_TEMPLATE_MAIN]);
		}
		
		return $parsedContent;
	}
	
	public function addTemplate($templateName, $templateType)
	{
		if(!JFile::exists(Path::Combine(F2cFactory::getConfig()->get('template_path'), $templateName)))
		{
			$this->error = JText::_('COM_FORM2CONTENT_ERROR_TEMPLATE_NOT_FOUND');
			return false;
		}
		
		$this->templates[$templateType] = $templateName;
		
		return true;	
	}

	public function clearTemplates()
	{
		// No action necessary
	}
		
	public function addVar($name, $value)
	{
		$this->smarty->assign(strtoupper($name), $value);		
	}
	
	public function clearVar($name)
	{
		$this->smarty->clear_assign(strtoupper($name));
	}
	
	
	public function clearAllVars()
	{
		$this->smarty->clear_all_assign();
	}
		
	public function addFormVar($field)
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

		$functionName 	= '_addVar'.$F2C_FIELD_FUNCTION_MAPPING[$field->fieldtypeid];
		$fieldData 		= count($field->values) ? $field->values : null;

		// Keep the title for backward compatibility
		$this->addVar($field->fieldname.'_TITLE', HtmlHelper::stringHTMLSafe($field->title));				
		$this->addVar($field->fieldname.'_CAPTION', HtmlHelper::stringHTMLSafe($field->title));				
		$this->addVar($field->fieldname.'_DESCRIPTION', HtmlHelper::stringHTMLSafe($field->description));				
		$this->$functionName($field, $fieldData);
	}
	
	private function _addVarSingleLineText($field, $data)
	{
		$this->addVar($field->fieldname, HtmlHelper::stringHTMLSafe($data['VALUE']));
		$this->addVar($field->fieldname .'_RAW', $data['VALUE']);
	}

	private function _addVarMultiLineText($field, $data)
	{
		$this->addVar($field->fieldname, nl2br(HtmlHelper::stringHTMLSafe($data['VALUE'])));
		$this->addVar($field->fieldname .'_RAW', nl2br($data['VALUE']));
	}
	
	private function _addVarMultiLineEditor($field, $data)
	{
		$this->addVar($field->fieldname, $data['VALUE']);
	}
	
	private function _addVarCheckBox($field, $data)
	{
		if($data['VALUE'])
		{
			$this->addVar($field->fieldname, $data['VALUE']); 
		}
	}

	private function _addVarSingleSelectList($field, $data)
	{
		$options = (array)$field->settings->get('ssl_options');
		
		if($data['VALUE'])
		{
			$fieldText 	= '';					
			
			// TODO: why doesn't array_key_exists work here?
			foreach($options as $key => $value)
			{
				if($key == $data['VALUE'])
				{
					$fieldText = $value;
					break;
				}
			}
			
			$this->addVar($field->fieldname, $data['VALUE']);
			$this->addVar($field->fieldname.'_TEXT', $fieldText);
		}
	}
	
	private function _addVarImage($field, $data)
	{
		if($data['FILENAME'])
		{
			if($field->settings->get('img_output_mode') == 0)
			{				
				$this->addVar($field->fieldname, F2C_Image::GetImagesUrl($this->form->projectid, $this->form->id) . $data['FILENAME']);						
			}
			else
			{
				$tagWidth = ($data['WIDTH'] > 0) ? ' width="'.$data['WIDTH'].'"' : '';
				$tagHeight = ($data['HEIGHT'] > 0) ? ' height="'.$data['HEIGHT'].'"' : '';
				$this->addVar($field->fieldname, '<img src="' . F2C_Image::GetImagesUrl($this->form->projectid, $this->form->id) . $data['FILENAME'] . '" alt="' . $data['ALT'] . '" title="' . $data['TITLE'] . '"' . $tagWidth . $tagHeight . '/>');
			}

			// add image information
			$this->addVar($field->fieldname.'_WIDTH', ($data['WIDTH'] > 0) ? $data['WIDTH'] : '');					
			$this->addVar($field->fieldname.'_HEIGHT', ($data['HEIGHT'] > 0) ? $data['HEIGHT'] : '');					
			$this->addVar($field->fieldname.'_WIDTH_THUMB', ($data['WIDTH_THUMBNAIL'] > 0) ? $data['WIDTH_THUMBNAIL'] : '');					
			$this->addVar($field->fieldname.'_HEIGHT_THUMB', ($data['HEIGHT_THUMBNAIL'] > 0) ? $data['HEIGHT_THUMBNAIL'] : '');					

			// add image urls
			$this->addVar($field->fieldname.'_PATH_ABSOLUTE', Path::Combine(F2C_Image::GetImagesPath($this->form->projectid, $this->form->id, false), $data['FILENAME']));
			$this->addVar($field->fieldname.'_PATH_RELATIVE', Path::Combine(F2C_Image::GetImagesPath($this->form->projectid, $this->form->id, true), $data['FILENAME']));
			
			// add thumbnail urls
			$this->addVar($field->fieldname.'_THUMB_URL_ABSOLUTE', Path::Combine(F2C_Image::GetThumbnailsUrl($this->form->projectid, $this->form->id), $data['FILENAME']));
			$this->addVar($field->fieldname.'_THUMB_URL_RELATIVE', Path::Combine(F2C_Image::GetThumbnailsUrl($this->form->projectid, $this->form->id, true), $data['FILENAME']));			
		}
		else
		{
			// no image was specified
			$this->addVar($field->fieldname, '');
			$this->addVar($field->fieldname.'_PATH_ABSOLUTE', '');
			$this->addVar($field->fieldname.'_PATH_RELATIVE', '');
			$this->addVar($field->fieldname.'_THUMB_URL_ABSOLUTE', '');
			$this->addVar($field->fieldname.'_THUMB_URL_RELATIVE', '');
		}

		$this->addVar($field->fieldname.'_ALT', HtmlHelper::stringHTMLSafe($data['ALT']));					
		$this->addVar($field->fieldname.'_TITLE', HtmlHelper::stringHTMLSafe($data['TITLE']));					
	}

	private function _addVarIFrame($field, $data)
	{
		if($data['URL'])
		{
			$iframeTag = '<iframe src="' . $data['URL'] . '" height="' . $data['HEIGHT'] . '" width="' . $data['WIDTH'] . '"></iframe>';
			$this->addVar($field->fieldname, $iframeTag);
		}				
	}	
	
	private function _addVarEmail($field, $data)
	{
		$emailTag = '';
		$emailAddress = '';
		$emailDisplay = '';
				
		if($data['EMAIL'])
		{
			$emailDisplay = $data['DISPLAY_AS'] ? $data['DISPLAY_AS'] : $data['EMAIL'];
			$emailTag = '<a href="mailto:' . $data['EMAIL'] . '">' . HtmlHelper::stringHTMLSafe($emailDisplay) . '</a>';
			$emailAddress = $data['EMAIL'];
		}
			
		$this->addVar($field->fieldname, $emailTag);
		$this->addVar($field->fieldname.'_ADDRESS', $data['EMAIL']);
		$this->addVar($field->fieldname.'_DISPLAY', $data['DISPLAY_AS']);
	}
	
	private function _addVarHyperlink($field, $data)
	{
		$linkTitle = '';
		$linkTarget = '';
		$linkDisplay = '';
		$linkUrl = '';
		
		if($data['URL'])
		{
			$display = $data['DISPLAY_AS'] ? $data['DISPLAY_AS'] : $data['URL'];
			$linkTitle 		= $data['TITLE'];
			$linkTarget 	= $data['TARGET'];
			$linkDisplay 	= $data['DISPLAY_AS'];
			$linkUrl 		= $data['URL'];
			
			if($field->settings->get('lnk_add_http_prefix', 0))
			{
				if(!strstr($linkUrl, '://'))
				{
					$linkUrl = 'http://' . $linkUrl;
				}
			}
			
			if($field->settings->get('lnk_output_mode') == 0)
			{
				$linkTag = $linkUrl;
			}
			else
			{
				$linkTag = '<a href="' . $linkUrl . '" target="' . $data['TARGET'] . '" title="' . $data['TITLE'] . '">' . $display . '</a>';					
			}
			
			$this->addVar($field->fieldname, $linkTag);
		}
		else
		{
			$this->addVar($field->fieldname, '');
		}
		
		$this->addVar($field->fieldname.'_URL', $linkUrl);		
		$this->addVar($field->fieldname.'_TITLE', $linkTitle);		
		$this->addVar($field->fieldname.'_TARGET', $linkTarget);		
		$this->addVar($field->fieldname.'_DISPLAY', $linkDisplay);					
	}

	private function _addVarMultiSelectList($field, $data)
	{
		$tag 			= '';		
		$customFormat 	= ''; 
		$assocArray		= array();
		$options		= (array)$field->settings->get('msl_options');
		
		if($data['VALUE'] && count($data['VALUE']))
		{
			$customFormat .= $field->settings->get('msl_pre_list_tag');  
							
			foreach($data['VALUE'] as $selectedValue)
			{
				// TODO: Why doesn't array_key_exists work here?
				foreach($options as $key => $value)
				{
					if($key == $selectedValue)
					{
						$tag 						.= '<li>' . htmlspecialchars($value) . '</li>';
						$customFormat 				.= $field->settings->get('msl_pre_element_tag').$value.$field->settings->get('msl_post_element_tag');
						$assocArray[$selectedValue] = $value;
						break;		
					}
				}
			}
			
			$customFormat .= $field->settings->get('msl_post_list_tag'); 				
		}

		$this->addVar($field->fieldname, $tag);		
		$this->addVar($field->fieldname.'_CUSTOM_FORMAT', $customFormat);
		$this->addVar($field->fieldname.'_VALUES', $assocArray);
		$this->addVar($field->fieldname.'_CSV', implode(', ', $assocArray));
	}

	private function _addVarInfoText($field, $data)
	{
		// Nothing to parse		
	}

	private function _addVarDatePicker($field, $data)
	{
		$value 			= '';
		$unixTimestamp 	= '';
		$dateFormat		= str_replace('%', '', F2cFactory::getConfig()->get('date_format'));
		
		if($data['VALUE'])
		{
			$date 			= new JDate($data['VALUE']);
			$value			= $date->format($dateFormat);
			$unixTimestamp	= $date->toUnix();
		}

		$this->addVar($field->fieldname, $value);
		$this->addVar($field->fieldname . '_RAW', $unixTimestamp);
	}

	private function _addVarDisplayList($field, $data)
	{
		$output = '';
		$values	= array();
		
		if($data['VALUE'] && count($data['VALUE']))
		{
			foreach($data['VALUE'] as $value)
			{
				$output 	.= '<li>'.htmlspecialchars($value).'</li>';
				$values[] 	= $value;
			}	
			
			if($field->settings->get('dsp_output_mode'))
			{
				$output = '<ul>'.$output.'</ul>';
			}
			else
			{
				$output = '<ol>'.$output.'</ol>';				
			}				
		}
						
		$this->addVar($field->fieldname, $output);
		$this->addVar($field->fieldname.'_VALUES', $values);
		$this->addVar($field->fieldname.'_CSV', implode(', ', $values));
	}

	private function _addVarFileUpload($field, $data)
	{
		if($data['FILENAME'])
		{
			if($field->settings->get('ful_output_mode') == 0)
			{				
				$this->addVar($field->fieldname, Path::Combine(F2C_FileUpload::GetFileUrl($this->form->projectid, $this->form->id, $field->id), $data['FILENAME']));						
			}
			else
			{
				$this->addVar($field->fieldname, '<a href="'.Path::Combine(F2C_FileUpload::GetFileUrl($this->form->projectid, $this->form->id, $field->id), $data['FILENAME']).'" target="_blank">' . HtmlHelper::stringHTMLSafe($data['FILENAME']) . '</a>');
			}
			
			$this->addVar($field->fieldname.'_FILENAME', $data['FILENAME']);
			$this->addVar($field->fieldname.'_URL_RELATIVE', Path::Combine(F2C_FileUpload::GetFileUrl($this->form->projectid, $this->form->id, $field->id, true), $data['FILENAME']));
		}
		else
		{
			// no file was specified
			$this->addVar($field->fieldname, '');
			$this->addVar($field->fieldname.'_FILENAME', '');
			$this->addVar($field->fieldname.'_URL_RELATIVE', '');
		} 
	}

	private function _addVarDatabaseLookup($field, $data)
	{
		$text = '';
		$value = '';
				
		if($data['VALUE'])
		{
			$value 		= $data['VALUE'];
			$db 		=& JFactory::getDBO();			
			$db->setQuery($field->settings->get('dbl_query'));				
			$rowList 	= $db->loadRowList(0);
			$text 		= $rowList[$value][1];			
		}	
			
		$this->addVar($field->fieldname, $value);
		$this->addVar($field->fieldname.'_TEXT', $text);
	}
	
	private function _addVarGeoCoder($field, $data)
	{
		if($data)
		{
			$this->addVar($field->fieldname.'_ADDRESS', HtmlHelper::stringHTMLSafe($data['ADDRESS']));
			$this->addVar($field->fieldname.'_LAT', $data['LAT']);
			$this->addVar($field->fieldname.'_LON', $data['LON']);
		}
		else
		{
			$this->addVar($field->fieldname.'_ADDRESS', '');
			$this->addVar($field->fieldname.'_LAT', '');
			$this->addVar($field->fieldname.'_LON', '');
		}
	}
	
	private function _addVarDatabaseLookupMulti($field, $data)
	{
		$output 	= '';		
		$db 		=& JFactory::getDBO();
		$assocArray	= array();
		
		$db->setQuery($field->settings->get('dlm_query'));				
		$dicValues = $db->loadRowList(0);

		if(count($data))
		{
			foreach($data['VALUE'] as $value)
			{
				$output .= '<li>'.$dicValues[$value][1].'</li>';
				$assocArray[$value] = $dicValues[$value][1];
			}	
			
			if($field->settings->get('dlm_output_mode'))
			{
				$output = '<ul>'.$output.'</ul>';
			}
			else
			{
				$output = '<ol>'.$output.'</ol>';				
			}				
		}
		
		$this->addVar($field->fieldname.'_VALUES', $assocArray);
		$this->addVar($field->fieldname, $output);
		$this->addVar($field->fieldname.'_CSV', implode(', ', $assocArray));		
	}
	
	private function _addVarImageGallery($field, $data)
	{
//		$this->addVar($field->fieldname, HtmlHelper::stringHTMLSafe($data['VALUE']));
	}
	
	public function getTemplateVars($formVars, &$usedVars)
	{
		foreach($this->templates as $templateName)
		{
			$this->_getTemplateVars($templateName, $formVars, $usedVars);
		}
	}
	
	private function _getTemplateVars($templateName, $formVars, &$usedVars)
	{
		$contents = JFile::read(Path::Combine(F2cFactory::getConfig()->get('template_path'), $templateName));

		// check which vars are used within the template
		foreach($formVars as $formVarAlias => $formVarName)
		{
			if(strpos($contents, '{$'.$formVarAlias.'}') !== false)
			{
				if(!array_key_exists($formVarName, $usedVars))
				{
					$usedVars[$formVarName] = strtoupper($formVarName);
				}
			}

			if(strpos($contents, '{$'.$formVarAlias.'|') !== false)
			{
				if(!array_key_exists($formVarName, $usedVars))
				{
					$usedVars[$formVarName] = strtoupper($formVarName);
				}
			}
		}		
	} 
	
	public function getPossibleTemplateVars($contentTypeFields)
	{
		$aliases = array();
		
		if(count($contentTypeFields))
		{
			foreach($contentTypeFields as $contentTypeField)
			{
				switch($contentTypeField->fieldtypeid)
				{
					case F2C_FIELDTYPE_CHECKBOX:
					case F2C_FIELDTYPE_IFRAME:
					case F2C_FIELDTYPE_MULTILINEEDITOR:
					case F2C_FIELDTYPE_MULTILINETEXT:
					case F2C_FIELDTYPE_SINGLELINE:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						break;
					case F2C_FIELDTYPE_DATABASE_LOOKUP:
					case F2C_FIELDTYPE_SINGLESELECTLIST:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_TEXT'] = $contentTypeField->fieldname;
						break;						
					case F2C_FIELDTYPE_DB_LOOKUP_MULTI:
					case F2C_FIELDTYPE_DISPLAYLIST:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_VALUES'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_CSV'] = $contentTypeField->fieldname;
						break;
					case F2C_FIELDTYPE_DATEPICKER:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_RAW'] = $contentTypeField->fieldname;
						break;
					case F2C_FIELDTYPE_EMAIL:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_ADDRESS'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_DISPLAY'] = $contentTypeField->fieldname;
						break;
					case F2C_FIELDTYPE_FILE:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_FILENAME'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_URL_RELATIVE'] = $contentTypeField->fieldname;
						break;
					case F2C_FIELDTYPE_GEOCODER:
						$aliases[strtoupper($contentTypeField->fieldname).'_ADDRESS'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_LAT'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_LON'] = $contentTypeField->fieldname;
						break;
					case F2C_FIELDTYPE_HYPERLINK:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_URL'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_TITLE'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_DISPLAY'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_TARGET'] = $contentTypeField->fieldname;
						break;
					case F2C_FIELDTYPE_IMAGE:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_RAW'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_IMAGE'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_ALT'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_TITLE'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_WIDTH'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_HEIGHT'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_WIDTH_THUMB'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_HEIGHT_THUMB'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_PATH_ABSOLUTE'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_PATH_RELATIVE'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_THUMB_URL_ABSOLUTE'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_THUMB_URL_RELATIVE'] = $contentTypeField->fieldname;
						break;
					case F2C_FIELDTYPE_INFOTEXT:
						// Infotext has no template parameters
						break;
					case F2C_FIELDTYPE_MULTISELECTLIST:
						$aliases[strtoupper($contentTypeField->fieldname)] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_CUSTOM_FORMAT'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_VALUES'] = $contentTypeField->fieldname;
						$aliases[strtoupper($contentTypeField->fieldname).'_CSV'] = $contentTypeField->fieldname;
						break;
				}
			}
		}
		
		return $aliases;
	}
}
?>