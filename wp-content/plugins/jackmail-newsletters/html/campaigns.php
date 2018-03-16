<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="CampaignsController as c">
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
		<div ng-show="c.nb_campaigns === 0">
			<div class="jackmail_campaigns_action_container">
				<div>
					<div class="jackmail_campaigns_action">
						<div ng-click="c.create_campaign()" class="jackmail_campaign_new_campaign">
							<p class="jackmail_bold"><?php _e( 'Send a campaign', 'jackmail-newsletters' ) ?></p>
							<p class="jackmail_grey"><?php _e( 'Create and send an email to your recipients', 'jackmail-newsletters' ) ?></p>
							<span><?php _e( 'Start', 'jackmail-newsletters' ) ?></span>
						</div>
						<div ng-click="c.create_scenario()" class="jackmail_campaign_new_scenario">
							<p class="jackmail_bold"><?php _e( 'Set up an automated workflow', 'jackmail-newsletters' ) ?></p>
							<p class="jackmail_grey"><?php _e( 'Set up a workflow based on events', 'jackmail-newsletters' ) ?></p>
							<span><?php _e( 'Start', 'jackmail-newsletters' ) ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div ng-show="c.nb_campaigns > 0">
			<div class="jackmail_campaigns_header">
				<div ng-click="c.create_scenario()">
					<span class="jackmail_bold"><?php _e( 'Set up an automated workflow', 'jackmail-newsletters' ) ?></span>
					<br/>
					<span class="jackmail_grey"><?php _e( 'Set up a workflow based on events', 'jackmail-newsletters' ) ?></span>
					<br/>
					<span class="jackmail_green"><?php _e( 'Schedule workflow execution', 'jackmail-newsletters' ) ?></span>
				</div>
				<div ng-click="c.create_campaign()">
					<span class="jackmail_bold"><?php _e( 'Send a campaign', 'jackmail-newsletters' ) ?></span>
					<br/>
					<span class="jackmail_grey"><?php _e( 'Create and send an email to your recipients', 'jackmail-newsletters' ) ?></span>
					<br/>
					<span class="jackmail_green"><?php _e( 'Start my campaign', 'jackmail-newsletters' ) ?></span>
				</div>
			</div>
			<div class="jackmail_content">
				<p class="jackmail_title jackmail_center"><?php _e( 'Campaign history', 'jackmail-newsletters' ) ?></p>
				<div class="jackmail_previews_selector">
					<div class="jackmail_previews_selection">
						<span><?php _e( 'Campaign type:', 'jackmail-newsletters' ) ?></span>
						<span jackmail-checkbox="c.filter.emailing"
							ng-click="c.change_option( 'emailing' )"
							checkbox-title="<?php esc_attr_e( 'Email campaign', 'jackmail-newsletters' ) ?>">
						</span>
						<span jackmail-checkbox="c.filter.scenario"
							ng-click="c.change_option( 'scenario' )"
							checkbox-title="<?php esc_attr_e( 'Automated workflow', 'jackmail-newsletters' ) ?>">
						</span>
					</div>
					<div ng-show="c.filter.emailing" class="jackmail_campaign_status_select" jackmail-dropdown-button button-value="<?php esc_attr_e( 'Status', 'jackmail-newsletters' ) ?>"
						titles-clicks-grid="c.campaigns_status"
						titles-clicks-grid-checked="[ c.filter.status.draft, c.filter.status.sent, c.filter.status.scheduled, c.filter.status.sending, c.filter.status.refused, c.filter.status.error ]"
						titles-clicks-grid-event="c.filter_status( key )">
					</div>
					<div jackmail-multiple-calendar
						on-confirm="c.change_filter_date" jackmail-option="day" jackmail-position="bottom"
						jackmail-refresh="true"
						selected-date1="{{c.filter.selected_date1}}" selected-date2="{{c.filter.selected_date2}}"
						class="jackmail_left">
					</div>
				</div>
				<div class="jackmail_previews_grid_container">
					<div>
						<div ng-repeat="( key, campaigns ) in c.campaigns_grid track by $index" class="jackmail_previews_grid_column">
							<div ng-repeat="( subkey, campaign ) in campaigns track by $index" class="jackmail_previews_grid_column_campaign_{{campaign.status | lowercase}}" ng-style="{'z-index': 1000 - subkey}">
								<span class="jackmail_previews_grid_preview">
									<img ng-src="{{campaign.preview}}" alt=""/>
								</span>
								<div>
									<div>
										<p class="jackmail_previews_grid_title">
											<span jackmail-content-editable ng-click="c.focus_campaign_name( campaign.name )"
												when-enter="c.save_campaign_name( campaign.id, campaign.type, campaign.name )"
												input-value="campaign.name"></span>
											<span ng-click="c.click_campaign_name( $event )" class="dashicons dashicons-edit"></span>
										</p>
										<p ng-show="campaign.type !== 'campaign'">({{campaign.send_option | scenarioType}})</p>
										<p>{{campaign.updated_date_gmt | formatedDate : 'gmt_to_timezone' : 'hours'}}</p>
										<p class="jackmail_previews_grid_by" ng-show="campaign.updated_by !== ''"><?php _e( 'by', 'jackmail-newsletters' ) ?> {{campaign.updated_by}}</p>
										<p class="jackmail_previews_grid_status">
											<span ng-if="campaign.status === 'REFUSED'" class="jackmail_thumbnail_campaign_{{campaign.status | lowercase}}"
											    jackmail-tooltip="{{c.get_display_campaign_status(campaign)}}">
												{{campaign.status | campaignStatus}}
											</span>
											<span ng-if="campaign.status !== 'REFUSED'" class="jackmail_thumbnail_campaign_{{campaign.status | lowercase}}">
												{{campaign.status | campaignStatus}}
											</span>
										</p>
										<div ng-show="campaign.type === 'campaign'" class="jackmail_previews_grid_buttons">
											<div class="jackmail_previews_grid_dropdown_actions">
												<div ng-hide="campaign.show_delete_confirmation" ng-mouseleave="c.hide_actions( key, subkey )">
													<div ng-click="c.display_actions( key, subkey )">
														<span><?php _e( 'Actions', 'jackmail-newsletters' ) ?></span>
														<span class="dashicons dashicons-arrow-down-alt2"></span>
													</div>
													<div ng-show="campaign.show_actions">
														<div>
															<span ng-show="campaign.status === 'DRAFT' || campaign.status === 'REFUSED'" ng-click="c.go_edit_page( 'campaign', campaign.id + '/contacts', campaign.json )">
																<?php _e( 'Edit', 'jackmail-newsletters' ) ?>
															</span>
															<span ng-click="c.duplicate_campaign( campaign.id )">
																<?php _e( 'Duplicate', 'jackmail-newsletters' ) ?>
															</span>
															<span ng-show="campaign.status === 'SENT'" ng-click="$root.change_page_with_parameters( 'statistics', 'campaign/' + campaign.id )">
																<?php _e( 'Statistics', 'jackmail-newsletters' ) ?>
															</span>
															<span ng-show="( campaign.status === 'PROCESS_SCHEDULED' || campaign.status === 'SCHEDULED' ) && ( ( campaign.send_option_date_begin_gmt | date1DiffDate2: $root.settings.current_time ) > 2700 )" ng-click="c.cancel_scheduled_campaign( campaign.id )">
																<?php _e( 'Cancel', 'jackmail-newsletters' ) ?>
															</span>
															<span ng-show="campaign.status === 'DRAFT' || campaign.status === 'SENT' || campaign.status === 'REFUSED' || campaign.status === 'ERROR'" ng-click="c.delete_confirm( key, subkey )">
																<?php _e( 'Delete', 'jackmail-newsletters' ) ?>
															</span>
														</div>
													</div>
												</div>
											</div>
											<p ng-show="campaign.show_delete_confirmation" class="jackmail_previews_grid_buttons_confirm">
												<span ng-click="c.delete_campaign( campaign.id )" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
												<span ng-click="c.delete_cancel( key, subkey )" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
											</p>
										</div>
										<div ng-show="campaign.type === 'scenario'" class="jackmail_previews_grid_buttons">
											<div class="jackmail_previews_grid_dropdown_actions">
												<div ng-hide="campaign.show_delete_confirmation" ng-mouseleave="c.hide_actions( key, subkey )">
													<div ng-click="c.display_actions( key, subkey )">
														<span><?php _e( 'Actions', 'jackmail-newsletters' ) ?></span>
														<span class="dashicons dashicons-arrow-down-alt2"></span>
													</div>
													<div ng-show="campaign.show_actions">
														<div>
															<span ng-click="c.go_edit_page( 'scenario', campaign.send_option + '/' + campaign.id + '/settings', campaign.json )">
																<?php _e( 'Edit', 'jackmail-newsletters' ) ?>
															</span>
															<span ng-show="campaign.status === 'ACTIVED'" ng-click="c.deactivate_scenario( campaign.id )">
																<?php _e( 'Deactivate', 'jackmail-newsletters' ) ?>
															</span>
															<span ng-click="c.delete_confirm( key, subkey )">
																<?php _e( 'Delete', 'jackmail-newsletters' ) ?>
															</span>
														</div>
													</div>
												</div>
											</div>
											<p ng-show="campaign.show_delete_confirmation" class="jackmail_previews_grid_buttons_confirm">
												<span ng-click="c.delete_scenario( campaign.id )" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
												<span ng-click="c.delete_cancel( key, subkey )" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
											</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div ng-show="c.nb_campaigns_grid === 0" class="jackmail_none">
					<p class="jackmail_none_title"><?php _e( 'No campaigns for this selection', 'jackmail-newsletters' ) ?></p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>