<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_COMPONENT.DS.'models'.DS.'project.php');
require_once(JPATH_COMPONENT_SITE.DS.'renderer.form2content.php');


jimport('joomla.application.component.view');
jimport('joomla.language.helper');

class Form2ContentViewForm extends JView
{
	protected $form;
	protected $item;
	protected $fields;
	protected $fieldsNew;
	protected $fieldValues = array();
	protected $state;
	protected $jArticle;
	protected $jsScripts = array();
	protected $renderer;
	protected $nullDate;
	
	function display($tpl = null)
	{
		$model 				= $this->getModel();
		$db					= $this->get('Dbo');		
		$this->form			= $this->get('Form');
		$this->item			= $this->get('Item');
		$this->state		= $this->get('State');		
		$this->canDo		= Form2ContentHelperAdmin::getActions($this->state->get('filter.category_id'));
		$this->jArticle 	= $model->getJarticle($this->item->id);
		$this->nullDate		= $db->getNullDate();		
		$this->fields		= $model->loadFieldData($this->item->id, $this->item->projectid);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$modelContentType = new Form2ContentModelProject();
		$contentType = $modelContentType->getItem($this->item->projectid);

		$document = JFactory::getDocument();
		$document->addStyleSheet('../media/com_form2content/css/f2cfields.css');
		
		$this->prepareForm($contentType);
		$this->addToolbar($contentType);

		parent::display($tpl);		
	}	
	
	protected function addToolbar($contentType)
	{		
		$isNew = ($this->item->id == 0);
		$formTitle = JText::_('COM_FORM2CONTENT_ARTICLE_MANAGER') . ' : ';
		$formTitle .= ($isNew ? JText::_('COM_FORM2CONTENT_NEW') : JText::_('COM_FORM2CONTENT_EDIT')) . ' ';		
		$formTitle .= $contentType->settings['article_caption'] ? $contentType->settings['article_caption'] : JText::_('COM_FORM2CONTENT_FORM');
		
		JToolBarHelper::title($formTitle);
		JToolBarHelper::save('form.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::apply('form.apply', 'JTOOLBAR_APPLY');
		
		if ($isNew)  
		{
			JToolBarHelper::cancel('form.cancel', 'JTOOLBAR_CANCEL');
		} 
		else 
		{
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel('form.cancel', 'JTOOLBAR_CLOSE');
		}
	}
	
	private function prepareForm($contentType)
	{
		$db 							=& JFactory::getDBO();		
		$editor 						=& JFactory::getEditor();
		$lang 							= JFactory::getLanguage();
		$dateFormat						= F2cFactory::getConfig()->get('date_format');
		$this->jsScripts['validation']	= '';
		$this->jsScripts['fieldval']	= '';
		$this->jsScripts['editorinit']	= '';
		$this->jsScripts['editorsave']	= '';
		
	  	$translatedFields 		= $this->getModel()->loadFieldTranslations($this->item->projectid, $lang->getTag());
		$translatedDateFormat	= F2cDateTimeHelper::getTranslatedDateFormat();
		
		$this->jsScripts['validation'] .= 'var arrValidation=new Array;';
				
		$validationCounter = 0;

		$this->jsScripts['editorinit'] .= 'function F2C_GetEditorText(id) { switch(id) { ';
		
		if(count($this->fields))
		{
			foreach($this->fields as $field)
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
						$this->jsScripts['fieldval'] .= F2C_Validation::createDatePickerValidation('t'.$field->id, $field->title, $dateFormat, $translatedDateFormat);
						break;
				}
				
				if($field->settings->get('requiredfield'))
				{
					$requiredMsg = ($field->settings->get('error_message_required')) ? $field->settings->get('error_message_required') : sprintf(JText::_('COM_FORM2CONTENT_ERROR_FIELD_X_REQUIRED', true), $field->title);
					$this->jsScripts['validation'] .= 'arrValidation['.$validationCounter++.']=new Array('.$field->id.','.$field->fieldtypeid.',\''.addslashes($requiredMsg).'\');';
				}
			}
		}
				
		// Add validation scripts for the datefields
		$label = JText::_($this->form->getFieldAttribute('created', 'label'), true);
		$this->jsScripts['fieldval'] .= F2C_Validation::createDatePickerValidation('jform_created', $label, $dateFormat, $translatedDateFormat, false);
		$label = JText::_($this->form->getFieldAttribute('publish_up', 'label'), true);
		$this->jsScripts['fieldval'] .= F2C_Validation::createDatePickerValidation('jform_publish_up', $label, $dateFormat, $translatedDateFormat, false);
		$label = JText::_($this->form->getFieldAttribute('publish_down', 'label'), true);
		$this->jsScripts['fieldval'] .= F2C_Validation::createDatePickerValidation('jform_publish_down', $label, $dateFormat, $translatedDateFormat, false);
		
		$this->jsScripts['editorinit'] .= '}}';		
		$this->renderer = new F2C_Renderer($this->item->id, $translatedFields, $contentType->settings);		
	}
}
?>