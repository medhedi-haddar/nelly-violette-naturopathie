<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="ListDetailController as ld">
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
			<div class="jackmail_email_list_detail_buttons_container">
				<div>
					<input ng-click="ld.go_back()" value="<?php esc_attr_e( 'Back', 'jackmail-newsletters' ) ?>" type="button"/>
				</div>
				<div jackmail-dropdown-button dropdown-left="true" button-value="{{ld.email_lists_detail_current_list_name}}"
				     titles-clicks-json="ld.email_lists_detail" titles-clicks-json-event="ld.select_email_list_details( key, checked )">
				</div>
				<div>
					<span><?php _e( 'Found in', 'jackmail-newsletters' ) ?></span>
					<span>{{ld.email_lists_detail.length}}</span>
					<span ng-hide="ld.email_lists_detail.length > 1"><?php _e( 'list', 'jackmail-newsletters' ) ?></span>
					<span ng-show="ld.email_lists_detail.length > 1"><?php _e( 'lists', 'jackmail-newsletters' ) ?></span>
				</div>
			</div>
			<div ng-repeat="( key, email_lists_detail ) in ld.email_lists_detail track by $index" ng-if="email_lists_detail.checked" class="jackmail_email_list_detail_container">
				<div class="jackmail_email_list_detail_block">
					<div ng-class="ld.show_list_contact_detail_columns ? 'jackmail_email_list_detail_mini' : ''" class="jackmail_email_list_detail_general">
						<div class="jackmail_email_list_detail_img"></div>
						<span class="jackmail_email_list_detail_email">{{ld.list_contact_detail_email}}</span>
						<span ng-show="email_lists_detail.contact.blacklist === '0'" class="jackmail_email_list_detail_active"><?php _e( 'Subscriber', 'jackmail-newsletters' ) ?></span>
						<span ng-show="email_lists_detail.contact.blacklist === '1'" class="jackmail_email_list_detail_unsubscribed"><?php _e( 'Unsubscribed', 'jackmail-newsletters' ) ?></span>
						<span ng-show="email_lists_detail.contact.blacklist === '2'" class="jackmail_email_list_detail_complained"><?php _e( 'Complained', 'jackmail-newsletters' ) ?></span>
						<span ng-show="email_lists_detail.contact.blacklist === '3'" class="jackmail_email_list_detail_hardbounced"><?php _e( 'Hardbounced', 'jackmail-newsletters' ) ?></span>
					</div>
					<div ng-click="ld.display_hide_list_contact_detail_columns()" class="jackmail_email_list_detail_view">
						<span class="jackmail_email_list_detail_list_name">{{email_lists_detail.name}}</span>
						<span ng-hide="ld.show_list_contact_detail_columns" class="dashicons dashicons-arrow-down-alt2"></span>
						<span ng-show="ld.show_list_contact_detail_columns" class="dashicons dashicons-arrow-up-alt2"></span>
					</div>
					<div ng-show="ld.show_list_contact_detail_columns" class="jackmail_email_list_detail_grid_container">
						<table class="jackmail_email_list_detail_grid">
							<tr ng-repeat="( subkey, field ) in email_lists_detail.contact track by $index" ng-if="subkey !== 'blacklist' && subkey !== 'id_list'">
								<td>{{subkey === 'email' ? '<?php echo esc_js( 'Email', 'jackmail-newsletters' ) ?>' : email_lists_detail.type === 'list' ? email_lists_detail.fields[ $index - 2 ] : email_lists_detail.fields[ $index - 3 ]}}</td>
								<td>
									<span ng-show="subkey === 'email'">{{email_lists_detail.contact[ subkey ]}}</span>
									<input ng-show="subkey !== 'email'" ng-model="email_lists_detail.contact[ subkey ]"
										ng-enter-up-down ng-blur="ld.update_detail_contact( key, subkey )"
										ng-keyup="ld.blur_contact( $event )" type="text"/>
								</td>
							</tr>
						</table>
					</div>
					<div ng-show="email_lists_detail.type === 'list'" class="jackmail_email_list_detail_statistics">
						<div>
							<span><?php _e( 'Sent', 'jackmail-newsletters' ) ?></span>
							<span>{{ld.nb_sends}}</span>
						</div>
						<div>
							<span><?php _e( 'Opened', 'jackmail-newsletters' ) ?></span>
							<span>{{ld.nb_opens}}</span>
						</div>
						<div>
							<span><?php _e( 'Clicked', 'jackmail-newsletters' ) ?></span>
							<span>{{ld.nb_clicks}}</span>
						</div>
					</div>
				</div>
				<div ng-show="email_lists_detail.type === 'list' && email_lists_detail.contact.blacklist === '0'" ng-click="ld.unsubscribe_contact( key )" class="jackmail_email_list_detail_unsubscribe">
					<span class="dashicons dashicons-no"></span>
					<span><?php _e( 'Unsubscribe this recipient', 'jackmail-newsletters' ) ?></span>
				</div>
				<div ng-show="email_lists_detail.type === 'list' && email_lists_detail.contact.blacklist !== '0'" ng-click="ld.unblacklist_contact( key )" class="jackmail_email_list_detail_unblacklist">
					<span class="dashicons dashicons-yes"></span>
					<span><?php _e( 'Include this recipient', 'jackmail-newsletters' ) ?></span>
				</div>
			</div>
			<div ng-show="ld.nb_synthesis_timeline > 0" class="jackmail_list_detail_timeline">
				<div class="jackmail_list_detail_timeline_line"></div>
				<div ng-repeat="timeline in ld.synthesis_timeline track by $index" ng-class="'jackmail_statistics_timeline_' + timeline.event" ng-style="{ 'top': 'calc(' + timeline.position + '% - 7px)' }">
					<div>
						<span ng-show="timeline.event === 'open'"><?php _e( 'Opened:', 'jackmail-newsletters' ) ?></span>
						<span ng-show="timeline.event === 'click'"><?php _e( 'Clicked:', 'jackmail-newsletters' ) ?></span>
						<span ng-show="timeline.event === 'unsubscribe'"><?php _e( 'Opted out:', 'jackmail-newsletters' ) ?></span>
						<br/>
						{{timeline.date | formatedDate : 'gmt_to_timezone' : 'hours'}}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>