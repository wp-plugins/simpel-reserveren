/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and 
 * then make any necessary changes to the page using jQuery.
 */
( function( $ ) {
	
	//Update site title color in real time...
	wp.customize( 'simpelreserveren[zoek_bg]', function( value ) {
		value.bind( function( newval ) {
			$('.sr-order-box').css('background-color', newval );
		} );
	} );

	wp.customize( 'simpelreserveren[filter_bg]', function( value ) {
		value.bind( function( newval ) {
			$('.sr-filter-box').css('background-color', newval );
		} );
	} );

	wp.customize( 'simpelreserveren[zoek_color]', function( value ) {
		value.bind( function( newval ) {
			$('.sr-order-box').css('color', newval );
		} );
	} );

	wp.customize( 'simpelreserveren[primary_button_bg]', function( value ) {
		value.bind( function( newval ) {
			$('.sr-primary-button').css('background-color', newval );
		} );
	} );

	wp.customize( 'simpelreserveren[primary_button_color]', function( value ) {
		value.bind( function( newval ) {
			$('.sr-primary-button').css('color', newval );
		} );
	} );

	wp.customize( 'simpelreserveren[prijs_color]', function( value ) {
		value.bind( function( newval ) {
			$('.sr-boeken-prijs-voor').css('color', newval );
		} );
	} );

	wp.customize( 'simpelreserveren[prijs_bg]', function( value ) {
		value.bind( function( newval ) {
			$('.sr-boeken').css('background-color', newval );
		} );
	} );

	wp.customize( 'simpelreserveren[zoek_blok_position]', function( value ) {
		value.bind( function( newval ) {
			$('#zoeken').removeClass('static fixed').addClass( newval );
		} );
	} );

	var a = $('<a href="/zoeken/" class="btn btn-default sr-primary-button">Zoeken</a>');
	a.html($('#zoeken button:submit').html());
	$('#zoeken button:submit').after(a);
	$('#zoeken button:submit').remove();


	
} )( jQuery );