(function($){

  var prepareSlidingRec = function() {
    
		slidingRec = $('.sliding_rec');
		slidingRecVisible = null;
		slidingRecActive = true; // this one is needed to activeate feature, active closing with closing button deactivates feature

		if ( slidingRec.length ){ // checking, if there are any sliders on the page at all. If not, we should not fire the functions and waste memory
			preparePositioningEvents(); // listeners, that control the state of the web-page
			prepareClosingButton();
			actSlidingRec( 'hide', 'fast' );
		}

  };

	var preparePositioningEvents = function(){
		
		$(document).scroll(function(){
			countScrollFactor();
		});
		$(window).resize(function(){
			countScrollFactor();
		});
		
	};
	
	var countScrollFactor = function(){
		
		if ( slidingRecActive == true ){
			
			var windowHeight = $(window).height();
			var bodyHeight = $('body').height();
			var hiddenSize = bodyHeight - windowHeight;
			var scrollValue = $(window).scrollTop();
			var showingFactor = scrollValue > ( Math.round(hiddenSize / 3).toFixed(0) ); // event to show
			var hidingFactor = scrollValue > ( Math.round(hiddenSize / 4).toFixed(0) ); // event to hide

			if ( (hidingFactor == true) && (showingFactor == true) && ( slidingRecVisible == false ) ){
				actSlidingRec( 'show', 'animated' );
			}else if( (hidingFactor == false) && (showingFactor == false) && ( slidingRecVisible == true ) ){
				actSlidingRec( 'hide', 'animated' );
			}
			
			var debugText = ('Window height is: ' + windowHeight + ',<br/>Body height is: ' + bodyHeight + ',<br/>Hidden size is: ' + hiddenSize + ',<br/>Scroll is: ' + scrollValue + ',<br/>Showing Factor: ' + showingFactor + ',<br/>Hiding Factor: ' + hidingFactor);
			
		}else{
			
			var debugText = "Feature was actively disabled and will not start again until the page is reloaded";
			
		}
		
		$('div.test').html( debugText );
		
	};
	
	var prepareClosingButton = function() {
		
		var slidingRecCloseButton = slidingRec.find('.destroy_sliding_rec'); // defining closing button
		slidingRecCloseButton.live('click', function(){ // using "live" for the case that block will be created / loaded via AJAX
			actSlidingRec( 'hide', 'animated' ); // calling hiding function
			slidingRecActive = false; // deactivating feature completely, until the page will be reloaded again
		});
		
	};

	var tellSlidingRecDimensions = function() {
		
		slidingRecDimension = new Object(); // defining dimensions container to store all relevant data
		slidingRecDimension.myWidth = slidingRec.outerWidth(); // getting the width of the container itself
		slidingRecDimension.myOffset = slidingRec.offset(); // getting positions of the block
		slidingRecDimension.closingButtonOffset = $(slidingRec.find('.destroy_sliding_rec')).offset(); // getting positions of the closing button
		
		if ( slidingRecDimension.myOffset.left > slidingRecDimension.closingButtonOffset.left ){
			var sizingDifference = slidingRecDimension.myOffset.left - slidingRecDimension.closingButtonOffset.left;
			slidingRecDimension.completeWidth = slidingRecDimension.myWidth + sizingDifference;
		}else{
			slidingRecDimension.completeWidth = slidingRecDimension.myWidth;
		} // finding out the real size of the block (for the case closing button is not within parent's borders)
		
		return slidingRecDimension;
		
	};

	var actSlidingRec = function( action, kind ) {
		
		tellSlidingRecDimensions(); // asking about dimensions of the block
		directionModificator = ""; // to control the direction of animation (show or hide)
		
		if ( (action == 'hide') && (kind == 'fast') ){
			slidingRec.css('right', slidingRecDimension.completeWidth * -1);
			slidingRecVisible = false;
		}else if( (action == 'hide') && (kind == 'animated') ){
			slidingRecVisible = false;
			slidingRecAnimation( slidingRecDimension.completeWidth * -1 );
		}else if( (action == 'show') && (kind == 'animated') ){
			slidingRecVisible = true;
			slidingRecAnimation( 0 );
		} 
		
	};
	
	var slidingRecAnimation = function( directionParams, callback ){
		slidingRec.animate({
			right: directionParams,
			easing: 'easeout'
			}, 400, function() {
				if ( callback ){ callback(); }
		});
	};

  $(document).ready(function () {
    prepareSlidingRec(); // Please remember, that this function works only when there is 0 or 1 slider on the page. Multiple sliders are not supported. Slider must be positioned ar the right side of the page.
  });
  
})(jQuery);