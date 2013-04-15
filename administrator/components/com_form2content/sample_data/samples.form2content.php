<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT.DS.'models'.DS.'project.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'projectfield.php');
require_once(JPATH_COMPONENT.DS.'models'.DS.'form.php');

class F2cSampleDataHelper
{
	function install()
	{
		$user 			= JFactory::getUser();
		$contentType1 	= new Form2ContentModelProject();
		$contentType2 	= new Form2ContentModelProject();
		$fieldIds		= array();	
		$templatebase 	= JPATH_SITE.'/media/com_form2content/templates/';
		$samplebase 	= JPATH_COMPONENT_ADMINISTRATOR.DS.'sample_data'.DS;
	
		JFile::copy($samplebase . 'intro_template_simple_article_example.tpl', $templatebase .'intro_template_simple_article_example.tpl');
		JFile::copy($samplebase . 'main_template_simple_article_example.tpl', $templatebase .'main_template_simple_article_example.tpl');
		JFile::copy($samplebase . 'intro_template_all_fields_example.tpl', $templatebase .'intro_template_all_fields_example.tpl');
		JFile::copy($samplebase . 'main_template_all_fields_example.tpl', $templatebase .'main_template_all_fields_example.tpl');
		
		$rules = array();
		$rules['core.create'] = array();
		$rules['core.delete'] = array();
		$rules['core.edit'] = array();
		$rules['core.edit.state'] = array();

		$rulesForm = array();
		$rulesForm['core.delete'] = array();
		$rulesForm['core.edit'] = array();
		$rulesForm['core.edit.state'] = array();
		
		$metadata = array();
		$metadata['robots'] = '';
		$metadata['author'] = '';
		$metadata['rights'] = '';
		$metadata['xreference'] = '';
		
		$attribs = array();
		$attribs['show_title']= '';
		$attribs['link_titles']= ''; 
		$attribs['show_intro']= ''; 
		$attribs['show_category']= ''; 
		$attribs['link_category']= ''; 
		$attribs['show_parent_category']= ''; 
		$attribs['link_parent_category']= '';
		$attribs['show_author']= ''; 
		$attribs['link_author']= ''; 
		$attribs['show_create_date']= ''; 
		$attribs['show_modify_date']= ''; 
		$attribs['show_publish_date']= ''; 
		$attribs['show_item_navigation']= ''; 
		$attribs['show_icons']= ''; 
		$attribs['show_print_icon']= ''; 
		$attribs['show_email_icon']= ''; 
		$attribs['show_vote']= ''; 
		$attribs['show_hits']= ''; 
		$attribs['show_noauth']= ''; 
		$attribs['alternative_readmore']= ''; 
		$attribs['article_layout']= '';
		
		$settings = array();
		$settings['article_caption'] = ''; 
		$settings['title_front_end'] = 1; 
		$settings['title_caption'] = 'Simple article Title'; 
		$settings['title_default'] = 'Simple article example - default title'; 
		$settings['title_alias_front_end'] = 1; 
		$settings['title_alias_caption'] = '';
		$settings['metadesc_front_end'] = 1;
		$settings['metadesc_caption'] = '';
		$settings['metakey_front_end'] = 1; 
		$settings['metakey_caption'] = ''; 
		$settings['frontend_catsel'] = 1;
		$settings['category_caption'] = '';
		$settings['catid'] = -1; 
		$settings['cat_behaviour'] = 0; 
		$settings['author_front_end'] = 0; 
		$settings['author_caption'] = '';
		$settings['author_alias_front_end'] = 0; 
		$settings['author_alias_caption'] = '';
		$settings['access_level_front_end'] = 0; 
		$settings['access_level_caption'] = '';
		$settings['access_default'] = 1;
		$settings['frontend_templsel'] = 1; 
		$settings['intro_template'] = 'intro_template_simple_article_example.tpl'; 
		$settings['main_template'] = 'main_template_simple_article_example.tpl'; 
		$settings['date_created_front_end'] = 1; 
		$settings['frontend_pubsel'] = 1;
		$settings['state_front_end'] = 0; 
		$settings['state_caption'] = ''; 
		$settings['state_default'] = 0; 
		$settings['featured_front_end'] = 0; 
		$settings['featured_caption'] = '';
		$settings['featured_default'] = 0; 
		$settings['language_front_end'] = 0; 
		$settings['language_caption'] = ''; 
		$settings['language_default'] = '*'; 
		$settings['max_forms'] = '';
		$settings['captcha_front_end'] = 0; 
		$settings['required_field_text'] = '*';
				
		$data = array();
		$data['id'] = 0;
		$data['title'] = 'Simple article example';
		$data['metakey'] = 'This is the default meta keyword field. Change this in the content type configuration';
		$data['metadesc'] = 'This is the default meta description. Change this in the content type configuration.';
		$data['published'] = 1;
		$data['created_by'] = $user->id; 
		$data['created'] = ''; 
		$data['modified'] = ''; 
		$data['rules'] = $rules;
		$data['metadata'] = $metadata;
		$data['attribs'] = $attribs;
		$data['settings'] = $settings;
		
		$contentType1->save($data);
		$contentType1Id = $contentType1->getState('project.id');
		
		$fieldIds[$contentType1Id] = array();
		
		// Insert the ContentType Fields
		self::createSimpleArticleExampleFields($fieldIds, $contentType1Id);
		// Create the form and add the data
		self::createSimpleArticleExampleForm($contentType1Id, $fieldIds[$contentType1Id], $rulesForm, $attribs, $metadata);
		
		// reset fieldId array
		$fieldIds = array();
		
		// modify some settings
		$settings['title_caption'] = ''; 
		$settings['title_default'] = ''; 
		$settings['category_caption'] = 'Where do you want to save the article created?';
		$settings['intro_template'] = 'intro_template_all_fields_example.tpl'; 
		$settings['main_template'] = 'main_template_all_fields_example.tpl'; 		
		
		$data = array();
		$data['id'] = 0;
		$data['title'] = 'All fields example';
		$data['metakey'] = '';
		$data['metadesc'] = '';
		$data['published'] = 1;
		$data['created_by'] = $user->id; 
		$data['created'] = ''; 
		$data['modified'] = ''; 
		$data['rules'] = $rules;
		$data['metadata'] = $metadata;
		$data['attribs'] = $attribs;
		$data['settings'] = $settings;
		
		$contentType2->save($data);
		$contentType2Id = $contentType2->getState('project.id');
		
		$fieldIds[$contentType2Id] = array();

		// Insert the ContentType Fields
		self::createAllFieldsExampleFields($fieldIds, $contentType2Id);		
		// Create the form and add the data		
		self::createAllFieldsExampleForm($contentType2Id, $fieldIds[$contentType2Id], $rulesForm, $attribs, $metadata);
	}
	
	private function createSimpleArticleExampleFields(&$fieldIds, $contentTypeId)
	{
		// Insert fields in reversed order to get the correct ordering.
		// Single select 'Show article information' field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['ssl_display_mode'] = '0';
		$fldSettings['ssl_show_empty_choice_text'] = '0';
		$fldSettings['ssl_empty_choice_text'] = '';
		$fldSettings['ssl_attributes'] = '';
		$fldSettings['ssl_options'] = array();
		$fldSettings['ssl_options']['1'] = 'Yes';
		$fldSettings['ssl_options']['2'] = 'No';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'joomla_information';
		$data['title'] = 'Show article information';
		$data['description'] = 'This is an option to display some extra article information at the bottom ... just because we can.';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_SINGLESELECTLIST;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 				
		
		// Info text 'Joomla information' field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['inf_text'] = 'This is an option to display some extra Joomla article information at the bottom of the article ... just because we can. In the template you can see the Smarty conditional code used.';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'joomla_info';
		$data['title'] = '';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_INFOTEXT;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Single line text 'Reference' field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['slt_size'] = '';
		$fldSettings['slt_max_length'] = '';
		$fldSettings['slt_attributes'] = '';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'reference';
		$data['title'] = 'Reference';
		$data['description'] = 'Text box with default value in template';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_SINGLELINE;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Image field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['img_max_width'] = '200';
		$fldSettings['img_max_height'] = '200';
		$fldSettings['img_thumb_width'] = '100';
		$fldSettings['img_thumb_height'] = '100';
		$fldSettings['img_output_mode'] = '0';
		$fldSettings['img_attributes_image'] = '';
		$fldSettings['img_attributes_delete'] = '';
		$fldSettings['img_attributes_alt_text'] = '';
		$fldSettings['img_attributes_title'] = '';
		$fldSettings['img_show_alt_tag'] = '1';
		$fldSettings['img_show_title'] = '1';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'image';
		$data['title'] = 'Image';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_IMAGE;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Multi Line Editor 'Main Article' field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['mle_num_rows'] = '';
		$fldSettings['mle_num_cols'] = '';
		$fldSettings['mle_width'] = '';
		$fldSettings['mle_height'] = '';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'main';
		$data['title'] = 'Article main';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_MULTILINEEDITOR;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Info text 'Main information' field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['inf_text'] = 'SHOW or HIDE the read more function automatically. If no content is submitted in the field below (main article), there will be NO \'read more\' button in Joomla blog layouts. This is realised by templating. Please see the template for more details.';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'main_info';
		$data['title'] = '';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_INFOTEXT;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Multi Line Text 'Article Intro' field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['mlt_num_rows'] = '';
		$fldSettings['mlt_num_cols'] = '';
		$fldSettings['mlt_attributes'] = '';
		$fldSettings['mlt_max_num_chars'] = '250';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'intro';
		$data['title'] = 'Article intro';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_MULTILINETEXT;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Info text 'Form information' field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['inf_text'] = 'We have created this form example so you can experience some of the powerful features of Form2Content Lite. The content of this form is combined with the template to create your Joomla article.';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'form_info';
		$data['title'] = '';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_INFOTEXT;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
	}
	
	private function createAllFieldsExampleFields(&$fieldIds, $contentTypeId)
	{
		// Insert fields in reversed order to get the correct ordering.
		// Single select field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['ssl_display_mode'] = '0';
		$fldSettings['ssl_show_empty_choice_text'] = '0';
		$fldSettings['ssl_empty_choice_text'] = '';
		$fldSettings['ssl_attributes'] = '';
		$fldSettings['ssl_options'] = array();
		$fldSettings['ssl_options']['1'] = 'Select 1';
		$fldSettings['ssl_options']['2'] = 'Select 2';
		$fldSettings['ssl_options']['3'] = 'Select 3';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'singleselect';
		$data['title'] = 'Single select list (radio buttons/drop-down)';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_SINGLESELECTLIST;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 				
		
		// Single line text field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['slt_size'] = '';
		$fldSettings['slt_max_length'] = '';
		$fldSettings['slt_attributes'] = '';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'textbox';
		$data['title'] = 'Single line text (Textbox)';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_SINGLELINE;
		$data['settings'] = $fldSettings;
		
		$fld->save($data);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Multi Line Text field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['mlt_num_rows'] = '';
		$fldSettings['mlt_num_cols'] = '';
		$fldSettings['mlt_attributes'] = 'class="textarea"';
		$fldSettings['mlt_max_num_chars'] = '500';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'textarea';
		$data['title'] = 'Multi-line text (Text area)';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_MULTILINETEXT;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Multi Line Editor field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['mle_num_rows'] = '';
		$fldSettings['mle_num_cols'] = '';
		$fldSettings['mle_width'] = '100%';
		$fldSettings['mle_height'] = '200px';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'editor';
		$data['title'] = 'Multi-line text (editor)';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_MULTILINEEDITOR;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Multi Select List field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['msl_attributes'] = '';
		$fldSettings['msl_pre_list_tag'] = '<span class="comma_list">';
		$fldSettings['msl_post_list_tag'] = '</span>';
		$fldSettings['msl_pre_element_tag'] = '';
		$fldSettings['msl_post_element_tag'] = ',&nbsp;';
		$fldSettings['msl_options']['1'] = 'Option 1';
		$fldSettings['msl_options']['2'] = 'Option 2';
		$fldSettings['msl_options']['3'] = 'Option 3';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'multiselectlist';
		$data['title'] = 'Multi select list';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_MULTISELECTLIST;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 

		// Info text field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['inf_text'] = 'Add anything you like in the submisison form. Good to let users know what you want!';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'info';
		$data['title'] = '';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_INFOTEXT;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Image field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['img_max_width'] = '600';
		$fldSettings['img_max_height'] = '600';
		$fldSettings['img_thumb_width'] = '120';
		$fldSettings['img_thumb_height'] = '120';
		$fldSettings['img_output_mode'] = '0';
		$fldSettings['img_attributes_image'] = '';
		$fldSettings['img_attributes_delete'] = '';
		$fldSettings['img_attributes_alt_text'] = '';
		$fldSettings['img_attributes_title'] = '';
		$fldSettings['img_show_alt_tag'] = '1';
		$fldSettings['img_show_title'] = '1';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'image';
		$data['title'] = 'Image';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_IMAGE;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Iframe field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['ifr_attributes_iframe'] = 'class="iframe"';
		$fldSettings['ifr_attributes_width'] = '';
		$fldSettings['ifr_attributes_height'] = '';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'iframe';
		$data['title'] = 'Iframe';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_IFRAME;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 

		// Hyperlink field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['linkOutputMode'] = '0';
		$fldSettings['lnk_attributes_link'] = '';
		$fldSettings['lnk_attributes_display_as'] = '';
		$fldSettings['lnk_attributes_title'] = '';
		$fldSettings['lnk_attributes_target'] = '';
		$fldSettings['lnk_show_display_as'] = '1';
		$fldSettings['lnk_show_title'] = '1';
		$fldSettings['lnk_show_target'] = '1';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'hyperlink';
		$data['title'] = 'Hyperlink (URL)';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_HYPERLINK;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 

		// Geocoder field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['gcd_show_map'] = '1';
		$fldSettings['gcd_map_width'] = '';
		$fldSettings['gcd_map_height'] = '';
		$fldSettings['gcd_map_lat'] = '';
		$fldSettings['gcd_map_lon'] = '1';
		$fldSettings['gcd_map_zoom'] = '1';
		$fldSettings['gcd_map_type'] = 'ROADMAP';
		$fldSettings['gcd_attributes_address'] = '';
		$fldSettings['gcd_attributes_lookup_lat_lon'] = '';
		$fldSettings['gcd_attributes_clear_results'] = '';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'geocoder';
		$data['title'] = 'Geo coder';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_GEOCODER;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 

		// File field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['ful_output_mode'] = '0';
		$fldSettings['tblFileWhiteList_0key'] = 'pdf';
		$fldSettings['ful_attributes_upload'] = 'class="file"';
		$fldSettings['ful_attributes_delete'] = '';
		$fldSettings['ful_max_file_size'] = '';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'file';
		$data['title'] = 'File Upload';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_FILE;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 

		// E-mail field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['eml_attributes_email'] = '';
		$fldSettings['eml_attributes_display_as'] = '';
		$fldSettings['eml_show_display_as'] = '1';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'email';
		$data['title'] = 'E-mail';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_EMAIL;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 

		// Display List field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['dsp_output_mode'] = '0';
		$fldSettings['dsp_attributes_table'] = 'class="list"';
		$fldSettings['dsp_attributes_tr'] = '';
		$fldSettings['dsp_attributes_th'] = '';
		$fldSettings['dsp_attributes_td'] = '';
		$fldSettings['dsp_attributes_item_text'] = '';
		$fldSettings['dsp_attributes_add_button'] = '';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'displaylist';
		$data['title'] = 'Display List';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_DISPLAYLIST;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 

		// Date field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['chk_attributes'] = 'class="date"';
				
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'date';
		$data['title'] = 'Date Picker';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_DATEPICKER;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Database Lookup field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['dbl_display_mode'] = '0';
		$fldSettings['dbl_show_empty_choice_text'] = '1';		
		$fldSettings['dbl_empty_choice_text'] = 'Please select an article';
		$fldSettings['dbl_query'] = 'SELECT id, title FROM #__content ORDER BY title';
		$fldSettings['dbl_attributes'] = 'class="database"';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'database';
		$data['title'] = 'Database lookup';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_DATABASE_LOOKUP;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 
		
		// Checkbox field
		$fld = new Form2ContentModelProjectField();
		
		$fldSettings = array();
		$fldSettings['requiredfield'] = 0;
		$fldSettings['error_message_required'] = '';
		$fldSettings['chk_attributes'] = 'class="checkbox"';
		
		$data = array();
		$data['projectid'] = $contentTypeId;
		$data['fieldname'] = 'checkbox';
		$data['title'] = 'Checkbox';
		$data['description'] = '';
		$data['frontvisible'] = 1;
		$data['fieldtypeid'] = F2C_FIELDTYPE_CHECKBOX;
		$data['settings'] = $fldSettings;
		
		$fld->save($data, false);
		$fieldIds[$contentTypeId][$data['fieldname']] = $fld->getState('projectfield.id'); 		
	}

	private function createSimpleArticleExampleForm($contentTypeId, $fieldIds, $rules, $attribs, $metadata)
	{
		$user	=	JFactory::getUser();
		$form 	= new Form2ContentModelForm();
		
		$data = array();
		$data['id'] = 0;
		$data['projectid'] = $contentTypeId;
		$data['title'] = 'Demo simple article example';
		$data['alias'] = '';
		$data['intro_template'] = 'intro_template_simple_article_example.tpl';
		$data['main_template'] = 'main_template_simple_article_example.tpl';
		$data['state'] = 0;
		$data['catid'] = self::_getDefaultCategory();
		$data['created'] = '';
		$data['created_by'] = $user->id;
		$data['created_by_alias'] = '';
		$data['modified'] = '';
		$data['publish_up'] = '';
		$data['publish_down'] = '';
		$data['metakey'] = 'This is the default meta keyword field . Change this in the content type configuration';
		$data['metadesc'] = 'This is the default meta description. Change this in the content type configuration';
		$data['access'] = 1;
		$data['language'] = '*';
		$data['featured'] = 0;
		$data['rules'] = $rules;
		$data['attribs'] = $attribs;
		$data['metadata'] = $metadata;
		
		$form->save($data, true);
		
		$fieldDefinitions = $form->loadFieldDefinitions($contentTypeId);
		
		$fieldData = array();
		
		foreach($fieldDefinitions as $fieldDefinition)
		{
			$f2cFieldData 								= new F2cFieldData();
			$f2cFieldData->id 							= $fieldDefinition->id;
			$f2cFieldData->fieldtypeid 					= $fieldDefinition->fieldtypeid;
			$f2cFieldData->title 						= $fieldDefinition->title;
			$f2cFieldData->fieldname 					= $fieldDefinition->fieldname;
			$f2cFieldData->ordering 					= $fieldDefinition->ordering;
			$f2cFieldData->frontvisible 				= $fieldDefinition->frontvisible;				
			$f2cFieldData->settings 					= $fieldDefinition->settings;
			$f2cFieldData->projectid					= $fieldDefinition->projectid;
			$f2cFieldData->internal['fieldcontentid'] 	= null;
			
			switch($f2cFieldData->fieldname)
			{
				case 'joomla_information':
					$f2cFieldData->values['VALUE'] = '1';
					break;
					case 'reference':
					$f2cFieldData->values['VALUE'] = 'Open Source Design';
					break;
				case 'image':
					$f2cFieldData->internal['method'] 			= 'copy';
					$f2cFieldData->internal['delete'] 			= null;
					$f2cFieldData->internal['currentfilename']	= null;
					$f2cFieldData->internal['imagelocation']	= JPATH_COMPONENT_ADMINISTRATOR.DS.'sample_data'.DS.'osd_logo.gif';
					$f2cFieldData->internal['thumblocation']	= JPATH_COMPONENT_ADMINISTRATOR.DS.'sample_data'.DS.'osd_logo_thumb.gif';					
					$f2cFieldData->values['FILENAME']			= 'osd_logo.gif';
					$f2cFieldData->values['ALT']				= 'Logo Open Source Design';
					$f2cFieldData->values['TITLE']				= 'Logo Open Source Design';	
					$f2cFieldData->values['WIDTH']				= 230;
					$f2cFieldData->values['HEIGHT']				= 122;
					$f2cFieldData->values['WIDTH_THUMBNAIL']	= 150;
					$f2cFieldData->values['HEIGHT_THUMBNAIL']	= 79;
					break;
				case 'main':
					$f2cFieldData->values['VALUE'] = '<p>This is the <strong>main text</strong> of your article with <em>mark-up</em></p>';
					break;
				case 'intro':
					$f2cFieldData->values['VALUE'] = 'This is the intro text of your article';
					break;
			}
			
			$fieldData[$f2cFieldData->fieldname] = $f2cFieldData;
		}

		$storage = new F2cStorage($form->getState('form.id'), true);		
		$storage->preparedData = $fieldData;
		$storage->storeFields($fieldDefinitions);
	}

	private function createAllFieldsExampleForm($contentTypeId, $fieldIds, $rules, $attribs, $metadata)
	{
		$user	= JFactory::getUser();
		$form 	= new Form2ContentModelForm();
		
		$data = array();
		$data['id'] = 0;
		$data['projectid'] = $contentTypeId;
		$data['title'] = 'Demo all fields submission';
		$data['alias'] = '';
		$data['intro_template'] = 'intro_template_all_fields_example.tpl';
		$data['main_template'] = 'main_template_all_fields_example.tpl';
		$data['state'] = 0;
		$data['catid'] = self::_getDefaultCategory();
		$data['created'] = '';
		$data['created_by'] = $user->id;
		$data['created_by_alias'] = '';
		$data['modified'] = '';
		$data['publish_up'] = '';
		$data['publish_down'] = '';
		$data['metakey'] = '';
		$data['metadesc'] = '';
		$data['access'] = 1;
		$data['language'] = '*';
		$data['featured'] = 0;
		$data['rules'] = $rules;
		$data['attribs'] = $attribs;
		$data['metadata'] = $metadata;
		
		$form->save($data, true);

		$fieldDefinitions = $form->loadFieldDefinitions($contentTypeId);
		
		$fieldData = array();
		
		foreach($fieldDefinitions as $fieldDefinition)
		{
			$f2cFieldData 								= new F2cFieldData();
			$f2cFieldData->id 							= $fieldDefinition->id;
			$f2cFieldData->fieldtypeid 					= $fieldDefinition->fieldtypeid;
			$f2cFieldData->title 						= $fieldDefinition->title;
			$f2cFieldData->fieldname 					= $fieldDefinition->fieldname;
			$f2cFieldData->ordering 					= $fieldDefinition->ordering;
			$f2cFieldData->frontvisible 				= $fieldDefinition->frontvisible;				
			$f2cFieldData->settings 					= $fieldDefinition->settings;
			$f2cFieldData->projectid					= $fieldDefinition->projectid;
			$f2cFieldData->internal['fieldcontentid'] 	= null;
			
			switch($f2cFieldData->fieldname)
			{
				case 'singleselect':
					$f2cFieldData->values['VALUE'] = '2';
					break;
				case 'textbox':
					$f2cFieldData->values['VALUE'] = 'Just a line of text....';
					break;
				case 'textarea':
					$f2cFieldData->values['VALUE'] = 'This is text without mark-up.';
					break;
				case 'editor':
					$f2cFieldData->values['VALUE'] = 'This is the text editor field.';
					break;
				case 'multiselectlist':
					$f2cFieldData->values['VALUE'] = array();
					$f2cFieldData->values['VALUE'][] = '2';
					$f2cFieldData->values['VALUE'][] = '3';
					break;
				case 'image':
					$f2cFieldData->internal['method'] 			= 'copy';
					$f2cFieldData->internal['delete'] 			= null;
					$f2cFieldData->internal['currentfilename']	= null;
					$f2cFieldData->internal['imagelocation']	= JPATH_COMPONENT_ADMINISTRATOR.DS.'sample_data'.DS.'osd_logo.gif';
					$f2cFieldData->internal['thumblocation']	= JPATH_COMPONENT_ADMINISTRATOR.DS.'sample_data'.DS.'osd_logo_thumb.gif';					
					$f2cFieldData->values['FILENAME']			= 'osd_logo.gif';
					$f2cFieldData->values['ALT']				= 'Logo Open Source Design';
					$f2cFieldData->values['TITLE']				= 'Logo Open Source Design';	
					$f2cFieldData->values['WIDTH']				= 230;
					$f2cFieldData->values['HEIGHT']				= 122;
					$f2cFieldData->values['WIDTH_THUMBNAIL']	= 150;
					$f2cFieldData->values['HEIGHT_THUMBNAIL']	= 79;
					break;
				case 'iframe':
					$f2cFieldData->values['URL'] = 'http://www.form2content.com';
					$f2cFieldData->values['WIDTH'] = '400';			
					$f2cFieldData->values['HEIGHT'] = '400';
					break;		
				case 'hyperlink':
					$f2cFieldData->values['URL'] = 'http://www.form2content.com';
					$f2cFieldData->values['DISPLAY_AS'] = '';			
					$f2cFieldData->values['TITLE'] = '';
					$f2cFieldData->values['TARGET'] = '_blank';
					break;
				case 'geocoder':
					$f2cFieldData->values['ADDRESS'] = 'Ceresstraat 24, Breda, Netherlands';
					$f2cFieldData->values['LAT'] = '51.5943875';
					$f2cFieldData->values['LON'] = '4.7891219';
					break;
			}
			
			$fieldData[$f2cFieldData->fieldname] = $f2cFieldData;
		}
/*		
		$fieldData['t'.$fieldIds['checkbox']] 						= '1';
		$fieldData['t'.$fieldIds['database']] 						= self::_getDefaultArticle();
		$fieldData['t'.$fieldIds['date']] 							= '2011-03-19T00:00:00Z';		
		$fieldData['t'.$fieldIds['email']]							= 'info@opensourcedesign.nl';
		$fieldData['t'.$fieldIds['email'].'_display'] 				= 'Open Source Design';
		$fieldData['t'.$fieldIds['file'].'_method'] 				= 'copy';
		$fieldData['t'.$fieldIds['file'].'_del'] 					= null;
		$fieldData['t'.$fieldIds['file'].'_location'] 				= JPATH_COMPONENT_ADMINISTRATOR.DS.'sample_data'.DS.'Flyer_Joomla_dagen2010.pdf';
		$fieldData['t'.$fieldIds['file'].'_filename'] 				= 'Flyer_Joomla_dagen2010.pdf';					
*/
		$elementName 										= 't'.$fieldIds['displaylist'];
		$rowKey[] 											= array();
		$fieldData[$elementName.'_fieldid'] 				= '';
		$fieldData[$elementName.'_row'.$elementName.'0'] 	= 'Red';
		$rowKey[]			 								= $elementName.'0';
		$fieldData[$elementName.'_row'.$elementName.'1'] 	= 'Green';
		$rowKey[] 											= $elementName.'1';
		$fieldData[$elementName.'_row'.$elementName.'2'] 	= 'Blue';
		$rowKey[] 											= $elementName.'2';		
		$fieldData[$elementName.'_rowkey'] 					= $rowKey;
		
		$storage = new F2cStorage($form->getState('form.id'), true);		
		$storage->preparedData = $fieldData;
		$storage->storeFields($fieldDefinitions);
	}
	
	function _getDefaultCategory()
	{
		$db 	= JFactory::getDbo();		
		$query	= $db->getQuery(true);
		
		$query->select('id');
		$query->from('#__categories');
		$query->where('extension = \'com_content\' AND published = 1');
		
		$db->setQuery($query->__toString(), 0, 1);
		
		return $db->loadResult();
	}
	
	function _getDefaultArticle()
	{
		$db 	= JFactory::getDbo();		
		$query	= $db->getQuery(true);
		
		$query->select('id');
		$query->from('#__content');
		$query->where('state = 1');
		
		$db->setQuery($query->__toString(), 0, 1);
		
		return $db->loadResult();		
	}	
}
?>