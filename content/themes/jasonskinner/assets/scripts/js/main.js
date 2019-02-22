var $j = jQuery.noConflict()

$j( document ).ready(function() {
	/*
	MENU
	 */
	$j('[data-curtain-menu-button]').click(function(){
		$j('body').toggleClass('curtain-menu-open');
	})


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
	LOGO HOVER
	 */
	// $j( 'a#logo-trigger' ).hover(
	// 	function () {
	// 		//console.log('invisible');
	// 		Foundation.Motion.animateOut( $j('.logo', this ), 'slide-out-left' );
	// 		$j('.logo', this ).stop();
	// 		//Foundation.Motion.animateIn( $j('.hover-text', this ), 'slide-in-left' );
	// 		//First answer had this line coming first.
	// 		//$j('.logo', this ).css("left", "-1000px");
	// 		//$j( '.hover-text', this ).css("left", "0px");
	// 		// It should actually probably be here
	// 		// so the movement is still done invisibly
	// 		//$j( 'a#logo-trigger .logo').prev().css("left", "-1000px");
	// 	},
	// 	function () {
	// 		//console.log('visible');
	// 		Foundation.Motion.animateIn( $j('.logo', this ), 'slide-in-left' );
	// 		$j('.logo', this ).stop();
	// 		//Foundation.Motion.animateOut( $j('.hover-text', this ), 'slide-out-left' );
	// 		// $j( 'a#logo-trigger .logo').prev().css("left", "0px");
	// 		// $j( 'a#logo-trigger .logo').prev().stop().animate({
	// 		// 	opacity: 1
	// 		// }, 500);
	// 	}
	// )
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

