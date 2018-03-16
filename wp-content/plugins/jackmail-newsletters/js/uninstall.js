'use strict';

if ( window.jQuery ) {

	jQuery( document ).ready( function( $ ) {

		var jackmail_deactivate_init = false;
		var jackmail = 'tr#jackmail-newsletters';
		if ( jQuery( jackmail ).length === 0 ) {
			jackmail = 'tr[data-slug="jackmail-newsletters"]';
		}
		if ( jQuery( jackmail ).length === 0 ) {
			jackmail = 'tr#jackmail';
		}
		if ( jQuery( jackmail ).length === 0 ) {
			jackmail = 'tr[data-slug="jackmail"]';
		}
		if ( jQuery( jackmail ).length !== 0 ) {
			jQuery( jackmail + ' .deactivate' ).click( function( event ) {
				event.preventDefault();
				if ( !jackmail_deactivate_init ) {
					jackmail_deactivate_init = true;
					var translations = jackmail_uninstall_translations_object;
					var current_reason = '';
					var content =
						'<div class="jackmail">' +
						'	<div class="jackmail_confirmation">' +
						'		<div class="jackmail_confirmation_background"></div>' +
						'		<div class="jackmail_confirmation_message jackmail_confirmation_large jackmail_confirmation_uninstall">' +
						'			<div class="dashicons dashicons-no"></div>' +
						'			<div class="jackmail_confirmation_large_content">' +
						'				<p class="jackmail_title">' + translations.introduction + '</p>' +
						'				<p class="jackmail_grey">' + translations.warning + '</p>' +
						'				<div class="jackmail_uninstall_reasons">' +
						'					<p class="jackmail_bold jackmail_m_b_13">' + translations.reason + '</p>' +
						'					<span id="jackmail_reason1" class="jackmail_vertical_middle_container">' +
						'						<span class="jackmail_radio_unchecked"></span>' +
						'						<span class="jackmail_radio_title">' + translations.reason1 + '</span>' +
						'					</span>' +
						'					<br/>' +
						'					<span id="jackmail_reason2" class="jackmail_vertical_middle_container">' +
						'						<span class="jackmail_radio_unchecked"></span>' +
						'						<span class="jackmail_radio_title">' + translations.reason2 + '</span>' +
						'					</span>' +
						'					<br/>' +
						'					<span id="jackmail_reason3" class="jackmail_vertical_middle_container">' +
						'						<span class="jackmail_radio_unchecked"></span>' +
						'						<span class="jackmail_radio_title">' + translations.reason3 + '</span>' +
						'					</span>' +
						'					<br/>' +
						'					<span id="jackmail_reason4" class="jackmail_vertical_middle_container">' +
						'						<span class="jackmail_radio_unchecked"></span>' +
						'						<span class="jackmail_radio_title">' + translations.reason4 + '</span>' +
						'					</span>' +
						'					<br/>' +
						'					<span id="jackmail_reason5" class="jackmail_vertical_middle_container">' +
						'						<span class="jackmail_radio_unchecked"></span>' +
						'						<span class="jackmail_radio_title">' + translations.reason5 + '</span>' +
						'					</span>' +
						'					<br/>' +
						'					<span id="jackmail_reason6" class="jackmail_vertical_middle_container">' +
						'						<span class="jackmail_radio_unchecked"></span>' +
						'						<span class="jackmail_radio_title">' + translations.reason_other + '</span>' +
						'						<span class="jackmail_reason_other_detail">' +
						'							&nbsp;<input type="text"/>' +
						'						</span>' +
						'					</span>' +
						'					<br/>' +
						'				</div>' +
						'				<p id="jackmail_buttons">' +
						'					<input class="jackmail_green_button" type="button" value="' + translations.uninstall + '"/>' +
						'					<input class="jackmail_button" type="button" value="' + translations.cancel + '"/>' +
						'				</p>' +
						'			</div>' +
						'		</div>' +
						'	</div>' +
						'</div>';

					jQuery( jackmail + ' .row-actions' ).append( content );

					jQuery( '.jackmail .jackmail_reason_other_detail' ).hide();

					jQuery( '.jackmail .dashicons.dashicons-no' ).click( function( event ) {
						jackmail_hide();
					} );

					jQuery( '.jackmail .jackmail_green_button' ).click( function( event ) {
						jQuery( '.jackmail #jackmail_buttons' ).hide();
						var action = 'jackmail_uninstall_reason';
						var data_parameters = {
							'action': action,
							'key': jackmail_ajax_object.key,
							'nonce': jackmail_ajax_object.urls[ action ],
							'reason': current_reason,
							'reason_detail': jQuery( '.jackmail .jackmail_reason_other_detail input' ).val()
						};
						jQuery.post( jackmail_ajax_object.ajax_url, data_parameters, function() {
							var link = jQuery( jackmail + ' span.deactivate a' ).attr( 'href' );
							window.location.href = link;
						} );
					} );

					jQuery( '.jackmail .jackmail_button' ).click( function( event ) {
						jackmail_hide();
					} );

					jQuery( '.jackmail .jackmail_vertical_middle_container' ).click( function( event ) {
						jQuery( '.jackmail .jackmail_vertical_middle_container .jackmail_radio_unchecked' ).removeClass( 'jackmail_radio_checked' );
						var current_id = event.currentTarget.id;
						current_reason = current_id.replace( /\D+/g, '' );
						jQuery( '.jackmail #' + current_id + '.jackmail_vertical_middle_container .jackmail_radio_unchecked' ).addClass( 'jackmail_radio_checked' );
						if ( current_id === 'jackmail_reason6' ) {
							jQuery( '.jackmail .jackmail_reason_other_detail' ).show();
							jQuery( '.jackmail .jackmail_reason_other_detail input' ).focus();
						}
						else {
							jQuery( '.jackmail .jackmail_reason_other_detail' ).hide();
							jQuery( '.jackmail .jackmail_reason_other_detail input' ).val( '' );
						}
					} );

					var jackmail_hide = function() {
						jQuery( '.jackmail .jackmail_vertical_middle_container .jackmail_radio_unchecked' ).removeClass( 'jackmail_radio_checked' );
						current_reason = '';
						jQuery( '.jackmail .jackmail_reason_other_detail' ).hide();
						jQuery( '.jackmail .jackmail_reason_other_detail input' ).val( '' );
						jQuery( '.jackmail' ).hide();
					};

				}
				else {
					jQuery( '.jackmail' ).show();
				}
			} );

			if ( window.location.href.indexOf( '?jackmail' ) !== -1 ) {
				jQuery( jackmail ).each( function() {
					jQuery( jackmail + ' th, ' + jackmail + ' td' ).css( { 'background': '#8FBB24' } );
					jQuery( jackmail + ':not(.plugin-update-tr) *' ).css( { 'color': 'white' } );
					jQuery( document ).scrollTop( jQuery( jackmail ).offset().top );
				} );
			}

		}

	} );

}