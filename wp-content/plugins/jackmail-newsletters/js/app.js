'use strict';

var jackmail_app = angular.module( 'jackmail_app', [
	'ngRoute',
	'jackmail.controllers',
	'jackmail.directives',
	'jackmail.filters',
	'jackmail.services'
] )

	.config( [ '$locationProvider', function( $locationProvider ) {
		$locationProvider.hashPrefix( '' );
	} ] )

	.config( [ '$routeProvider', '$httpProvider', function( $routeProvider, $httpProvider ) {

		$routeProvider
			.when( '/campaigns', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_campaigns_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_campaigns_page' ]
				}
			} )
			.when( '/campaign/:id/:step', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_campaign_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_campaign_page' ]
				}
			} )
			.when( '/scenario_choice', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_scenario_choice_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_scenario_choice_page' ]
				}
			} )
			.when( '/scenario/:choice/:id/:step', {
				templateUrl: function( url ) {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_scenario_page' +
						'&key=' + jackmail_ajax_object.key +
						'&choice=' + url.choice +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_scenario_page' ]
				}
			} )
			.when( '/lists', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_lists_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_lists_page' ]
				}
			} )
			.when( '/list/:id', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_list_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_list_page' ]
				}
			} )
			.when( '/list_detail/:email/list/:id_list', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_list_detail_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_list_detail_page' ]
				}
			} )
			.when( '/list_detail/:email/campaign/:id_campaign', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_list_detail_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_list_detail_page' ]
				}
			} )
			.when( '/templates', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_templates_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_templates_page' ]
				}
			} )
			.when( '/template/:id/:gallery_id', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_template_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_template_page' ]
				}
			} )
			.when( '/statistics', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_statistics_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_statistics_page' ]
				}
			} )
			.when( '/statistics/list/:id_list', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_statistics_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_statistics_page' ]
				}
			} )
			.when( '/statistics/campaign/:id_campaign', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_statistics_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_statistics_page' ]
				}
			} )
			.when( '/settings', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_settings_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_settings_page' ]
				}
			} )
			.when( '/settings/:item', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_settings_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_settings_page' ]
				}
			} )
			.when( '/installation', {
				templateUrl: function() {
					return jackmail_ajax_object.ajax_url +
						'?action=jackmail_installation_page' +
						'&key=' + jackmail_ajax_object.key +
						'&nonce=' + jackmail_ajax_object.urls[ 'jackmail_installation_page' ]
				}
			} )
			.otherwise( {
				redirectTo: '/campaigns'
			} );

		$httpProvider.interceptors.push( [ '$rootScope', '$q', function( $rootScope, $q ) {
			return {
				'request': function( config ) {
					if ( config.method === 'GET' ) {
						if ( config.url.indexOf( 'action=' ) !== -1 ) {
							config.method = 'POST';
							var url = config.url.split( '?' );
							config.url = url[ 0 ];
							if ( url.length > 1 ) {
								config.data = url[ 1 ];
							}
							config.headers[ 'Content-Type' ] = 'application/x-www-form-urlencoded';
						}
					}
					return config || $q.when( config );
				},
				'response': function( response ) {
					return response || $q.when( response );
				},
				'responseError': function( rejection ) {
					return $q.reject( rejection );
				}
			};
		} ] );

	} ] )

	.run( [ '$route', '$rootScope', '$location', '$window', function( $route, $rootScope, $location ) {

		var original = $location.path;
		$location.path = function( path, reload ) {
			if ( reload === false ) {
				var last_route = $route.current;
				$rootScope.$on( '$locationChangeSuccess', function() {
					$route.current = last_route;
				} );
			}
			return original.apply( $location, [ path ] );
		};

	} ] );

angular.module( 'jackmail.controllers', [] );

angular.module( 'jackmail.directives', [] );

angular.module( 'jackmail.filters', [] );

angular.module( 'jackmail.services', [] );