'use strict';

angular.module( 'jackmail.filters' ).filter( 'blacklistType', [
	'$rootScope',
	function( $rootScope ) {
		return function( type ) {
			if ( type !== undefined ) {
				if ( type === $rootScope.settings.blacklist_type_bounces ) {
					return $rootScope.translations.hardbounced;
				}
				else if ( type === $rootScope.settings.blacklist_type_complaints ) {
					return $rootScope.translations.complained;
				}
				else if ( type === $rootScope.settings.blacklist_type_unsubscribes ) {
					return $rootScope.translations.unsubscribed;
				}
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'campaignListName', [
	'$rootScope',
	function( $rootScope ) {
		return function( id_list, lists ) {
			if ( id_list !== undefined && lists !== undefined ) {
				if ( id_list === '0' ) {
					return $rootScope.translations.campaign;
				}
				else {
					var i;
					var nb_lists = lists.length;
					for ( i = 0; i < nb_lists; i++ ) {
						if ( lists[ i ].id === id_list ) {
							return lists[ i ].name;
						}
					}
				}
			}
			return '-';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'campaignStatus', [
	'$rootScope',
	function( $rootScope ) {
		return function( status ) {
			if ( status !== undefined ) {
				if ( status === 'DRAFT' ) {
					return $rootScope.translations.draft;
				}
				else if ( status === 'SENT' ) {
					return $rootScope.translations.sent;
				}
				else if ( status === 'PROCESS_SCHEDULED' || status === 'SCHEDULED' ) {
					return $rootScope.translations.scheduled;
				}
				else if ( status === 'PROCESS_SENDING' || status === 'SENDING' ) {
					return $rootScope.translations.sending;
				}
				else if ( status === 'ACTIVED' ) {
					return $rootScope.translations.actived;
				}
				else if ( status === 'REFUSED' ) {
					return $rootScope.translations.refused;
				}
				else if ( status === 'ERROR' ) {
					return $rootScope.translations.error;
				}
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'campaignType', [
	'$rootScope',
	function( $rootScope ) {
		return function( type ) {
			if ( type !== undefined ) {
				if ( type === 'campaign' ) {
					return $rootScope.translations.campaign;
				}
				else {
					return $rootScope.translations.scenario;
				}
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'date1DiffDate2', [
	'$rootScope',
	function( $rootScope ) {
		return function( date1, date2 ) {
			if ( date1 !== undefined && date2 !== undefined ) {
				if ( date1 === '0000-00-00 00:00:00' || date2 === '0000-00-00 00:00:00' ) {
					return 0;
				}
				date1 = $rootScope.date_from_str( date1 );
				date2 = $rootScope.date_from_str( date2 );
				return parseInt( ( date1 - date2 ) / 1000 );
			}
			return 0;
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'firstUppercaseOthersLowercase', [
	function() {
		return function( text ) {
			if ( text !== undefined ) {
				return text.charAt( 0 ).toUpperCase() + text.substring( 1 ).toLowerCase()
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'formatedDateFromTimestampToTimezone', [
	'$rootScope',
	function( $rootScope ) {
		return function( timestamp, format ) {
			var result_date = '';
			if ( timestamp !== undefined ) {
				var date = new Date( timestamp );
				
				var day = date.getDate();
				if ( day < 10 ) {
					day = '0' + day;
				}
				var month = date.getMonth();
				var year = date.getFullYear();
				result_date = day + ' ' + $rootScope.months[ month ];
				if ( format === 'with_year' ) {
					result_date = result_date + ' ' + year;
				}
				else if ( format === 'file_name' ) {
					var hour = date.getHours();
					if ( hour < 10 ) {
						hour = '0' + hour;
					}
					var minute = date.getMinutes();
					if ( minute < 10 ) {
						minute = '0' + minute;
					}
					var second = date.getSeconds();
					if ( second < 10 ) {
						second = '0' + second;
					}
					result_date = day + '-' + $rootScope.months[ month ] + '-' + year + '-' + hour + '-' + minute + '-' + second;
				}
			}
			return result_date;
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'formatedDate', [
	'$rootScope',
	function( $rootScope ) {
		return function( date, type, format ) {
			var result_date = '';
			if ( date !== undefined ) {
				if ( date === '0000-00-00 00:00:00' || date === '' ) {
					return '';
				}
				date = $rootScope.date_from_str( date );
				if ( type === 'gmt_to_timezone' ) {
					date.setSeconds( date.getSeconds() + $rootScope.settings.timezone * 60 * 60 );
				}
				else {
					date.setSeconds( date.getSeconds() - $rootScope.settings.timezone * 60 * 60 );
				}
				var day = date.getDate();
				if ( day < 10 ) {
					day = '0' + day;
				}
				var month = date.getMonth();
				var year = date.getFullYear();
				result_date = day + ' ' + $rootScope.months[ parseInt( month ) ] + ' ' + year;
				if ( format === 'hours' || format === 'sql' ) {
					var hour = date.getHours();
					if ( hour < 10 ) {
						hour = '0' + hour;
					}
					var minute = date.getMinutes();
					if ( minute < 10 ) {
						minute = '0' + minute;
					}
					if ( format === 'hours' ) {
						result_date = result_date + ' ' + $rootScope.translations.at + ' ' + hour + ':' + minute;
					}
					else if ( format === 'sql' ) {
						month++;
						if ( month < 10 ) {
							month = '0' + month;
						}
						var second = date.getSeconds();
						if ( second < 10 ) {
							second = '0' + second;
						}
						result_date = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
					}
				}
			}
			return result_date;
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'numberSeparator', [
	'$rootScope',
	function( $rootScope ) {
		return function( number ) {
			if ( number !== undefined && number !== null ) {
				var separator = ',';
				if ( $rootScope.settings.language === 'fr' ) {
					separator = '.';
				}
				var n = number.toString();
				var p = n.indexOf( '.' );
				return n.replace( /\d(?=(?:\d{3})+(?:\.|$))/g, function( a, i ) {
					if ( p < 0 || i < p ) {
						return a + separator;
					}
					return a;
				} );
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'pluginName', [
	function() {
		return function( plugin ) {
			if ( plugin !== undefined ) {
				if ( plugin.indexOf( 'bloom' ) !== -1 ) {
					return 'Bloom';
				}
				if ( plugin.indexOf( 'contactform7' ) !== -1 ) {
					return 'Contact Form 7';
				}
				if ( plugin.indexOf( 'formidableforms' ) !== -1 ) {
					return 'Formidable Forms';
				}
				if ( plugin.indexOf( 'gravityforms' ) !== -1 ) {
					return 'Gravity Forms';
				}
				if ( plugin.indexOf( 'mailpoet2' ) !== -1 ) {
					return 'MailPoet 2';
				}
				if ( plugin.indexOf( 'mailpoet3' ) !== -1 ) {
					return 'MailPoet 3';
				}
				if ( plugin.indexOf( 'ninjaforms' ) !== -1 ) {
					return 'Ninja Forms';
				}
				if ( plugin.indexOf( 'popupbysupsystic' ) !== -1 ) {
					return 'PopUp by Supsystic';
				}
				if ( plugin.indexOf( 'woocommerce' ) !== -1 ) {
					return 'WooCommerce';
				}
				if ( plugin.indexOf( 'wordpress' ) !== -1 ) {
					return 'WordPress';
				}
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'pluginUrl', [
	function() {
		return function( plugin ) {
			if ( plugin !== undefined ) {
				if ( plugin.indexOf( 'bloom' ) !== -1 ) {
					return 'admin.php?page=et_bloom_options';
				}
				if ( plugin.indexOf( 'contactform7' ) !== -1 ) {
					return 'admin.php?page=wpcf7';
				}
				if ( plugin.indexOf( 'formidableforms' ) !== -1 ) {
					return 'admin.php?page=formidable';
				}
				if ( plugin.indexOf( 'gravityforms' ) !== -1 ) {
					return 'admin.php?page=gravityforms';
				}
				if ( plugin.indexOf( 'mailpoet2' ) !== -1 ) {
					return 'admin.php?page=wysija_campaigns';
				}
				if ( plugin.indexOf( 'mailpoet3' ) !== -1 ) {
					return 'admin.php?page=mailpoet-newsletters';
				}
				if ( plugin.indexOf( 'ninjaforms' ) !== -1 ) {
					return 'admin.php?page=ninja-forms';
				}
				if ( plugin.indexOf( 'popupbysupsystic' ) !== -1 ) {
					return 'admin.php?page=popup-wp-supsystic';
				}
				if ( plugin.indexOf( 'woocommerce' ) !== -1 ) {
					return 'edit.php?post_type=shop_order';
				}
				if ( plugin.indexOf( 'wordpress' ) !== -1 ) {
					return 'users.php';
				}
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'scenarioType', [
	'$rootScope',
	function( $rootScope ) {
		return function( scenario_type ) {
			if ( scenario_type !== undefined ) {
				if ( scenario_type !== 'NOW' && scenario_type !== 'DATE' ) {
					if ( $rootScope.translations[ scenario_type ] ) {
						return $rootScope.translations[ scenario_type ];
					}
				}
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'secondsConversion', [
	function() {
		return function( seconds ) {
			if ( seconds !== undefined ) {
				if ( seconds < 60 ) {
					return seconds;
				}
				else {
					var minutes = Math.floor( seconds / 60 );
					var seconds = seconds - minutes * 60;
					return minutes + 'm' + seconds;
				}
			}
			return '';
		};
	} ] );

angular.module( 'jackmail.filters' ).filter( 'textOverflow', [
	function() {
		return function( text ) {
			if ( text !== undefined ) {
				if ( text.length > 70 ) {
					return text.substring( 0, 70 ) + '...';
				}
				return text;
			}
			return '';
		};
	} ] );