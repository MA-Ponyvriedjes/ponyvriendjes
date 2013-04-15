<?php
defined('_JEXEC') or die('Restricted acccess');

jimport('joomla.application.component.controllerform');

class Form2ContentControllerTranslation extends JControllerForm
{
	public function __construct($config = array())
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin')) 
		{
			return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		parent::__construct($config);
	}
	
	public function add()
	{
		if(parent::add())
		{
			$this->redirect .= '&reference_id='.JRequest::getInt('reference_id').'&lang_code='.urlencode(JRequest::getString('lang_code'));
			return true;
		}
	 
		return false;
	}
	
	public function edit($key = null, $urlVar = null)
	{
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		$model	= $this->getModel();
		$table	= $model->getTable();
		
		// Determine the name of the primary key for the data.
		if (empty($key)) 
		{
			$key = $table->getKeyName();
		}

		// The urlVar may be different from the primary key to avoid data collisions.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		$recordId = count($cid) ? $cid[0] : JRequest::getInt($urlVar);

		// Structure of an untranslated field: R<reference id>L<language code>
		$posR = strpos($recordId, 'R');
		
		if($posR !== false && $posR == 0)
		{
			// This is the reference Id of an untranslated field
			$posL 			= strpos($recordId, 'L');
			$referenceId 	= (int)substr($recordId, 1, $posL - 1);
			$languageCode 	= substr($recordId, $posL + 1);
			
			$this->setRedirect('index.php?option=com_form2content&task=translation.add&reference_id='.$referenceId.'&lang_code='.urlencode($languageCode));
			
			return true;
		}
		
		if(parent::edit($key, $urlVar))
		{
			$this->redirect .= '&reference_id='.JRequest::getInt('reference_id');
			return true;
		}
		
		return false;		
	}
}
?>