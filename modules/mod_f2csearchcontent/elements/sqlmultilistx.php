<?php 
/**
* @copyright    Copyright (C) 2009 Open Source Matters. All rights reserved.
* @license      GNU/GPL
*/
 
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
 
jimport('joomla.html.html');
jimport('joomla.form.formfield');//import the necessary class definition for formfield


/**
 * Renders a multiple item select element 
 * using SQL result and explicitly specified params
 *
 */
 
class JFormFieldSQLMultiListX extends JFormField
{
        /**
        * Element name
        *
        * @access       protected
        * @var          string
        */
        var    $type = 'SQLMultiListX';
        
        /**
         * Method to get content articles
         *
         * @return array The field option objects.
         * @since 1.6
         */
         protected function getInput()
        {
                /*
                // Initialize variables.
                 $session = JFactory::getSession();
                 $options = array();
                 
                 $attr = '';

                 // Initialize some field attributes.
                 $attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

                 // To avoid user's confusion, readonly="true" should imply disabled="true".
                 if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
                 $attr .= ' disabled="disabled"';
                 }

                 $attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
                 $attr .= $this->multiple ? ' multiple="multiple"' : '';

                 // Initialize JavaScript field attributes.
                 $attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
                 

                 //now get to the business of finding the articles
                 
                 $db = &JFactory::getDBO();
                 $query = 'SELECT * FROM #__categories WHERE published=1 ORDER BY parent_id';
                 $db->setQuery( $query );
                 $categories = $db->loadObjectList();
                 
                 $articles=array();
                 
                 // set up first element of the array as all articles
                 $articles[0]->id = '';
                 $articles[0]->title = JText::_("ALLARTICLES");
                 
                 //loop through categories 
                 foreach ($categories as $category) {
                         $optgroup = JHTML::_('select.optgroup',$category->title,'id','title');
                         $query = 'SELECT id,title FROM #__content WHERE catid='.$category->id;
                         $db->setQuery( $query );
                         $results = $db->loadObjectList();
                         if(count($results)>0)
                         {
                         array_push($articles,$optgroup);
                                 foreach ($results as $result) {
                                 array_push($articles,$result);
                                 }
                        }
                 } 
                 
                 // Output
                 
                 return JHTML::_('select.genericlist', $articles, $this->name, trim($attr), 'id', 'title', $this->value );
 */
 
                $fieldname = $this->name;
                // Construct the various argument calls that are supported.
                $attribs       = ' ';
                if ($v = $this->size) {
                        $attribs       .= 'size="'.$v.'"';
                }
                if ($v = $this->class) {
                        $attribs       .= 'class="'.$v.'"';
                } else {
                        $attribs       .= 'class="inputbox"';
                }
                if ($m = $this->multiple)
                {
                        $attribs        .= ' multiple="multiple"';
                       // $fieldname           .= '[]';               

                }
 
                // Query items for list.
				$db			= & JFactory::getDBO();
				$db->setQuery($this->element['sql']);
				$key = ($this->element['key_field'] ? $this->element['key_field'] : 'value');
				$val = ($this->element['value_field'] ? $this->element['value_field'] : $this->name);
               
              	$options = array();
                /*foreach ($this->node->children() as $option)
                {
                        $options[]= array($key=> $option->attributes('value'),$val => $option->data());
                }
                echo 'hello';
                 */
                		$rows = $db->loadObjectList();
               
            
		foreach ($rows as $row){
			array_push($options,$row);
		      
                }
                if($options){
			return JHTML::_('select.genericlist',$options, $fieldname, $attribs, $key, $val, $this->value);
		}
        }
}
