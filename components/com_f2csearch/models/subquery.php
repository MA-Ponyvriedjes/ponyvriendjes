<?php defined('_JEXEC') or die('Restricted access');

 

// import Joomla modelitem library

jimport('joomla.application.component.modelitem');
include_once JPATH_BASE.'/components/com_users/models/profile.php';
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_f2csearch'.DS.'tables');
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_f2csearch' .DS. 'helpers'.DS.'f2csearch.php' );
/**

 * Subquery Model

 */

class F2CSearchModelSubquery extends JModelItem

{

	/**

	 * @var array<JArticle> searchResult

	 */


	public $_required = 0;
	public $_type = null;
	public $_filters = array();
	public $_query = null;
	public $_id = null;

//
	public function __construct($id = null,$query_id = nul,$required = false,$type = null){
		
		$this->_id = $id;
		$this->_query_id = $query_id;
		$this->_required = $required;
		$this->_type = $type;
		$this->setFilters();
		
		parent::__construct();
	}
	
	
 	public function getQuery()
	{
		if(empty($this->_query)){
			
			
	
				$userProfile = new UsersModelProfile();
				$userProfileData = $userProfile->getData(); 

				$db =& JFactory::getDBO();
				
			
				$subquery = "SELECT field" . $this->_filters[0]->filter_id . ".formid FROM ";
				
				foreach($this->_filters as $filter){
					
					switch($this->_type){
						case 'f2c-formfield' :
							$subquery .= 	"(	SELECT DISTINCT formid 
												FROM #__f2c_fieldcontent 
												WHERE fieldid IN (" . $filter->key_id . ") 
												AND content ". $filter->operator ." ". $db->Quote($filter->value) . " 
											) as field" . $filter->filter_id . " 
											INNER JOIN ";
						break;
						case 'user-profile' :
							$filter->value = F2csearchHelper::getProfileFilterValue($filter->value, $userProfileData);
							$subquery .= 	"( 	SELECT DISTINCT formid 
												FROM #__f2c_fieldcontent, #__user_profiles 
												WHERE fieldid IN (" . $filter->key_id . ") 
												AND content IN (" . $db->Quote($filter->value) . ") 
												AND user_id = " . $userProfileData->id . "
											) as field" . $filter->filter_id . " 
											INNER JOIN ";
						break;
						case 'user-rating' :
							$subquery .= 	"( 	SELECT DISTINCT #__f2c_form.id AS formid 
												FROM #__f2c_form, #__userrating 
												WHERE #__f2c_form.reference_id=#__userrating.album_id 
												AND #__userrating.user_id = " . $userProfileData->id . " 
												AND #__userrating.user_rating = " . $db->Quote($filter->value) . "
											) as field" . $filter->filter_id . " 
											INNER JOIN ";
						break;
						case 'f2c-author' :
							$subquery .= 	"( 	SELECT DISTINCT #__f2c_form.id AS formid 
												FROM #__f2c_form WHERE #__f2c_form.created_by IN (" . $db->Quote($filter->value) . ")
											) as field" . $filter->filter_id . " 
											INNER JOIN ";
						break;
						case 'category' :
							$subquery .= 	"( 	SELECT DISTINCT #__f2c_form.id AS formid
												FROM #__f2c_form WHERE #__f2c_form.catid 
												IN (" . $db->Quote($filter->value) . ")
											) as field" . $filter->filter_id . " 
											INNER JOIN ";
						break;

					}
				}
				$subquery = substr($subquery,0,-11);
				
				//join on statement
				$filterlength = count($this->_filters);
				if($filterlength>1){
					foreach ($this->_filters as $key=>$filter){
						if($key==0){
							$subquery .= "ON field" . $filter->filter_id . ".formid = ";
						}
						else if($key<$filterlength-1){
							$subquery .= "field" . $filter->filter_id . ".formid AND field" . $filter->filter_id . ".formid = ";
						}
						else{
							$subquery .= "field" . $filter->filter_id . ".formid ";
						}
					}
				}
				$this->_query = $subquery;
			
			
		}
		return $this->_query;
	}

	public function isRequired()
	{
		return $this->_required;
	}

	public function setFilters(){
		
			$exludeAutoMenuInQuery = JRequest::getString('exludeAutoMenuInQuery', NULL);
			

			$db =& JFactory::getDBO();
	        //$query 	= "SELECT * FROM #__f2c_filters WHERE #__f2c_filters.subquery_id = " . $this->_id ;
			if($this->_id!= null){
				$query 	= "SELECT * FROM #__f2c_filters WHERE #__f2c_filters.filter_id =" . $db->Quote($this->_id);
			}
			else{
				$query 	= "SELECT * FROM #__f2c_filters WHERE #__f2c_filters.type = " . $db->Quote($this->_type) . " AND #__f2c_filters.required=" . $db->Quote($this->_required) . " AND #__f2c_filters.query_id=" . $db->Quote($this->_query_id);	
			}
			if($exludeAutoMenuInQuery==1){
				$query .= " AND #__f2c_filters.auto_menu <> 1";
			}
			
	        $db->setQuery($query);
			$result = $db->loadObjectList();
			
			//override value of filter when set and only one filter is active
			foreach ($result as $filter){
				
				if($filter->auto_menu==1){
					
					$value = JRequest::getString('value', NULL);
					$value = urldecode(JRequest::getString('value', NULL));
					
					//$value = mysql_real_escape_string (urldecode(JRequest::getString('value', NULL)));
					//echo $value;
					
					//$value = mysql_real_escape_string (urldecode(JRequest::getString('value', NULL)));
					//echo $value;
					
					
					//die($value);
					if($value!=NULL){
						
						$filter->value = $value;
					} 
				}

			}
			
			$this->_filters = $result;
			
	}
	public function getFilters(){
		return $this->_filters;
	}

	
	function stop($msg = '')
	{
	    global $mainframe;
	    echo $msg;
	    $mainframe->close();

	}	
}