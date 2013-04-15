
$(document).ready(function(){


	/** NIEUW ARTICLE ***************************************************************************/
	$("li.item-116 span").click(function(){
		$('ul.menu.mod_menu.cms').removeClass('active');
		$('ul.menu.cms-submenu.new').addClass('active');
	});
	$("li.item-120 span").click(function(){
		$('ul.menu.mod_menu.cms').addClass('active');
		$('ul.menu.cms-submenu.new').removeClass('active');
	});

	/** ARTICLES OVERZICHT ***************************************************************************/
	$("li.item-117 span").click(function(){
		$('ul.menu.mod_menu.cms').removeClass('active');
		$('ul.menu.cms-submenu.overzicht').addClass('active');
	});
	$("li.item-121 span").click(function(){
		$('ul.menu.mod_menu.cms').addClass('active');
		$('ul.menu.cms-submenu.overzicht').removeClass('active');
	});

	/** TERUG NAAR WEBSITE LINK*************************************/
	$('li.item-118 a').attr('href',$('.redirect-path-cms').text());


}) 	/* EINDE DOCUMENT READY *******************************************************************************************/


	