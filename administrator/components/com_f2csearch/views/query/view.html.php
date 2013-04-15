<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');


class F2CSearchViewQuery extends JView
{
	
	function display($tpl = null)
	{
		
		$document = JFactory::getDocument();
		$document->addStyleSheet('components/com_f2csearch/assets/single.css');
		$document->addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js');
		$document->addScript('components/com_f2csearch/assets/query.js');
		//get the hello
		$query		=& $this->get('Data');
		$isNew		= ($query->query_id < 1);
		
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Query' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}
		
		$optionLists = array();
		$this->loadHelper('f2csearch');
		
		$optionLists['categoryList'] = F2csearchHelper::getCategoryList();
		$optionLists['authorList'] = F2csearchHelper::getAuthorList();
		$optionLists['f2cprojectList'] = F2csearchHelper::getF2cprojectList();
		$optionLists['f2cfieldList'] = F2csearchHelper::getF2cfieldList();
		$optionLists['userprofileList'] = F2csearchHelper::getUserprofileList();
		$optionLists['userratingList'] = array(
			array('id' => '2', 'title' => '+'),
			array('id' => '1', 'title' => '+-'),
			array('id' => '0', 'title' => '-'),
			
		);
		$optionLists['orderingtypes'] = array(
			array('id' => '', 'title' => '+'),
			array('id' => '1', 'title' => '+-'),
			array('id' => '0', 'title' => '-'),	
		);
		$optionLists['subquerytypes'] = array(
			array('value' => 'f2c-formfield', 'text' => 'F2C Form'),
			array('value' => 'user-profile', 'text' => 'Joomla User Profile'),
			array('value' => 'user-rating', 'text' => 'User Rating'),
			array('value' => 'f2c-author', 'text' => 'Authors'),
			array('value' => 'category', 'text' => 'Category')
			
			
		);
		$optionLists['filteroperators'] = array(
			array('value' => '=', 'text' => 'is equivalent to'),
			array('value' => '<>', 'text' => 'is not equivalant to'),
			array('value' => 'LIKE', 'text' => 'has phrase')
			
		);
		
		$filtersData =& $this->get('Data','filters');
		
		$this->assignRef('query',$query);
		$this->assignRef('filters',$filtersData);
		$this->assignRef('optionLists',$optionLists);
		
		
		parent::display('filters');
	}
}
