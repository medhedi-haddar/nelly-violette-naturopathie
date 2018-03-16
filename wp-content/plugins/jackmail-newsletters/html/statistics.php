<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="StatisticsController as s">
	<div class="jackmail_header_container">
		<div class="jackmail_header">
			<div>
				<div class="jackmail_header_menu" jackmail-header-menu></div>
				<div class="jackmail_header_buttons">
					<div jackmail-search></div>
				</div>
			</div>
		</div>
	</div>
	<div ng-hide="$root.show_help2">
		<div class="jackmail_content">
			<div class="jackmail_statistics_filter">
				<p class="jackmail_title jackmail_left jackmail_mt_0">
					<?php _e( 'Statistics', 'jackmail-newsletters' ) ?>
					<span class="jackmail_statistics_title" ng-class="s.compare ? 'jackmail_statistics_title_compare' : ''">{{s.page_title}}</span>
				</p>
				<div class="jackmail_statistics_calendar" ng-class="$root.content_loaded ? 'jackmail_statistics_calendar_display' : ''">
					<div jackmail-multiple-calendar
					     jackmail-option="day" jackmail-position="bottom"
					     on-confirm="s.change_filter_date" jackmail-refresh="true"
					     selected-date1="{{s.filter.selected_date1}}" selected-date2="{{s.filter.selected_date2}}">
					</div>
				</div>
			</div>
		</div>
		<div class="jackmail_statistics_content">
			<div class="jackmail_statistics_content_left jackmail_statistics_simplified" ng-style="{'height': s.left_height() + 'px'}">
				<div class="jackmail_statistics_content_left_title">
					<span jackmail-checkbox="$root.grid_service[ 0 ].nb_selected === s.campaigns_grid.length && $root.grid_service[ 0 ].nb_selected > 0"
						ng-click="s.grid_select_or_unselect_all()"
						ng-hide="s.campaigns_grid.length === 0">
					</span>
					<?php _e( 'Campaigns', 'jackmail-newsletters' ) ?>
				</div>
				<div ng-show="s.nb_campaigns === 0" class="jackmail_statistics_no_campaign"><?php _e( 'No campaigns', 'jackmail-newsletters' ) ?></div>
				<div ng-repeat="( key, campaign ) in s.campaigns_grid track by $index" class="jackmail_statistics_simplified_info" ng-class="campaign.show_details ? 'jackmail_statistics_simplified_selected' : ''">
					<div class="jackmail_statistics_simplified_info_content">
						<div class="jackmail_statistics_simplified_info_selector">
							<span jackmail-checkbox="campaign.selected"
								ng-click="s.grid_select_or_unselect_row( key )">
							</span>
						</div>
						<div class="jackmail_statistics_simplified_info_data">
							<span class="jackmail_bold">{{campaign.name}}</span>
							<br/>
							<span class="jackmail_grey">
								<span>
									<span><?php _e( 'Mailing date:', 'jackmail-newsletters' ) ?></span>
									<span>{{campaign.formated_date_campaign_sent}}</span>
								</span>
								<span>
									{{campaign.nb_contacts_valids | numberSeparator}}
									<span ng-show="campaign.type === 'campaign'">
										<span ng-hide="campaign.nb_contacts_valids > 1"><?php _e( 'contact', 'jackmail-newsletters' ) ?></span>
										<span ng-show="campaign.nb_contacts_valids > 1"><?php _e( 'contacts', 'jackmail-newsletters' ) ?></span>
									</span>
									<span ng-show="campaign.type === 'scenario'">
										<span ng-hide="campaign.nb_contacts_valids > 1"><?php _e( 'email', 'jackmail-newsletters' ) ?></span>
										<span ng-show="campaign.nb_contacts_valids > 1"><?php _e( 'emails', 'jackmail-newsletters' ) ?></span>
									</span>
								</span>
							</span>
						</div>
						<div class="jackmail_statistics_simplified_info_dropdown">
							<span ng-hide="campaign.show_details" ng-click="s.campaign_details( key )" class="dashicons dashicons-arrow-down-alt2"></span>
							<span ng-show="campaign.show_details" ng-click="s.campaign_details( key )" class="dashicons dashicons-arrow-up-alt2"></span>
						</div>
					</div>
					<div ng-show="campaign.show_details" class="jackmail_statistics_simplified_info_details {{$root.grid_service[ 0 ].grid_class}}">
						<div class="jackmail_column_0">
							<div><?php _e( 'Last modification:', 'jackmail-newsletters' ) ?></div>
							<div>{{campaign.updated_date_gmt | formatedDate : 'gmt_to_timezone' : 'hours'}}</div>
						</div>
						<div class="jackmail_column_2">
							<div><?php _e( 'Type:', 'jackmail-newsletters' ) ?></div>
							<div>{{campaign.type | campaignType}}</div>
						</div>
						<div class="jackmail_column_3">
							<div><?php _e( 'Campaign name:', 'jackmail-newsletters' ) ?></div>
							<div>{{campaign.name}}</div>
						</div>
						<div class="jackmail_column_4">
							<div><?php _e( 'Subject:', 'jackmail-newsletters' ) ?></div>
							<div>{{campaign.object}}</div>
						</div>
						<p>
							<img ng-src="{{campaign.preview}}"/>
						<p>
					</div>
				</div>
			</div>
			<div class="jackmail_statistics_content_right">
				<div class="jackmail_statistics_items">
					<div ng-click="s.show_hide_item( 'synthesis' )" ng-class="s.show_item === 'synthesis' ? 'jackmail_statistics_item_selected' : 'jackmail_statistics_item_not_selected'"><?php _e( 'Summary', 'jackmail-newsletters' ) ?></div>
					<div ng-click="s.show_hide_item( 'monitoring' )" ng-class="s.show_item === 'monitoring' ? 'jackmail_statistics_item_selected' : 'jackmail_statistics_item_not_selected'"><?php _e( 'Behavioral tracking', 'jackmail-newsletters' ) ?></div>
					<div ng-click="s.show_hide_item( 'technologies' )" ng-class="s.show_item === 'technologies' ? 'jackmail_statistics_item_selected' : 'jackmail_statistics_item_not_selected'"><?php _e( 'Technologies', 'jackmail-newsletters' ) ?></div>
				</div>
				<div ng-show="s.show_item === 'synthesis'" class="jackmail_statistics_container">
					<div class="jackmail_statistics_numbers_container">
						<div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left">
									<span class="jackmail_statistics_numbers_title"><?php _e( 'Recipients', 'jackmail-newsletters' ) ?></span>
								</div>
								<div class="jackmail_left jackmail_statistics_recipients">
									<span class="jackmail_statistics_number_principal">
										<span>{{s.numbers.period1.recipients | numberSeparator}}</span>
									</span>
								</div>
							</div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left jackmail_left_70">
									&nbsp;
								</div>
								<div class="jackmail_left jackmail_left_30">
									<span ng-class="s.numbers.tendency.recipients < 0 ? 'jackmail_statistics_numbers_compare_inferior_nok' : 'jackmail_statistics_numbers_compare_superior_ok'">
										{{s.numbers.tendency.recipients | numberSeparator}}
									</span>
								</div>
							</div>
							<canvas class="jackmail_statistics_synthesis_canvas" id="jackmail_statistics_synthesis_canvas_recipients" width="180" height="60"></canvas>
						</div>
						<div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left">
									<span class="jackmail_statistics_numbers_title"><?php _e( 'Click rate', 'jackmail-newsletters' ) ?></span>
								</div>
								<div class="jackmail_left jackmail_statistics_clicks">
									<span class="jackmail_statistics_number_principal">
										<span>{{s.numbers.period1.clicks_percent}} <span>%</span></span>
									</span>
								</div>
							</div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left jackmail_left_70">
									<span class="jackmail_grey">
										<span>
											{{s.numbers.period1.clicks | numberSeparator}}
											<span ng-hide="s.numbers.period1.clicks > 1"><?php _e( 'click', 'jackmail-newsletters' ) ?></span>
											<span ng-show="s.numbers.period1.clicks > 1"><?php _e( 'clicks', 'jackmail-newsletters' ) ?></span>
										</span>
									</span>
								</div>
								<div class="jackmail_left jackmail_left_30">
									<span ng-class="s.numbers.tendency.clicks_percent < 0 ? 'jackmail_statistics_numbers_compare_inferior_nok' : 'jackmail_statistics_numbers_compare_superior_ok'">
										{{s.numbers.tendency.clicks_percent | numberSeparator}} %
									</span>
								</div>
							</div>
							<canvas class="jackmail_statistics_synthesis_canvas" id="jackmail_statistics_synthesis_canvas_clicks" width="180" height="60"></canvas>
						</div>
						<div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left">
									<span class="jackmail_statistics_numbers_title"><?php _e( 'Opening rate', 'jackmail-newsletters' ) ?></span>
								</div>
								<div class="jackmail_left jackmail_statistics_opens">
									<span class="jackmail_statistics_number_principal">
										<span>{{s.numbers.period1.opens_percent}} <span>%</span></span>
									</span>
								</div>
							</div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left jackmail_left_70">
									<span class="jackmail_grey">
										<span>
											{{s.numbers.period1.opens | numberSeparator}}
											<span ng-hide="s.numbers.period1.opens > 1"><?php _e( 'opening', 'jackmail-newsletters' ) ?></span>
											<span ng-show="s.numbers.period1.opens > 1"><?php _e( 'openings', 'jackmail-newsletters' ) ?></span>
										</span>
									</span>
								</div>
								<div class="jackmail_left jackmail_left_30">
									<span ng-class="s.numbers.tendency.opens_percent < 0 ? 'jackmail_statistics_numbers_compare_inferior_nok' : 'jackmail_statistics_numbers_compare_superior_ok'">
										{{s.numbers.tendency.opens_percent | numberSeparator}} %
									</span>
								</div>
							</div>
							<canvas class="jackmail_statistics_synthesis_canvas" id="jackmail_statistics_synthesis_canvas_opens" width="180" height="60"></canvas>
						</div>
						<div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left">
									<span class="jackmail_statistics_numbers_title"><?php _e( 'Reading time', 'jackmail-newsletters' ) ?></span>
								</div>
								<div class="jackmail_left jackmail_statistics_read">
									<span class="jackmail_statistics_number_principal">
										<span>
											{{s.numbers.period1.read_seconds | secondsConversion}}
											<span ng-show="s.numbers.period1.read_seconds <= 1"><?php _e( 'sec.', 'jackmail-newsletters' ) ?></span>
											<span ng-show="s.numbers.period1.read_seconds > 1 && s.numbers.period1.read_seconds < 60"><?php _e( 'secs.', 'jackmail-newsletters' ) ?></span>
										</span>
									</span>
								</div>
							</div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left jackmail_left_70">
									&nbsp;
								</div>
								<div class="jackmail_left jackmail_left_30">
									<span ng-class="s.numbers.tendency.read_seconds < 0 ? 'jackmail_statistics_numbers_compare_inferior_nok' : 'jackmail_statistics_numbers_compare_superior_ok'">
										{{s.numbers.tendency.read_seconds | secondsConversion}}
										<span ng-show="s.numbers.period1.read_seconds <= 1"><?php _e( 'sec.', 'jackmail-newsletters' ) ?></span>
										<span ng-show="s.numbers.period1.read_seconds > 1 && s.numbers.tendency.read_seconds < 60"><?php _e( 'secs.', 'jackmail-newsletters' ) ?></span>
									</span>
								</div>
							</div>
							<canvas class="jackmail_statistics_synthesis_canvas" id="jackmail_statistics_synthesis_canvas_read_seconds" width="180" height="60"></canvas>
						</div>
						<div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left">
									<span class="jackmail_statistics_numbers_title"><?php _e( 'Unsubscribers', 'jackmail-newsletters' ) ?></span>
								</div>
								<div class="jackmail_left jackmail_statistics_unsubscribes">
									<span class="jackmail_statistics_number_principal">
										<span>{{s.numbers.period1.unsubscribes_percent}} <span>%</span></span>
									</span>
								</div>
							</div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left jackmail_left_70">
									<span class="jackmail_grey">
										<span>
											{{s.numbers.period1.unsubscribes | numberSeparator}}
											<span ng-hide="s.numbers.period1.unsubscribes > 1"><?php _e( 'Unsubscriber', 'jackmail-newsletters' ) ?></span>
											<span ng-show="s.numbers.period1.unsubscribes > 1"><?php _e( 'Unsubscribers', 'jackmail-newsletters' ) ?></span>
										</span>
									</span>
								</div>
								<div class="jackmail_left jackmail_left_30">
									<span ng-class="s.numbers.tendency.unsubscribes_percent <= 0 ? 'jackmail_statistics_numbers_compare_inferior_ok' : 'jackmail_statistics_numbers_compare_superior_nok'">
										{{s.numbers.tendency.unsubscribes_percent | numberSeparator}} %
									</span>
								</div>
							</div>
							<canvas class="jackmail_statistics_synthesis_canvas" id="jackmail_statistics_synthesis_canvas_unsubscribes" width="180" height="60"></canvas>
						</div>
						<div>
							<div class="jackmail_overflow_hidden">
								<div class="jackmail_left jackmail_statistics_synthesis_repartition_legend">
									<span class="jackmail_statistics_numbers_title"><?php _e( 'Distribution', 'jackmail-newsletters' ) ?></span>
									<p>
										<span class="jackmail_statistics_synthesis_repartition_legend_open" ng-style="{ 'background': value.color }"></span>
										<?php _e( 'Openers', 'jackmail-newsletters' ) ?> ({{s.numbers.period1.openers_percent}} %)
									</p>
									<p>
										<span class="jackmail_statistics_synthesis_repartition_legend_bounces" ng-style="{ 'background': value.color }"></span>
										<?php _e( 'Bounces', 'jackmail-newsletters' ) ?> ({{s.numbers.period1.bounces_percent}} %)
									</p>
									<p>
										<span class="jackmail_statistics_synthesis_repartition_legend_no_open" ng-style="{ 'background': value.color }"></span>
										<?php _e( 'Non openers', 'jackmail-newsletters' ) ?> ({{s.numbers.period1.no_openers_percent}} %)
									</p>
								</div>
								<div class="jackmail_left">
									<div ng-repeat="value in s.synthesis_graphic track by $index">
										<canvas width="100" height="100" id="jackmail_chartjs_synthesis_repartition"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="jackmail_statistics_synthesis_more_actives_contacts_container">
						<p class="jackmail_statistics_numbers_title"><?php _e( 'Most reactive contacts', 'jackmail-newsletters' ) ?></p>
						<table class="jackmail_statistics_synthesis_more_actives_contacts">
							<tr class="jackmail_grid_th">
								<th></th>
								<th><?php _e( 'Email', 'jackmail-newsletters' ) ?></th>
								<th><?php _e( 'Opening rate', 'jackmail-newsletters' ) ?></th>
								<th><?php _e( 'Click rate', 'jackmail-newsletters' ) ?></th>
							</tr>
							<tr ng-repeat="( key, contact ) in s.more_actives_contacts track by $index" ng-show="s.nb_more_actives_contacts > 0" class="jackmail_statistics_synthesis_more_actives_contacts_details">
								<td>
									<span class="jackmail_statistics_synthesis_more_actives_contacts_i dashicons dashicons-awards" ng-class="'jackmail_statistics_synthesis_more_actives_contacts_' + ( key + 1 )"></span>
									<span class="jackmail_statistics_synthesis_more_actives_contacts_value">{{key + 1}}</span>
									<span class="jackmail_statistics_synthesis_more_actives_contacts_email"></span>
								</td>
								<td data-headers="<?php esc_attr_e( 'Email', 'jackmail-newsletters' ) ?>">{{contact.email}}</td>
								<td data-headers="<?php esc_attr_e( 'Opening rate', 'jackmail-newsletters' ) ?>"class="jackmail_statistics_synthesis_more_actives_contacts_opens">{{contact.opens_percent}} %</td>
								<td data-headers="<?php esc_attr_e( 'Click rate', 'jackmail-newsletters' ) ?>"class="jackmail_statistics_synthesis_more_actives_contacts_clicks">{{contact.clicks_percent}} %</td>
							</tr>
						</table>
						<p ng-show="s.nb_more_actives_contacts > 0" ng-click="s.show_more_actives_contacts()" class="jackmail_statistics_synthesis_more_actives_contacts_details_link">
							<?php _e( 'Details', 'jackmail-newsletters' ) ?>
						</p>
						<p ng-show="s.nb_more_actives_contacts === 0" class="jackmail_statistics_no_data"><?php _e( 'No data', 'jackmail-newsletters' ) ?></p>
					</div>
					<div class="jackmail_statistics_graphic_timeline_choice">
						<input ng-click="s.display_synthesis_graphic()" ng-class="s.show_synthesis_item === 'graphic' ? 'jackmail_statistics_graphic_timeline_choice_selected' : ''" type="button" value="<?php esc_attr_e( 'Statistics', 'jackmail-newsletters' ) ?>"/>
						<input ng-click="s.display_synthesis_timeline()" ng-class="s.show_synthesis_item === 'timeline' ? 'jackmail_statistics_graphic_timeline_choice_selected' : ''" type="button" value="<?php esc_attr_e( 'Timeline', 'jackmail-newsletters' ) ?>"/>
					</div>
					<div ng-show="s.show_synthesis_item === 'graphic'">
						<div ng-repeat="value in s.synthesis_graphic track by $index" ng-show="s.nb_displays_graphic !== 0" class="jackmail_statistics_graphic">
							<canvas id="jackmail_chartjs_synthesis"></canvas>
							<div id="jackmail_chartjs_synthesis_tooltip"></div>
						</div>
					</div>
					<div ng-show="s.show_synthesis_item === 'timeline'">
						<div ng-show="s.nb_synthesis_timeline > 0" class="jackmail_statistics_timeline">
							<div class="jackmail_statistics_timeline_line"></div>
							<div ng-repeat="timeline in s.synthesis_timeline track by $index" ng-class="'jackmail_statistics_timeline_' + timeline.event" ng-style="{ 'right': 'calc(' + timeline.position + '% - 7px)' }">
								<div ng-style="timeline.position > 50 ? { 'left': '0' } : { 'right': '0' }">
									<span class="jackmail_green">{{timeline.email}}</span>
									<br/>
									<span ng-show="timeline.event === 'open'"><?php _e( 'opened:', 'jackmail-newsletters' ) ?></span>
									<span ng-show="timeline.event === 'click'"><?php _e( 'clicked:', 'jackmail-newsletters' ) ?></span>
									<span ng-show="timeline.event === 'unsubscribe'"><?php _e( 'opted out:', 'jackmail-newsletters' ) ?></span>
									<br/>
									{{timeline.date | formatedDate : 'gmt_to_timezone' : 'hours'}}
								</div>
							</div>
						</div>
						<div ng-show="s.nb_synthesis_timeline === 0" class="jackmail_statistics_no_data"><?php _e( 'No data', 'jackmail-newsletters' ) ?></div>
					</div>
					<div ng-show="s.show_synthesis_item === 'graphic' || s.show_synthesis_item === 'timeline'" class="jackmail_statistics_graphic_legend">
						<span ng-show="s.show_synthesis_item === 'graphic'"
							jackmail-checkbox-simple="s.graphic_displays.recipients"
							ng-click="s.show_hide_graphic_legend( 'recipients' )"
							checkbox-class="jackmail_checked_recipients"
							checkbox-title="<?php esc_attr_e( 'Recipients', 'jackmail-newsletters' ) ?>">
						</span>
						<span jackmail-checkbox-simple="s.graphic_displays.opens"
							ng-click="s.show_hide_graphic_legend( 'opens' )"
							checkbox-class="jackmail_checked_opens"
							checkbox-title="<?php esc_attr_e( 'Opens', 'jackmail-newsletters' ) ?>">
						</span>
						<span jackmail-checkbox-simple="s.graphic_displays.clicks"
							ng-click="s.show_hide_graphic_legend( 'clicks' )"
							checkbox-class="jackmail_checked_clicks"
							checkbox-title="<?php esc_attr_e( 'Clicks', 'jackmail-newsletters' ) ?>">
						</span>
						<span jackmail-checkbox-simple="s.graphic_displays.unsubscribes"
							ng-click="s.show_hide_graphic_legend( 'unsubscribes' )"
							checkbox-class="jackmail_checked_unsubscribes"
							checkbox-title="<?php esc_attr_e( 'Unsubscribers', 'jackmail-newsletters' ) ?>">
						</span>
					</div>
				</div>
				<div ng-show="s.show_item === 'monitoring'" class="jackmail_statistics_monitoring">
					<div class="jackmail_grid_buttons">
						<div class="jackmail_left">
							<span class="jackmail_statistics_monitoring_nb_recipients">{{s.monitoring_grid_total_rows}}</span>
							<span ng-hide="s.monitoring_grid_total_rows > 1"><?php _e( 'unique recipient', 'jackmail-newsletters' ) ?></span>
							<span ng-show="s.monitoring_grid_total_rows > 1"><?php _e( 'unique recipients', 'jackmail-newsletters' ) ?></span>
						</div>
						<div class="jackmail_statistics_retarget"
						    ng-show="$root.grid_service[ 0 ].nb_selected === 1 && s.page_title_type === 'campaign' && $root.settings.is_statistics_v2 === '1'">
							<span jackmail-tooltip-right jackmail-tooltip="<?php esc_attr_e( 'Re-send the campaign to people who didn\'t open/click it', 'jackmail-newsletters' ) ?>">
								<input ng-click="s.create_campaign_unopened()" class="jackmail_green_button" type="button" value="<?php esc_attr_e( 'Retarget this campaign', 'jackmail-newsletters' ) ?>"/>
							</span>
						</div>
						<div jackmail-grid-search
							jackmail-action="s.get_monitoring_data_search_reset"
							class="jackmail_right jackmail_grid_buttons_search jackmail_statistics_monitoring_search">
						</div>
					</div>
					<div class="jackmail_grid_container">
						<div class="jackmail_grid_header" ng-class="s.monitoring_view === 'simplified' ? 'jackmail_grid_header_statistics_simplified' : ''">
							<span class="jackmail_statistics_grid_header_title">
								<?php _e( 'Details for selected recipients', 'jackmail-newsletters' ) ?>
							</span>
							<div class="jackmail_right" jackmail-dropdown-button button-value="<?php esc_attr_e( 'Manage the columns', 'jackmail-newsletters' ) ?>"
								titles-clicks-grid="s.monitoring_columns" titles-clicks-grid-checked="$root.grid_service[ 1 ].grid_classes"
								titles-clicks-grid-event="$root.grid_service[ 1 ].display_or_hide_column( key )" titles-clicks-grid-repeat-filter="s.monitoring_columns_displayed">
							</div>
							<div button-class="jackmail_dropdown_button_container jackmail_grid_columns_button jackmail_right" jackmail-dropdown-button
							    button-value="{{s.monitoring_view === 'detailled' ? '<?php echo esc_js( __( 'Detailed reading', 'jackmail-newsletters' ) ) ?>' : '<?php echo esc_js( __( 'Simplified reading', 'jackmail-newsletters' ) ) ?>'}}"
								titles-clicks-array="s.monitoring_views_select_titles"
								titles-clicks-array-event="s.select_monitoring_view( key )">
							</div>
						</div>
						<div ng-show="s.monitoring_view === 'detailled'">
							<div class="jackmail_grid jackmail_grid_th" ng-class="$root.grid_service[ 1 ].grid_class">
								<table>
									<tr>
										<th ng-repeat="( key, column ) in s.monitoring_columns track by $index" ng-click="column.field !== '' ? s.monitoring_range_by( column.field ) : ''" class="{{'jackmail_column_' + key}}" ng-class="column.field !== '' ? 'jackmail_column_ordering' : ''">
											<span>{{column.name}}</span>
										</th>
									</tr>
								</table>
							</div>
							<div class="jackmail_grid jackmail_grid_content_defined" ng-class="$root.grid_service[ 1 ].grid_class"
								grid-scroll="$root.grid_service[ 1 ]"
								grid-total="s.monitoring_grid_total_rows"
								grid-load="s.get_monitoring_data_more">
								<table>
									<tr ng-repeat="( key, contact ) in s.monitoring_grid | limitTo: $root.grid_service[ 1 ].nb_lines_grid track by $index">
										<td data-headers="<?php esc_attr_e( 'Email', 'jackmail-newsletters' ) ?>" class="jackmail_column_0">
											<span ng-click="s.display_recipient_details( contact.email )" class="jackmail_green jackmail_cursor_pointer">
												{{contact.email}}
											</span>
										</td>
										<td data-headers="<?php esc_attr_e( 'Openings', 'jackmail-newsletters' ) ?>" class="jackmail_column_1">
											<span>{{contact.nbOpen | numberSeparator}}</span>
										</td>
										<td data-headers="<?php esc_attr_e( 'Clicks', 'jackmail-newsletters' ) ?>" class="jackmail_column_2">
											<span>{{contact.nbHit | numberSeparator}}</span>
										</td>
										<td data-headers="<?php esc_attr_e( 'Desktop', 'jackmail-newsletters' ) ?>" class="jackmail_column_3 statistics_ok_nok">
											<span class="dashicons" ng-class="( contact.nbOpenDesktop > 0 || contact.nbHitDesktop > 0 ) ? 'dashicons-yes' : 'dashicons-no-alt'"></span>
										</td>
										<td data-headers="<?php esc_attr_e( 'Mobile', 'jackmail-newsletters' ) ?>" class="jackmail_column_4 statistics_ok_nok">
											<span class="dashicons" ng-class="( contact.nbOpenMobile > 0 || contact.nbHitMobile > 0 ) ? 'dashicons-yes' : 'dashicons-no-alt'"></span>
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div ng-show="s.monitoring_view === 'simplified'" class="jackmail_statistics_simplified jackmail_grid_content_defined"
						    grid-scroll="$root.grid_service[ 1 ]"
						    grid-total="s.monitoring_grid_total_rows"
						    grid-load="s.get_monitoring_data_more">
							<div ng-repeat="( key, contact ) in s.monitoring_grid | limitTo: $root.grid_service[ 1 ].nb_lines_grid track by $index"
							    class="jackmail_statistics_simplified_info" ng-class="contact.show_details ? 'jackmail_statistics_simplified_selected' : ''">
								<div class="jackmail_statistics_simplified_info_content">
									<div class="jackmail_statistics_simplified_info_img">
										<img ng-src="{{$root.settings.jackmail_url}}img/statistics_contact.png" alt=""/>
									</div>
									<div class="jackmail_statistics_simplified_info_data">
										<span ng-click="s.display_recipient_details( contact.email )" class="jackmail_bold jackmail_cursor_pointer">
											{{contact.email}}
										</span>
										<br/>
										<span class="jackmail_grey">
											<span><?php _e( 'Openings:' ) ?> {{contact.nbOpen | numberSeparator}}</span>
											<span><?php _e( 'Clicks:' ) ?> {{contact.nbHit | numberSeparator}}</span>
										</span>
									</div>
									<div class="jackmail_statistics_simplified_info_dropdown">
										<span ng-hide="contact.show_details" ng-click="s.monitoring_details( key )" class="dashicons dashicons-arrow-down-alt2"></span>
										<span ng-show="contact.show_details" ng-click="s.monitoring_details( key )" class="dashicons dashicons-arrow-up-alt2"></span>
									</div>
								</div>
								<div ng-show="contact.show_details" class="jackmail_statistics_simplified_info_details" ng-class="$root.grid_service[ 1 ].grid_class">
									<div class="jackmail_column_1">
										<div><?php _e( 'Total opens:', 'jackmail-newsletters' ) ?></div>
										<div><span>{{contact.nbOpen | numberSeparator}}</span></div>
									</div>
									<div class="jackmail_column_1 jackmail_column_3">
										<div><?php _e( 'Opens on desktop:', 'jackmail-newsletters' ) ?></div>
										<div><span>{{contact.nbOpenDesktop | numberSeparator}}</span></div>
									</div>
									<div class="jackmail_column_1 jackmail_column_4">
										<div><?php _e( 'Opens on mobile:', 'jackmail-newsletters' ) ?></div>
										<div><span>{{contact.nbOpenMobile | numberSeparator}}</span></div>
									</div>
									<div class="jackmail_column_2">
										<div><?php _e( 'Total clicks:', 'jackmail-newsletters' ) ?></div>
										<div><span>{{contact.nbHit | numberSeparator}}</span></div>
									</div>
									<div class="jackmail_column_2 jackmail_column_3">
										<div><?php _e( 'Clicks on desktop:', 'jackmail-newsletters' ) ?></div>
										<div><span>{{contact.nbHitDesktop | numberSeparator}}</span></div>
									</div>
									<div class="jackmail_column_2 jackmail_column_4">
										<div><?php _e( 'Clicks on mobile:', 'jackmail-newsletters' ) ?></div>
										<div><span>{{contact.nbHitMobile | numberSeparator}}</span></div>
									</div>
								</div>
							</div>
						</div>
						<div ng-show="s.nb_monitoring_grid === 0" class="jackmail_statistics_no_data"><?php _e( 'No data', 'jackmail-newsletters' ) ?></div>
					</div>
				</div>
				<div ng-show="s.show_item === 'technologies'" class="jackmail_statistics_container">
					<div id="jackmail_chartjs_technologies_tooltip"></div>
					<div class="jackmail_statistics_technologies_graphics">
						<div>
							<p class="jackmail_technologies_title"><?php _e( 'Desktop/mobile distribution', 'jackmail-newsletters' ) ?></p>
							<div class="jackmail_statistics_technologies_numbers">
								<div>
									<span>{{s.technologies_os_total.desktop_percent}} %</span>
									<br/>
									<span><?php _e( 'Desktops', 'jackmail-newsletters' ) ?></span>
								</div>
								<div>
									<span>{{s.technologies_os_total.mobile_percent}} %</span>
									<br/>
									<span><?php _e( 'Mobiles / Tablets', 'jackmail-newsletters' ) ?></span>
								</div>
							</div>
							<div ng-show="s.technologies_os_details.length > 0" class="jackmail_statistics_technologies_graphic_container">
								<div>
									<p ng-repeat="value in s.technologies_os_details | limitTo: 5 | filter: type = 'desktop' track by $index">
										<span ng-style="{ 'background': value.color }"></span>
										{{value.percent}} % {{value.name}}
									</p>
								</div>
								<div ng-repeat="value in s.technologies_graphic track by $index" class="jackmail_statistics_technologies_graphic">
									<canvas width="130" height="130" id="jackmail_chartjs_technologies_os"></canvas>
								</div>
								<div>
									<p ng-repeat="value in s.technologies_os_details | limitTo:5 | filter: type = 'mobile' track by $index">
										{{value.percent}} % {{value.name}}
										<span ng-style="{ 'background': value.color }"></span>
									</p>
								</div>
							</div>
						</div>
						<div>
							<p class="jackmail_technologies_title"><?php _e( 'Email tools', 'jackmail-newsletters' ) ?></p>
							<div class="jackmail_statistics_technologies_numbers">
								<div>
									<span>{{s.technologies_softwares_total.application_percent}} %</span>
									<br/>
									<span><?php _e( 'Desktop clients', 'jackmail-newsletters' ) ?></span>
								</div>
								<div>
									<span>{{s.technologies_softwares_total.webmail_percent}} %</span>
									<br/>
									<span><?php _e( 'Web based clients', 'jackmail-newsletters' ) ?></span>
								</div>
							</div>
							<div ng-show="s.technologies_softwares_details.length > 0" class="jackmail_statistics_technologies_graphic_container">
								<div>
									<p ng-repeat="value in s.technologies_softwares_details | limitTo: 5 | filter: type = 'application' track by $index">
										<span ng-style="{ 'background': value.color }"></span>
										{{value.percent}} % {{value.name}}
									</p>
								</div>
								<div ng-repeat="value in s.technologies_graphic track by $index" class="jackmail_statistics_technologies_graphic">
									<canvas width="130" height="130" id="jackmail_chartjs_technologies_softwares"></canvas>
								</div>
								<div>
									<p ng-repeat="value in s.technologies_softwares_details | limitTo:5 | filter: type = 'webmail' track by $index">
										{{value.percent}} % {{value.name}}
										<span ng-style="{ 'background': value.color }"></span>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div>
						<p class="jackmail_technologies_title"><?php _e( 'Profile distribution', 'jackmail-newsletters' ) ?></p>
						<div class="jackmail_technologies_selectors_container">
							<div><?php _e( 'First variable', 'jackmail-newsletters' ) ?></div>
							<div class="jackmail_dropdown_button_container" ng-mouseleave="$root.grid_service[ 2 ].hide_columns_button()">
								<span jackmail-dropdown-button-visible span-title="{{s.technologies_first_selected.name}}" ng-click="$root.grid_service[ 2 ].display_or_hide_columns_button()"></span>
								<div ng-show="$root.grid_service[ 2 ].display_columns_button" ng-click="$root.grid_service[ 2 ].hide_columns_button()">
									<div class="jackmail_dropdown_button_border_container">
										<span class="jackmail_dropdown_button_border_top"></span>
										<span class="jackmail_dropdown_button_border_top2"></span>
									</div>
									<div class="jackmail_dropdown_button_content">
										<span ng-repeat="technologie in s.technologies_first track by $index"
											ng-show="technologie.name !== s.technologies_second_selected.name"
											ng-click="s.check_or_uncheck_technology_first( technologie.value )"
											class="jackmail_dropdown_button_click {{s.technologies_first_selected.value === technologie.value ? 'jackmail_dropdown_button_choice_selected' : ''}}">
											{{technologie.name}}
										</span>
									</div>
								</div>
							</div>
							<div><?php _e( 'Second variable', 'jackmail-newsletters' ) ?></div>
							<div class="jackmail_dropdown_button_container" ng-mouseleave="$root.grid_service[ 3 ].hide_columns_button()">
								<span jackmail-dropdown-button-visible span-title="{{s.technologies_second_selected.name}}" ng-click="$root.grid_service[ 3 ].display_or_hide_columns_button()"></span>
								<div ng-show="$root.grid_service[ 3 ].display_columns_button" ng-click="$root.grid_service[ 3 ].hide_columns_button()">
									<div class="jackmail_dropdown_button_border_container">
										<span class="jackmail_dropdown_button_border_top"></span>
										<span class="jackmail_dropdown_button_border_top2"></span>
									</div>
									<div class="jackmail_dropdown_button_content">
										<span ng-repeat="technologie in s.technologies_second track by $index"
											ng-show="technologie.name !== s.technologies_first_selected.name"
											ng-click="s.check_or_uncheck_technology_second( technologie.value )"
											class="jackmail_dropdown_button_click {{s.technologies_second_selected.value === technologie.value ? 'jackmail_dropdown_button_choice_selected' : ''}}">
											{{technologie.name}}
										</span>
									</div>
								</div>
							</div>
						</div>
						<div class="jackmail_technologies_grid">
							<div class="jackmail_grid_container">
								<div class="jackmail_grid_header">
									<span class="jackmail_statistics_grid_header_title"><?php _e( 'Profiles', 'jackmail-newsletters' ) ?></span>
								</div>
								<div class="jackmail_grid jackmail_grid_th" ng-class="$root.grid_service[ 3 ].grid_class">
									<table>
										<tr>
											<th ng-show="s.technologies_first_selected.value === 'messagerie'" ng-click="s.technology_range_by( 'messagerie' )" class="jackmail_column_ordering">
												<span><?php _e( 'Email client', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-show="s.technologies_first_selected.value === 'type_messagerie'" ng-click="s.technology_range_by( 'type_messagerie' )" class="jackmail_column_ordering">
												<span><?php _e( 'Email client category', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-show="s.technologies_first_selected.value === 'os'" ng-click="s.technology_range_by( 'os' )" class="jackmail_column_ordering">
												<span><?php _e( 'O.S.', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-show="s.technologies_first_selected.value === 'type_device'" ng-click="s.technology_range_by( 'type_device' )" class="jackmail_column_ordering">
												<span><?php _e( 'Device category', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-show="s.technologies_second_selected.value === 'messagerie'" ng-click="s.technology_range_by( 'messagerie' )" class="jackmail_column_ordering">
												<span><?php _e( 'Email client', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-show="s.technologies_second_selected.value === 'type_messagerie'" ng-click="s.technology_range_by( 'type_messagerie' )" class="jackmail_column_ordering">
												<span><?php _e( 'Email client category', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-show="s.technologies_second_selected.value === 'os'" ng-click="s.technology_range_by( 'os' )" class="jackmail_column_ordering">
												<span><?php _e( 'O.S.', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-show="s.technologies_second_selected.value === 'type_device'" ng-click="s.technology_range_by( 'type_device' )" class="jackmail_column_ordering">
												<span><?php _e( 'Device category', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-click="s.technology_range_by( 'openings' )" class="jackmail_column_ordering">
												<span><?php _e( 'Openings', 'jackmail-newsletters' ) ?></span>
											</th>
											<th ng-click="s.technology_range_by( 'percent' )" class="jackmail_column_ordering">
												<span><?php _e( 'Distribution', 'jackmail-newsletters' ) ?></span>
											</th>
										</tr>
									</table>
								</div>
								<div class="jackmail_grid jackmail_grid_content_defined" ng-class="$root.grid_service[ 3 ].grid_class">
									<table>
										<tr ng-repeat="technology in s.technologies_grid track by $index" ng-hide="technology.hidden">
											<td ng-show="s.technologies_first_selected.value === 'messagerie'" data-headers="<?php esc_attr_e( 'Email client', 'jackmail-newsletters' ) ?>">
												{{technology.messagerie | firstUppercaseOthersLowercase}}
											</td>
											<td ng-show="s.technologies_first_selected.value === 'type_messagerie'" data-headers="<?php esc_attr_e( 'Email client category', 'jackmail-newsletters' ) ?>">
												{{technology.type_messagerie | firstUppercaseOthersLowercase}}
											</td>
											<td ng-show="s.technologies_first_selected.value === 'os'" data-headers="<?php esc_attr_e( 'O.S.', 'jackmail-newsletters' ) ?>">
												{{technology.os | firstUppercaseOthersLowercase}}
											</td>
											<td ng-show="s.technologies_first_selected.value === 'type_device'" data-headers="<?php esc_attr_e( 'Device category', 'jackmail-newsletters' ) ?>">
												{{technology.type_device | firstUppercaseOthersLowercase}}
											</td>
											<td ng-show="s.technologies_second_selected.value === 'messagerie'" data-headers="<?php esc_attr_e( 'Email client', 'jackmail-newsletters' ) ?>">
												{{technology.messagerie | firstUppercaseOthersLowercase}}
											</td>
											<td ng-show="s.technologies_second_selected.value === 'type_messagerie'" data-headers="<?php esc_attr_e( 'Email client category', 'jackmail-newsletters' ) ?>">
												{{technology.type_messagerie | firstUppercaseOthersLowercase}}
											</td>
											<td ng-show="s.technologies_second_selected.value === 'os'" data-headers="<?php esc_attr_e( 'O.S.', 'jackmail-newsletters' ) ?>">
												{{technology.os | firstUppercaseOthersLowercase}}
											</td>
											<td ng-show="s.technologies_second_selected.value === 'type_device'" data-headers="<?php esc_attr_e( 'Device category', 'jackmail-newsletters' ) ?>">
												{{technology.type_device | firstUppercaseOthersLowercase}}
											</td>
											<td data-headers="<?php esc_attr_e( 'Openings', 'jackmail-newsletters' ) ?>">
												{{technology.openings | numberSeparator}}
											</td>
											<td data-headers="<?php esc_attr_e( 'Distribution', 'jackmail-newsletters' ) ?>">
												<span class="jackmail_green">{{technology.percent}} %</span>
											</td>
										</tr>
									</table>
								</div>
								<div ng-show="s.nb_technologies_grid === 0" class="jackmail_statistics_no_data"><?php _e( 'No data', 'jackmail-newsletters' ) ?></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>