<?php defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

require_once( JPATH_COMPONENT.DS.'helpers'.DS.'f2csearch.php' );

class F2CSearchControllerQuery extends JController
{
		/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
	    parent::__construct();
	 	//;
	    // Register Extra tasks
	    //$this->registerTask( 'add', 'edit' );
	}
	
	function edit()
	{
		
		$view = & $this->getView( 'query', 'html' );
		
		$view->setModel( $this->getModel( 'filters' ));
		
		JRequest::setVar('view', 'query');

		$this->display();
	}
	
	function add()
	{
		
		
		$view = & $this->getView( 'query', 'html' );
		
		$view->setModel( $this->getModel( 'filters' ));
		JRequest::setVar('view', 'query');

		$this->display();
	}
	
	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('query');
	
		if ($model->store($post)) {
			$msg = JText::_( 'Query Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Query' );
		}
		$filterModel = $this->getModel('filters');
		$filterModel->store();
			
	
		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_f2csearch';
		$this->setRedirect($link, $msg);
	}
	

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('query');
		
		F2csearchHelper::setReferencialIds();
		

		$filterModel = $this->getModel('filters');
		$filterModel->delete();
		

		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Querys Could not be Deleted' );
		} else {
			$msg = JText::_( 'Query(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_f2csearch', $msg );
	}

	function publish()
	{
		
		$model = $this->getModel('query');
		JRequest::setVar('publish',1);
		if(!$model->publish()) {
			$msg = JText::_( 'Error: One or More Querys Could not be Published' );
		} else {
			$msg = JText::_( 'Query(s) Published' );
		}

		$this->setRedirect( 'index.php?option=com_f2csearch', $msg );
	}

	function unpublish()
	{
		
		$model = $this->getModel('query');
		JRequest::setVar('publish',0);
		if(!$model->publish()) {
			$msg = JText::_( 'Error: One or More Querys Could not be Unpublished' );
		} else {
			$msg = JText::_( 'Query(s) Published' );
		}

		$this->setRedirect( 'index.php?option=com_f2csearch', $msg );
	}
	
	function display()
	{
		$view = JRequest::getVar('view');
				
		if (!$view) {
			
			JRequest::setVar('view', 'querys');
		}
		
		parent::display();
	}
}