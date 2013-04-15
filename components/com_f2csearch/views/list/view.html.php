<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * Form2ContentCustomSearch View
 */
class  F2CSearchViewList extends JView
{
	/**
	 *  Form2ContentCustomSearch view display method
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get data from the model
		$app	= JFactory::getApplication();
		
		$menuparams		= $app->getMenu()->getActive()->params;
		
		//$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		$tpl = JRequest::getVar('layout');
		//$result = $this->searchResult;
		$result = &$this->get('Results');
	
		$this->assignRef('articles',$result);
		$this->assignRef('menuparams',$menuparams);
		
		// Display the template
		parent::display($tpl);
		
	}
	
	function cmp($a, $b)
{
		if ($a->publish_up == $b->publish_up) {
			return 0;
		}
		//$a = strtotime($a); 
        //$b = strtotime($b); 
		return ($a->publish_up < $b->publish_up) ? 1 : -1;
	}
}

?>