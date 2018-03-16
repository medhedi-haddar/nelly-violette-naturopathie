'use strict';

angular.module( 'jackmail.services' ).factory( 'CampaignCommonService', [
	'$rootScope', '$window', '$location', 'UrlService', 'EmailContentService', '$timeout', '$sce', 'VerificationService',
	function( $rootScope, $window, $location, UrlService, EmailContentService, $timeout, $sce, VerificationService ) {

		function change_url( vm ) {
			if ( vm.campaign_type === 'campaign' ) {
				var url = '/campaign/' + vm.url_id + '/' + vm.c_common.current_step_name;
			}
			else {
				var url = '/scenario/' + vm.c_common.campaign.send_option + '/' + vm.url_id + '/' + vm.c_common.current_step_name;
			}
			if ( $location.path() !== url ) {
				if ( $location.path().indexOf( '/0/' ) !== -1 && vm.url_id !== '0' ) {
					UrlService.change_url_parameters_without_reload_and_history( url );
				}
				else {
					UrlService.change_url_parameters_without_reload( url );
				}
			}
		}

		var service = {

			init_data: function( vm ) {
				$timeout( function() {
					vm.c_common.get_campaign_data( true );

					UrlService.get_file_data( $rootScope.settings.jackmail_url + 'libs/emoji_pretty_light3.json', function( data ) {
						vm.c_common.emojis = JSON.parse( data );
					}, function() {

					} );
					UrlService.post_data( 'jackmail_account_info', {}, function( data ) {
						vm.c_common.test_campaign_recipient = data.email;
						vm.c_common.account_data = data;
					}, function() {

					} );
				} );
			},

			create_list_and_go: function( vm ) {
				$rootScope.change_page_with_parameters( 'list', '0' );
			},

			show_hide_emojis_dropdown: function( vm ) {
				vm.c_common.display_emojis_dropdown = !vm.c_common.display_emojis_dropdown;
			},

			hide_emojis_dropdown: function( vm ) {
				vm.c_common.display_emojis_dropdown = false;
			},

			select_emoji_categorie: function( vm, id ) {
				vm.c_common.emoji_categorie_selected_key = id;
			},

			insert_emoji: function( vm, key ) {
				var i;
				var nb_emojis = vm.c_common.emojis.length;
				var j = 0;
				for ( i = 0; i < nb_emojis; i++ ) {
					if ( vm.c_common.emojis[ i ].category === vm.c_common.emoji_categorie_selected_key ) {
						if ( j === key ) {
							vm.c_common.campaign.object = vm.c_common.campaign.object + String.fromCodePoint( "0x" + vm.c_common.emojis[ i ].unified );
							break;
						}
						j++;
					}
				}
			},

			object_to_save: function( vm, object ) {
				object = object.replace( /&nbsp;/g, ' ' );
				
				object = object.replace( /<img draggable=\"false\" class=\"emoji\" alt=\"/g, '' );
				
				object = object.replace( /\" src=\"[a-zA-Z0-9\:\/\.]*\"\>/g, '' );
				object = angular.element( '<div>' + object + '</div>' ).text();
				return object;
			},

			object_trust_html: function( vm, object ) {
				return $sce.trustAsHtml( object );
			},

			set_created_campaign_info: function( vm, data ) {
				if ( data.id !== undefined ) {
					vm.url_id = data.id;
					vm.c_common.campaign.id = data.id;
				}
				vm.c_common.campaign.content_size = data.content_size;
				vm.c_common.campaign.content_images_size = data.content_images_size;
				if ( vm.c_common.campaign.content_email_json !== data.content_email_json ) {
					vm.c_common.campaign.content_email_json = data.content_email_json;
					EmailContentService.set_emailbuilder_json( vm.c_common.campaign.content_email_json );
				}
				if ( vm.c_common.campaign.content_email_html !== data.content_email_html ) {
					vm.c_common.campaign.content_email_html = data.content_email_html;
					EmailContentService.set_email_content_editor( vm.c_common.current_content_email_type, vm.c_common.campaign.content_email_html );
				}
				if ( vm.c_common.campaign.content_email_txt !== data.content_email_txt ) {
					vm.c_common.campaign.content_email_txt = data.content_email_txt;
					EmailContentService.set_email_content_editor( vm.c_common.current_content_email_type, vm.c_common.campaign.content_email_txt );
				}
				change_url( vm );
			},

			close_templates: function( vm ) {
				vm.c_common.show_templates = false;
				EmailContentService.display( vm.c_common.current_content_email_type );
			},

			import_template_in_campaign: function( vm, template_type, id ) {
				if ( template_type === 'template' ) {
					var data_parameters = {
						'id': id,
						'link_tracking': vm.c_common.campaign.link_tracking
					};
					var url = 'jackmail_get_template_json';
				}
				else {
					var data_parameters = {
						'gallery_id': id,
						'link_tracking': vm.c_common.campaign.link_tracking
					};
					var url = 'jackmail_get_gallery_template_json';
				}
				UrlService.post_data( url, data_parameters, function( data ) {
					var content = EmailContentService.init_and_display_emailbuilder( data.content_email_json );
					vm.c_common.campaign.content_email_json = content.content_email_json;
					vm.c_common.campaign.content_email_html = '';
					vm.c_common.campaign.content_email_txt = '';
					vm.c_common.current_content_email_type = content.current_content_email_type;
					vm.content_email_types = content.content_email_types;
					vm.c_common.show_templates = false;
				}, function() {

				} );
			},

			insert_subject_customize: function( vm, key, title ) {
				vm.c_common.campaign.object = vm.c_common.campaign.object + ' ((' + title + '))';
			},

			calculate_import_lists_grid_height: function( vm ) {
				grid_height();
				angular.element( $window ).on( 'resize', function() {
					grid_height();
				} );

				function grid_height() {
					var row_height = 54;
					var height = angular.element( $window ).height();
					var div_height = height - 350;
					var import_lists_grid_height = 0;
					if ( div_height < 160 ) {
						import_lists_grid_height = row_height * 3;
					}
					else {
						var nb_lists = vm.c_common.lists.length;
						if ( nb_lists < 3 ) {
							import_lists_grid_height = row_height * 3;
						}
						else {
							var max_rows = Math.floor( div_height / row_height );
							if ( nb_lists > max_rows ) {
								import_lists_grid_height = row_height * max_rows;
							}
							else {
								import_lists_grid_height = row_height * nb_lists;
							}
						}
					}
					vm.c_common.import_lists_grid_height = {
						'height': import_lists_grid_height + 'px'
					};
				}
			},

			focus_campaign_name: function( vm ) {
				angular.element( '.jackmail_name .jackmail_content_editable' ).focus();
				vm.common.name_editing = true;
			},

			blur_campaign_name: function( vm ) {
				if ( vm.c_common.campaign.name === '' ) {
					vm.c_common.campaign.name = $rootScope.translations.campaign_with_no_name;
				}
				vm.common.name_editing = false;
			},

			select_list: function( vm, key ) {
				vm.c_common.lists[ key ].selected = !vm.c_common.lists[ key ].selected;
				vm.c_common.selected_lists_total();
			},

			selected_lists_total: function( vm ) {
				var i;
				var nb = vm.c_common.lists.length;
				var nb_contacts_from_lists = 0;
				var nb_selected_lists = 0;
				for ( i = 0; i < nb; i++ ) {
					if ( vm.c_common.lists[ i ].selected ) {
						nb_contacts_from_lists = nb_contacts_from_lists + parseInt( vm.c_common.lists[ i ].nb_display_contacts );
						nb_selected_lists++;
					}
				}
				vm.c_common.nb_contacts_from_lists = nb_contacts_from_lists;
				vm.c_common.nb_selected_lists = nb_selected_lists;
				vm.c_common.campaign.id_lists = vm.c_common.get_selected_lists();
			},

			get_campaign_data: function( vm, url_get_data, url_post_types, url_categories, init ) {
				var data_parameters = {
					'id': vm.url_id
				};
				if ( vm.campaign_type === 'scenario' ) {
					data_parameters[ 'choice' ] = vm.only_scenario.scenario_choice;
				}
				UrlService.post_data( url_get_data, data_parameters, function( data ) {
					if ( data === null ) {
						$rootScope.go_page( 'campaigns' );
					}
					if ( !$rootScope.settings.emailbuilder_installed ) {
						if ( data.content_email_json !== '' ) {
							$rootScope.go_page( 'campaigns' );
						}
					}
					
					vm.c_common.campaign = data;
					if ( vm.c_common.campaign.link_tracking === '1' ) {
						EmailContentService.activate_link_tracking();
					}
					else {
						EmailContentService.deactivate_link_tracking();
					}
					if ( vm.url_id === '0' ) {
						if ( $rootScope.settings.emailbuilder_installed ) {
							vm.c_common.current_content_email_type = 'emailbuilder';
						}
						else {
							vm.c_common.current_content_email_type = 'html';
						}
						vm.c_common.content_email_types = '';
						vm.c_common.content_email_nb_links = '0';
						vm.c_common.content_email_unsubscribe_link = false;
						if ( vm.c_common.campaign.content_email_json !== '' ) {
							var content = EmailContentService.set( vm.c_common.campaign.content_email_json, vm.c_common.campaign.content_email_html, vm.c_common.campaign.content_email_txt );
							
						}
					}
					else {
						var content = EmailContentService.set( vm.c_common.campaign.content_email_json, vm.c_common.campaign.content_email_html, vm.c_common.campaign.content_email_txt );
						vm.c_common.current_content_email_type = content.current_content_email_type;
						vm.c_common.content_email_types = content.content_email_types;
						vm.c_common.content_email_nb_links = content.content_email_nb_links;
						vm.c_common.content_email_unsubscribe_link = content.content_email_unsubscribe_link;
					}
					vm.saved_campaign = angular.copy( vm.c_common.campaign );
					if ( init ) {
						if ( vm.campaign_type === 'scenario' ) {
							vm.c_common.get_lists_available();
							UrlService.post_data( url_post_types, {}, function( data ) {
								vm.only_scenario.post_type_available = vm.only_scenario.get_post_type_select( data );
							}, function() {

							} );
							UrlService.post_data( url_categories, {}, function( data ) {
								vm.only_scenario.post_categories_available = vm.only_scenario.get_post_categories_select( data );
							}, function() {

							} );
						}
						if ( vm.campaign_type === 'campaign' ) {
							vm.common.get_list_data();
						}
						else {
							vm.common.list_fields = [ 'EMAIL' ];
							vm.common.list_fields_plus = [ 'EMAIL', 'WEBCOPY_LINK', 'UNSUBSCRIBE_LINK' ];
							EmailContentService.update_list_fields( vm.common.list_fields_plus );
						}
					}
					else {
						vm.c_common.go_step( vm.c_common.current_step_name );
					}
				}, function() {

				} );
			},

			get_lists_available: function( vm, url_lists_available ) {
				var data_parameters = {
					'id': vm.c_common.campaign.id
				};
				UrlService.post_data( url_lists_available, data_parameters, function( data ) {
					vm.c_common.lists = data;
					if ( vm.campaign_type === 'campaign' ) {
						if ( vm.c_common.campaign ) {
							if ( vm.c_common.campaign.id_lists ) {
								var selected_lists = vm.c_common.get_selected_lists();
								if ( selected_lists !== vm.c_common.campaign.id_lists ) {
									vm.c_common.campaign.id_lists = selected_lists;
									vm.saved_campaign.id_lists = selected_lists;
								}
							}
						}
					}
					vm.c_common.selected_lists_total();
					if ( vm.campaign_type === 'scenario' ) {
						vm.c_common.display_import_lists();
					}
					vm.c_common.go_step( vm.c_common.current_step_name );
				}, function() {

				} );
			},

			go_step_correct_sender: function( vm, error ) {
				if ( error !== '' ) {
					vm.c_common.go_step( 'create' );
					$timeout( function() {
						angular.element( 'span[input-value="lc.c_common.campaign.sender_email"] span[ng-click="djce.show_editable()"] > span' ).click();
					}, 250 );
				}
			},

			go_step_correct_reply_to: function( vm, error ) {
				if ( error !== '' ) {
					vm.c_common.go_step( 'create' );
					if ( !vm.c_common.show_reply_to ) {
						vm.c_common.display_hide_reply_to();
					}
					$timeout( function() {
						angular.element( '.jackmail span[input-value="lc.c_common.campaign.reply_to_email"] span[ng-click="djce.show_editable()"] > span' ).click();
					}, 250 );
				}
			},

			go_step_correct_recipients: function( vm, error ) {
				if ( error !== '' ) {
					vm.c_common.go_step( 'contacts' );
					$timeout( function() {
						angular.element( '.jackmail .jackmail_grid_content tr:first-child td.jackmail_column_0_email input' ).focus();
					} );
				}
			},

			go_step_correct_object: function( vm, error ) {
				if ( error !== '' ) {
					vm.c_common.go_step( 'create' );
					$timeout( function() {
						angular.element( '.jackmail span[input-value="lc.c_common.campaign.object"] span[ng-click="djce.show_editable()"] > span' ).click();
					}, 250 );
				}
			},

			go_step_correct_content_email: function( vm, error ) {
				if ( error !== '' ) {
					vm.c_common.go_step( 'create' );
				}
			},

			go_step: function( vm, steps, step_name ) {
				if ( vm.campaign_type === 'campaign' && vm.c_common.current_step_name === 'contacts' && vm.common.show_grid === 0 && vm.show_import_lists ) {
					vm.only_campaign.import_selected_lists( step_name );
				}
				else {
					vm.c_common.campaign_data_checked = false;
					vm.c_common.campaign_data_analysis_checked = false;
					var step = steps.indexOf( step_name ) + 1;
					if ( step_name === 'create' ) {
						if ( !vm.c_common.show_templates ) {
							EmailContentService.display( vm.c_common.current_content_email_type );
						}
					}
					else {
						EmailContentService.hide_emailbuilder();
						if ( vm.campaign_type === 'scenario' ) {
							vm.c_common.campaign.nb_posts_content = EmailContentService.get_nb_posts_content();
						}
					}
					var current_step_name_temp = vm.c_common.current_step_name;
					if ( steps.indexOf( vm.c_common.current_step_name ) >= step ) {
						vm.c_common.current_step_name = step_name;
					}
					else {
						vm.c_common.current_step_name = step_name;
						if ( vm.c_common.current_step_name === 'checklist' ) {
							var save_needed = service.check_save_campaign_needed( vm );
							
							if ( !save_needed ) {
								vm.c_common.check_campaign_data_ws();
							}
						}
					}
					if ( current_step_name_temp !== vm.c_common.current_step_name ) {
						$rootScope.scroll_top();
					}
					change_url( vm );
				}
			},

			previous_step: function( vm, steps ) {
				var step_name = steps[ steps.indexOf( vm.c_common.current_step_name ) - 1 ];
				vm.c_common.go_step( step_name );
			},

			next_step: function( vm, steps ) {
				var step_name = steps[ steps.indexOf( vm.c_common.current_step_name ) + 1 ];
				vm.c_common.go_step( step_name );
			},

			save_campaign: function( vm, url_create, url_update, refresh_emailbuilder_changes ) {
				if ( refresh_emailbuilder_changes ) {
					vm.c_common.refresh_content_email();
				}
				if ( vm.campaign_type === 'scenario' ) {
					vm.c_common.campaign.nb_posts_content = EmailContentService.get_nb_posts_content();
				}
				if ( vm.url_id === '0' ) {
					var data_parameters = angular.copy( vm.c_common.campaign );
					data_parameters[ 'object' ] = vm.c_common.object_to_save( vm.c_common.campaign.object );
					UrlService.post_data( url_create, data_parameters, function( data ) {
						vm.c_common.save_campaign_success = data.success;
						if ( data.success ) {
							vm.c_common.set_created_campaign_info( data );
							vm.saved_campaign = angular.copy( vm.c_common.campaign );
						}
						$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_campaign_has_been_saved );
						vm.c_common.check_campaign_data_ws();
						
					}, function() {

					} );
				}
				else {
					vm.saved_campaign = angular.copy( vm.c_common.campaign );
					var data_parameters = angular.copy( vm.c_common.campaign );
					data_parameters[ 'object' ] = vm.c_common.object_to_save( vm.c_common.campaign.object );
					UrlService.post_data( url_update, data_parameters, function( data ) {
						vm.c_common.save_campaign_success = data.success;
						if ( data.success ) {
							vm.c_common.set_created_campaign_info( data );
						}
						$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_campaign_has_been_saved );
						vm.c_common.check_campaign_data_ws();
						
					}, function() {

					} );
				}
			},

			display_hide_reply_to: function( vm ) {
				vm.c_common.show_reply_to = !vm.c_common.show_reply_to;
				EmailContentService.hide_emailbuilder_menu();
				if ( vm.c_common.current_content_email_type === 'html' ) {
					var height = parseInt( angular.element( '.jackmail #jackmail_content_email > div' ).css( 'height' ) );
					if ( vm.c_common.show_reply_to ) {
						height = height - 50;
					}
					else {
						height = height + 50;
					}
					angular.element( '.jackmail #jackmail_content_email > div' ).css( { 'height': height + 'px' } );
				}
				$timeout( function() {
					EmailContentService.refresh_email_content_height();
				}, 500 );
			},

			change_current_content_email_type: function( vm, current_content_email_type ) {
				if ( vm.c_common.current_content_email_type !== current_content_email_type ) {
					var content = EmailContentService.change_current_content_email_type( vm.c_common.current_content_email_type, current_content_email_type, vm.c_common.campaign.content_email_json, vm.c_common.campaign.content_email_html, vm.c_common.campaign.content_email_txt );
					vm.c_common.campaign.content_email_json = content.content_email_json;
					vm.c_common.campaign.content_email_html = content.content_email_html;
					vm.c_common.campaign.content_email_txt = content.content_email_txt;
					vm.c_common.current_content_email_type = content.current_content_email_type;
					vm.c_common.content_email_types = content.content_email_types;
					vm.c_common.content_email_nb_links = content.content_email_nb_links;
					vm.c_common.content_email_unsubscribe_link = content.content_email_unsubscribe_link;
				}
			},

			insert_email_content_editor_customize: function( vm, key, title ) {
				
				EmailContentService.insert_email_content_editor_customize( vm.c_common.current_content_email_type, title );
			},

			get_selected_lists: function( vm ) {
				var i;
				var nb_lists = vm.c_common.lists.length;
				var selected_lists = [];
				for ( i = 0; i < nb_lists; i++ ) {
					if ( vm.c_common.lists[ i ].selected ) {
						selected_lists.push( vm.c_common.lists[ i ].id );
					}
				}
				selected_lists = $rootScope.join( selected_lists );
				return selected_lists;
			},

			display_import_lists: function( vm ) {
				vm.c_common.calculate_import_lists_grid_height();
				vm.common.show_grid = 0;
				vm.common_list_detail.show_list_contact_detail = false;
				vm.show_import_lists = true;
			},

			check_campaign_data_ws: function( vm, url_last_step_checker ) {
				if ( vm.c_common.current_step_name === 'checklist' ) {
					if ( vm.url_id === '0' ) {
						vm.c_common.check_campaign_data();
						vm.c_common.campaign_data_checked = true;
						vm.c_common.campaign_data_analysis_checked = true;
					}
					else {
						var data_parameters = {
							'id': vm.url_id
						};
						UrlService.post_data( url_last_step_checker, data_parameters, function( data ) {
							vm.shared_campaign.checked_campaign_data = data;
							vm.c_common.check_campaign_data();
							vm.c_common.campaign_data_checked = true;
							if ( vm.campaign_type === 'campaign' ) {
								UrlService.post_data_background( 'jackmail_campaign_last_step_checker_analysis', data_parameters, function( data ) {
									vm.shared_campaign.checked_campaign_data.analysis_checked = data.analysis_checked;
									vm.shared_campaign.checked_campaign_data.analysis = data.analysis;
									vm.c_common.campaign_data_analysis_checked = true;
								}, function() {

								} );
							}
						}, function() {

						} );
					}
				}
			},

			check_campaign_data: function( vm ) {
				var error = {
					'sender': '',
					'sender_warning': false,
					'reply_to': '',
					'recipients': '',
					'object': '',
					'content_email': '',
					'send_option_date': ''
				};
				var customized_columns_used = [];
				var customized_columns_unknown = [];
				if ( vm.c_common.campaign.sender_name === '' || vm.c_common.campaign.sender_email === '' ) {
					error.sender = $rootScope.translations.the_sender_field_is_required;
				}
				else if ( !VerificationService.email( vm.c_common.campaign.sender_email ) ) {
					error.sender = $rootScope.translations.a_valid_sender_email_address_is_required;
				}
				if ( error.sender === '' ) {
					if ( vm.c_common.campaign.sender_email.indexOf( '@outlook.' ) !== -1 ) {
						error.sender_warning = true;
					}
					else if ( vm.c_common.campaign.sender_email.indexOf( '@hotmail.' ) !== -1 ) {
						error.sender_warning = true;
					}
					else if ( vm.c_common.campaign.sender_email.indexOf( '@gmail.' ) !== -1 ) {
						error.sender_warning = true;
					}
					else if ( vm.c_common.campaign.sender_email.indexOf( '@yahoo.' ) !== -1 ) {
						error.sender_warning = true;
					}
				}
				if ( ( vm.c_common.campaign.reply_to_name === '' && vm.c_common.campaign.reply_to_email !== '' ) || ( vm.c_common.campaign.reply_to_name !== '' && vm.c_common.campaign.reply_to_email === '' ) ) {
					error.reply_to = $rootScope.translations.the_reply_to_field_is_required;
				}
				else if ( vm.c_common.campaign.reply_to_email !== '' ) {
					if ( !VerificationService.email( vm.c_common.campaign.reply_to_email ) ) {
						error.reply_to = $rootScope.translations.a_valid_reply_to_email_address_is_required;
					}
				}
				if ( vm.c_common.campaign.object === '' ) {
					error.object = $rootScope.translations.the_subject_field_is_required;
				}
				if ( vm.c_common.campaign.content_email_json === '' && vm.c_common.campaign.content_email_html === '' && vm.c_common.campaign.content_email_txt === '' ) {
					error.content_email = $rootScope.translations.the_message_field_is_required;
				}
				if ( !vm.c_common.campaign.content_size ) {
					error.content_email = $rootScope.translations.error_content_is_too_long;
				}
				if ( !vm.c_common.campaign.content_images_size ) {
					error.content_email = $rootScope.translations.error_images_are_too_large;
				}
				if ( vm.campaign_type === 'campaign' ) {
					if ( vm.shared_campaign.checked_campaign_data.nb_contacts_valids < 1 ) {
						error.recipients = $rootScope.translations.no_recipient_included_in_the_campaign;
					}
				}
				else {
					if ( vm.c_common.nb_selected_lists < 1 ) {
						error.recipients = $rootScope.translations.no_list_associated_in_the_campaign;
					}
				}
				vm.c_common.error = angular.copy( error );

				var i;
				var nb_list_fields = vm.common.list_fields.length;
				for ( i = 0; i < nb_list_fields; i++ ) {
					var column = vm.common.list_fields[ i ];
					var column_with_separator = '((' + column + '))';
					if ( ( $rootScope.html_to_text( vm.c_common.campaign.content_email_json + '' + vm.c_common.campaign.content_email_html + '' + vm.c_common.campaign.content_email_txt ) ).indexOf( column_with_separator ) !== -1 ) {
						customized_columns_used.push( column );
					}
				}
				vm.c_common.customized_columns_used = angular.copy( customized_columns_used );

				var regex_columns = /\(\(\s*(.*?)\s*\)\)/g;
				var result;
				while ( result = regex_columns.exec( $rootScope.html_to_text( vm.c_common.campaign.content_email_json + '' + vm.c_common.campaign.content_email_html + '' + vm.c_common.campaign.content_email_txt ) ) ) {
					var column = result[ 1 ];
					if ( column !== 'WEBCOPY_LINK' && column !== 'UNSUBSCRIBE_LINK' ) {
						if ( customized_columns_unknown.indexOf( column ) === -1 ) {
							if ( customized_columns_used.indexOf( column ) === -1 ) {
								customized_columns_unknown.push( column );
							}
						}
					}
				}
				vm.c_common.customized_columns_unknown = angular.copy( customized_columns_unknown );
			},

			check_campaign_data_input_value: function( vm, error ) {
				if ( error === '' ) {
					return $rootScope.translations.edit;
				}
				return $rootScope.translations.correct;
			},

			activate_link_tracking: function( vm ) {
				EmailContentService.activate_link_tracking();
				vm.c_common.campaign.link_tracking = '1';
				vm.c_common.save_campaign( true );
			},

			deactivate_link_tracking: function( vm ) {
				EmailContentService.deactivate_link_tracking();
				vm.c_common.campaign.link_tracking = '0';
				vm.c_common.save_campaign( true );
			},

			refresh_content_email: function( vm ) {
				var content = EmailContentService.refresh_content_email( vm.c_common.current_content_email_type, vm.c_common.campaign.content_email_json, vm.c_common.campaign.content_email_html, vm.c_common.campaign.content_email_txt );
				if ( content.content_email_changes ) {
					vm.c_common.content_email_changes = content.content_email_changes;
					vm.c_common.campaign.content_email_json = content.content_email_json;
					vm.c_common.campaign.content_email_html = content.content_email_html;
					vm.c_common.campaign.content_email_txt = content.content_email_txt;
					vm.c_common.content_email_types = content.content_email_types;
					vm.c_common.content_email_nb_links = content.content_email_nb_links;
					vm.c_common.content_email_unsubscribe_link = content.content_email_unsubscribe_link;
				}
			},

			send_test_check: function( vm ) {
				if ( vm.c_common.test_campaign_recipient === '' || !VerificationService.email( vm.c_common.test_campaign_recipient ) ) {
					if ( vm.c_common.test_campaign_recipient === '' ) {
						vm.c_common.send_test_status.error_email = $rootScope.translations.the_test_recipient_is_required;
					}
					else if ( !VerificationService.email( vm.c_common.test_campaign_recipient ) ) {
						vm.c_common.send_test_status.error_email = $rootScope.translations.a_valid_test_recipient_email_address_is_required;
					}
				}
				else {
					vm.c_common.send_test_status.error_email = '';
				}
				if ( vm.c_common.error.sender !== '' || vm.c_common.error.reply_to !== '' || vm.c_common.error.object !== '' || vm.c_common.error.content_email !== '' || vm.c_common.send_test_status.error_email !== '' ) {
					vm.c_common.send_test_status.campaign_ok = false;
				}
				else {
					vm.c_common.send_test_status.campaign_ok = true;
				}
				if ( !vm.shared_campaign.checked_campaign_data.nb_credits_checked || vm.shared_campaign.checked_campaign_data.nb_credits_before < 1 ) {
					vm.c_common.send_test_status.credits_ok = false;
				}
				else {
					vm.c_common.send_test_status.credits_ok = true;
				}
				if ( vm.c_common.send_test_status.error_email !== '' || !vm.c_common.send_test_status.campaign_ok || !vm.c_common.send_test_status.credits_ok ) {
					return false;
				}
				return true;
			},

			send_test_confirmation_validation: function( vm ) {
				if ( !vm.c_common.save_campaign_success ) {
					$rootScope.display_error( $rootScope.translations.error_while_save_campaign );
					vm.c_common.display_send_test_confirmation = false;
				}
				else if ( !$rootScope.settings.openssl_random_pseudo_bytes_extension_function_exists ) {
					$rootScope.display_error( $rootScope.translations.please_activate_the_extension_openssl_random_pseudo_bytes_on_your_web_server );
					vm.c_common.display_send_test_confirmation = false;
				}
				else if ( !$rootScope.settings.gzdecode_gzencode_function_exists && display_confirmation === '' ) {
					$rootScope.display_error( $rootScope.translations.gzdecode_or_gzencode_php_function_not_found );
					vm.c_common.display_send_test_confirmation = false;
				}
				else if ( !$rootScope.settings.base64_decode_base64_encode_function_exists && display_confirmation === '' ) {
					$rootScope.display_error( $rootScope.translations.base64_encode_or_base64_decode_php_function_not_found );
					vm.c_common.display_send_test_confirmation = false;
				}
				else if ( !$rootScope.settings.json_encode_json_decode_function_exists && display_confirmation === '' ) {
					$rootScope.display_error( $rootScope.translations.json_encode_json_decode_php_function_not_found );
					vm.c_common.display_send_test_confirmation = false;
				}
				else if ( vm.c_common.send_test_check() ) {
					vm.c_common.display_send_test_confirmation = true;
				}
				else {
					vm.c_common.display_send_test_confirmation = false;
					if ( vm.c_common.send_test_status.error_email !== '' ) {
						$rootScope.display_error( vm.c_common.send_test_status.error_email );
					}
					else if ( vm.c_common.error.sender !== '' ) {
						$rootScope.display_error( vm.c_common.error.sender );
					}
					else if ( vm.c_common.error.reply_to !== '' ) {
						$rootScope.display_error( vm.c_common.error.reply_to );
					}
					else if ( vm.c_common.error.object !== '' ) {
						$rootScope.display_error( vm.c_common.error.object );
					}
					else if ( vm.c_common.error.content_email !== '' ) {
						$rootScope.display_error( vm.c_common.error.content_email );
					}
					else if ( !vm.shared_campaign.checked_campaign_data.nb_credits_checked ) {
						if ( !$rootScope.settings.is_authenticated ) {
							$rootScope.display_error( $rootScope.translations.you_must_login_or_create_a_jackmail_account );
							$rootScope.display_account_connection_popup( 'create' );
						}
						else {
							$rootScope.display_error( $rootScope.translations.error_while_checking_credits_available );
						}
					}
					else if ( vm.shared_campaign.checked_campaign_data.nb_credits_before < 1 ) {
						$rootScope.display_error( $rootScope.translations.you_don_t_have_enough_credits_available );
					}
					else {
						$rootScope.display_error( $rootScope.translations.one_or_more_campaign_fields_are_missing );
					}
				}
			},

			send_test: function( vm, url_send_test ) {
				var data_parameters = {
					'id': vm.url_id,
					'test_recipient': vm.c_common.test_campaign_recipient
				};
				UrlService.post_data( url_send_test, data_parameters, function( data ) {
					vm.c_common.check_campaign_data();
					if ( data.success ) {
						$rootScope.display_success( $rootScope.translations.the_test_email_has_been_sent );
						vm.shared_campaign.checked_campaign_data.nb_credits_before = ( vm.shared_campaign.checked_campaign_data.nb_credits_before - 1 ).toString();
						vm.shared_campaign.checked_campaign_data.nb_credits_after = ( vm.shared_campaign.checked_campaign_data.nb_credits_after - 1 ).toString();
					}
					else {
						var message = CampaignService.get_send_campaign_message( data.message );
						$rootScope.display_error( message );
					}
					vm.c_common.display_send_test_confirmation = false;
				}, function() {

				} );
			},

			cancel_test: function( vm ) {
				vm.c_common.display_send_test_confirmation = false;
			},

			check_save_campaign_needed: function( vm ) {
				var save = false;
				if ( !$rootScope.show_help2 ) {
					if ( !vm.c_common.sending_campaign ) {
						if ( $rootScope.nb_loading === 0 ) {
							vm.c_common.refresh_content_email();
							if ( vm.url_id !== '0' || vm.c_common.campaign.name !== $rootScope.translations.campaign_with_no_name || vm.c_common.content_email_changes
								|| vm.c_common.campaign.sender_email !== vm.c_common.account_info.email
								|| vm.c_common.campaign.sender_name !== ( vm.c_common.account_info.firstname + ' ' + vm.c_common.account_info.lastname )
								|| vm.c_common.campaign.reply_to_email !== '' || vm.c_common.campaign.reply_to_name !== ''
								|| vm.c_common.campaign.object !== ''
								|| vm.c_common.campaign.content_email_json !== '' || vm.c_common.campaign.content_email_html !== '' || vm.c_common.campaign.content_email_txt !== '' ) {
								if ( VerificationService.differents_arrays( vm.c_common.campaign, vm.saved_campaign ) ) {
									vm.c_common.save_campaign( false );
									save = true;
								}
							}
							else if ( vm.campaign_type === 'scenario' && vm.c_common.campaign.id_lists !== vm.saved_campaign.id_lists ) {
								vm.c_common.save_campaign( false );
								save = true;
							}
						}
					}
				}
				return save;
			},

			on_pop_state: function( vm, steps ) {
				$timeout( function() {
					var url = $location.path().split( '/' );
					var step_name = steps[ steps.indexOf( url[ url.length - 1 ] ) ];
					if ( step_name !== vm.c_common.current_step_name ) {
						vm.c_common.go_step( step_name );
					}
				} );
			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'ScenarioService', [
	'$rootScope', '$window', '$location', 'UrlService', 'EmailContentService', '$filter',
	function( $rootScope, $window, $location, UrlService, EmailContentService, $filter ) {

		function scenario_after_activate_or_deactivate( vm, data, message_ok, message_error ) {
			vm.only_scenario.activating_or_desactivating_scenario = false;
			$rootScope.display_success_error( data.success, message_ok, message_error );
			if ( data.success ) {
				if ( vm.c_common.campaign.status === 'ACTIVED' ) {
					vm.c_common.campaign.status = 'DRAFT';
				}
				else {
					vm.c_common.campaign.status = 'ACTIVED';
				}
			}
			else {
				vm.c_common.get_campaign_data( false );
			}
		}

		var service = {

			post_type_check_unckeck: function( vm, key ) {
				var i;
				var nb_post_type_available = vm.only_scenario.post_type_available.length;
				for ( i = 0; i < nb_post_type_available; i++ ) {
					if ( i !== key ) {
						vm.only_scenario.post_type_available[ i ].checked = false;
					}
				}
				vm.only_scenario.post_type_available[ key ].checked = true;
				vm.c_common.campaign.post_type = vm.only_scenario.post_type_available[ key ].id;
				vm.only_scenario.post_type_selected = vm.only_scenario.post_type_available[ key ].label;

				var nb_post_categories_available = vm.only_scenario.post_categories_available.length;
				vm.only_scenario.post_categories_available[ 0 ].checked = true;
				for ( i = 0; i < nb_post_categories_available; i++ ) {
					if ( i !== 0 ) {
						vm.only_scenario.post_categories_available[ i ].checked = true;
					}
				}
				vm.c_common.campaign.post_categories = '[]';
				vm.only_scenario.nb_selected_post_categories = 0;
				vm.only_scenario.selected_post_categories_title = vm.only_scenario.get_selected_post_categories_title();
			},

			get_post_type_select: function( vm, data ) {
				var i;
				var nb_post_type_available = data.length;
				if ( vm.only_scenario.post_type_selected === '' && nb_post_type_available > 0 ) {
					vm.only_scenario.post_type_selected = data[ 0 ].label;
				}
				for ( i = 0; i < nb_post_type_available; i++ ) {
					data[ i ].name = data[ i ].label;
					if ( vm.c_common.campaign.post_type === data[ i ].name ) {
						data[ i ].checked = true;
					}
					else {
						data[ i ].checked = false;
					}
				}
				return data;
			},

			post_categories_check_unckeck: function( vm, key ) {
				var post_categories = [];
				var nb_selected_post_categories = 0;
				var i;
				var nb_post_categories_available = vm.only_scenario.post_categories_available.length;
				if ( key === 0 ) {
					vm.only_scenario.post_categories_available[ 0 ].checked = !vm.only_scenario.post_categories_available[ 0 ].checked;
					var all_checked = false;
					if ( vm.only_scenario.post_categories_available[ 0 ].checked ) {
						all_checked = true;
					}
					for ( i = 0; i < nb_post_categories_available; i++ ) {
						if ( i !== 0 ) {
							vm.only_scenario.post_categories_available[ i ].checked = true;
							if ( !all_checked ) {
								post_categories.push( vm.only_scenario.post_categories_available[ i ].id );
								nb_selected_post_categories++;
							}
						}
					}
				}
				else {
					vm.only_scenario.post_categories_available[ key ].checked = !vm.only_scenario.post_categories_available[ key ].checked;
					if ( !vm.only_scenario.post_categories_available[ key ].checked ) {
						vm.only_scenario.post_categories_available[ 0 ].checked = false;
					}
					for ( i = 0; i < nb_post_categories_available; i++ ) {
						if ( i !== 0 ) {
							if ( vm.only_scenario.post_categories_available[ i ].checked ) {
								post_categories.push( vm.only_scenario.post_categories_available[ i ].id );
								nb_selected_post_categories++;
							}
						}
					}
					if ( nb_selected_post_categories === 0 ) {
						vm.only_scenario.post_categories_available[ 0 ].checked = true;
						for ( i = 0; i < nb_post_categories_available; i++ ) {
							if ( i !== 0 ) {
								vm.only_scenario.post_categories_available[ i ].checked = true;
							}
						}
					}
				}
				vm.c_common.campaign.post_categories = $rootScope.join( post_categories );
				vm.only_scenario.nb_selected_post_categories = nb_selected_post_categories;
				vm.only_scenario.selected_post_categories_title = vm.only_scenario.get_selected_post_categories_title();
			},

			get_post_categories_select: function( vm, data ) {
				var nb_selected_post_categories = 0;
				var post_categories = $rootScope.split( vm.c_common.campaign.post_categories );
				var nb_post_categories = post_categories.length;
				var nb_post_categories_available = data.length;
				var all_checked = false;
				if ( nb_post_categories === 0 ) {
					all_checked = true;
					for ( i = 0; i < nb_post_categories_available; i++ ) {
						data[ i ].checked = true;
						data[ i ].name = data[ i ].label;
					}
				}
				else {
					var i;
					for ( i = 0; i < nb_post_categories_available; i++ ) {
						data[ i ].name = data[ i ].label;
						if ( post_categories.indexOf( data[ i ].id ) !== -1 ) {
							data[ i ].checked = true;
							nb_selected_post_categories++;
						}
						else {
							data[ i ].checked = false;
						}
					}
				}
				data.unshift( { 'id': 0, 'name': $rootScope.translations.all_categories, 'checked': all_checked } );
				vm.only_scenario.nb_selected_post_categories = nb_selected_post_categories;
				vm.only_scenario.selected_post_categories_title = vm.only_scenario.get_selected_post_categories_title();
				return data;
			},

			get_selected_post_categories_title: function( vm ) {
				if ( vm.c_common.campaign.post_categories === '[]' ) {
					return $rootScope.translations.all_categories;
				}
				else if ( vm.only_scenario.nb_selected_post_categories === 1 ) {
					return vm.only_scenario.nb_selected_post_categories + ' ' + $rootScope.translations.selected_category;
				}
				else if ( vm.only_scenario.nb_selected_post_categories > 1 ) {
					return vm.only_scenario.nb_selected_post_categories + ' ' + $rootScope.translations.selected_categories;
				}
				return '';
			},

			activate_scenario_confirmation_validation: function( vm ) {
				var display_confirmation = '';
				if ( !vm.c_common.save_campaign_success ) {
					display_confirmation = $rootScope.translations.error_while_save_campaign;
				}
				else if ( !$rootScope.settings.openssl_random_pseudo_bytes_extension_function_exists ) {
					display_confirmation = $rootScope.translations.please_activate_the_extension_openssl_random_pseudo_bytes_on_your_web_server;
				}
				else if ( !$rootScope.settings.gzdecode_gzencode_function_exists ) {
					display_confirmation = $rootScope.translations.gzdecode_or_gzencode_php_function_not_found;
				}
				else if ( !$rootScope.settings.base64_decode_base64_encode_function_exists ) {
					display_confirmation = $rootScope.translations.base64_encode_or_base64_decode_php_function_not_found;
				}
				else if ( !$rootScope.settings.json_encode_json_decode_function_exists ) {
					display_confirmation = $rootScope.translations.json_encode_json_decode_php_function_not_found;
				}
				else if ( vm.c_common.error.sender !== '' ) {
					display_confirmation = vm.c_common.error.sender;
				}
				else if ( vm.c_common.error.reply_to !== '' ) {
					display_confirmation = vm.c_common.error.reply_to;
				}
				else if ( vm.c_common.error.recipients !== '' ) {
					display_confirmation = vm.c_common.error.recipients;
				}
				else if ( vm.c_common.error.object !== '' ) {
					display_confirmation = vm.c_common.error.object;
				}
				else if ( vm.c_common.error.content_email !== '' ) {
					display_confirmation = vm.c_common.error.content_email;
				}
				else if ( !$rootScope.settings.is_authenticated ) {
					display_confirmation = $rootScope.translations.you_must_login_or_create_a_jackmail_account;
					$rootScope.display_account_connection_popup( 'create' );
				}
				if ( display_confirmation !== '' ) {
					$rootScope.display_error( display_confirmation );
				}
				else {
					if ( vm.url_id !== '0' ) {
						$rootScope.display_validation(
							$rootScope.translations.activate_scenario,
							function() {
								vm.only_scenario.activating_or_desactivating_scenario = true;
								var data_parameters = {
									'id': vm.c_common.campaign.id
								};
								UrlService.post_data( 'jackmail_activate_scenario', data_parameters, function( data ) {
									scenario_after_activate_or_deactivate( vm, data, $rootScope.translations.the_scenario_was_activated, data.message );
								}, function() {

								} );
							}
						);
					}
					else {
						$rootScope.display_error( $rootScope.translations.an_error_occurred );
					}
				}
			},

			deactivate_scenario_confirmation_validation: function( vm ) {
				if ( vm.url_id !== '0' ) {
					$rootScope.display_validation(
						$rootScope.translations.deactivate_scenario,
						function() {
							vm.only_scenario.activating_or_desactivating_scenario = true;
							var data_parameters = {
								'id': vm.c_common.campaign.id
							};
							UrlService.post_data( 'jackmail_deactivate_scenario', data_parameters, function( data ) {
								scenario_after_activate_or_deactivate( vm, data, $rootScope.translations.the_scenario_was_disabled, '' );
							}, function() {

							} );
						}
					);
				}
				else {
					$rootScope.display_error( $rootScope.translations.an_error_occurred );
				}
			},

			display_current_content_email_type_or_template: function( vm, key ) {
				if ( key === 0 ) {
					vm.c_common.change_current_content_email_type( 'emailbuilder' );
				}
				else if ( key === 1 ) {
					vm.c_common.change_current_content_email_type( 'txt' );
				}
				else if ( key === 2 || key === 3 ) {
					vm.c_common.show_templates = true;
					$rootScope.get_templates_from_campaign_page( key === 2 ? 'templates_gallery' : 'templates' );
					EmailContentService.hide_emailbuilder();
				}
			},

			publish_a_post: {

				select_periodicity_value: function( vm, key ) {
					if ( vm.c_common.campaign.periodicity_type === 'HOURS' ) {
						vm.c_common.campaign.periodicity_value = vm.shared_scenario.settings_hours_choice[ key ];
					}
					else if ( vm.c_common.campaign.periodicity_type === 'DAYS' ) {
						vm.c_common.campaign.periodicity_value = vm.shared_scenario.settings_days_choice[ key ];
					}
				},

				select_periodicity_type: function( vm, key ) {
					if ( key === 0 ) {
						vm.c_common.campaign.periodicity_type = 'HOURS';
					}
					else {
						if ( vm.c_common.campaign.periodicity_value > 5 ) {
							vm.c_common.campaign.periodicity_value = '5';
						}
						vm.c_common.campaign.periodicity_type = 'DAYS';
					}
				},

				change_periodicity_type: function( vm, choice ) {
					vm.c_common.campaign.periodicity_value = '1';
					vm.c_common.campaign.periodicity_type = choice;
				}

			},

			automated_newsletter: {

				select_periodicity_value: function( vm, key ) {
					if ( vm.c_common.campaign.periodicity_type === 'DAYS' ) {
						vm.c_common.campaign.periodicity_value = vm.shared_scenario.settings_days_choice[ key ];
					}
					else if ( vm.c_common.campaign.periodicity_type === 'MONTHS' ) {
						vm.c_common.campaign.periodicity_value = vm.shared_scenario.settings_months_choice[ key ];
					}
				},

				select_periodicity_type: function( vm, key ) {
					if ( key === 0 ) {
						vm.c_common.campaign.periodicity_type = 'DAYS';
					}
					else {
						if ( vm.c_common.campaign.periodicity_value > 12 ) {
							vm.c_common.campaign.periodicity_value = '12';
						}
						vm.c_common.campaign.periodicity_type = 'MONTHS';
					}
				},

				change_periodicity_type: function( vm, choice ) {
					if ( choice === 'POSTS' ) {
						vm.c_common.campaign.periodicity_value = '5';
						vm.c_common.campaign.event_date_gmt = $rootScope.settings.current_time;
					}
					else {
						vm.c_common.campaign.periodicity_value = '1';
						var event_date_gmt = $rootScope.settings.current_time.substring( 0, 10 ) + ' 12:00:00';
						event_date_gmt = $filter( 'formatedDate' )( event_date_gmt, 'timezone_to_gmt', 'sql' );
						vm.c_common.campaign.event_date_gmt = $rootScope.add_date_interval( event_date_gmt, 86400 );
					}
					vm.c_common.campaign.periodicity_type = choice;
				},

				change_nb_posts_periodicity_value: function( vm, key ) {
					vm.c_common.campaign.periodicity_value = vm.shared_scenario.settings_nb_posts_periodicity_value_choice[ key ];
				},

				change_nb_posts_content: function( vm, key ) {
					vm.c_common.campaign.nb_posts_content = vm.shared_scenario.settings_nb_posts_content_choice[ key ];
					EmailContentService.set_nb_posts_content( vm.c_common.campaign.nb_posts_content );
				},

				change_event_date: function( vm, date ) {
					vm.c_common.campaign.event_date_gmt = date;
				}

			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'CampaignService', [
	'$rootScope', '$window', '$location', 'UrlService', 'EmailContentService', '$filter',
	function( $rootScope, $window, $location, UrlService, EmailContentService, $filter ) {

		function save_as_template( vm ) {
			if ( vm.c_common.current_content_email_type === 'emailbuilder' ) {
				vm.c_common.refresh_content_email();
				var data_parameters = {
					'name': $rootScope.translations.template_from + ' "' + vm.c_common.campaign.name + '"',
					'content_email_json': vm.c_common.campaign.content_email_json,
					'content_email_html': vm.c_common.campaign.content_email_html,
					'content_email_txt': ''
				};
				UrlService.post_data( 'jackmail_create_template', data_parameters, function( data ) {
					$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_template_was_saved );
				}, function() {

				} );
			}
		}

		var service = {

			get_send_campaign_message: function( message ) {
				var error = $rootScope.translations.an_error_occurred;
				if ( message === 'ERROR_READ_EMAIL_CONTENT_FILE' ) {
					error = $rootScope.translations.an_error_occurred_the_campaign_was_not_found;
				}
				else if ( message === 'FORBIDDEN' ) {
					error = $rootScope.translations.the_user_name_or_the_password_is_incorrect;
				}
				else if ( message === 'MESSAGE_CONTENT_IS_TOO_LARGE' ) {
					error = $rootScope.translations.error_content_is_too_long;
				}
				else if ( message === 'MESSAGE_IMAGES_SIZE_IS_TOO_LARGE' ) {
					error = $rootScope.translations.error_images_are_too_large;
				}
				else if ( message === 'MISSING_PHP_OPENSSL' ) {
					error = $rootScope.translations.please_activate_the_extension_openssl_random_pseudo_bytes_on_your_web_server;
				}
				else if ( message === 'MISSING_GZDECODE_OR_GZENCODE' ) {
					error = $rootScope.translations.gzdecode_or_gzencode_php_function_not_found;
				}
				else if ( message === 'MISSING_BASE64_ENCODE_OR_BASE64_DECODE' ) {
					error = $rootScope.translations.base64_encode_or_base64_decode_php_function_not_found;
				}
				else if ( message === 'MISSING_JSON_ENCODE_OR_JSON_DECODE' ) {
					error = $rootScope.translations.json_encode_or_json_decode_php_function_not_found;
				}
				else if ( message === 'ERROR_WHILE_LOADING_IMAGES' ) {
					error = $rootScope.translations.error_while_loading_images;
				}
				else if ( message === 'ERROR_WHILE_LOADING_CONTENT' ) {
					error = $rootScope.translations.error_while_loading_content;
				}
				else if ( message === 'NOT_ENOUGH_CREDITS' ) {
					error = $rootScope.translations.not_enough_credits;
				}
				else if ( message === 'ERROR_WHILE_CHECKING_CREDITS' ) {
					error = $rootScope.translations.error_while_checking_credits_available;
				}
				else if ( message === 'NO_VALIDS_RECIPIENTS' ) {
					error = $rootScope.translations.no_valids_recipients;
				}
				else if ( message === 'NB_DISPLAYED_CONTACTS' ) {
					error = $rootScope.translations.data_displayed_are_not_up_to_date_click_ok_to_reload_it_before_you_send_the_campaign;
					if ( vm.c_common.campaign.send_option !== 'NOW' && !vm.c_common.sending_campaign ) {
						error = $rootScope.translations.data_displayed_are_not_up_to_date_click_ok_to_reload_it_before_you_schedule_the_campaign;
					}
				}
				else {
					error = $rootScope.translations.an_error_occurred + ' (' + message + ')';
				}
				return error;
			},

			import_selected_lists: function( vm, url_create, url_update, step_name ) {
				vm.show_import_lists = false;
				if ( vm.url_id === '0' ) {
					if ( vm.c_common.nb_selected_lists > 0 ) {
						vm.c_common.refresh_content_email();
						$rootScope.display_success( $rootScope.translations.import_contacts );
						UrlService.post_data( url_create, vm.c_common.campaign, function( data ) {
							if ( data.success ) {
								vm.c_common.set_created_campaign_info( data );
								import_selected_lists();
							}
							else {
								$rootScope.display_error_writable();
							}
						}, function() {

						} );
					}
					else {
						vm.c_common.go_step( step_name );
					}
				}
				else {
					vm.c_common.refresh_content_email();
					$rootScope.display_success( $rootScope.translations.import_contacts );
					UrlService.post_data( url_update, vm.c_common.campaign, function( data ) {
						if ( data.success ) {
							import_selected_lists();
						}
						else {
							$rootScope.display_error_writable();
						}
					}, function() {

					} );
				}

				function import_selected_lists() {
					var data_parameters = {
						'id_campaign': vm.url_id,
						'id_lists': vm.c_common.campaign.id_lists
					};
					if ( vm.c_common.campaign.id_lists !== vm.saved_campaign.id_lists ) {
						$rootScope.display_success( $rootScope.translations.import_contacts );
						UrlService.post_data( 'jackmail_set_campaign_lists', data_parameters, function() {
							vm.c_common.get_campaign_data( false );
							after_import();
						}, function() {

						} );
					}
					else {
						after_import();
					}
				}

				function after_import() {
					vm.common.get_list_data_reset();
					vm.c_common.go_step( step_name );
				}

			},

			hide_grid: function( vm ) {
				vm.common.show_grid = 0;
				vm.common_list_detail.show_list_contact_detail = false;
				vm.show_import_lists = false;
			},

			change_campaign_option: function( vm, option ) {
				if ( vm.c_common.campaign.send_option !== option ) {
					vm.c_common.campaign.send_option = option;
					vm.c_common.campaign.send_option_date_begin_gmt = '0000-00-00 00:00:00';
					vm.c_common.campaign.send_option_date_end_gmt = '0000-00-00 00:00:00';
					$rootScope.scroll_bottom();
				}
			},

			change_send_option_date: function( vm, date1, date2 ) {
				vm.c_common.campaign.send_option_date_begin_gmt = date1;
				if ( date2 ) {
					vm.c_common.campaign.send_option_date_end_gmt = date2;
				}
				else {
					vm.c_common.campaign.send_option_date_end_gmt = date1;
				}
			},

			send_campaign_confirmation_validation: function( vm, type_send ) {
				var display_confirmation = '';
				if ( !vm.c_common.save_campaign_success ) {
					display_confirmation = $rootScope.translations.error_while_save_campaign;
				}
				else if ( !$rootScope.settings.openssl_random_pseudo_bytes_extension_function_exists ) {
					display_confirmation = $rootScope.translations.please_activate_the_extension_openssl_random_pseudo_bytes_on_your_web_server;
				}
				else if ( !$rootScope.settings.gzdecode_gzencode_function_exists ) {
					display_confirmation = $rootScope.translations.gzdecode_or_gzencode_php_function_not_found;
				}
				else if ( !$rootScope.settings.base64_decode_base64_encode_function_exists ) {
					display_confirmation = $rootScope.translations.base64_encode_or_base64_decode_php_function_not_found;
				}
				else if ( !$rootScope.settings.json_encode_json_decode_function_exists ) {
					display_confirmation = $rootScope.translations.json_encode_json_decode_php_function_not_found;
				}
				else if ( vm.c_common.error.sender !== '' ) {
					display_confirmation = vm.c_common.error.sender;
				}
				else if ( vm.c_common.error.reply_to !== '' ) {
					display_confirmation = vm.c_common.error.reply_to;
				}
				else if ( vm.c_common.error.recipients !== '' ) {
					display_confirmation = vm.c_common.error.recipients;
				}
				else if ( vm.c_common.error.object !== '' ) {
					display_confirmation = vm.c_common.error.object;
				}
				else if ( vm.c_common.error.content_email !== '' ) {
					display_confirmation = vm.c_common.error.content_email;
				}
				else if ( ( vm.c_common.campaign.send_option !== 'NOW' && ( vm.c_common.campaign.send_option_date_begin_gmt === '0000-00-00 00:00:00' || vm.c_common.campaign.send_option_date_end_gmt === '0000-00-00 00:00:00' ) ) ) {
					vm.c_common.error.send_option_date = $rootScope.translations.selected_date_is_not_valid;
					display_confirmation = vm.c_common.error.send_option_date;
				}
				if ( type_send === 'send' ) {
					if ( !vm.shared_campaign.checked_campaign_data.nb_credits_checked && display_confirmation === '' ) {
						if ( !$rootScope.settings.is_authenticated ) {
							display_confirmation = $rootScope.translations.you_must_login_or_create_a_jackmail_account;
							$rootScope.display_account_connection_popup( 'create' );
						}
						else {
							display_confirmation = $rootScope.translations.error_while_checking_credits_available;
						}
					}
					else if ( vm.shared_campaign.checked_campaign_data.nb_credits_after < 0 && display_confirmation === '' ) {
						display_confirmation = $rootScope.translations.you_don_t_have_enough_credits_available;
					}
				}
				if ( display_confirmation !== '' ) {
					$rootScope.display_error( display_confirmation );
				}
				else {
					if ( vm.url_id !== '0' ) {
						var recipients = $filter( 'numberSeparator' )( vm.shared_campaign.checked_campaign_data.nb_contacts_valids ) + ' ';
						if ( vm.shared_campaign.checked_campaign_data.nb_contacts_valids <= 1 ) {
							recipients += $rootScope.translations.recipient;
						}
						else {
							recipients += $rootScope.translations.recipients;
						}
						var message_title = '';
						var message_confirmation = '';
						if ( type_send === 'program' ) {
							message_title = $rootScope.translations.scheduled_sending_confirmation;
							message_confirmation = $rootScope.translations.the_campaign_named + ' "' + vm.c_common.campaign.name + '" ' + $rootScope.translations.will_be_scheduled_to + ' ' + recipients + '. ' + $rootScope.translations.do_you_confirm_this_scheduling;
						}
						else if ( type_send === 'send' ) {
							message_title = $rootScope.translations.sending_confirmation;
							message_confirmation = $rootScope.translations.the_campaign_named + ' "' + vm.c_common.campaign.name + '" ' + $rootScope.translations.will_be_sent_to + ' ' + recipients + '. ' + $rootScope.translations.do_you_confirm_this_sending;
						}
						if ( message_title !== '' && message_confirmation !== '' ) {
							$rootScope.display_validation(
								'<span class="jackmail_title">' +
								'	' + message_title +
								'</span>' +
								'<br/><br/>' +
								'<span class="jackmail_campaign_confirmation_message jackmail_grey">' +
								'	' + message_confirmation +
								'</span>',
								function() {
									vm.c_common.sending_campaign = true;
									var data_parameters = {
										'id': vm.c_common.campaign.id,
										'send_option': vm.c_common.campaign.send_option,
										'send_option_date_begin_gmt': vm.c_common.campaign.send_option_date_begin_gmt,
										'send_option_date_end_gmt': vm.c_common.campaign.send_option_date_end_gmt,
										'nb_contacts_valids_displayed': vm.shared_campaign.checked_campaign_data.nb_contacts_valids
									};
									UrlService.post_data( 'jackmail_send_campaign', data_parameters, function( data ) {
										if ( data.success ) {
											if ( $rootScope.settings.analytics ) {
												$window.ga( 'send', {
													'hitType': 'event',
													'appName': 'Jackmail',
													'appVersion': $rootScope.settings.version,
													'screenName': $location.url(),
													'eventCategory': 'Send campaign',
													'eventLabel': 'Send campaign',
													'eventAction': 'click',
													'eventValue': parseInt( vm.shared_campaign.checked_campaign_data.nb_contacts_valids )
												} );
											}
											$rootScope.go_page( 'campaigns' );
										}
										else {
											vm.c_common.sending_campaign = false;
											var error_message = service.get_send_campaign_message( data.message );
											if ( data.message === 'NB_DISPLAYED_CONTACTS' ) {
												$rootScope.display_validation(
													error_message,
													function() {
														vm.check_campaign_data();
														$rootScope.cancel_validation();
													}
												);
											}
											else {
												$rootScope.display_error( error_message );
											}
										}
									}, function() {
										vm.c_common.sending_campaign = false;
									} );
								}
							);
						}
					}
					else {
						$rootScope.display_error( $rootScope.translations.an_error_occurred );
					}
				}
			},

			save_campaign_or_create_template: function( vm, key ) {
				if ( key === 0 ) {
					vm.c_common.save_campaign( true );
				}
				else {
					save_as_template( vm );
				}
			},

			reset_emailbuilder_content: function( vm ) {
				$rootScope.display_validation( $rootScope.translations.you_will_loose_your_emailbuilder_content, function() {
					EmailContentService.set_emailbuilder_json( 'reset' );
					vm.c_common.refresh_content_email();
				} );
			},

			not_enought_credits_link: function( vm ) {
				if ( $rootScope.settings.analytics ) {
					$window.ga( 'send', {
						'hitType': 'event',
						'appName': 'Jackmail',
						'appVersion': $rootScope.settings.version,
						'screenName': $location.url(),
						'eventCategory': 'Purchase more credits',
						'eventLabel': 'Purchase more credits',
						'eventAction': 'click',
						'eventValue': 0
					} );
				}
			},

			display_current_content_email_type_or_template: function( vm, key ) {
				if ( !$rootScope.settings.emailbuilder_installed && ( key === 0 || key === 3 || key === 4 ) ) {
					$rootScope.display_emailbuilder_popup();
				}
				else {
					vm.c_common.refresh_content_email();
					if ( key === 0 ) {
						if ( vm.c_common.campaign.content_email_html !== '' ) {
							$rootScope.display_validation( $rootScope.translations.you_will_loose_your_html_content, function() {
								vm.c_common.change_current_content_email_type( 'emailbuilder' );
							} );
						}
						else {
							vm.c_common.change_current_content_email_type( 'emailbuilder' );
						}
					}
					else if ( key === 1 ) {
						if ( vm.c_common.campaign.content_email_json !== '' ) {
							$rootScope.display_validation( $rootScope.translations.you_will_loose_your_emailbuilder_content, function() {
								vm.c_common.change_current_content_email_type( 'html' );
							} );
						}
						else {
							vm.c_common.change_current_content_email_type( 'html' );
						}
					}
					else if ( key === 2 ) {
						vm.c_common.change_current_content_email_type( 'txt' );
					}
					else if ( key === 3 || key === 4 ) {
						vm.c_common.show_templates = true;
						$rootScope.get_templates_from_campaign_page( key === 3 ? 'templates_gallery' : 'templates' );
						EmailContentService.hide_emailbuilder();
					}
				}
			},

			create_list: function( vm, url_create ) {
				if ( vm.url_id === '0' ) {
					vm.c_common.refresh_content_email();
					UrlService.post_data( url_create, vm.c_common.campaign, function( data ) {
						if ( data.success ) {
							vm.c_common.set_created_campaign_info( data );
							vm.common.display_grid();
						}
						else {
							$rootScope.display_error_writable();
						}
					}, function() {

					} );
				}
				else {
					vm.common.display_grid();
				}
			},

			display_import: function( vm, key ) {
				if ( key === 0 ) {
					vm.c_common.display_import_lists();
				}
				else {
					vm.common.display_copy_paste();
				}
			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'EmailContentService', [
	'$rootScope', '$timeout', '$document', '$window', 'UrlService', '$http', '$q',
	function( $rootScope, $timeout, $document, $window, UrlService, $http, $q ) {

		var refresh_email_content_height = false;

		var current_mode = '';

		var editor;

		var content_is_loading = false;

		function display_emailbuilder() {
			if ( $rootScope.settings.emailbuilder_installed ) {
				$rootScope.show_emailbuilder = true;
				refresh_email_content_height = true;
				$timeout( function() {
					service.refresh_email_content_height();
				} );
			}
		}

		function display_email_content_editor( current_content_email_type ) {
			$rootScope.show_emailbuilder = false;
			refresh_email_content_height = true;
			init_editor_if_needed( current_content_email_type );
			refresh_html_editor();
			$timeout( function() {
				service.refresh_email_content_height();
			} );
		}

		function init_editor_if_needed( current_content_email_type ) {
			var mode = 'text/html';
			var theme = 'monokai';
			if ( current_content_email_type === 'txt' ) {
				mode = 'text/plain';
				theme = '';
			}
			if ( editor === undefined ) {
				editor = new SbEditor( document.getElementById( 'jackmail_content_email' ), {
					'mode': mode,
					'theme': theme,
					'lineNumbers': true
				} );
			}
			else {
				if ( mode !== current_mode ) {
					editor.theme = theme;
					editor.mode = mode;
					refresh_html_editor();
				}
			}
			current_mode = mode;
		}

		function refresh_html_editor() {
			$timeout( function() {
				editor.refresh();
				if ( editor.content === '' ) {
					editor.focus();
				}
			}, 100 );
		}

		function get_emailbuilder_json() {
			if ( $rootScope.settings.emailbuilder_installed ) {
				try {
					var json = content_is_loading;
					if ( json === false ) {
						json = window.angularComponentRef.zone.run( function() {
							return window.angularComponentRef.component.saveContent();
						} );
					}
					var json_parsed = JSON.parse( json );
					if ( json_parsed.globalSettings !== undefined && json_parsed.workspace !== undefined ) {
						if ( json_parsed.workspace.structures !== undefined ) {
							if ( json_parsed.globalSettings.displayHeader !== undefined && json_parsed.globalSettings.displayFooter !== undefined ) {
								var nb_structures = 0;
								if ( json_parsed.globalSettings.displayHeader ) {
									nb_structures++;
								}
								if ( json_parsed.globalSettings.displayFooter ) {
									nb_structures++;
								}
								if ( json_parsed.workspace.structures.length > nb_structures ) {
									return json;
								}
							}
						}
					}
				}
				catch ( e ) {

				}
			}
			return '';
		}

		function content_email_types( content_email_json, content_email_html, content_email_txt ) {
			if ( content_email_json !== '' && content_email_txt === '' ) {
				return 'Html (Emailbuilder)';
			}
			else if ( content_email_json !== '' && content_email_txt !== '' ) {
				return 'Html (Emailbuilder) / Text';
			}
			else if ( content_email_json === '' && content_email_html === '' && content_email_txt !== '' ) {
				return 'Text';
			}
			else if ( content_email_json === '' && content_email_html !== '' && content_email_txt !== '' ) {
				return 'Html / Text';
			}
			else if ( content_email_json === '' && content_email_html !== '' && content_email_txt === '' ) {
				return 'Html';
			}
			return '';
		}

		function content_email_nb_links( content_email_json, content_email_html, content_email_txt ) {
			var nb_links = 0;
			if ( content_email_json !== '' ) {
				var json_parsed = JSON.parse( content_email_json );
				if ( json_parsed.links !== undefined ) {
					nb_links += json_parsed.links.length;
				}
				
				nb_links += content_email_json.split( '"link":"BUTTON"' ).length - 1;
				nb_links += content_email_json.split( '"link":"TEXT"' ).length - 1;
			}
			else if ( content_email_html !== '' ) {
				nb_links += content_email_html.split( ' href="' ).length - 1;
				nb_links += content_email_html.split( ' href=\'' ).length - 1;
			}
			else if ( content_email_txt !== '' ) {
				nb_links += content_email_txt.split( '((WEBCOPY_LINK))' ).length - 1;
				nb_links += content_email_txt.split( '((UNSUBSCRIBE_LINK))' ).length - 1;
				var links = content_email_txt.match( /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig );
				if ( links ) {
					nb_links += links.length;
				}
			}
			return nb_links;
		}

		function content_email_unsubscribe_link( content_email_json, content_email_html, content_email_txt ) {
			if ( content_email_json.indexOf( '{{tracking.unsubscribe}}' ) !== -1 ) {
				return true;
			}
			if ( content_email_json.indexOf( '{{unsubscribe}}' ) !== -1 ) {
				return true;
			}
			var link_unsubscribe = '((UNSUBSCRIBE_LINK))';
			if ( content_email_json.indexOf( link_unsubscribe ) !== -1 ) {
				return true;
			}
			if ( content_email_html.indexOf( link_unsubscribe ) !== -1 ) {
				return true;
			}
			if ( content_email_txt.indexOf( link_unsubscribe ) !== -1 ) {
				return true;
			}
			return false;
		}

		var service = {

			dynamic_content_type: 'article',

			init_emailbuilder: function( is_scenario, nb_posts ) {

				var deferred = $q.defer();

				if ( $rootScope.settings.emailbuilder_installed ) {

					if ( window.location.href.indexOf( 'woocommerce_automated_newsletter' ) !== -1 ) {
						service.dynamic_content_type = 'product';
					}

					var static_content_type = 'article';

					window.angularComponentRef.zone.run( function() {
						var settings = {
							'environment': 'jackmail',
							'wordpress': {
								'frontApiWpUrl': $rootScope.settings.ajax_url + '?action=jackmail_get_images&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ 'jackmail_get_images' ],
								'imageLibraryWpUrl': $rootScope.settings.upload_url
							},
							'frontApiUrl': $rootScope.settings.emailbuilder_api_url,
							'libraries': [ 'Getty', 'Phovoir' ],
							'imageLibraryUrl': $rootScope.settings.emailbuilder_image_library_url,
							'lang': $rootScope.settings.language
						};
						window.angularComponentRef.component.initEmailBuilder( settings ).then( function() {
							window.angularComponentRef.component.configurationService.useWordpressIntegrationProduct = $rootScope.settings.emailbuilder_display_product_button;
							if ( is_scenario === true ) {
								if ( service.dynamic_content_type === 'product' ) {
									window.angularComponentRef.component.setAutomatedProductWorkflow( true );
								} else {
									window.angularComponentRef.component.setAutomatedWorkflow( true );
								}
								service.set_nb_posts_content( nb_posts );
							}
							deferred.resolve();
						} );
						

						if ( window.angularComponentRef.component.dynamicContentService ) {

							var nb_categories = 0;

							window.angularComponentRef.component.dynamicContentService.loadCategories = function( article_type ) {
								if ( article_type === 3 ) {
									static_content_type = 'article';
								}
								else if ( article_type === 5 ) {
									static_content_type = 'product';
								}
								else if ( article_type === 7 ) {
									static_content_type = 'page';
								}
								else if ( article_type === 11 ) {
									static_content_type = 'custom-post-type';
								}

								var deferred = $q.defer();
								var action = 'jackmail_get_post_categories';
								if ( static_content_type === 'page' ) {
									nb_categories = 0;
									return JSON.parse( '[]' );
								}
								else if ( static_content_type === 'product' ) {
									action = 'jackmail_get_woocommerce_product_categories';
								}
								else if ( static_content_type === 'custom-post-type' ) {
									action = 'jackmail_get_custom_posts_categories';
								}
								$http( {
									method: 'POST',
									url: $rootScope.settings.ajax_url,
									data: 'action=' + action + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ],
									headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
									transformResponse: [ function( data ) {
										return data;
									} ]
								} ).then(
									function( response ) {
										deferred.resolve( JSON.parse( response.data ) );
										nb_categories = JSON.parse( response.data ).length;
									},
									function( response ) {
										UrlService.error_message( response );
									}
								);
								return deferred.promise;
							};

							window.angularComponentRef.component.dynamicContentService.loadArticles = function( article_type, title, categories ) {
								if ( title === undefined ) {
									title = '';
								}
								if ( categories === undefined || categories === null || categories.length === nb_categories ) {
									categories = [];
								}
								var deferred = $q.defer();
								var action = 'jackmail_get_posts';
								var data = 'action=' + action + '&title=' + title + '&categories=' + $rootScope.join( categories ) + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ];
								if ( static_content_type === 'page' ) {
									action = 'jackmail_get_pages';
									data = 'action=' + action + '&title=' + title + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ];
								}
								else if ( static_content_type === 'product' ) {
									action = 'jackmail_get_woocommerce_products';
									data = 'action=' + action + '&title=' + title + '&categories=' + $rootScope.join( categories ) + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ];
								}
								else if ( static_content_type === 'custom-post-type' ) {
									action = 'jackmail_get_custom_posts';
									data = 'action=' + action + '&title=' + title + '&post_type=' + categories[ 0 ] + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ];
								}
								$http( {
									method: 'POST',
									url: $rootScope.settings.ajax_url,
									data: data,
									headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
									transformResponse: [ function( data ) {
										return data;
									} ]
								} ).then(
									function( response ) {
										deferred.resolve( JSON.parse( response.data ) );
									},
									function( response ) {
										UrlService.error_message( response );
									}
								);
								return deferred.promise;
							};

							window.angularComponentRef.component.dynamicContentService.loadArticle = function( article_type, id ) {
								if ( id !== undefined ) {
									var deferred = $q.defer();
									var action = 'jackmail_get_post_or_page_or_custom_post_full_content';
									if ( static_content_type === 'product' ) {
										action = 'jackmail_get_woocommerce_product_full_content';
									}
									$http( {
										method: 'POST',
										url: $rootScope.settings.ajax_url,
										data: 'action=' + action + '&post_id=' + id + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ],
										headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
										transformResponse: [ function( data ) {
											return data;
										} ]
									} ).then(
										function( response ) {
											deferred.resolve( JSON.parse( response.data ) );
										},
										function( response ) {
											UrlService.error_message( response );
										}
									);
									return deferred.promise;
								}
							};

							if ( window.angularComponentRef.component.imageUploadManager !== undefined ) {
								var imageStorageService = window.angularComponentRef.component.imageUploadManager;
							}
							else {
								var imageStorageService = window.angularComponentRef.component.imageStorageService;
							}

							imageStorageService.imageSizeError = function( image_error ) {
								$rootScope.display_error( image_error );
							};

							imageStorageService.base64ToUrl = function( base64 ) {
								if ( base64 !== undefined ) {
									var deferred = $q.defer();
									var action = 'jackmail_save_image';
									var data = {
										'image': base64
									};
									var params = angular.element.param( data ) + '&action=' + action + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ];
									$http( {
										method: 'POST',
										url: $rootScope.settings.ajax_url,
										data: params,
										headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
										transformResponse: [ function( data ) {
											return data;
										} ]
									} ).then(
										function( response ) {
											var json = JSON.parse( response.data );
											if ( json.success ) {
												deferred.resolve( json.url );
											}
											else {
												UrlService.error_message( '' );
											}
										},
										function( response ) {
											UrlService.error_message( response );
										}
									);
									return deferred.promise;
								}

							};
							

						}

					} );
					
					angular.element( '.jackmail email-builder .menu-title' ).click( function() {
						service.refresh_email_content_height();
					} );
				}
				else {
					deferred.reject();
				}
				return deferred.promise;
			},

			update_list_fields: function( fields ) {
				if ( $rootScope.settings.emailbuilder_installed ) {
					var i;
					var nb_fields = fields.length;
					var eb_fields = [];
					for ( i = 0; i < nb_fields; i++ ) {
						if ( fields[ i ] !== 'WEBCOPY_LINK' && fields[ i ] !== 'UNSUBSCRIBE_LINK' ) {
							eb_fields.push( fields[ i ] );
						}
					}
					var data_parameters = {
						'fields': $rootScope.join_fields( eb_fields )
					};
					UrlService.post_data( 'jackmail_get_fields_and_ids', data_parameters, function( data ) {
						window.angularComponentRef.zone.run( function() {
							window.angularComponentRef.component.loadCustomTags( data );
						} );
					}, function() {

					} );
				}
			},

			get_nb_posts_content: function() {
				return window.angularComponentRef.zone.run( function() {
					var nb_posts_content = window.angularComponentRef.component.getArticleCount();
					return nb_posts_content.toString();
				} );
			},

			set_nb_posts_content: function( article_count ) {
				window.angularComponentRef.zone.run( function() {
					if ( window.angularComponentRef.component.setArticleCount !== undefined ) {
						window.angularComponentRef.component.setArticleCount( article_count );
					}
				} );
			},

			activate_link_tracking: function() {
				if ( $rootScope.settings.emailbuilder_installed ) {
					window.angularComponentRef.component.setLinkTracking( true );
				}
			},

			deactivate_link_tracking: function() {
				if ( $rootScope.settings.emailbuilder_installed ) {
					window.angularComponentRef.component.setLinkTracking( false );
				}
			},

			set_email_content_editor: function( current_content_email_type, content ) {
				init_editor_if_needed( current_content_email_type );
				editor.content = content;
				refresh_html_editor();
			},

			set_emailbuilder_json: function( json ) {
				window.angularComponentRef.zone.run( function() {
					if ( json === 'reset' ) {
						window.angularComponentRef.component.resetWorkspace();
					}
					else if ( json ) {
						content_is_loading = json;
						window.angularComponentRef.component.loadContent( json ).then( function() {
							content_is_loading = false;
							
						} );
					}
				} );
			},

			display: function( current_content_email_type ) {
				if ( current_content_email_type === 'emailbuilder' ) {
					display_emailbuilder();
				}
				else {
					display_email_content_editor( current_content_email_type );
				}
			},

			set: function( content_email_json, content_email_html, content_email_txt ) {
				var current_content_email_type = '';
				if ( $rootScope.settings.emailbuilder_installed && ( content_email_json !== '' || ( content_email_html === '' && content_email_txt === '' ) ) ) {
					current_content_email_type = 'emailbuilder';
					if ( content_email_json !== '' ) {
						service.set_emailbuilder_json( content_email_json );
					}
					
				}
				else {
					if ( content_email_txt !== '' ) {
						current_content_email_type = 'txt';
						service.set_email_content_editor( current_content_email_type, content_email_txt );
					}
					else if ( content_email_html !== '' ) {
						current_content_email_type = 'html';
						service.set_email_content_editor( current_content_email_type, content_email_html );
					}
					else {
						current_content_email_type = 'html';
					}
					display_email_content_editor( current_content_email_type );
				}
				return {
					'content_email_json': content_email_json,
					'content_email_html': content_email_html,
					'content_email_txt': content_email_txt,
					'current_content_email_type': current_content_email_type, 
					'content_email_types': content_email_types( content_email_json, content_email_html, content_email_txt ),
					'content_email_nb_links': content_email_nb_links( content_email_json, content_email_html, content_email_txt ),
					'content_email_unsubscribe_link': content_email_unsubscribe_link( content_email_json, content_email_html, content_email_txt )
				};
			},

			init_and_display_emailbuilder: function( content_email_json ) {
				var content_email = service.set( content_email_json, '', '' );
				display_emailbuilder();
				return content_email;
			},

			change_current_content_email_type: function( old_current_content_email_type, new_current_content_email_type, content_email_json, content_email_html, content_email_txt ) {
				if ( old_current_content_email_type === 'txt' ) {
					content_email_txt = service.get_email_content_editor( new_current_content_email_type );
				}
				if ( old_current_content_email_type === 'html' && new_current_content_email_type === 'txt' ) {
					content_email_html = service.get_email_content_editor( new_current_content_email_type );
				}
				if ( new_current_content_email_type === 'emailbuilder' ) {
					service.set_email_content_editor( new_current_content_email_type, '' );
					content_email_html = '';
					display_emailbuilder();
				}
				else if ( new_current_content_email_type === 'html' ) {
					if ( content_email_json !== '' ) {
						content_email_html = '';
						content_email_json = '';
						service.set_emailbuilder_json( 'reset' );
					}
					service.set_email_content_editor( new_current_content_email_type, content_email_html );
					display_email_content_editor( new_current_content_email_type );
				}
				else if ( new_current_content_email_type === 'txt' ) {
					service.set_email_content_editor( new_current_content_email_type, content_email_txt );
					display_email_content_editor( new_current_content_email_type );
				}
				return {
					'content_email_json': content_email_json,
					'content_email_html': content_email_html,
					'content_email_txt': content_email_txt,
					'current_content_email_type': new_current_content_email_type, 
					'content_email_types': content_email_types( content_email_json, content_email_html, content_email_txt ),
					'content_email_nb_links': content_email_nb_links( content_email_json, content_email_html, content_email_txt ),
					'content_email_unsubscribe_link': content_email_unsubscribe_link( content_email_json, content_email_html, content_email_txt )
				};
			},

			refresh_content_email: function( current_content_email_type, content_email_json, content_email_html, content_email_txt ) {
				var content_email_changes = false;
				var old_content_email_json = angular.copy( content_email_json );
				var old_last_content_email_html = angular.copy( content_email_html );
				var old_last_content_email_txt = angular.copy( content_email_txt );
				if ( current_content_email_type === 'emailbuilder' ) {
					content_email_json = get_emailbuilder_json();
					content_email_html = '';
				}
				else {
					if ( current_content_email_type === 'html' ) {
						content_email_html = service.get_email_content_editor( current_content_email_type );
					}
					else {
						content_email_txt = service.get_email_content_editor( current_content_email_type );
					}
				}
				if ( old_content_email_json !== content_email_json || old_last_content_email_html !== content_email_html || old_last_content_email_txt !== content_email_txt ) {
					content_email_changes = true;
				}
				return {
					'content_email_json': content_email_json,
					'content_email_html': content_email_html,
					'content_email_txt': content_email_txt,
					'content_email_changes': content_email_changes, 
					'content_email_types': content_email_types( content_email_json, content_email_html, content_email_txt ),
					'content_email_nb_links': content_email_nb_links( content_email_json, content_email_html, content_email_txt ),
					'content_email_unsubscribe_link': content_email_unsubscribe_link( content_email_json, content_email_html, content_email_txt )
				};
			},

			hide_emailbuilder: function() {
				$rootScope.show_emailbuilder = false;
				refresh_email_content_height = false;
			},

			get_email_content_editor: function( current_content_email_type ) {
				init_editor_if_needed( current_content_email_type );
				return editor.content;
			},

			insert_email_content_editor_customize: function( current_content_email_type, content ) {
				if ( editor !== undefined ) {
					if ( current_content_email_type === 'html' && content === 'WEBCOPY_LINK' ) {
						editor.addToCursor( '<a href="((' + content + '))">' + $rootScope.translations.webcopy + '</a>' );
					}
					else if ( current_content_email_type === 'html' && content === 'UNSUBSCRIBE_LINK' ) {
						editor.addToCursor( '<a href="((' + content + '))">' + $rootScope.translations.unsubscribe + '</a>' );
					}
					else {
						editor.addToCursor( '((' + content + '))' );
					}
				}
			},

			hide_emailbuilder_menu: function() {
				window.angularComponentRef.zone.run( function() {
					window.angularComponentRef.component.hideSideMenu = true;
				} );
			},

			refresh_email_content_height: function() {
				if ( refresh_email_content_height || $rootScope.show_emailbuilder ) {
					var window_height = parseInt( angular.element( $window ).height() );
					var scroll = parseInt( angular.element( $window ).scrollTop() );
					if ( $rootScope.show_emailbuilder ) {
						var jackmail_position_top = Math.round( angular.element( '.jackmail.jackmail_angular #jackmail_emailbuilder_container' ).offset().top );
						var footer_height = 0;
						angular.element( '.jackmail.jackmail_angular .jackmail_footer' ).each( function() {
							footer_height = parseInt( angular.element( this ).height() );
						} );
						var height = window_height - footer_height - jackmail_position_top + scroll;
						angular.element( '.jackmail #jackmail_emailbuilder_container' ).css( 'height', height + 'px' );
					}
					else {
						angular.element( '.jackmail #jackmail_content_email' ).each( function() {
							var position = Math.round( angular.element( this ).offset().top );
							var footerHeight = 40;
							angular.element( '.jackmail .jackmail_footer' ).each( function() {
								footerHeight = parseInt( angular.element( '.jackmail .jackmail_footer' ).css( 'height' ) );
							} );
							var height = parseInt( window_height - position + scroll - footerHeight - 25 );
							angular.element( '.jackmail #jackmail_content_email > div' ).css( 'height', height + 'px' );
						} );
					}
				}
			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'ExportService', [
	'$filter', '$timeout', '$rootScope',
	function( $filter, $timeout, $rootScope ) {

		var service = {

			escape_double_quote: function( string ) {
				if ( string.indexOf( '"' ) === -1 && string.indexOf( ' ' ) === -1 ) {
					return string;
				}
				return '"' + string.replace( /"/g, '""' ) + '"';
			},

			blacklist_type_string: function( blacklist_id ) {
				if ( blacklist_id !== '0' ) {
					if ( $rootScope.settings.blacklist_type_bounces === blacklist_id ) {
						return 'bounce';
					}
					else if ( $rootScope.settings.blacklist_type_complaints === blacklist_id ) {
						return 'complaint';
					}
					else if ( $rootScope.settings.blacklist_type_unsubscribes === blacklist_id ) {
						return 'unsubscribe';
					}
				}
				return '';
			},

			get_contact_file: function( list, export_type ) {
				var csv = '';
				var has_blacklist = false;
				var i;
				var header = list.list;
				var header_fields = JSON.parse( header.fields );
				var contacts = list.contacts;
				var nb_contacts = contacts.length;
				var nb_header_fields = header_fields.length;
				for ( i = 0; i < nb_contacts; i++ ) {
					if ( export_type === 'all' || ( export_type === 'selection' && contacts[ i ].selected ) ) {
						if ( contacts[ i ].blacklist !== '0' ) {
							has_blacklist = true;
							break;
						}
					}
				}
				csv += service.escape_double_quote( $rootScope.translations.email.toUpperCase() );
				for ( i = 0; i < nb_header_fields; i++ ) {
					csv += ';' + service.escape_double_quote( header_fields[ i ] );
				}
				if ( has_blacklist ) {
					csv += ';BLACKLIST';
				}
				csv += "\n";
				for ( i = 0; i < nb_contacts; i++ ) {
					if ( export_type === 'all' || ( export_type === 'selection' && contacts[ i ].selected ) ) {
						csv += service.escape_double_quote( contacts[ i ].email );
						var j;
						for ( j = 0; j < nb_header_fields; j++ ) {
							csv += ';';
							if ( contacts[ i ][ 'field' + ( j + 1 ) ] !== undefined ) {
								csv += service.escape_double_quote( contacts[ i ][ 'field' + ( j + 1 ) ] );
							}
						}
						if ( has_blacklist ) {
							csv += ';' + service.blacklist_type_string( contacts[ i ].blacklist );
						}
						csv += "\n";
					}
				}
				return csv;
			},

			export_contact_file_multiple_data: function( data ) {
				var i;
				var nb_parts = data.length;
				var new_data = {
					'list': {},
					'contacts': [],
					'nb_contacts': '',
					'nb_contacts_search': ''
				};
				var j;
				var nb_contacts;
				for ( i = 0; i < nb_parts; i++ ) {
					nb_contacts = data[ i ].contacts.length;
					for ( j = 0; j < nb_contacts; j++ ) {
						new_data.contacts.push( data[ i ].contacts[ j ] );
					}
					if ( i === nb_parts - 1 ) {
						new_data.list = data[ i ].list;
						new_data.nb_contacts = data[ i ].nb_contacts;
						new_data.nb_contacts_search = data[ i ].nb_contacts_search;
					}
				}
				service.export_contact_file( new_data, 'all' );
			},

			export_contact_file: function( list, export_type ) {
				var csv = '\ufeff' + service.get_contact_file( list, export_type );
				var current_date = new Date();
				var filename = 'export-' + $filter( 'formatedDateFromTimestampToTimezone' )( current_date, 'file_name' ) + '.csv';
				var blob = new Blob( [ csv ], { type: 'text/csv;charset=utf-8;' } );
				if ( navigator.msSaveBlob ) {
					navigator.msSaveBlob( blob, filename );
				}
				else {
					var data = URL.createObjectURL( blob );
					angular.element( '.jackmail_download' ).attr( { 'href': data, 'download': filename } );
					$timeout( function() {
						angular.element( '.jackmail_download' )[ 0 ].click();
						angular.element( '.jackmail_download' ).attr( { 'href': '', 'download': '' } );
					} );
				}
			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'GridService', [
	'$window', '$rootScope',
	function( $window, $rootScope ) {

		var GridService = function() {

			var service = {

				nb_selected: 0,

				grid_class: '',

				display_columns_button: false,

				grid_range_by_order: false,

				grid_range_by_item: '',

				load_interval: 100,

				nb_lines_grid: 100,

				begin: 0,

				grid_classes: [],

				init_columns_list: function( fields ) {
					var nb_columns = fields.length;
					var var_grid_classes = [];
					var var_grid_class = '';
					var i;
					var_grid_classes[ 0 ] = true;
					var firstname_or_lastname = false;
					for ( i = 0; i < nb_columns; i++ ) {
						if ( fields[ i ] === ( $rootScope.translations.name ).toUpperCase() || fields[ i ] === ( $rootScope.translations.firstname ).toUpperCase() || fields[ i ] === ( $rootScope.translations.lastname ).toUpperCase() ) {
							var_grid_classes[ i + 1 ] = true;
							firstname_or_lastname = true;
						}
						else {
							var_grid_classes[ i + 1 ] = false;
							var_grid_class += ' jackmail_hide_column_' + ( i + 1 );
						}
					}
					if ( !firstname_or_lastname ) {
						var_grid_class = '';
						for ( i = 0; i < nb_columns; i++ ) {
							if ( i < 2 ) {
								var_grid_classes[ i + 1 ] = true;
							}
							else {
								var_grid_classes[ i + 1 ] = false;
								var_grid_class += ' jackmail_hide_column_' + ( i + 1 );
							}
						}
					}
					service.grid_classes = var_grid_classes;
					service.grid_class = var_grid_class;
				},

				init_columns: function( nb_columns ) {
					var var_grid_classes = [];
					var var_grid_class = '';
					var i;
					for ( i = 0; i < nb_columns; i++ ) {
						if ( i < 5 ) {
							var_grid_classes[ i ] = true;
						}
						else {
							var_grid_classes[ i ] = false;
							var_grid_class += ' jackmail_hide_column_' + i;
						}
					}
					service.grid_classes = var_grid_classes;
					service.grid_class = var_grid_class;
				},

				set_nb_selected: function( value ) {
					service.nb_selected = value;
				},

				set_nb_lines_grid: function( nb_lines_grid ) {
					service.nb_lines_grid = nb_lines_grid;
				},

				reset_nb_lines_grid: function() {
					service.nb_lines_grid = 100;
				},

				increase_begin: function() {
					service.begin += 100;
				},

				reset_begin: function() {
					service.begin = 0;
				},

				merge_arrays: function( array1, array2 ) {
					var i;
					var nb = array2.length;
					for ( i = 0; i < nb; i++ ) {
						array1.push( array2[ i ] );
					}
					return array1;
				},

				refresh_grid_max_height: function( type ) {
					angular.element( '.jackmail_grid_content' ).each( function() {
						var position = Math.round( angular.element( this ).offset().top );
						var footerHeight = 0;
						angular.element( '.jackmail_footer' ).each( function() {
							footerHeight = parseInt( angular.element( '.jackmail_footer' ).css( 'height' ) );
						} );
						var height = parseInt( angular.element( $window ).height() );
						var scroll = parseInt( angular.element( $window ).scrollTop() );
						var grid_height = parseInt( height - position + scroll - footerHeight - 65 );
						angular.element( this ).css( 'maxHeight', grid_height + 'px' );
						if ( type === 'resize' || type === 'refresh' ) {
							var width = parseInt( angular.element( this ).css( 'width' ) );
							if ( width !== 0 ) {
								angular.element( this ).find( ' > table' ).css( 'width', width );
							}
						}
					} );
					if ( type === 'resize' || type === 'refresh' ) {
						angular.element( '.jackmail_grid_content_defined' ).each( function() {
							var width = parseInt( angular.element( this ).css( 'width' ) );
							if ( width !== 0 ) {
								angular.element( this ).find( ' > table' ).css( 'width', width );
							}
						} );
					}
				},

				display_or_hide_column: function( key ) {
					service.grid_classes[ key ] = !service.grid_classes[ key ];
					service.grid_column_refresh();
				},

				display_column: function( key ) {
					service.grid_classes[ key ] = true;
					service.grid_column_refresh();
				},

				grid_column_refresh: function() {
					var i;
					var nb_columns = service.grid_classes.length;
					var grid_class = '';
					for ( i = 0; i < nb_columns; i++ ) {
						if ( !service.grid_classes[ i ] ) {
							grid_class += ' jackmail_hide_column_' + i;
						}
					}
					service.grid_class = grid_class;
				},

				grid_range: function( items, i ) {
					service.grid_range_by_order = !service.grid_range_by_order;
					service.grid_range_by_item = i;
					var filtered = service.grid_order_by( items, i, 'ASC' );
					if ( service.grid_range_by_order ) {
						filtered.reverse();
					}
					return filtered;
				},

				grid_range_from_server: function( i ) {
					service.grid_range_by_order = !service.grid_range_by_order;
					service.grid_range_by_item = i;
				},

				grid_order_by: function( items, i, order ) {
					var filtered = [];
					angular.forEach( items, function( item ) {
						filtered.push( item );
					} );
					filtered.sort( function( a, b ) {
						return ( a[ i ] > b[ i ] ? 1 : -1 );
					} );
					if ( order === 'DESC' ) {
						filtered.reverse();
					}
					return filtered;
				},

				display_or_hide_columns_button: function() {
					service.display_columns_button = !service.display_columns_button;
				},

				hide_columns_button: function() {
					service.display_columns_button = false;
				},

				grid_select_or_unselect_row: function( rows, key ) {
					if ( rows[ key ].selected ) {
						rows[ key ].selected = false;
						service.nb_selected--;
					}
					else {
						service.nb_selected++;
						rows[ key ].selected = true;
					}
					return rows;
				},

				grid_select_or_unselect_all: function( rows ) {
					if ( service.nb_selected === rows.length ) {
						var select_all = false;
						service.nb_selected = 0;
					}
					else {
						var select_all = true;
						service.nb_selected = rows.length;
					}
					var i;
					var nb_rows = rows.length;
					for ( i = 0; i < nb_rows; i++ ) {
						rows[ i ].selected = select_all;
					}
					return rows;
				},

				grid_select_or_unselect_all_with_field_restriction: function( rows, field ) {
					var i;
					var nb_lists = rows.length;
					var nb_selected = 0;
					for ( i = 0; i < nb_lists; i++ ) {
						if ( !rows[ i ][ field ] ) {
							nb_selected++;
						}
					}
					if ( service.nb_selected === nb_selected ) {
						var select_all = false;
						service.set_nb_selected( 0 );
					}
					else {
						var select_all = true;
						service.set_nb_selected( nb_selected );
					}
					for ( i = 0; i < nb_lists; i++ ) {
						if ( !rows[ i ][ field ] ) {
							rows[ i ].selected = select_all;
						}
					}
					return rows;
				},

				grid_select_all: function( rows ) {
					service.nb_selected = rows.length;
					var i;
					var nb_rows = rows.length;
					for ( i = 0; i < nb_rows; i++ ) {
						rows[ i ].selected = true;
					}
					return rows;
				},

				grid_select_all_new: function( rows ) {
					var nb_selected = 0;
					var i;
					var nb_rows = rows.length;
					for ( i = 0; i < nb_rows; i++ ) {
						if ( rows[ i ].selected === undefined ) {
							rows[ i ].selected = true;
							nb_selected++;
						}
					}
					service.nb_selected = nb_selected;
					return rows;
				},				grid_select_only_campaign_ids: function( rows, ids ) {
					var nb_selected_temp = 0;
					var i;
					var nb_rows = rows.length;
					for ( i = 0; i < nb_rows; i++ ) {
						if ( ids.indexOf( rows[ i ].type + '' + rows[ i ].id ) !== -1 ) {
							rows[ i ].selected = true;
							nb_selected_temp++;
						}
						else {
							rows[ i ].selected = false;
						}
					}
					service.nb_selected = nb_selected_temp;
					return rows;
				}

			};

			service.init_columns( 10 );

			return service;

		};

		return GridService;

	} ] );

angular.module( 'jackmail.services' ).factory( 'ListAndCampaignCommonService', [
	'$rootScope', '$timeout', '$filter', 'UrlService', 'EmailContentService', 'ExportService', 'VerificationService', '$window',
	function( $rootScope, $timeout, $filter, UrlService, EmailContentService, ExportService, VerificationService, $window ) {

		var service = {

			hide_name_popup: function( vm ) {
				vm.common.show_name_popup = false;
			},

			display_copy_paste: function( vm ) {
				vm.common.show_copy_paste = true;
			},

			hide_copy_paste: function( vm ) {
				vm.common.show_copy_paste = false;
				vm.common.copy_paste_content = '';
			},

			confirm_copy_paste: function( vm, url_create ) {
				if ( vm.page_type === 'campaign' && vm.url_id === '0' ) {
					vm.c_common.refresh_content_email();
					UrlService.post_data( url_create, vm.c_common.campaign, function( data ) {
						if ( data.success ) {
							vm.c_common.set_created_campaign_info( data );
							vm.common.add_contacts( vm.common.copy_paste_content );
						}
					}, function() {

					} );
				}
				else {
					vm.common.add_contacts( vm.common.copy_paste_content );
				}
			},

			go_back: function( vm ) {
				$window.history.back();
			},

			update_list_fields_array: function( vm ) {
				var list_fields = [];
				var list_fields_plus = [];
				list_fields.push( 'EMAIL' );
				var i = 1;
				while ( vm.common.list.list[ 'field' + i ] !== undefined ) {
					list_fields.push( vm.common.list.list[ 'field' + i ] );
					i++;
				}
				if ( vm.page_type === 'campaign' ) {
					vm.c_common.refresh_content_email();
					var object = vm.c_common.campaign.object;
					var content_email_json = vm.c_common.campaign.content_email_json;
					var content_email_html = vm.c_common.campaign.content_email_html;
					var content_email_txt = vm.c_common.campaign.content_email_txt;
					var nb_fields = vm.common.list_fields.length;
					for ( i = 0; i < nb_fields; i++ ) {
						var old_column = vm.common.list_fields[ i ];
						var new_column = list_fields[ i ];
						if ( old_column !== undefined && new_column !== undefined && old_column !== new_column ) {
							var old_column_html = $rootScope.text_to_html( old_column );
							var new_column_html = $rootScope.text_to_html( new_column );
							object = $rootScope.replace_all( object, '((' + old_column + '))', '((' + new_column + '))' );
							content_email_json = $rootScope.replace_all( content_email_json, '((' + old_column + '))', '((' + new_column + '))' );
							content_email_html = $rootScope.replace_all( content_email_html, '((' + old_column + '))', '((' + new_column + '))' );
							content_email_txt = $rootScope.replace_all( content_email_txt, '((' + old_column + '))', '((' + new_column + '))' );
							content_email_json = $rootScope.replace_all( content_email_json, '((' + old_column_html + '))', '((' + new_column_html + '))' );
							content_email_html = $rootScope.replace_all( content_email_html, '((' + old_column_html + '))', '((' + new_column_html + '))' );
						}
					}
					var fields = angular.copy( list_fields );
					fields.splice( 0, 1 );
					vm.common.list.list.fields = $rootScope.join_fields( fields );
					list_fields_plus = angular.copy( list_fields );
					list_fields_plus.push( 'WEBCOPY_LINK');
					list_fields_plus.push( 'UNSUBSCRIBE_LINK');
					var object_changed = false;
					if ( vm.c_common.campaign.object !== object ) {
						vm.c_common.campaign.object = object;
						object_changed = true;
					}
					var content_email_changed = false;
					if ( vm.c_common.campaign.content_email_json !== content_email_json ) {
						vm.c_common.campaign.content_email_json = content_email_json;
						content_email_changed = true;
					}
					if ( vm.c_common.campaign.content_email_html !== content_email_html ) {
						vm.c_common.campaign.content_email_html = content_email_html;
						content_email_changed = true;
					}
					if ( vm.c_common.campaign.content_email_txt !== content_email_txt ) {
						vm.c_common.campaign.content_email_txt = content_email_txt;
						content_email_changed = true;
					}
					if ( content_email_changed || object_changed ) {
						if ( content_email_changed ) {
							EmailContentService.set( vm.c_common.campaign.content_email_json, vm.c_common.campaign.content_email_html, vm.c_common.campaign.content_email_txt );
						}
						vm.c_common.save_campaign( true );
					}
					EmailContentService.update_list_fields( list_fields_plus );
					vm.common.list_fields_plus = list_fields_plus;
				}
				vm.common.list_fields = angular.copy( list_fields );
			},

			get_list_data_reset: function( vm ) {
				$rootScope.grid_service.reset_begin();
				$rootScope.grid_service.reset_nb_lines_grid();
				vm.common.get_list_data();
				$rootScope.grid_service.set_nb_selected( 0 );
				vm.common.list_top_load = true;
				$rootScope.scroll_top();
				vm.common.manual_select_all = false;
				angular.element( '.jackmail_grid_content' ).scrollTop( 0 );
			},

			get_list_data_search_reset: function( vm, list_search ) {
				vm.common.list_search = list_search;
				vm.common.get_list_data_reset();
			},

			split_list_fields: function( vm, data ) {
				if ( data.list.fields !== '' ) {
					var fields = $rootScope.split_fields( data.list.fields );
					var i;
					var nb_fields = fields.length;
					var main_nb_fields = [];
					for ( i = 0; i < nb_fields; i++ ) {
						data.list[ 'field' + ( i + 1 ) ] = fields[ i ];
						main_nb_fields.push( i + 1 );
					}
				}
				return data;
			},

			check_list_editable: function( vm ) {
				if ( vm.page_type === 'list' ) {
					if ( vm.common.list.list ) {
						if ( vm.common.list.list.type !== '' ) {
							vm.common.columns_editable = 0;
							vm.common.list_editable = 0;
							vm.common.list_full_editable = 0;
							vm.common.list_targeting = 0;
							if ( $filter( 'pluginName' )( vm.common.list.list.type ) === 'Contact Form 7' ) {
								vm.common.list_editable = 1;
							}
							return;
						}
					}
				}
				vm.common.list_editable = 1;
				vm.common.list_full_editable = 1;
				vm.common.list_targeting = 1;
				vm.common.columns_editable = 1;
			},

			display_grid: function( vm ) {
				vm.common.show_grid = 1;
				vm.common_list_detail.show_list_contact_detail = false;
				if ( vm.common.list.nb_contacts === '0' && vm.common.list_full_editable ) {
					var add_contact_manual = true;
					if ( vm.common.list.contacts[ 0 ] ) {
						if ( vm.common.list.contacts[ 0 ].email === '' ) {
							add_contact_manual = false;
						}
					}
					if ( add_contact_manual ) {
						vm.common.add_contact_manual();
					}
				}
				$timeout( function() {
					$rootScope.grid_service.refresh_grid_max_height( 'refresh' );
				} );
			},

			grid_select_or_unselect_all: function( vm ) {
				vm.common.list.contacts = $rootScope.grid_service.grid_select_or_unselect_all( vm.common.list.contacts );
				if ( $rootScope.grid_service.nb_selected > 0 ) {
					vm.common.manual_select_all = true;
				}
				else {
					vm.common.manual_select_all = false;
				}
			},

			grid_select_or_unselect_row: function( vm, key ) {
				vm.common.list.contacts = $rootScope.grid_service.grid_select_or_unselect_row( vm.common.list.contacts, key );
			},

			add_contacts_file: function( vm, url_create, event ) {
				if ( vm.page_type === 'campaign' && vm.url_id === '0' ) {
					vm.c_common.refresh_content_email();
					UrlService.post_data( url_create, vm.c_common.campaign, function( data ) {
						if ( data.success ) {
							vm.c_common.set_created_campaign_info( data );
							add_contacts_file();
						}
						else {
							$rootScope.display_error_writable();
						}
					}, function() {

					} );
				}
				else {
					add_contacts_file();
				}

				function add_contacts_file() {
					var file_name = event.target.files[ 0 ].name;
					var file_extension = file_name.substring( file_name.lastIndexOf( '.' ), file_name.length );
					if ( file_extension === '.csv' || file_extension === '.CSV' || file_extension === '.txt' || file_extension === '.TXT' || file_extension === '.json' || file_extension === '.JSON' ) {
						var tmpPath = URL.createObjectURL( event.target.files[ 0 ] );
						UrlService.get_file_data( tmpPath, function( data ) {
							if ( file_extension === '.json' || file_extension === '.JSON' ) {
								try {
									data = JSON.parse( data );
									var i;
									var nb_rows = data.length;
									emails_import = '';
									var nb_columns = 0;
									var new_data = {
										'contacts': [],
										'list': {
											'email': '',
											'fields': []
										}
									};
									var fields = [];
									if ( nb_rows > 0 ) {
										var j = 0;
										for ( var key in data[ 0 ] ) {
											if ( j === 0 ) {
												new_data.list[ 'email' ] = key;
											}
											else {
												new_data.list[ 'fields' ].push( key );
												fields.push( key );
												nb_columns++;
											}
											j++;
										}
										for ( i = 0; i < nb_rows; i++ ) {
											j = 0;
											var contact = {};
											for ( j = 0; j <= nb_columns; j++ ) {
												if ( j === 0 ) {
													if ( data[ i ][ new_data.list[ 'email' ] ] ) {
														contact[ 'email' ] = data[ i ][ new_data.list[ 'email' ] ];
													}
													else {
														contact[ 'email' ] = '';
													}
												}
												else {
													if ( data[ i ][ fields[ j - 1 ] ] ) {
														contact[ 'field' + j ] = data[ i ][ fields[ j - 1 ] ];
													}
													else {
														contact[ 'field' + j ] = '';
													}
												}
											}
											new_data.contacts.push( contact );
										}
										new_data.list[ 'fields' ] = $rootScope.join_fields( new_data.list[ 'fields' ] );
										emails_import = ExportService.get_contact_file( new_data, 'all' );
									}
								}
								catch ( e ) {
									$rootScope.display_error( $rootScope.translations.the_json_file_is_not_valid );
									return;
								}
							}
							else {
								var emails_import = data;
							}
							vm.common.add_contacts( emails_import );
						}, function() {

						} );
					}
					else {
						$rootScope.display_error( $rootScope.translations.the_file_format_is_not_valid );
						$rootScope.$apply();
					}
				}
			},

			split_csv: function( vm, row, field_separator ) {
				for ( var foo = row.split( field_separator ), x = foo.length - 1, tl; x >= 0; x-- ) {
					if ( foo[ x ].replace( /"\s+$/, '"' ).charAt( foo[ x ].length - 1 ) == '"' ) {
						if ( ( tl = foo[ x ].replace( /^\s+"/, '"' ) ).length > 1 && tl.charAt( 0 ) == '"' ) {
							foo[ x ] = foo[ x ].replace( /^\s*"|"\s*$/g, '' ).replace( /""/g, '"' );
						}
						else if ( x ) {
							foo.splice( x - 1, 2, [ foo[ x - 1 ], foo[ x ] ].join( field_separator ) );
						}
						else foo = foo.shift().split( field_separator ).concat( foo );
					}
					else foo[ x ].replace( /""/g, '"' );
				}
				return foo;
			},

			add_contacts: function( vm, emails_import ) {
				emails_import = emails_import.replace( /(\r\n|\r|\n)/g, "\n" );
				emails_import = emails_import.replace( /;\n/g, "\n" );
				var field_separator = ';';
				var emails_header = '';
				var rows = emails_import.split( "\n" );
				var nb_rows = rows.length;
				if ( nb_rows > 0 ) {
					var has_header = false;
					emails_header = rows[ 0 ];
					if ( emails_header.indexOf( '@' ) === -1 || emails_header.indexOf( '.' ) === -1 ) {
						has_header = true;
						rows.splice( 0, 1 );
					}
					var i;
					nb_rows = rows.length;
					var has_at = false;
					var has_email = false;
					var email_position = -1;
					var nb_test_rows = 50;
					if ( nb_test_rows > nb_rows ) {
						nb_test_rows = nb_rows;
					}
					var nb_semicolons = 0;
					var nb_commas = 0;
					var nb_tabs = 0;
					var nb_seps = 0;
					for ( i = 0; i < nb_test_rows; i++ ) {
						nb_semicolons += rows[ i ].split( ';' ).length - 1;
						nb_commas += rows[ i ].split( ',' ).length - 1;
						nb_tabs += rows[ i ].split( '	' ).length - 1;
						nb_seps += rows[ i ].split( '|' ).length - 1;
					}
					if ( nb_commas > nb_semicolons ) {
						field_separator = ',';
					}
					if ( nb_tabs > nb_commas ) {
						field_separator = 'TAB';
					}
					if ( nb_seps > nb_tabs ) {
						field_separator = '|';
					}
					for ( i = nb_rows - 1; i >= 0; i-- ) {
						if ( rows[ i ].indexOf( '@' ) === -1 || rows[ i ].indexOf( '.' ) === -1 ) {
							rows.splice( i, 1 );
						}
						else {
							has_at = true;
							var row_splited = vm.common.split_csv( rows[ i ], field_separator );
							var nb_columns_row = row_splited.length;
							if ( email_position === -1 ) {
								email_position = 0;
								var j;
								var email_position_found = false;
								for ( j = 0; j < nb_columns_row; j++ ) {
									if ( VerificationService.email( $rootScope.replace_all( row_splited[ j ], '"', '' ) ) ) {
										email_position = j;
										email_position_found = true;
										break;
									}
								}
								if ( !email_position_found ) {
									email_position = -1;
								}
							}
							if ( email_position !== -1 ) {
								if ( VerificationService.email( $rootScope.replace_all( row_splited[ email_position ], '"', '' ) ) ) {
									has_email = true;
								}
								else {
									rows.splice( i, 1 );
								}
							}
							else {
								rows.splice( i, 1 );
							}
						}
					}
					nb_rows = rows.length;
					if ( has_email && has_at ) {
						var part = 3000;
						var nb_parts = Math.ceil( nb_rows / part );
						var begin = 0;
						var data_parameters = [];
						for ( i = 0; i < nb_parts; i++ ) {
							emails_import = '';
							if ( has_header ) {
								emails_import += emails_header + "\n";
							}
							for ( j = begin; j < begin + part; j++ ) {
								if ( rows[ j ] !== undefined ) {
									emails_import += rows[ j ] + "\n";
								}
								else {
									break;
								}
							}
							data_parameters.push( {
								'field_separator': field_separator,
								'email_position': email_position,
								'contacts': emails_import
							} );
							if ( vm.page_type === 'campaign' ) {
								data_parameters[ i ][ 'id_campaign' ] = vm.url_id;
							}
							else {
								data_parameters[ i ][ 'id_list' ] = vm.url_id;
							}
							begin = begin + part;
						}
						UrlService.post_multiple_data( 'jackmail_import_contacts', data_parameters, $rootScope.translations.downloading, function() {
							vm.common.hide_copy_paste();
							if ( vm.page_type === 'campaign' ) {
								vm.c_common.get_campaign_data( false );
							}
							vm.common.get_list_data_reset();
						}, function() {

						} );
					}
					else {
						var message = $rootScope.translations.no_valid_contacts_found;
						if ( has_at && !has_email ) {
							message = message + ' ' + $rootScope.translations.fields_separator_must_be_semicolon_comma_vertical_bar_or_tabulation;
						}
						$rootScope.display_error( message );
					}
				}
				else {
					$rootScope.display_error( $rootScope.translations.content_is_empty );
				}
			},

			add_contact_manual: function( vm ) {
				var new_contact = {
					'email': '',
					'id_list': '0',
					'blacklist': '0'
				};
				var i;
				var nb_fields = vm.common.list_fields.length - 1;
				for ( i = 1; i < nb_fields; i++ ) {
					new_contact[ 'field' + i ] = '';
				}
				vm.common.list.contacts.unshift( new_contact );
				angular.element( '.jackmail_grid_content' ).scrollTop( 0 );
				$timeout( function() {
					angular.element( '.jackmail_grid_content .jackmail_column_0:first input' ).focus();
				} );
			},

			focus_contact: function( vm, key ) {
				vm.common.current_contact = angular.copy( vm.common.list.contacts[ key ] );
			},

			blur_contact: function( vm, $event ) {
				if ( $event.keyCode === 13 ) {
					angular.element( $event.target ).blur();
				}
			},

			update_contact: function( vm, key, field_id ) {
				var current_contact = vm.common.current_contact;
				vm.common.list.contacts[ key ].email = vm.common.list.contacts[ key ].email.toLowerCase();
				var new_contact = vm.common.list.contacts[ key ];
				if ( VerificationService.differents_arrays( current_contact, new_contact ) ) {
					if ( field_id === -1 ) {
						if ( vm.common.list.contacts[ key ].error ) {
							current_contact.email = '';
						}
						var data_parameters = {
							'email': current_contact.email,
							'new_email': new_contact.email
						};
						var url = 'jackmail_update_contact_email';
					}
					else {
						if ( vm.common.list.contacts[ key ].error ) {
							new_contact.email = '';
						}
						var data_parameters = {
							'email': new_contact.email,
							'field_id': field_id,
							'field': new_contact[ 'field' + field_id ]
						};
						var url = 'jackmail_update_contact_field';
					}
					if ( vm.common.list.contacts[ key ].error ) {
						vm.common.list.contacts[ key ].error = false;
					}
					if ( vm.page_type === 'campaign' ) {
						data_parameters[ 'id_campaign' ] = vm.url_id;
					}
					else {
						data_parameters[ 'id_list' ] = vm.url_id;
					}
					if ( ( url === 'jackmail_update_contact_field' && data_parameters.email !== '' && VerificationService.email( data_parameters.email ) ) || ( url === 'jackmail_update_contact_email' && data_parameters.new_email !== '' && VerificationService.email( data_parameters.new_email ) ) ) {
						UrlService.post_data( url, data_parameters, function( data ) {
							if ( data.success ) {
								if ( !VerificationService.email( current_contact.email ) ) {
									vm.common.list.nb_contacts++;
									vm.common.list.nb_contacts_search++;
								}
								if ( url === 'jackmail_update_contact_email' && field_id === -1 ) {
									vm.common.list.contacts[ key ].id_list = '0';
									vm.common.list.contacts[ key ].blacklist = '0';
								}
								$rootScope.display_success( $rootScope.translations.contact_saved );
							}
							else {
								if ( current_contact.email === '' ) {
									vm.common.list.contacts[ key ].error = true;
								}
								else if ( field_id === -1 ) {
									vm.common.list.contacts[ key ].email = current_contact.email;
								}
								$rootScope.display_error( data.message );
							}
						}, function() {

						} );
					}
					else {
						$rootScope.display_error( $rootScope.translations.a_valid_email_address_is_required );
						if ( current_contact.email === '' ) {
							vm.common.list.contacts[ key ].error = true;
						}
						else {
							vm.common.list.contacts[ key ].email = current_contact.email;
						}
					}
					vm.common.focus_contact( key );
				}
			},

			add_header_column: function( vm ) {
				if ( vm.page_type === 'list' ) {
					var data_parameters = {
						'id_list': vm.url_id
					};
				}
				else {
					var data_parameters = {
						'id_campaign': vm.url_id
					};
				}
				UrlService.post_data( 'jackmail_add_header_column', data_parameters, function( data ) {
					if ( data.success ) {
						var id_columns = vm.common.list_fields.length;
						vm.common.list.list[ 'field' + id_columns ] = '';
						vm.common.update_list_fields_array();
						var i;
						var nb_contacts = vm.common.list.contacts.length;
						for ( i = 0; i < nb_contacts; i++ ) {
							vm.common.list.contacts[ i ][ 'field' + id_columns ] = '';
						}
						$rootScope.grid_service.display_column( id_columns );
						$timeout( function() {
							angular.element( 'th.jackmail_column_' + id_columns + ' span.jackmail_input_edit_container > span.dashicons-edit' ).click();
						} );
					}
					else {
						vm.common.get_list_data_reset();
					}
					$rootScope.display_success_error( data.success, $rootScope.translations.the_column_has_been_saved, '' );
				} );
			},

			edit_header_column: function( vm, field_id ) {
				if ( vm.common.list.list[ 'field' + field_id ] === '' ) {
					var i;
					var nb_fields = vm.common.list_fields.length - 1;
					var fields = [];
					for ( i = 0; i < nb_fields; i++ ) {
						fields.push( vm.common.list.list[ 'field' + ( i + 1 ) ] );
					}
					var fields_name_try = '( ' + ( nb_fields ) + ' )';
					i = 1;
					while ( fields.indexOf( fields_name_try ) !== -1 ) {
						fields_name_try = '( ' + ( nb_fields ) + '-' + i + ' )';
						i++;
					}
					vm.common.list.list[ 'field' + field_id ] = fields_name_try;
					vm.common.update_list_fields_array();
				}
				else {
					vm.common.list.list[ 'field' + field_id ] = vm.common.list.list[ 'field' + field_id ].toUpperCase();
					vm.common.update_list_fields_array();
					var data_parameters = {
						'field_id': field_id,
						'field': vm.common.list.list[ 'field' + field_id ]
					};
					if ( vm.page_type === 'list' ) {
						data_parameters[ 'id_list' ] = vm.url_id;
					}
					else {
						data_parameters[ 'id_campaign' ] = vm.url_id;
					}
					UrlService.post_data( 'jackmail_edit_header_column', data_parameters, function( data ) {
						if ( !data.success ) {
							vm.common.get_list_data_reset();
						}
						$rootScope.display_success_error( data.success, $rootScope.translations.the_column_has_been_saved, data.message );
					} );
				}
			},

			delete_header_column: function( vm, field_id ) {
				var data_parameters = {
					'field_id': field_id
				};
				if ( vm.page_type === 'list' ) {
					data_parameters[ 'id_list' ] = vm.url_id;
				}
				else {
					data_parameters[ 'id_campaign' ] = vm.url_id;
				}
				UrlService.post_data( 'jackmail_delete_header_column', data_parameters, function( data ) {
					vm.common.get_list_data_reset();
					$rootScope.display_success_error( data.success, $rootScope.translations.the_column_has_been_deleted, '' );
				} );
			},

			delete_contacts_selection_confirmation: function( vm ) {
				var filtering = false;
				if ( vm.common.list_search !== '' ) {
					filtering = true;
				}
				if ( vm.page_type === 'list' ) {
					if ( vm.only_list.targeting_rules.length > 0 ) {
						filtering = true;
					}
				}
				if ( !filtering && $rootScope.grid_service.nb_selected === vm.common.list.contacts.length ) {
					if ( vm.page_type === 'campaign' ) {
						var data_parameters = {
							'id_campaign': vm.url_id
						};
					}
					else {
						var data_parameters = {
							'id_list': vm.url_id
						};
					}
					UrlService.post_data( 'jackmail_delete_all_contacts', data_parameters, function( data ) {
						vm.common.get_list_data_reset();
						var message = $rootScope.translations.deleted_contacts;
						if ( $rootScope.grid_service.nb_selected === 1 ) {
							message = $rootScope.translations.deleted_contact;
						}
						$rootScope.display_success_error( data.success, message, '' );
					}, function() {

					} );
				}
				else {
					var contacts_selection_and_contacts_selection_type = vm.common.get_contacts_selection_and_contacts_selection_type();
					var data_parameters = {
						'search': vm.common.list_search,
						'targeting_rules': vm.common.get_targeting_rules(),
						'contacts_selection': JSON.stringify( contacts_selection_and_contacts_selection_type[ 'contacts_selection' ] ),
						'contacts_selection_type': contacts_selection_and_contacts_selection_type[ 'contacts_selection_type' ]
					};
					if ( vm.page_type === 'list' ) {
						data_parameters[ 'id_list' ] = vm.url_id;
					}
					else if ( vm.page_type === 'campaign' ) {
						data_parameters[ 'id_campaign' ] = vm.url_id;
					}
					UrlService.post_data( 'jackmail_delete_contacts_selection', data_parameters, function( data ) {
						vm.common.get_list_data_reset();
						var message = $rootScope.translations.deleted_contacts;
						if ( contacts_selection_and_contacts_selection_type[ 'contacts_selection' ].length === 1 ) {
							message = $rootScope.translations.deleted_contact;
						}
						$rootScope.display_success_error( data.success, message, '' );
					}, function() {

					} );
				}

			},

			range_by: function( vm, i ) {
				if ( vm.common.list.nb_contacts_search < 100 ) {
					vm.common.list.contacts = $rootScope.grid_service.grid_range( vm.common.list.contacts, i );
				}
				else {
					$rootScope.grid_service.grid_range_from_server( i );
					vm.common.get_list_data_reset();
				}
			},

			export_all_or_export_selection: function( vm, key ) {
				if ( key === 0 ) {
					vm.common.export_all();
				}
				else {
					vm.common.export_selection();
				}
			},

			export_all: function( vm, url_export_list ) {
				if ( vm.common.list.nb_contacts_search > 0 ) {
					if ( vm.common.list.nb_contacts_search <= 100 ) {
						ExportService.export_contact_file( vm.common.list, 'all' );
					}
					else {
						var part = 3000;
						var nb_parts = Math.ceil( vm.common.list.nb_contacts_search / part );
						var begin = 0;
						var i;
						var data_parameters = [];
						for ( i = 0; i < nb_parts; i++ ) {
							data_parameters.push( {
								'id': vm.url_id,
								'begin': begin.toString(),
								'sort_by': '',
								'sort_order': '',
								'search': vm.common.list_search,
								'targeting_rules': vm.common.get_targeting_rules()
							} );
							begin = begin + part;
						}
						UrlService.post_multiple_data( url_export_list, data_parameters, $rootScope.translations.downloading, function( data ) {
							ExportService.export_contact_file_multiple_data( data );
						}, function() {

						} );
					}
				}
				else {
					$rootScope.display_error( $rootScope.translations.list_is_empty );
				}
			},

			export_selection: function( vm ) {
				if ( $rootScope.grid_service.nb_selected > 0 ) {
					if ( vm.common.list.nb_contacts_search <= 100 ) {
						var contacts = vm.common.list.contacts;
						var i;
						var nb_contacts = contacts.length;
						var export_contacts = [];
						for ( i = 0; i < nb_contacts; i++ ) {
							if ( contacts[ i ].selected ) {
								export_contacts.push( contacts[ i ] );
							}
						}
						var data = {
							'list': vm.common.list.list,
							'contacts': export_contacts
						};
						ExportService.export_contact_file( data, 'all' );
					}
					else {
						if ( $rootScope.grid_service.nb_selected === vm.common.list.contacts.length ) {
							vm.common.export_all();
						}
						else if ( vm.common.manual_select_all && $rootScope.grid_service.nb_selected !== vm.common.list.contacts.length ) {
							var contacts_selection_and_contacts_selection_type = vm.common.get_contacts_selection_and_contacts_selection_type();
							var contacts_selection = contacts_selection_and_contacts_selection_type[ 'contacts_selection' ];
							if ( contacts_selection.length > 0 ) {
								var nb_rows_file = vm.common.list.nb_contacts_search - contacts_selection.length;
								var part = 3000;
								var nb_parts = Math.ceil( nb_rows_file / part );
								var begin = 0;
								var data_parameters = [];
								for ( i = 0; i < nb_parts; i++ ) {
									data_parameters.push( {
										'begin': begin.toString(),
										'contacts_selection': JSON.stringify( contacts_selection ),
										'search': vm.common.list_search,
										'targeting_rules': vm.common.get_targeting_rules()
									} );
									if ( vm.page_type === 'list' ) {
										data_parameters[ i ][ 'id_list' ] = vm.url_id;
									}
									else if ( vm.page_type === 'campaign' ) {
										data_parameters[ i ][ 'id_campaign' ] = vm.url_id;
									}
									begin = begin + part;
								}
								UrlService.post_multiple_data( 'jackmail_export_contacts_selection', data_parameters, $rootScope.translations.downloading, function( data ) {
									ExportService.export_contact_file_multiple_data( data );
								} );
							}
						}
						else {
							ExportService.export_contact_file( vm.common.list, 'selection' );
						}
					}
				}
				else {
					$rootScope.display_error( $rootScope.translations.selection_is_empty );
				}
			},


			get_contacts_selection_and_contacts_selection_type: function( vm ) {
				var i;
				var nb_contacts = vm.common.list.contacts.length;
				var contacts_selection = [];
				var manual_select_all = vm.common.manual_select_all;
				for ( i = 0; i < nb_contacts; i++ ) {
					if ( ( manual_select_all && !vm.common.list.contacts[ i ].selected ) || ( !manual_select_all && vm.common.list.contacts[ i ].selected ) ) {
						contacts_selection.push( vm.common.list.contacts[ i ].email );
					}
				}
				if ( contacts_selection.length === nb_contacts && manual_select_all === 'SELECTED' ) {
					return {
						'contacts_selection': [],
						'contacts_selection_type': 'NOT_SELECTED'
					};
				}
				return {
					'contacts_selection': contacts_selection,
					'contacts_selection_type': manual_select_all ? 'NOT_SELECTED' : 'SELECTED'
				};
			},

			get_list_data: function( vm, url_get_list, show_name_popup, callback ) {
				var data_parameters = {
					'id': vm.url_id,
					'begin': $rootScope.grid_service.begin,
					'sort_by': $rootScope.grid_service.grid_range_by_item,
					'sort_order': $rootScope.grid_service.grid_range_by_order ? 'DESC' : 'ASC',
					'search': vm.common.list_search,
					'targeting_rules': vm.common.get_targeting_rules()
				};
				UrlService.post_data( url_get_list, data_parameters, function( data ) {
					if ( callback !== undefined ) {						callback();
					}
					if ( vm.page_type === 'list' && data.list === null ) {
						$rootScope.go_page( 'lists' );
					}
					else {
						data = vm.common.split_list_fields( data );
						if ( vm.common.list_first_load || vm.common.list_top_load ) {
							vm.common.list = angular.copy( data );
							$rootScope.select_name_popup( show_name_popup );
							vm.common.check_list_editable();
							var show_grid = 0;
							if ( vm.common.list.nb_contacts > 0 || !vm.common.list_full_editable || vm.common.list_fields.length > 1 || vm.common.show_grid === 1 || ( vm.page_type === 'campaign' && ( vm.c_common.campaign.id_lists !== '0' || vm.show_import_lists ) ) ) {
								if ( vm.url_id === '0' ) {
									show_grid = 0;
								}
								else {
									show_grid = 1;
								}
							}
							vm.common.show_grid = show_grid;
							vm.common_list_detail.show_list_contact_detail = false;
							vm.common.update_list_fields_array();
							vm.common.list_top_load = false;
							if ( vm.common.show_grid === 1 ) {
								vm.common.display_grid();
							}
							if ( vm.common.list_first_load ) {
								vm.common.list_first_load = false;
								$rootScope.grid_service.init_columns_list( $rootScope.split_fields( vm.common.list.list.fields ) );
							}
						}
						else {
							var select_all = false;
							if ( $rootScope.grid_service.nb_selected === vm.common.list.contacts.length || vm.common.manual_select_all ) {
								select_all = true;
							}
							data.contacts = $rootScope.grid_service.merge_arrays( vm.common.list.contacts, data.contacts );
							vm.common.list = angular.copy( data );
							vm.common.update_list_fields_array();
							if ( select_all ) {
								vm.list.contacts = $rootScope.grid_service.grid_select_all_new( vm.common.list.contacts );
							}
						}
					}
				}, function() {

				} );
				if ( vm.page_type === 'campaign' ) {
					vm.c_common.get_lists_available();
				}
			},

			get_targeting_rules: function( vm ) {
				if ( vm.page_type === 'list' ) {
					var i;
					var nb_targeting_rules = vm.only_list.targeting_rules.length;
					var targeting_rules = [];
					for ( i = 0; i < nb_targeting_rules; i++ ) {
						if ( vm.only_list.targeting_rules[ i ].rule_content !== '' || vm.only_list.targeting_rules[ i ].rule_option === 'EMPTY' || vm.only_list.targeting_rules[ i ].rule_option === 'UNSUBSCRIBED' ) {
							targeting_rules.push( vm.only_list.targeting_rules[ i ] );
						}
					}
					return JSON.stringify( targeting_rules );
				}
				return '[]';
			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'ListService', [
	'$rootScope', '$timeout', '$filter', 'UrlService', 'EmailContentService', 'ExportService', 'VerificationService', '$sce',
	function( $rootScope, $timeout, $filter, UrlService, EmailContentService, ExportService, VerificationService, $sce ) {

		var show_connectors = false;

		var focused_list_name = '';

		var targeting_rule_options_email = [ '=', '!=', 'LIKE', 'UNSUBSCRIBED' ];

		var targeting_rule_options = [ '=', '!=', 'LIKE', 'EMPTY', 'NUMBER>', 'NUMBER<', 'DATE>', 'DATE<' ];

		var targeting_rule_and_or = [ 'AND', 'OR' ];

		function change_and_get_list_data_targeting_rules_reset_if_needed( vm, current, key ) {
			var value;
			var refresh = false;
			if ( current === 'rule_and_or' ) {
				value = targeting_rule_and_or[ key ];
				refresh = true;
			}
			else if ( current === 'rule_column' ) {
				value = key;
				refresh = true;
			}
			else if ( current === 'rule_option' ) {
				if ( vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ].rule_column === 0 ) {
					value = targeting_rule_options_email[ key ];
				}
				else {
					value = targeting_rule_options[ key ];
				}
				if ( value === 'EMPTY' || value === 'UNSUBSCRIBED' ) {
					vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ].rule_content = '';
				}
				if ( vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ].rule_option !== value ) {
					if ( vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ].rule_content !== ''
						|| ( vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ].rule_option === 'UNSUBSCRIBED'
							|| value === 'UNSUBSCRIBED' ) ) {
						refresh = true;
					}
				}
			}
			if ( vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ][ current ] !== value ) {
				if ( current === 'rule_column' ) {
					if ( vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ][ current ] === 0 && value !== 0 ) {
						var current_rule_option = vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ].rule_option;
						if ( current_rule_option !== '=' && current_rule_option !== '!=' && current_rule_option !== 'LIKE' ) {
							vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ].rule_option = '=';
						}
					}
				}
				vm.only_list.targeting_rules[ vm.only_list.targeting_rule_current ][ current ] = value;
				if ( refresh ) {
					vm.only_list.get_list_data_targeting_rules_reset();
				}
			}
		}

		var service = {

			display_hide_connectors: function( vm ) {
				vm.only_list.show_connectors = !vm.only_list.show_connectors;
				if ( vm.only_list.show_connectors && !show_connectors ) {
					show_connectors = true;
					var data_parameters = {
						'id': vm.url_id
					};
					UrlService.post_data( 'jackmail_display_connectors', data_parameters, function( data ) {
						if ( data.active ) {
							vm.only_list.connectors_actived = 1;
						}
						else {
							vm.only_list.connectors_actived = 0;
						}
					}, function() {

					} );
				}
				var i;
				var nb_fields = vm.common.list_fields.length;
				var columns_add = '';
				var columns_update = '';
				for ( i = 1; i < nb_fields; i++ ) {
					columns_add += '&<span>' + encodeURIComponent( vm.common.list_fields[ i ].toLowerCase() ) + '</span>=' + 'value';
					columns_update += '&<span>' + encodeURIComponent( vm.common.list_fields[ i ].toLowerCase() ) + '</span>=' + 'new_value';
				}
				if ( columns_update === '' ) {
					var action_update = [
						$sce.trustAsHtml(
							'<span>email</span>=' + $rootScope.translations.email_at_example_com + '&<span>new_email</span>=' + $rootScope.translations.new_at_example_com
						)
					];
				}
				else {
					var action_update = [
						$sce.trustAsHtml(
							'<span>email</span>=' + $rootScope.translations.email_at_example_com + '&<span>new_email</span>=' + $rootScope.translations.new_at_example_com + columns_update
						),
						$sce.trustAsHtml(
							'<span>email</span>=' + $rootScope.translations.email_at_example_com + columns_update
						)
					];
				}
				vm.only_list.connectors_example = {
					'action_add': {
						'url': $sce.trustAsHtml( $rootScope.settings.website_url + '?<span>jackmail</span>&<span>action</span>=add&<span>list</span>=' + vm.common.list.list.connector_key ),
						'parameters': [ $sce.trustAsHtml( '<span>email</span>=' + $rootScope.translations.email_at_example_com + columns_add ) ]
					},
					'action_update': {
						'url': $sce.trustAsHtml( $rootScope.settings.website_url + '?<span>jackmail</span>&<span>action</span>=update&<span>list</span>=' + vm.common.list.list.connector_key ),
						'parameters': action_update
					},
					'action_delete': {
						'url': $sce.trustAsHtml( $rootScope.settings.website_url + '?<span>jackmail</span>&<span>action</span>=delete&<span>list</span>=' + vm.common.list.list.connector_key ),
						'parameters': [ $sce.trustAsHtml( '<span>email</span>=' + $rootScope.translations.email_at_example_com ) ]
					}
				};
				vm.only_list.connectors_calculate_position();
			},

			connectors_calculate_position: function( vm ) {
				$timeout( function() {
					var height = angular.element( '.jackmail_confirmation_connectors' ).height();
					angular.element( '.jackmail_confirmation_connectors' ).css( { 'margin-top': '-' + ( height / 2 ) + 'px' } );
				} );
			},

			ok_list_name_popup: function( vm ) {
				var data_parameters = {
					'name': vm.common.list.list.name
				};
				UrlService.post_data( 'jackmail_create_list', data_parameters, function( data ) {
					if ( data.success ) {
						vm.url_id = data.id;
						vm.common.get_list_data_reset();
						UrlService.change_url_parameters_without_reload_and_history( 'list/' + data.id );
						vm.common.show_name_popup = false;
					}
					else {
						$rootScope.display_error( $rootScope.translations.list_name_must_be_unique );
					}
				}, function() {

				} );

			},

			cancel_list_name_popup: function( vm ) {
				$rootScope.change_page( 'lists' );
			},

			focus_list_name: function( vm ) {
				focused_list_name = vm.common.list.list.name;
				angular.element( '.jackmail_name .jackmail_content_editable' ).focus();
				vm.common.name_editing = true;
			},

			blur_list_name: function( vm ) {
				if ( vm.common.name_editing ) {
					var data_parameters = {
						'id': vm.url_id,
						'name': vm.common.list.list.name
					};
					UrlService.post_data( 'jackmail_save_name', data_parameters, function( data ) {
						$rootScope.display_success_error( data.success, $rootScope.translations.the_name_of_the_list_has_been_saved, $rootScope.translations.list_name_must_be_unique );
						if ( !data.success ) {
							vm.common.list.list.name = focused_list_name;
						}
					}, function() {

					} );
					vm.common.name_editing = false;
				}
			},

			create_campaign_with_list: function( vm ) {
				$rootScope.display_success( $rootScope.translations.creating_campaign );
				UrlService.post_data( 'jackmail_create_campaign_with_data', {}, function( data ) {
					if ( data.success ) {
						var contacts_selection_and_contacts_selection_type = vm.common.get_contacts_selection_and_contacts_selection_type();
						var id_campaign = data.id;
						var data_parameters = {
							'id_campaign': id_campaign,
							'id_lists': $rootScope.join( [ vm.url_id ] ),
							'search': vm.common.list_search,
							'targeting_rules': vm.common.get_targeting_rules(),
							'contacts_selection': JSON.stringify( contacts_selection_and_contacts_selection_type[ 'contacts_selection' ] ),
							'contacts_selection_type': contacts_selection_and_contacts_selection_type[ 'contacts_selection_type' ]
						};
						UrlService.post_data( 'jackmail_set_campaign_lists', data_parameters, function() {
							$rootScope.change_page_with_parameters( 'campaign', id_campaign + '/create' );
						}, function() {

						} );
					}
					else {
						$rootScope.display_error_writable();
					}
				}, function() {

				} );
			},

			display_hide_targeting_settings: function( vm ) {
				vm.only_list.show_targeting_settings = !vm.only_list.show_targeting_settings;
				$timeout( function() {
					$rootScope.grid_service.refresh_grid_max_height( 'refresh' );
				} );
				if ( vm.only_list.targeting_rules.length === 0 ) {
					vm.only_list.add_targeting_rule();
				}
			},

			add_targeting_rule: function( vm ) {
				$timeout( function() {
					$rootScope.grid_service.refresh_grid_max_height( 'refresh' );
				} );
				vm.only_list.targeting_rules.push( {
					'rule_and_or': 'AND',
					'rule_column': 0,
					'rule_option': '=',
					'rule_content': ''
				} );
			},

			get_targeting_rule_current: function( vm, key ) {
				vm.only_list.targeting_rule_current = key;
			},

			remove_targeting_rule: function( vm, key ) {
				vm.only_list.targeting_rules.splice( key, 1 );
				if ( vm.only_list.targeting_rules.length === 0 ) {
					vm.only_list.show_targeting_settings = false;
				}
				$timeout( function() {
					$rootScope.grid_service.refresh_grid_max_height( 'refresh' );
				} );
				vm.only_list.get_list_data_targeting_rules_reset();
			},

			select_targeting_rule_and_or: function( vm, key ) {
				change_and_get_list_data_targeting_rules_reset_if_needed( vm, 'rule_and_or', key );
			},

			select_targeting_rule_column: function( vm, key ) {
				change_and_get_list_data_targeting_rules_reset_if_needed( vm, 'rule_column', key );
			},

			select_targeting_rule_option: function( vm, key ) {
				change_and_get_list_data_targeting_rules_reset_if_needed( vm, 'rule_option', key );
			},

			get_targeting_rule_option_title: function( vm, rule_column, rule_option ) {
				var select = targeting_rule_options;
				if ( rule_column === 0 ) {
					select = targeting_rule_options_email;
				}
				var key = 0;
				var i;
				var length = select.length;
				for ( i = 0; i < length; i++ ) {
					if ( select[ i ] === rule_option ) {
						key = i;
						break;
					}
				}
				if ( rule_column === 0 ) {
					return vm.only_list.targeting_rule_options_email[ key ];
				}
				else {
					return vm.only_list.targeting_rule_options[ key ];
				}
			},

			get_targeting_rule_and_or_title: function( vm, rule_column, rule_option ) {
				var select = targeting_rule_and_or;
				var key = 0;
				var i;
				var length = select.length;
				for ( i = 0; i < length; i++ ) {
					if ( select[ i ] === rule_option ) {
						key = i;
						break;
					}
				}
				return vm.only_list.targeting_rule_and_or[ key ];
			},

			get_list_data_targeting_rules_reset: function( vm ) {
				$rootScope.display_success( $rootScope.translations.searching );
				var i;
				var nb_targeting_rules = vm.only_list.targeting_rules.length;
				var nb_searched_targeting_rules = 0;
				for ( i = 0; i < nb_targeting_rules; i++ ) {
					if ( vm.only_list.targeting_rules[ i ].rule_content !== '' || vm.only_list.targeting_rules[ i ].rule_option === 'EMPTY' || vm.only_list.targeting_rules[ i ].rule_option === 'UNSUBSCRIBED' ) {
						nb_searched_targeting_rules++;
					}
				}
				vm.only_list.nb_searched_targeting_rules = nb_searched_targeting_rules;
				vm.common.get_list_data_reset();
			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'PluginService', [
	'$rootScope',
	function( $rootScope ) {

		var service = {

			get_compatible_plugins: function() {
				return {
					'bloom': {
						'active': false,
						'selected': false,
						'name': 'Bloom',
						'description': $rootScope.translations.plugin_bloom_description
					},
					'contactform7': {
						'active': false,
						'selected': false,
						'name': 'Contact Form 7',
						'description': $rootScope.translations.plugin_contactform7_description
					},
					'formidableforms': {
						'active': false,
						'selected': false,
						'name': 'Formidable Forms',
						'description': $rootScope.translations.plugin_formidableforms_description
					},
					'gravityforms': {
						'active': false,
						'selected': false,
						'name': 'Gravity Forms',
						'description': $rootScope.translations.plugin_gravityforms_description
					},
					'mailpoet2': {
						'active': false,
						'selected': false,
						'name': 'MailPoet 2',
						'description': $rootScope.translations.plugin_mailpoet2_description
					},
					'mailpoet3': {
						'active': false,
						'selected': false,
						'name': 'MailPoet 3',
						'description': $rootScope.translations.plugin_mailpoet3_description
					},
					'ninjaforms': {
						'active': false,
						'selected': false,
						'name': 'Ninja Forms',
						'description': $rootScope.translations.plugin_ninjaforms_description
					},
					'popupbysupsystic': {
						'active': false,
						'selected': false,
						'name': 'PopUp by Supsystic',
						'description': $rootScope.translations.plugin_popupbysupsystic_description
					},
					'woocommerce': {
						'active': false,
						'selected': false,
						'name': 'WooCommerce',
						'description': $rootScope.translations.plugin_woocommerce_description
					}
				};
			},

			get_plugins_to_display: function( plugins, data ) {
				var i;
				var nb = data.length;
				for ( i = 0; i < nb; i++ ) {
					plugins[ data[ i ].plugin ].active = true;
					if ( data[ i ].selected ) {
						plugins[ data[ i ].plugin ].selected = true;
					}
				}
				return plugins;
			},

			get_active_plugins: function( plugins ) {
				var active_plugins = [];
				if ( plugins.bloom.active && plugins.bloom.selected ) {
					active_plugins.push( 'bloom' );
				}
				if ( plugins.contactform7.active && plugins.contactform7.selected ) {
					active_plugins.push( 'contactform7' );
				}
				if ( plugins.formidableforms.active && plugins.formidableforms.selected ) {
					active_plugins.push( 'formidableforms' );
				}
				if ( plugins.gravityforms.active && plugins.gravityforms.selected ) {
					active_plugins.push( 'gravityforms' );
				}
				if ( plugins.mailpoet2.active && plugins.mailpoet2.selected ) {
					active_plugins.push( 'mailpoet2' );
				}
				if ( plugins.mailpoet3.active && plugins.mailpoet3.selected ) {
					active_plugins.push( 'mailpoet3' );
				}
				if ( plugins.ninjaforms.active && plugins.ninjaforms.selected ) {
					active_plugins.push( 'ninjaforms' );
				}
				if ( plugins.popupbysupsystic.active && plugins.popupbysupsystic.selected ) {
					active_plugins.push( 'popupbysupsystic' );
				}
				if ( plugins.woocommerce.active && plugins.woocommerce.selected ) {
					active_plugins.push( 'woocommerce' );
				}
				return active_plugins;
			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'UrlService', [
	'$http', '$rootScope', '$location', '$timeout', '$window',
	function( $http, $rootScope, $location, $timeout, $window ) {

		function post_data( display_loader, action, data, callback_success, callback_error ) {
			if ( display_loader ) {
				service.request_data();
			}
			var params = angular.element.param( data ) + '&action=' + action + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ];
			$http( {
				method: 'POST',
				url: $rootScope.settings.ajax_url,
				data: params,
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				transformResponse: [ function( data ) {
					return data;
				} ]
			} ).then(
				function( response ) {
					check_received_data( display_loader, response.data, callback_success, callback_error );
				},
				function( response ) {
					console.log( response );
					if ( display_loader ) {
						service.response_data();
					}
					service.error_message( response );
					callback_error( response.data );
				}
			);
		}

		function check_received_data( display_loader, data, callback_success, callback_error ) {
			if ( display_loader ) {
				service.response_data();
			}
			try {
				data = JSON.parse( data );
				callback_success( data );
			}
			catch ( e ) {
				console.log( e );
				service.error_message( '' );
				callback_error( { 'success': false } );
			}
		}

		var service = {

			post_data: function( action, data, callback_success, callback_error ) {
				post_data( true, action, data, callback_success, callback_error );
			},

			post_data_background: function( action, data, callback_success, callback_error ) {
				post_data( false, action, data, callback_success, callback_error );
			},

			get_file_data: function( url, callback_success, callback_error ) {
				service.request_data();
				$http( {
					method: 'GET',
					url: url,
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					transformResponse: [ function( data ) {
						return data;
					} ]
				} ).then(
					function( response ) {
						service.response_data();
						callback_success( response.data );
					},
					function( response ) {
						console.log( response );
						service.response_data();
						service.error_message( response );
						callback_error( response.data );
					}
				);
			},

			post_multiple_data: function( action, data, message, callback_success, callback_error ) {
				var current_part = 0;
				var nb_parts = data.length;

				if ( nb_parts > 0 ) {
					service.request_data();
					var result = [];
					next_part();
				}

				function next_part() {
					if ( message !== '' ) {
						$rootScope.jackmail_success = true;
						$rootScope.jackmail_error = false;
						var current_message = message;
						if ( nb_parts > 1 ) {
							current_message += ' (' + parseInt( ( current_part + 1 ) * ( 100 / nb_parts ) ) + ' %)';
						}
						$rootScope.jackmail_message = current_message;
					}
					var params = angular.element.param( data[ current_part ] ) + '&action=' + action + '&key=' + $rootScope.settings.key + '&nonce=' + $rootScope.settings.urls[ action ];
					$http( {
						method: 'POST',
						url: $rootScope.settings.ajax_url,
						data: params,
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						transformResponse: [ function( data ) {
							return data;
						} ]
					} ).then(
						function( response ) {
							current_part++;
							if ( response.data !== '{"success":true}' && response.data !== '{"success":false}' ) {
								result.push( JSON.parse( response.data ) );
							}
							if ( current_part < nb_parts ) {
								next_part();
							}
							else {
								service.response_data();
								if ( result.length === 0 ) {
									callback_success( { 'success': true } );
								}
								else {
									callback_success( result );
								}
								if ( message !== '' ) {
									$rootScope.jackmail_success = false;
									$rootScope.jackmail_message = '';
								}
							}
						},
						function( response ) {
							console.log( response );
							service.error_message( response );
							callback_error( { 'success': false } );
						}
					);
				}

			},

			error_message: function( response ) {
				if ( response.status !== undefined && response.statusText !== undefined ) {
					if ( response.status >= 300 ) {
						var message = $rootScope.translations.an_error_occurred + ' (' + $rootScope.translations.server_message;
						message += ' ' + response.status;
						if ( response.statusText !== '' ) {
							message += ' - ' + response.statusText;
						}
						message += ')';
						$rootScope.display_success_error( false, '', message );
					}
				}
			},

			request_data: function() {
				$timeout( function() {
					$rootScope.nb_loading++;
					if ( $rootScope.nb_loading === 1 ) {
						$rootScope.loading = true;
					}
				} );
			},

			response_data: function() {
				$timeout( function() {
					$rootScope.nb_loading--;
					if ( $rootScope.nb_loading === 0 ) {
						$rootScope.loading = false;
					}
				} );
			},

			change_url_parameters_without_reload: function( url ) {
				if ( $rootScope.settings.analytics ) {
					$window.ga( 'send', 'pageview', $location.path() );
				}
				$location.path( url, false );
			},

			change_url_parameters_without_reload_and_history: function( url ) {
				$location.path( url, false ).replace();
			}

		};

		return service;

	} ] );

angular.module( 'jackmail.services' ).factory( 'VerificationService', [
	function() {
		return {

			email: function( email ) {
				var regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				if ( regex.test( email ) ) {
					return true;
				}
				return false;
			},

			differents_arrays: function( a, b ) {
				delete a[ '$$hashKey' ];
				delete b[ '$$hashKey' ];
				if ( JSON.stringify( a ) === JSON.stringify( b ) ) {
					return false;
				}
				return true;
			}

		};
	} ] );