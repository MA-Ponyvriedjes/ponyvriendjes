<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'project.php');
require_once(JPATH_COMPONENT_SITE.DS.'renderer.form2content.php');
require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'form2content.php');

jimport('joomla.application.component.view');
jimport('joomla.language.helper');

class Form2ContentViewForm extends JView
{
	protected $form;
	protected $item;
	protected $fields;
	protected $state;
	protected $jArticle;
	protected $jsScripts = array();
	protected $renderer;
	protected $nullDate;
	protected $pageTitle;
	protected $contentTypeSettings;
	protected $renderCaptcha = '';
	protected $submitForm = '';
	protected $itemId;
	protected $dateFormat = '';
	protected $params;
	private $activeMenu;
	private $menuParms;
	private $f2cConfig;
	
	function display($tpl = null)
	{
		$app				=& JFactory::getApplication();
		$menu				=& JSite::getMenu();
		$this->activeMenu	= $menu->getActive();
		$model 				=& $this->getModel();		
		$db					= $this->get('Dbo');
		$this->f2cConfig	=& F2cFactory::getConfig();
		$this->state		= $this->get('State');
		$this->params		= $app->getParams();
		$this->nullDate		= $db->getNullDate();		
		$this->dateFormat	= $this->f2cConfig->get('date_format');
		$this->itemId		= JRequest::getInt('Itemid');	
		$this->menuParms	= F2cMenuHelper::getParameters($this->itemId);

		// Feed the model with the parameters
		$model->contentTypeId = (int)$this->activeMenu->params->get('contenttypeid');

		if($this->activeMenu->params->get('editmode') != '')
		{
			if((int)$this->activeMenu->params->get('editmode') == 1)
			{
				// edit existing form or create a new one
				$formId = $model->getDefaultArticleId((int)$this->activeMenu->params->get('contenttypeid'));
			}
			else
			{
				$formId = 0;
			}
			
			// Initialize the state -> For the first getState call,
			// the internal data will be overwritten
			$dummy = $model->getState($this->getName().'.id');
			$model->setState($this->getName().'.id', $formId);
			
			$ids[]	= $formId;
			$app->setUserState('com_form2content.edit.form.id', $ids);			
		}		
		
		$this->item		= $this->get('Item');		
		$this->form		= $this->get('Form');		
		$this->fields	= $model->loadFieldData($this->item->id, $this->item->projectid);		
		$this->canDo	= Form2ContentHelper::getActions($this->state->get('filter.category_id'));
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// set the state to indicate this is a new form or an existing one
		$app->setUserState('com_form2content.edit.form.new', $this->item->id ? false : true);
		
		$modelContentType = new Form2ContentModelProject();
		$contentType = $modelContentType->getItem($this->item->projectid);
		$this->contentTypeSettings = new JRegistry();
		$this->contentTypeSettings->loadArray($contentType->settings);
		
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true) . '/media/com_form2content/css/f2cfields.css');
		$document->addStyleSheet(JURI::root(true) . '/media/com_form2content/css/f2cfrontend.css');
		
		$this->prepareForm($contentType);
		$this->addToolbar($contentType);

		// Set the page title
		$document->setTitle(HtmlHelper::getPageTitle($this->params->get('page_title', '')));
		
		parent::display($tpl);		
	}	
	
	protected function addToolbar($contentType)
	{
		$isNew 	= ($this->item->id == 0);
		$text 	= $isNew ? JText::_('COM_FORM2CONTENT_NEW') : JText::_('COM_FORM2CONTENT_EDIT');
		
		if($this->menuParms->get('view') == 'forms')
		{
			// coming from Article Manager menu entry
			$formTitle = $this->contentTypeSettings->get('article_caption') ? $this->contentTypeSettings->get('article_caption') : JText::_('COM_FORM2CONTENT_FORM');
		}
		else
		{
			// coming from single Article menu entry
			if($this->params->get('show_page_heading', 1))
			{
				$formTitle = $this->escape($this->params->get('page_heading'));
			}
			else
			{
				$formTitle = $this->contentTypeSettings->get('article_caption') ? $this->contentTypeSettings->get('article_caption') : JText::_('COM_FORM2CONTENT_FORM');
			}
		}
						
		$this->pageTitle = $formTitle .': <small><small>[ '.$text.' ]</small></small>';
	}
	
	private function prepareForm($contentType)
	{
		$db 							=& JFactory::getDBO();		
		$editor 						=& JFactory::getEditor();
		$lang 							= JFactory::getLanguage();
		$this->jsScripts['validation']	= '';
		$this->jsScripts['fieldval']	= '';
		$this->jsScripts['editorinit']	= '';
		$this->jsScripts['editorsave']	= '';
		
		$this->form->setFieldAttribute('id', 'label', Jtext::_('COM_FORM2CONTENT_ID'));
		$this->form->setFieldAttribute('id', 'description', '');
		
		$this->overrideFieldLabel('title', $this->contentTypeSettings->get('title_caption'));
		$this->overrideFieldLabel('alias', $this->contentTypeSettings->get('title_alias_caption'));
		$this->overrideFieldLabel('metakey', $this->contentTypeSettings->get('metakey_caption'));
		$this->overrideFieldLabel('metadesc', $this->contentTypeSettings->get('metadesc_caption'));
		$this->overrideFieldLabel('catid', $this->contentTypeSettings->get('category_caption'));
		$this->overrideFieldLabel('created_by', $this->contentTypeSettings->get('author_caption'));
		$this->overrideFieldLabel('created_by_alias', $this->contentTypeSettings->get('author_alias_caption'));
		$this->overrideFieldLabel('state', $this->contentTypeSettings->get('state_caption'));
		$this->overrideFieldLabel('featured', $this->contentTypeSettings->get('featured_caption'));
		$this->overrideFieldLabel('access', $this->contentTypeSettings->get('access_level_caption'));
		$this->overrideFieldLabel('language', $this->contentTypeSettings->get('language_caption'));
		
	  	$translatedFields 		= $this->getModel()->loadFieldTranslations($this->item->projectid, $lang->getTag());	  	
	  	$translatedDateFormat 	= F2cDateTimeHelper::getTranslatedDateFormat();
		
		$this->jsScripts['validation'] .= 'var arrValidation=new Array;';
				
		$validationCounter = 0;

		$this->jsScripts['editorinit'] .= 'function F2C_GetEditorText(id) { switch(id) { ';
		
		if(count($this->fields))
		{
			foreach($this->fields as $field)
			{
				if($field->frontvisible)
				{
					switch($field->fieldtypeid)
					{
						case F2C_FIELDTYPE_MULTILINEEDITOR:
							$elmEditor = 't' . $field->id; // elementname
							$this->jsScripts['editorinit'] .= "case '".$elmEditor."': return ".$editor->getContent($elmEditor)."\n";
							$this->jsScripts['editorsave'] .= $editor->save($elmEditor);
							break;
						case F2C_FIELDTYPE_FILE:
							$this->jsScripts['fieldval'] .= F2C_Validation::createFileUploadClientSideScript($field);
							break;
						case F2C_FIELDTYPE_DATEPICKER:
							$this->jsScripts['fieldval'] .= F2C_Validation::createDatePickerValidation('t'.$field->id, $field->title, $this->dateFormat, $translatedDateFormat);
							break;
					}
				
					if($field->settings->get('requiredfield'))
					{
						$requiredMsg = ($field->settings->get('error_message_required')) ? $field->settings->get('error_message_required') : sprintf(JText::_('COM_FORM2CONTENT_ERROR_FIELD_X_REQUIRED', true), $field->title);
						$this->jsScripts['validation'] .= 'arrValidation['.$validationCounter++.']=new Array('.$field->id.','.$field->fieldtypeid.',\''.addslashes($requiredMsg).'\');';
					}
				}
			}
		}
						
		// Add validation scripts for the datefields
		if($this->contentTypeSettings->get('date_created_front_end'))
		{
			$label = JText::_($this->form->getFieldAttribute('created', 'label'), true);
			$this->jsScripts['fieldval'] .= F2C_Validation::createDatePickerValidation('jform_created', $label, $this->dateFormat, $translatedDateFormat, false);
		}

		if($this->contentTypeSettings->get('frontend_pubsel'))
		{
			$label = JText::_($this->form->getFieldAttribute('publish_up', 'label'), true);
			$this->jsScripts['fieldval'] .= F2C_Validation::createDatePickerValidation('jform_publish_up', $label, $this->dateFormat, $translatedDateFormat, false);
			
			$label = JText::_($this->form->getFieldAttribute('publish_down', 'label'), true);
			$this->jsScripts['fieldval'] .= F2C_Validation::createDatePickerValidation('jform_publish_down', $label, $this->dateFormat, $translatedDateFormat, false);
		}
		
		$this->jsScripts['editorinit'] .= '}}';
		
		$this->renderer = new F2C_Renderer($this->item->id, $translatedFields, $contentType->settings);
		
		// Handle the captcha
		if($this->contentTypeSettings->get('captcha_front_end'))
		{
			if(!function_exists('recaptcha_get_html'))
			{
				require_once(JPATH_COMPONENT_SITE.DS.'libraries'.DS.'recaptcha'.DS.'recaptchalib.php');
			}
			
			$this->renderCaptcha .= '<tr><td colspan="2"><br/>'.recaptcha_get_html($this->f2cConfig->get('recaptcha_public_key')).'</td></tr>';
			$this->submitForm = 'F2C_CheckCaptcha(task, \''.JText::_('COM_FORM2CONTENT_ERROR_CAPTCHA_INCORRECT', true).'\','.$this->itemId.'); return false;';
		}
		else
		{
			$this->submitForm = 'Joomla.submitform(task, document.getElementById(\'item-form\'));';
		}		
	}
	
	private function overrideFieldLabel($field, $caption)
	{
		// only override the field label when a value has been provided
		if($caption)
		{
			$this->form->setFieldAttribute($field, 'label', $caption);
		}
	}
	
	protected function renderFormTemplate()
	{
		$parser = new F2cParser();
		$varsInTemplate = array();		
		$formVars = array('F2C_ID' => 'F2C_ID', 'F2C_TITLE' => 'F2C_TITLE', 'F2C_TITLE_ALIAS' => 'F2C_TITLE_ALIAS',
						  'F2C_METADESC' => 'F2C_METADESC', 'F2C_METAKEY' => 'F2C_METAKEY',
						  'F2C_CATID' => 'F2C_CATID', 'F2C_CREATED_BY' => 'F2C_CREATED_BY',
						  'F2C_CREATED_BY_ALIAS' => 'F2C_CREATED_BY_ALIAS', 'F2C_ACCESS' => 'F2C_ACCESS',
						  'F2C_INTRO_TEMPLATE' => 'F2C_INTRO_TEMPLATE', 'F2C_MAIN_TEMPLATE' => 'F2C_MAIN_TEMPLATE',
						  'F2C_CREATED' => 'F2C_CREATED', 'F2C_PUBLISH_UP' => 'F2C_PUBLISH_UP',
						  'F2C_PUBLISH_DOWN' => 'F2C_PUBLISH_DOWN', 'F2C_STATE' => 'F2C_STATE',
						  'F2C_LANGUAGE' => 'F2C_LANGUAGE', 'F2C_FEATURED' => 'F2C_FEATURED');
		
		if(count($this->fields))
		{
			foreach($this->fields as $field)
			{
				$formVars[strtoupper($field->fieldname)] = strtoupper($field->fieldname);
			}
		}
		
		if(!$parser->addTemplate($this->contentTypeSettings->get('form_template'), F2C_TEMPLATE_INTRO))
		{
			$this->setError($errorMsgPrefix . $parser->getError());
			return false;				
		}

		$parser->getTemplateVars($formVars, $varsInTemplate);

		// add the buttons
		if($this->item->id == 0)
		{
			$parser->addVar('F2C_BUTTON_CANCEL', '<button type="button" class="f2c_button f2c_cancel" onclick="javascript:Joomla.submitbutton(\'form.cancel\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_CANCEL').'</button>');
		}
		else
		{
			$parser->addVar('F2C_BUTTON_CANCEL', '<button type="button" class="f2c_button f2c_cancel" onclick="javascript:Joomla.submitbutton(\'form.cancel\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_CLOSE').'</button>');
		}
		
		$parser->addVar('F2C_BUTTON_SAVE', '<button type="button" class="f2c_button f2c_save" onclick="javascript:Joomla.submitbutton(\'form.save\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_SAVE').'</button>');
		$parser->addVar('F2C_BUTTON_APPLY', '<button type="button" class="f2c_button f2c_apply" onclick="javascript:Joomla.submitbutton(\'form.apply\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_APPLY').'</button>');
				
		// Add the default form fields
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('id_front_end', 1), 'F2C_ID', 'id');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('title_front_end'), 'F2C_TITLE', 'title');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('title_alias_front_end'), 'F2C_TITLE_ALIAS', 'alias');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('metadesc_front_end'), 'F2C_METADESC', 'metadesc');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('metakey_front_end'), 'F2C_METAKEY', 'metakey');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_catsel'), 'F2C_CATID', 'catid');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('author_front_end'), 'F2C_CREATED_BY', 'created_by');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('author_alias_front_end'), 'F2C_CREATED_BY_ALIAS', 'created_by_alias');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('access_level_front_end'), 'F2C_ACCESS', 'access');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_templsel'), 'F2C_INTRO_TEMPLATE', 'intro_template');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_templsel'), 'F2C_MAIN_TEMPLATE', 'main_template');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('date_created_front_end'), 'F2C_CREATED', 'created');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_pubsel'), 'F2C_PUBLISH_UP', 'publish_up');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_pubsel'), 'F2C_PUBLISH_DOWN', 'publish_down');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('state_front_end'), 'F2C_STATE', 'state');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('language_front_end'), 'F2C_LANGUAGE', 'language');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('featured_front_end'), 'F2C_FEATURED', 'featured');

		$parser->addVar('F2C_CAPTCHA', $this->renderCaptcha);

		// User defined fields
		if(count($this->fields))
		{
			foreach ($this->fields as $field) 
			{
				// skip processing of hidden fields
				//if(!$field->frontvisible) continue;
																		
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
				
				$fieldname = strtoupper($field->fieldname);

				if($field->frontvisible)
				{
					if(array_key_exists($fieldname, $varsInTemplate))
					{
						$parser->addVar($fieldname.'_CAPTION', $this->renderer->renderFieldLabel($field));
						$parser->addVar($fieldname, $this->renderer->renderField($field, $parms));
					}
					else 
					{
						JError::raiseError(501, Jtext::_('COM_FORM2CONTENT_ERROR_F2C').' : '.sprintf(JText::_(COM_FORM2CONTENT_ERROR_TEMPLATE_FIELD_NOT_PRESENT), $fieldname));
					}
				}
				else 
				{
					if(array_key_exists($fieldname, $varsInTemplate))
					{
						JError::raiseError(501, Jtext::_('COM_FORM2CONTENT_ERROR_F2C').' : '.sprintf(JText::_(COM_FORM2CONTENT_ERROR_TEMPLATE_FIELD_PRESENT), $fieldname));
					}
				}
			}
		}
		
		echo $parser->parseIntro();
	}
	
	private function addF2cJoomlaVar($parser, $varsInTemplate, $condition, $title, $field)
	{
		if($condition)
		{
			if(array_key_exists($title, $varsInTemplate))
			{
				$parser->addVar($title.'_CAPTION', $this->form->getLabel($field));
				$parser->addVar($title, $this->form->getInput($field));
			}
			else
			{
				JError::raiseError(501, Jtext::_('COM_FORM2CONTENT_ERROR_F2C').' : '.sprintf(JText::_(COM_FORM2CONTENT_ERROR_TEMPLATE_FIELD_NOT_PRESENT), $title));
			}
		}
		else 
		{			
			// no display in front-end
			if(array_key_exists($title, $varsInTemplate))
			{
				JError::raiseError(501, Jtext::_('COM_FORM2CONTENT_ERROR_F2C').' : '.sprintf(JText::_(COM_FORM2CONTENT_ERROR_TEMPLATE_FIELD_PRESENT), $title));
			}
		}
	}
}
?>