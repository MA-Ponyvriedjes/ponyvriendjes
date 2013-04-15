/* VERSION 1.0.1 */
/* Made by InsertHTML.com */

(function($) {
    /* Remember! This is for vertical scrolling. 
    I might work on a horizontal scroll some other time! */
    
    $.fn.parallaxScroll = function(options) {
        
        var defaults = {
            multiplier: '1.0',
            position: '',
            negative: true
        }
            
        var options = $.extend(defaults, options);
        
        return this.each(function() { 
        	
        	/* Disable if it's a mobile */
        	var android = navigator.platform.match(/Android/i);
        	var ipad = navigator.platform.match(/iPad/i);
        	var iphone = navigator.platform.match(/iPhone/i);
        	var blackberry = navigator.platform.match(/Blackberry/i);
        	var webos = navigator.platform.match(/webOS/i);
        	
        	if(android || ipad || iphone || blackberry || webos) { return false; }
        	else {
	            var that = $(this);
	            
	            /* Should you have applied any positioning beforehand */
	
	            var topMove = parseFloat($(that).css('top'));
	            var bottomMove = parseFloat($(that).css('bottom'));
	
				if(isNaN(topMove)) { topMove = ''; }
				if(isNaN(bottomMove)) { bottomMove = ''; }
				
				/* Position Get */
				var positionGet = $(that).css('position');
				
				if(positionGet == 'static' && options.position == '') { options.position = 'relative'; }
				else if(positionGet != 'static' && options.position == '') { options.position = positionGet; }
				
				/* Scrolling */    
	            $(window).scroll(function() {
	                var scroll = $(this).scrollTop();
	                
	                /* Because of macs allowing scrolling above 0 */
	                if(scroll < 0) { return false; } 
	        		if(options.negative == true) {
	        		    var positionMoved = -(scroll * options.multiplier) + topMove - bottomMove;
	                }
	                else if(options.negative == false) {
	                    var positionMoved = (scroll * options.multiplier) + topMove - bottomMove;
	                }
	                
	                $(that).css({'position' : options.position, 'top' : positionMoved+'px'});    
	
	            
	    
	        	});
	        
	        }
       
        });
    
    }

})(jQuery);
    