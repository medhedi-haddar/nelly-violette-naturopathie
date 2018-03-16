<?php if ( defined( 'ABSPATH' ) ) { ?>
<div>
	<?php include_once plugin_dir_path( __FILE__ ) . 'campaign_checklist_test.inc.php'; ?>
	<div class="jackmail_check_campaign jackmail_mt_50 jackmail_mb_100">
		<?php if ( $scenario_type === 'publish_a_post' ) { ?>
		<p class="jackmail_center">
			<span><?php _e( 'Send an email', 'jackmail-newsletters' ) ?></span>
			<span ng-show="lc.c_common.campaign.periodicity_type === 'NOW'"><?php _e( 'when', 'jackmail-newsletters' ) ?></span>
			<span ng-show="lc.c_common.campaign.periodicity_type === 'HOURS'">
				{{lc.c_common.campaign.periodicity_value}}
				<span ng-show="lc.c_common.campaign.periodicity_value === '1'"><?php _e( 'hour', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.c_common.campaign.periodicity_value !== '1'"><?php _e( 'hours', 'jackmail-newsletters' ) ?></span>
				<span><?php _e( 'after', 'jackmail-newsletters' ) ?></span>
			</span>
			<span ng-show="lc.c_common.campaign.periodicity_type === 'DAYS'">
				{{lc.c_common.campaign.periodicity_value}}
				<span ng-show="lc.c_common.campaign.periodicity_value === '1'"><?php _e( 'day', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.c_common.campaign.periodicity_value !== '1'"><?php _e( 'days', 'jackmail-newsletters' ) ?></span>
				<span><?php _e( 'after', 'jackmail-newsletters' ) ?></span>
			</span>
			<span ng-show="lc.only_scenario.nb_selected_post_categories > 1"><?php _e( 'an article has been published in one of the following categories:', 'jackmail-newsletters' ) ?></span>
			<span ng-show="lc.only_scenario.nb_selected_post_categories === 1"><?php _e( 'an article has been published in the following category:', 'jackmail-newsletters' ) ?></span>
			<span ng-show="lc.only_scenario.nb_selected_post_categories === 0"><?php _e( 'an article has been published in all selected categories.', 'jackmail-newsletters' ) ?></span>
			<span ng-show="lc.only_scenario.nb_selected_post_categories >= 1">
				<br/>
				<span ng-repeat="( key, post_category ) in lc.only_scenario.post_categories_available | filter:{ checked: true } track by $index">
					<span>{{post_category.name}}</span><span ng-show="( key + 1 ) < lc.only_scenario.nb_selected_post_categories">, </span>
				</span>
			</span>
		</p>
		<?php } else if ( $scenario_type === 'automated_newsletter' || $scenario_type === 'woocommerce_automated_newsletter' ) { ?>
		<p class="jackmail_center">
			<span ng-show="lc.c_common.campaign.periodicity_type === 'POSTS'">
				<span><?php _e( 'Your campaign will be sent', 'jackmail-newsletters' ) ?></span>
				<?php if ( $scenario_type === 'automated_newsletter' ) { ?>
				<span ng-show="lc.c_common.campaign.periodicity_value === '1'"><?php _e( 'when a new article is published.', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.c_common.campaign.periodicity_value !== '1'"><?php _e( 'as soon as', 'jackmail-newsletters' ) ?> {{lc.c_common.campaign.periodicity_value}} <?php _e( 'new articles are published.', 'jackmail-newsletters' ) ?></span>
				<?php } else { ?>
				<span ng-show="lc.c_common.campaign.periodicity_value === '1'"><?php _e( 'when a new product is published.', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.c_common.campaign.periodicity_value !== '1'"><?php _e( 'as soon as', 'jackmail-newsletters' ) ?> {{lc.c_common.campaign.periodicity_value}} <?php _e( 'new products are published.', 'jackmail-newsletters' ) ?></span>
				<?php } ?>
			</span>
			<span ng-show="lc.c_common.campaign.periodicity_type === 'DAYS' || lc.c_common.campaign.periodicity_type === 'MONTHS'">
				<span><?php _e( 'Your campaign will be sent from', 'jackmail-newsletters' ) ?></span>
				<span>{{lc.c_common.campaign.event_date_gmt | formatedDate : 'gmt_to_timezone' : 'hours'}},</span>
				<span><?php _e( 'on and every', 'jackmail-newsletters' ) ?> {{lc.c_common.campaign.periodicity_value}}</span>
				<span ng-show="lc.c_common.campaign.periodicity_type === 'DAYS'">
					<span ng-show="lc.c_common.campaign.periodicity_value === '1'"><?php _e( 'day.', 'jackmail-newsletters' ) ?></span>
					<span ng-show="lc.c_common.campaign.periodicity_value !== '1'"><?php _e( 'days.', 'jackmail-newsletters' ) ?></span>
				</span>
				<span ng-show="lc.c_common.campaign.periodicity_type === 'MONTHS'">
					<span ng-show="lc.c_common.campaign.periodicity_value === '1'"><?php _e( 'month.', 'jackmail-newsletters' ) ?></span>
					<span ng-show="lc.c_common.campaign.periodicity_value !== '1'"><?php _e( 'months.', 'jackmail-newsletters' ) ?></span>
				</span>
				<br/>
				<span class="jackmail_grey jackmail_campaign_checklist_info">
					<span class="dashicons dashicons-editor-help"></span>
					<span><?php _e( 'In case there are fewer articles published than scheduled in your workflow,<br/>the sending process will be postponed to next deadline.', 'jackmail-newsletters' ) ?></span>
				</span>
			</span>
		</p>
		<?php }  ?>
		<div ng-show="!lc.only_scenario.activating_or_desactivating_scenario">
			<p class="jackmail_center">
				<span><?php _e( 'Status:', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.c_common.campaign.status === 'DRAFT'" class="jackmail_text_red"><?php _e( 'Draft', 'jackmail-newsletters' ) ?></span>
				<span ng-show="lc.c_common.campaign.status === 'ACTIVED'" class="jackmail_text_green"><?php _e( 'Actived', 'jackmail-newsletters' ) ?></span>
			</p>
			<div ng-show="lc.c_common.campaign.status === 'DRAFT' && $root.settings.is_authenticated" class="jackmail_campaign_big_button_container jackmail_campaign_scenario_big_button_container">
				<div ng-click="lc.only_scenario.activate_scenario_confirmation_validation()" class="jackmail_campaign_big_button"
				    ng-class="( lc.c_common.error.recipients !== '' || lc.c_common.error.sender !== '' || lc.c_common.error.reply_to !== '' || lc.c_common.error.object !== '' || lc.c_common.error.content_email !== '' ) ? 'jackmail_campaign_big_red_button' : 'jackmail_campaign_big_green_button'">
					<span class="dashicons dashicons-yes"></span>
					<span class="dashicons dashicons-no-alt"></span>
					<?php _e( 'Activate workflow', 'jackmail-newsletters' ) ?>
				</div>
			</div>
			<div ng-hide="$root.settings.is_authenticated" class="jackmail_center">
				<p>
					<?php _e( 'To activate a workflow you have to be logged in the Jackmail settings.', 'jackmail-newsletters' ) ?>
				</p>
				<p>
					<input ng-click="$root.display_account_connection_popup( 'create' )" class="jackmail_green_button" value="<?php esc_attr_e( 'Create an account', 'jackmail-newsletters' ) ?>" type="button"/>
					<br/>
					<span ng-click="$root.display_account_connection_popup( 'connection' )" class="jackmail_connect_account"><?php _e( 'Sign in to my account', 'jackmail-newsletters' ) ?></span>
				</p>
			</div>
			<div ng-show="lc.c_common.campaign.status === 'ACTIVED'" class="jackmail_campaign_big_button_container jackmail_campaign_scenario_big_button_container">
				<div ng-click="lc.only_scenario.deactivate_scenario_confirmation_validation()" class="jackmail_campaign_big_button jackmail_campaign_big_red_button">
					<span class="dashicons dashicons-no-alt"></span>
					<?php _e( 'Deactivate workflow', 'jackmail-newsletters' ) ?>
				</div>
			</div>
		</div>
		<div ng-show="lc.only_scenario.activating_or_desactivating_scenario" class="jackmail_sending_campaign"><?php _e( 'Loading...', 'jackmail-newsletters' ) ?></div>
	</div>
</div>
<?php } ?>