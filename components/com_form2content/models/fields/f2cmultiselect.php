<?php
defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldF2cMultiSelect extends JFormField
{
	protected $type = 'F2cMultiSelect';

	private static $initialized = false;
	
	protected function getInput()
	{
		// Perform the javascript and css initialization. This code should only run once for multiple JFormFieldF2cMultiSelect controls
		if(!self::$initialized)
		{
			// Add the javascript library
			$document = &JFactory::getDocument();
			
			$document->addStyleSheet(JURI::root() . '/media/com_form2content/css/f2cmtmulti.css');
			
			$document->addScript(JURI::root() . '/components/com_form2content/libraries/javascript/MTMultiSelect.js');
			
			$document->addScriptDeclaration('window.addEvent(\'domready\', function (){
	         						$$(\'.multiselect\').each(function(multiselect){
	            					new MTMultiWidget({\'datasrc\': multiselect, \'items_per_page\': 20});
	        						});            
	    						});');
			
			// Prevent this code from running again
			self::$initialized = true;
		}
				
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : 'class="multiselect"';
		$attr .= $this->element['style'] ? ' style="'.(string) $this->element['style'].'"' : '';
		
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true') {
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
		}
		// Create a regular list.
		else {
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$reg = new JRegistry();		
		$reg->loadString($this->element['options']);
		$optionList = $reg->get('options');
		
		// Initialize variables.
		$options = array();

		foreach ($optionList as $key => $value) 
		{

			$tmp = JHtml::_('select.option', $key, $value, 'value', 'text');
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}

