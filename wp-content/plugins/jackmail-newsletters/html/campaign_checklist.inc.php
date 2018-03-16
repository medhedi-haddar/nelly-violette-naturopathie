<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-show="lc.c_common.current_step_name === 'checklist'" class="jackmail_content">
	<p class="jackmail_title jackmail_center"><?php _e( 'Check your campaign out', 'jackmail-newsletters' ) ?></p>
	<div class="jackmail_check_campaign jackmail_check_campaign_infos" ng-class="lc.c_common.campaign_data_checked ? '' : 'jackmail_check_campaign_infos_not_checked'">
		<div ng-class="lc.c_common.error.sender !== '' ? 'jackmail_check_campaign_with_error' : ''">
			<span>
				<span ng-click="lc.c_common.go_step_correct_sender( lc.c_common.error.sender )" class="jackmail_icon_ok dashicons" ng-class="lc.c_common.error.sender !== '' ? 'dashicons-no-alt' : 'dashicons-yes'" title="{{lc.c_common.error.sender}}"></span>
			</span>
			<span class="jackmail_bold jackmail_check_campaign_title"><?php _e( 'From', 'jackmail-newsletters' ) ?></span>
			<span class="jackmail_grey jackmail_check_campaign_data">
				<span>{{lc.c_common.campaign.sender_name}} <{{lc.c_common.campaign.sender_email}}></span>
				<span class="jackmail_check_campaign_error">{{lc.c_common.error.sender}}</span>
				<span ng-show="lc.c_common.error.sender_warning" ng-click="lc.c_common.go_step_correct_sender( true )" class="jackmail_check_campaign_warning_container">
					<br/>
					<span class="dashicons dashicons-info"></span>
					<span class="jackmail_check_campaign_warning"><?php _e( 'Jackmail highly recommands to use your own domain', 'jackmail-newsletters' ) ?></span>
				</span>
			</span>
			<span class="jackmail_ckeck_campaign_button">
				<input ng-click="lc.c_common.go_step_correct_sender( true )" type="button" class="jackmail_white_button" ng-value="lc.c_common.check_campaign_data_input_value( lc.c_common.error.sender )"/>
			</span>
		</div>
		<div ng-if="( lc.c_common.campaign.reply_to_name !== '' || lc.c_common.campaign.reply_to_email !== '' ) && ( lc.c_common.campaign.reply_to_name !== lc.c_common.campaign.sender_name || lc.c_common.campaign.reply_to_email !== lc.c_common.campaign.sender_email )"
		    ng-class="lc.c_common.error.reply_to !== '' ? 'jackmail_check_campaign_with_error' : ''" >
			<span>
				<span ng-click="lc.c_common.go_step_correct_reply_to( lc.c_common.error.reply_to )" class="jackmail_icon_ok dashicons" ng-class="lc.c_common.error.reply_to !== '' ? 'dashicons-no-alt' : 'dashicons-yes'" title="{{lc.c_common.error.reply_to}}"></span>
			</span>
			<span class="jackmail_bold jackmail_check_campaign_title"><?php _e( 'Reply to', 'jackmail-newsletters' ) ?></span>
			<span class="jackmail_grey jackmail_check_campaign_data">
				<span>{{lc.c_common.campaign.reply_to_name}} <{{lc.c_common.campaign.reply_to_email}}></span>
				<span class="jackmail_check_campaign_error">{{lc.c_common.error.reply_to}}</span>
			</span>
			<span class="jackmail_ckeck_campaign_button">
				<input ng-click="lc.c_common.go_step_correct_reply_to( true )" type="button" class="jackmail_white_button" ng-value="lc.c_common.check_campaign_data_input_value( lc.c_common.error.reply_to )"/>
			</span>
		</div>
		<?php if ( $campaign_type === 'campaign' ) { ?>
		<div ng-class="lc.c_common.error.recipients !== '' ? 'jackmail_check_campaign_with_error' : ''">
			<span>
				<span ng-click="lc.c_common.go_step_correct_recipients( lc.c_common.error.recipients )" class="jackmail_icon_ok dashicons" ng-class="lc.c_common.error.recipients !== '' ? 'dashicons-no-alt' : 'dashicons-yes'" title="{{lc.c_common.error.recipients}}"></span>
			</span>
			<span class="jackmail_bold jackmail_check_campaign_title">
				<span ng-hide="lc.shared_campaign.checked_campaign_data.nb_contacts_valids > 1"><?php _e( 'Recipient', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.shared_campaign.checked_campaign_data.nb_contacts_valids > 1"><?php _e( 'Recipients', 'jackmail-newsletters' ) ?></span>
			</span>
			<span class="jackmail_grey jackmail_check_campaign_data">
				<span>
					{{lc.shared_campaign.checked_campaign_data.nb_contacts_valids | numberSeparator}}
					<span ng-hide="lc.shared_campaign.checked_campaign_data.nb_contacts_valids > 1"><?php _e( 'contact', 'jackmail-newsletters' ) ?></span>
					<span ng-show="lc.shared_campaign.checked_campaign_data.nb_contacts_valids > 1"><?php _e( 'contacts', 'jackmail-newsletters' ) ?></span>
				</span>
				<span class="jackmail_check_campaign_error">{{lc.c_common.error.recipients}}</span>
			</span>
			<span class="jackmail_ckeck_campaign_button">
				<input ng-click="lc.c_common.go_step_correct_recipients( true )" type="button" class="jackmail_white_button" ng-value="lc.c_common.check_campaign_data_input_value( lc.c_common.error.recipients )"/>
			</span>
		</div>
		<?php } else { ?>
		<div ng-class="lc.c_common.error.recipients !== '' ? 'jackmail_check_campaign_with_error' : ''">
			<span>
				<span ng-click="lc.c_common.go_step_correct_recipients( lc.c_common.error.recipients )" class="jackmail_icon_ok dashicons" ng-class="lc.c_common.error.recipients !== '' ? 'dashicons-no-alt' : 'dashicons-yes'" title="{{lc.c_common.error.recipients}}"></span>
			</span>
			<span class="jackmail_bold jackmail_check_campaign_title">
				<span ng-hide="lc.c_common.nb_selected_lists > 1"><?php _e( 'List', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.c_common.nb_selected_lists > 1"><?php _e( 'Lists', 'jackmail-newsletters' ) ?></span>
			</span>
			<span class="jackmail_grey jackmail_check_campaign_data">
				<span>
					{{lc.c_common.nb_selected_lists | numberSeparator}}
					<span ng-hide="lc.c_common.nb_selected_lists > 1"><?php _e( 'list', 'jackmail-newsletters' ) ?></span>
					<span ng-show="lc.c_common.nb_selected_lists > 1"><?php _e( 'lists', 'jackmail-newsletters' ) ?></span>
				</span>
				<span class="jackmail_check_campaign_error">{{lc.c_common.error.recipients}}</span>
			</span>
			<span class="jackmail_ckeck_campaign_button">
				<input ng-click="lc.c_common.go_step_correct_recipients( true )" type="button" class="jackmail_white_button" ng-value="lc.c_common.check_campaign_data_input_value( lc.c_common.error.recipients )"/>
			</span>
		</div>
		<?php } ?>
		<div ng-class="lc.c_common.error.object !== '' ? 'jackmail_check_campaign_with_error' : ''">
			<span>
				<span ng-click="lc.c_common.go_step_correct_object( lc.c_common.error.object )" class="jackmail_icon_ok dashicons" ng-class="lc.c_common.error.object !== '' ? 'dashicons-no-alt' : 'dashicons-yes'" title="{{lc.c_common.error.object}}"></span>
			</span>
			<span class="jackmail_bold jackmail_check_campaign_title"><?php _e( 'Subject', 'jackmail-newsletters' ) ?></span>
			<span class="jackmail_grey jackmail_check_campaign_data">
				<span ng-bind-html="lc.c_common.object_trust_html( lc.c_common.campaign.object )"></span>
				<span class="jackmail_check_campaign_error">{{lc.c_common.error.object}}</span>
			</span>
			<span class="jackmail_ckeck_campaign_button">
				<input ng-click="lc.c_common.go_step_correct_object( true )" type="button" class="jackmail_white_button" ng-value="lc.c_common.check_campaign_data_input_value( lc.c_common.error.object )"/>
			</span>
		</div>
		<div ng-class="lc.c_common.error.content_email !== '' ? 'jackmail_check_campaign_with_error' : ''">
			<span>
				<span ng-click="lc.c_common.go_step_correct_content_email( lc.c_common.error.content_email )" class="jackmail_icon_ok dashicons" ng-class="lc.c_common.error.content_email !== '' ? 'dashicons-no-alt' : 'dashicons-yes'" title="{{lc.c_common.error.content_email}}"></span>
			</span>
			<span class="jackmail_bold jackmail_check_campaign_title"><?php _e( 'Message', 'jackmail-newsletters' ) ?></span>
			<span class="jackmail_grey jackmail_check_campaign_data">
				<span>
					{{lc.c_common.content_email_types}}
					<br/>
				</span>
				<span ng-hide="lc.c_common.content_email_unsubscribe_link || lc.c_common.error.content_email !== ''" ng-click="lc.c_common.go_step_correct_content_email( true )" class="jackmail_check_campaign_warning jackmail_check_campaign_warning_unsubscribe_link">
					<?php _e( 'Warning: No unsubscribe link', 'jackmail-newsletters' ) ?>
				</span>
				<span class="jackmail_check_campaign_error">
					{{lc.c_common.error.content_email}}
				</span>
			</span>
			<span ng-show="lc.c_common.content_email_unsubscribe_link" class="jackmail_ckeck_campaign_button">
				<input ng-click="lc.c_common.go_step_correct_content_email( true )" type="button" class="jackmail_white_button" ng-value="lc.c_common.check_campaign_data_input_value( lc.c_common.error.content_email )"/>
			</span>
			<span ng-show="!lc.c_common.content_email_unsubscribe_link" class="jackmail_ckeck_campaign_button jackmail_check_campaign_with_error">
				<input ng-click="lc.c_common.go_step_correct_content_email( true )" type="button" class="jackmail_white_button" value="<?php echo esc_attr_e( 'Correct', 'jackmail-newsletters' ) ?>"/>
			</span>
		</div>
		<div ng-if="lc.c_common.customized_columns_used.length > 0">
			<span>
				<span ng-click="lc.c_common.go_step_correct_content_email( lc.c_common.error.content_email )" class="jackmail_icon_ok dashicons dashicons-yes"></span>
			</span>
			<span class="jackmail_bold jackmail_check_campaign_title"><?php _e( 'Customized columns', 'jackmail-newsletters' ) ?></span>
			<span class="jackmail_grey jackmail_check_campaign_data">
				<span ng-repeat="(key, customized_column) in lc.c_common.customized_columns_used">
					{{customized_column}}<span ng-show="key < lc.c_common.customized_columns_used.length - 1">, </span>
				</span>
				<span ng-show="lc.c_common.customized_columns_unknown.length > 0" ng-click="lc.c_common.go_step_correct_content_email( true )" class="jackmail_check_campaign_warning">
					<br/>
					<?php _e( 'Warning: unknown columns found:', 'jackmail-newsletters' ) ?>
					<br/>
					<span ng-repeat="(key, customized_column) in lc.c_common.customized_columns_unknown">
						{{customized_column}}<span ng-show="key < lc.c_common.customized_columns_unknown.length - 1">, </span>
					</span>
				</span>
			</span>
			<span class="jackmail_ckeck_campaign_button">
				<input ng-click="lc.c_common.go_step_correct_content_email( true )" type="button" class="jackmail_white_button" value="<?php echo esc_attr_e( 'Edit', 'jackmail-newsletters' ) ?>"/>
			</span>
		</div>
		<div>
			<span>
				<span class="jackmail_icon_ok dashicons" ng-class="lc.c_common.campaign.link_tracking === '0' ? 'dashicons-no-alt' : 'dashicons-yes'"></span>
			</span>
			<span class="jackmail_bold jackmail_check_campaign_title"><?php _e( 'Tracking', 'jackmail-newsletters' ) ?></span>
			<span class="jackmail_grey jackmail_check_campaign_data">
				<span ng-show="lc.c_common.campaign.link_tracking === '1'">
					<span><?php _e( 'Tracking is enabled', 'jackmail-newsletters' ) ?></span>
					<span>({{lc.c_common.content_email_nb_links}}</span>
					<span ng-hide="lc.c_common.content_email_nb_links > 1"><?php _e( 'link', 'jackmail-newsletters' ) ?>)</span>
					<span ng-show="lc.c_common.content_email_nb_links > 1"><?php _e( 'links', 'jackmail-newsletters' ) ?>)</span>
				</span>
				<span ng-show="lc.c_common.campaign.link_tracking === '0'"><?php _e( 'Tracking is disabled', 'jackmail-newsletters' ) ?></span>
				<br/>
				<span ng-show="lc.shared_campaign.checked_campaign_data.domain_is_valid === false" class="jackmail_domain_info_available">
					<span><?php _e( 'Custom domain is actually not available', 'jackmail-newsletters' ) ?></span>
				</span>
			</span>
			<span class="jackmail_ckeck_campaign_button">
				<input ng-show="lc.c_common.campaign.link_tracking === '1'" ng-click="lc.c_common.deactivate_link_tracking()" type="button" class="jackmail_white_button" value="<?php _e( 'Deactivate', 'jackmail-newsletters' ) ?>"/>
				<input ng-show="lc.c_common.campaign.link_tracking === '0'" ng-click="lc.c_common.activate_link_tracking()" type="button" class="jackmail_green_button" value="<?php _e( 'Activate', 'jackmail-newsletters' ) ?>"/>
			</span>
		</div>
	</div>
	<?php if ( $campaign_type === 'campaign' ) { ?>
		<?php include_once plugin_dir_path( __FILE__ ) . 'campaign_type_campaign_checklist.inc.php'; ?>
	<?php } else if ( $campaign_type === 'scenario' ) { ?>
		<?php include_once plugin_dir_path( __FILE__ ) . 'campaign_type_scenario_checklist.inc.php'; ?>
	<?php } ?>
</div>
<?php } ?>