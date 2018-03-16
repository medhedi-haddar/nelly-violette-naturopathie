<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-show="lc.c_common.current_step_name === 'settings'" class="jackmail_content">
	<p class="jackmail_title jackmail_center"><?php _e( 'Settings', 'jackmail-newsletters' ) ?></p>
	<div class="jackmail_settings_campaign">
		<?php if ( $scenario_type === 'publish_a_post' ) { ?>
		<div class="jackmail_settings_campaign_left jackmail_settings_campaign_left_publish_a_post"></div>
		<div class="jackmail_settings_campaign_right">
			<p class="jackmail_bold"><?php _e( 'Trigger:', 'jackmail-newsletters' ) ?></p>
			<p class="jackmail_grey"><?php _e( 'Jackmail will automatically send an email to your list when you publish a new article according to your publication settings below.', 'jackmail-newsletters' ) ?></p>
			<br/>
			<p class="jackmail_bold"><?php _e( 'Which post type?', 'jackmail-newsletters' ) ?></p>
			<div jackmail-dropdown-button
				button-value="{{lc.only_scenario.post_type_selected}}"
				titles-clicks-json-hide-checkbox="true"
				titles-clicks-json="lc.only_scenario.post_type_available"
				titles-clicks-json-event="lc.only_scenario.post_type_check_unckeck( key, checked )">
			</div>
			<div ng-show="lc.c_common.campaign.post_type === 'post'">
				<br/>
				<p class="jackmail_bold"><?php _e( 'Which categories?', 'jackmail-newsletters' ) ?></p>
				<div jackmail-dropdown-button
					button-value="{{lc.only_scenario.selected_post_categories_title}}"
					titles-clicks-json="lc.only_scenario.post_categories_available"
					titles-clicks-json-event="lc.only_scenario.post_categories_check_unckeck( key, checked )">
				</div>
			</div>
			<br/>
			<div class="jackmail_settings_campaign_option_periodicity_container">
				<p class="jackmail_bold"><?php _e( 'When should it send?', 'jackmail-newsletters' ) ?></p>
				<div>
					<span ng-click="lc.shared_scenario.change_periodicity_type( 'NOW' )"
						jackmail-radio="lc.c_common.campaign.periodicity_type === 'NOW'"
						radio-title="<?php esc_attr_e( 'In the coming hour', 'jackmail-newsletters' ) ?>">
					</span>
				</div>
				<div>
					<span ng-click="lc.shared_scenario.change_periodicity_type( 'HOURS' )"
						jackmail-radio="lc.c_common.campaign.periodicity_type === 'HOURS' || lc.c_common.campaign.periodicity_type === 'DAYS'"
						radio-title="<?php esc_attr_e( 'Postpone the sending process', 'jackmail-newsletters' ) ?>">
					</span>
				</div>
				<div ng-show="lc.c_common.campaign.periodicity_type === 'HOURS' || lc.c_common.campaign.periodicity_type === 'DAYS'">
					<div class="jackmail_settings_campaign_option_periodicity">
						<span class="jackmail_settings_campaign_option_periodicity_begin"><?php _e( 'from', 'jackmail-newsletters' ) ?></span>
						<div class="jackmail_settings_campaign_option_periodicity_dropdown" jackmail-dropdown-button
						     button-value="{{lc.c_common.campaign.periodicity_value}}"
						     titles-clicks-array="lc.c_common.campaign.periodicity_type === 'HOURS' ? lc.shared_scenario.settings_hours_choice : lc.shared_scenario.settings_days_choice"
						     titles-clicks-array-event="lc.shared_scenario.select_periodicity_value( key )">
						</div>
						<div class="jackmail_settings_campaign_option_periodicity_dropdown" jackmail-dropdown-button
						     button-value="{{lc.c_common.campaign.periodicity_type === 'HOURS' ? ( lc.c_common.campaign.periodicity_value === '1' ? 'hour' : 'hours' ) : ( lc.c_common.campaign.periodicity_value === '1' ? 'day' : 'days' ) }}"
						     titles-clicks-array="[ ( lc.c_common.campaign.periodicity_value === '1' ? 'hour' : 'hours' ), ( lc.c_common.campaign.periodicity_value === '1' ? 'day' : 'days' ) ]"
						     titles-clicks-array-event="lc.shared_scenario.select_periodicity_type( key )">
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php } else if ( $scenario_type === 'automated_newsletter' || $scenario_type === 'woocommerce_automated_newsletter' ) { ?>
		<div class="jackmail_settings_campaign_left jackmail_settings_campaign_left_automated_newsletter"></div>
		<div class="jackmail_settings_campaign_right">
			<p class="jackmail_bold"><?php _e( 'Trigger:', 'jackmail-newsletters' ) ?></p>
			<p class="jackmail_grey">
				<?php if ( $scenario_type === 'automated_newsletter' ) {
					_e( 'Jackmail will automatically send an email to your contacts whenever you publish a new article based on the settings below.', 'jackmail-newsletters' );
				} else {
					_e( 'Jackmail will automatically send an email to your contacts whenever you publish a new product based on the settings below.', 'jackmail-newsletters' );
				} ?>
			</p>
			<?php if ( $scenario_type === 'automated_newsletter' ) { ?>
			<br/>
			<p class="jackmail_bold"><?php _e( 'Which post type?', 'jackmail-newsletters' ) ?></p>
			<div jackmail-dropdown-button
				button-value="{{lc.c_common.campaign.post_type}}"
				titles-clicks-json-hide-checkbox="true"
				titles-clicks-json="lc.only_scenario.post_type_available"
				titles-clicks-json-event="lc.only_scenario.post_type_check_unckeck( key, checked )">
			</div>
			<?php } ?>
			<br/>
			<div class="jackmail_settings_campaign_option_posts_container">
				<p class="jackmail_bold">
					<?php $scenario_type === 'automated_newsletter' ? _e( 'Number of articles:', 'jackmail-newsletters' ) : _e( 'Number of products:', 'jackmail-newsletters' ) ?>
				</p>
				<div class="jackmail_settings_campaign_option_posts">
					<div class="jackmail_settings_campaign_option_posts_dropdown" jackmail-dropdown-button
						button-value="{{lc.c_common.campaign.nb_posts_content}}"
						titles-clicks-array="lc.shared_scenario.settings_nb_posts_content_choice"
						titles-clicks-array-event="lc.shared_scenario.change_nb_posts_content( key )">
					</div>
					<?php if ( $scenario_type === 'automated_newsletter' ) { ?>
					<span class="jackmail_grey">
						<span ng-show="lc.c_common.campaign.post_type !== 'post'"><?php _e( 'latest articles', 'jackmail-newsletters' ) ?></span>
						<span ng-show="lc.c_common.campaign.post_type === 'post'"><?php _e( 'latest articles in', 'jackmail-newsletters' ) ?></span>
					</span>
					<?php } else { ?>
					<span class="jackmail_grey">
						<span><?php _e( 'latest products in', 'jackmail-newsletters' ) ?></span>
					</span>
					<?php } ?>
					<div<?php if ( $scenario_type === 'automated_newsletter') { ?> ng-show="lc.c_common.campaign.post_type === 'post'"<?php } ?>
						jackmail-dropdown-button
						button-value="{{lc.only_scenario.selected_post_categories_title}}"
						titles-clicks-json="lc.only_scenario.post_categories_available"
						titles-clicks-json-event="lc.only_scenario.post_categories_check_unckeck( key, checked )">
					</div>
				</div>
			</div>
			<br/>
			<div class="jackmail_settings_campaign_option_periodicity_container">
				<p class="jackmail_bold"><?php _e( 'Newsletter to be sent:', 'jackmail-newsletters' ) ?></p>
				<div>
					<span ng-click="lc.shared_scenario.change_periodicity_type( 'POSTS' )"
						jackmail-radio="lc.c_common.campaign.periodicity_type === 'POSTS'"
						radio-title="<?php esc_attr_e( 'As soon as I have', 'jackmail-newsletters' ) ?>">
					</span>
				</div>
				<div ng-show="lc.c_common.campaign.periodicity_type === 'POSTS'">
					<div class="jackmail_settings_campaign_option_periodicity">
						<div class="jackmail_settings_campaign_option_posts_dropdown" jackmail-dropdown-button
						     button-value="{{lc.c_common.campaign.periodicity_value}}"
						     titles-clicks-array="lc.shared_scenario.settings_nb_posts_periodicity_value_choice"
						     titles-clicks-array-event="lc.shared_scenario.change_nb_posts_periodicity_value( key )">
						</div>
						<span class="jackmail_settings_campaign_option_periodicity_end">
							<?php if ( $scenario_type === 'automated_newsletter' ) { ?>
							{{lc.c_common.campaign.periodicity_value > 1 ? '<?php esc_attr_e( 'new articles', 'jackmail-newsletters' ) ?>' : '<?php esc_attr_e( 'new article', 'jackmail-newsletters' ) ?>'}}
							<?php } else { ?>
							{{lc.c_common.campaign.periodicity_value > 1 ? '<?php esc_attr_e( 'new products', 'jackmail-newsletters' ) ?>' : '<?php esc_attr_e( 'new product', 'jackmail-newsletters' ) ?>'}}
							<?php } ?>
						</span>
					</div>
				</div>
				<div>
					<span ng-click="lc.shared_scenario.change_periodicity_type( 'DAYS' )"
						jackmail-radio="lc.c_common.campaign.periodicity_type === 'DAYS' || lc.c_common.campaign.periodicity_type === 'MONTHS'"
						radio-title="<?php esc_attr_e( 'Schedule recurring sending process', 'jackmail-newsletters' ) ?>">
					</span>
				</div>
				<div ng-show="lc.c_common.campaign.periodicity_type === 'DAYS' || lc.c_common.campaign.periodicity_type === 'MONTHS'">
					<div class="jackmail_settings_campaign_option_periodicity">
						<span class="jackmail_settings_campaign_option_periodicity_begin"><?php _e( 'from', 'jackmail-newsletters' ) ?></span>
						<div class="jackmail_settings_campaign_option_periodicity_dropdown" jackmail-dropdown-button
						     button-value="{{lc.c_common.campaign.periodicity_value}}"
						     titles-clicks-array="lc.c_common.campaign.periodicity_type === 'DAYS' ? lc.shared_scenario.settings_days_choice : lc.shared_scenario.settings_months_choice"
						     titles-clicks-array-event="lc.shared_scenario.select_periodicity_value( key )">
						</div>
						<div class="jackmail_settings_campaign_option_periodicity_dropdown" jackmail-dropdown-button
						     button-value="{{ lc.c_common.campaign.periodicity_type === 'DAYS' ? ( lc.c_common.campaign.periodicity_value === '1' ? '<?php esc_attr_e( 'day', 'jackmail-newsletters' ) ?>' : '<?php esc_attr_e( 'days', 'jackmail-newsletters' ) ?>' ) : ( lc.c_common.campaign.periodicity_value === '1' ? '<?php esc_attr_e( 'month', 'jackmail-newsletters' ) ?>' : '<?php esc_attr_e( 'months', 'jackmail-newsletters' ) ?>' ) }}"
						     titles-clicks-array="[ ( lc.c_common.campaign.periodicity_value === '1' ? '<?php esc_attr_e( 'day', 'jackmail-newsletters' ) ?>' : '<?php esc_attr_e( 'days', 'jackmail-newsletters' ) ?>' ), ( lc.c_common.campaign.periodicity_value === '1' ? '<?php esc_attr_e( 'month', 'jackmail-newsletters' ) ?>' : '<?php esc_attr_e( 'months', 'jackmail-newsletters' ) ?>' ) ]"
						     titles-clicks-array-event="lc.shared_scenario.select_periodicity_type( key )">
						</div>
					</div>
				</div>
			</div>
			<div ng-show="lc.c_common.campaign.periodicity_type == 'DAYS' || lc.c_common.campaign.periodicity_type == 'MONTHS'">
				<p class="jackmail_bold">
					<span ng-show="!lc.c_common.campaign.already_send"><?php _e( 'Date of first send:', 'jackmail-newsletters' ) ?></span>
					<span ng-show="lc.c_common.campaign.already_send"><?php _e( 'Date of next send:', 'jackmail-newsletters' ) ?></span>
				</p>
				<div jackmail-multiple-calendar on-confirm="lc.shared_scenario.change_event_date" jackmail-refresh="{{lc.c_common.campaign.periodicity_type}}"
				     jackmail-simple-calendar="true" jackmail-position="top" selected-date1="{{lc.c_common.campaign.event_date_gmt}}">
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<?php } ?>