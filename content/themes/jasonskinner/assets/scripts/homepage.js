var $j = jQuery.noConflict()

$j( document ).ready(function() {
	/*
	TYPED
	 */
	var typed = new Typed('#typed', {
		stringsElement: '#typed-strings',
		typeSpeed: 80,
		backSpeed: 70,
		loop: true,
		loopCount: Infinity,
		cursorChar: "|",
	});
});