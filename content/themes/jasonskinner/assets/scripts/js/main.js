var $j = jQuery.noConflict()

$j( document ).ready(function() {
	/*
	TYPED
	 */
	// var typed = new Typed('#typed-container', {
	// 	stringsElement: '#typed-strings',
	// 	typeSpeed: 60,
	// 	backSpeed: 60,
	// 	loop: true,
	// 	loopCount: Infinity,
	// 	showCursor: false,
	// });

	/*
	WOW
	 */
	// $j(function() {
	// 	var wow = new WOW({
	// 		boxClass: 'moveit',
	// 		animateCLass: 'is-animating',
	// 		mobile: false        // trigger animations on mobile devices (true is default)
	// 	});
	// 	wow.init();
	// });

	/*
	ISOTOPE
	 */
	//set container variable so we don't have to type alot
	var $container = $j('.work');
	//run function when all images touched by isotope are loaded
	$container.imagesLoaded( function(){
		//set parameters
		$container.isotope({
			//tell isotope what to target
			itemSelector : '.cell',
			//set the layout mode
			layoutMode: 'fitRows',
			//tell isotope to use CSS3 if it can and fallback to jQuery
			animationEngine : 'best-available',
			//set masonry parameter
			// masonry: {
			// 	//we want 5 columns
			// 	columnWidth: $container.width() / 5
			// }

		});
	});
	//tell isotope our filters are in the options id & links
	var $optionSets = $j('#work-options'),
		$optionLinks = $optionSets.find('a');

	//click function to sort by data
	$optionLinks.click(function(){
		var $this = $j(this);
		// don't proceed if already selected
		if ( $this.hasClass('selected') ) {
			return false;
		}
		var $optionSet = $this.parents('.option-set');
		$optionSet.find('.selected').removeClass('selected');
		$this.addClass('selected');

		// make option object dynamically, i.e. { filter: '.my-filter-class' }
		var options = {},
			key = $optionSet.attr('data-option-key'),
			value = $this.attr('data-option-value');
		// parse 'false' as false boolean
		value = value === 'false' ? false : value;
		options[ key ] = value;
		if ( key === 'layoutMode' && typeof changeLayoutMode === 'function' ) {
			// changes in layout modes need extra logic
			changeLayoutMode( $this, options );
		} else {
			// otherwise, apply new options
			$container.isotope( options );
		}
		return false;
	});

});

// $j(document).foundation();
// if(matchMedia){
// 	var matchingMediumAndLargeScreens = matchMedia('only all and (min-width: 680px)');
// 	if(matchingMediumAndLargeScreens && matchingMediumAndLargeScreens.matches){
// 		$j('.journal-post)').waypoint(function(direction){
// 			if(direction === 'down'){
// 				$j(this).addClass('is-animating');
// 			}
// 		}, { offset: '0%' });
// 	}
// }

