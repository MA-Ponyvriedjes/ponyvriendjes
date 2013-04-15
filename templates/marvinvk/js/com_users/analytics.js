

$(document).ready(function(){


	/** BEGIN DATUM *************************************************************************/
	var nldatestart = '01/01/2013';
	var startdate = '1/1/2013';

	$('span.startdate').text(nldatestart);


	/** EIND DATUM (HUIDIGE) *************************************************************************/
	var end_date = new Date();

	var month = end_date.getMonth()+1;
	var day = end_date.getDate();

	var nldatenow = (day<10 ? '0' : '') + day + '/' + (month<10 ? '0' : '') + month + '/' + end_date.getFullYear();
	var datenow = month + '/' + day + '/' + end_date.getFullYear();

	$('span.enddate').text(nldatenow);


	/** TOTAL VIEWS REQUEST *************************************************************************/
	//8c8692637c2f4620807c7b1ebc45b3da
	//68095802
	$.ooQuery({
		id : '8c8692637c2f4620807c7b1ebc45b3da'
		, aid : '68095802'
		, startDate : startdate
		, endDate : datenow.toString()
		, metrics : ['ga:visitors'].toString()
	},
	{
		success : function(data){
			$('span.visits').text(data);
		}
		, error : function(){
			$('span.visits').append('error');
		}
		, timeout : 5000
	});


}); 	/* EINDE DOCUMENT READY *******************************************************************************************/
