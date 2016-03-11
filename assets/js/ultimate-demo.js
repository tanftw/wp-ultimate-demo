jQuery( function($) {

	'use strict';

	/**
	 * Show Countdown bar in demo mode
	 */
	if ( $('#countdown-wrapper').length )
	{
		// Time left to display
		var timeLeft = parseInt(window.cleanup.time_left),
		 	interval = parseInt(window.cleanup.interval);

		var $countdownWrapper = $('#countdown-wrapper'),
			$countdown 		  = $('#countdown');

		setInterval(function () 
		{
			if ( timeLeft < 0 )
				return;

			if ( timeLeft <= interval ) {
				$countdown.html(timeLeft);

				$countdownWrapper.show();
			}

			timeLeft--;

			if ( timeLeft == 0 )
				window.location.href = window.location.pathname + "?" + $.param({'cleanup':'success'});
		
		}, 1000);
	}

	// Settings Conditional Logic
	$( '#show_countdown' ).change(function() {
		if ( $( this ).is(':checked') )
		{
			$( '#show-countdown-child-condition' ).show();
		}
		else
		{
			$( '#show-countdown-child-condition' ).hide();
		}
	} );

	$('#auto_login').change(function () {
		var thisVal = $( '#auto_login' ).val();

		if ( thisVal == 0 )
			$( '#auto-login-as, #prefill-settings' ).hide();

		if ( thisVal == 1 ) 
		{
			$( '#auto-login-as' ).show();
			$( '#prefill-settings' ).hide();
		}

		if ( thisVal == 2 ) 
		{
			$( '#auto-login-as' ).hide();
			$( '#prefill-settings' ).show();
		}
	} );

	$('#hide_from_anyone').change(function() {
		if ( $( this ).is(':checked') )
		{
			$( '#hide-menu' ).show();
		}
		else
		{
			$( '#hide-menu' ).hide();
		}
	});

	$('#show_countdown, #auto_login, #hide_from_anyone').trigger('change');

} );