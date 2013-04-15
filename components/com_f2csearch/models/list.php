<?php defined('_JEXEC') or die('Restricted access');

 

// import Joomla modelitem library

jimport('joomla.application.component.modelitem');

include_once JPATH_BASE.'/components/com_content/models/article.php';
include_once JPATH_BASE.'/components/com_f2csearch/models/subquery.php';
include_once JPATH_BASE.'/components/com_f2csearch/models/subquerys.php';
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_f2csearch' .DS. 'helpers'.DS.'f2csearch.php' );
JTable::addIncludePath(JPATH_BASE . DS . 'administrator/components/com_f2csearch/tables');


 

/**

 * F2CSearch Model

 */

class F2CSearchModelList extends JModelItem

{

	/**

	 * @var array<JArticle> searchResult

	 */

	private $_results = null;
 	private $queryparams = array();
 	
 	
	public $_autoMenuList = array();
	private $menuparams = array();
		

	public function __construct(){
		
		
		parent::__construct();
		
	}

	public function &getResults()

	{
	
		if (empty($this->_results)) {
			    
				

					//set parameters from request if not set
					if(empty($this->queryparams))
					{
						$this->setQueryParams();
					}
					
					//get&run super sql
					$articleQuery = $this->getQuery();
					$articleData = $this->_getList($articleQuery);

					/*$db =& JFactory::getDBO();
					$db->setQuery($articleQuery);
					//get ArticleDataObjects
					$articleData = $db->loadObjectList();*/

				$this->_results = $articleData;


			
		}
		return $this->_results;

	}

	function getQuery(){
		
		$user = JFactory::getUser();

		$subquerys = new F2CSearchModelSubquerys();
		$subquerysData = $subquerys->getData(); 
		$filterOrder = $subquerys->getFilterOrder();
		
		/*$articleQuery = "SELECT #__content.*, digirating.user_rating 
						FROM #__f2c_form, #__content  
						LEFT JOIN ( 
							SELECT * 
							FROM #__userrating
							WHERE user_id=" . $user->id . "
						) as digirating ON #__content.id = digirating.album_id 
						WHERE #__f2c_form.reference_id=#__content.id 
						AND " . $this->getWhereAccessSQL();*/
		
			$select = "SELECT #__content.* ";
			$from = "FROM #__f2c_form, #__content  ";
			$where = "WHERE #__f2c_form.reference_id=#__content.id ";
			$counter = 0;
			foreach($filterOrder as $order){

				$select .= ', order' . $counter . '.content '; 
				$from .= ', #__f2c_fieldcontent AS order'.$counter . ' ';
				$where .= 'AND order' . $counter . '.fieldid = ' . $order->key_id . ' ';
				$where .= 'AND order' . $counter . '.formid = #__f2c_form.id ';
				
				$counter++;
			}


		
		/*	
		$articleQuery = "SELECT #__content.*  
						FROM #__f2c_form, #__content  
						WHERE #__f2c_form.reference_id=#__content.id 
						AND " . $this->getWhereAccessSQL();
		*/
		$articleQuery =	$select . $from . $where . " AND " .  $this->getWhereAccessSQL();

		if($this->queryparams['form_id']==0&&empty($this->queryparams['include_form_ids'])){
		
			$articleQuery .=	"AND " . $this->getWhereParametersSQL() . " AND " . $subquerys->getQuery();
			
		}
		else if(!empty($this->queryparams['include_form_ids'])){
			//$articleQuery .=  " AND ((" . $this->getWhereParametersSQL() . " AND " . $subquerys->getQuery() . ") 
								//OR (#__f2c_form.id IN(" . $this->queryparams['include_form_ids'] . "))) ";
			$articleQuery .= "AND #__f2c_form.id IN(" . $this->queryparams['include_form_ids'] . ") ";	
		}
		else{
			
			$articleQuery .=	"AND #__f2c_form.id = " . $this->queryparams['form_id'] . " ";	
		}
		
		$articleQuery .= $this->getOrderBySQL($filterOrder);	
		$articleQuery .= $this->getLimitSQL();	
		//dump($this->queryparams,'queryparams');
		//var_dump($articleQuery);
		//die();
		return $articleQuery; 			
	}


	public function setMenuParams($params){
		
		$this->menuparams = $params;

	}

	public function &getMenuItems(){
		
		$db =& JFactory::getDBO();
		$this->setQueryParams();
		
		//prepare query
		switch($this->menuparams['type']){
			case 'f2c-formfield':
				$select = "SELECT DISTINCT #__f2c_fieldcontent.content as value, #__f2c_fieldcontent.content as title "; 
				$from = "FROM #__f2c_fieldcontent, #__f2c_form ";
				$where = "WHERE #__f2c_fieldcontent.fieldid IN (" . $this->menuparams['fieldIds'] . " ) AND #__f2c_fieldcontent.formid=#__f2c_form.id ";
			break;
			case 'f2c-author':
				$select = "SELECT DISTINCT #__f2c_form.created_by as value, #__users.name as title ";
				$from = "FROM #__f2c_form, #__users "; 
				$where = "WHERE #__f2c_form.created_by = #__users.id ";
			break;
		}
		
		$order = "ORDER BY ". $this->menuparams['order'];
		
		//load every distinct form, to create submenu with direct links
		if($this->menuparams['submenu']){
			$select .= ",#__f2c_form.id as id, #__f2c_form.title as subtitle ";
		}
	
		//make sure there is a result
		if($this->menuparams['withResult']){
			
			
			JRequest::setVar('exludeAutoMenuInQuery',1);
			
			$subquerys = new F2CSearchModelSubquerys();
			$subquerysData = $subquerys->getData(); 

			
			$where .= " AND " . $this->getWhereAccessSQL() . " 
						AND " . $this->getWhereParametersSQL() . " AND " . $subquerys->getQuery();
					
			
		}	
			
		//build the query
		$query = $select . $from . $where . $order;
		
		//get the final result
		
		$db->setQuery($query);
		$fieldContentList = $db->loadObjectlist();
	
		return $fieldContentList;

	}

	function getAutoMenuList(){
		
		if (empty($this->_autoMenuList)) {
		
			$query_id = JRequest::getString('queryid', NULL); 
			
			switch($this->menuparams['type']){
				case 'f2c-formfield' :
					$list = F2csearchHelper::getFieldContentList($query_id, $this->menuparams);
				break;
				case 'f2c-author' :
					$list = F2csearchHelper::getAuthorList($query_id, $this->menuparams);
				break;
				case 'user-rating' :
					$list = F2csearchHelper::getRatinglist($query_id, $this->menuparams);
				break;
				case 'week' :
					$list = F2csearchHelper::getWeeklist($query_id, $this->menuparams);
				break;

			}

			$this->_autoMenuList = $list;
		}
			
		return $this->_autoMenuList; 			
	}
	function getWhereAccessSQL(){
		$user = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$groups = implode(',',$groups);

		$db =& JFactory::getDBO();
		$nullDate = $db->Quote($db->getNullDate());
		$nowDate = $db->Quote(JFactory::getDate()->toMySQL());

		$where = " #__f2c_form.access IN (" . $groups . ")
						AND #__f2c_form.state = 1
						AND (#__f2c_form.publish_up = " . $nullDate . " OR #__f2c_form.publish_up <= " . $nowDate . ")
						AND (#__f2c_form.publish_down = " . $nullDate . " OR #__f2c_form.publish_down >= " . $nowDate . ") ";
		return $where;
	}

	private function getOrderBySQL($filterOrders){
		
		if($filterOrders){

			$order = 'ORDER BY ';
			$counter = 0;

			foreach($filterOrders as $filter){
				$order	.= 'order' . $counter . '.content,';
				$counter++;
			}
			$order = substr($order, 0, strlen($order)-1); 
			$order .= ' ';
			return $order;
		}
		else if($this->queryparams['order_by']){
			$order = 'ORDER BY #__f2c_form.' . $this->queryparams['order_by'] . ' ';
			$order .= $this->queryparams['order_direction'] . ' ';
			
			return $order;
		}


		return '';
		 
	}
	private function getLimitSQL(){
		if($this->queryparams['limit']){
			$limit = 'LIMIT 0,' . $this->queryparams['limit'] . ' ';
			
			return $limit;
		}


		return '';
		 
	}
	public function getWhereParametersSQL(){
		$db =& JFactory::getDBO();
		
		//$this->queryparams['include_subcategories'] == true
		$chainedParams = '';

		if(!empty($this->queryparams['fromDate'])&&$this->queryparams['fromDate']!='0000-00-00'){
			$chainedParams .= "#__f2c_form.publish_up >= " . $db->Quote($this->queryparams['fromDate']) . " AND ";
		}
		if(!empty($this->queryparams['tillDate'])&&$this->queryparams['tillDate']!='0000-00-00'){
			$chainedParams .= "#__f2c_form.publish_up <= " . $db->Quote($this->queryparams['tillDate']) . " AND ";	
		}
		
		if(!empty($this->queryparams['category_id'])&&$this->queryparams['include_subcategories'] == false){
			$chainedParams .= "#__f2c_form.catid IN (" . implode(',',$this->queryparams['category_id']) . ") AND ";	
		}
		else if(!empty($this->queryparams['category_id'])&&$this->queryparams['include_subcategories'] == true){
			$chainedParams .= "#__f2c_form.catid IN (
								SELECT sub.id 
								FROM #__categories as sub 
								INNER JOIN #__categories as this 
								ON sub.lft > this.lft 
								AND sub.rgt < this.rgt 
								WHERE this.id =" . $this->queryparams['category_id'][0] . ") AND ";	

		}
		
		
		
		if(!empty($this->queryparams['created_by'])){
			$chainedParams .= "#__f2c_form.created_by IN (" . implode(',',$this->queryparams['created_by']) . ") AND ";	
		}
		if(!empty($this->queryparams['featured'])){
			$chainedParams .= "#__f2c_form.featured = " . $this->queryparams['featured'] . " AND ";	
		}
		if(!empty($this->queryparams['project_id'])){
			if(is_array($this->queryparams['project_id'])){
				$chainedParams .= "#__f2c_form.projectid IN (" . implode(',',$this->queryparams['project_id']) . ") AND ";	
			}
			else{
				$chainedParams .= "#__f2c_form.projectid IN (" . $this->queryparams['project_id'] . ") AND ";	
			}
		}
		if(!empty($this->queryparams['exclude_form_ids'])){
			$chainedParams .= "#__f2c_form.formid NOT IN (" . $this->queryparams['exclude_form_ids'] . ") AND ";	
		}

		if(empty($chainedParams)){
			$chainedParams = ' 1=1 ';
		}
		else{
			$chainedParams = substr($chainedParams,0,-4);
		}

		return $chainedParams;
	}

	public function setQueryParams($params = null){
		
		
		//if not provided get query parameter from the request
		if($params===null){
			$query_id = JRequest::getInt('query_id', NULL); 
		}
		else{
			$query_id = $params['query_id'];
			JRequest::setVar('query_id',$query_id);
		}
		
		//Fetch Parameters from db based on query_id
		if(!empty($query_id)){
			$query =& $this->getTable('querys');
		
			if (!$query->load($query_id)) {
	    		return JError::raiseWarning( 500, $query->getError() );
			}
			
			$queryparams = array(
					'fromDate' => $query->fromdate,
					'tillDate' => $query->tilldate,
					'category_id' => $query->category_id,
					'section_id' => $query->section_id,
					'featured' => $query->featured,
					'created_by' => $query->created_by,
					'project_id' => $query->form_id,
					'order_by' => $query->order_by
					
			);
		}
			
		//if not provided get override parameter from the request
		if($params===null){

			//Look for override from the request
			$created_by  = JRequest::getInt('created_by', NULL);
			$fromDate = JRequest::getString('fromdate', NULL);
			
			$tillDate = JRequest::getString('tilldate', NULL);
			$category_id = JRequest::getInt('category_id', NULL);
			$section_id = JRequest::getInt('section_id', NULL);
			$featured = JRequest::getBool('featured', NULL);
			$project_id = JRequest::getInt('project_id', NULL);
			$form_id = JRequest::getInt('form_id', NULL);
		}
		else{
			
			//Look for override from the provided params
			$created_by  = $params->created_by;
			$fromDate = $params->fromdate;
			
			$tillDate = $params->tilldate;
			$category_id = $params['category_id'];
			$section_id = $params->section_id;
			$featured = $params->featured;
			$project_id = $params['project_id'];
			$exclude_form_ids = $params['exclude_form_ids'];
			$include_form_ids = $params['include_form_ids'];
			$include_subcategories = $params['include_subcategories'];
			$order_by = $params['order_by'];
			$order_direction = $params['direction'];
			$limit = $params['limit'];
			
			
		}	
		
		//Only override when the parameter was actually found in de request or the provided params
		if(!empty($created_by)){
			$queryparams['created_by'] = $created_by;
		}
		if(!empty($fromDate)){
			
			$queryparams['fromDate'] = "'".JFactory::getDate($fromDate)->toMySQL()."'";

		}
		if(!empty($tillDate)){
			$queryparams['tillDate'] = "'".JFactory::getDate($tillDate)->toMySQL()."'";
			
		}

		if(!empty($category_id)){
			$queryparams['category_id'] = $category_id;

		}

		if(!empty($section_id)){
			$queryparams['section_id'] = $section_id;
		}

		
		if(!empty($project_id)){
			$queryparams['project_id'] = $project_id;
		}
		if(!empty($form_id)){
			$queryparams['form_id'] = $form_id;
		}

		if(!empty($featured)){
			$queryparams['featured'] = $featured;
		}

		if(!empty($include_form_ids)){
			$queryparams['include_form_ids'] = $include_form_ids;
		}

		if(!empty($exclude_form_ids)){
			$queryparams['exclude_form_ids'] = $exclude_form_ids;
		}

		if(!empty($include_subcategories)){
			$queryparams['include_subcategories'] = $include_subcategories;
		}

		if(!empty($order_by)){
			$queryparams['order_by'] = $order_by;
		}

		if(!empty($order_direction)){
			$queryparams['order_direction'] = $order_direction;
		}
		
		if(!empty($limit)){
			$queryparams['limit'] = $limit;
		}
		
		$date = $queryparams['fromDate'];
		if($date== '0000-00-00 00:00:00'){
			unset($queryparams['fromDate']);
		}
		$date = $queryparams['tillDate'];
		if($date== '0000-00-00 00:00:00'){
			unset($queryparams['tillDate']);
		}
		
		
		$this->queryparams = $queryparams;

	}
	
}



?>