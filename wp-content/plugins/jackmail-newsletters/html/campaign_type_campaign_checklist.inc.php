<?php if ( defined( 'ABSPATH' ) ) { ?>
<div>
	<p class="jackmail_title jackmail_center"><?php _e( 'Measure your spam score', 'jackmail-newsletters' ) ?></p>
	<div class="jackmail_check_campaign jackmail_check_campaign_analysis">
		<div ng-show="lc.c_common.campaign_data_analysis_checked" class="jackmail_center">
			<p>
				<span ng-show="lc.shared_campaign.checked_campaign_data.analysis_checked">
					<span ng-class="lc.shared_campaign.checked_campaign_data.analysis.score <= 5 ? 'jackmail_check_campaign_analysis_not_ok' : 'jackmail_check_campaign_analysis_ok'" style="font-size:50px;">
						{{lc.shared_campaign.checked_campaign_data.analysis.score | numberSeparator}}
					</span>
					<span class="jackmail_check_campaign_analysis_max">/10</span>
				</span>
			</p>
			<p><?php _e( 'You could be considered spam if your score is lower than 5', 'jackmail-newsletters' ) ?></p>
			<p ng-show="lc.shared_campaign.checked_campaign_data.analysis.improvements.length > 0">
				<span ng-show="lc.shared_campaign.checked_campaign_data.analysis.improvements.length === 1"><?php _e( 'This reason can explain your score:', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.shared_campaign.checked_campaign_data.analysis.improvements.length > 1"><?php _e( 'These reasons can explain your score:', 'jackmail-newsletters' ) ?></span>
				<span ng-repeat="improvement in lc.shared_campaign.checked_campaign_data.analysis.improvements">
					<br/>
					{{improvement}}
				</span>
			</p>
			<div ng-show="lc.shared_campaign.checked_campaign_data.analysis_checked">
				<div class="jackmail_check_campaign_analysis_bar_container">
					<div class="jackmail_check_campaign_analysis_bar1"></div>
					<div class="jackmail_check_campaign_analysis_bar2" ng-style="{'width': ( ( 10 - lc.shared_campaign.checked_campaign_data.analysis.score ) * 10 ) + '%'}"></div>
				</div>
			</div>
			<div ng-show="!lc.shared_campaign.checked_campaign_data.analysis_checked" class="jackmail_center">
				<p><?php _e( 'An error occurred while analyzing the anti-spam score', 'jackmail-newsletters' ) ?></p>
				<input ng-click="lc.c_common.check_campaign_data_ws()" class="jackmail_green_button" type="button" value="<?php esc_attr_e( 'Check again', 'jackmail-newsletters' ) ?>"/>
			</div>
		</div>
	</div>
	<?php include_once plugin_dir_path( __FILE__ ) . 'campaign_checklist_test.inc.php'; ?>
	<p class="jackmail_title jackmail_center"><?php _e( 'Send the campaign', 'jackmail-newsletters' ) ?></p>
	<div class="jackmail_check_campaign jackmail_mb_100">
		<p class="jackmail_send_campaign_option">
			<span ng-click="lc.only_campaign.change_campaign_option( 'NOW' )"
				jackmail-radio="lc.c_common.campaign.send_option === 'NOW'"
				radio-title="<?php esc_attr_e( 'Send immediately', 'jackmail-newsletters' ) ?>">
			</span>
			<span ng-click="lc.only_campaign.change_campaign_option( 'DATE' )"
				jackmail-radio="lc.c_common.campaign.send_option === 'DATE'"
				radio-title="<?php esc_attr_e( 'Schedule the campaign', 'jackmail-newsletters' ) ?>">
			</span>
		</p>
		<div ng-show="lc.c_common.campaign.send_option === 'DATE'" class="jackmail_center ng-hide-animate">
			<div jackmail-multiple-calendar on-confirm="lc.only_campaign.change_send_option_date" jackmail-refresh="{{lc.c_common.campaign.send_option}}"
			     jackmail-simple-calendar="true" jackmail-position="top"
			     selected-date1="{{lc.c_common.campaign.send_option_date_begin_gmt | formatedDate : 'gmt_to_timezone' : 'sql'}}" selected-date2="{{lc.c_common.campaign.send_option_date_end_gmt | formatedDate : 'gmt_to_timezone' : 'sql'}}">
			</div>
		</div>
		<div ng-show="$root.settings.is_authenticated">
			<div ng-show="lc.c_common.campaign_data_checked && lc.shared_campaign.checked_campaign_data.nb_contacts_valids !== '0'">
				<div ng-show="lc.shared_campaign.checked_campaign_data.nb_credits_after >= 0" class="jackmail_center">
					<span>
						<span><?php _e( 'This campaign will use', 'jackmail-newsletters' ) ?></span>
						<span>{{lc.shared_campaign.checked_campaign_data.nb_contacts_valids | numberSeparator}}</span>
						<span ng-hide="lc.shared_campaign.checked_campaign_data.nb_contacts_valids > 1"><?php _e( 'credit.', 'jackmail-newsletters' ) ?></span>
						<span ng-show="lc.shared_campaign.checked_campaign_data.nb_contacts_valids > 1"><?php _e( 'credits.', 'jackmail-newsletters' ) ?></span>
					</span>
					<br/>
					<span ng-show="lc.shared_campaign.checked_campaign_data.nb_credits_checked">
						<span><?php _e( 'Once this campaign is sent, there will be', 'jackmail-newsletters' ) ?></span>
						<span>{{lc.shared_campaign.checked_campaign_data.nb_credits_after | numberSeparator}}</span>
						<span ng-hide="lc.shared_campaign.checked_campaign_data.nb_credits_after > 1"><?php _e( 'credit.', 'jackmail-newsletters' ) ?></span>
						<span ng-show="lc.shared_campaign.checked_campaign_data.nb_credits_after > 1"><?php _e( 'credits.', 'jackmail-newsletters' ) ?></span>
					</span>
					<span ng-show="!lc.shared_campaign.checked_campaign_data.nb_credits_checked">
						<?php _e( 'Error while checking the extra credits left.', 'jackmail-newsletters' ) ?>
						<input ng-click="lc.c_common.check_campaign_data_ws()" class="jackmail_green_button" type="button" value="<?php esc_attr_e( 'Refresh', 'jackmail-newsletters' ) ?>"/>
					</span>
				</div>
				<div ng-show="lc.shared_campaign.checked_campaign_data.nb_credits_after < 0" class="jackmail_center jackmail_not_enough_credits">
					<span ng-show="lc.shared_campaign.checked_campaign_data.subscription_type !== 'FREE'">
						<?php _e( 'You don\'t have enough credits to send your campaign.', 'jackmail-newsletters' ) ?>
						<br/>
						<a href="https://www.jackmail.com/pricing" ng-click="lc.only_campaign.not_enought_credits_link()" target="_blank"><?php _e( 'Click here to buy new credits.', 'jackmail-newsletters' ) ?></a>
					</span>
					<span ng-show="lc.shared_campaign.checked_campaign_data.subscription_type === 'FREE'">
						<?php _e( 'Your current plan doesn\'t allow you to send more than 100 emails.', 'jackmail-newsletters' ) ?>
						<br/>
						<?php _e( 'To be able to send your campaign, <a href="https://www.jackmail.com/pricing" ng-click="lc.only_campaign.not_enought_credits_link()" target="_blank">subscribe a premium plan</a>, or reduce your contact list', 'jackmail-newsletters' ) ?>.
					</span>
				</div>
			</div>
			<div ng-show="lc.c_common.campaign.send_option !== 'NOW' && !lc.c_common.sending_campaign" class="jackmail_campaign_big_button_container">
				<div ng-click="lc.only_campaign.send_campaign_confirmation_validation( 'program' )" class="jackmail_campaign_big_button jackmail_campaign_big_green_button">
					<?php _e( 'Schedule the campaign', 'jackmail-newsletters' ) ?>
				</div>
			</div>
			<div ng-show="lc.c_common.campaign.send_option === 'NOW' && !lc.c_common.sending_campaign" class="jackmail_campaign_big_button_container">
				<div ng-click="lc.only_campaign.send_campaign_confirmation_validation( 'send' )" class="jackmail_campaign_big_button jackmail_campaign_big_green_button">
					<?php _e( 'Send the campaign', 'jackmail-newsletters' ) ?>
				</div>
			</div>
            <div ng-show="lc.c_common.sending_campaign" class="jackmail_sending_campaign"><?php _e( 'Please wait, your campaign is in the process of being send out to the Jackmail platform...', 'jackmail-newsletters' ) ?></div>
		</div>
		<div ng-hide="$root.settings.is_authenticated" class="jackmail_center">
			<p>
				<?php _e( 'Please register or sign up with your Jackmail account to send a campaign.', 'jackmail-newsletters' ) ?>
			</p>
			<p>
				<input ng-click="$root.display_account_connection_popup( 'create' )" class="jackmail_green_button" value="<?php esc_attr_e( 'Create an account', 'jackmail-newsletters' ) ?>" type="button"/>
				<br/>
				<span ng-click="$root.display_account_connection_popup( 'connection' )" class="jackmail_connect_account"><?php _e( 'Sign in to my account', 'jackmail-newsletters' ) ?></span>
			</p>
		</div>
	</div>
</div>
<?php } ?>