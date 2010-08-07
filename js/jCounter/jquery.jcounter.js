/**
 * jCounter v1.3.0
 * Licensed under a Creative Commons Attribution-Noncommercial-Share Alike 3.0 United States License.
 * @author Dan Serpiello
 * dserpiello@gmail.com
 */
(function($){
	$.fn.jCounter = function( options ){
		var defaults = {
			count: 0,
			counterImg: 'counter-numbers.gif',
			counterBg: 'bg-counter.gif',
			dollarImg: 'dollar.png',
      currency: false,
			height: 800,
			width: 33,
			duration: 250
		};
		var options = $.extend(defaults, options);
		
		var nums = new Image();
		nums.src = options.counterImg;
		var numHeight = options.height / 10;
		
		var title = $(this).attr('title');
		
		var count = String(options.count);//.toString();
		
		if( count.charAt(0) == '+' && count.charAt(1).match(/\d/) ){
			var count = Number(title) + Number(count.substr(1));
		} else if( count.charAt(0) == '-' && count.charAt(1).match(/\d/) ){
			var count = Number(title) - Number(count.substr(1));
			if (title == '0' || count < 0) return false;
		} else if( count.match(/\D/) ){
			return false;
		}
		
		count = count.toString();
		var countlen = count.length;
		
		if( countlen != title.length ){
			$(this).empty();
		}
		
		if( $(this).children().size() == 0 ){
      if(!options.currency) {
        $(this).css({'width' : ((options.width * countlen) + (countlen - 1)) + 'px', 'height' : numHeight, 'background-image' : 'url(' + options.counterBg + ')'});
      }
      else {
        $(this).css({'width' : ((options.width * countlen) + (countlen - 1) + options.width+1) + 'px', 'height' : numHeight, 'background-image' : 'url(' + options.counterBg + ')'});
      }
			for( var i = 0; i < countlen; i++ ){
				$(this).append('<div class="jCounter' + i + '"></div>\n');
				$('.jCounter' + i, $(this)).css({'background-image' : 'url(' + options.counterImg + ')', 'float' : 'right', 'width' : options.width + 'px', 'height' : numHeight + 'px'});
				if (i > 0) $('.jCounter' + i, $(this)).css('border-right', '1px solid #fcfdfd');
			}
      if(options.currency) {
        $(this).append('<div class="jCounterDollar"></div>\n');
        $('.jCounterDollar', $(this)).css({'background-image' : 'url(' + options.dollarImg + ')', 'float' : 'right', 'width' : options.width + 'px', 'height' : numHeight + 'px'});
        $('.jCounterDollar', $(this)).css('border-right', '1px solid #fcfdfd');
      }
		}
    
		for( var i = 0; i < countlen; i++ ){
			$('.jCounter' + i, $(this)).animate(
				{backgroundPosition: '(0 ' + (count.substr(countlen - (i+1), 1) * (-numHeight)) + 'px)'},
				{duration: options.duration}
			);
		}
		
		$(this).attr('title', count);
	};
})(jQuery);