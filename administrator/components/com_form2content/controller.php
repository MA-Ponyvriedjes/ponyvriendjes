<?php
defined('_JEXEC') or die;

// Set some toolbar icons
$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-32-refresh {background-image: url(../media/com_form2content/images/icon-32-refresh.png);}');
$document->addStyleDeclaration('.icon-32-syncorder {background-image: url(../media/com_form2content/images/icon-32-syncorder.png);}');

jimport('joomla.application.component.controller');

class Form2ContentController extends JController
{
	protected $default_view = 'forms';

	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/form2content.php';
		
		// Load the submenu.
		Form2ContentHelperAdmin::addSubmenu(JRequest::getWord('view', ''));

		parent::display();

		return $this;
	}
}