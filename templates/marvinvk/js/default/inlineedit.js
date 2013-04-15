$(document).ready(function(){

	var temppath = $('.templates.article.main')

	/** ADDING INLINE TOOLBAR ********************************************************************/
	temppath.each(function(){
		if($(this).hasClass('-editable')){
			$(this).append('<div class="toolbar"><a href="#" class="edit">bewerk</a></div>');
		}
	});

	/** ADD ATTR WHEN CLICKED ON EDIT ********************************************************************/
	$('.toolbar a.edit').click(function(){
		temppath.attr('contenteditable','true');
	});


});