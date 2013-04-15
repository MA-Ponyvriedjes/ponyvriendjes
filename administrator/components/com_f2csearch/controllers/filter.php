<?php defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class F2CSearchControllerFilter extends JController
{
	
	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function delete()
	{
		
		$model = $this->getModel('filters');
		if(!$model->delete()){
			die('deletion failed');
		}		
				
		$link = 'index.php?option=com_f2csearch';
		$this->setRedirect($link, $msg);

	}
	
}