jQuery( function($) {

	'use strict';

	/**
	 * Show Countdown bar in demo mode
	 */
	if ( $('#countdown-wrapper').length )
	{
		// Time left to display
		var timeLeft = Math.floor( ( Date.parse(window.cleanup.next_cleanup) - Date.now() ) / 1000 ),
		 	interval = window.cleanup.interval;

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

	function setShowCountdownVisibility()
	{
		if ( $( '#show_countdown' ).is(':checked') )
		{
			$( '#show-countdown-child-condition' ).show();
		}
		else
		{
			$( '#show-countdown-child-condition' ).hide();
		}
	}

	function setLoginVisibility()
	{
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
	}

	setShowCountdownVisibility();
	setLoginVisibility();

	// Settings Conditional Logic
	$( '#show_countdown' ).change(function()
	{
		setShowCountdownVisibility();
	} );

	$('#auto_login').change(function () 
	{
		setLoginVisibility();
	} );

} );