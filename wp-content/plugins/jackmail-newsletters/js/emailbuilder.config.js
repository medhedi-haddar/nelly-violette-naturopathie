'use strict';

( function( window ) {

	window.__EB_CONFIG__ = {
		'I18N_PATH': jackmail_ajax_object.emailbuilder_path
	};

} )( window );

document.write( '<script src="' + window.__EB_CONFIG__.I18N_PATH + 'polyfills.bundle.js"></script>' );
document.write( '<script src="' + window.__EB_CONFIG__.I18N_PATH + 'main.bundle.js"></script>' );