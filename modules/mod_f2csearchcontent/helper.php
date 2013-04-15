<?php defined( '_JEXEC' ) or die( 'Restricted access' );


			
//require_once('components'.DS.'com_content'.DS.'models'.DS.'article.php');

class modF2CHelper
{
	
	function getList($requestData){
		
		JLoader::import('joomla.application.component.model'); 
		JLoader::import( 'list', 'components' . DS . 'com_f2csearch' . DS . 'models' );

	
		$F2CSearchModelList = JModelItem::getInstance( 'list', 'F2CSearchModel' );		
			
		$F2CSearchModelList->setQueryParams($requestData);
		

		$list = &$F2CSearchModelList->getResults();
		return $list;
	}

	function renderListItems($requestData,$layout){
		
		$list = ModF2CHelper::getList($requestData);
		
		if(count($list))
			$html = '<div class="items">';

		foreach($list as $item) {
			$html .= '<div class="item">';
			$html .= $item->introtext; 
			$html .= '</div>';
		}

		if(count($list))
			$html .= "</div>";

		return $html;

	}
	/*function getArticles(&$params)
	{
		$items = $params->get('items', 1);
		
		//make a database query to get articles
		$db =& JFactory::getDBO();
		$query = modF2CHelper::getArticleQuery($params);
		$db->setQuery( $query, 0, $items );
		$articles = $db->loadObjectList();
		
		$template = $params->get('template', -1);
		$joomla_articles = array();
		$i=0;
			
		//create a content array  
		foreach ($articles as &$article)
		{
			//JModel::addIncludePath('components'.DS.'com_content'.DS.'models');
			//$joomla_article =& JModel::getInstance('Article', 'ContentModel');
			if($template=='main'||$template=='intro'){
				
				//Get article object
				$joomla_article =& JTable::getInstance('content');
				$joomla_article->load($article->id);
				
				if($template=='intro'){
					$joomla_articles[$i] = $joomla_article->introtext;
				}
				else{
					$joomla_articles[$i] = $joomla_article->fulltext;
				}
			}
			else{
				
				$joomla_article =& JTable::getInstance('F2CModules', 'Table');
				$joomla_article->load($article->id);
				
				if($template=='intro_mod'){
					$joomla_articles[$i] = $joomla_article->introtext;
				}
				else{
					$joomla_articles[$i] = $joomla_article->fulltext;
				}
				
			}
			
			
			$i++;
		}
		return $joomla_articles;
	}
	
	
	function getArticleContent($id)
	{
		
		$db =& JFactory::getDBO();
		$query = "SELECT projectid, formid, fieldtypeid, fieldname, content 
				FROM #__f2c_fieldcontent INNER JOIN #__f2c_projectfields 
				ON #__f2c_fieldcontent.fieldid = #__f2c_projectfields.id 
				WHERE formid=".$id;
				 
		
		$db->setQuery( $query);
		$fields = $db->loadAssocList();
		$content = modF2CHelper::getContentObject($fields);

		return $content;
	}
	
	function getContentObject($fields)
	{
		$content = array();
		foreach ($fields as &$field)
		{	
			$i++;
			switch ($field['fieldtypeid']) 
			{	
				case 6:
					$content[$field['fieldname']] = modF2CHelper::getImageContentObject($field);
					//echo $content[$field['fieldname']] -> GetImagesPath($field['projectid'],$field['formid'],true);
					//die();
				break;
				default:
					$content[$field['fieldname']] = $field['content'];
				break;
			}
		}
		
		return $content;
	}
	
	function getImageContentObject($field)
	{
		$image = array();
		$imageSettings = new F2C_ImageSettings();	
		$imageSettings = unserialize($field['content']);
		
		$image['PATH_RELATIVE'] = F2C_Image::GetImagesPath($field['projectid'],$field['formid'],true).'/'.$imageSettings->filename;
		$image['THUMB_URL_RELATIVE'] = F2C_Image::GetThumbnailsUrl($field['projectid'],$field['formid'],true).'/'.$imageSettings->filename;
		
		return $image;
	}
	
	function getArticleQuery($params){
		
		//User & Time Parameters
		$user =& JFactory::getUser();
		$db =& JFactory::getDBO();
		$nullDate = $db->Quote($db->getNullDate());
		$nowDate = $db->Quote(JFactory::getDate()->toMySQL());

		//Module parameters
		$projectid = $params->get('projectid', -1);
		$categoryid = $params->get('categoryid', -1);
		$sectionid = $params->get('sectionid', -1);
		$ordening = $params->get('ordering', -1);
		$secordening = $params->get('sec_ordering', -1);
		$template = $params->get('template', -1);
		$includearticle = explode(',', $params->get('includearticle', -1));
		$excludearticle =  explode(',', $params->get('excludearticle', -1));
	
		//Select & Join
		
		//Join with com_content if intro or main template is selected
		if($template=='main'||$template=='intro'){
			$select = "SELECT #__content.id FROM #__f2c_form ";
			$join = "INNER JOIN #__content ON #__f2c_form.reference_id=#__content.id ";
		}
		//Join with com_form2content_modules 
		else{
			$select = "SELECT #__f2c_modules.id FROM #__f2c_form ";
			$join = "INNER JOIN #__f2c_modules ON #__f2c_form.id=#__f2c_modules.reference_id ";
		}
		
		
		//Where
		
		//@USER RIGHTS
		
		//Guest -> Only published, public articles within the right timeframe 
		if ($user->guest) {
			$where = "WHERE #__f2c_form.state = '1' && #__f2c_form.access=0 && (#__f2c_form.publish_up = " . $nullDate . " OR #__f2c_form.publish_up <= " . $nowDate . ") && (#__f2c_form.publish_down = " . $nullDate . " OR #__f2c_form.publish_down >= " . $nowDate . ") ";
		}
		//Registered user -> Only published, public or registered articles within the right timeframe 
		else if($user->usertype=='Registered'){
			$where = "WHERE #__f2c_form.state = '1' && #__f2c_form.access!=2 && (#__f2c_form.publish_up = " . $nullDate . " OR #__f2c_form.publish_up <= " . $nowDate . ") && (#__f2c_form.publish_down = " . $nullDate . " OR #__f2c_form.publish_down >= " . $nowDate . ") ";
		}
		//Authors and above -> All published articles
		else{
			$where = "WHERE  #__f2c_form.state = '1' ";
		}
		
		
		//@FILTERING ON MODULE PARAMETERS
		
		if($projectid==-1&&$categoryid == -1 && $sectionid == -1&&$includearticle[0] != -1 ){
			$where .= "&& (#__f2c_form.id=".$includearticle.") ";
		}
		else{
			if($excludearticle[0] != -1){
				$where .= "&& (#__f2c_form.id<>".implode(' or #__f2c_form.id<>',$excludearticle).") ";
			}
			if($includearticle[0] != -1){
				$where .= "&& ((#__f2c_form.id=".implode(' or #__f2c_form.id=!',$includearticle).") || (";
			}
			if($projectid != -1){
				if(!is_array($projectid)){
					$where .= "&& (projectid=".$projectid.")";
				}
				else{
					$where .= "&& (projectid=".implode(' or projectid=',$projectid).") ";
				}
			}
			
			//Use OR statement if both sections and categories are selected
			if($categoryid != -1&&$sectionid != -1){
				
				if(count($categoryid)==1){
					$where .= "&& ((#__f2c_form.catid=".$categoryid.")";
				}
				else{
					$where .= "&& ((#__f2c_form.catid=".implode(' or #__f2c_form.catid=',$categoryid).") ";
				}
				
				if(count($sectionid)==1){
					$where .= "|| (#__f2c_form.sectionid=".$sectionid."))";
				}
				else{
					$where .= "|| (#__f2c_form.sectionid=".implode(' or #__f2c_form.sectionid=',$sectionid).")) ";
				}
			}
			
			else if($categoryid != -1){
				if(count($categoryid)==1){
					$where .= "&& (#__f2c_form.catid=".$categoryid.")";
				}
				else{
					$where .= "&& (#__f2c_form.catid=".implode(' or #__f2c_form.catid=',$categoryid).") ";
				}
				
			}
			else if($sectionid != -1) 
			{
				if(count($sectionid)==1){
					$where .= "&& (#__f2c_form.sectionid=".$sectionid.")";
				}
				else{
					$where .= "&& (#__f2c_form.sectionid=".implode(' or #__f2c_form.sectionid=',$sectionid).") ";
				}
			}
			
			if($includearticle[0] != -1){
				$where = str_replace('|| (&&','|| (',$where);
				$where .= " ))";
			}
			
		}	
		
		//Order
		$order = "ORDER BY ";
		if($ordening!=-1 && $ordening!='none'){
			$order .= '#__f2c_form.'.modF2CHelper::getOrderingString($ordering).', ';
		}
		$order .= '#__f2c_form.'.modF2CHelper::getOrderingString($secordering);
		$query = $select.$join.$where.$order;
		
		
		return $query;
	}
	
	function getOrderingString($orderingvalue){
		
		switch($orderingvalue)
		{
			
			case 'p_up_desc':		
				$ordering = 'publish_up DESC';
			break;
			case 'p_up_asc':		
				$ordering = 'publish_up ASC';
			break;		
			case 'o_dsc':
				$ordering = 'ordering DESC';
				break;
			case 'o_asc':
				$ordering = 'ordering ASC';
				break;
			case 'm_dsc':
				$ordering = 'modified DESC, created DESC';
				break;
			case 's_asc':
				$ordering = 'sectionid ASC';
				break;
			case 's_dsc':
				$ordering = 'sectionid DESC';
				break;
			case 'cy_asc':
				$ordering = 'catid ASC, sectionid ASC';
				break;
			case 'cy_dsc':
				$ordering = 'catid DESC, sectionid DESC';
				break;
			case 'random':
				$ordering = 'RAND()';
				break;
			case 'c_dsc':
			default:
				$ordering = 'created DESC';
			break;
		}
		
		return $ordering;
	}*/
}
//Array([0]=>Array([ID]=>1,[greeting]=>Hello, World!),[1]=>Array([ID]=>2,[greeting]=>Bonjour, Monde!),[2]=>Array([ID]=>3,[greeting]=>Ciao, Mondo!))
//SELECT fieldname, content FROM jos_f2c_fieldcontent INNER JOIN jos_f2c_projectfields ON jos_f2c_fieldcontent.fieldid = jos_f2c_projectfields.id WHERE formid=4