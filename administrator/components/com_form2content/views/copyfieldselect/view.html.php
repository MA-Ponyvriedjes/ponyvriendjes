<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_form2content'.DS.'models'.DS.'form.php');

class Form2ContentViewCopyFieldSelect extends JView
{
	protected $contentTypeList;	

	function display($tpl = null)
	{
		$this->addToolbar();

		$model = new Form2ContentModelForm();
		$this->contentTypeList = $model->getContentTypeSelectList(false);		

		parent::display($tpl);				
	}
	
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_FORM2CONTENT_CONTENTTYPE_FIELDS_MANAGER').': '. JText::_('COM_FORM2CONTENT_COPY_FIELD'));
		JToolBarHelper::custom('projectfield.copy','forward','forward',JText::_('COM_FORM2CONTENT_NEXT'), false);
		JToolBarHelper::cancel('projectfield.cancel', 'JTOOLBAR_CANCEL');	
	}	
}

?>