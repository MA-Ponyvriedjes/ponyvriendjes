<?php defined('_JEXEC') or die;

/**
 * F2csearch component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_f2search
 * @since		1.7
 */
class F2csearchHelper
{

	function setReferencialIds(){
		
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		
		/*if(!empty($cids)){
			$query = "SELECT subquery_id FROM #__f2c_subquerys WHERE #__f2c_subquerys.query_id IN ( " . implode(',',$cids) . " )";
			$db =& JFactory::getDBO();
			$db->setQuery( $query );
			$subqueryids = $db->loadResultArray();
		}	
		
		if(!empty($subqueryids)){
			$query = "SELECT filter_id FROM #__f2c_filters WHERE #__f2c_FILTERS.subquery_id IN ( " . implode(',',$subqueryids) . " )";
			$db =& JFactory::getDBO();
			$db->setQuery( $query );
			$filterids = $db->loadResultArray();
		}

		JRequest::setVar( 'subqueryids',$subqueryids);*/
		if(!empty($cids)){
			$query = "SELECT query_id FROM #__f2c_filters WHERE #__f2c_filters.query_id IN ( " . implode(',',$cids) . " )";
			$db =& JFactory::getDBO();
			$db->setQuery( $query );
			$filterids = $db->loadResultArray();
		}
		JRequest::setVar( 'filterids',$filterids);
		
	}
	function getCategoryList()
	{
		// Load the data
		$query = "SELECT id ,title FROM #__categories WHERE extension ='com_content' ";
		$db =& JFactory::getDBO();
		$db->setQuery( $query );
		$result = $db->loadAssocList();
		
		return $result;
	}
	
	function getAuthorList()
	{
		// Load the data
		$query = "SELECT DISTINCT #__content.created_by as id, #__users.name as title FROM #__content, #__users WHERE #__users.id = #__content.created_by ";
		$db =& JFactory::getDBO();
		$db->setQuery( $query );
		$result = $db->loadAssocList();
		
		return $result;
	
	}
	function &getF2cprojectList()
	{
		// Load the data
		$query = "SELECT id ,title FROM #__f2c_project";
		$db =& JFactory::getDBO();
		$db->setQuery( $query );
		$result = $db->loadAssocList();
		
		return $result;
	}
	function &getF2cfieldList()
	{
		// Load the data
		$query = 	"SELECT #__f2c_projectfields.id as id, CONCAT( #__f2c_project.title,  ' - ', #__f2c_projectfields.title ) as title
					FROM  `#__f2c_projectfields` , #__f2c_project
					WHERE #__f2c_projectfields.projectid = #__f2c_project.id";
		$db =& JFactory::getDBO();
		$db->setQuery( $query );
		$result = $db->loadAssocList();
		
		return $result;
	}
	function &getUserprofileList()
	{
		// Load the data
		$query = "SELECT DISTINCT profile_key as id, SUBSTRING(profile_key,9,20) as title FROM #__user_profiles";
		$db =& JFactory::getDBO();
		$db->setQuery( $query );
		$result = $db->loadAssocList();
		
		return $result;
	}
	function &getProfileFilterValue($values,$userProfileData){
		
		$profileFilterValue = array();
		$values = explode(",",$values);
		foreach($values as $value){
			array_push($profileFilterValue, "'".$userProfileData->profile[substr($value,8)] . "'");  
		}	
		//remove empty values
		$profileFilterValue = array_filter($profileFilterValue);
		//only unique values are needed
		$profileFilterValue  = array_unique($profileFilterValue );

		$profileFilterValue = implode(', ',$profileFilterValue);
		return $profileFilterValue;
	} 
	function renderFilter($filter,$type,$i){
		
		$html = '';
		
		if($filter->auto_menu == 1){
			$filter->auto_menu = ' checked ';
		}
		else{
			$filter->auto_menu = '';
		}

		switch($type){
			case 'f2c-formfield' :
				$html .= '
				   <td>
				   		<a href="#" class="deletefilter_btn">remove</a>
				   		<input class="filter_id" type="hidden" name="filters[' . $i . '][filter_id]" value="' . $filter->filter_id . '" />
				   		<input class="query_id" type="hidden" name="filters[' . $i . '][query_id]" value="' . $filter->query_id . '" />
				   		<input class="type" type="hidden" name="filters[' . $i . '][type]" value="' . $filter->type . '" />
				   			   	</td>
				   	<td> '
				   		. JHTML::_('select.genericlist',  array(0 => "Include if true",1 => "Exclude if not true"), 'filters[' . $i . '][required]', 'class="inputbox required"  size="1"', 'id', 'title', $filter->required) .
				   	'</td>
                   <td>' . JHTML::_('select.genericlist',  $this->optionLists['f2cfieldList'], 'filters[' . $i . '][key_id][]', 'class="inputbox key_id" multiple="multiple" size="10"', 'id', 'title', explode(',',$filter->key_id)) . '</td>
                   <td>' . JHTML::_('select.genericList', $this->optionLists['filteroperators'], 'filters[' . $i . '][operator]', 'class="inputbox operators" ', 'value', 'text', $filter->operator ) . '</td>
                   <td><input class="text_area value" type="text" name="filters[' . $i . '][value]" size="50" maxlength="250" value="' . $filter->value . '"/></td>                   
                   <td><input type="checkbox" class="auto_menu" value="automenu" name="filters[' . $i . '][auto_menu]" ' . $filter->auto_menu . '/></td>
                   <td>' . JHTML::_('select.genericlist',  array("0" => "- geen -","1ASC" => "First ASC","1DESC" => "First DESC"), 'filters[' . $i . '][order_by]', 'class="inputbox"  size="1"', 'id', 'title', $filter->order_by) .'</td>

                 ';
			break;

			case 'user-profile' :

				$html .= '
			 			<td>
			 				<a href="#" class="deletefilter_btn">remove</a> 
			 				<input class="filter_id" type="hidden" name="filters[' . $i . '][filter_id]" value="' . $filter->filter_id . '" /> 
			 				<input class="query_id" type="hidden" name="filters[' . $i . '][query_id]" value="' . $filter->query_id . '" />
				   			<input class="type" type="hidden" name="filters[' . $i . '][type]" value="' . $filter->type . '" />
				   			<input class="required" type="hidden" name="filters[' . $i . '][required]" value="' . $filter->required . '" />
			 			</td>
			            <td> '. JHTML::_('select.genericlist', $this->optionLists['f2cfieldList'], 'filters[' . $i . '][key_id][]', 'class="inputbox key_id" multiple="multiple" size="3"', 'id', 'title', explode(',',$filter->key_id)).' </td>
			            <td> '. JHTML::_('select.genericList', $this->optionLists['filteroperators'], 'filters[' . $i . '][operator]', 'class="inputbox operator"', 'value', 'text', $filter->operator ).'</td>
			            <td> '. JHTML::_('select.genericlist',  $this->optionLists['userprofileList'], 'filters[' . $i . '][value][]', 'class="inputbox value" multiple="multiple" size="3"', 'id', 'title',explode(',',$filter->value)) .' </td>
			            <td> <input class="auto_menu" type="checkbox"   name="filters[' . $i . '][auto_menu]" value="automenu" ' . $filter->auto_menu .'/> </td>

				';
			break;
			
			case 'user-rating' :
				$html .= '
					    <td>
					    	<a href="#" class="deletefilter_btn">remove</a> 
					    	<input class="filter_id" type="hidden" name="filters[' . $i . '][filter_id]" value="' . $filter->filter_id . '" /> 
					    	<input class="query_id" type="hidden" name="filters[' . $i . '][query_id]" value="' . $filter->query_id . '" />
				   			<input class="type" type="hidden" name="filters[' . $i . '][type]" value="' . $filter->type . '" />
				   			<input class="required" type="hidden" name="filters[' . $i . '][required]" value="' . $filter->required . '" />
				   		</td>
			            <td> Rating</td>
			            <td>' . JHTML::_('select.genericList', $this->optionLists['filteroperators'], 'filters[' . $i . '][operator]', 'class="inputbox operator"', 'value', 'text', $filter->operator ) . '</td>
			            <td>' . JHTML::_('select.genericlist',  $this->optionLists['userratingList'], 'filters[' . $i . '][value][]', 'class="inputbox value" multiple="multiple" size="3"', 'id', 'title', $filter->value) . ' </td>
			            <td> <input class="auto_menu" type="checkbox" name="filters[' . $i . '][auto_menu]" value="automenu" ' . $filter->auto_menu .'/> </td>

				';
			break;

			case 'f2c-author' :
				$html .= '
					    <td>
					    	<a href="#" class="deletefilter_btn">remove</a> 
					    	<input class="filter_id" type="hidden" name="filters[' . $i . '][filter_id]" value="' . $filter->filter_id . '" /> 
					    	<input class="query_id" type="hidden" name="filters[' . $i . '][query_id]" value="' . $filter->query_id . '" />
				   			<input class="type" type="hidden" name="filters[' . $i . '][type]" value="' . $filter->type . '" />
				   			<input class="required" type="hidden" name="filters[' . $i . '][required]" value="' . $filter->required . '" />
					    </td>
			            <td> Auteur</td>
			            <td>' . JHTML::_('select.genericList', $this->optionLists['filteroperators'], 'filters[' . $i . '][operator]', 'class="inputbox operator"', 'value', 'text', $filter->operator ) . '</td>
			            <td>' . JHTML::_('select.genericlist',  $this->optionLists['authorList'], 'filters[' . $i . '][value][]', 'class="inputbox value" multiple="multiple" size="3"', 'id', 'title', $filter->value) . ' </td>
			            <td> <input class="auto_menu" type="checkbox" name="filters[' . $i . '][auto_menu]" value="automenu" ' . $filter->auto_menu .'/> </td>

				';
			break;
			
			case 'category' :
				$html .= '
					    <td>
					    	<a href="#" class="deletefilter_btn">remove</a> 
					    	<input class="filter_id" type="hidden" name="filters[' . $i . '][filter_id]" value="' . $filter->filter_id . '" /> 
					    	<input class="query_id" type="hidden" name="filters[' . $i . '][query_id]" value="' . $filter->query_id . '" />
				   			<input class="type" type="hidden" name="filters[' . $i . '][type]" value="' . $filter->type . '" />
				   			<input class="required" type="hidden" name="filters[' . $i . '][required]" value="' . $filter->required . '" />
				 
					    </td>
					    	<td> '
				   		. JHTML::_('select.genericlist',  array(0 => "Include if true",1 => "Exclude if not true"), 'filters[' . $i . '][required]', 'class="inputbox required"  size="1"', 'id', 'title', $filter->required) .
				   	'</td>
			            <td> Category</td>
			            <td>' . JHTML::_('select.genericList', $this->optionLists['filteroperators'], 'filters[' . $i . '][operator]', 'class="inputbox operator"', 'value', 'text', $filter->operator ) . '</td>
			            <td>' . JHTML::_('select.genericlist',  $this->optionLists['categoryList'], 'filters[' . $i . '][value][]', 'class="inputbox value" multiple="multiple" size="3"', 'id', 'title', $filter->value) . ' </td>
			            <td> <input class="auto_menu" type="checkbox" name="filters[' . $i . '][auto_menu]" value="automenu" ' . $filter->auto_menu .'/> </td>
			             <td>' . JHTML::_('select.genericlist',  array("0" => "- geen -","1ASC" => "First ASC","1DESC" => "First DESC"), 'filters[' . $i . '][order_by]', 'class="inputbox"  size="1"', 'id', 'title', $filter->order_by) .'</td>

				';
			break;
			
		}		

		return $html;
	}

	function renderSubquery(){
		
		return $html;
	}
}