<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="ListAndCampaignController as lc">
	<div class="jackmail_header_container">
		<div class="jackmail_header">
			<div>
				<div class="jackmail_header_menu" jackmail-header-menu></div>
				<div class="jackmail_name">
					<span jackmail-content-editable ng-click="lc.c_common.focus_campaign_name()" when-enter="lc.c_common.blur_campaign_name()" input-value="lc.c_common.campaign.name"></span>
					<span ng-hide="lc.common.name_editing" ng-click="lc.c_common.focus_campaign_name()" class="dashicons dashicons-edit"></span>
				</div>
				<div class="jackmail_header_buttons">
					<div jackmail-search></div>
					<?php if ( $campaign_type === 'campaign' ) { ?>
					<div ng-show="lc.c_common.current_content_email_type === 'emailbuilder' || lc.c_common.campaign.content_email_json !== ''" class="jackmail_campaign_save" jackmail-dropdown-button button-value="<?php esc_attr_e( 'Save', 'jackmail-newsletters' ) ?>"
					     titles-clicks-array="lc.only_campaign.save_choice_select_title"
					     titles-clicks-array-event="lc.only_campaign.save_campaign_or_create_template( key )">
					</div>
					<span ng-show="lc.c_common.current_content_email_type !== 'emailbuilder' && lc.c_common.campaign.content_email_json === ''" ng-click="lc.c_common.save_campaign( true )" class="jackmail_header_button"><?php _e( 'Save campaign', 'jackmail-newsletters' ) ?></span>
					<?php } else { ?>
					<span ng-click="lc.c_common.save_campaign( true )" class="jackmail_header_button"><?php _e( 'Save campaign', 'jackmail-newsletters' ) ?></span>
					<?php } ?>
				</div>
			</div>
			<div class="jackmail_step_header">
				<div>
					<span ng-show="lc.campaign_type === 'scenario'" ng-click="lc.c_common.go_step( 'settings' )" ng-class="lc.c_common.current_step_name === 'settings' ? 'jackmail_step_header_active' : 'jackmail_step_header_inactive'">
						<?php _e( 'Settings', 'jackmail-newsletters' ) ?>
					</span>
					<span ng-show="lc.campaign_type === 'scenario'" class="dashicons dashicons-arrow-right-alt2"></span>
					<span ng-click="lc.c_common.go_step( 'contacts' )" class="jackmail_footer_item" ng-class="lc.c_common.current_step_name === 'contacts' ? 'jackmail_step_header_active' : 'jackmail_step_header_inactive'">
						<?php _e( 'Contacts', 'jackmail-newsletters' ) ?>
					</span>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
					<span ng-click="lc.c_common.go_step( 'create' )" ng-class="lc.c_common.current_step_name === 'create' ? 'jackmail_step_header_active' : 'jackmail_step_header_inactive'">
						<?php _e( 'Create', 'jackmail-newsletters' ) ?>
					</span>
					<span class="dashicons dashicons-arrow-right-alt2"></span>
					<span ng-click="lc.c_common.go_step( 'checklist' )" ng-class="lc.c_common.current_step_name === 'checklist' ? 'jackmail_step_header_active' : 'jackmail_step_header_inactive'">
						<?php _e( 'Checklist', 'jackmail-newsletters' ) ?>
					</span>
				</div>
			</div>
		</div>
	</div>
	<div ng-hide="$root.show_help2">
		<?php if ( $campaign_type === 'scenario' ) { ?>
			<?php include_once plugin_dir_path( __FILE__ ) . 'campaign_type_scenario_settings.inc.php'; ?>
		<?php } ?>
		<div ng-show="lc.c_common.current_step_name === 'contacts'">
			<?php include_once plugin_dir_path( __FILE__ ) . 'list_and_campaign.inc.php'; ?>
		</div>
		<?php include_once plugin_dir_path( __FILE__ ) . 'campaign_create.inc.php'; ?>
		<?php include_once plugin_dir_path( __FILE__ ) . 'campaign_checklist.inc.php'; ?>
		<div ng-show="!lc.c_common.show_templates" class="jackmail_footer">
			<div>
				<div class="jackmail_footer_left">
					<?php if ( $campaign_type === 'campaign' ) { ?>
					<span ng-show="lc.c_common.current_step_name !== 'contacts' || lc.common.show_grid || lc.show_import_lists" ng-click="lc.c_common.current_step_name === 'contacts' ? lc.only_campaign.hide_grid() : lc.c_common.previous_step()">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
						<?php _e( 'Back', 'jackmail-newsletters' ) ?>
					</span>
					<?php } else { ?>
					<span ng-click="lc.c_common.current_step_name === 'settings' ? $root.change_page( 'scenario_choice' ) : lc.c_common.previous_step()">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
						<?php _e( 'Back', 'jackmail-newsletters' ) ?>
					</span>
					<?php } ?>
				</div>
				<div class="jackmail_footer_middle">
					<div>
						<span ng-show="lc.campaign_type === 'scenario'" ng-click="lc.c_common.go_step( 'settings' )" class="jackmail_footer_item" ng-class="lc.c_common.current_step_name === 'settings' ? 'jackmail_footer_active' : 'jackmail_footer_inactive'">
							<?php _e( 'Settings', 'jackmail-newsletters' ) ?>
						</span>
						<span ng-show="lc.campaign_type === 'scenario'" class="dashicons dashicons-arrow-right-alt2"></span>
						<span ng-click="lc.c_common.go_step( 'contacts' )" class="jackmail_footer_item" ng-class="lc.c_common.current_step_name === 'contacts' ? 'jackmail_footer_active' : 'jackmail_footer_inactive'">
							<?php _e( 'Contacts', 'jackmail-newsletters' ) ?>
						</span>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
						<span ng-click="lc.c_common.go_step( 'create' )" class="jackmail_footer_item" ng-class="lc.c_common.current_step_name === 'create' ? 'jackmail_footer_active' : 'jackmail_footer_inactive'">
							<?php _e( 'Create', 'jackmail-newsletters' ) ?>
						</span>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
						<span ng-click="lc.c_common.go_step( 'checklist' )" class="jackmail_footer_item" ng-class="lc.c_common.current_step_name === 'checklist' ? 'jackmail_footer_active' : 'jackmail_footer_inactive'">
							<?php _e( 'Checklist', 'jackmail-newsletters' ) ?>
						</span>
						<span class="jackmail_footer_active_border"></span>
					</div>
				</div>
				<div class="jackmail_footer_right jackmail_text_right">
					<span ng-show="lc.c_common.current_step_name !== 'checklist'" ng-click="lc.c_common.next_step()">
						<?php _e( 'Next', 'jackmail-newsletters' ) ?>
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</span>
				</div>
			</div>
		</div>
		<div ng-show="lc.common.show_name_popup" class="jackmail_confirmation">
			<div class="jackmail_confirmation_background"></div>
			<div class="jackmail_confirmation_message">
				<div>
					<p class="jackmail_title"><?php _e( 'What\'s your campaign name?', 'jackmail-newsletters' ) ?></p>
					<p class="jackmail_text_left"><?php _e( 'Name:', 'jackmail-newsletters' ) ?></p>
					<p><input ng-model="lc.c_common.campaign.name" ng-enter="lc.only_list.hide_list_name_popup()" ng-echap="lc.only_list.hide_list_name_popup()" class="jackmail_name_popup" type="text"/></p>
					<p>
						<span ng-click="lc.common.hide_name_popup()" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
						<span ng-click="lc.common.hide_name_popup()" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>