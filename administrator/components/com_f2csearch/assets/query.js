
 jQuery.noConflict();
 var $_ = jQuery;
 var gSubquery = 0;
$_(document).ready(function(){
	
	$_('.newfilter_btn').click(addFilter);
	$_('.deletefilter_btn').click(deleteFilter);
		
});

function addFilter(){
	
	var sType = $_(this).prev().val();
	var jFilter = $_('#lib .filter.'+sType).clone();
	var nQuery_id = $_('input[name=query_id]').val();
	var sRowClass = getNextRowClass($_(this).closest('tbody'));
	jFilter.removeClass();
	jFilter.addClass(sRowClass);
	jFilter.addClass(sType);
	
	var nVolgnummer = $_('#filters tbody tr').length-1;
	
	/*var bRequired = 0;
	if($_(this).closest('table').hasClass('required')){
		bRequired = 1;
	}
	
	var nSubqueryId = getSubqueryId(sType,bRequired);

	if(nSubqueryId===false){
	
		nSubqueryId = 0;
	}*/

	//rewrite name attr to include volgnummer
	$_('[name]',jFilter).each(function(){
		replacement = $_(this).attr('name').replace('<ID>',nVolgnummer);
		$_(this).attr('name', replacement);
	});
	/*
	$_('input.subquery_id',jFilter).attr('value',nSubqueryId);
	$_('input.required',jFilter).attr('value',bRequired);
	*/
	$_('input.query_id',jFilter).attr('value',nQuery_id);
	$_('input.type',jFilter).attr('value',sType);
	
	
	$_(this).closest('tr').before(jFilter);
	$_('.deletefilter_btn').click(deleteFilter);
	
	/*
	if(nSubqueryId === 0){
		addSubquery(sType,bRequired);
	}*/
	return false;
}

function getNextRowClass(jTable){
	
	var nRows = jTable.children('tr').length-1;

	if(nRows%2!==0){
		sRowClass = 'row1';
	}
	else if(nRows%2===0){
		
		sRowClass = 'row0';
	}
	
	return sRowClass;
}
/**
*	Returns subquery for refererence by a filter
*	Creates a new subquery if none can be found
*
*
**/

function getSubqueryId(sType,bRequired){
	
	if(bRequired){
		
		if($_('table.required tr.'+sType).length !== 0){
			
			return $_('table.required tr.'+sType+' .subquery_id').val();
		}
	}
	
	return false;
	
}


function addSubquery(sType,bRequired){
	
	var jSubquery = $_('#lib .subquery').clone();
	var nVolgnummer = $_('#subquerys tbody tr').length;

	$_('[name]',jSubquery).each(function(){
		replacement = $_(this).attr('name').replace('<ID>',nVolgnummer);
		$_(this).attr('name', replacement);
	});

	$_('.type',jSubquery).val(sType);
	$_('.required',jSubquery).val(bRequired);
	
	$_('#subquerys tbody').append(jSubquery);

	var oSubquery = {subquery_id : '0',query_id : $_('.query_id',jSubquery).val(),type : sType,required : bRequired};
	addSubquerytoDB(oSubquery);
	
}

function addSubquerytoDB(oSubquery){
	
	$_.ajax({
		type: 'POST',
		url: 'index.php?option=com_f2csearch&task=save&controller=subquery',
		data: oSubquery,
		success: function(data){
		$_('#subquerys .subquery_id').last().val(data);
		$_('#filters .subquery_id').last().val(data);
		
	
		}
	});
}
function deleteFilter(){

	
	nFilterId = $_(this).closest('tr').find('.filter_id').val();
	
	if(nFilterId!==''){
		arrFilterIds = [nFilterId];
		deleteFilterFromDB(arrFilterIds);
	}
	nSubqueryId = $_(this).closest('tr').find('.subquery_id').val();
	
	$_(this).closest('tr').remove();
	
	if($_('#filters .subquery_id[value='+nSubqueryId+']').length === 0){
		deleteSubquery(nSubqueryId);
	}

	return false;
}

function deleteSubquery(nId){
	
	arrSubqueryIds = [nId];
	deleteSubqueryFromDB(arrSubqueryIds);
	$_('#filters .subquery_id[value='+nSubqueryId+']').closest('tr').remove();
}
function deleteFilterFromDB(arrFilter){
	

	$_.ajax({
	  type: 'POST',
	  url: 'index.php?option=com_f2csearch&task=delete&controller=filter',
	  data: {filterids: arrFilter},
	  success: function(data){
	  }
	});
}
function deleteSubqueryFromDB(arrIds){
	

	$_.ajax({
	  type: 'POST',
	  url: 'index.php?option=com_f2csearch&task=delete&controller=subquery',
	  data: {subqueryids: arrIds},
	  success: function(data){
	  }
	});
}
function removeFilter(){
	//ajax call to component
}

function removeSubquery(){
	
}

/*and (s1 of (s2 and s3))
and (s1 and s2 and s3)
and ((s1 and s2) of (s3 and s4))

een rij van ands staan in dezelfde subquery als ze van hetzelfde type zijn, allemaal of fataal zijn of dat niet 

Loop door de filters heen en plaats deze met de rest van de filters in een nieuwe subquery wanneer:

function resetSubquerys(){
	
	var nOldFatal       = null;
	var jSubquerys      = $_('.subquerys');
	var currentSubquery = -1;
	var sOldType        = -1;

	$_('.filters').each(function(){
			
		var sOperator = $_(this).prev().val();
		var sType     = $_(this).attr('class').split(' ')[1];
		var nFatal    = $_(this).children('.nFatal').val();
		
		if( sOperator == 'OF' || sOldType != sType || nOldFatal != nFatal ){
		
			nFatal        = $_(this).children('.nFatal').text();
			sType         = $_(this).children('.type').text();
			currentSubquery++;
		}

		moveFilterInSubquery($(this), currentSubquery);
			
			
	})
}

function moveFilterInSubquery(jFilter, currentSubquery){
	
	if($_('.subquerys').eq(currentSubquery) != null){
		
		$_('.subquerys').eq(currentSubquery).append(jFilter);
	}
	else{
		var sType= jFilter.attr('class').split(' ')[1];
		addSubquery(sType);
		$_('.subquerys').eq(currentSubquery).append(jFilter);
	}

}

- Er een of teken voor de filter staat
- Wanneer de waarde voor fataal != aan zijn voorganger
- Wanneer het type != aan zijn voorganger


resetSubqueryId

 
 
bij een of 
*/