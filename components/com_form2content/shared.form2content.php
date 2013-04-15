<?php
defined('_JEXEC') or die('Restricted acccess');

require_once(JPATH_SITE.DS.'components'.DS.'com_form2content'.DS.'parser.form2content.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

jimport('joomla.template.template');
jimport('joomla.utilities.date');

defined('F2C_FIELDTYPE_SINGLELINE') 		or define('F2C_FIELDTYPE_SINGLELINE', 1);
defined('F2C_FIELDTYPE_MULTILINETEXT') 		or define('F2C_FIELDTYPE_MULTILINETEXT', 2);
defined('F2C_FIELDTYPE_MULTILINEEDITOR')	or define('F2C_FIELDTYPE_MULTILINEEDITOR', 3);
defined('F2C_FIELDTYPE_CHECKBOX') 			or define('F2C_FIELDTYPE_CHECKBOX', 4);
defined('F2C_FIELDTYPE_SINGLESELECTLIST')	or define('F2C_FIELDTYPE_SINGLESELECTLIST', 5);
defined('F2C_FIELDTYPE_IMAGE') 				or define('F2C_FIELDTYPE_IMAGE', 6);
defined('F2C_FIELDTYPE_IFRAME') 			or define('F2C_FIELDTYPE_IFRAME', 7);
defined('F2C_FIELDTYPE_EMAIL') 				or define('F2C_FIELDTYPE_EMAIL', 8);
defined('F2C_FIELDTYPE_HYPERLINK') 			or define('F2C_FIELDTYPE_HYPERLINK', 9);
defined('F2C_FIELDTYPE_MULTISELECTLIST') 	or define('F2C_FIELDTYPE_MULTISELECTLIST', 10);
defined('F2C_FIELDTYPE_INFOTEXT') 			or define('F2C_FIELDTYPE_INFOTEXT', 11);
defined('F2C_FIELDTYPE_DATEPICKER') 		or define('F2C_FIELDTYPE_DATEPICKER', 12);
defined('F2C_FIELDTYPE_DISPLAYLIST') 		or define('F2C_FIELDTYPE_DISPLAYLIST', 13);
defined('F2C_FIELDTYPE_FILE') 				or define('F2C_FIELDTYPE_FILE', 14);
defined('F2C_FIELDTYPE_DATABASE_LOOKUP')	or define('F2C_FIELDTYPE_DATABASE_LOOKUP', 15);
defined('F2C_FIELDTYPE_GEOCODER')			or define('F2C_FIELDTYPE_GEOCODER', 16);
defined('F2C_FIELDTYPE_DB_LOOKUP_MULTI')	or define('F2C_FIELDTYPE_DB_LOOKUP_MULTI', 17);
defined('F2C_FIELDTYPE_IMAGE_GALLERY')		or define('F2C_FIELDTYPE_IMAGE_GALLERY', 18);

defined('F2C_DEFAULT_THUMBNAIL_WIDTH')		or define('F2C_DEFAULT_THUMBNAIL_WIDTH', 150);
defined('F2C_DEFAULT_THUMBNAIL_HEIGHT')		or define('F2C_DEFAULT_THUMBNAIL_HEIGHT', 150);

class HtmlHelper
{
	function HiddenField($name, $value)
	{
		return '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.htmlspecialchars($value).'">';
	}
	
	function detectUTF8($string)
	{
	    return preg_match('%(?:
	        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
	        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
	        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
	        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
	        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
	        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
	        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
	        )+%xs', 
	    $string);
	}

	function stringHTMLSafe($string)
	{
		if(HtmlHelper::detectUTF8($string))
		{
			$safeString = htmlentities ($string, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			$safeString = htmlentities ($string, ENT_COMPAT);
		}
		
		return $safeString;
	}
	
	function unquoteData($data)
	{
		if(get_magic_quotes_gpc())
		{
			return(stripslashes($data));
		}
		else
		{
			return $data;
		}
	}

	public function renderCalendar($valueFormatted, $valueRaw, $name, $id, $format = '%Y-%m-%d', $attribs = null)
	{
		static $done;

		if ($done === null) 
		{
			$done = array();
		}

		$readonly = isset($attribs['readonly']) && $attribs['readonly'] == 'readonly';
		$disabled = isset($attribs['disabled']) && $attribs['disabled'] == 'disabled';
		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString($attribs);
		}

		if ((!$readonly) && (!$disabled)) {
			// Load the calendar behavior
			JHtml::_('behavior.calendar');
			JHtml::_('behavior.tooltip');

			// Only display the triggers once for each control.
			if (!in_array($id, $done))
			{
				$document = JFactory::getDocument();
				$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
				inputField: "'.$id.'",		// id of the input field
				ifFormat: "'.$format.'",	// format of the input field
				button: "'.$id.'_img",		// trigger for the calendar (button ID)
				align: "Tl",				// alignment (defaults to "Bl")
				singleClick: true,
				firstDay: '.JFactory::getLanguage()->getFirstDay().'
				});});');
				$done[] = $id;
			}
		}

		return '<input type="text" title="'.(0!==(int)$valueRaw ? JHtml::_('date',$valueRaw):'').'" name="'.$name.'" id="'.$id.'" value="'.htmlspecialchars($valueFormatted, ENT_COMPAT, 'UTF-8').'" '.$attribs.' />'.
				($readonly ? '' : JHTML::_('image','system/calendar.png', JText::_('JLIB_HTML_CALENDAR'), array( 'class' => 'calendar', 'id' => $id.'_img'), true));
	}
	
	/**
	 * Create the HTML page title.
	 *
	 * @param	string	$title	The title as provided by the component.
	 *
	 * @return	string	The title as it should be displayed in the browser.
	 * @since	4.3.0
	 */
	public function getPageTitle($title)
	{
		$app = JFactory::getApplication();
		
		if(empty($title))
		{
			$title = $app->getCfg('sitename');	
		}
		else
		{
			// test the version of of Joomla, see if we have 1.7.x or higher
			list($major, $minor, $revision) = explode('.', JVERSION);
			
			if((int)$minor > 6)
			{
				switch($app->getCfg('sitename_pagetitles', 0))
				{
					case 0: // No
						break;
					case 1: // After
						$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
						break;
					case 2: // Before
						$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
						break;
				}
			}
			else
			{
				switch($app->getCfg('sitename_pagetitles', 0))
				{
					case 0: // No
						break;
					case 1: // After
						$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
						break;
				}
			}
		}
				
		return $title;		
	}
}
?>
