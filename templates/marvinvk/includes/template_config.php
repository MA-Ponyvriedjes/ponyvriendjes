<?php
/**
 * @version		$Id: template_config.php WaseemSadiq $
 * @package		Joomla
 * @subpackage	Templates / basic skeleton template
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');

	$view = JRequest::getVar('view');
	$id = JRequest::getVar('id');
	$option = JRequest::getVar('option');

	$url = clone(JURI::getInstance());
	$user =& JFactory::getUser();
	
	$bodyid = '';
	$bodyclass = $this->language;
	$startOfSession = false;

	if ($option=='com_content'){
		if($view == 'frontpage'){
			$bodyclass .= ' pg-home';
		}
		else if($view == 'category'){
			$bodyclass .= ' pg-category'.$id;
		}
		else {
			$bodyclass .= ' pg-article'.$id;
		}
		$option='';
		$view='';
	}
	else if ($option=='com_form2content'){
		$bodyid = 'cms-active';
		$bodyclass .= ' pg-'.$option.'-'.$view;
	}
	else{
		$bodyclass .= ' pg-'.$option.'-'.$view;
	}

	
	//Keep track of last visited page for redirection purposes
	if($user->guest!=1){
		if(($option!='com_form2content'||$view!='form')){
			setcookie('cms-redirect-form', $url->toString(), time()+60*60);
			JRequest::setVar('cms-redirect-form',$url->toString(),'cookie');
		}
		if($option!='com_form2content'){
			setcookie('cms-redirect-forms', $url->toString(), time()+60*60);
			JRequest::setVar('cms-redirect-forms',$url->toString(),'cookie');
		}
		
		//enable loggedin class
		$bodyclass .= ' loggedin';
		//check for start of a new session
		$session =& JFactory::getSession();
		$session->set('secPage','true'); 
		
		if ($session->has('firstPage')==false) {
			$startOfSession = true;
			$session->set('firstPage','true'); 
		}
	}
?>
