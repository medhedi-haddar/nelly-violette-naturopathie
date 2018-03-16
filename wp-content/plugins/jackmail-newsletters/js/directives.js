'use strict';

angular.module( 'jackmail.directives' ).directive( 'jackmailButtonDelete', [
	function() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				whenDelete: '&',
				deleteValue: '@'
			},
			controllerAs: 'djbd',
			template: [
				'<span class="jackmail_input_edit_container">' +
				'	<span ng-click="$event.stopPropagation()" title="{{$root.translations.delete}}" class="jackmail_display_delete_value">' +
				'		<span ng-hide="djbd.deleteValue" class="dashicons dashicons-no"></span>' +
				'		<input ng-show="djbd.deleteValue" type="button" class="jackmail_white_button" value="{{djbd.deleteValue}}"/>' +
				'		<span class="jackmail_display_delete_confirmation jackmail_display_delete_confirmation_left">' +
				'			<span ng-click="djbd.delete_name( $event )" class="jackmail_display_delete_confirmation_text">{{$root.translations.confirm}}</span>' +
				'			<span class="jackmail_display_delete_confirmation_popin">' +
				'				<span class="jackmail_display_delete_confirmation_popin_border">' +
				'					<span></span>' +
				'					<span></span>' +
				'				</span>' +
				'			</span>' +
				'		</span>' +
				'	</span>' +
				'</span>'
			],
			controller: [ function() {

				var vm = this;

				vm.delete_name = function( $event ) {
					$event.stopPropagation();
					vm.whenDelete();
				};

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailCheckboxSimple', [
	function() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				jackmailCheckboxSimple: '<',
				checkboxClick: '&',
				checkboxClass: '@',
				checkboxTitle: '@'
			},
			controllerAs: 'djcs',
			template: [
				'<span ng-click="djcs.checkboxClick()" class="jackmail_vertical_middle_container">' +
				'	<span ng-class="djcs.jackmailCheckboxSimple ? \'jackmail_checked {{djcs.checkboxClass}}\' : \'jackmail_unchecked\'"></span>' +
				'	<span class="jackmail_checkbox_title" ng-show="djcs.checkboxTitle">{{djcs.checkboxTitle}}</span>' +
				'</span>'
			],
			controller: [ function() {

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailCheckbox', [
	function() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				jackmailCheckbox: '<',
				checkboxClick: '&',
				checkboxTitle: '@',
				checkboxDisabled: '<'
			},
			controllerAs: 'djc',
			template: [
				'<span ng-click="djc.checkbox_click()">' +
				'	<span class="jackmail_button_not_checked" ng-class="djc.checkboxDisabled ? \'jackmail_checkbox_disabled\' : \'\'">' +
				'		<span ng-class="djc.jackmailCheckbox ? \'jackmail_button_checked dashicons dashicons-yes\' : \'\'"></span>' +
				'	</span>' +
				'	<span class="jackmail_checkbox_title" ng-show="djc.checkboxTitle">{{djc.checkboxTitle}}</span>' +
				'</span>'
			],
			controller: [ function() {

				var vm = this;

				vm.checkbox_click = function() {
					if ( !vm.checkboxDisabled ) {
						vm.checkboxClick();
					}
				};

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'ngClickOut', [
	'$document',
	function( $document ) {
		return {
			restrict: 'A',
			controllerAs: 'djnco',
			controller: [ '$scope', '$element', '$attrs', function( $scope, $element, $attrs ) {

				var vm = this;

				$element.on( 'click', function( event ) {
					event.stopPropagation();
				} );

				$document.on( 'click', function() {
					$scope.$apply( $attrs.ngClickOut );
				} );

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailContentEditable', [
	function() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				inputValue: '=',
				before: '@',
				after: '@',
				placeHolder: '@',
				whenEnter: '&'
			},
			controllerAs: 'djce',
			template: [
				'<span>' +
				'	<span ng-show="!djce.display_editable" ng-click="djce.show_editable()">' +
				'		{{djce.before}}' +
				'		<span class="jackmail_content_editable jackmail_content_editable_empty">{{djce.placeHolder}}</span>' +
				'		{{djce.after}}' +
				'	</span>' +
				'	<span ng-show="djce.display_editable">' +
				'		{{djce.before}}' +
				'		<span class="jackmail_content_editable_span jackmail_content_editable jackmail_content_editable_not_empty" contenteditable="true" ng-click="djce.focus( $event )" ng-keydown="djce.key_down( $event )" ng-keyup="djce.key_up( $event )" ng-blur="djce.blur()"></span>' +
				'		{{djce.after}}' +
				'	</span>' +
				'	<input type="text" class="jackmail_content_editable_blur"/>' +
				'</span>'
			],
			controller: [ '$element', '$scope', '$timeout', function( $element, $scope, $timeout ) {

				var vm = this;

				vm.display_editable = true;

				vm.current_input_value = '';

				$scope.$watch( 'djce.placeHolder', function( new_value ) {
					if ( new_value !== undefined ) {
						vm.display_editable = false;
					}
				} );

				vm.show_editable = function() {
					vm.display_editable = true;
					$timeout( function() {
						$element.find( '.jackmail_content_editable_span' ).focus();
					} );
				};

				vm.hide_editable = function() {
					vm.display_editable = false;
				};

				angular.element( '.jackmail_content_editable_span' ).on( 'dragover drop', function( event ) {
					event.preventDefault();
				} );

				$scope.$watch( 'djce.inputValue', function( new_value ) {
					if ( new_value !== undefined ) {
						if ( vm.inputValue !== undefined ) {
							if ( vm.inputValue !== '' ) {
								vm.display_editable = true;
								if ( $element.find( '.jackmail_content_editable_span' ).html() !== vm.inputValue ) {
									$element.find( '.jackmail_content_editable_span' ).html( vm.inputValue );
								}
							}
						}
					}
				} );

				vm.focus = function( $event ) {
					vm.current_input_value = vm.inputValue;
					$element.find( '.jackmail_content_editable_span' ).blur();
					$element.find( '.jackmail_content_editable_span' ).focus();
				};

				vm.key_down = function( $event ) {
					if ( $event.keyCode === 13 ) {
						$event.preventDefault();
						$element.find( '.jackmail_content_editable_span' ).blur();
						$element.find( '.jackmail_content_editable_blur' ).focus();
					}
				};

				vm.blur = function() {
					if ( vm.inputValue === '' ) {
						vm.hide_editable();
					}
					if ( vm.current_input_value !== vm.inputValue ) {
						vm.whenEnter();
					}
				};

				vm.key_up = function( $event ) {
					if ( $event.keyCode !== 9 ) {
						vm.inputValue = $element.find( '.jackmail_content_editable_span' ).html();
					}
				};

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailDropdownButtonVisible', [
	function() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				spanTitle: '@'
			},
			controllerAs: 'djdbv',
			template: [
				'<span class="jackmail_dropdown_button">' +
				'	{{djdbv.spanTitle}}' +
				'	<span class="dashicons dashicons-arrow-down-alt2"></span>' +
				'</span>'
			],
			controller: [ function() {

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailDropdownButton', [
	'$rootScope',
	function() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				buttonValue: '@',
				buttonClass: '@',
				buttonDisabled: '@',
				dropdownLeft: '@',

				titleFile: '@',
				titleFileEvent: '<',

				titlesClicksArray: '<',
				titlesClicksArrayEvent: '&',

				titlesClicksJson: '<',
				titlesClicksJsonEvent: '&',
				titlesClicksJsonHideCheckbox: '@',

				titlesClicksGrid: '<',
				titlesClicksGridChecked: '<',
				titlesClicksGridEvent: '&',
				titleClicksGridAdd: '<',
				titleClicksGridAddEvent: '&',
				titlesClicksGridRepeatFilter: '<'
			},
			controllerAs: 'djdb',
			template: [
				'<div class="jackmail_dropdown_button_container {{djdb.buttonClass}} {{djdb.dropdownLeft ? \'jackmail_dropdown_button_container_left\' : \'\'}} {{djdb.buttonValue ? \'\' : \'jackmail_dropdown_small_button\'}}"' +
				'	ng-class="djdb.buttonDisabled ? \'jackmail_dropdown_button_container_disabled\' : \'\'"' +
				'	ng-mouseleave="djdb.hide_button()">' +
				'	<span ng-show="djdb.buttonValue" ng-click="djdb.display_or_hide_button()" span-title="{{djdb.buttonValue}}" jackmail-dropdown-button-visible></span>' +
				'	<span ng-hide="djdb.buttonValue" ng-click="djdb.display_or_hide_button()" class="jackmail_lists_more"></span>' +
				'	<div ng-show="djdb.show_button" ng-click="!djdb.titlesClicksGrid && !djdb.titlesClicksJson ? djdb.hide_button() : \'\'">' +
				'		<div class="jackmail_dropdown_button_border_container">' +
				'			<span class="jackmail_dropdown_button_border_top"></span>' +
				'			<span class="jackmail_dropdown_button_border_top2"></span>' +
				'		</div>' +
				'		<div class="jackmail_dropdown_button_content">' +
				'			<span ng-repeat="( key, title_click ) in djdb.titlesClicksArray track by $index" class="jackmail_dropdown_button_click"' +
				'				ng-click="djdb.titlesClicksArrayEvent( { \'key\': key, \'title\': title_click } )">' +
				'				{{djdb.buttonDisabled}}{{title_click}}' +
				'			</span>' +
				'			<span ng-repeat="( key, title_click ) in djdb.titlesClicksJson track by $index" class="jackmail_dropdown_button_click"' +
				'				ng-click="djdb.titlesClicksJsonEvent( { \'key\': key, \'checked\': title_click.checked } );djdb.close_if_needed()">' +
				'				<span ng-hide="djdb.titlesClicksJsonHideCheckbox" class="jackmail_dropdown_button_select"' +
				'					jackmail-checkbox="title_click.checked" checkbox-title="{{title_click.name}}">' +
				'				</span>' +
				'				{{djdb.titlesClicksJsonHideCheckbox ? title_click.name : \'\'}}' +
				'			</span>' +
				'			<span ng-hide="djdb.titlesClicksGridRepeatFilter ? ( djdb.titlesClicksGridRepeatFilter( key ) ) : \'\'"' +
				'				ng-repeat="( key, title_click ) in djdb.titlesClicksGrid track by $index"' +
				'				ng-click="djdb.titlesClicksGridEvent( { \'key\': key } )" class="jackmail_dropdown_button_click">' +
				'				<span class="jackmail_dropdown_button_select" jackmail-checkbox="djdb.titlesClicksGridChecked[ key ]"' +
				'					checkbox-title="{{title_click.name ? title_click.name : title_click}}">' +
				'				</span>' +
				'			</span>' +
				'			<span ng-if="djdb.titleClicksGridAdd && djdb.titleClicksGridAddEvent"' +
				'				ng-click="djdb.titleClicksGridAddEvent()" class="jackmail_dropdown_button_click">' +
				'				{{$root.translations.add_a_column}}' +
				'			</span>' +
				'			<span class="jackmail_dropdown_button_file" ng-if="djdb.titleFile">' +
				'				{{djdb.titleFile}}<input onchange="angular.element( this ).scope().djdb.titleFileEvent( event )" type="file"/>' +
				'			</span>' +
				'		</div>' +
				'	</div>' +
				'</div>'
			],
			controller: [ function() {

				var vm = this;

				vm.show_button = false;

				vm.hide_button = function() {
					vm.show_button = false;
				};

				vm.display_or_hide_button = function() {
					if ( !vm.buttonDisabled ) {
						vm.show_button = !vm.show_button;
					}
				};

				vm.close_if_needed = function() {
					if ( vm.titlesClicksJsonHideCheckbox ) {
						vm.hide_button();
					}
				};

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'ngEchap', [
	function() {
		return function( scope, element, attrs ) {
			element.on( 'keydown', function( event ) {
				if ( event.keyCode === 27 ) {
					scope.$apply( function() {
						scope.$eval( attrs.ngEchap );
					} );
					event.preventDefault();
				}
			} );
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'ngEnterUpDown', [
	function() {
		return function( $scope, $element, $attrs ) {
			$element.on( 'keydown', function( $event ) {
				if ( $event.keyCode === 13 || $event.keyCode === 40 || $event.keyCode === 38 ) {
					$scope.$apply( function() {
						if ( $event.keyCode === 13 ) {
							$scope.$eval( $attrs.ngEnter );
						}
						else if ( $event.keyCode === 40 || $event.keyCode === 38 ) {
							var current_column = $element.parent().index();
							var curent_row = $element.closest( 'tr' )[ 0 ].rowIndex;
							if ( $event.keyCode === 40 ) {
								curent_row++;
							}
							else if ( $event.keyCode === 38 ) {
								curent_row--;
							}
							if ( curent_row >= 0 ) {
								$element.parent().parent().parent().find( 'tr:eq( ' + curent_row + ' ) td:eq( ' + current_column + ' ) input' ).focus();
							}
						}
					} );
					$event.preventDefault();
				}
			} );
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'ngEnter', [
	function() {
		return function( scope, element, attrs ) {
			element.on( 'keydown', function( event ) {
				if ( event.keyCode === 13 ) {
					scope.$apply( function() {
						scope.$eval( attrs.ngEnter );
					} );
					event.preventDefault();
				}
			} );
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'gridScroll', [
	'$rootScope',
	function( $rootScope ) {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				gridScroll: '<',
				gridTotal: '<',
				gridLoad: '<'
			},
			controllerAs: 'djgs',
			controller: [ '$element', function( $element ) {

				var vm = this;

				var raw = $element[ 0 ];
				$element.bind( 'scroll', function() {
					var nb_lines_grid = vm.gridScroll.nb_lines_grid;
					var nb_lines_total = vm.gridTotal;
					if ( raw.scrollTop + raw.offsetHeight >= raw.scrollHeight ) {
						if ( nb_lines_grid < nb_lines_total ) {
							var load_interval = vm.gridScroll.load_interval;
							if ( nb_lines_grid + load_interval < nb_lines_total ) {
								nb_lines_grid += load_interval;
							}
							else {
								nb_lines_grid = nb_lines_total;
							}
							vm.gridScroll.set_nb_lines_grid( nb_lines_grid );
							vm.gridScroll.increase_begin();
							vm.gridLoad();
							$rootScope.$apply();
						}
					}
				} );

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailGridSearch', [
	'$timeout', '$rootScope',
	function( $timeout, $rootScope ) {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				'searchTitle': '@',
				'jackmailAction': '<'
			},
			controllerAs: 'djgs',
			template: [
				'<div class="jackmail_grid_search_container">' +
				'	<span class="jackmail_grid_search_input_container ng-hide-animate" ng-show="djgs.display_input">' +
				'		<input class="jackmail_grid_search_input" ng-change="djgs.hide_list_search()" ng-blur="djgs.blur_search()" ng-model="djgs.list_search"' +
				'			ng-model-options="{ \'updateOn\': \'default blur\', \'debounce\': { \'default\': 1000, \'blur\': 0 } }" type="text"' +
				'			placeholder="{{djgs.searchTitle ? djgs.searchTitle : $root.translations.search}}"/>' +
				'		<span class="dashicons dashicons-search"></span>' +
				'	</span>' +
				'	<input ng-click="djgs.show_hide_list_search()" ng-hide="djgs.display_input" class="jackmail_search_button jackmail_grid_search_show_button ng-hide-animate"' +
				'		type="button" value="{{djgs.searchTitle ? djgs.searchTitle : $root.translations.search}}"/>' +
				'</div>'
			],
			controller: [ function() {

				var vm = this;

				vm.display_input = false;

				vm.list_search = '';

				vm.show_hide_list_search = function() {
					vm.display_input = !vm.display_input;
					if ( vm.display_input ) {
						$timeout( function() {
							angular.element( '.jackmail input.jackmail_grid_search_input' ).focus();
						}, 100 );
					}
				};

				vm.hide_list_search = function() {
					if ( vm.list_search === '' ) {
						vm.display_input = false;
					}
					$rootScope.display_success( $rootScope.translations.searching );
					vm.jackmailAction( vm.list_search );
				};

				vm.blur_search = function() {
					$timeout( function() {
						if ( vm.list_search === '' ) {
							vm.display_input = false;
						}
					}, 1000 );
				};

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailHeaderMenu', [
	function() {
		return {
			restrict: 'A',
			controllerAs: 'djhm',
			template: [
				'<div class="jackmail_header_button_menu jackmail_dropdown_button_container jackmail_dropdown_button_left_container" ng-mouseleave="djhm.hide_menu_button()">' +
				'	<span ng-click="djhm.display_or_hide_menu_button()" class="dashicons dashicons-menu"></span>' +
				'	<span class="jackmail_header_logo_container">' +
				'		<span ng-click="djhm.display_or_hide_menu_button()" class="jackmail_header_logo"></span>' +
				'	</span>' +
				'	<div ng-show="djhm.display_menu_button">' +
				'		<div class="jackmail_dropdown_button_border_container">' +
				'			<span class="jackmail_dropdown_button_border_top"></span>' +
				'			<span class="jackmail_dropdown_button_border_top2"></span>' +
				'		</div>' +
				'		<div>' +
				'			<span ng-click="$root.change_page( \'campaigns\' )" class="jackmail_dropdown_button_click">{{$root.translations.campaigns}}</span>' +
				'			<span ng-click="$root.change_page( \'lists\' )" class="jackmail_dropdown_button_click">{{$root.translations.lists}}</span>' +
				'			<span ng-click="$root.change_page( \'templates\' )" class="jackmail_dropdown_button_click">{{$root.translations.templates}}</span>' +
				'			<span ng-click="$root.change_page( \'statistics\' )" class="jackmail_dropdown_button_click">{{$root.translations.statistics}}</span>' +
				'			<span ng-click="$root.change_page( \'settings\' )" class="jackmail_dropdown_button_click">{{$root.translations.settings}}</span>' +
				'		</div>' +
				'	</div>' +
				'</div>'
			],
			controller: [ function() {

				var vm = this;

				vm.display_menu_button = false;

				vm.display_or_hide_menu_button = function() {
					vm.display_menu_button = !vm.display_menu_button;
				};

				vm.hide_menu_button = function() {
					vm.display_menu_button = false;
				};

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailInputEdit', [
	'$timeout',
	function( $timeout ) {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				inputValue: '=',
				notEditable: '@',
				whenEnter: '&',
				isDeletable: '@',
				whenDelete: '&'
			},
			controllerAs: 'djie',
			template: [
				'<span class="jackmail_input_edit_container">' +
				'	<span ng-hide="djie.edit" class="jackmail_input_edit_text">{{djie.inputValue}}</span>' +
				'	<span ng-show="djie.edit" ng-click="$event.stopPropagation()">' +
				'		<input class="jackmail_input_transparent" type="text" ng-enter="djie.edit_name()" ng-blur="djie.edit_name()" ng-model="djie.inputValue" ng-trim="false"/>' +
				'	</span>' +
				'	<span ng-hide="djie.edit || djie.notEditable" ng-click="djie.display_hide_edit_list_name( $event )" title="{{$root.translations.edit}}" class="dashicons dashicons-edit"></span>' +
				'	<span ng-hide="djie.edit || !djie.isDeletable" ng-click="$event.stopPropagation()" title="{{$root.translations.delete}}" class="jackmail_display_delete_value">' +
				'		<span class="dashicons dashicons-no"></span>' +
				'		<span class="jackmail_display_delete_confirmation jackmail_display_delete_confirmation_left">' +
				'			<span ng-click="djie.delete_name( $event )" class="jackmail_display_delete_confirmation_text">{{$root.translations.confirm}}</span>' +
				'			<span class="jackmail_display_delete_confirmation_popin">' +
				'				<span class="jackmail_display_delete_confirmation_popin_border">' +
				'					<span></span>' +
				'					<span></span>' +
				'				</span>' +
				'			</span>' +
				'		</span>' +
				'	</span>' +
				'</span>'
			],
			controller: [ '$element', function( $element ) {

				var vm = this;

				vm.edit = false;

				var focused_text = '';

				vm.display_hide_edit_list_name = function( $event ) {
					$event.stopPropagation();

					if ( vm.edit ) {
						vm.edit = false;
					}
					else {
						vm.edit = true;
						$timeout( function() {
							$element.find( 'input' ).focus();
							focused_text = vm.inputValue;
						} );
					}
				};

				vm.edit_name = function() {
					$timeout( function() {
						if ( $element.find( 'input' ).is( ':focus' ) ) {
							$element.find( 'input' ).blur();
						}
						else {
							vm.edit = false;
							if ( focused_text !== vm.inputValue || vm.inputValue === '' ) {
								vm.whenEnter();
							}
						}
					} );
				};

				vm.delete_name = function( $event ) {
					$event.stopPropagation();
					vm.whenDelete();
				};

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailInputInterval', [
	'$timeout',
	function( $timeout ) {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				'inputValue': '=',
				'jackmailAction': '<'
			},
			controllerAs: 'djii',
			template: [
				'<div>' +
				'	<input ng-change="djii.input_change()" ng-model="djii.inputValue" ng-model-options="{ \'updateOn\': \'default blur\', \'debounce\': { \'default\': 1000, \'blur\': 0 } }" type="text"/>' +
				'</div>'
			],
			controller: [ function() {

				var vm = this;

				vm.input_change = function() {
					$timeout( function() {
						vm.jackmailAction();
					} );
				};

			} ]
		};

	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailMultipleCalendar', [
	'$rootScope', '$timeout', '$filter',
	function( $rootScope, $timeout, $filter ) {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				onConfirm: '<',
				jackmailOption: '@',
				selectedDate1: '@',
				selectedDate2: '@',
				selectedDate3: '@',
				selectedDate4: '@',
				jackmailSimpleCalendar: '@',
				
				jackmailSimpleCalendarTwo: '@',
				jackmailPosition: '@',
				calendarCompare: '@',
				calendarCompareHidden: '@',
				refreshDate: '<',
				jackmailRefresh: '@'
			},
			controllerAs: 'djmc',
			template: [
				'<div>' +
				'	<div class="jackmail_campaign_calendar" ng-click-out="djmc.hide_calendar()" ng-class="djmc.calendarCompare && djmc.compare ? \'jackmail_campaign_calendar_compare\' : \'\'">' +
				'		<div ng-click="djmc.display_hide_calendar()" class="jackmail_campaign_calendar_display">' +
				'			<span ng-repeat="( key, date ) in [ djmc.date1, djmc.date2, djmc.date3, djmc.date4 ] track by $index" ng-show="djmc.selected_period === 0" ng-if="( key === 0 && djmc.selectedDate1 ) || ( key === 1 && ( !djmc.jackmailSimpleCalendar || djmc.jackmailSimpleCalendarTwoDisabled ) ) || ( ( key === 2 || key === 3 ) && djmc.calendarCompare && djmc.compare )">' +
				'				<span ng-show="key === 1 || key === 3"> - </span>' +
				'				<span ng-show="key === 2 && djmc.compare"> {{$root.translations.and}} </span>' +
				'				<span>' +
				'					<span>{{date.selected_day}}</span>' +
				'					<span ng-repeat="v in djmc.months track by $index" ng-show="v.value === date.selected_month">{{v.text}}</span>' +
				'					<span>{{date.selected_year}}</span>' +
				'				</span>' +
				'				<span ng-hide="djmc.jackmailOption === \'day\'">' +
				'					-' +
				'					<span ng-repeat="v in djmc.hours track by $index" ng-show="v.value === date.selected_hour">{{v.text}}</span> :' +
				'					<span ng-repeat="v in djmc.minutes track by $index" ng-show="v.value === date.selected_minute">{{v.text}}</span>' +
				'				</span>' +
				'			</span>' +
				'			<span ng-hide="djmc.selected_period === 0">' +
				'				<span ng-repeat="period in djmc.periods | filter: { value : djmc.selected_period } : true track by $index">{{period.name}}</span>' +
				'				<span ng-show="djmc.compare"> {{$root.translations.comparison}}</span>' +
				'			</span>' +
				'		</div>' +
				'		<div class="jackmail_calendar_dropdown jackmail_calendar_dropdown_position_{{djmc.jackmailPosition}}{{djmc.jackmailSimpleCalendar ? \' jackmail_calendar_simple\' : \'\'}}{{djmc.calendarCompare ? \' jackmail_calendar_compare\' : \'\'}}" ng-show="djmc.show_calendar">' +
				'			<div ng-class="!djmc.jackmailSimpleCalendar ? \'jackmail_calendar_multiple_left\' : \'\'">' +
				'				<div class="jackmail_calendar_period_title" ng-show="djmc.calendarCompare && djmc.compare">' +
				'					{{$root.translations.calendar_period1}}' +
				'				</div>' +
				'				<div ng-repeat="( key, date ) in [ djmc.display_date1, djmc.display_date2, djmc.display_date3, djmc.display_date4 ] track by $index" ng-if="( key === 0 && djmc.selectedDate1 ) || ( key === 1 ) || ( ( key === 2 || key === 3 ) && djmc.calendarCompare && djmc.compare )">' +
				'					<div class="jackmail_calendar_content">' +
				'						<div class="jackmail_calendar_months">' +
				'							<span ng-show="key === 0 || key === 2" ng-click="djmc.previous_month( \'display_date\' + ( key + 1 ) )" class="jackmail_calendar_month_previous"><</span>' +
				'							<span ng-repeat="v in djmc.months track by $index" ng-show="v.value === date.selected_month">{{v.text}}</span> ' +
				'							<span>{{date.selected_year}}</span>' +
				'							<span ng-click="djmc.next_month( \'display_date\' + (key + 1) )" ng-show="key === 1 || key === 3" class="jackmail_calendar_month_next">></span>' +
				'						</div>' +
				'						<div class="jackmail_calendar_week">' +
				'							<span ng-repeat="v in djmc.week track by $index">{{v.text}}</span>' +
				'						</div>' +
				'						<div class="jackmail_campaign_calendar_days">' +
				'							<div ng-repeat="week_day in date.begin_week_day track by $index" class="jackmail_campaign_calendar_day jackmail_campaign_calendar_week_day">00</div>' +
				'							<div ng-repeat="day in djmc.days track by $index" ng-click="djmc.select_day( key, day, date )"' +
				'								ng-class="djmc.show_calendar ? djmc.day_class( day, \'date\' + ( key + 1 ), date ) : \'\'" ' +
				'								ng-show="day.value <= date.nb_days && day.value !== \'\'">' +
				'								{{day.text}}' +
				'							</div>' +
				'						</div>' +
				'						<div class="jackmail_campaign_calendar_time" ng-hide="djmc.jackmailOption === \'day\' || key !== 0">' +
				'							<select ng-change="djmc.change_send_option_date_hour( \'date\' + (key + 1), date.selected_hour, date.selected_minute )" ng-options="hour.value as hour.text for hour in djmc.hours" ng-model="date.selected_hour"></select>' +
				'							<select ng-change="djmc.change_send_option_date_hour( \'date\' + (key + 1), date.selected_hour, date.selected_minute )" ng-options="minute.value as minute.text for minute in djmc.minutes" ng-model="date.selected_minute"></select>' +
				'						</div>' +
				'					</div>' +
				'					<div ng-if="djmc.calendarCompare && key === 1">' +
				'						<div class="jackmail_calendar_period_title" ng-show="djmc.calendarCompare && djmc.compare">' +
				'							{{$root.translations.calendar_period2}}' +
				'						</div>' +
				'					</div>' +
				'				</div>' +
				'			</div>' +
				'			<div ng-if="!djmc.jackmailSimpleCalendar" class="jackmail_calendar_multiple_right" ng-class="djmc.calendarCompare && djmc.compare ? \'jackmail_calendar_multiple_right_comparing\' : \'\'">' +
				'				<div>{{$root.translations.period}}</div>' +
				'				<div class="jackmail_campaign_calendar_periods" ng-class="djmc.calendarCompare ? \'jackmail_campaign_calendar_periods_compare\' : \'\'">' +
				'					<div ng-repeat="period in djmc.periods track by $index">' +
				'						<div ng-click="djmc.select_period( period.value )" ng-class="djmc.selected_period === period.value ? \'jackmail_campaign_calendar_selected_period\' : \'\'">{{period.name}}</div>' +
				'						</div>' +
				'				</div>' +
				'				<div class="jackmail_calendar_multiple_right_dates">' +
				'					<span ng-click="djmc.change_current_period( 1, key )" ng-repeat="( key, date ) in [ djmc.date1, djmc.date2 ] track by $index" ng-class="djmc.current_period.period1 === key ? \'jackmail_calendar_multiple_right_date_selected\' : \'\'">' +
				'						<span>{{date.selected_day}}</span>' +
				'						<span ng-repeat="v in djmc.months track by $index" ng-show="v.value === date.selected_month">{{v.text}}</span>' +
				'						<span>{{date.selected_year}}</span>' +
				'					</span>' +
				'				</div>' +
				'				<div ng-if="djmc.calendarCompare && !djmc.calendarCompareHidden" class="jackmail_statistics_compare">' +
				'					<span jackmail-checkbox="djmc.compare" checkbox-title="{{$root.translations.compare}}" checkbox-click="djmc.show_hide_compare()"></span>' +
				'				</div>' +
				'				<div class="jackmail_calendar_multiple_right_dates" ng-show="djmc.calendarCompare && djmc.compare">' +
				'					<span ng-click="djmc.change_current_period( 2, key )" ng-repeat="( key, date ) in [ djmc.date3, djmc.date4 ] track by $index" ng-class="djmc.current_period.period2 === key ? \'jackmail_calendar_multiple_right_date_selected\' : \'\'">' +
				'						<span>{{date.selected_day}}</span>' +
				'						<span ng-repeat="v in djmc.months track by $index" ng-show="v.value === date.selected_month">{{v.text}}</span>' +
				'						<span>{{date.selected_year}}</span>' +
				'					</span>' +
				'				</div>' +
				'				<div class="jackmail_calendar_multiple_buttons">' +
				'					<span ng-click="djmc.hide_calendar()">{{$root.translations.apply}}</span>' +
				'				</div>' +
				'			</div>' +
				'		</div>' +
				'		<div ng-click="djmc.display_hide_calendar()" class="dashicons dashicons-calendar-alt"></div>' +
				'	</div>' +
				'	<div class="jackmail_campaign_calendar_period" ng-if="djmc.jackmailSimpleCalendarTwo">' +
				'		<p>{{$root.translations.select_a_period_to_send_out}}</p>' +
				'		<div class="jackmail_dropdown_button_container jackmail_dropdown_button_container_button jackmail_grid_columns_button" ng-mouseleave="djmc.hide_period_selection()">' +
				'			<span ng-click="djmc.show_hide_period_selection()" span-title="{{djmc.calendar_simple_calendar_two_dates_period}} {{djmc.calendar_simple_calendar_two_dates_period === 1 ? $root.translations.day : $root.translations.days}}" jackmail-dropdown-button-visible></span>' +
				'			<div ng-show="djmc.display_period_selection" ng-click="djmc.hide_period_selection()">' +
				'				<div class="jackmail_dropdown_button_border_container">' +
				'					<span class="jackmail_dropdown_button_border_top"></span>' +
				'					<span class="jackmail_dropdown_button_border_top2"></span>' +
				'				</div>' +
				'				<div class="jackmail_dropdown_button_content">' +
				'					<span ng-repeat="period in djmc.calendar_simple_calendar_two_dates_periods track by $index" ng-click="djmc.change_calendar_simple_calendar_two_dates_periods( period.value )" class="jackmail_dropdown_button_click">' +
				'						{{period.name}}' +
				'					</span>' +
				'				</div>' +
				'			</div>' +
				'		</div>' +
				'	</div>' +
				'</div>'
			],
			controller: [ '$scope', function( scope ) {

				var vm = this;

				vm.show_hide_period_selection = function() {
					vm.display_period_selection = !vm.display_period_selection;
				};

				vm.hide_period_selection = function() {
					vm.display_period_selection = false;
				};

				vm.calendar_simple_calendar_two_dates_periods = [
					{ 'value': '1', 'name': '1 ' + $rootScope.translations.day },
					{ 'value': '2', 'name': '2 ' + $rootScope.translations.days },
					{ 'value': '3', 'name': '3 ' + $rootScope.translations.days },
					{ 'value': '4', 'name': '4 ' + $rootScope.translations.days },
					{ 'value': '5', 'name': '5 ' + $rootScope.translations.days },
					{ 'value': '6', 'name': '6 ' + $rootScope.translations.days },
					{ 'value': '7', 'name': '7 ' + $rootScope.translations.days }
				];

				vm.periods = [
					{ 'value': '1', 'name': $rootScope.translations.today },
					{ 'value': '2', 'name': $rootScope.translations.yesterday },
					{ 'value': '3', 'name': $rootScope.translations.this_week },
					{ 'value': '4', 'name': $rootScope.translations.the_last_7_days },
					{ 'value': '5', 'name': $rootScope.translations.last_week_mon_fri },
					{ 'value': '6', 'name': $rootScope.translations.last_week_mon_sun },
					{ 'value': '7', 'name': $rootScope.translations.the_last_14_days },
					{ 'value': '8', 'name': $rootScope.translations.this_month },
					{ 'value': '9', 'name': $rootScope.translations.last_month },
					{ 'value': '10', 'name': $rootScope.translations.the_last_30_days },
					{ 'value': '11', 'name': $rootScope.translations.the_last_90_days },
					{ 'value': '12', 'name': $rootScope.translations.the_last_180_days }
				];

				var today;
				var actual_date;

				function init_vars() {

					var today_date = $filter( 'formatedDate' )( $rootScope.settings.current_time, 'gmt_to_timezone', 'sql' );

					var today_day = today_date.substring( 8, 10 );
					var today_month = today_date.substring( 5, 7 );
					var today_year = today_date.substring( 0, 4 );
					var today_hour = today_date.substring( 11, 13 );
					var today_minute = today_date.substring( 14, 16 );
					today = new Date( today_year, today_month - 1, today_day, today_hour, today_minute, 0, 0 );
					
					var today_day = today.getDate();
					if ( today_day < 10 ) {
						today_day = '0' + today_day;
					}
					var today_month = today.getMonth() + 1;
					if ( today_month < 10 ) {
						today_month = '0' + today_month;
					}
					vm.today = {
						'day': today_day.toString(),
						'month': today_month.toString(),
						'year': today.getFullYear().toString()
					};
					actual_date = new Date( today.getFullYear(), today.getMonth(), today.getDate(), 0, 0, 0, 0 );

					vm.date1 = {};
					vm.date2 = {};
					vm.date3 = {};
					vm.date4 = {};
					vm.display_date1 = {};
					vm.display_date2 = {};
					vm.display_date3 = {};
					vm.display_date4 = {};
					vm.display_period_selection = false;
					vm.calendar_simple_calendar_two_dates_period = -1;
					vm.show_calendar = false;
					vm.compare = false;
					vm.selected_period = 0;
					vm.need_refreshing = true;
					vm.need_refreshing = true;
					vm.current_period = {
						'period1': 0,
						'period2': 0
					};

				}

				init_vars();

				vm.init = function() {
					init_vars();
					change_selected_date( 'date1' );
					var date1 = new Date( vm.date1.selected_year, vm.date1.selected_month - 1, vm.date1.selected_day, 0, 0, 0, 0 );
					if ( ( vm.jackmailSimpleCalendar && date1 < actual_date ) ) {
						vm.selectedDate1 = '0000-00-00 00:00:00';
						change_selected_date( 'date1' );
						if ( vm.jackmailSimpleCalendarTwo ) {
							vm.selectedDate2 = '0000-00-00 00:00:00';
						}
					}
					if ( vm.jackmailSimpleCalendar ) {
						vm.display_date1 = angular.copy( vm.date1 );
						var date2 = new Date( vm.display_date1.selected_year, vm.display_date1.selected_month, 1, 0, 0, 0, 0 );
						var selected_day2 = date2.getDate();
						if ( selected_day2 < 10 ) {
							selected_day2 = '0' + selected_day2;
						}
						var selected_month2 = date2.getMonth() + 1;
						if ( selected_month2 < 10 ) {
							selected_month2 = '0' + selected_month2;
						}
						vm.display_date2 = {
							'selected_day': selected_day2.toString(),
							'selected_month': selected_month2.toString(),
							'selected_year': date2.getFullYear().toString()
						};
						nb_days_in_month( 'display_date1' );
						nb_days_in_month( 'display_date2' );
						if ( vm.jackmailSimpleCalendarTwo ) {
							change_selected_date( 'date2' );
							var date1 = new Date( vm.date1.selected_year, vm.date1.selected_month - 1, vm.date1.selected_day, 0, 0, 0, 0 ).getTime();
							var date2 = new Date( vm.date2.selected_year, vm.date2.selected_month - 1, vm.date2.selected_day, 0, 0, 0, 0 ).getTime();
							vm.calendar_simple_calendar_two_dates_period = Math.round( ( date2 - date1 ) / 86400000 );
						}
					}
					else {
						change_selected_date( 'date2' );
						vm.display_date2 = angular.copy( vm.date2 );
						nb_days_in_month( 'display_date2' );
						var display_date1 = new Date( vm.date2.selected_year, vm.date2.selected_month - 1, vm.date2.selected_day, 0, 0, 0, 0 );
						display_date1.setMonth( display_date1.getMonth() - 1 );
						var selected_day1 = display_date1.getDate();
						if ( selected_day1 < 10 ) {
							selected_day1 = '0' + selected_day1;
						}
						var selected_month1 = display_date1.getMonth() + 1;
						if ( selected_month1 < 10 ) {
							selected_month1 = '0' + selected_month1;
						}
						vm.display_date1 = {
							'selected_day': selected_day1.toString(),
							'selected_month': selected_month1.toString(),
							'selected_year': display_date1.getFullYear().toString()
						};
						nb_days_in_month( 'display_date1' );

						if ( vm.calendarCompare ) {

							change_selected_date( 'date4' );
							vm.display_date4 = angular.copy( vm.date4 );
							nb_days_in_month( 'display_date4' );

							change_selected_date( 'date3' );
							var display_date3 = new Date( vm.date4.selected_year, vm.date4.selected_month - 1, vm.date4.selected_day, 0, 0, 0, 0 );
							display_date3.setMonth( display_date3.getMonth() - 1 );
							var selected_day3 = display_date3.getDate();
							if ( selected_day3 < 10 ) {
								selected_day3 = '0' + selected_day3;
							}
							var selected_month3 = display_date3.getMonth() + 1;
							if ( selected_month3 < 10 ) {
								selected_month3 = '0' + selected_month3;
							}
							vm.display_date3 = {
								'selected_day': selected_day3.toString(),
								'selected_month': selected_month3.toString(),
								'selected_year': display_date3.getFullYear().toString()
							};
							nb_days_in_month( 'display_date3' );

						}
					}
					vm.change_send_option_date_all();
				}

				$timeout( function() {
					scope.$watch( 'djmc.jackmailRefresh', function( newValue ) {
						if ( newValue ) {
							vm.init();
						}
					} );
				} );

				vm.change_current_period = function( period, key ) {
					vm.current_period[ 'period' + period ] = key;
				};

				vm.show_hide_compare = function() {
					vm.need_refreshing = true;
					vm.compare = !vm.compare;
				};

				vm.select_day = function( key, day, display_date ) {
					vm.need_refreshing = true;
					var selected_day = day.value;
					var selected_month = display_date.selected_month;
					var selected_year = display_date.selected_year;
					var check_date = new Date( selected_year, selected_month - 1, selected_day, 0, 0, 0, 0 );
					if ( ( !vm.jackmailSimpleCalendar && check_date > actual_date ) || ( vm.jackmailSimpleCalendar && check_date <= actual_date ) ) {
						return;
					}
					vm.selected_period = 0;
					if ( vm.jackmailSimpleCalendar ) {
						vm[ 'date1' ].selected_day = selected_day;
						vm[ 'date1' ].selected_month = selected_month;
						vm[ 'date1' ].selected_year = selected_year;
						vm.change_send_option_date_one( 'date1' );
					}
					else {
						if ( key === 0 || key === 1 ) {
							var first_date = 'date1';
							var second_date = 'date2';
							var period = 'period1';
						}
						else {
							var first_date = 'date3';
							var second_date = 'date4';
							var period = 'period2';
						}
						if ( vm.current_period[ period ] === 0 ) {
							vm[ first_date ].selected_day = selected_day;
							vm[ first_date ].selected_month = selected_month;
							vm[ first_date ].selected_year = selected_year;
							vm.change_send_option_date_one( first_date );
						}
						else {
							vm[ second_date ].selected_day = selected_day;
							vm[ second_date ].selected_month = selected_month;
							vm[ second_date ].selected_year = selected_year;
							vm.change_send_option_date_one( second_date );
						}
						var check_date1 = new Date( vm[ first_date ].selected_year, vm[ first_date ].selected_month - 1, vm[ first_date ].selected_day, 0, 0, 0, 0 );
						var check_date2 = new Date( vm[ second_date ].selected_year, vm[ second_date ].selected_month - 1, vm[ second_date ].selected_day, 0, 0, 0, 0 );
						if ( check_date1 > check_date2 ) {
							vm[ second_date ] = angular.copy( vm[ first_date ] );
							vm.change_send_option_date_one( second_date );
						}
						if ( vm.current_period[ period ] === 0 ) {
							vm.current_period[ period ] = 1;
						}
						else {
							vm.current_period[ period ] = 0;
						}
					}
				};

				vm.select_period = function( selection ) {
					vm.need_refreshing = true;
					vm.selected_period = selection;
					var date1 = angular.copy( today );
					var date2 = angular.copy( today );
					var date3 = angular.copy( today );
					var date4 = angular.copy( today );
					if ( selection === '1' ) {
						date3.setDate( date3.getDate() - 1 );
						date4.setDate( date4.getDate() - 1 );
					}
					else if ( selection === '2' ) {
						date1.setDate( date1.getDate() - 1 );
						date2.setDate( date2.getDate() - 1 );
						date3.setDate( date3.getDate() - 2 );
						date4.setDate( date4.getDate() - 2 );
					}
					else if ( selection === '3' ) {
						var day_week = date1.getDay();
						var diff_to_begin = 0;
						if ( day_week === 0 ) {
							diff_to_begin = 6;
						}
						else {
							diff_to_begin = day_week - 1;
						}
						date1.setDate( date1.getDate() - diff_to_begin );
						date2.setDate( date2.getDate() );
						date3.setDate( date3.getDate() - diff_to_begin - 7 );
						date4.setDate( date4.getDate() - 7 );
					}
					else if ( selection === '4' ) {
						date1.setDate( date1.getDate() - 7 );
						date2.setDate( date2.getDate() );
						date3.setDate( date3.getDate() - 15 );
						date4.setDate( date4.getDate() - 8 );
					}
					else if ( selection === '5' ) {
						var day_week = date1.getDay();
						var diff_to_begin = 0;
						var diff_to_end = 0;
						if ( day_week === 0 ) {
							diff_to_begin = 6;
						}
						else {
							diff_to_begin = 6 + day_week;
						}
						diff_to_end = diff_to_begin - 4;
						date1.setDate( date1.getDate() - diff_to_begin );
						date2.setDate( date2.getDate() - diff_to_end );
						date3.setDate( date3.getDate() - diff_to_begin - 7 );
						date4.setDate( date4.getDate() - diff_to_end - 7 );
					}
					else if ( selection === '6' ) {
						var day_week = date1.getDay();
						var diff_to_begin = 0;
						var diff_to_end = 0;
						if ( day_week === 0 ) {
							diff_to_begin = 6;
						}
						else {
							diff_to_begin = 6 + day_week;
						}
						diff_to_end = diff_to_begin - 6;
						date1.setDate( date1.getDate() - diff_to_begin );
						date2.setDate( date2.getDate() - diff_to_end );
						date3.setDate( date3.getDate() - diff_to_begin - 7 );
						date4.setDate( date4.getDate() - diff_to_end - 7 );
					}
					else if ( selection === '7' ) {
						date1.setDate( date1.getDate() - 14 );
						date2.setDate( date2.getDate() );
						date3.setDate( date3.getDate() - 29 );
						date4.setDate( date4.getDate() - 15 );
					}
					else if ( selection === '8' ) {
						date1.setDate( 1 );
						date3.setDate( 1 );
						date3.setMonth( date3.getMonth() - 1 );
						date4.setMonth( date4.getMonth() - 1 );
					}
					else if ( selection === '9' ) {
						date1.setDate( 1 );
						date1.setMonth( date1.getMonth() - 1 );
						date2.setMonth( date2.getMonth() - 1 );
						date3.setDate( 1 );
						date3.setMonth( date3.getMonth() - 2 );
						date4.setMonth( date4.getMonth() - 2 );
						var nb_days1 = new Date( date1.getFullYear(), date1.getMonth(), 0 ).getDate();
						var nb_days2 = new Date( date3.getFullYear(), date3.getMonth(), 0 ).getDate();
						if ( nb_days1 < nb_days2 ) {
							date2.setDate( nb_days1 );
							date4.setDate( nb_days1 );
						}
						else {
							date2.setDate( nb_days2 );
							date4.setDate( nb_days2 );
						}
					}
					else if ( selection === '10' ) {
						date1.setDate( date1.getDate() - 30 );
						date2.setDate( date2.getDate() );
						date3.setDate( date3.getDate() - 61 );
						date4.setDate( date4.getDate() - 31 );
					}
					else if ( selection === '11' ) {
						date1.setDate( date4.getDate() - 90 );
						date2.setDate( date2.getDate() );
						date3.setDate( date3.getDate() - 181 );
						date4.setDate( date4.getDate() - 91 );
					}
					else if ( selection === '12' ) {
						date1.setDate( date1.getDate() - 180 );
						date2.setDate( date2.getDate() );
						date3.setDate( date3.getDate() - 361 );
						date4.setDate( date4.getDate() - 181 );
					}
					var selected_day1 = date1.getDate();
					if ( selected_day1 < 10 ) {
						selected_day1 = '0' + selected_day1;
					}
					var selected_month1 = date1.getMonth() + 1;
					if ( selected_month1 < 10 ) {
						selected_month1 = '0' + selected_month1;
					}
					var selected_day2 = date2.getDate();
					if ( selected_day2 < 10 ) {
						selected_day2 = '0' + selected_day2;
					}
					var selected_month2 = date2.getMonth() + 1;
					if ( selected_month2 < 10 ) {
						selected_month2 = '0' + selected_month2;
					}
					var selected_day3 = date3.getDate();
					if ( selected_day3 < 10 ) {
						selected_day3 = '0' + selected_day3;
					}
					var selected_month3 = date3.getMonth() + 1;
					if ( selected_month3 < 10 ) {
						selected_month3 = '0' + selected_month3;
					}
					var selected_day4 = date4.getDate();
					if ( selected_day4 < 10 ) {
						selected_day4 = '0' + selected_day4;
					}
					var selected_month4 = date4.getMonth() + 1;
					if ( selected_month4 < 10 ) {
						selected_month4 = '0' + selected_month4;
					}
					vm.date1 = {
						'selected_day': selected_day1.toString(),
						'selected_month': selected_month1.toString(),
						'selected_year': date1.getFullYear().toString()
					};
					vm.date2 = {
						'selected_day': selected_day2.toString(),
						'selected_month': selected_month2.toString(),
						'selected_year': date2.getFullYear().toString()
					};
					vm.date3 = {
						'selected_day': selected_day3.toString(),
						'selected_month': selected_month3.toString(),
						'selected_year': date3.getFullYear().toString()
					};
					vm.date4 = {
						'selected_day': selected_day4.toString(),
						'selected_month': selected_month4.toString(),
						'selected_year': date4.getFullYear().toString()
					};
					var display_date1 = new Date( vm.date2.selected_year, vm.date2.selected_month, 1, 0, 0, 0, 0 );
					display_date1.setMonth( display_date1.getMonth() - 1 );
					var selected_month1 = display_date1.getMonth();
					var selected_year1 = display_date1.getFullYear();
					if ( selected_month1 === 0 ) {
						selected_month1 = 12;
						selected_year1--;
					}
					if ( selected_month1 < 10 ) {
						selected_month1 = '0' + selected_month1;
					}
					var selected_month2 = vm.date2.selected_month;
					var selected_year2 = vm.date2.selected_year;
					var display_date3 = new Date( vm.date4.selected_year, vm.date4.selected_month, 1, 0, 0, 0, 0 );
					display_date3.setMonth( display_date3.getMonth() - 1 );
					var selected_month3 = display_date3.getMonth();
					var selected_year3 = display_date3.getFullYear();
					if ( selected_month3 === 0 ) {
						selected_month3 = 12;
						selected_year3--;
					}
					if ( selected_month3 < 10 ) {
						selected_month3 = '0' + selected_month3;
					}
					var selected_month4 = vm.date4.selected_month;
					var selected_year4 = vm.date4.selected_year;
					vm.display_date1.selected_month = selected_month1.toString();
					vm.display_date1.selected_year = selected_year1.toString();
					vm.display_date2.selected_month = selected_month2.toString();
					vm.display_date2.selected_year = selected_year2.toString();
					vm.display_date3.selected_month = selected_month3.toString();
					vm.display_date3.selected_year = selected_year3.toString();
					vm.display_date4.selected_month = selected_month4.toString();
					vm.display_date4.selected_year = selected_year4.toString();
					nb_days_in_month( 'display_date1' );
					nb_days_in_month( 'display_date2' );
					nb_days_in_month( 'display_date3' );
					nb_days_in_month( 'display_date4' );
				};

				vm.day_class = function( day, current_date, display_date ) {
					var date = vm[ current_date ];
					var class_name = '';
					var date_begin = '';
					var date_end = '';
					if ( current_date === 'date1' || current_date === 'date2' ) {
						date_begin = 'date1';
						date_end = 'date2';
					}
					else if ( current_date === 'date3' || current_date === 'date4' ) {
						date_begin = 'date3';
						date_end = 'date4';
					}
					if ( vm.show_calendar ) {
						if ( !vm.jackmailSimpleCalendar && vm.today.day < day.value && vm.today.month === display_date.selected_month && vm.today.year === display_date.selected_year ) {
							class_name += ' jackmail_campaign_calendar_day_disabled';
						}
						else if ( vm.jackmailSimpleCalendar && vm.today.day > day.value && vm.today.month === display_date.selected_month && vm.today.year === display_date.selected_year ) {
							class_name += ' jackmail_campaign_calendar_day_disabled';
						}
						if ( vm.today.day === day.value && vm.today.month === display_date.selected_month && vm.today.year === display_date.selected_year ) {
							class_name += ' jackmail_bold';
						}
						if ( date.selected_month === display_date.selected_month && date.selected_year === display_date.selected_year ) {
							if ( day.value === date.selected_day ) {
								if ( vm.jackmailSimpleCalendarTwo && current_date === date_end ) {
									class_name += ' jackmail_campaign_calendar_day';
								}
								else {
									class_name += ' jackmail_campaign_calendar_day_selected';
								}
							}
							else {
								class_name += ' jackmail_campaign_calendar_day';
							}
							if ( ( !vm.jackmailSimpleCalendar ) && current_date === date_begin && day.value > date.selected_day && date.selected_day !== '' && vm[ date_end ].selected_day !== '' && day.value <= vm[ date_end ].selected_day && date.selected_month === vm[ date_end ].selected_month ) {
								if ( day.value === vm[ date_end ].selected_day ) {
									class_name += ' jackmail_campaign_calendar_day_selected';
								}
								else {
									class_name += ' jackmail_campaign_calendar_day_in';
								}
							}
							else if ( ( !vm.jackmailSimpleCalendar || vm.jackmailSimpleCalendarTwo ) && current_date === date_begin && date.selected_day !== '' && day.value > date.selected_day && ( date.selected_month < vm[ date_end ].selected_month || date.selected_year < vm[ date_end ].selected_year ) ) {
								if ( vm.jackmailSimpleCalendarTwo ) {
									class_name += ' jackmail_campaign_calendar_day';
								}
								else {
									class_name += ' jackmail_campaign_calendar_day_in';
								}
							}
							else if ( ( !vm.jackmailSimpleCalendar || vm.jackmailSimpleCalendarTwo ) && current_date === date_end && day.value < date.selected_day && date.selected_day !== '' && vm[ date_begin ].selected_day !== '' && day.value >= vm[ date_begin ].selected_day && date.selected_month === vm[ date_begin ].selected_month ) {
								if ( day.value === vm[ date_begin ].selected_day ) {
									class_name += ' jackmail_campaign_calendar_day_selected';
								}
								else if ( vm.jackmailSimpleCalendarTwo ) {
									class_name += ' jackmail_campaign_calendar_day';
								}
								else {
									class_name += ' jackmail_campaign_calendar_day_in';
								}
							}
							else if ( ( !vm.jackmailSimpleCalendar || vm.jackmailSimpleCalendarTwoDisabled ) && current_date === date_end && vm[ date_begin ].selected_day !== '' && day.value < date.selected_day && ( date.selected_month > vm[ date_begin ].selected_month || date.selected_year > vm[ date_begin ].selected_year ) ) {
								class_name += ' jackmail_campaign_calendar_day_in';
							}
						}
						else if ( vm[ date_begin ].selected_month === display_date.selected_month && vm[ date_begin ].selected_year === display_date.selected_year ) {
							if ( day.value === vm[ date_begin ].selected_day ) {
								class_name += ' jackmail_campaign_calendar_day_selected';
							}
							else {
								if ( ( !vm.jackmailSimpleCalendar ) && day.value > vm[ date_begin ].selected_day ) {
									class_name += ' jackmail_campaign_calendar_day_in';
								}
								else {
									class_name += ' jackmail_campaign_calendar_day';
								}
							}
						}
						else if ( vm[ date_end ].selected_month === display_date.selected_month && vm[ date_end ].selected_year === display_date.selected_year ) {
							if ( day.value === vm[ date_end ].selected_day ) {
								class_name += ' jackmail_campaign_calendar_day_selected';
							}
							else {
								if ( ( !vm.jackmailSimpleCalendar ) && day.value < vm[ date_end ].selected_day ) {
									class_name += ' jackmail_campaign_calendar_day_in';
								}
								else {
									class_name += ' jackmail_campaign_calendar_day';
								}
							}
						}
						else if ( ( !vm.jackmailSimpleCalendar ) && parseInt( display_date.selected_year + '' + display_date.selected_month ) > parseInt( vm[ date_begin ].selected_year + '' + vm[ date_begin ].selected_month )
							&& parseInt( display_date.selected_year + '' + display_date.selected_month ) < parseInt( vm[ date_end ].selected_year + '' + vm[ date_end ].selected_month ) ) {
							class_name += ' jackmail_campaign_calendar_day_in';
						}
						else {
							class_name += ' jackmail_campaign_calendar_day';
						}
					}
					return class_name;
				};

				vm.previous_month = function( display_date ) {
					var selected_month = parseInt( vm[ display_date ].selected_month ) - 1;
					var selected_year = parseInt( vm[ display_date ].selected_year );
					if ( selected_month === 0 ) {
						selected_month = 12;
						selected_year--;
					}
					var check_date = new Date( selected_year, selected_month, ( new Date ).getDate(), 0, 0, 0, 0 );
					if ( ( !vm.jackmailSimpleCalendar && check_date > actual_date ) || ( vm.jackmailSimpleCalendar && check_date <= actual_date ) ) {
						return;
					}
					if ( selected_month < 10 ) {
						selected_month = '0' + selected_month;
					}
					vm[ display_date ].selected_month = selected_month.toString();
					vm[ display_date ].selected_year = selected_year.toString();
					nb_days_in_month( display_date );
					if ( display_date === 'display_date1' ) {
						vm.previous_month( 'display_date2' );
					}
					if ( display_date === 'display_date3' ) {
						vm.previous_month( 'display_date4' );
					}
				};

				vm.next_month = function( display_date ) {
					var selected_month = parseInt( vm[ display_date ].selected_month ) + 1;
					var selected_year = parseInt( vm[ display_date ].selected_year );
					if ( selected_month === 13 ) {
						selected_month = 1;
						selected_year++;
					}
					var check_date = new Date( selected_year, selected_month - 1, ( new Date ).getDate(), 0, 0, 0, 0 );
					if ( ( !vm.jackmailSimpleCalendar && check_date > actual_date ) || ( vm.jackmailSimpleCalendar && check_date <= actual_date ) ) {
						return;
					}
					if ( selected_month < 10 ) {
						selected_month = '0' + selected_month;
					}
					vm[ display_date ].selected_month = selected_month.toString();
					vm[ display_date ].selected_year = selected_year.toString();
					nb_days_in_month( display_date );
					if ( display_date === 'display_date2' ) {
						vm.next_month( 'display_date1' );
					}
					if ( display_date === 'display_date4' ) {
						vm.next_month( 'display_date3' );
					}
				};

				vm.display_hide_calendar = function() {
					vm.show_calendar = !vm.show_calendar;
					if ( !vm.jackmailSimpleCalendar && !vm.show_calendar ) {
						vm.change_send_option_date_all();
						
					}
				};

				vm.hide_calendar = function() {
					vm.show_calendar = false;
					if ( !vm.jackmailSimpleCalendar ) {
						vm.change_send_option_date_all();
					}
				};

				function change_selected_date( current_date ) {
					if ( current_date === 'date1' ) {
						var selectedDate = vm.selectedDate1;
					}
					else if ( current_date === 'date2' ) {
						var selectedDate = vm.selectedDate2;
					}
					else if ( current_date === 'date3' ) {
						var selectedDate = vm.selectedDate3;
					}
					else if ( current_date === 'date4' ) {
						var selectedDate = vm.selectedDate4;
					}
					if ( selectedDate !== '0000-00-00 00:00:00' ) {
						selectedDate = $filter( 'formatedDate' )( selectedDate, 'gmt_to_timezone', 'sql' );
						vm[ current_date ].selected_day = selectedDate.substring( 8, 10 );
						vm[ current_date ].selected_month = selectedDate.substring( 5, 7 );
						vm[ current_date ].selected_year = selectedDate.substring( 0, 4 );
						vm[ current_date ].selected_hour = selectedDate.substring( 11, 13 );
						vm[ current_date ].selected_minute = selectedDate.substring( 14, 16 );
					}
					else {
						var today_date = angular.copy( today );
						if ( vm.jackmailSimpleCalendar ) {
							today_date.setMonth( today_date.getMonth() );
							
							if ( current_date === 'date2' && vm.jackmailSimpleCalendar && vm.jackmailSimpleCalendarTwo ) {
								today_date.setDate( today_date.getDate() + 1 );
							}
						}
						else if ( current_date === 'date1' ) {
							today_date.setMonth( today_date.getMonth() - 1 );
						}
						else if ( current_date === 'date3' ) {
							today_date.setMonth( today_date.getMonth() - 2 );
							today_date.setDate( today_date.getDate() - 1 );
						}
						else if ( current_date === 'date4' ) {
							today_date.setMonth( today_date.getMonth() - 1 );
							today_date.setDate( today_date.getDate() - 1 );
						}
						var actual_month = today_date.getMonth() + 1;
						if ( actual_month < 10 ) {
							actual_month = '0' + actual_month;
						}
						var actual_day = today_date.getDate();
						if ( actual_day < 10 ) {
							actual_day = '0' + actual_day;
						}
						var actual_hours = today_date.getHours();
						if ( actual_hours < 10 ) {
							actual_hours = '0' + actual_hours;
						}
						var actual_minutes = today_date.getMinutes();
						if ( actual_minutes < 10 ) {
							actual_minutes = '0' + actual_minutes;
						}
						var actual_year = today_date.getFullYear();
						vm[ current_date ].selected_day = actual_day.toString();
						vm[ current_date ].selected_month = actual_month.toString();
						vm[ current_date ].selected_year = actual_year.toString();
						vm[ current_date ].selected_hour = actual_hours.toString();
						vm[ current_date ].selected_minute = actual_minutes.toString();
					}
				}

				var value;

				if ( $rootScope.settings.language === 'fr' ) {
					vm.week = [ { 'text': 'L' }, { 'text': 'M' }, { 'text': 'M' }, { 'text': 'J' }, { 'text': 'V' }, { 'text': 'S' }, { 'text': 'D' } ];
				}
				else {
					vm.week = [ { 'text': 'M' }, { 'text': 'T' }, { 'text': 'W' }, { 'text': 'T' }, { 'text': 'F' }, { 'text': 'S' }, { 'text': 'S' } ];
				}

				var days = [];
				for ( var i = 1; i <= 31; i++ ) {
					value = i.toString();
					if ( i < 10 ) {
						value = ( '0' + i ).toString();
					}
					days.push( { 'value': value, 'text': value } );
				}
				vm.days = days;

				var months = [];
				months = [
					{ 'value': '01', 'text': $rootScope.translations.jan },
					{ 'value': '02', 'text': $rootScope.translations.feb },
					{ 'value': '03', 'text': $rootScope.translations.mar },
					{ 'value': '04', 'text': $rootScope.translations.apr },
					{ 'value': '05', 'text': $rootScope.translations.may },
					{ 'value': '06', 'text': $rootScope.translations.june },
					{ 'value': '07', 'text': $rootScope.translations.july },
					{ 'value': '08', 'text': $rootScope.translations.aug },
					{ 'value': '09', 'text': $rootScope.translations.sept },
					{ 'value': '10', 'text': $rootScope.translations.oct },
					{ 'value': '11', 'text': $rootScope.translations.nov },
					{ 'value': '12', 'text': $rootScope.translations.dec }
				];
				vm.months = months;

				var hours = [];
				hours.push( { 'value': '', 'text': $rootScope.translations.hour } );
				for ( var i = 0; i < 24; i++ ) {
					value = i.toString();
					if ( i < 10 ) {
						value = ( '0' + i ).toString();
					}
					hours.push( { 'value': value, 'text': value + 'h' } );
				}
				vm.hours = hours;

				var minutes = [];
				minutes.push( { 'value': '', 'text': $rootScope.translations.minute } );
				for ( var i = 0; i < 60; i++ ) {
					value = i.toString();
					if ( i < 10 ) {
						value = ( '0' + i ).toString();
					}
					minutes.push( { 'value': value, 'text': value + 'min' } );
				}
				vm.minutes = minutes;

				vm.change_calendar_simple_calendar_two_dates_periods = function( period ) {
					vm.calendar_simple_calendar_two_dates_period = period;
					vm.change_send_option_date_one( 'date1' );
				};

				vm.change_send_option_date_one = function( current_date ) {
					if ( vm.jackmailSimpleCalendar ) {
						var selectedDate1 = vm.change_send_option_date( current_date );
						if ( vm.jackmailSimpleCalendarTwo ) {
							var date2 = new Date( vm.date1.selected_year, vm.date1.selected_month - 1, vm.date1.selected_day, 0, 0, 0, 0 );
							date2.setDate( date2.getDate() + parseInt( vm.calendar_simple_calendar_two_dates_period ) );
							var selected_day2 = date2.getDate() + 1;
							if ( selected_day2 < 10 ) {
								selected_day2 = '0' + selected_day2;
							}
							var selected_month2 = date2.getMonth() + 1;
							if ( selected_month2 < 10 ) {
								selected_month2 = '0' + selected_month2;
							}
							vm.date2 = {
								'selected_day': selected_day2.toString(),
								'selected_month': selected_month2.toString(),
								'selected_year': date2.getFullYear()
							};
							var selectedDate2 = vm.change_send_option_date( 'date2' );
							vm.onConfirm( selectedDate1, selectedDate2 );
						}
						else {
							vm.onConfirm( selectedDate1 );
						}
					}
				};

				vm.change_send_option_date_hour = function( current_date, hour, minute ) {
					vm[ current_date ].selected_hour = hour;
					vm[ current_date ].selected_minute = minute;
					vm.need_refreshing = true;
					vm.change_send_option_date_all();
				};

				vm.change_send_option_date_all = function() {
					if ( vm.need_refreshing ) {
						var date1 = vm.change_send_option_date( 'date1' );
						if ( date1.length === 19 ) {
							if ( !vm.jackmailSimpleCalendar || ( vm.jackmailSimpleCalendar && vm.jackmailSimpleCalendarTwo ) ) {
								var date2 = vm.change_send_option_date( 'date2' );
								if ( date2.length === 19 ) {
									if ( vm.calendarCompare ) {
										var date3 = vm.change_send_option_date( 'date3' );
										var date4 = vm.change_send_option_date( 'date4' );
										if ( date3.length === 19 || date4.length === 19 ) {
											vm.onConfirm( vm.compare, date1, date2, date3, date4 );
										}
									}
									else {
										vm.onConfirm( date1, date2 );
									}
								}
							}
							else {
								vm.onConfirm( date1 );
							}
						}
						vm.need_refreshing = false;
					}
				};

				vm.change_send_option_date = function( current_date ) {
					var date = '';
					if ( vm[ current_date ].selected_day !== '' && vm[ current_date ].selected_month !== '' && vm[ current_date ].selected_year !== '' ) {
						date = vm[ current_date ].selected_year + '-' + vm[ current_date ].selected_month + '-' + vm[ current_date ].selected_day;
						if ( !vm.jackmailSimpleCalendar || ( vm.jackmailSimpleCalendar && vm.jackmailSimpleCalendarTwo ) ) {
							if ( current_date === 'date1' || current_date === 'date3' ) {
								date = date + ' 00:00:00';
							}
							else {
								date = date + ' 23:59:59';
							}
						}
						else if ( vm[ current_date ].selected_hour !== '' && vm[ current_date ].selected_minute !== '' ) {
							date = date + ' ' + vm[ current_date ].selected_hour + ':' + vm[ current_date ].selected_minute + ':00';
						}
						return $filter( 'formatedDate' )( date, 'timezone_to_gmt', 'sql' );
					}
					return '';
				};

				function nb_days_in_month( display_date ) {
					var nb_days = new Date( vm[ display_date ].selected_year, vm[ display_date ].selected_month, 0, 0, 0, 0, 0 ).getDate();
					var first_day = new Date( vm[ display_date ].selected_year, vm[ display_date ].selected_month - 1, 1, 0, 0, 0, 0 ).getDay();
					if ( first_day === 0 ) {
						first_day = 7;
					}
					var i;
					var begin_week_day = [];
					for ( i = 0; i < first_day - 1; i++ ) {
						begin_week_day.push( i );
					}
					vm[ display_date ].nb_days = nb_days;
					vm[ display_date ].begin_week_day = begin_week_day;
				}

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailRadio', [
	function() {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				jackmailRadio: '<',
				radioClass: '@',
				radioTitle: '@'
			},
			controllerAs: 'djr',
			template: [
				'<span class="jackmail_vertical_middle_container">' +
				'	<span ng-class="djr.jackmailRadio ? \'jackmail_radio_checked {{djr.radioClass}}\' : \'jackmail_radio_unchecked\'"></span>' +
				'	<span class="jackmail_radio_title" ng-show="djr.radioTitle">{{djr.radioTitle}}</span>' +
				'</span>'
			],
			controller: [ function() {

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailSearch', [
	'$timeout', '$rootScope',
	function( $timeout, $rootScope ) {
		return {
			restrict: 'A',
			controllerAs: 'djs',
			template: [
				'<div class="jackmail_header_search_container">' +
				'	<span ng-click="djs.display_hide_help()" class="jackmail_header_search">' +
				'		<span class="dashicons dashicons-search"></span>' +
				'		<span>{{$root.translations.needHelp}}</span>' +
				'	</span>' +
				'	<div ng-show="$root.show_help1" class="jackmail_header">' +
				'		<div>' +
				'			<div class="jackmail_header_buttons">' +
				'				<span ng-click="djs.display_hide_help()" class="jackmail_header_search_hide">' +
				'					<span class="dashicons dashicons-no-alt"></span>' +
				'				</span>' +
				'			</div>' +
				'		</div>' +
				'	</div>' +
				'</div>'
			],
			controller: [ function() {

				var vm = this;

				vm.display_hide_help = function() {
					$rootScope.scroll_top();
					$rootScope.show_help1 = !$rootScope.show_help1;
					if ( $rootScope.show_help1 ) {
						$timeout( function() {
							$rootScope.show_help2 = !$rootScope.show_help2;
							$timeout( function() {
								$rootScope.refresh_header_footer_position();
							} );
							$timeout( function() {
								angular.element( '.jackmail .jackmail_search_input input' ).focus();
								$rootScope.search_text();
							}, 500 );
						}, 500 );
					}
					else {
						$rootScope.show_help2 = !$rootScope.show_help2;
						$timeout( function() {
							$rootScope.refresh_header_footer_position();
						} );
					}
				};

			} ]
		};
	} ] );

angular.module( 'jackmail.directives' ).directive( 'jackmailTooltip', [
	'$compile', '$sce',
	function( $compile, $sce ) {
		return {
			restrict: 'A',
			scope: {},
			bindToController: {
				jackmailTooltip: '@',
				jackmailTooltipRight: '@'
			},
			controllerAs: 'djt',
			controller: [ '$scope', '$element', function( scope, element ) {
				var vm = this;
				var content =
					'<span class="jackmail_tooltip{{djt.jackmailTooltipRight !== undefined ? \' jackmail_tooltip_right\' : \'\'}}"' +
					'	ng-bind-html="djt.getContent(djt.jackmailTooltip)">' +
					'</span>';
				content = $compile( content )( scope );
				angular.element( element ).append( content );

				vm.getContent = function( content ) {
					return $sce.trustAsHtml( content );
				};

			} ]
		};
	} ] );