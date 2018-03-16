'use strict';

angular.module( 'jackmail.controllers' ).controller( 'AccountConnectionController', [
	'$rootScope', 'UrlService', 'VerificationService',
	function( $rootScope, UrlService, VerificationService ) {

		var vm = this;
		console.log( 'test' );

		vm.current_page_type = 'popup';

		if ( window.location.href.indexOf( 'jackmail_installation' ) !== -1 ) {
			vm.current_page_type = 'installation';
		}

		vm.email = '';

		vm.create_login = {
			'password': '',
			'password_confirmation': '',
			'lastname': '',
			'firstname': ''
		};

		vm.login_error = {
			'terms': false,
			'email': false,
			'password': false,
			'password_confirmation': false,
			'lastname': false,
			'firstname': false
		};

		vm.login = {
			'password': ''
		};

		vm.terms = false;

		UrlService.post_data( 'jackmail_account_info', {}, function( data ) {
			vm.email = data.email;
			vm.create_login.lastname = data.lastname;
			vm.create_login.firstname = data.firstname;
		}, function() {

		} );

		function init_account_messages() {
			vm.account_created = false;
			vm.account_not_actived = false;
			vm.account_ids_not_valid = false;
			vm.account_resend_activation_email = false;
		}

		init_account_messages();

		vm.recheck_account_creation = function() {
			if ( vm.terms && vm.login_error.terms ) {
				vm.terms = false;
			}
			if ( vm.create_login.lastname !== '' && vm.login_error.lastname ) {
				vm.login_error.lastname = false;
			}
			if ( vm.create_login.firstname !== '' && vm.login_error.firstname ) {
				vm.login_error.firstname = false;
			}
			if ( VerificationService.email( vm.email ) && vm.login_error.email ) {
				vm.login_error.email = false;
			}
			if ( vm.create_login.password.length >= 8 && vm.login_error.password ) {
				vm.login_error.password = false;
			}
			if ( vm.create_login.password_confirmation === vm.create_login.password && vm.login_error.password_confirmation ) {
				vm.login_error.password_confirmation = false;
			}
		};

		vm.check_account_creation = function() {
			var login_error = {
				'terms': false,
				'email': false,
				'password': false,
				'password_confirmation': false,
				'lastname': false,
				'firstname': false
			};
			if ( !vm.terms ) {
				login_error.terms = true;
				$rootScope.display_error( $rootScope.translations.you_must_accept_the_general_terms_of_jackmail );
			}
			if ( vm.create_login.lastname === '' ) {
				login_error.lastname = true;
			}
			if ( vm.create_login.firstname === '' ) {
				login_error.firstname = true;
			}
			if ( !VerificationService.email( vm.email ) ) {
				login_error.email = true;
			}
			if ( vm.create_login.password.length < 8 ) {
				login_error.password = true;
			}
			if ( vm.create_login.password !== vm.create_login.password_confirmation ) {
				login_error.password_confirmation = true;
				$rootScope.display_error( $rootScope.translations.password_confirmation_doesn_t_match_password );
			}
			vm.login_error = login_error;
			if ( !vm.login_error.terms && !vm.login_error.lastname && !vm.login_error.firstname && !vm.login_error.email
				&& !vm.login_error.password && !vm.login_error.password_confirmation ) {
				return true;
			}
			return false;
		};

		vm.account_creation = function() {
			init_account_messages();
			if ( vm.check_account_creation() ) {
				var data_parameters = {
					'email': vm.email,
					'password': vm.create_login.password,
					'lastname': vm.create_login.lastname,
					'firstname': vm.create_login.firstname
				};
				UrlService.post_data( 'jackmail_account_creation', data_parameters, function( data ) {
					if ( data.success ) {
						vm.account_created = true;
						vm.show_login_form( 'connection' );
					}
					else {
						$rootScope.display_error( $rootScope.translations.an_error_occurred );
					}
				}, function() {

				} );
			}
		};

		vm.account_connection = function() {
			init_account_messages();
			var data_parameters = {
				'email': vm.email,
				'password': vm.login.password
			};
			UrlService.post_data( 'jackmail_account_connection', data_parameters, function( data ) {
				if ( data.success ) {
					if ( angular.element( '.jackmail_notice' ).length !== 0 ) {
						window.location.reload();
					}
					else {
						$rootScope.email = vm.email;
						$rootScope.settings.is_authenticated = true;
						$rootScope.hide_account_connection_popup();
					}
				}
				else {
					if ( data.message === 'NOT_ACTIVED' ) {
						vm.account_not_actived = true;
					}
					else {
						vm.account_ids_not_valid = true;
					}
				}
			}, function() {

			} );
		};

		vm.resend_activation_email = function() {
			init_account_messages();
			var data_parameters = {
				'email': vm.email,
				'password': vm.login.password
			};
			UrlService.post_data( 'jackmail_account_resend_activation_email', data_parameters, function( data ) {
				if ( data.success ) {
					vm.account_resend_activation_email = true;
				}
			}, function() {

			} );
		};

		vm.account_reset = function() {
			init_account_messages();
			var data_parameters = {
				'email': vm.email
			};
			if ( VerificationService.email( vm.email ) ) {
				UrlService.post_data( 'jackmail_account_reset', data_parameters, function() {
					vm.show_login_form( 'new_password_confirm' );
				}, function() {

				} );
			}
			else {
				vm.show_login_form( 'new_password_confirm' );
			}
		};

		vm.show_login_form = function( page ) {
			$rootScope.show_account_connection_popup_form = page;
		};

		vm.check_uncheck_terms = function() {
			vm.terms = !vm.terms;
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'CampaignsController', [
	'UrlService', '$timeout', '$window', '$rootScope', 'GridService', '$interval', '$filter',
	function( UrlService, $timeout, $window, $rootScope, GridService, $interval, $filter ) {

		var vm = this;

		$rootScope.grid_service = new GridService();

		var all_campaigns = [];
		var campaigns = [];
		vm.nb_campaigns = -1;

		vm.campaigns_grid = [];
		vm.nb_campaigns_grid = -1;

		vm.campaigns_status = [
			$rootScope.translations.draft,
			$rootScope.translations.sent,
			$rootScope.translations.scheduled,
			$rootScope.translations.sending,
			$rootScope.translations.refused,
			$rootScope.translations.error
		];

		vm.filter = {
			'emailing': $rootScope.settings.campaign_emailing,
			'scenario': $rootScope.settings.campaign_scenario,
			'status': {
				'draft': true,
				'sent': true,
				'scheduled': true,
				'sending': true,
				'refused': true,
				'error': true
			},
			'selected_date1': $rootScope.settings.selected_date1,
			'selected_date2': $rootScope.settings.selected_date2
		};

		var current_name = '';

		$interval( function() {
			var i;
			var nb_campaigns = all_campaigns.length;
			var refresh = false;
			for ( i = 0; i < nb_campaigns; i++ ) {
				if ( all_campaigns[ i ].status === 'PROCESS_SENDING' || all_campaigns[ i ].status === 'PROCESS_SCHEDULED' ) {
					refresh = true;
					break;
				}
				if ( all_campaigns[ i ].status === 'SENDING' || all_campaigns[ i ].status === 'SCHEDULED' ) {
					if ( $filter( 'date1DiffDate2' )( all_campaigns[ i ].send_option_date_begin_gmt, $rootScope.settings.current_time ) < 0 ) {
						refresh = true;
						break;
					}
				}
			}
			if ( refresh ) {
				get_campaigns( true );
			}
			$rootScope.settings.current_time = $rootScope.add_date_interval( $rootScope.settings.current_time, 120 );
		}, 60000 );

		vm.create_campaign = function() {
			$rootScope.change_page_with_parameters( 'campaign', '0/contacts' );
		};

		vm.create_scenario = function() {
			$rootScope.change_page( 'scenario_choice' );
		};

		vm.go_edit_page = function( type, url, json ) {
			if ( $rootScope.settings.emailbuilder_installed ) {
				$rootScope.change_page_with_parameters( type, url );
			}
			else {
				if ( json === '1' ) {
					$rootScope.display_emailbuilder_popup();
				}
				else {
					$rootScope.change_page_with_parameters( type, url );
				}
			}
		};

		function get_campaigns( refresh_status ) {
			var data_parameters = {
				'refresh_status': refresh_status
			};
			UrlService.post_data( 'jackmail_get_campaigns', data_parameters, function( data ) {
				vm.nb_campaigns = data.length;
				all_campaigns = data;
				$timeout( function() {
					change_filter( false );
				} );
			}, function() {

			} );
		}

		get_campaigns( true );

		vm.get_display_campaign_status = function( campaign ) {
			var text = $filter( 'campaignStatus' )( campaign.status );
			if ( campaign.status === 'PROCESS_SCHEDULED' || campaign.status === 'SCHEDULED' ) {
				text += ' (' + $filter( 'formatedDate' )( campaign.send_option_date_begin_gmt );
			}
			if ( campaign.status === 'REFUSED' && campaign.status_detail !== '' ) {
				if ( $rootScope.translations[ 'refused_' + campaign.status_detail.toLowerCase() ] ) {
					text += '<br/>(' + $rootScope.translations[ 'refused_' + campaign.status_detail.toLowerCase() ] + ')';
				}
			}
			return text;
		};

		vm.change_filter_date = function( date1, date2 ) {
			if ( vm.filter.selected_date1 !== date1 || vm.filter.selected_date2 !== date2 ) {
				vm.filter.selected_date1 = date1;
				vm.filter.selected_date2 = date2;
				change_filter( true );
			}
		};

		function change_filter( update_cookie ) {
			if ( vm.nb_campaigns !== -1 ) {
				var selected_date1 = new Date( vm.filter.selected_date1.substring( 0, 10 ) );
				var selected_date2 = new Date( vm.filter.selected_date2.substring( 0, 10 ) );
				var i;
				var nb_campaigns = all_campaigns.length;
				var new_campaigns = [];
				for ( i = 0; i < nb_campaigns; i++ ) {
					var campaign_date_gmt = new Date( all_campaigns[ i ].updated_date_gmt.substring( 0, 10 ) );
					if ( campaign_date_gmt >= selected_date1 && campaign_date_gmt <= selected_date2 ) {
						if ( vm.filter.emailing ) {
							if ( all_campaigns[ i ].type === 'campaign' ) {
								if ( ( vm.filter.status.draft && all_campaigns[ i ].status === 'DRAFT' )
									|| ( vm.filter.status.sent && all_campaigns[ i ].status === 'SENT' )
									|| ( vm.filter.status.scheduled && ( all_campaigns[ i ].status === 'PROCESS_SCHEDULED' || all_campaigns[ i ].status === 'SCHEDULED' ) )
									|| ( vm.filter.status.sending && ( all_campaigns[ i ].status === 'PROCESS_SENDING' || all_campaigns[ i ].status === 'SENDING' ) )
									|| ( vm.filter.status.refused && all_campaigns[ i ].status === 'REFUSED' )
									|| ( vm.filter.status.error && all_campaigns[ i ].status === 'ERROR' ) ) {
									new_campaigns.push( all_campaigns[ i ] );
								}
							}
						}
						if ( vm.filter.scenario ) {
							if ( all_campaigns[ i ].type.indexOf( 'scenario' ) !== -1 ) {
								new_campaigns.push( all_campaigns[ i ] );
							}
						}
					}
				}
				campaigns = $rootScope.grid_service.grid_order_by( new_campaigns, 'updated_date_gmt', 'DESC' );
				generate_grid();
				if ( update_cookie ) {
					var data_parameters = {
						'selected_date1': vm.filter.selected_date1,
						'selected_date2': vm.filter.selected_date2,
						'campaign_emailing': vm.filter.emailing,
						'campaign_scenario': vm.filter.scenario
					};
					UrlService.post_data( 'jackmail_update_cookies', data_parameters, function( data ) {

					}, function() {

					} );
				}
			}
		}

		vm.change_option = function( option ) {
			vm.filter[ option ] = !vm.filter[ option ];
			change_filter( true );
		};

		vm.filter_status = function( key ) {
			if ( key === 0 ) {
				vm.filter.status.draft = !vm.filter.status.draft;
			}
			else if ( key === 1 ) {
				vm.filter.status.sent = !vm.filter.status.sent;
			}
			else if ( key === 2 ) {
				vm.filter.status.scheduled = !vm.filter.status.scheduled;
			}
			else if ( key === 3 ) {
				vm.filter.status.sending = !vm.filter.status.sending;
			}
			else if ( key === 4 ) {
				vm.filter.status.refused = !vm.filter.status.refused;
			}
			else if ( key === 5 ) {
				vm.filter.status.error = !vm.filter.status.error;
			}
			change_filter( false );
		};

		angular.element( $window ).on( 'resize', function() {
			generate_grid();
		} );

		function generate_grid() {
			vm.campaigns_grid = $rootScope.generate_grid( campaigns );
			vm.nb_campaigns_grid = vm.campaigns_grid.length;
		}

		vm.display_actions = function( key, subkey ) {
			vm.campaigns_grid[ key ][ subkey ].show_actions = true;
		};

		vm.hide_actions = function( key, subkey ) {
			vm.campaigns_grid[ key ][ subkey ].show_actions = false;
		};

		vm.duplicate_campaign = function( id ) {
			var data_parameters = {
				'id': id
			};
			UrlService.post_data( 'jackmail_duplicate_campaign', data_parameters, function( data ) {
				get_campaigns( false );
				$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_campaign_has_been_duplicated );
			}, function() {

			} );
		};

		vm.cancel_scheduled_campaign = function( id ) {
			var data_parameters = {
				'id': id
			};
			UrlService.post_data( 'jackmail_cancel_scheduled_campaign', data_parameters, function( data ) {
				$rootScope.display_success_error( data.success, $rootScope.translations.the_campaign_has_been_canceled, $rootScope.translations.an_error_occurred_while_the_campaign_was_canceled );
				get_campaigns( false );
			}, function() {

			} );
		};

		vm.delete_campaign = function( id ) {
			var data_parameters = {
				'id': id
			};
			UrlService.post_data( 'jackmail_delete_campaign', data_parameters, function( data ) {
				$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_campaign_has_been_deleted );
				get_campaigns( false );
			}, function() {

			} );
		};

		vm.delete_confirm = function( key, subkey ) {
			vm.campaigns_grid[ key ][ subkey ].show_delete_confirmation = true;
		};

		vm.delete_cancel = function( key, subkey ) {
			vm.campaigns_grid[ key ][ subkey ].show_delete_confirmation = false;
		};

		vm.delete_scenario = function( id ) {
			var data_parameters = {
				'id': id
			};
			UrlService.post_data( 'jackmail_delete_scenario', data_parameters, function( data ) {
				get_campaigns( false );
				$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_scenario_was_deleted );
			}, function() {

			} );
		};

		vm.deactivate_scenario = function( id ) {
			var data_parameters = {
				'id': id
			};
			UrlService.post_data( 'jackmail_deactivate_scenario', data_parameters, function( data ) {
				get_campaigns( false );
				$rootScope.display_success_error( data.success, $rootScope.translations.the_scenario_was_disabled, '' );
			}, function() {

			} );
		};

		vm.click_campaign_name = function( $event ) {
			angular.element( $event.currentTarget ).parent()
				.children( 'span:nth-child(1)' )
				.children( 'span' )
				.children( 'span' )
				.children( '.jackmail_content_editable_span' )
				.focus();
		};

		vm.focus_campaign_name = function( name ) {
			current_name = name;
		};

		vm.save_campaign_name = function( id, type, name ) {
			if ( current_name !== name ) {
				var data_parameters = {
					'id': id,
					'name': name
				};
				UrlService.post_data( 'jackmail_edit_' + type + '_name', data_parameters, function( data ) {
					$rootScope.display_success_error( data.success, $rootScope.translations[ 'the_' + type + '_name_has_been_saved' ], '' );
				}, function() {

				} );
			}
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'InstallationController', [
	'$rootScope', 'UrlService', 'PluginService', '$sce', '$window', '$location',
	function( $rootScope, UrlService, PluginService, $sce, $window, $location ) {

		var vm = this;

		vm.current_step = 1;

		vm.plugins = PluginService.get_compatible_plugins();
		vm.nb_plugins_actived = -1;

		$rootScope.emailbuilder_popup_licence = '';

		if ( $rootScope.settings.openssl_random_pseudo_bytes_extension_function_exists && $rootScope.settings.gzdecode_gzencode_function_exists
			&& $rootScope.settings.base64_decode_base64_encode_function_exists && $rootScope.settings.json_encode_json_decode_function_exists
			&& $rootScope.settings.jackmail_file_path_exists && $rootScope.settings.jackmail_file_path_writable ) {
			vm.configuration_ok = true;
		}
		else {
			vm.configuration_ok = false;
		}

		UrlService.post_data( 'jackmail_get_plugins_init', {}, function( data ) {
			vm.plugins = PluginService.get_plugins_to_display( vm.plugins, data );
			vm.nb_plugins_actived = data.length;
		}, function() {

		} );

		UrlService.post_data( 'jackmail_get_emailbuilder_licence', {}, function( data ) {
			if ( data.licence ) {
				if ( data.licence.length > 2000 && data.licence.indexOf( 'EmailBuilder' ) !== -1 ) {
					$rootScope.emailbuilder_popup_licence = $sce.trustAsHtml( data.licence );
				}
			}
		}, function() {

		} );

		vm.reload_page = function() {
			window.location.reload();
		};

		vm.skip_account_creation = function() {
			if ( $rootScope.settings.analytics ) {
				$window.ga( 'send', {
					'hitType': 'event',
					'appName': 'Jackmail',
					'appVersion': $rootScope.settings.version,
					'screenName': $location.url(),
					'eventCategory': 'Ship account creation',
					'eventLabel': 'Ship account creation',
					'eventAction': 'click',
					'eventValue': 0
				} );
			}
			vm.go_step( 5 );
		};

		vm.go_step = function( step ) {
			if ( vm.configuration_ok ) {
				if ( vm.current_step > step ) {
					vm.current_step = step;
				}
				else if ( step - vm.current_step === 1 ) {
					vm.current_step = step;
				}
				if ( vm.current_step === 5 ) {
					is_configured();
				}
			}
		};

		vm.install_emailbuilder = function() {
			UrlService.post_data( 'jackmail_install_emailbuilder', {}, function( data ) {
				if ( data.success ) {
					vm.go_step( 3 );
				}
			}, function() {

			} );
		};

		vm.select_or_unselect_plugin = function( plugin ) {
			vm.plugins[ plugin ].selected = !vm.plugins[ plugin ].selected;
		};

		vm.import_plugins = function() {
			var plugins = [];
			if ( vm.plugins.bloom.active && vm.plugins.bloom.selected ) {
				plugins.push( 'bloom' );
			}
			if ( vm.plugins.contactform7.active && vm.plugins.contactform7.selected ) {
				plugins.push( 'contactform7' );
			}
			if ( vm.plugins.formidableforms.active && vm.plugins.formidableforms.selected ) {
				plugins.push( 'formidableforms' );
			}
			if ( vm.plugins.gravityforms.active && vm.plugins.gravityforms.selected ) {
				plugins.push( 'gravityforms' );
			}
			if ( vm.plugins.mailpoet2.active && vm.plugins.mailpoet2.selected ) {
				plugins.push( 'mailpoet2' );
			}
			if ( vm.plugins.mailpoet3.active && vm.plugins.mailpoet3.selected ) {
				plugins.push( 'mailpoet3' );
			}
			if ( vm.plugins.ninjaforms.active && vm.plugins.ninjaforms.selected ) {
				plugins.push( 'ninjaforms' );
			}
			if ( vm.plugins.popupbysupsystic.active && vm.plugins.popupbysupsystic.selected ) {
				plugins.push( 'popupbysupsystic' );
			}
			if ( vm.plugins.woocommerce.active && vm.plugins.woocommerce.selected ) {
				plugins.push( 'woocommerce' );
			}
			var data_parameters = {
				'plugins': $rootScope.join( plugins )
			};
			UrlService.post_data( 'jackmail_import_plugins', data_parameters, function() {

			}, function() {

			} );
			vm.go_step( 4 );
		};

		function is_configured() {
			UrlService.post_data( 'jackmail_is_configured', {}, function() {

			}, function() {

			} );
			
			angular.element( '#toplevel_page_jackmail_installation > a, #toplevel_page_jackmail_installation > ul.wp-submenu > li.current > a.current' ).attr( 'href', 'admin.php?page=jackmail_campaigns' );
		}

		vm.show_login_form = function( page ) {
			vm.login_form = page;
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'ListAndCampaignController', [
	'$rootScope', 'UrlService', '$location', '$routeParams', '$timeout', 'VerificationService',
	'$interval', '$sce', '$filter', 'ExportService', 'GridService', '$window', 'EmailContentService', 'CampaignService',
	'ListAndCampaignCommonService', 'ScenarioService', 'ListService', 'CampaignCommonService',
	function( $rootScope, UrlService, $location, $routeParams, $timeout, VerificationService,
			  $interval, $sce, $filter, ExportService, GridService, $window, EmailContentService, CampaignService,
			  ListAndCampaignCommonService, ScenarioService, ListService, CampaignCommonService ) {

		var vm = this;

		$rootScope.grid_service = new GridService();

		vm.url_id = $routeParams.id;

		if ( $location.absUrl().indexOf( 'page=jackmail_list' ) !== -1 ) {
			$rootScope.active_item_menu( 'lists' );
			vm.page_type = 'list';
			var url_create = 'jackmail_create_campaign';
			var url_get_list = 'jackmail_get_list';
			var url_export_list = 'jackmail_export_list';
			vm.campaign_type = 'list';
			vm.show_import_lists = false;
			var show_name_popup = vm.url_id === '0';
			vm.import_choices = [ $rootScope.translations.by_copy_pasting ];
		}
		else {
			$rootScope.active_item_menu( 'campaigns' );
			vm.page_type = 'campaign';
			if ( $location.absUrl().indexOf( 'page=jackmail_campaign' ) !== -1 ) {
				vm.show_import_lists = false;
				var steps = [ 'contacts', 'create', 'checklist' ];
				var url_get_data = 'jackmail_get_campaign';
				var url_create = 'jackmail_create_campaign';
				var url_update = 'jackmail_update_campaign';
				var url_get_list = 'jackmail_get_campaign_contacts';
				var url_export_list = 'jackmail_export_campaign_contacts';
				var url_lists_available = 'jackmail_get_campaign_lists_available';
				var url_last_step_checker = 'jackmail_campaign_last_step_checker';
				var url_send_test = 'jackmail_send_campaign_test';
				vm.campaign_type = 'campaign';
				vm.import_choices = [ $rootScope.translations.from_a_list, $rootScope.translations.by_copy_pasting ];
			}
			else {
				if ( !$rootScope.settings.emailbuilder_installed ) {
					$rootScope.go_page( 'campaigns' );
				}
				vm.show_import_lists = true;
				var steps = [ 'settings', 'contacts', 'create', 'checklist' ];
				var url_get_data = 'jackmail_get_scenario';
				var url_create = 'jackmail_create_scenario';
				var url_update = 'jackmail_update_scenario';
				var url_lists_available = 'jackmail_get_scenario_lists_available';
				var url_post_types = 'jackmail_get_post_types';
				var url_categories = 'jackmail_get_post_categories';
				if ( $routeParams.choice === 'woocommerce_automated_newsletter' ) {
					var url_categories = 'jackmail_get_woocommerce_product_categories';
				}
				var url_last_step_checker = 'jackmail_scenario_last_step_checker';
				var url_send_test = 'jackmail_send_scenario_test';
				vm.campaign_type = 'scenario';
			}
			var previous_page_is_list = ( document.referrer ).indexOf( 'jackmail_list' ) !== -1;
			var show_name_popup = ( vm.url_id === '0' && $routeParams.step === steps[ 0 ] && !previous_page_is_list ) ? true : false;

			$timeout( function() {
				var border_width = 60;
				var step1_position = angular.element( '.jackmail_footer_middle > div > span:nth-child(1)' ).position().left;
				var step1_width = angular.element( '.jackmail_footer_middle > div > span:nth-child(1)' ).width();
				var step2_position = angular.element( '.jackmail_footer_middle > div > span:nth-child(3)' ).position().left;
				var step2_width = angular.element( '.jackmail_footer_middle > div > span:nth-child(3)' ).width();
				var step3_position = angular.element( '.jackmail_footer_middle > div > span:nth-child(5)' ).position().left;
				var step3_width = angular.element( '.jackmail_footer_middle > div > span:nth-child(5)' ).width();
				var step4_position = angular.element( '.jackmail_footer_middle > div > span:nth-child(7)' ).position().left;
				var step4_width = angular.element( '.jackmail_footer_middle > div > span:nth-child(7)' ).width();
				if ( border_width > step1_width ) {
					var border1_position = step1_position - ( border_width - step1_width ) / 2;
				}
				else {
					var border1_position = step1_position + ( step1_width - border_width ) / 2;
				}
				if ( border_width > step2_width ) {
					var border2_position = step2_position - ( border_width - step2_width ) / 2;
				}
				else {
					var border2_position = step2_position + ( step2_width - border_width ) / 2;
				}
				if ( border_width > step3_width ) {
					var border3_position = step3_position - ( border_width - step3_width ) / 2;
				}
				else {
					var border3_position = step3_position + ( step3_width - border_width ) / 2;
				}
				if ( border_width > step4_width ) {
					var border4_position = step4_position - ( border_width - step4_width ) / 2;
				}
				else {
					var border4_position = step4_position + ( step4_width - border_width ) / 2;
				}
				var css_begin = '.jackmail .jackmail_footer_middle .jackmail_footer_item';
				angular.element( 'body' ).append(
					'<style>' +
					css_begin + ':nth-child(1):hover ~ span.jackmail_footer_active_border {left: ' + border1_position + 'px !important;}' +
					css_begin + '.jackmail_footer_active:nth-child(1) ~ span.jackmail_footer_active_border {left: ' + border1_position + 'px;}' +
					css_begin + ':nth-child(3):hover ~ span.jackmail_footer_active_border {left: ' + border2_position + 'px !important;}' +
					css_begin + '.jackmail_footer_active:nth-child(3) ~ span.jackmail_footer_active_border {left: ' + border2_position + 'px;}' +
					css_begin + ':nth-child(5):hover ~ span.jackmail_footer_active_border {left: ' + border3_position + 'px !important;}' +
					css_begin + '.jackmail_footer_active:nth-child(5) ~ span.jackmail_footer_active_border {left: ' + border3_position + 'px;}' +
					css_begin + ':nth-child(7):hover ~ span.jackmail_footer_active_border {left: ' + border4_position + 'px !important;}' +
					css_begin + '.jackmail_footer_active:nth-child(7) ~ span.jackmail_footer_active_border {left: ' + border4_position + 'px;}' +
					'</style>'
				);
			} );

		}

		vm.common = {

			show_grid: -1,

			list: {},

			current_contact: [],

			list_first_load: true,
			list_top_load: true,

			name_editing: false,

			manual_select_all: false,

			list_search: '',

			list_editable: -1,
			list_full_editable: -1,
			list_targeting: -1,
			columns_editable: -1,

			list_fields: [],
			list_fields_plus: [],

			export_select: [ $rootScope.translations.all, $rootScope.translations.selection ],

			show_copy_paste: false,

			copy_paste_content: '',

			show_name_popup: show_name_popup,

			hide_name_popup: function() {
				ListAndCampaignCommonService.hide_name_popup( vm );
			},

			display_copy_paste: function() {
				ListAndCampaignCommonService.display_copy_paste( vm );
			},

			hide_copy_paste: function() {
				ListAndCampaignCommonService.hide_copy_paste( vm );
			},

			confirm_copy_paste: function() {
				ListAndCampaignCommonService.confirm_copy_paste( vm, url_create );
			},

			go_back: function() {
				ListAndCampaignCommonService.go_back( vm );
			},

			update_list_fields_array: function() {
				ListAndCampaignCommonService.update_list_fields_array( vm );
			},

			get_list_data_reset: function() {
				ListAndCampaignCommonService.get_list_data_reset( vm );
			},

			get_list_data_search_reset: function( list_search ) {
				ListAndCampaignCommonService.get_list_data_search_reset( vm, list_search );
			},

			split_list_fields: function( data ) {
				return ListAndCampaignCommonService.split_list_fields( vm, data );
			},

			check_list_editable: function() {
				ListAndCampaignCommonService.check_list_editable( vm );
			},

			display_grid: function() {
				ListAndCampaignCommonService.display_grid( vm );
			},

			grid_select_or_unselect_all: function() {
				ListAndCampaignCommonService.grid_select_or_unselect_all( vm );
			},

			grid_select_or_unselect_row: function( key ) {
				ListAndCampaignCommonService.grid_select_or_unselect_row( vm, key );
			},

			add_contacts_file: function( event ) {
				ListAndCampaignCommonService.add_contacts_file( vm, url_create, event );
			},

			split_csv: function( row, field_separator ) {
				return ListAndCampaignCommonService.split_csv( vm, row, field_separator );
			},

			add_contacts: function( emails_import ) {
				ListAndCampaignCommonService.add_contacts( vm, emails_import );
			},

			add_contact_manual: function() {
				ListAndCampaignCommonService.add_contact_manual( vm );
			},

			focus_contact: function( key ) {
				ListAndCampaignCommonService.focus_contact( vm, key );
			},

			blur_contact: function( $event ) {
				ListAndCampaignCommonService.blur_contact( vm, $event );
			},

			update_contact: function( key, field_id ) {
				ListAndCampaignCommonService.update_contact( vm, key, field_id );
			},

			add_header_column: function() {
				ListAndCampaignCommonService.add_header_column( vm );
			},

			edit_header_column: function( field_id ) {
				ListAndCampaignCommonService.edit_header_column( vm, field_id );
			},

			delete_header_column: function( field_id ) {
				ListAndCampaignCommonService.delete_header_column( vm, field_id );
			},

			delete_contacts_selection_confirmation: function() {
				ListAndCampaignCommonService.delete_contacts_selection_confirmation( vm );
			},

			range_by: function( i ) {
				ListAndCampaignCommonService.range_by( vm, i );
			},

			export_all_or_export_selection: function( key ) {
				ListAndCampaignCommonService.export_all_or_export_selection( vm, key );
			},

			export_all: function() {
				ListAndCampaignCommonService.export_all( vm, url_export_list );
			},

			export_selection: function() {
				ListAndCampaignCommonService.export_selection( vm );
			},

			get_contacts_selection_and_contacts_selection_type: function() {
				return ListAndCampaignCommonService.get_contacts_selection_and_contacts_selection_type( vm );
			},

			get_list_data: function( callback ) {
				ListAndCampaignCommonService.get_list_data( vm, url_get_list, show_name_popup, callback );
			},

			get_targeting_rules: function() {
				return ListAndCampaignCommonService.get_targeting_rules( vm );
			}

		};

		vm.common_list_detail = {

			display_list_contact_detail: function( key ) {
				$rootScope.change_page_with_parameters( 'list_detail', vm.common.list.contacts[ key ].email + '/' + vm.page_type + '/' + vm.url_id );
			}

		};

		if ( vm.page_type === 'list' ) {

			$timeout( function() {
				vm.common.get_list_data();
			} );

			vm.only_list = {

				show_connectors: false,

				connectors_example: {
					'action_add': {
						'url': '',
						'parameters': ''
					},
					'action_update': {
						'url': '',
						'parameters': ''
					},
					'action_delete': {
						'url': '',
						'parameters': ''
					}
				},

				connectors_actions: [
					{ 'id': 'action_add', 'name': $rootScope.translations.add_a_recipient },
					{ 'id': 'action_update', 'name': $rootScope.translations.update_contact_details },
					{ 'id': 'action_delete', 'name': $rootScope.translations.delete_a_contact }
				],

				display_connectors_action: 'action_add',

				connectors_actived: -1,

				show_targeting_settings: false,

				targeting_rules: [],

				targeting_rule_options_email: [
					$rootScope.translations.is_equal_to,
					$rootScope.translations.is_different_from,
					$rootScope.translations.contains,
					$rootScope.translations.is_unsubscribed
				],

				targeting_rule_options: [
					$rootScope.translations.is_equal_to,
					$rootScope.translations.is_different_from,
					$rootScope.translations.contains,
					$rootScope.translations.is_empty,
					'(' + $rootScope.translations.number + ') ' + $rootScope.translations.higher_than,
					'(' + $rootScope.translations.number + ') ' + $rootScope.translations.lower_than,
					'(' + $rootScope.translations.date + ') ' + $rootScope.translations.after,
					'(' + $rootScope.translations.date + ') ' + $rootScope.translations.before
				],

				targeting_rule_and_or: [ $rootScope.translations.and, $rootScope.translations.or ],

				targeting_rule_current: '',

				nb_searched_targeting_rules: 0,

				display_hide_connectors: function() {
					ListService.display_hide_connectors( vm );
				},

				connectors_calculate_position: function() {
					ListService.connectors_calculate_position( vm );
				},

				ok_list_name_popup: function() {
					ListService.ok_list_name_popup( vm );
				},

				cancel_list_name_popup: function() {
					ListService.cancel_list_name_popup( vm );
				},

				focus_list_name: function() {
					ListService.focus_list_name( vm );
				},

				blur_list_name: function() {
					ListService.blur_list_name( vm );
				},

				create_campaign_with_list: function() {
					ListService.create_campaign_with_list( vm );
				},

				display_hide_targeting_settings: function() {
					ListService.display_hide_targeting_settings( vm );
				},

				add_targeting_rule: function() {
					ListService.add_targeting_rule( vm );
				},

				get_targeting_rule_current: function( key ) {
					ListService.get_targeting_rule_current( vm, key );
				},

				remove_targeting_rule: function( key ) {
					ListService.remove_targeting_rule( vm, key );
				},

				select_targeting_rule_and_or: function( key ) {
					ListService.select_targeting_rule_and_or( vm, key );
				},

				select_targeting_rule_column: function( key ) {
					ListService.select_targeting_rule_column( vm, key );
				},

				select_targeting_rule_option: function( key ) {
					ListService.select_targeting_rule_option( vm, key );
				},

				get_targeting_rule_option_title: function( rule_column, rule_option ) {
					return ListService.get_targeting_rule_option_title( vm, rule_column, rule_option );
				},

				get_targeting_rule_and_or_title: function( rule_column, rule_option ) {
					ListService.get_targeting_rule_and_or_title( vm, rule_column, rule_option );
				},

				get_list_data_targeting_rules_reset: function() {
					ListService.get_list_data_targeting_rules_reset( vm );
				}

			};

			vm.create_list = function() {
				vm.common.display_grid();
			};

			vm.display_import = function() {
				vm.common.display_copy_paste();
			};

		}

		if ( vm.page_type === 'campaign' ) {

			vm.saved_campaign = {};

			var url = $location.path().split( '/' );

			vm.c_common = {

				campaign: {},

				lists: [],

				current_step_name: url[ url.length - 1 ],

				error: {
					'sender': '',
					'reply_to': '',
					'recipients': '',
					'object': '',
					'content_email': '',
					'send_option_date': ''
				},

				customized_columns_used: [],

				customized_columns_unknown: [],

				campaign_data_checked: false,

				campaign_data_analysis_checked: false,

				content_email_changes: false,

				content_email_types: '',

				content_email_nb_links: '0',

				content_email_unsubscribe_link: false,

				current_content_email_type: '',

				sending_campaign: false,

				test_campaign_recipient: '',

				save_campaign_success: true,

				nb_contacts_from_lists: '0',
				nb_selected_lists: '0',

				show_reply_to: false,

				import_lists_grid_height: {},

				show_templates: false,

				display_send_test_confirmation: false,

				emoji_categories: [
					$rootScope.translations.emoticons,
					$rootScope.translations.weather,
					$rootScope.translations.sport,
					$rootScope.translations.heart,
					$rootScope.translations.office,
					$rootScope.translations.mail,
					$rootScope.translations.business,
					$rootScope.translations.gestures,
					$rootScope.translations.tools,
					$rootScope.translations.characters,
					$rootScope.translations.games,
					$rootScope.translations.music,
					$rootScope.translations.monsters,
					$rootScope.translations.astronomy,
					$rootScope.translations.hobbies,
					$rootScope.translations.health,
					$rootScope.translations.clothing,
					$rootScope.translations.hours,
					$rootScope.translations.nature,
					$rootScope.translations.food,
					$rootScope.translations.transport,
					$rootScope.translations.technology,
					$rootScope.translations.animals,
					$rootScope.translations.tourism,
					$rootScope.translations.objects,
					$rootScope.translations.symbols
				],

				emojis: [],

				emoji_categorie_selected_key: 0,

				display_emojis_dropdown: false,

				account_data: {
					'email': '',
					'firstname': '',
					'lastname': ''
				},

				send_test_status: {
					'error_email': '',
					'campaign_ok': false,
					'credits_ok': false
				},

				init_data: function() {
					CampaignCommonService.init_data( vm );
				},

				create_list_and_go: function() {
					CampaignCommonService.create_list_and_go( vm );
				},

				show_hide_emojis_dropdown: function() {
					CampaignCommonService.show_hide_emojis_dropdown( vm );
				},

				hide_emojis_dropdown: function() {
					CampaignCommonService.hide_emojis_dropdown( vm );
				},

				select_emoji_categorie: function( id ) {
					CampaignCommonService.select_emoji_categorie( vm, id );
				},

				insert_emoji: function( key ) {
					CampaignCommonService.insert_emoji( vm, key );
				},

				object_to_save: function( object ) {
					return CampaignCommonService.object_to_save( vm, object );
				},

				object_trust_html: function( object ) {
					return CampaignCommonService.object_trust_html( vm, object );
				},

				set_created_campaign_info: function( data ) {
					CampaignCommonService.set_created_campaign_info( vm, data );
				},

				close_templates: function() {
					CampaignCommonService.close_templates( vm );
				},

				import_template_in_campaign: function( template_type, id ) {
					CampaignCommonService.import_template_in_campaign( vm, template_type, id );
				},

				insert_subject_customize: function( key, title ) {
					CampaignCommonService.insert_subject_customize( vm, key, title );
				},

				calculate_import_lists_grid_height: function() {
					CampaignCommonService.calculate_import_lists_grid_height( vm );
				},

				focus_campaign_name: function() {
					CampaignCommonService.focus_campaign_name( vm );
				},

				blur_campaign_name: function() {
					CampaignCommonService.blur_campaign_name( vm );
				},

				select_list: function( key ) {
					CampaignCommonService.select_list( vm, key );
				},

				selected_lists_total: function() {
					CampaignCommonService.selected_lists_total( vm );
				},

				get_campaign_data: function( init ) {
					CampaignCommonService.get_campaign_data( vm, url_get_data, url_post_types, url_categories, init );
				},

				get_lists_available: function() {
					CampaignCommonService.get_lists_available( vm, url_lists_available );
				},

				go_step_correct_sender: function( error ) {
					CampaignCommonService.go_step_correct_sender( vm, error );
				},

				go_step_correct_reply_to: function( error ) {
					CampaignCommonService.go_step_correct_reply_to( vm, error );
				},

				go_step_correct_recipients: function( error ) {
					CampaignCommonService.go_step_correct_recipients( vm, error );
				},

				go_step_correct_object: function( error ) {
					CampaignCommonService.go_step_correct_object( vm, error );
				},

				go_step_correct_content_email: function( error ) {
					CampaignCommonService.go_step_correct_content_email( vm, error );
				},

				go_step: function( step_name ) {
					CampaignCommonService.go_step( vm, steps, step_name );
				},

				previous_step: function() {
					CampaignCommonService.previous_step( vm, steps );
				},

				next_step: function() {
					CampaignCommonService.next_step( vm, steps );
				},

				save_campaign: function( refresh_emailbuilder_changes ) {
					CampaignCommonService.save_campaign( vm, url_create, url_update, refresh_emailbuilder_changes );
				},

				display_hide_reply_to: function() {
					CampaignCommonService.display_hide_reply_to( vm );
				},

				change_current_content_email_type: function( current_content_email_type ) {
					CampaignCommonService.change_current_content_email_type( vm, current_content_email_type );
				},

				insert_email_content_editor_customize: function( key, title ) {
					CampaignCommonService.insert_email_content_editor_customize( vm, key, title );
				},

				get_selected_lists: function() {
					return CampaignCommonService.get_selected_lists( vm );
				},

				display_import_lists: function() {
					CampaignCommonService.display_import_lists( vm );
				},

				check_campaign_data_ws: function() {
					CampaignCommonService.check_campaign_data_ws( vm, url_last_step_checker );
				},

				check_campaign_data: function() {
					CampaignCommonService.check_campaign_data( vm );
				},

				check_campaign_data_input_value: function( error ) {
					return CampaignCommonService.check_campaign_data_input_value( vm, error );
				},

				activate_link_tracking: function() {
					CampaignCommonService.activate_link_tracking( vm );
				},

				deactivate_link_tracking: function() {
					CampaignCommonService.deactivate_link_tracking( vm );
				},

				refresh_content_email: function() {
					CampaignCommonService.refresh_content_email( vm );
				},

				send_test_check: function() {
					return CampaignCommonService.send_test_check( vm );
				},

				send_test_confirmation_validation: function() {
					CampaignCommonService.send_test_confirmation_validation( vm );
				},

				send_test: function() {
					CampaignCommonService.send_test( vm, url_send_test );
				},

				cancel_test: function() {
					CampaignCommonService.cancel_test( vm );
				}

			};

			$interval( function() {
				if ( vm.c_common.current_step_name !== 'checklist' ) {
					CampaignCommonService.check_save_campaign_needed( vm );
				}
			}, 300000 );

			window.onpopstate = function() {
				CampaignCommonService.on_pop_state( vm, steps );
			};

		}

		if ( vm.page_type === 'campaign' && vm.campaign_type === 'campaign' ) {

			EmailContentService.init_emailbuilder().then( function() {
				vm.c_common.init_data();
			} );

			$rootScope.$watch( 'settings.is_authenticated', function( new_value ) {
				if ( new_value === true ) {
					vm.c_common.check_campaign_data_ws();
				}
			} );

			vm.only_campaign = {

				save_choice_select_title: [
					$rootScope.translations.save_campaign,
					$rootScope.translations.save_as_template
				],

				import_selected_lists: function( step_name ) {
					CampaignService.import_selected_lists( vm, url_create, url_update, step_name );
				},

				hide_grid: function() {
					CampaignService.hide_grid( vm );
				},

				change_campaign_option: function( option ) {
					CampaignService.change_campaign_option( vm, option );
				},

				change_send_option_date: function( date1, date2 ) {
					CampaignService.change_send_option_date( vm, date1, date2 );
				},

				send_campaign_confirmation_validation: function( type_send ) {
					CampaignService.send_campaign_confirmation_validation( vm, type_send );
				},

				save_campaign_or_create_template: function( key ) {
					CampaignService.save_campaign_or_create_template( vm, key );
				},

				reset_emailbuilder_content: function() {
					CampaignService.reset_emailbuilder_content( vm );
				},

				not_enought_credits_link: function() {
					CampaignService.not_enought_credits_link( vm );
				}

			};

			vm.shared_campaign = {

				checked_campaign_data: {
					'nb_contacts_valids': '0',
					'nb_credits_before': '0',
					'nb_credits_after': '0',
					'nb_credits_checked': false,
					'subscription_type': '',
					'domain_is_valid': false,
					'analysis_checked': false,
					'analysis': [],
					'image_size_warning': false
				},

				type_choice_select_titles: [
					$rootScope.translations.emailbuilder,
					$rootScope.translations.html_code,
					$rootScope.translations.plain_text,
					$rootScope.translations.templates_gallery,
					$rootScope.translations.my_templates
				],

				display_current_content_email_type_or_template: function( key ) {
					CampaignService.display_current_content_email_type_or_template( vm, key );
				}

			};

			vm.shared_campaign.type_choice_select_titles = [
				$rootScope.translations.emailbuilder,
				$rootScope.translations.html_code,
				$rootScope.translations.plain_text,
				$rootScope.translations.templates_gallery,
				$rootScope.translations.my_templates
			];

			vm.create_list = function() {
				CampaignService.create_list( vm, url_create );
			};

			vm.display_import = function( key ) {
				CampaignService.display_import( vm, key );
			};

		}

		if ( vm.page_type === 'campaign' && vm.campaign_type === 'scenario' ) {

			vm.only_scenario = {

				scenario_choice: $routeParams.choice,

				post_type_available: [],

				post_type_selected: '',

				post_categories_available: [],

				nb_selected_post_categories: -1,

				selected_post_categories_title: '',

				activating_or_desactivating_scenario: false,

				post_type_check_unckeck: function( key ) {
					ScenarioService.post_type_check_unckeck( vm, key );
				},

				get_post_type_select: function( data ) {
					return ScenarioService.get_post_type_select( vm, data );
				},

				post_categories_check_unckeck: function( key ) {
					ScenarioService.post_categories_check_unckeck( vm, key );
				},

				get_post_categories_select: function( data ) {
					return ScenarioService.get_post_categories_select( vm, data );
				},

				get_selected_post_categories_title: function() {
					return ScenarioService.get_selected_post_categories_title( vm );
				},

				activate_scenario_confirmation_validation: function() {
					ScenarioService.activate_scenario_confirmation_validation( vm );
				},

				deactivate_scenario_confirmation_validation: function() {
					ScenarioService.deactivate_scenario_confirmation_validation( vm );
				}

			};

			vm.shared_campaign = {

				checked_campaign_data: {
					'nb_contacts_valids': '0',
					'nb_credits_checked': false,
					'image_size_warning': false
				},

				type_choice_select_titles: [
					$rootScope.translations.emailbuilder,
					$rootScope.translations.plain_text,
					$rootScope.translations.templates_gallery,
					$rootScope.translations.my_templates
				],

				display_current_content_email_type_or_template: function( key ) {
					ScenarioService.display_current_content_email_type_or_template( vm, key );
				}

			};

			if ( vm.only_scenario.scenario_choice === 'publish_a_post' ) {

				EmailContentService.init_emailbuilder( true, 1 ).then( function() {
					vm.c_common.init_data();
				} );

				vm.shared_scenario = {

					settings_hours_choice: [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23' ],

					settings_days_choice: [ '1', '2', '3', '4', '5' ],

					select_periodicity_value: function( key ) {
						ScenarioService.publish_a_post.select_periodicity_value( vm, key );
					},

					select_periodicity_type: function( key ) {
						ScenarioService.publish_a_post.select_periodicity_type( vm, key );
					},

					change_periodicity_type: function( choice ) {
						ScenarioService.publish_a_post.change_periodicity_type( vm, choice );
					}

				};

			}

			else if ( vm.only_scenario.scenario_choice === 'automated_newsletter' || vm.only_scenario.scenario_choice === 'woocommerce_automated_newsletter' ) {

				EmailContentService.init_emailbuilder( true, 5 ).then( function() {
					vm.c_common.init_data();
				} );

				vm.shared_scenario = {

					settings_days_choice: [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31' ],

					settings_months_choice: [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' ],

					settings_nb_posts_periodicity_value_choice: [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10' ],

					settings_nb_posts_content_choice: [ '1', '2', '3', '4', '5', '6', '7', '8', '9', '10' ],

					select_periodicity_value: function( key ) {
						ScenarioService.automated_newsletter.select_periodicity_value( vm, key );
					},

					select_periodicity_type: function( key ) {
						ScenarioService.automated_newsletter.select_periodicity_type( vm, key );
					},

					change_periodicity_type: function( choice ) {
						ScenarioService.automated_newsletter.change_periodicity_type( vm, choice );
					},

					change_nb_posts_periodicity_value: function( key ) {
						ScenarioService.automated_newsletter.change_nb_posts_periodicity_value( vm, key );
					},

					change_nb_posts_content: function( key ) {
						ScenarioService.automated_newsletter.change_nb_posts_content( vm, key );
					},

					change_event_date: function( date ) {
						ScenarioService.automated_newsletter.change_event_date( vm, date );
					}

				};

			}
		}

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'ListDetailController', [
	'UrlService', 'VerificationService', '$routeParams', '$timeout', '$window', '$rootScope',
	function( UrlService, VerificationService, $routeParams, $timeout, $window, $rootScope ) {

		var vm = this;

		$rootScope.active_item_menu( 'lists' );

		var email = $routeParams.email;

		vm.url_id = $routeParams.id_list;
		vm.page_type = 'list';
		if ( $routeParams.id_campaign !== undefined ) {
			vm.url_id = $routeParams.id_campaign;
			vm.page_type = 'campaign';
		}

		vm.list_contact_detail_email = '';
		vm.list_contact_detail_email_key = 0;

		vm.email_lists_detail = [];
		vm.email_lists_detail_saved = [];

		vm.email_lists_detail_current_list_name = '';
		vm.email_lists_detail_current_list_id = 0;
		vm.email_lists_detail_current_list_type = '';

		vm.show_list_contact_detail_columns = false;

		vm.nb_sends = '';
		vm.nb_opens = '';
		vm.nb_clicks = '';

		vm.synthesis_timeline = [];
		vm.nb_synthesis_timeline = 0;

		function display_list_contact_detail() {
			if ( VerificationService.email( email ) ) {
				var data_parameters = {
					'email': email
				};
				if ( vm.page_type === 'campaign' ) {
					data_parameters[ 'id_campaign' ] = vm.url_id;
				}
				UrlService.post_data( 'jackmail_get_email_lists_detail', data_parameters, function( data ) {
					var i;
					var nb_lists = data.length;
					if ( nb_lists > 0 ) {
						vm.show_list_contact_detail = true;
						if ( vm.url_id === '0' ) {
							data[ 0 ].checked = true;
							vm.email_lists_detail_current_list_name = data[ 0 ].name;
							vm.email_lists_detail_current_list_id = data[ 0 ].id;
							vm.email_lists_detail_current_list_type = data[ 0 ].type;
						} else {
							for ( i = 0; i < nb_lists; i++ ) {
								data[ i ].fields = $rootScope.split_fields( data[ i ].fields );
								if ( data[ i ].id === vm.url_id && data[ i ].type === vm.page_type ) {
									data[ i ].checked = true;
									vm.email_lists_detail_current_list_name = data[ i ].name;
									vm.email_lists_detail_current_list_id = data[ i ].id;
									vm.email_lists_detail_current_list_type = data[ i ].type;
									break;
								}
							}
						}
						get_email_detail_data();
						vm.email_lists_detail = angular.copy( data );
						vm.email_lists_detail_saved = angular.copy( data );
					}
				}, function() {

				} );
			}
		}

		display_list_contact_detail();

		function get_email_detail_data() {
			if ( vm.email_lists_detail_current_list_type === 'list' ) {
				var data_parameters = {
					'id': vm.email_lists_detail_current_list_id,
					'email': email
				};
				UrlService.post_data( 'jackmail_get_email_detail', data_parameters, function( data ) {
					vm.nb_sends = data.sends;
					vm.nb_opens = data.opens;
					vm.nb_clicks = data.clicks;
					vm.synthesis_timeline = $rootScope.generate_timeline_data( data.timeline, true, true, true );
					vm.nb_synthesis_timeline = vm.synthesis_timeline.length;
				}, function() {

				} );
			}
			else {
				vm.synthesis_timeline = [];
				vm.nb_synthesis_timeline = 0;
			}
		}

		vm.blur_contact = function( $event ) {
			if ( $event.keyCode === 13 ) {
				angular.element( $event.target ).blur();
			}
		};

		vm.update_detail_contact = function( key, subkey ) {
			var email = vm.email_lists_detail[ key ].contact.email;
			var field_id = subkey.substr( 5 );
			var field = vm.email_lists_detail[ key ].contact[ subkey ];
			var id = vm.email_lists_detail_current_list_id;
			var type = vm.email_lists_detail_current_list_type;
			var field_saved = vm.email_lists_detail_saved[ key ].contact[ subkey ];
			if ( field !== field_saved ) {
				var data_parameters = {
					'email': email,
					'field_id': field_id,
					'field': field
				};
				if ( type === 'list' ) {
					data_parameters[ 'id_list' ] = id;
				}
				else {
					data_parameters[ 'id_campaign' ] = id;
				}
				var url = 'jackmail_update_contact_field';
				UrlService.post_data( url, data_parameters, function( data ) {
					$rootScope.display_success_error( data.success, $rootScope.translations.contact_saved, '' );
					if ( data.success ) {
						vm.email_lists_detail_saved = angular.copy( vm.email_lists_detail );
					}
				}, function() {

				} );
			}
		};

		vm.unsubscribe_contact = function( key ) {
			var url = 'jackmail_unsubscribe_contact';
			vm.unsubscribe_or_unblacklist_contact( key, url );
		};

		vm.unblacklist_contact = function( key ) {
			var url = 'jackmail_unblacklist_contact';
			vm.unsubscribe_or_unblacklist_contact( key, url );
		};

		vm.unsubscribe_or_unblacklist_contact = function( key, url ) {
			var email = vm.email_lists_detail[ key ].contact.email;
			var id = vm.email_lists_detail_current_list_id;
			var type = vm.email_lists_detail_current_list_type;
			var data_parameters = {
				'email': email
			};
			if ( type === 'list' ) {
				data_parameters[ 'id_list' ] = id;
			}
			else {
				data_parameters[ 'id_campaign' ] = id;
			}
			UrlService.post_data( url, data_parameters, function( data ) {
				$rootScope.display_success_error( data.success, $rootScope.translations.contact_saved, '' );
				if ( data.success ) {
					var blacklist = '0';
					if ( url === 'jackmail_unsubscribe_contact' ) {
						blacklist = '1';
					}
					vm.email_lists_detail[ key ].contact.blacklist = blacklist;
					vm.email_lists_detail_saved = angular.copy( vm.email_lists_detail );
				}
			}, function() {

			} );
		};

		vm.display_hide_list_contact_detail_columns = function() {
			vm.show_list_contact_detail_columns = !vm.show_list_contact_detail_columns;
		};

		vm.select_email_list_details = function( key ) {
			var i;
			var nb_lists = vm.email_lists_detail.length;
			for ( i = 0; i < nb_lists; i++ ) {
				vm.email_lists_detail[ i ].checked = false;
			}
			vm.email_lists_detail[ key ].checked = !vm.email_lists_detail[ key ].checked;
			vm.email_lists_detail_current_list_name = vm.email_lists_detail[ key ].name;
			vm.email_lists_detail_current_list_id = vm.email_lists_detail[ key ].id;
			vm.email_lists_detail_current_list_type = vm.email_lists_detail[ key ].type;
			get_email_detail_data();
		};

		vm.go_back = function() {
			$window.history.back();
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'ListsController', [
	'$rootScope', 'UrlService', '$timeout', 'ExportService', 'GridService', 'PluginService',
	function( $rootScope, UrlService, $timeout, ExportService, GridService, PluginService ) {

		var vm = this;

		$rootScope.grid_service = new GridService();

		vm.columns = [
			{ 'name': $rootScope.translations.list_name, 'field': 'name' },
			{ 'name': $rootScope.translations.Recipients, 'field': 'nb_contacts' },
			{ 'name': $rootScope.translations.statistics, 'field': '' },
			{ 'name': $rootScope.translations.actions, 'field': '' }
		];

		vm.lists = [];
		vm.nb_lists = -1;
		vm.nb_deletable_lists = -1;

		vm.show_new_plugins_confirmation = false;
		var nb_new_plugins = 0;
		vm.new_plugins = PluginService.get_compatible_plugins();

		UrlService.post_data( 'jackmail_get_new_plugins', {}, function( data ) {
			vm.new_plugins = PluginService.get_plugins_to_display( vm.new_plugins, data );
			nb_new_plugins = data.length;
			if ( nb_new_plugins > 0 ) {
				vm.show_new_plugins_confirmation = true;
			}

		}, function() {

		} );

		function get_lists( search ) {
			var data_parameters = {
				'search': search
			};
			UrlService.post_data( 'jackmail_get_lists', data_parameters, function( data ) {
				vm.lists = data;
				vm.nb_lists = data.length;
				var i;
				var nb_lists = data.length;
				var nb_deletable_lists = 0;
				for ( i = 0; i < nb_lists; i++ ) {
					if ( data[ i ].type === '' ) {
						nb_deletable_lists++;
					}
				}
				vm.nb_deletable_lists = nb_deletable_lists;
				$timeout( function() {
					$rootScope.grid_service.refresh_grid_max_height( 'refresh' );
				} );
			}, function() {

			} );
		}

		get_lists( '' );

		vm.get_lists_search = function( search ) {
			get_lists( search );
		};

		vm.delete_list_confirmation_validation = function( key, id, in_widget, in_scenario ) {
			var message = $rootScope.translations.delete_list;
			if ( in_widget ) {
				message += '<br/>' + $rootScope.translations.widget_which_use_this_list_will_be_deactived;
			}
			if ( in_scenario ) {
				message += '<br/>' + $rootScope.translations.workflows_which_use_only_this_list_will_be_deactived;
			}
			var data_parameters = {
				'id_lists': $rootScope.join( [ id ] )
			};
			UrlService.post_data( 'jackmail_delete_lists', data_parameters, function( data ) {
				if ( data.success ) {
					if ( vm.lists[ key ].selected ) {
						$rootScope.grid_service.set_nb_selected( $rootScope.grid_service.nb_selected - 1 );
					}
					vm.lists.splice( key, 1 );
					vm.nb_lists = vm.lists.length;
					vm.nb_deletable_lists--;
				}
				$rootScope.display_success_error( data.success, $rootScope.translations.the_list_was_deleted, '' );
			}, function() {

			} );
		};

		vm.delete_selection_confirmation = function() {
			var i;
			var nb_lists = vm.lists.length;
			var delete_lists = [];
			for ( i = 0; i < nb_lists; i++ ) {
				if ( vm.lists[ i ].selected ) {
					delete_lists.push( vm.lists[ i ].id );
				}
			}
			if ( delete_lists.length > 0 ) {
				var data_parameters = {
					'id_lists': $rootScope.join( delete_lists )
				};
				UrlService.post_data( 'jackmail_delete_lists', data_parameters, function( data ) {
					if ( data.success ) {
						var nb_deletable_lists = vm.nb_deletable_lists;
						for ( i = nb_lists - 1; i >= 0; i-- ) {
							if ( vm.lists[ i ].selected ) {
								vm.lists.splice( i, 1 );
								nb_deletable_lists--;
							}
						}
						vm.nb_lists = vm.lists.length;
						vm.nb_deletable_lists = nb_deletable_lists;
						$rootScope.grid_service.set_nb_selected( 0 );
						$rootScope.display_success_error( data.success, $rootScope.translations.selected_lists_have_been_deleted, '' );
					}
				}, function() {

				} );
			}
		};

		vm.export_all = function( id, nb_contacts ) {
			if ( nb_contacts > 0 ) {
				var part = 3000;
				var nb_parts = Math.ceil( nb_contacts / part );
				var begin = 0;
				var i;
				var data_parameters = [];
				for ( i = 0; i < nb_parts; i++ ) {
					data_parameters.push( {
						'id': id,
						'begin': begin.toString(),
						'sort_by': '',
						'sort_order': '',
						'search': '',
						'targeting_rules': ''
					} );
					begin = begin + part;
				}
				UrlService.post_multiple_data( 'jackmail_export_list', data_parameters, $rootScope.translations.downloading, function( data ) {
					ExportService.export_contact_file_multiple_data( data );
				}, function() {

				} );
			}
			else {
				$rootScope.display_error( $rootScope.translations.the_list_is_empty );
			}
		};

		vm.grid_select_or_unselect_row = function( key ) {
			vm.lists = $rootScope.grid_service.grid_select_or_unselect_row( vm.lists, key );
		};

		vm.grid_select_or_unselect_all = function() {
			vm.lists = $rootScope.grid_service.grid_select_or_unselect_all_with_field_restriction( vm.lists, 'type' );
		};

		vm.range_by = function( i ) {
			vm.lists = $rootScope.grid_service.grid_range( vm.lists, i );
		};

		vm.create_list = function() {
			$rootScope.change_page_with_parameters( 'list', '0' );
		};

		vm.edit_list_name = function( name, id ) {
			var data_parameters = {
				'name': name,
				'id': id
			};
			UrlService.post_data( 'jackmail_save_name', data_parameters, function( data ) {
				if ( !data.success ) {
					get_lists( '' );
				}
				$rootScope.display_success_error( data.success, $rootScope.translations.the_list_was_saved, $rootScope.translations.list_name_must_be_unique );
			}, function() {

			} );
		};

		vm.select_or_unselect_new_plugin = function( plugin ) {
			vm.new_plugins[ plugin ].selected = !vm.new_plugins[ plugin ].selected;
		};

		vm.cancel_new_plugins_confirmation = function() {
			vm.show_new_plugins_confirmation = false;
		};

		vm.select_new_plugin = function( plugin ) {
			vm.new_plugins[ plugin ].selected = true;
			vm.new_plugins[ plugin ].hide = true;
			vm.save_new_plugin_confirmation();
		};

		vm.unselect_new_plugin = function( plugin ) {
			vm.new_plugins[ plugin ].selected = false;
			vm.new_plugins[ plugin ].hide = true;
			vm.save_new_plugin_confirmation();
		};

		vm.save_new_plugin_confirmation = function() {
			nb_new_plugins--;
			if ( nb_new_plugins === 0 ) {
				angular.element( '.jackmail_notice.jackmail_notice_plugins' ).parent().parent().hide();
				vm.show_new_plugins_confirmation = false;
				var plugins = PluginService.get_active_plugins( vm.new_plugins );
				var data_parameters = {
					'plugins': $rootScope.join( plugins )
				};
				UrlService.post_data( 'jackmail_save_new_plugins', data_parameters, function() {
					if ( plugins.length > 0 ) {
						get_lists( '' );
					}
				}, function() {

				} );
			}
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'MainController', [
	'$rootScope', '$location', '$timeout', '$sce', '$window', '$document', '$interval', 'EmailContentService', 'UrlService',
	function( $rootScope, $location, $timeout, $sce, $window, $document, $interval, EmailContentService, UrlService ) {

		$rootScope.translations = jackmail_translations_object;

		$rootScope.settings = jackmail_ajax_object;
		$rootScope.settings.jackmail_file_path_name = str( $rootScope.settings.jackmail_file_path_name );
		$rootScope.settings.key = str( $rootScope.settings.key );
		$rootScope.settings.statistics_campaigns_selection = $rootScope.settings.statistics_campaigns_selection.split( '|' );

		var url = $location.absUrl();

		if ( $rootScope.settings.analytics ) {
			$window.ga( 'create', $rootScope.settings.analytics_account, 'auto' );
		}

		$rootScope.jackmail_success = false;
		$rootScope.jackmail_error = false;
		$rootScope.jackmail_message = '';
		$rootScope.loading = false;
		$rootScope.nb_loading = 0;
		

		$rootScope.validation_popup = false;
		$rootScope.validation_popup_message = '';
		$rootScope.validation_popup_callback = function() {
		};

		$rootScope.show_help1 = false;
		$rootScope.show_help2 = false;

		$rootScope.show_emailbuilder = false;

		$rootScope.show_html_preview_popup = false;
		$rootScope.html_preview_content_popup = '';

		$rootScope.show_account_connection_popup = false;

		if ( url.indexOf( 'jackmail_installation' ) !== -1 ) {
			$rootScope.show_account_connection_popup_form = 'create';
		}
		else {
			$rootScope.show_account_connection_popup_form = 'connection';
		}

		$rootScope.show_emailbuilder_popup = false;
		$rootScope.emailbuilder_popup_licence = '';

		$timeout( function() {
			$rootScope.content_loaded = true;
		} );

		var begin = url.indexOf( 'page=jackmail_' ) + 14;
		if ( url.indexOf( '#/' ) === -1 ) {
			$location.path( url.substr( begin, url.length - begin ) );
		}

		$rootScope.$on( '$routeChangeStart', function() {
			if ( $rootScope.settings.analytics ) {
				$window.ga( 'send', { 'hitType': 'pageview', 'appName': 'Jackmail', 'appVersion': $rootScope.settings.version, 'screenName': $location.url() } );
			}
			
		} );
		
		$rootScope.split = function( data ) {
			if ( data !== undefined && data !== '' ) {
				return JSON.parse( data );
			}
			else {
				return [];
			}
		};

		$rootScope.join = function( data ) {
			return JSON.stringify( data );
		};

		$rootScope.split_fields = function( data ) {
			if ( data !== undefined && data !== '' ) {
				data = data.toUpperCase();
				return JSON.parse( data );
			}
			else {
				return [];
			}
		};

		$rootScope.join_fields = function( data ) {
			return JSON.stringify( data );
		};

		$rootScope.refresh_header_footer_position = function() {
			var document_width = parseInt( angular.element( $document ).width() );
			var window_width = parseInt( angular.element( $window ).width() );
			var scroll_left = parseInt( angular.element( $window ).scrollLeft() );
			
			var menu_width = 0;
			if ( angular.element( '#adminmenuback' ).css( 'display' ) === 'block' ) {
				menu_width = parseInt( angular.element( '#adminmenuback' ).width() );
			}
			var width = 'auto';
			var width_notices = 'auto';
			if ( window_width < document_width ) {
				width = ( document_width - menu_width ) + 'px';
				width_notices = ( document_width - menu_width - 80 ) + 'px';
			}
			var left = menu_width - scroll_left;
			angular.element(
				'.jackmail .jackmail_campaigns_action_container > div,' +
				'.jackmail .jackmail_message.jackmail_success > div,' +
				'.jackmail .jackmail_message.jackmail_error > div,' +
				'.jackmail .jackmail_header > div,' +
				'.jackmail .jackmail_footer > div' ).css( {
				'margin-left': left + 'px',
				'width': width
			} );
			angular.element( '.jackmail_notice_subcontainer > div' ).css( {
				'margin-left': ( left + 15 ) + 'px',
				'width': width_notices
			} );
			angular.element( '#adminmenuback' ).css( { 'left': ( -scroll_left ) + 'px' } );
		};

		function refresh_grid_max_height( type ) {
			if ( $rootScope.grid_service ) {
				if ( $rootScope.grid_service.length ) {
					var i;
					var nb_grids = $rootScope.grid_service.length;
					for ( i = 0; i < nb_grids; i++ ) {
						$rootScope.grid_service[ i ].refresh_grid_max_height( type );
					}
				}
				else {
					$rootScope.grid_service.refresh_grid_max_height( type );
				}
			}
		}

		$window.onbeforeunload = function() {
			$timeout( function() {
				$rootScope.loading = true;
			} );
		};

		angular.element( $window ).on( 'scroll', function() {
			$rootScope.refresh_header_footer_position();
			
			refresh_grid_max_height( 'scroll' );
			EmailContentService.refresh_email_content_height();
		} );

		angular.element( $window ).on( 'resize', function() {
			$timeout( function() {
				$rootScope.refresh_header_footer_position();
			} );
			refresh_grid_max_height( 'resize' );
			EmailContentService.refresh_email_content_height();
		} );

		angular.element( '#collapse-menu' ).click( function() {
			$timeout( function() {
				$rootScope.refresh_header_footer_position();
				refresh_grid_max_height( 'resize' );
			} );
		} );

		angular.element( '.notice' ).click( function() {
			$timeout( function() {
				$rootScope.refresh_header_footer_position();
			}, 200 );
		} );

		$rootScope.$on( '$viewContentLoaded', function() {
			$timeout( function() {
				$rootScope.refresh_header_footer_position();
			} );
		} );

		$rootScope.go_page = function( page ) {
			$timeout( function() {
				$rootScope.loading = true;
			} );
			window.location.href = 'admin.php?page=jackmail_' + page;
		};

		$rootScope.change_page = function( page ) {
			$timeout( function() {
				$rootScope.loading = true;
			} );
			if ( $location.path() !== '/' + page ) {
				window.location.href = 'admin.php?page=jackmail_' + page + '#/' + page;
			}
			else {
				window.location.reload();
			}
		};

		$rootScope.change_page_with_parameters = function( page, parameters ) {
			$timeout( function() {
				$rootScope.loading = true;
			} );
			var page_parameters = '/' + page + '/' + parameters;
			if ( $location.path() !== page_parameters ) {
				window.location.href = 'admin.php?page=jackmail_' + page + '#' + page_parameters;
			}
			else {
				window.location.reload();
			}
		};

		var progress_success_error;
		
		$rootScope.display_success_error = function( status, message_ok, messsage_error ) {
			$timeout.cancel( progress_success_error );
			if ( status ) {
				$rootScope.jackmail_success = true;
				$rootScope.jackmail_error = false;
				$rootScope.jackmail_message = message_ok;
				progress_success_error = $timeout( function() {
					$rootScope.jackmail_success = false;
					$rootScope.jackmail_message = '';
				}, 2500 );
			}
			else {
				$rootScope.jackmail_error = true;
				$rootScope.jackmail_success = false;
				if ( messsage_error ) {
					$rootScope.jackmail_message = messsage_error;
				}
				else {
					$rootScope.jackmail_message = $rootScope.translations.an_error_occurred;
				}
				progress_success_error = $timeout( function() {
					$rootScope.jackmail_error = false;
					$rootScope.jackmail_message = '';
				}, 5000 );
			}
		};

		$rootScope.display_success = function( message_ok ) {
			$rootScope.display_success_error( true, message_ok, '' );
		};

		$rootScope.display_error = function( message_error ) {
			$rootScope.display_success_error( false, '', message_error );
		};

		$rootScope.display_success_error_writable = function( status, message_ok ) {
			var message_error = $rootScope.translations.an_error_occurred;
			if ( !$rootScope.settings.jackmail_file_path_writable ) {
				message_error += ' - ' + $rootScope.translations.the_file + ' "' + $rootScope.settings.jackmail_file_path_name + '" ' + $rootScope.translations.should_be_editable;
			}
			$rootScope.display_success_error( status, message_ok, message_error );
		};

		$rootScope.display_error_writable = function() {
			$rootScope.display_success_error_writable( false, '' );
		};

		$rootScope.hide_success_error = function() {
			$rootScope.jackmail_success = false;
			$rootScope.jackmail_error = false;
		};

		$rootScope.display_validation = function( message, callback ) {
			$rootScope.validation_popup = true;
			$rootScope.validation_popup_message = $sce.trustAsHtml( message );
			$rootScope.validation_popup_callback = callback;
		};

		$rootScope.hide_validation = function() {
			$rootScope.validation_popup = false;
		};

		$rootScope.cancel_validation = function() {
			$rootScope.validation_popup = false;
		};

		$rootScope.ok_validation = function() {
			$rootScope.validation_popup_callback();
			$rootScope.validation_popup = false;
		};

		$rootScope.display_emailbuilder_popup = function() {
			UrlService.post_data( 'jackmail_get_emailbuilder_licence', {}, function( data ) {
				if ( data.licence ) {
					if ( data.licence.length > 2000 && data.licence.indexOf( 'EmailBuilder' ) !== -1 ) {
						$rootScope.emailbuilder_popup_licence = $sce.trustAsHtml( data.licence );
						$rootScope.show_emailbuilder_popup = true;
					}
					else {
						$rootScope.display_error( $rootScope.translations.can_t_create_new_template_error_while_loading_emailbuilder_licence );
					}
				}
				else {
					$rootScope.display_error( $rootScope.translations.can_t_create_new_template_error_while_loading_emailbuilder_licence );
				}
			}, function() {

			} );
		};

		$rootScope.cancel_emailbuilder_popup = function() {
			$rootScope.show_emailbuilder_popup = false;
		};

		$rootScope.ok_emailbuilder_popup = function() {
			UrlService.post_data( 'jackmail_install_emailbuilder', {}, function( data ) {
				if ( data.success ) {
					
					$rootScope.display_success( $rootScope.translations.emailbuilder_is_now_installed );
					window.location.reload();
				}
			}, function() {

			} );
			$rootScope.show_emailbuilder_popup = false;
		};

		$rootScope.hide_update_available_popup = function() {
			$rootScope.settings.update_available = false;
			UrlService.post_data( 'jackmail_hide_update_popup', {}, function( data ) {

			}, function() {

			} );
		};

		$rootScope.scroll_top = function() {
			angular.element( 'html' ).scrollTop( 0 );
		};

		$rootScope.scroll_bottom = function() {
			$timeout( function() {
				angular.element( 'html' ).scrollTop( angular.element( 'html' ).height() );
			} );
		};

		$rootScope.active_item_menu = function( item ) {
			var i = 2;
			if ( item === 'lists' ) {
				i = 6;
			} else if ( item === 'templates' ) {
				i = 9;
			}
			angular.element( '#toplevel_page_jackmail_campaigns > ul > li:nth-child( ' + i + ' ), #toplevel_page_jackmail_campaigns > ul > li:nth-child( ' + i + ' ) > a' ).addClass( 'current' );
		};

		$rootScope.months = [
			$rootScope.translations.jan,
			$rootScope.translations.feb,
			$rootScope.translations.mar,
			$rootScope.translations.apr,
			$rootScope.translations.may,
			$rootScope.translations.june,
			$rootScope.translations.july,
			$rootScope.translations.aug,
			$rootScope.translations.sept,
			$rootScope.translations.oct,
			$rootScope.translations.nov,
			$rootScope.translations.dec
		];

		$rootScope.date_from_str = function( date ) {
			var a = date.split( ' ' );
			var d = a [ 0 ].split( '-' );
			var t = a [ 1 ].split( ':' );
			return new Date( d [ 0 ], ( d [ 1 ] - 1 ), d [ 2 ], t [ 0 ], t [ 1 ], t [ 2 ] );
		};

		$rootScope.add_date_interval = function( date, interval ) {
			date = $rootScope.date_from_str( date );
			date.setSeconds( date.getSeconds() + interval );
			var selected_day = date.getDate();
			if ( selected_day < 10 ) {
				selected_day = '0' + selected_day;
			}
			var selected_month = date.getMonth() + 1;
			if ( selected_month < 10 ) {
				selected_month = '0' + selected_month;
			}
			var selected_year = date.getFullYear();
			var selected_hour = date.getHours();
			if ( selected_hour < 10 ) {
				selected_hour = '0' + selected_hour;
			}
			var selected_minute = date.getMinutes();
			if ( selected_minute < 10 ) {
				selected_minute = '0' + selected_minute;
			}
			var selected_second = date.getSeconds();
			if ( selected_second < 10 ) {
				selected_second = '0' + selected_second;
			}
			return selected_year + '-' + selected_month + '-' + selected_day + ' ' + selected_hour + ':' + selected_minute + ':' + selected_second;
		};

		$rootScope.generate_grid = function( datas ) {
			var container_width = angular.element( '.jackmail' ).width();
			var column_width = 240;
			var column_width_padding = column_width + 20;
			var nb_campaigns = datas.length;
			var nb_columns = parseInt( container_width / column_width_padding );
			var nb_datas_columns = ( nb_campaigns / nb_columns );
			if ( nb_columns > nb_campaigns ) {
				nb_columns = nb_campaigns;
			}
			var i;
			var j;
			var campaigns_grid = [];
			for ( i = 0; i < nb_columns; i++ ) {
				var column_datas = [];
				for ( j = 0; j < nb_datas_columns; j++ ) {
					if ( datas[ i + j * nb_columns ] ) {
						column_datas.push( datas[ i + j * nb_columns ] );
					}
				}
				campaigns_grid.push( column_datas );
			}
			var grid_width = column_width_padding * campaigns_grid.length;
			if ( grid_width !== 0 ) {
				angular.element( '.jackmail_previews_grid_container > div' ).css( { 'width': grid_width + 'px' } );
			}
			return campaigns_grid;
		};

		$rootScope.display_html_preview_popup = function() {
			var html_preview = EmailContentService.get_email_content_editor( 'html' );
			html_preview = html_preview.replace( /..\/\?jackmail_image\=/g, $rootScope.settings.website_url + '?jackmail_image=' );
			
			$rootScope.html_preview_content_popup = $sce.trustAsResourceUrl( 'data:text/html;charset=utf-8,' + encodeURIComponent( html_preview ) );
			$rootScope.show_html_preview_popup = true;
		};

		$rootScope.hide_html_preview_popup = function() {
			$rootScope.show_html_preview_popup = false;
			$rootScope.html_preview_content_popup = '';
		};

		$rootScope.display_account_connection_popup = function( form ) {
			$rootScope.show_account_connection_popup = true;
			$rootScope.show_account_connection_popup_form = form;
		};

		$rootScope.hide_account_connection_popup = function() {
			angular.element( 'div[ng-show="$root.show_account_connection_popup"]' ).toggleClass( 'ng-hide' );
			$rootScope.show_account_connection_popup = false;
		};

		$rootScope.select_name_popup = function( select ) {
			if ( select ) {
				$timeout( function() {
					angular.element( '.jackmail .jackmail_name_popup' ).select();
				} );
			}
		};

		$rootScope.replace_all = function( str, find, replace ) {
			find = find.replace( /([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1" );
			return str.replace( new RegExp( find, 'g' ), replace );
		};

		function str( value ) {
			value = value.substr( 5 );
			value = value.substr( 0, value.length - 5 );
			var value_length = value.length;
			var new_value = '';
			var i;
			for ( i = 0; i < value_length; i++ ) {
				if ( i % 2 === 0 ) {
					new_value += value[ i ];
				}
			}
			return angular.element( '<div/>' ).html( new_value ).text();
		}

		$rootScope.html_to_text = function( value ) {
			return angular.element( '<div/>' ).html( value ).text();
		};

		$rootScope.text_to_html = function( value ) {
			return value
				.replace( /&/g, "&amp;" ).replace( /</g, "&lt;" ).replace( />/g, "&gt;" ).replace( /¢/g, "&cent;" ).replace( /£/g, "&pound;" ).replace( /€/g, "&euro;" )
				.replace( /¥/g, "&yen;" ).replace( /°/g, "&deg;" ).replace( /¼/g, "&frac14;" ).replace( /Œ/g, "&OElig;" ).replace( /½/g, "&frac12;" ).replace( /œ/g, "&oelig;" )
				.replace( /¾/g, "&frac34;" ).replace( /Ÿ/g, "&Yuml;" ).replace( /¡/g, "&iexcl;" ).replace( /«/g, "&laquo;" ).replace( /»/g, "&raquo;" ).replace( /¿/g, "&iquest;" )
				.replace( /À/g, "&Agrave;" ).replace( /Á/g, "&Aacute;" ).replace( /Â/g, "&Acirc;" ).replace( /Ã/g, "&Atilde;" ).replace( /Ä/g, "&Auml;" ).replace( /Å/g, "&Aring;" )
				.replace( /Æ/g, "&AElig;" ).replace( /Ç/g, "&Ccedil;" ).replace( /È/g, "&Egrave;" ).replace( /É/g, "&Eacute;" ).replace( /Ê/g, "&Ecirc;" ).replace( /Ë/g, "&Euml;" )
				.replace( /Ì/g, "&Igrave;" ).replace( /Í/g, "&Iacute;" ).replace( /Î/g, "&Icirc;" ).replace( /Ï/g, "&Iuml;" ).replace( /Ð/g, "&ETH;" ).replace( /Ñ/g, "&Ntilde;" )
				.replace( /Ò/g, "&Ograve;" ).replace( /Ó/g, "&Oacute;" ).replace( /Ô/g, "&Ocirc;" ).replace( /Õ/g, "&Otilde;" ).replace( /Ö/g, "&Ouml;" ).replace( /Ø/g, "&Oslash;" )
				.replace( /Ù/g, "&Ugrave;" ).replace( /Ú/g, "&Uacute;" ).replace( /Û/g, "&Ucirc;" ).replace( /Ü/g, "&Uuml;" ).replace( /Ý/g, "&Yacute;" ).replace( /Þ/g, "&THORN;" )
				.replace( /ß/g, "&szlig;" ).replace( /à/g, "&agrave;" ).replace( /á/g, "&aacute;" ).replace( /â/g, "&acirc;" ).replace( /ã/g, "&atilde;" ).replace( /ä/g, "&auml;" )
				.replace( /å/g, "&aring;" ).replace( /æ/g, "&aelig;" ).replace( /ç/g, "&ccedil;" ).replace( /è/g, "&egrave;" ).replace( /é/g, "&eacute;" ).replace( /ê/g, "&ecirc;" )
				.replace( /ë/g, "&euml;" ).replace( /ì/g, "&igrave;" ).replace( /í/g, "&iacute;" ).replace( /î/g, "&icirc;" ).replace( /ï/g, "&iuml;" ).replace( /ð/g, "&eth;" )
				.replace( /ñ/g, "&ntilde;" ).replace( /ò/g, "&ograve;" ).replace( /ó/g, "&oacute;" ).replace( /ô/g, "&ocirc;" ).replace( /õ/g, "&otilde;" ).replace( /ö/g, "&ouml;" )
				.replace( /ø/g, "&oslash;" ).replace( /ù/g, "&ugrave;" ).replace( /ú/g, "&uacute;" ).replace( /û/g, "&ucirc;" ).replace( /ü/g, "&uuml;" ).replace( /ý/g, "&yacute;" )
				.replace( /þ/g, "&thorn;" ).replace( /"/g, "&quot;" ).replace( /'/g, "&#039;" );
		};

		$rootScope.generate_timeline_data = function( data, opens, clicks, unsubscribes ) {
			var new_data = [];
			if ( data.length !== 0 ) {
				var first_date = '';
				var last_date = '';
				var i;
				var nb_data = data.length;
				for ( i = 0; i < nb_data; i++ ) {
					if ( ( data[ i ].event === 'open' && opens ) || ( data[ i ].event === 'click' && clicks ) || ( data[ i ].event === 'unsubscribe' && unsubscribes ) ) {
						if ( first_date === '' ) {
							first_date = data[ i ].date;
						}
						last_date = data[ i ].date;
						new_data.push( data[ i ] );
					}
				}
				var position = 0;
				if ( first_date !== '' && last_date !== '' ) {
					first_date = $rootScope.date_from_str( first_date );
					last_date = $rootScope.date_from_str( last_date );
					first_date = first_date.getTime();
					last_date = last_date.getTime();
					if ( new_data.length >= 2 ) {
						position = parseInt( ( last_date - first_date ) / 100 );
					}
					var nb_new_data = new_data.length;
					var current_date = '';
					for ( i = 0; i < nb_new_data; i++ ) {
						if ( position !== 0 ) {
							current_date = $rootScope.date_from_str( new_data[ i ].date );
							current_date = current_date.getTime();
							new_data[ i ].position = ( ( ( last_date - current_date ) / position ) ).toFixed( 2 );
						}
						else {
							new_data[ i ].position = 0;
						}
					}
				}
			}
			return new_data;
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'ScenarioController', [
	'$rootScope', 'UrlService',
	function( $rootScope, UrlService ) {

		var vm = this;

		$rootScope.active_item_menu( 'campaigns' );

		vm.page_width = '850';
		vm.jackmail_display = true;
		vm.contactform7_display = true;
		vm.woocommerce_display = true;

		vm.contactform7_active = false;
		vm.woocommerce_active = false;

		vm.jackmail_events = [
			{
				'id': 'publish_a_post',
				'title': $rootScope.translations.publish_a_post,
				'description': $rootScope.translations.publish_a_post_description
			},
			{
				'id': 'automated_newsletter',
				'title': $rootScope.translations.automated_newsletter,
				'description': $rootScope.translations.automated_newsletter_description
			}
		];


		vm.woocommerce_events = [];
		UrlService.post_data( 'jackmail_get_plugins', {}, function( data ) {
			var i;
			var nb_plugins = data.length;
			for ( i = 0; i < nb_plugins; i++ ) {
				if ( data[ i ].plugin === 'woocommerce' && data[ i ].selected ) {					vm.woocommerce_active = true;
					vm.woocommerce_events = [
						{
							'id': 'woocommerce_automated_newsletter',
							'title': $rootScope.translations.automated_newsletter,
							'description': $rootScope.translations.woocommerce_automated_newsletter_description
						}
					];
				}
			}
		}, function() {

		} );


		vm.change_option = function( option ) {
			vm[ option + '_display' ] = !vm[ option + '_display' ];
		};

		vm.create_scenario = function( send_option ) {
			if ( $rootScope.settings.emailbuilder_installed ) {
				$rootScope.change_page_with_parameters( 'scenario', send_option + '/0/settings' );
			}
			else {
				$rootScope.display_emailbuilder_popup();
			}
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'SearchController', [
	'$rootScope', 'UrlService', '$sce',
	function( $rootScope, UrlService, $sce ) {

		var vm = this;

		vm.search = '';

		vm.search_faq = [];
		vm.search_campaigns = [];
		vm.search_module = [];

		vm.suggestion_faq = [];
		vm.suggestion_forum = [];

		vm.show_results = false;
		vm.show_suggestions = false;

		vm.details = {
			'question': '',
			'response': ''
		};

		function bold_search( search, data, key ) {
			var search_length = search.length;
			var i;
			var nb = data.length;
			var str_pos;
			var value;
			for ( i = 0; i < nb; i++ ) {
				value = data[ i ][ key ].valueOf().replace( '<b>', '' ).replace( '</b>', '' );
				if ( search_length !== 0 ) {
					str_pos = value.toLowerCase().indexOf( search.toLowerCase() );
					if ( str_pos !== -1 ) {
						value = value.substr( 0, str_pos ) + '<b>' + value.substr( str_pos, search_length ) + '</b>' + value.substr( str_pos + search_length, value.length );
					}
				}
				data[ i ][ key ] = $sce.trustAsHtml( value );
			}
			return data;
		}

		var results_loaded = 0;

		function display_depending_results( search ) {
			results_loaded++;
			if ( results_loaded === 3 ) {
				if ( vm.search_faq.length !== 0 || vm.search_campaigns.length !== 0 || vm.search_module.length !== 0 ) {
					vm.show_results = true;
					vm.show_suggestions = false;
				}
				else {
					get_suggestions( search );
					vm.show_results = false;
					vm.show_suggestions = true;
				}
			}
		}

		vm.search_text = function() {
			results_loaded = 0;
			var data_parameters = {
				search: vm.search
			};
			UrlService.post_data( 'jackmail_search_faq', data_parameters, function( data ) {
				vm.search_faq = bold_search( data_parameters.search, data, 'question' );
				display_depending_results( data_parameters.search );
			}, function() {

			} );
			UrlService.post_data( 'jackmail_search_campaigns', data_parameters, function( data ) {
				vm.search_campaigns = bold_search( data_parameters.search, data, 'name' );
				display_depending_results( data_parameters.search );
			}, function() {

			} );
			UrlService.post_data( 'jackmail_search_all', data_parameters, function( data ) {
				vm.search_module = bold_search( data_parameters.search, data, 'name' );
				display_depending_results( data_parameters.search );
			}, function() {

			} );
		};

		$rootScope.search_text = function() {
			if ( results_loaded === 0 ) {
				vm.search_text();
			}
		};

		function get_suggestions( search ) {
			if ( vm.suggestion_faq.length === 0 ) {
				UrlService.post_data( 'jackmail_suggestion_faq', {}, function( data ) {
					vm.suggestion_faq = bold_search( search, data, 'question' );
				}, function() {

				} );
			}
			else {
				vm.suggestion_faq = bold_search( search, vm.suggestion_faq, 'question' );
			}
			if ( vm.suggestion_forum.length === 0 ) {
				var data_parameters = {
					search: vm.search
				};
				UrlService.post_data( 'jackmail_suggestion_forum', data_parameters, function( data ) {
					vm.suggestion_forum = bold_search( search, data, 'title' );
				}, function() {

				} );
			}
			else {
				vm.suggestion_forum = bold_search( search, vm.suggestion_forum, 'title' );
			}
		}

		vm.display_forum = function() {
			UrlService.post_data( 'jackmail_get_forum', data_parameters, function( data ) {
				vm.search_faq = bold_search( data_parameters.search, data );
				display_depending_results( data_parameters.search );
			}, function() {

			} );
		};

		vm.display_campaign_page = function( id, status ) {
			if ( status === 'DRAFT' ) {
				$rootScope.change_page_with_parameters( 'campaign', id + '/contacts' );
				$rootScope.show_help1 = false;
				$rootScope.show_help2 = false;
			}
		};

		vm.display_page = function( id, type ) {
			if ( type === 'campaign' || type === 'scenario' ) {
				$rootScope.change_page( 'campaigns' );
			}
			else if ( type === 'list' ) {
				$rootScope.change_page_with_parameters( 'list', id );
			}
			else if ( type === 'statistics' ) {
				$rootScope.change_page( 'statistics' );
			}
			else if ( type === 'template' ) {
				$rootScope.change_page_with_parameters( 'template', id );
			}
			$rootScope.show_help1 = false;
			$rootScope.show_help2 = false;
		};

		vm.go_faq = function() {
			window.open( 'https://docs.jackmail.com/faq' );
		};

		vm.go_formular = function() {
			window.open( 'https://www.jackmail.com/contact' );
		};

		vm.go_forum = function() {
			window.open( 'https://wordpress.org/support/plugin/jackmail-newsletters' );
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'SettingsController', [
	'$rootScope', 'UrlService', '$timeout', 'PluginService', '$window', '$document',
	function( $rootScope, UrlService, $timeout, PluginService, $window, $document ) {

		var vm = this;

		vm.email = '';

		vm.plugins = PluginService.get_compatible_plugins();
		vm.nb_plugins_actived = -1;

		vm.domain = {
			'subdomain': '',
			'txt': '',
			'ns1': '',
			'ns2': '',
			'is_valid': false
		};

		var domain_saved = {
			'subdomain': '',
			'txt': '',
			'is_valid': false
		};

		vm.domain_step = 1;
		vm.edit_domain = -1;
		vm.show_steps = 1;
		vm.domain_configured = 0;

		vm.connectors = {
			'active': false,
			'ip_restriction': false,
			'allowed_ips': []
		};

		vm.display_button_add_connectors_allowed_ip = true;

		var last_allowed_ips = '';

		vm.link_tracking = {
			'active': false
		};

		vm.support_chat = {
			'active': false
		};

		vm.selected_role_text = $rootScope.translations.permissions;

		vm.roles_dropdown = [
			$rootScope.translations.administrators,
			$rootScope.translations.editors_and_administrators,
			$rootScope.translations.shop_managers_and_administrators
		];

		vm.selected_role_array = [ false, false, false ];

		vm.premium_notification = {
			'active': false
		};

		vm.formula = {
			'nb_credits': '',
			'subscription_type': '',
			'product_key': ''
		};

		vm.debug = {
			'server': '',
			'database': '',
			'php': '',
			'jackmail': '',
			'wordpress': '',
			'browser': '',
			'wordfence': '',
			'logs': ''
		};
		vm.debug_displayed = false;

		vm.debug_data = {
			'campaigns_data': '',
			'scenarios_data': '',
			'scenarios_details_data': '',
			'lists_data': ''
		};
		vm.debug_data_displayed = false;

		vm.improvement = {
			'active': false
		};

		vm.plugin_info = function( name ) {
			var display_for_plugins = [ 'Contact Form 7', 'Formidable Forms', 'Gravity Forms', 'Ninja Forms', 'WordPress' ];
			if ( display_for_plugins.indexOf( name ) !== -1 ) {
				return true;
			}
			return false;
		};

		UrlService.post_data( 'jackmail_account_info', {}, function( data ) {
			vm.email = data.email;
		}, function() {

		} );

		UrlService.post_data( 'jackmail_get_plugins', {}, function( data ) {
			vm.plugins = PluginService.get_plugins_to_display( vm.plugins, data );
			vm.nb_plugins_actived = data.length;
		}, function() {

		} );

		UrlService.post_data( 'jackmail_connectors_configuration', {}, function( data ) {
			last_allowed_ips = data.allowed_ips;
			data.allowed_ips = $rootScope.split( data.allowed_ips );
			vm.connectors = data;
			vm.display_button_add_connectors_allowed_ip = true;
		}, function() {

		} );

		UrlService.post_data( 'jackmail_get_link_tracking', {}, function( data ) {
			vm.link_tracking = data;
		}, function() {

		} );

		UrlService.post_data( 'jackmail_get_jackmail_role', {}, function( data ) {
			if ( data.role === 'administrator' ) {
				vm.selected_role_text = $rootScope.translations.administrators;
				vm.selected_role_array = [ true, false, false ];
			}
			else if ( data.role === 'editor' ) {
				vm.selected_role_text = $rootScope.translations.editors_and_administrators;
				vm.selected_role_array = [ false, true, false ];
			}
			else if ( data.role === 'shop_manager' ) {
				vm.selected_role_text = $rootScope.translations.shop_managers_and_administrators;
				vm.selected_role_array = [ false, false, true ];
			}
		}, function() {

		} );

		if ( $rootScope.settings.is_authenticated ) {
			UrlService.post_data( 'jackmail_credits_available', {}, function( data ) {
				vm.formula = data;
			}, function() {

			} );
		}

		UrlService.post_data( 'jackmail_get_support_chat', {}, function( data ) {
			vm.support_chat = data;
		}, function() {

		} );

		UrlService.post_data( 'jackmail_get_premium_notification', {}, function( data ) {
			vm.premium_notification = data;
		}, function() {

		} );

		UrlService.post_data( 'jackmail_get_improvement', {}, function( data ) {
			vm.improvement = data;
		}, function() {

		} );

		vm.import_plugins = function( plugin ) {
			vm.plugins[ plugin ].selected = !vm.plugins[ plugin ].selected;
			var data_parameters = {
				'plugins': $rootScope.join( PluginService.get_active_plugins( vm.plugins ) )
			};
			UrlService.post_data( 'jackmail_import_plugins', data_parameters, function( data ) {
				$rootScope.display_success_error( data.success, $rootScope.translations.information_saved, '' );
			}, function() {

			} );
		};

		UrlService.post_data( 'jackmail_domain_configuration', {}, function( data ) {
			vm.domain = data;
			domain_saved = {
				'subdomain': vm.domain.subdomain,
				'txt': vm.domain.txt,
				'is_valid': vm.domain.is_valid
			};
			if ( data.subdomain === '' ) {
				vm.edit_domain = 1;
				vm.show_steps = 0;
				vm.domain_configured = 0;
			}
			else {
				hide_edit_domain();
				vm.domain_configured = 1;
			}
		}, function() {

		} );

		vm.go_domain_previous_step = function() {
			vm.domain_step--;
		};

		vm.go_domain_next_step = function() {
			if ( vm.domain.subdomain !== '' && vm.domain.subdomain.indexOf( ' ' ) === -1 && vm.domain.subdomain.indexOf( '.' ) !== -1 ) {
				if ( vm.domain.subdomain.split( '.' ).length >= 3 ) {
					if ( vm.domain_step === 1 ) {
						var data_parameters = {
							'subdomain': vm.domain.subdomain
						};
						UrlService.post_data( 'jackmail_domain_get_txt_ns', data_parameters, function( data ) {
							if ( data.txt !== '' ) {
								vm.domain.txt = data.txt;
								vm.domain.ns1 = data.ns1;
								vm.domain.ns2 = data.ns2;
								vm.domain_step++;
								vm.show_steps = 1;
							}
						}, function() {

						} );
					}
					else if ( vm.domain_step === 2 ) {
						vm.domain_step++;
					}
				} else {
					$rootScope.display_error( $rootScope.translations.incorrect_sub_domain );
				}
			}
			else {
				$rootScope.display_error( $rootScope.translations.incorrect_sub_domain );
			}
		};

		vm.go_domain_step = function( domain_step ) {
			vm.edit_domain = 1;
			vm.domain_step = domain_step;
		};

		vm.delete_domain = function() {
			UrlService.post_data( 'jackmail_domain_delete', {}, function( data ) {
				if ( data.success ) {
					vm.domain = {
						'subdomain': '',
						'txt': '',
						'ns1': '',
						'ns2': '',
						'is_valid': false
					};
					domain_saved = {
						'subdomain': '',
						'txt': '',
						'is_valid': false
					};
					vm.domain_step = 1;
					vm.edit_domain = -1;
					vm.show_steps = 1;
					vm.domain_configured = 0;
				}
				$rootScope.display_success_error( data.success, $rootScope.translations.information_saved, '' );
			}, function() {

			} );
		};

		vm.show_edit_domain = function() {
			vm.edit_domain = 1;
			vm.domain_step = 1;
			vm.show_steps = 1;
		};

		function hide_edit_domain() {
			vm.edit_domain = 0;
		}

		vm.cancel_edit_domain = function() {
			vm.domain.subdomain = domain_saved.subdomain;
			vm.domain.txt = domain_saved.txt;
			vm.domain.is_valid = domain_saved.is_valid;
			hide_edit_domain();
		};

		vm.create_domain_delegation = function() {
			var data_parameters = {
				'subdomain': vm.domain.subdomain
			};
			UrlService.post_data( 'jackmail_domain_create_delegation', data_parameters, function( data ) {
				if ( data.success ) {
					hide_edit_domain();
					vm.domain.subdomain = data.subdomain;
					vm.domain.txt = data.txt;
					vm.domain.is_valid = data.is_valid;
					domain_saved = {
						'subdomain': vm.domain.subdomain,
						'txt': vm.domain.txt,
						'is_valid': vm.domain.is_valid
					};
					vm.domain_configured = 1;
				}
				$rootScope.display_success_error( data.success, $rootScope.translations.information_saved, '' );
			}, function() {

			} );
		};

		vm.connectors_change_status = function() {
			vm.connectors.active = !vm.connectors.active;
			var data_parameters = {
				'active': vm.connectors.active ? '1' : '0'
			};
			UrlService.post_data( 'jackmail_connectors_configure', data_parameters, function( data ) {
				$rootScope.display_success_error( data.success, $rootScope.translations.information_saved, '' );
			}, function() {

			} );
		};

		vm.connectors_configure_ip_restriction = function() {
			if ( vm.connectors.active ) {
				vm.connectors.ip_restriction = !vm.connectors.ip_restriction;
				var data_parameters = {
					'ip_restriction': vm.connectors.ip_restriction ? '1' : '0'
				};
				UrlService.post_data( 'jackmail_connectors_configure_ip_restriction', data_parameters, function( data ) {
					$rootScope.display_success_error( data.success, $rootScope.translations.information_saved, '' );
				}, function() {

				} );
			}
		};

		vm.connectors_configure_allowed_ips = function() {
			var allowed_ips = [];
			var i;
			var nb_allowed_ips = vm.connectors.allowed_ips.length;
			for ( i = 0; i < nb_allowed_ips; i++ ) {
				if ( vm.connectors.allowed_ips[ i ] !== '' ) {
					allowed_ips.push( vm.connectors.allowed_ips[ i ] );
				}
			}
			allowed_ips = $rootScope.join( allowed_ips );
			if ( last_allowed_ips !== allowed_ips ) {
				var data_parameters = {
					'allowed_ips': allowed_ips
				};
				UrlService.post_data( 'jackmail_connectors_configure_allowed_ips', data_parameters, function( data ) {
					last_allowed_ips = data.allowed_ips;
					data.allowed_ips = $rootScope.split( data.allowed_ips );
					vm.display_button_add_connectors_allowed_ip = true;
					vm.connectors.allowed_ips = data.allowed_ips;
					$rootScope.display_success_error( data.success, $rootScope.translations.information_saved, $rootScope.translations.invalid_ip_address );
				}, function() {

				} );
			}
			else {
				if ( vm.connectors.allowed_ips[ vm.connectors.allowed_ips.length - 1 ] === '' ) {
					vm.connectors.allowed_ips.splice( vm.connectors.allowed_ips.length - 1, 1 );
					vm.check_show_hide_button_add_connectors_allowed_ip();
				}
			}
		};

		vm.add_connectors_allowed_ip = function() {
			vm.connectors.allowed_ips.push( '' );
			$timeout( function() {
				angular.element( '.jackmail_settings_restriction_allowed_ips .jackmail_content_editable:last' ).focus();
			} );
			vm.display_button_add_connectors_allowed_ip = false;
		};

		vm.delete_connectors_allowed_ip = function( i ) {
			vm.connectors.allowed_ips.splice( i, 1 );
			vm.connectors_configure_allowed_ips();
		};

		vm.check_show_hide_button_add_connectors_allowed_ip = function( i ) {
			if ( vm.connectors.allowed_ips[ i ] !== '' ) {
				vm.display_button_add_connectors_allowed_ip = true;
			}
			else {
				vm.display_button_add_connectors_allowed_ip = false;
			}
		};

		vm.change_link_tracking = function() {
			if ( vm.link_tracking.active === '0' ) {
				vm.link_tracking.active = '1';
			}
			else {
				vm.link_tracking.active = '0';
			}
			var data_parameters = {
				'active': vm.link_tracking.active
			};
			UrlService.post_data( 'jackmail_set_link_tracking', data_parameters, function() {
				$rootScope.display_success( $rootScope.translations.information_saved );
			}, function() {

			} );
		};

		vm.change_jackmail_role = function( key ) {
			var selected_role_array = [ false, false, false ];
			selected_role_array[ key ] = true;
			vm.selected_role_array = selected_role_array;
			var data_parameters = {};
			if ( vm.selected_role_array[ 1 ] === true ) {
				vm.selected_role_text = $rootScope.translations.editors_and_administrators;
				data_parameters = {
					'role': 'editor'
				};
			}
			else if ( vm.selected_role_array[ 2 ] === true ) {
				vm.selected_role_text = $rootScope.translations.shop_managers_and_administrators;
				data_parameters = {
					'role': 'shop_manager'
				};
			}
			else {
				vm.selected_role_text = $rootScope.translations.administrators;
				data_parameters = {
					'role': 'administrator'
				};
			}
			UrlService.post_data( 'jackmail_set_jackmail_role', data_parameters, function() {
				$rootScope.display_success( $rootScope.translations.information_saved );
			}, function() {

			} );
		};

		vm.uninstall_emailbuilder = function() {
			UrlService.post_data( 'jackmail_uninstall_emailbuilder', {}, function() {
				window.location.reload();
				$rootScope.display_success( $rootScope.translations.emailbuilder_is_now_uninstalled );
			}, function() {

			} );
		};

		vm.user_disconnect = function() {
			$rootScope.display_validation(
				$rootScope.translations.log_out_when_login_out_you_will_loose_all_yout_data_and_will_need_to_set_up_jackmail_again,
				function() {
					UrlService.post_data( 'jackmail_user_disconnect', {}, function() {
						$rootScope.go_page( 'installation' );
					}, function() {

					} );
				}
			);
		};

		vm.display_hide_debug = function() {
			if ( vm.debug_displayed === true ) {
				vm.debug_displayed = false;
			}
			else {
				vm.display_debug();
			}
		};

		vm.display_debug = function() {
			UrlService.post_data( 'jackmail_get_debug', {}, function( data ) {
				vm.debug = angular.copy( data );
				vm.debug.browser = navigator.userAgent.toLowerCase();
				vm.debug_displayed = true;
				$timeout( function() {
					angular.element( $window ).scrollTop( angular.element( 'span[jackmail-checkbox="s.debug_data_displayed"]' ).position().top );
				} );
			}, function() {

			} );
		};

		vm.display_hide_debug_data = function() {
			if ( vm.debug_data_displayed === true ) {
				vm.debug_data_displayed = false;
			}
			else {
				vm.display_debug_data();
			}
		};

		vm.display_debug_data = function() {
			UrlService.post_data( 'jackmail_get_debug_data', {}, function( data ) {
				vm.debug_data = angular.copy( data );
				vm.debug_data_displayed = true;
				$timeout( function() {
					angular.element( $window ).scrollTop( angular.element( $document ).height() );
				} );
			}, function() {

			} );
		};

		vm.change_improvement = function() {
			vm.improvement.active = !vm.improvement.active;
			var data_parameters = {
				'active': vm.improvement.active
			};
			UrlService.post_data( 'jackmail_set_improvement', data_parameters, function() {
				$rootScope.display_success( $rootScope.translations.information_saved );
			}, function() {

			} );
		};

		vm.change_support_chat = function() {
			vm.support_chat.active = !vm.support_chat.active;
			var data_parameters = {
				'active': vm.support_chat.active
			};
			UrlService.post_data( 'jackmail_set_support_chat', data_parameters, function() {
				$rootScope.display_success( $rootScope.translations.information_saved );
				window.location.reload();
			}, function() {

			} );
		};

		vm.change_premium_notification = function() {
			vm.premium_notification.active = !vm.premium_notification.active;
			var data_parameters = {
				'active': vm.premium_notification.active
			};
			UrlService.post_data( 'jackmail_set_premium_notification', data_parameters, function() {
				$rootScope.display_success( $rootScope.translations.information_saved );
			}, function() {

			} );
		};

		vm.manual_update_data = function() {
			UrlService.post_data( 'jackmail_manual_update_data', {}, function() {

			}, function() {

			} );
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'StatisticsController', [
	'$rootScope', '$routeParams', 'UrlService', '$timeout', '$filter', 'GridService',
	function( $rootScope, $routeParams, UrlService, $timeout, $filter, GridService ) {

		var vm = this;

		$rootScope.grid_service = [ new GridService, new GridService, new GridService, new GridService ];

		vm.show_item = 'synthesis';

		vm.show_synthesis_item = 'graphic';

		vm.page_title = '';
		vm.page_title_type = '';
		vm.filter = {
			'selected_date1': $rootScope.settings.selected_date1,
			'selected_date2': $rootScope.settings.selected_date2
		};
		vm.selected_segments = [];
		vm.campaigns_grid = [];
		vm.nb_campaigns = -1;

		vm.monitoring_view = 'detailled';

		vm.monitoring_views_select_titles = [
			$rootScope.translations.simplified_reading,
			$rootScope.translations.detailed_reading
		];

		vm.monitoring_columns = [
			{ 'name': $rootScope.translations.email, 'field': 'email' },
			{ 'name': $rootScope.translations.openings, 'field': 'opens' },
			{ 'name': $rootScope.translations.clicks, 'field': 'clicks' },
			{ 'name': $rootScope.translations.desktop, 'field': 'desktop' },
			{ 'name': $rootScope.translations.mobile, 'field': 'mobile' }
		];

		vm.monitoring_search = '';
		var monitoring_parameters = {
			'search': '',
			'column': 'email',
			'order': 'ASC'
		};

		vm.monitoring_grid = [];
		vm.monitoring_grid_total_rows = '';
		vm.nb_monitoring_grid = -1;

		var technology_data = [];
		vm.technologies_grid = [];
		vm.nb_technologies_grid = -1;

		vm.technologies_first = [
			{ 'name': $rootScope.translations.email_client, 'value': 'messagerie' },
			{ 'name': $rootScope.translations.email_client_category, 'value': 'type_messagerie' },
			{ 'name': $rootScope.translations.o_s, 'value': 'os' },
			{ 'name': $rootScope.translations.device_category, 'value': 'type_device' }
		];

		vm.technologies_first_selected = {
			'name': $rootScope.translations.email_client,
			'value': 'messagerie'
		};

		vm.technologies_second = angular.copy( vm.technologies_first );

		vm.technologies_second_selected = {
			'name': $rootScope.translations.o_s,
			'value': 'os'
		};

		var numbers_period_default = {
			'recipients': 0,
			'opens': 0,
			'opens_percent': 0,
			'clicks': 0,
			'clicks_percent': 0,
			'unsubscribes': 0,
			'unsubscribes_percent': 0,
			'bounces': 0,
			'read_seconds': 0,
			'bounces_percent': 0,
			'openers_percent': 0,
			'no_openers_percent': 0
		};

		vm.numbers = {
			'period1': angular.copy( numbers_period_default ),
			'period2': angular.copy( numbers_period_default ),
			'tendency': {
				'recipients': 0,
				'clicks_percent': 0,
				'read_seconds': 0,
				'unsubscribes_percent': 0
			}
		};

		vm.more_actives_contacts = [];
		vm.nb_more_actives_contacts = -1;

		var synthesis_statistics = {
			'period1': [],
			'period2': [],
			'period1_openers': 0,
			'period2_openers': 0
		};

		var synthesis_timeline = [];
		vm.synthesis_timeline = [];
		vm.nb_synthesis_timeline = -1;

		vm.synthesis_graphic = [];

		vm.graphic_displays = {
			'recipients': true,
			'opens': true,
			'clicks': true,
			'unsubscribes': true
		};

		var graphic_labels = [ [], [] ];

		var graphic_colors = {
			'recipients': [ '#254872', '#92A3B8' ],
			'opens': [ '#00ADEF', '#80D6F7' ],
			'clicks': [ '#ABBF06', '#D5DF83' ],
			'unsubscribes': [ '#F66C6C', '#FBB5B5' ]
		};

		var graphic_lines = {
			'recipients': [
				{
					'label': $rootScope.translations.recipients,
					'strokeColor': graphic_colors.recipients[ 0 ],
					'pointColor': graphic_colors.recipients[ 0 ],
					'pointStrokeColor': '#fff',
					'pointHighlightFill': '#fff',
					'pointHighlightStroke': graphic_colors.recipients[ 0 ],
					'data': [],
					'saved_data': []
				},
				{
					'label': $rootScope.translations.recipients,
					'strokeColor': graphic_colors.recipients[ 1 ],
					'pointColor': graphic_colors.recipients[ 1 ],
					'pointStrokeColor': '#fff',
					'pointHighlightFill': '#fff',
					'pointHighlightStroke': graphic_colors.recipients[ 1 ],
					'data': [],
					'saved_data': []
				}
			],
			'opens': [
				{
					'label': $rootScope.translations.openings,
					'strokeColor': graphic_colors.opens[ 0 ],
					'pointColor': graphic_colors.opens[ 0 ],
					'pointStrokeColor': '#fff',
					'pointHighlightFill': '#fff',
					'pointHighlightStroke': graphic_colors.opens[ 0 ],
					'data': [],
					'saved_data': []
				},
				{
					'label': $rootScope.translations.openings,
					'strokeColor': graphic_colors.opens[ 1 ],
					'pointColor': graphic_colors.opens[ 1 ],
					'pointStrokeColor': '#fff',
					'pointHighlightFill': '#fff',
					'pointHighlightStroke': graphic_colors.opens[ 1 ],
					'data': [],
					'saved_data': []
				}
			],
			'clicks': [
				{
					'label': $rootScope.translations.clicks,
					'strokeColor': graphic_colors.clicks[ 0 ],
					'pointColor': graphic_colors.clicks[ 0 ],
					'pointStrokeColor': '#fff',
					'pointHighlightFill': '#fff',
					'pointHighlightStroke': graphic_colors.clicks[ 0 ],
					'data': [],
					'saved_data': []
				},
				{
					'label': $rootScope.translations.clicks,
					'strokeColor': graphic_colors.clicks[ 1 ],
					'pointColor': graphic_colors.clicks[ 1 ],
					'pointStrokeColor': '#fff',
					'pointHighlightFill': '#fff',
					'pointHighlightStroke': graphic_colors.clicks[ 1 ],
					'data': [],
					'saved_data': []
				}
			],
			'unsubscribes': [
				{
					'label': $rootScope.translations.unsubscribers,
					'strokeColor': graphic_colors.unsubscribes[ 0 ],
					'pointColor': graphic_colors.unsubscribes[ 0 ],
					'pointStrokeColor': '#fff',
					'pointHighlightFill': '#fff',
					'pointHighlightStroke': graphic_colors.unsubscribes[ 0 ],
					'data': [],
					'saved_data': []
				},
				{
					'label': $rootScope.translations.unsubscribers,
					'strokeColor': graphic_colors.unsubscribes[ 1 ],
					'pointColor': graphic_colors.unsubscribes[ 1 ],
					'pointStrokeColor': '#fff',
					'pointHighlightFill': '#fff',
					'pointHighlightStroke': graphic_colors.unsubscribes[ 1 ],
					'data': [],
					'saved_data': []
				}
			]
		};

		var read_seconds = [
			{ 'data': [] },
			{ 'data': [] }
		];

		vm.compare = false;

		vm.technologies_graphic = [];

		vm.technologies_os_details = [];

		vm.technologies_os_total = {
			'desktop_percent': '',
			'mobile_percent': ''
		};

		vm.technologies_softwares_details = [];

		vm.technologies_softwares_total = {
			'application_percent': '',
			'webmail_percent': ''
		};

		vm.left_height = function() {
			return angular.element( '.jackmail_statistics_content > div:last-child' ).height();
		};

		vm.select_monitoring_view = function( key ) {
			if ( key === 0 ) {
				vm.monitoring_view = 'simplified';
			}
			else {
				vm.monitoring_view = 'detailled';
			}
		};

		vm.monitoring_columns_displayed = function( key ) {
			return vm.monitoring_view === 'simplified' && ( key === 0 );
		};

		vm.monitoring_details = function( key ) {
			var new_value = !vm.monitoring_grid[ key ].show_details;
			var i;
			var nb_contacts = vm.monitoring_grid.length;
			for ( i = 0; i < nb_contacts; i++ ) {
				if ( i !== key ) {
					vm.monitoring_grid[ i ].show_details = false;
				}
			}
			vm.monitoring_grid[ key ].show_details = new_value;
		};

		vm.campaign_details = function( key ) {
			var new_value = !vm.campaigns_grid[ key ].show_details;
			var i;
			var nb_campaigns = vm.campaigns_grid.length;
			for ( i = 0; i < nb_campaigns; i++ ) {
				if ( i !== key ) {
					vm.campaigns_grid[ i ].show_details = false;
				}
			}
			vm.campaigns_grid[ key ].show_details = new_value;
		};

		function get_segments() {
			var segments = [];
			var i;
			var nb_segments = vm.selected_segments.length;
			for ( i = 0; i < nb_segments; i++ ) {
				segments.push( vm.selected_segments[ i ].id );
			}
			return $rootScope.join( segments );
		}

		vm.show_more_actives_contacts = function() {
			monitoring_parameters.column = 'opens';
			monitoring_parameters.order = 'DESC';
			vm.show_hide_item( 'monitoring' );
		};

		vm.show_hide_item = function( item ) {
			vm.show_item = item;
			refresh_data();
		};


		function refresh_data() {
			$timeout( function() {
				if (vm.show_item === 'synthesis' ) {
					get_campaigns_data( 'only_synthesis' );
					if ( vm.show_synthesis_item === 'timeline') {
						get_synthesis_timeline_data();
					}
				}
				if (vm.show_item === 'monitoring' ) {
					get_monitoring_data_reset();
				}
				if (vm.show_item === 'technologies' ) {
					get_technologies_data();
				}
			} );
		}

		function page_title() {
			var page_title = '';
			var page_title_type = '';
			if ( $rootScope.grid_service[ 0 ].nb_selected === 1 ) {
				var name = '';
				var i;
				var nb_campaigns = vm.campaigns_grid.length;
				for ( i = 0; i < nb_campaigns; i++ ) {
					if ( vm.campaigns_grid[ i ].selected ) {
						name = vm.campaigns_grid[ i ].name;
						if ( vm.campaigns_grid[ i ].type === 'campaign' ) {
							page_title_type = 'campaign';
						}
						else {
							page_title_type = 'scenario';
						}
						break;
					}
				}
				page_title = $rootScope.translations.from_campaign + ' "' + name + '"';
			}
			else if ( $rootScope.grid_service[ 0 ].nb_selected === 0 || $rootScope.grid_service[ 0 ].nb_selected > 1 ) {
				page_title = $rootScope.translations.campaigns_from + ' ' + $filter( 'formatedDate' )( vm.filter.selected_date1, 'gmt_to_timezone', '' ) + ' ' + $rootScope.translations.to + ' ' + $filter( 'formatedDate' )( vm.filter.selected_date2, 'gmt_to_timezone', '' );
			}
			vm.page_title = page_title;
			vm.page_title_type = page_title_type;
		}

		function grid_selection_change() {
			get_campaigns_data( 'only_synthesis' );
			get_monitoring_data_reset();
			get_technologies_data();
		}

		vm.grid_select_or_unselect_all = function() {
			vm.campaigns_grid = $rootScope.grid_service[ 0 ].grid_select_or_unselect_all( vm.campaigns_grid );
			grid_selection_change();
			page_title();
			grid_selection_cookie();
		};

		vm.grid_select_or_unselect_row = function( key ) {
			vm.campaigns_grid = $rootScope.grid_service[ 0 ].grid_select_or_unselect_row( vm.campaigns_grid, key );
			grid_selection_change();
			page_title();
			grid_selection_cookie();
		};

		vm.change_filter_date = function(date1, date2) {
			vm.filter.selected_date1 = date1;
			vm.filter.selected_date2 = date2;
			get_campaigns_data( 'all' );
			get_monitoring_data_reset();
			get_technologies_data();
			page_title();
			filter_data_cookie();
		};

		function grid_selection_cookie() {
			if ( !$routeParams.id_campaign && !$routeParams.id_list ) {
				var statistics_campaigns_selection = 'ALL';
				if ( $rootScope.grid_service[ 0 ].nb_selected !== vm.campaigns_grid.length ) {
					statistics_campaigns_selection = get_selected_id_campaigns();
				}
				var data_parameters = {
					'statistics_campaigns_selection': statistics_campaigns_selection
				};
				UrlService.post_data( 'jackmail_update_cookies', data_parameters, function( data ) {

				}, function() {

				} );
			}
		}

		function filter_data_cookie() {
			var data_parameters = {
				'selected_date1': vm.filter.selected_date1,
				'selected_date2': vm.filter.selected_date2
			};
			UrlService.post_data( 'jackmail_update_cookies', data_parameters, function( data ) {

			}, function() {

			} );
		}

		vm.campaign_range_by = function( i ) {
			vm.campaigns_grid = $rootScope.grid_service[ 0 ].grid_range( vm.campaigns_grid, i );
		};

		function get_campaigns_data( action ) {
			if ( vm.show_item === 'synthesis' ) {
				if ( action === 'all' ) {
					$timeout( function() {
						var data_parameters = {
							'selected_date1': vm.filter.selected_date1,
							'selected_date2': vm.filter.selected_date2
						};
						UrlService.post_data( 'jackmail_get_sent_campaigns', data_parameters, function( data ) {
							var i;
							var nb_data = data.length;
							for ( i = 0; i < nb_data; i++ ) {
								data[ i ].formated_date_campaign_sent = formated_date_campaign_sent( data[ i ] );
							}
							vm.campaigns_grid = data;
							vm.nb_campaigns = data.length;
							if ( $routeParams.id_campaign || $routeParams.id_list ) {
								var selected_campaigns = [];
								if ( $routeParams.id_campaign ) {
									selected_campaigns.push( 'campaign' + $routeParams.id_campaign );
								}
								if ( $routeParams.id_list ) {
									var nb_campaigns = vm.campaigns_grid.length;
									for ( i = 0; i < nb_campaigns; i++ ) {
										var j;
										var id_lists = $rootScope.split( vm.campaigns_grid[ i ].id_lists );
										var nb_lists = id_lists.length;
										for ( j = 0; j < nb_lists; j++ ) {
											if ( id_lists[ j ] === $routeParams.id_list ) {
												selected_campaigns.push( vm.campaigns_grid[ i ].type + '' + vm.campaigns_grid[ i ].id );
											}
										}
									}
								}
								vm.campaigns_grid = $rootScope.grid_service[ 0 ].grid_select_only_campaign_ids( vm.campaigns_grid, selected_campaigns );
							}
							else {
								if ( $rootScope.settings.statistics_campaigns_selection[ 0 ] === 'ALL' ) {
									vm.campaigns_grid = $rootScope.grid_service[ 0 ].grid_select_all( vm.campaigns_grid );
								} else {
									vm.campaigns_grid = $rootScope.grid_service[ 0 ].grid_select_only_campaign_ids( vm.campaigns_grid, $rootScope.settings.statistics_campaigns_selection );
								}
							}
							page_title();
							get_synthesis_statistics( 0 );
						}, function() {

						} );
					} );
				}
				else {
					get_synthesis_statistics( 0 );
				}
			}

			function get_synthesis_statistics() {
				var data_parameters = {
					'id_campaigns': get_selected_id_campaigns(),
					'selected_date1': vm.filter.selected_date1,
					'selected_date2': vm.filter.selected_date2,
					'segments': get_segments()
				};
				UrlService.post_data( 'jackmail_get_synthesis_more_actives_contacts', data_parameters, function( data ) {
					vm.more_actives_contacts = data;
					vm.nb_more_actives_contacts = vm.more_actives_contacts.length;
				}, function() {

				} );
				var refresh = 0;
				var synthesis_data = {};
				data_parameters[ 'period' ] = '1';
				UrlService.post_data( 'jackmail_get_synthesis', data_parameters, function( data ) {
					synthesis_data.period1 = data.period;
					synthesis_data.period1_openers = data.period_openers;
					refresh++;
					if ( refresh === 2 ) {
						set_synthesis();
					}
				}, function() {

				} );
				data_parameters[ 'period' ] = '2';
				UrlService.post_data( 'jackmail_get_synthesis', data_parameters, function( data ) {
					synthesis_data.period2 = data.period;
					synthesis_data.period2_openers = data.period_openers;
					refresh++;
					if ( refresh === 2 ) {
						set_synthesis();
					}
				}, function() {

				} );
				if ( vm.show_synthesis_item === 'timeline' ) {
					get_synthesis_timeline_data();
				}

				function set_synthesis() {
					synthesis_statistics = angular.copy( synthesis_data );
					set_synthesis_numbers( synthesis_statistics );
					refresh_graphic_data();
					get_synthesis_graphic();
					refresh_minis_synthesis_graphics();
				}

				function set_synthesis_numbers( data ) {
					var numbers = {
						'period1': angular.copy( numbers_period_default ),
						'period2': angular.copy( numbers_period_default )
					};
					var i;
					for ( i = 0; i < 2; i++ ) {
						var id = i + 1;
						var j;
						var nb_period_data = data[ 'period' + id ].length;
						for ( j = 0; j < nb_period_data; j++ ) {
							numbers[ 'period' + id ].recipients += data[ 'period' + id ][ j ].recipients;
							numbers[ 'period' + id ].opens += data[ 'period' + id ][ j ].opens;
							numbers[ 'period' + id ].clicks += data[ 'period' + id ][ j ].clicks;
							numbers[ 'period' + id ].unsubscribes += data[ 'period' + id ][ j ].unsubscribes;
							numbers[ 'period' + id ].bounces += data[ 'period' + id ][ j ].bounces;
							numbers[ 'period' + id ].read_seconds += data[ 'period' + id ][ j ].read_seconds;
						}
					}
					for ( i = 0; i < 2; i++ ) {
						id = i + 1;
						if ( numbers[ 'period' + id ].recipients > 0 ) {
							numbers[ 'period' + id ].clicks_percent = get_percent( numbers[ 'period' + id ].clicks, numbers[ 'period' + id ].recipients );
							numbers[ 'period' + id ].opens_percent = get_percent( numbers[ 'period' + id ].opens, numbers[ 'period' + id ].recipients );
							numbers[ 'period' + id ].unsubscribes_percent = get_percent( numbers[ 'period' + id ].unsubscribes, numbers[ 'period' + id ].recipients );
							numbers[ 'period' + id ].bounces_percent = get_percent( numbers[ 'period' + id ].bounces, numbers[ 'period' + id ].recipients );
							numbers[ 'period' + id ].openers_percent = get_percent( data[ 'period' + id + '_openers' ], numbers[ 'period' + id ].recipients );
							numbers[ 'period' + id ].no_openers_percent = numbers[ 'period' + id ].recipients > 0 ? get_percent( numbers[ 'period' + id ].recipients - data[ 'period' + id + '_openers' ] - numbers[ 'period' + id ].bounces, numbers[ 'period' + id ].recipients ) : 0;
						}
					}
					numbers.tendency = {
						'recipients': numbers.period1.recipients - numbers.period2.recipients,
						'clicks_percent': ( numbers.period1.clicks_percent - numbers.period2.clicks_percent ).toFixed( 2 ),
						'opens_percent': ( numbers.period1.opens_percent - numbers.period2.opens_percent ).toFixed( 2 ),
						'read_seconds': numbers.period1.read_seconds - numbers.period2.read_seconds,
						'unsubscribes_percent': ( numbers.period1.unsubscribes_percent - numbers.period2.unsubscribes_percent ).toFixed( 2 )
					};
					vm.numbers = angular.copy( numbers );
				}
			}
		}

		function formated_date_campaign_sent( campaign ) {
			if ( campaign.type === 'campaign' ) {
				if ( campaign.send_option === 'NOW' || campaign.send_option === 'DATE' ) {
					return $filter( 'formatedDate' )( campaign.send_option_date_begin_gmt, 'gmt_to_timezone', '' );
				}
			}
			else {
				return $filter( 'formatedDate' )( campaign.send_option_date_begin_gmt, 'gmt_to_timezone', '' );
			}
			return '';
		}

		function refresh_minis_synthesis_graphics() {
			var canvas = [
				{ 'name': 'recipients', 'color': '#254872' },
				{ 'name': 'clicks', 'color': '#abbf06' },
				{ 'name': 'opens', 'color': '#00adef' },
				{ 'name': 'read_seconds', 'color': '#254872' },
				{ 'name': 'unsubscribes', 'color': '#f66c6c' }
			];
			var i;
			var nb_canvas = canvas.length;
			for ( i = 0; i < nb_canvas; i++ ) {
				var current_canvas = canvas[ i ].name;
				var current_color = canvas[ i ].color;
				if ( current_canvas === 'read_seconds' ) {
					var current_data = read_seconds;
				}
				else {
					var current_data = graphic_lines[ current_canvas ];
				}
				var j;
				var nb_data = current_data[ 0 ].data.length;
				var data = [];
				for ( j = 0; j < nb_data; j++ ) {
					data.push( current_data[ 0 ].data[ j ] - current_data[ 1 ].data[ j ] );
				}
				var data_min = Math.min.apply( Math, data );
				var data_max = Math.max.apply( Math, data );
				nb_data = data.length;
				var max_absolute = Math.abs( data_min ) > data_max ? Math.abs( data_min ) : data_max;
				var width = 180;
				var height = 25;
				var x_px = Math.ceil( width / nb_data );
				var y_px = max_absolute > 0 ? ( height / max_absolute ) : 0;
				width = Math.ceil( x_px * nb_data );
				var c = document.getElementById( 'jackmail_statistics_synthesis_canvas_' + current_canvas );
				c.width = width;
				c.height = height * 2 + 10;
				var ctx = c.getContext( "2d" );
				ctx.strokeStyle = '#DBE5E7';
				ctx.moveTo( 0, 30 );
				ctx.lineTo( width - x_px, 30 );
				ctx.moveTo( 0, 30 );
				ctx.stroke();
				ctx.beginPath();
				ctx.strokeStyle = '#CDD4DE';
				ctx.lineWidth = 2;
				var x, y;
				for ( j = 0; j < nb_data; j++ ) {
					x = j * x_px;
					y = -( data[ j ] * y_px - 30 );
					ctx.lineTo( x, y );
					if ( j === nb_data - 1 ) {
						ctx.stroke();
						ctx.beginPath();
						ctx.strokeStyle = current_color;
						ctx.fillStyle = current_color;
						ctx.arc( x, y, 3, 0, 2 * Math.PI, false );
						ctx.fill();
					}
				}
				ctx.stroke();
			}
			var chart_data = [
				{ 'color': '#00ADEF', 'highlight': '#00ADEF', 'value': vm.numbers.period1.openers_percent },
				{ 'color': '#F66C6C', 'highlight': '#F66C6C', 'value': vm.numbers.period1.bounces_percent },
				{ 'color': '#DADADA', 'highlight': '#DADADA', 'value': vm.numbers.period1.no_openers_percent }
			];
			$timeout( function() {
				var ctx = document.getElementById( 'jackmail_chartjs_synthesis_repartition' ).getContext( '2d' );
				var options = {
					segmentShowStroke: false,
					percentageInnerCutout: 0,
					animationSteps: 40,
					animationEasing: 'easeOutQuart',
					animateRotate: true,
					animateScale: false,
					showTooltips: false
				};
				new Chart( ctx ).Doughnut( chart_data, options );
			} );
		}

		function get_selected_id_campaigns() {
			var id_campaigns = [];
			var i;
			var nb_campaigns = vm.campaigns_grid.length;
			for ( i = 0; i < nb_campaigns; i++ ) {
				if ( vm.campaigns_grid[ i ].selected ) {
					id_campaigns.push( vm.campaigns_grid[ i ].type + '' + vm.campaigns_grid[ i ].id );
				}
			}
			return $rootScope.join( id_campaigns );
		}

		function get_monitoring_data() {
			if ( vm.show_item === 'monitoring' ) {
				var begin = $rootScope.grid_service[ 1 ].begin;
				var data_parameters = {
					'id_campaigns': get_selected_id_campaigns(),
					'selected_date1': vm.filter.selected_date1,
					'selected_date2': vm.filter.selected_date2,
					'segments': get_segments(),
					'search': monitoring_parameters.search,
					'column': monitoring_parameters.column,
					'order': monitoring_parameters.order,
					'begin': begin
				};
				UrlService.post_data( 'jackmail_get_recipients', data_parameters, function( data ) {
					vm.monitoring_grid_total_rows = data.total_rows;
					if ( begin === 0 ) {
						vm.monitoring_grid = data.recipients;
					}
					else {
						vm.monitoring_grid = $rootScope.grid_service[ 1 ].merge_arrays( vm.monitoring_grid, data.recipients );
					}
					vm.nb_monitoring_grid = vm.monitoring_grid.length;
				}, function() {

				} );
			}
		}

		vm.get_monitoring_data_search_reset = function( search ) {
			monitoring_parameters.search = search;
			get_monitoring_data_reset();
		};

		vm.get_monitoring_data_more = function() {
			get_monitoring_data();
		};

		function get_monitoring_data_reset() {
			$rootScope.grid_service[ 1 ].reset_begin();
			angular.element( '.jackmail_statistics_monitoring .jackmail_grid_content_defined' ).scrollTop( 0 );
			get_monitoring_data();
		}

		vm.monitoring_range_by = function( i ) {
			if ( monitoring_parameters.column === i ) {
				if ( monitoring_parameters.order === 'ASC' ) {
					monitoring_parameters.order = 'DESC';
				}
				else {
					monitoring_parameters.order = 'ASC';
				}
			}
			else {
				monitoring_parameters.column = i;
				monitoring_parameters.order = 'ASC';
			}
			get_monitoring_data_reset();
		};

		function refresh_graphic_data() {
			var keys = [ 0, 1 ];
			var k;
			var nb_keys = keys.length;
			for ( k = 0; k < nb_keys; k++ ) {
				var key = k;
				if ( key === 0 ) {
					var data = synthesis_statistics[ 'period1' ];
				}
				else {
					var data = synthesis_statistics[ 'period2' ];
				}

				var nb_data = data.length;

				var recipients_data = [];
				var opens_data = [];
				var clicks_data = [];
				var unsubscribes_data = [];
				var read_seconds_data = [];

				var graphic_labels_data = [];

				var begin_date = $rootScope.date_from_str( vm.filter.selected_date1 );
				var end_date = $rootScope.date_from_str( vm.filter.selected_date2 );
				begin_date.setSeconds( begin_date.getSeconds() + $rootScope.settings.timezone * 60 * 60 );
				end_date.setSeconds( begin_date.getSeconds() + $rootScope.settings.timezone * 60 * 60 );
				begin_date = begin_date.getTime();
				end_date = end_date.getTime();

				if ( key === 1 ) {
					var seconds_in_day = 86400;
					var nb_days = Math.floor( ( end_date - begin_date ) / seconds_in_day );
					begin_date = begin_date - seconds_in_day - nb_days * seconds_in_day;
					end_date = end_date - seconds_in_day - nb_days * seconds_in_day;
				}

				var i;
				var dates = [];
				for ( i = begin_date; i <= end_date; i = i + 86400000) {
					recipients_data.push( 0 );
					opens_data.push( 0 );
					clicks_data.push( 0 );
					unsubscribes_data.push( 0 );
					read_seconds_data.push( 0 );
					graphic_labels_data.push( $filter( 'formatedDateFromTimestampToTimezone' )( i, '' ) );
					dates.push( $filter( 'formatedDateFromTimestampToTimezone' )( i, 'with_year' ) );
				}
				for ( i = 0; i < nb_data; i++ ) {
					var graphic_position = dates.indexOf( $filter( 'formatedDate' )( data[ i ].date, 'gmt_to_timezone', '' ) );
					if ( graphic_position !== -1 ) {
						recipients_data[ graphic_position ] += data[ i ].recipients;
						opens_data[ graphic_position ] += data[ i ].opens;
						clicks_data[ graphic_position ] += data[ i ].clicks;
						unsubscribes_data[ graphic_position ] += data[ i ].unsubscribes;
						read_seconds_data[ graphic_position ] += data[ i ].read_seconds;
					}
				}
				graphic_lines.recipients[ key ].data = recipients_data;
				graphic_lines.opens[ key ].data = opens_data;
				graphic_lines.clicks[ key ].data = clicks_data;
				graphic_lines.unsubscribes[ key ].data = unsubscribes_data;
				graphic_lines.recipients[ key ].saved_data = recipients_data;
				graphic_lines.opens[ key ].saved_data = opens_data;
				graphic_lines.clicks[ key ].saved_data = clicks_data;
				graphic_lines.unsubscribes[ key ].saved_data = unsubscribes_data;
				read_seconds[ key ].data = read_seconds_data;

				graphic_labels[ key ] = graphic_labels_data;
			}
		}

		vm.show_hide_graphic_legend = function( legend ) {
			if ( vm.graphic_displays[ legend ] ) {
				hide_graphic_legend( legend );
			}
			else {
				show_graphic_legend( legend );
			}
			if ( vm.show_synthesis_item === 'graphic' ) {
				get_synthesis_graphic();
			}
			else {
				get_synthesis_timeline();
			}
		};

		function show_graphic_legend( legend ) {
			vm.graphic_displays[ legend ] = true;
			graphic_lines[ legend ][ 0 ].data = angular.copy( graphic_lines[ legend ][ 0 ].saved_data );
			graphic_lines[ legend ][ 1 ].data = angular.copy( graphic_lines[ legend ][ 1 ].saved_data );
		}

		function hide_graphic_legend( legend ) {
			vm.graphic_displays[ legend ] = false;
			graphic_lines[ legend ][ 0 ].data = [];
			graphic_lines[ legend ][ 1 ].data = [];
		}

		vm.display_synthesis_graphic = function() {
			vm.show_synthesis_item = 'graphic';
			get_synthesis_graphic();
		};

		vm.display_synthesis_timeline = function() {
			vm.show_synthesis_item = 'timeline';
			get_synthesis_timeline_data();
		};

		function get_synthesis_timeline_data() {
			var data_parameters = {
				'id_campaigns': get_selected_id_campaigns(),
				'selected_date1': vm.filter.selected_date1,
				'selected_date2': vm.filter.selected_date2,
				'segments': get_segments()
			};
			UrlService.post_data( 'jackmail_get_synthesis_timeline', data_parameters, function( data ) {
				synthesis_timeline = angular.copy( data );
				get_synthesis_timeline();
			}, function() {

			} );
		}

		function get_synthesis_timeline() {
			vm.synthesis_timeline = $rootScope.generate_timeline_data( synthesis_timeline, vm.graphic_displays.opens, vm.graphic_displays.clicks, vm.graphic_displays.unsubscribes );
			vm.nb_synthesis_timeline = vm.synthesis_timeline.length;
		}

		function get_synthesis_graphic() {
			if ( vm.show_item === 'synthesis' ) {
				$timeout( function() {
					var data = [];
					data.push( graphic_lines.recipients[ 0 ] );
					data.push( graphic_lines.opens[ 0 ] );
					data.push( graphic_lines.clicks[ 0 ] );
					data.push( graphic_lines.unsubscribes[ 0 ] );
					var labels = [];
					if ( !vm.compare ) {
						labels = graphic_labels[ 0 ];
					}
					vm.synthesis_graphic = [ Math.floor( ( Math.random() * 100000 ) + 1 ) ];
					$timeout( function() {
						var graphic_data = {
							labels: labels,
							datasets: data
						};
						Chart.Scale = Chart.Scale.extend( {
							draw: function() {								var helpers = Chart.helpers;
								var each = helpers.each;
								var aliasPixel = helpers.aliasPixel;
								var toRadians = helpers.radians;
								var ctx = this.ctx,
									yLabelGap = ( this.endPoint - this.startPoint ) / this.steps,
									xStart = Math.round( this.xScalePaddingLeft );
								if ( this.display ) {
									ctx.fillStyle = this.textColor;
									ctx.font = this.font;
									each( this.yLabels, function( labelString, index ) {
										var yLabelCenter = this.endPoint - ( yLabelGap * index ),
											linePositionY = Math.round( yLabelCenter );

										ctx.textAlign = "right";
										ctx.textBaseline = "middle";
										if ( this.showLabels ) {
											ctx.fillText( labelString, xStart - 10, yLabelCenter );
										}
										ctx.beginPath();
										if ( index > 0 ) {
											ctx.lineWidth = this.gridLineWidth;
											ctx.strokeStyle = this.gridLineColor;
										}
										else {
											ctx.lineWidth = this.lineWidth;
											ctx.strokeStyle = this.lineColor;
										}

										linePositionY += helpers.aliasPixel( ctx.lineWidth );

										ctx.moveTo( xStart, linePositionY );
										ctx.lineTo( this.width, linePositionY );
										ctx.stroke();
										ctx.closePath();

										ctx.lineWidth = this.lineWidth;
										ctx.strokeStyle = this.lineColor;
										ctx.beginPath();
										ctx.moveTo( xStart - 5, linePositionY );
										ctx.lineTo( xStart, linePositionY );
										ctx.stroke();
										ctx.closePath();

									}, this );

									each( this.xLabels, function( label, index ) {
										if ( typeof label === "number" && label % 5 !== 0 ) {
											return;
										}
										var xPos = this.calculateX( index ) + aliasPixel( this.lineWidth ),
											linePos = this.calculateX( index - ( this.offsetGridLines ? 0.5 : 0 ) ) + aliasPixel( this.lineWidth ),
											isRotated = ( this.xLabelRotation > 0 );

										ctx.beginPath();

										if ( index > 0 ) {
											ctx.lineWidth = this.gridLineWidth;
											ctx.strokeStyle = this.gridLineColor;
										}
										else {
											ctx.lineWidth = this.lineWidth;
											ctx.strokeStyle = this.lineColor;
										}
										ctx.moveTo( linePos, this.endPoint );
										ctx.lineTo( linePos, this.startPoint - 3 );
										ctx.stroke();
										ctx.closePath();

										ctx.lineWidth = this.lineWidth;
										ctx.strokeStyle = this.lineColor;

										ctx.beginPath();
										ctx.moveTo( linePos, this.endPoint );
										ctx.lineTo( linePos, this.endPoint + 5 );
										ctx.stroke();
										ctx.closePath();

										ctx.save();
										ctx.translate( xPos, ( isRotated ) ? this.endPoint + 12 : this.endPoint + 8 );
										ctx.rotate( toRadians( this.xLabelRotation ) * -1 );

										ctx.textAlign = ( isRotated ) ? "right" : "center";
										ctx.textBaseline = ( isRotated ) ? "middle" : "top";

										if ( !vm.compare ) {
											var nb_graphic_labels = labels.length;
											var modulo;
											if ( nb_graphic_labels < 45 ) {
												modulo = 1;
											}
											else if ( nb_graphic_labels < 90 ) {
												modulo = 2;
											}
											else if ( nb_graphic_labels < 180 ) {
												modulo = 3;
											}
											else if ( nb_graphic_labels < 360 ) {
												modulo = 4;
											}
											else if ( nb_graphic_labels < 720 ) {
												modulo = 8;
											}
											else if ( nb_graphic_labels < 1440 ) {
												modulo = 16;
											}
											else {
												modulo = 32;
											}
											if ( index % modulo !== 0 ) {
												label = '';
											}
										}

										ctx.fillText( label, 0, 0 );

										ctx.restore();

									}, this );

								}
							}
						} );
						Chart.types.Line.extend( {
							name: 'LineJackmail',
							calculateXLabelRotation: function() {
								this.xLabelRotation = 0;
							},
							initialize: function( data ) {
								Chart.types.Line.prototype.initialize.apply( this, arguments );
							}
						} );
						var ctx = document.getElementById( 'jackmail_chartjs_synthesis' ).getContext( '2d' );
						var graphic_options = {
							datasetFill: false,
							responsive: true,
							maintainAspectRatio: false,
							animation: false,
							showTooltips: true,
							tooltipTitleFontSize: 0,
							pointDot: true,
							pointHitDetectionRadius: 0,
							scaleFontSize: 10,
							tooltipFontSize: 12,
							customTooltips: function( tooltip ) {
								var tooltipEl = angular.element( '#jackmail_chartjs_synthesis_tooltip' );
								if ( !tooltip ) {
									tooltipEl.css( {
										opacity: 0,
										display: 'none'
									} );
									return;
								}
								tooltipEl.removeClass( 'above below' );
								tooltipEl.addClass( tooltip.yAlign );
								var titles = tooltip.title.split( ' / ' );
								var innerHtml = '';
								var i;
								var label;
								var nb_labels = tooltip.labels.length;
								for ( i = 0; i < nb_labels; i++ ) {
									if ( i === 0 ) {
										innerHtml += '<b>' + titles[ 0 ];
										innerHtml += ' :</b>';
									}
									if ( tooltip.legendColors[ i ].fill !== 'transparent' ) {
										label = tooltip.labels[ i ].split( ':' );
										innerHtml += [
											'<div class="jackmail_chartjs_synthesis_tooltip-section">',
											'	<span class="jackmail_chartjs_synthesis_tooltip_key" style="background-color:' + tooltip.legendColors[ i ].fill + '"></span>',
											'	<span class="jackmail_chartjs_synthesis_tooltip-value">' +
											'		<span>' + label[ 0 ] + ': </span>' +											'		<span style="color:' + tooltip.legendColors[ i ].fill + '">' + label[ 1 ] + '</span>' +
											'	</span>',
											'</div>'
										].join( '' );
									}
								}
								tooltipEl.html( innerHtml );
								tooltipEl.css( {
									opacity: 1,
									display: 'block',
									left: ( tooltip.chart.canvas.offsetLeft + tooltip.x ) + 'px',
									top: ( tooltip.chart.canvas.offsetTop + tooltip.y - 45 ) + 'px',
									fontFamily: tooltip.fontFamily,
									fontSize: tooltip.fontSize,
									fontStyle: tooltip.fontStyle
								} );
							},
						};
						new Chart( ctx ).LineJackmail( graphic_data, graphic_options );
					} );
				} );
			}
		}

		function refresh_technologies_graphics() {
			var graphics_ids = [ 'jackmail_chartjs_technologies_os', 'jackmail_chartjs_technologies_softwares' ];
			var i;
			var nb_graphics = 2;
			for ( i = 0; i < nb_graphics; i++ ) {
				if ( i === 0 ) {
					var technologies_details = angular.copy( vm.technologies_os_details );
				}
				else {
					var technologies_details = angular.copy( vm.technologies_softwares_details );
				}
				var ctx = document.getElementById( graphics_ids[ i ] ).getContext( '2d' );
				var data = [];
				var j;
				var nb_details = technologies_details.length;
				for ( j = 0; j < nb_details; j++ ) {
					data.push( {
						'value': technologies_details[ j ].percent,
						'color': technologies_details[ j ].color,
						'highlight': technologies_details[ j ].color,
						'label': technologies_details[ j ].name
					} );
				}
				var options = {
					segmentShowStroke: false,
					percentageInnerCutout: 75,
					animationSteps: 40,
					animationEasing: 'easeOutQuart',
					animateRotate: true,
					animateScale: false,
					tooltipFontSize: 12,
					customTooltips: function( tooltip ) {
						var tooltipEl = angular.element( '#jackmail_chartjs_technologies_tooltip' );
						if ( !tooltip ) {
							tooltipEl.css( {
								opacity: 0
							} );
							return;
						}
						tooltipEl.removeClass( 'above below' );
						tooltipEl.addClass( tooltip.yAlign );
						var left = 21;
						var top = 42;
						var width = 82;
						var texts = tooltip.text.split( ': ' );
						var text = $filter( 'firstUppercaseOthersLowercase' )( texts[ 0 ] + ' :<br/>' + texts[ texts.length - 1 ] ) + ' %';
						tooltipEl.html( text );
						tooltipEl.css( {
							opacity: 1,
							left: tooltip.chart.canvas.offsetLeft + left + 'px',
							top: tooltip.chart.canvas.offsetTop + top + 'px',
							width: width + 'px',
							fontFamily: tooltip.fontFamily,
							fontSize: tooltip.fontSize,
							fontStyle: tooltip.fontStyle
						} );
					}
				};
				new Chart( ctx ).Doughnut( data, options );
			}
		}

		vm.technology_range_by = function( i ) {
			vm.technologies_grid = $rootScope.grid_service[ 2 ].grid_range( vm.technologies_grid, i );
			vm.nb_technologies_grid = vm.technologies_grid.length
		};

		function get_technologies_data() {
			if ( vm.show_item === 'technologies' ) {
				$timeout( function() {
					vm.technologies_graphic = [ Math.floor( ( Math.random() * 100000 ) + 1 ) ];
					$timeout( function() {
						var data_parameters = {
							'id_campaigns': get_selected_id_campaigns(),
							'selected_date1': vm.filter.selected_date1,
							'selected_date2': vm.filter.selected_date2,
							'segments': get_segments()
						};
						UrlService.post_data( 'jackmail_get_technologies', data_parameters, function( data ) {
							var i;
							var nb_data = data.operatingSystem_operatingSystemCategory.length;
							var total = 0;
							for ( i = 0; i < nb_data; i++ ) {
								total += data.operatingSystem_operatingSystemCategory[ i ].count;
							}
							data.total = total;
							technology_data = data;
							generate_technology_grid();
							var technologies_os_details = [];
							var technologies_os_total = {
								'desktop_percent': 0,
								'mobile_percent': 0
							};
							var technologies_softwares_details = [];
							var technologies_softwares_total = {
								'application_percent': 0,
								'webmail_percent': 0
							};
							var type = '';
							var count = 0;
							var color = '';
							var i_type1 = 0;
							var i_type2 = 0;
							var technologies_colors = {
								'type1': [
									'#5A992E', '#7EAE27', '#B5CE59', '#D6E5A2', '#E7F6D9'
								],
								'type2': [
									'#1C3850', '#244674', '#31639E', '#5284C1', '#8FB2D8'
								]
							};
							nb_data = data.operatingSystem_operatingSystemCategory.length;
							for ( i = 0; i < nb_data; i++ ) {
								type = ( data.operatingSystem_operatingSystemCategory[ i ].type ).toLowerCase();
								count = data.operatingSystem_operatingSystemCategory[ i ].count;
								if ( type === 'desktop' ) {
									technologies_os_total.desktop_percent += count;
									color = technologies_colors[ 'type1' ][ i_type1 ];
									i_type1++;
								}
								else {
									technologies_os_total.mobile_percent += count;
									color = technologies_colors[ 'type2' ][ i_type2 ];
									i_type2++;
								}
								technologies_os_details.push( {
									'name': os_name( data.operatingSystem_operatingSystemCategory[ i ].name ),
									'type': type,
									'percent': get_percent( count, data.total ),
									'color': color
								} );
							}
							i_type1 = 0;
							i_type2 = 0;
							var nb_data = data.browserGroup_browserCategory.length;
							for ( i = 0; i < nb_data; i++ ) {
								type = ( data.browserGroup_browserCategory[ i ].type === 'EMAILCLIENT' ? 'application' : 'webmail' );
								count = data.browserGroup_browserCategory[ i ].count;
								if ( type === 'application' ) {
									technologies_softwares_total.application_percent += count;
									color = technologies_colors[ 'type1' ][ i_type1 ];
									i_type1++;
								}
								else {
									technologies_softwares_total.webmail_percent += count;
									color = technologies_colors[ 'type2' ][ i_type2 ];
									i_type2++;
								}
								technologies_softwares_details.push( {
									'name': data.browserGroup_browserCategory[ i ].name,
									'type': type,
									'percent': get_percent( count, data.total ),
									'color': color
								} );
							}
							vm.technologies_os_total = {
								'desktop_percent': get_percent( technologies_os_total.desktop_percent, data.total ),
								'mobile_percent': get_percent( technologies_os_total.mobile_percent, data.total )
							};
							vm.technologies_softwares_total = {
								'application_percent': get_percent( technologies_softwares_total.application_percent, data.total ),
								'webmail_percent': get_percent( technologies_softwares_total.webmail_percent, data.total )
							};
							vm.technologies_os_details = angular.copy( technologies_os_details );
							vm.technologies_softwares_details = angular.copy( technologies_softwares_details );
							refresh_technologies_graphics();
						}, function() {

						} );
					} );
				} );
			}
		}

		function generate_technology_grid() {
			var json = [];
			var i;
			var nb_data;
			if ( ( vm.technologies_first_selected.value === 'type_messagerie' && vm.technologies_second_selected.value === 'os' )
				|| ( vm.technologies_first_selected.value === 'os' && vm.technologies_second_selected.value === 'type_messagerie' ) ) {
				nb_data = technology_data.operatingSystem_browserCategory.length;
				for ( i = 0; i < nb_data; i++ ) {
					json.push( {
						'os': os_name( technology_data.operatingSystem_browserCategory[ i ].name ),
						'type_messagerie': technology_data.operatingSystem_browserCategory[ i ].type === 'EMAILCLIENT' ? 'APPLICATION' : 'WEBMAIL',
						'openings': technology_data.operatingSystem_browserCategory[ i ].count,
						'percent': get_percent( technology_data.operatingSystem_browserCategory[ i ].count, technology_data.total )
					} );
				}
			}
			else if ( ( vm.technologies_first_selected.value === 'type_messagerie' && vm.technologies_second_selected.value === 'messagerie' )
				|| ( vm.technologies_first_selected.value === 'messagerie' && vm.technologies_second_selected.value === 'type_messagerie' ) ) {
				nb_data = technology_data.browserGroup_browserCategory.length;
				for ( i = 0; i < nb_data; i++ ) {
					json.push( {
						'type_messagerie': technology_data.browserGroup_browserCategory[ i ].type === 'EMAILCLIENT' ? 'APPLICATION' : 'WEBMAIL',
						'messagerie': technology_data.browserGroup_browserCategory[ i ].name,
						'openings': technology_data.browserGroup_browserCategory[ i ].count,
						'percent': get_percent( technology_data.browserGroup_browserCategory[ i ].count, technology_data.total )
					} );
				}
			}
			else if ( ( vm.technologies_first_selected.value === 'messagerie' && vm.technologies_second_selected.value === 'os' )
				|| ( vm.technologies_first_selected.value === 'os' && vm.technologies_second_selected.value === 'messagerie' ) ) {
				nb_data = technology_data.browserGroup_operatingSystem.length;
				for ( i = 0; i < nb_data; i++ ) {
					json.push( {
						'messagerie': technology_data.browserGroup_operatingSystem[ i ].name,
						'os': os_name( technology_data.browserGroup_operatingSystem[ i ].type ),
						'openings': technology_data.browserGroup_operatingSystem[ i ].count,
						'percent': get_percent( technology_data.browserGroup_operatingSystem[ i ].count, technology_data.total )
					} );
				}
			}
			else if ( ( vm.technologies_first_selected.value === 'messagerie' && vm.technologies_second_selected.value === 'type_device' )
				|| ( vm.technologies_first_selected.value === 'type_device' && vm.technologies_second_selected.value === 'messagerie' ) ) {
				nb_data = technology_data.browserGroup_operatingSystemCategory.length;
				for ( i = 0; i < nb_data; i++ ) {
					json.push( {
						'messagerie': technology_data.browserGroup_operatingSystemCategory[ i ].name,
						'type_device': technology_data.browserGroup_operatingSystemCategory[ i ].type,
						'openings': technology_data.browserGroup_operatingSystemCategory[ i ].count,
						'percent': get_percent( technology_data.browserGroup_operatingSystemCategory[ i ].count, technology_data.total )
					} );
				}
			}
			else if ( ( vm.technologies_first_selected.value === 'type_device' && vm.technologies_second_selected.value === 'type_messagerie' )
				|| ( vm.technologies_first_selected.value === 'type_messagerie' && vm.technologies_second_selected.value === 'type_device' ) ) {
				nb_data = technology_data.browserCategory_operatingSystemCategory.length;
				for ( i = 0; i < nb_data; i++ ) {
					json.push( {
						'type_device': technology_data.browserCategory_operatingSystemCategory[ i ].type,
						'type_messagerie': technology_data.browserCategory_operatingSystemCategory[ i ].name === 'EMAILCLIENT' ? 'APPLICATION' : 'WEBMAIL',
						'openings': technology_data.browserCategory_operatingSystemCategory[ i ].count,
						'percent': get_percent( technology_data.browserCategory_operatingSystemCategory[ i ].count, technology_data.total )
					} );
				}
			}
			else if ( ( vm.technologies_first_selected.value === 'type_device' && vm.technologies_second_selected.value === 'os' )
				|| ( vm.technologies_first_selected.value === 'os' && vm.technologies_second_selected.value === 'type_device' ) ) {
				nb_data = technology_data.operatingSystem_operatingSystemCategory.length;
				for ( i = 0; i < nb_data; i++ ) {
					json.push( {
						'type_device': technology_data.operatingSystem_operatingSystemCategory[ i ].type,
						'os': os_name( technology_data.operatingSystem_operatingSystemCategory[ i ].name ),
						'openings': technology_data.operatingSystem_operatingSystemCategory[ i ].count,
						'percent': get_percent( technology_data.operatingSystem_operatingSystemCategory[ i ].count, technology_data.total )
					} );
				}
			}
			vm.technologies_grid = json;
			vm.nb_technologies_grid = json.length;
		}

		function os_name( os ) {
			os = os.replace( '_', ' ' );
			os = os.replace( 'MACOS', 'MAC OS' );
			return os;
		}

		function get_percent( value, total ) {
			if ( total !== 0 ) {
				return parseFloat( ( value / total * 100 ).toFixed( 2 ) );
			}
			return 0;
		}

		vm.check_or_uncheck_technology_first = function( value ) {
			vm.technologies_first_selected.value = value;
			var i;
			var nb = vm.technologies_first.length;
			for ( i = 0; i < nb; i++ ) {
				if ( vm.technologies_first_selected.value === vm.technologies_first[ i ].value ) {
					vm.technologies_first_selected.name = vm.technologies_first[ i ].name;
					break;
				}
			}
			generate_technology_grid();
		};

		vm.check_or_uncheck_technology_second = function( value ) {
			vm.technologies_second_selected.value = value;
			var i;
			var nb = vm.technologies_second.length;
			for ( i = 0; i < nb; i++ ) {
				if ( vm.technologies_second_selected.value === vm.technologies_second[ i ].value ) {
					vm.technologies_second_selected.name = vm.technologies_second[ i ].name;
					break;
				}
			}
			generate_technology_grid();
		};

		vm.display_recipient_details = function( email ) {
			$rootScope.change_page_with_parameters( 'list_detail', email + '/list/0' );
		};

		vm.create_campaign_unopened = function() {
			var campaign_id = get_selected_id_campaigns();
			campaign_id = campaign_id.replace( /\D/g, '' );
			var data_parameters = {
				'id': campaign_id
			};
			UrlService.post_data( 'jackmail_duplicate_campaign', data_parameters, function( data ) {
				if ( data.success && data.id ) {
					var new_id_campaign = data.id;
					var data_parameters = {
						'new_id_campaign': new_id_campaign,
						'id_campaign': get_selected_id_campaigns(),
						'selected_date1': vm.filter.selected_date1,
						'selected_date2': vm.filter.selected_date2
					};
					UrlService.post_data( 'jackmail_add_campaign_contacts_unopened', data_parameters, function() {
						$rootScope.change_page_with_parameters( 'campaign', new_id_campaign + '/contacts' );
					}, function() {
					} );
				}
			}, function() {

			} );
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'TemplateController', [
	'$routeParams', '$rootScope', 'UrlService', 'EmailContentService', '$interval', '$location', '$q', 'VerificationService',
	function( $routeParams, $rootScope, UrlService, EmailContentService, $interval, $location, $q, VerificationService ) {

		var vm = this;

		EmailContentService.init_emailbuilder().then( function() {
			get_template_data();
		} );

		$rootScope.active_item_menu( 'templates' );

		vm.url_id = $routeParams.id;
		vm.gallery_id = $routeParams.gallery_id;

		vm.template = {};
		var saved_template = {};

		vm.name_editing = false;

		vm.current_content_email_type = 'emailbuilder';

		vm.show_name_popup = vm.url_id === '0';

		vm.save_choice_select_title_template = [
			$rootScope.translations.save_template,
			$rootScope.translations.save_template_and_start_my_campaign
		];

		vm.hide_name_popup = function() {
			vm.show_name_popup = false;
		};

		vm.focus_template_name = function() {
			if ( vm.template.name === 'Template sans nom' || vm.template.name === 'Template with no name' ) {
				vm.template.name = '';
			}
			angular.element( '.jackmail_name .jackmail_content_editable' ).focus();
			vm.name_editing = true;
		};

		vm.blur_template_name = function() {
			if ( vm.template.name === '' ) {
				vm.template.name = $rootScope.translations.template_with_no_name;
			}
			vm.name_editing = false;
		};

		function get_template_data() {
			if ( !$rootScope.settings.emailbuilder_installed ) {
				$rootScope.go_page( 'templates' );
			}
			if ( vm.url_id === '0' && vm.gallery_id !== '0' ) {
				var data_parameters = {
					'gallery_id': vm.gallery_id,
					'link_tracking': '1'
				};
				UrlService.post_data( 'jackmail_get_gallery_template_json', data_parameters, function( data ) {
					var content = EmailContentService.init_and_display_emailbuilder( data.content_email_json );
					vm.template = {
						'id': '0',
						'name': $rootScope.translations.template_with_no_name,
						'content_email_json': content.content_email_json,
						'content_email_html': '',
						'content_email_txt': ''
					};
					$rootScope.select_name_popup( vm.show_name_popup );
					saved_template = angular.copy( vm.template );
				}, function() {

				} );
			}
			else {
				var data_parameters = {
					'id': vm.url_id
				};
				UrlService.post_data( 'jackmail_get_template', data_parameters, function( data ) {
					if ( data === null ) {
						$rootScope.go_page( 'templates' );
					}
					EmailContentService.init_and_display_emailbuilder( data.content_email_json );
					vm.template = data;
					$rootScope.select_name_popup( vm.show_name_popup );
					saved_template = angular.copy( vm.template );
				}, function() {

				} );
			}
		}

		$interval( function() {
			check_save_template_needed();
		}, 300000 );

		function check_save_template_needed() {
			if ( !$rootScope.show_help2 && $rootScope.nb_loading === 0 ) {
				refresh_content_email();
				if ( vm.url_id !== '0' || vm.template.name !== $rootScope.translations.template_with_no_name || vm.template.content_email_changes ) {
					if ( VerificationService.differents_arrays( vm.template, saved_template ) ) {
						vm.save_template( false );
					}
				}
			}
		}

		vm.save_template = function( refresh_emailbuilder_changes ) {
			var deferred = $q.defer();
			if ( refresh_emailbuilder_changes ) {
				refresh_content_email();
			}
			var url_create = 'jackmail_create_template';
			var url_update = 'jackmail_update_template';
			if ( vm.url_id === '0' ) {
				UrlService.post_data( url_create, vm.template, function( data ) {
					if ( data.success ) {
						vm.url_id = data.id;
						vm.template.id = data.id;
						if ( vm.template.content_email_json !== data.content_email_json ) {
							vm.template.content_email_json = data.content_email_json;
							EmailContentService.set_emailbuilder_json( vm.c_common.campaign.content_email_json );
						}
						change_url();
					}
					$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_template_was_saved );
					deferred.resolve( data );
				}, function() {

				} );
			}
			else {
				UrlService.post_data( url_update, vm.template, function( data ) {
					if ( data.success ) {
						if ( vm.template.content_email_json !== data.content_email_json ) {
							vm.template.content_email_json = data.content_email_json;
							EmailContentService.set_emailbuilder_json( vm.template.content_email_json );
						}
					}
					$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_template_was_saved );
					deferred.resolve( data );
				}, function() {

				} );
			}
			saved_template = angular.copy( vm.template );
			return deferred.promise;
		};

		function refresh_content_email() {
			var content = EmailContentService.refresh_content_email( vm.current_content_email_type, vm.template.content_email_json, vm.template.content_email_html, vm.template.content_email_txt );
			if ( content.content_email_changes ) {
				vm.template.content_email_changes = content.content_email_changes;
				vm.template.content_email_json = content.content_email_json;
				vm.template.content_email_html = content.content_email_html;
				vm.template.content_email_txt = content.content_email_txt;
			}
		}

		function change_url() {
			var url = '/template/' + vm.url_id + '/0';
			if ( $location.path() !== url ) {
				UrlService.change_url_parameters_without_reload_and_history( url );
			}
		}

		vm.reset_emailbuilder_content = function() {
			$rootScope.display_validation( $rootScope.translations.you_will_loose_your_emailbuilder_content, function() {
				EmailContentService.set_emailbuilder_json( 'reset' );
				refresh_content_email();
			} );
		};

		vm.create_campaign_with_template = function() {
			var data_parameters = {
				'id': vm.url_id
			};
			UrlService.post_data( 'jackmail_create_campaign_with_template', data_parameters, function( data ) {
				$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_campaign_has_been_created );
				if ( data.success ) {
					$rootScope.change_page_with_parameters( 'campaign', data.id + '/contacts' );
				}
			}, function() {

			} );
		};

		vm.save_template_or_create_campaign = function( key ) {
			if ( key === 0 ) {
				vm.save_template( true );
			} else {
				vm.save_template( true ).then( function( data ) {
					if ( data.success ) {
						vm.create_campaign_with_template();
					}
				} );
			}
		};

	} ] );

angular.module( 'jackmail.controllers' ).controller( 'TemplatesController', [
	'UrlService', '$rootScope',
	function( UrlService, $rootScope ) {

		var vm = this;

		vm.current_page = 'templates_page';
		if ( window.location.href.indexOf( 'jackmail_campaign' ) !== -1 || window.location.href.indexOf( 'jackmail_scenario' ) !== -1 ) {
			vm.current_page = 'campaign_page';
		}

		vm.templates_type = [
			$rootScope.translations.my_templates,
			$rootScope.translations.templates_gallery
		];

		var templates_all = [];
		var templates = [];
		var nb_templates = -1;

		var templates_gallery_all = [];
		var templates_gallery = [];
		var nb_templates_gallery = -1;

		vm.templates_grid = [];
		vm.nb_templates_grid = -1;

		vm.displayed_templates = {
			'templates': false,
			'templates_gallery': false
		};

		vm.templates_gallery_categories = [];
		vm.selected_templates_gallery_category = '';

		vm.templates_search = '';

		function get_templates() {
			UrlService.post_data( 'jackmail_get_templates', {}, function( data ) {
				templates_all = data;
				if ( vm.current_page === 'templates_page' ) {
					templates_all.unshift( { 'name': '' } );
				}
				nb_templates = templates_all.length;
				templates = angular.copy( templates_all );
				if ( nb_templates > 1 || vm.current_page === 'campaign_page' ) {
					vm.display_templates();
				}
				else {
					vm.display_templates_gallery();
				}
			}, function() {

			} );
		}

		if ( vm.current_page === 'templates_page' ) {
			get_templates();
		}

		$rootScope.get_templates_from_campaign_page = function( templates_type ) {
			if ( templates_type === 'templates' ) {
				vm.displayed_templates = {
					'templates': true,
					'templates_gallery': false
				};
				if ( nb_templates === -1 ) {
					get_templates();
				}
				else {
					generate_templates_grid();
				}
			}
			else {
				vm.displayed_templates = {
					'templates': false,
					'templates_gallery': true
				};
				if ( nb_templates_gallery === -1 ) {
					get_templates_gallery();
				}
				else {
					generate_templates_gallery_grid();
				}
			}
		};

		function get_templates_gallery() {
			UrlService.post_data( 'jackmail_get_templates_gallery', {}, function( data ) {
				vm.templates_gallery_categories = get_templates_gallery_categories( data );
				templates_gallery_all = data;
				if ( vm.current_page === 'templates_page' ) {
					templates_gallery_all.unshift( { 'name': '' } );
				}
				nb_templates_gallery = templates_gallery_all.length;
				templates_gallery = angular.copy( templates_gallery_all );
				generate_templates_gallery_grid();
			}, function() {

			} );
		}

		function get_templates_gallery_categories( data ) {
			var i;
			var nb_templates = data.length;
			var templates_gallery_categories = [];
			for ( i = 0; i < nb_templates; i++ ) {
				var j;
				var nb_categories = data[ i ].categories.length;
				for ( j = 0; j < nb_categories; j++ ) {
					var category = data[ i ].categories[ j ];
					if ( templates_gallery_categories.indexOf( category ) === -1 ) {
						templates_gallery_categories.push( category );
					}
				}
			}
			templates_gallery_categories.sort();
			templates_gallery_categories.unshift( $rootScope.translations.all_categories );
			return templates_gallery_categories;
		}

		function generate_templates_grid() {
			search_on_templates();
			vm.templates_grid = $rootScope.generate_grid( templates );
			vm.nb_templates_grid = vm.templates_grid.length;
		}

		function generate_templates_gallery_grid() {
			search_on_templates();
			select_gallery_template_category();
			vm.templates_grid = $rootScope.generate_grid( templates_gallery );
			vm.nb_templates_grid = vm.templates_grid.length;
		}

		vm.display_templates_or_templates_gallery = function( key ) {
			if ( key === 0 ) {
				vm.display_templates();
			}
			else {
				vm.display_templates_gallery();
			}
		};

		vm.display_templates = function() {
			vm.displayed_templates = {
				'templates': true,
				'templates_gallery': false
			};
			if ( nb_templates === -1 ) {
				get_templates();
			}
			else {
				generate_templates_grid();
			}
		};

		vm.display_templates_gallery = function() {
			vm.displayed_templates = {
				'templates': false,
				'templates_gallery': true
			};
			if ( nb_templates_gallery === -1 ) {
				get_templates_gallery();
			}
			else {
				generate_templates_gallery_grid();
			}
		};

		vm.select_gallery_template_category = function( key, title ) {
			if ( title === $rootScope.translations.all_categories ) {
				vm.selected_templates_gallery_category = '';
			}
			else {
				vm.selected_templates_gallery_category = title;
			}
			generate_templates_gallery_grid();
		};

		function select_gallery_template_category() {
			if ( vm.selected_templates_gallery_category !== '' ) {
				var i;
				var nb_templates = templates_gallery.length;
				var data = [];
				for ( i = 0; i < nb_templates; i++ ) {
					if ( i === 0 && vm.current_page === 'templates_page' ) {
						data.push( templates_gallery[ i ] );
					}
					else {
						var j;
						var nb_categories = templates_gallery[ i ].categories.length;
						for ( j = 0; j < nb_categories; j++ ) {
							var category = templates_gallery[ i ].categories[ j ];
							if ( category === vm.selected_templates_gallery_category ) {
								data.push( templates_gallery[ i ] );
							}
						}
					}
				}
				templates_gallery = angular.copy( data );
			}
		}

		function search_on_templates() {
			var i;
			var new_data = [];
			if ( vm.displayed_templates.templates ) {
				var nb_templates = templates_all.length;
				var data = templates_all;
			}
			else {
				var nb_templates = templates_gallery_all.length;
				var data = templates_gallery_all;
			}
			for ( i = 0; i < nb_templates; i++ ) {
				if ( i === 0 && vm.current_page === 'templates_page' ) {
					new_data.push( data[ i ] );
				}
				else if ( ( data[ i ].name ).indexOf( vm.templates_search ) !== -1 ) {
					new_data.push( data[ i ] );
				}
			}
			if ( vm.displayed_templates.templates ) {
				templates = angular.copy( new_data );
			}
			else {
				templates_gallery = angular.copy( new_data );
			}
		}

		vm.search_on_templates = function() {
			if ( vm.displayed_templates.templates ) {
				generate_templates_grid();
			}
			else {
				generate_templates_gallery_grid();
			}
		};

		vm.import_gallery_template = function( id ) {
			if ( $rootScope.settings.emailbuilder_installed ) {
				$rootScope.change_page_with_parameters( 'template', '0/' + id );
			}
			else {
				$rootScope.display_emailbuilder_popup();
			}
		};

		vm.create_new_template = function() {
			if ( $rootScope.settings.emailbuilder_installed ) {
				$rootScope.change_page_with_parameters( 'template', '0/0' );
			}
			else {
				$rootScope.display_emailbuilder_popup();
			}
		};

		vm.edit_template = function( id ) {
			if ( $rootScope.settings.emailbuilder_installed ) {
				$rootScope.change_page_with_parameters( 'template', id + '/0' );
			}
			else {
				$rootScope.display_emailbuilder_popup();
			}
		};

		vm.create_campaign_with_template = function( id ) {
			if ( $rootScope.settings.emailbuilder_installed ) {
				var data_parameters = {
					'id': id
				};
				UrlService.post_data( 'jackmail_create_campaign_with_template', data_parameters, function( data ) {
					$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_campaign_has_been_created );
					if ( data.success ) {
						$rootScope.change_page_with_parameters( 'campaign', data.id + '/contacts' );
					}
				}, function() {

				} );
			} else {
				$rootScope.display_emailbuilder_popup();
			}
		};

		vm.duplicate_template = function( id ) {
			var data_parameters = {
				'id': id
			};
			UrlService.post_data( 'jackmail_duplicate_template', data_parameters, function( data ) {
				get_templates();
				$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_template_has_been_duplicated );
			}, function() {

			} );
		};

		vm.delete_confirm = function( key, subkey ) {
			vm.templates_grid[ key ][ subkey ].show_delete_confirmation = true;
		};

		vm.delete_cancel = function( key, subkey ) {
			vm.templates_grid[ key ][ subkey ].show_delete_confirmation = false;
		};

		vm.delete_template = function( id ) {
			var data_parameters = {
				'id': id
			};
			UrlService.post_data( 'jackmail_delete_template', data_parameters, function( data ) {
				$rootScope.display_success_error_writable( data.success, $rootScope.translations.the_template_was_deleted );
				get_templates();
			}, function() {

			} );
		};

		vm.display_actions = function( key, subkey ) {
			vm.templates_grid[ key ][ subkey ].show_actions = true;
		};

		vm.hide_actions = function( key, subkey ) {
			vm.templates_grid[ key ][ subkey ].show_actions = false;
		};

	} ] );