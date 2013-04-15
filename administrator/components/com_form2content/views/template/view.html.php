<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.view');

class Form2ContentViewTemplate extends JView
{
	protected $item;
	protected $id;	

	function display($tpl = null)
	{
		$model = $this->getModel();
	
		$this->item	= $this->get('Item');
		$this->id	= $model->get('id');
		
		$this->addToolbar();
		
		parent::display($tpl);		
	}
	
	protected function addToolbar()
	{
		$formTitle = JText::_('COM_FORM2CONTENT_TEMPLATE_MANAGER') . ' : ';
		$formTitle .= JText::_('COM_FORM2CONTENT_EDIT') . ' ' . JText::_('COM_FORM2CONTENT_TEMPLATE');
		
		JToolBarHelper::title($formTitle);
		JToolBarHelper::save('template.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::apply('template.cancel', 'JTOOLBAR_CANCEL');
	}
}
?>