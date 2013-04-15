<?php defined('_JEXEC') or die('Restricted access');

 

// import Joomla modelitem library

jimport('joomla.application.component.modelitem');
include_once JPATH_BASE.'/components/com_users/models/profile.php';
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_f2csearch'.DS.'tables');
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_f2csearch' .DS. 'helpers'.DS.'f2csearch.php' );
/**

 * Subquery Model

 */

class F2CSearchModelSubquerys extends JModel

{

	/**

	 * @var array<JArticle> searchResult

	 */


	private $optionalSubquerys = array();
 	private $requiredSubquerys = array();


	public function __construct(){
		
		
		
		parent::__construct();
	}
	
	public function &getData()
	{
		$query_id = JRequest::getInt('query_id', NULL); 
			
		$requiredSubquerys = array();
		$optionalSubquerys = array();

		if(!empty($query_id)){

	        //$query = "SELECT * FROM #__f2c_subquerys WHERE #__f2c_subquerys.query_id = " . $query_id;
	        $query = "SELECT * FROM #__f2c_filters WHERE #__f2c_filters.query_id = " . $query_id; //. " GROUP BY type, required";
	        
			$subqueryDataList  = $this->_getList($query);
			
			foreach($subqueryDataList as $subqueryData){
		
				
				if($subqueryData->required=='1'){
					$subquery = new F2CSearchModelSubquery(null,$subqueryData->query_id, $subqueryData->required, $subqueryData->type);
				
					array_push($requiredSubquerys, $subquery);
				}
				else{
					$subquery = new F2CSearchModelSubquery($subqueryData->filter_id,$subqueryData->query_id, $subqueryData->required, $subqueryData->type);
				
					array_push($optionalSubquerys, $subquery);	
				}			
			}
		}
		$this->requiredSubquerys = $requiredSubquerys;
		$this->optionalSubquerys = $optionalSubquerys; 
		
	}

	public function getQuery(){
		

			$requiredQuery = '';
			$optionalQuery = '';
			
			foreach($this->requiredSubquerys as $subquery){ 
						
					if(count($subquery->getFilters())!=0){
						$requiredQuery .= "#__f2c_form.id IN (" . $subquery->getQuery() . ") AND ";
					}
			}

			foreach($this->optionalSubquerys as $subquery){
					
					if(count($subquery->getFilters())!=0){
						$optionalQuery .= "#__f2c_form.id IN (" . $subquery->getQuery() . ") OR  ";
					}
				
			}

			if(empty($requiredQuery) && empty($optionalQuery)){
				$chainedQuery = ' 1=1 ';
			}
			else if(empty($optionalQuery)){
				$chainedQuery = substr($requiredQuery,0,-4);

			}
			else if(empty($requiredQuery)){
				$chainedQuery = " ( " . substr($optionalQuery,0,-4) . " ) ";
			}
			else{
				$chainedQuery = $requiredQuery . " AND (" . $optionalQuery .") ";
			}
			
			return $chainedQuery;
	}

	public function getFilterOrder(){
		$query_id = JRequest::getInt('query_id', NULL); 
		
		$query = "SELECT * FROM #__f2c_filters WHERE #__f2c_filters.query_id = " . $query_id . " AND #__f2c_filters.order_by <> 0 "; //. " GROUP BY type, required";
	        
		$filterOrderList  = $this->_getList($query);
		$filters = array();
		foreach($filterOrderList as $filter){
			$order = substr($filter->order_by,0,1);
			$filters[$order] = $filter;
		}
		return $filters;
		
	}
	
}