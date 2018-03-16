<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-show="lc.c_common.current_step_name === 'create' && lc.c_common.show_templates">
	<?php
	$current_page = 'campaign';
	include_once plugin_dir_path( __FILE__ ) . 'templates.php';
	?>
</div>
<div ng-show="lc.c_common.current_step_name === 'create' && !lc.c_common.show_templates">
	<div class="jackmail_campaign_header jackmail_post_header">
		<span ng-click="lc.c_common.display_hide_reply_to()" class="jackmail_grey">
			<span ng-hide="lc.c_common.show_reply_to" class="jackmail_option_up_down">
				<?php _e( 'Option', 'jackmail-newsletters' ) ?>
				<span class="dashicons dashicons-arrow-down-alt2"></span>
			</span>
			<span ng-show="lc.c_common.show_reply_to" class="jackmail_option_up_down">
				<?php _e( 'Hide', 'jackmail-newsletters' ) ?>
				<span class="dashicons dashicons-arrow-up-alt2"></span>
			</span>
		</span>
		<div>
			<span class="jackmail_grey">
				<span class="jackmail_info dashicons dashicons-info" jackmail-tooltip="<?php esc_attr_e( 'The name appears as the sender', 'jackmail-newsletters' ) ?>"></span>
				<?php _e( 'Sender:', 'jackmail-newsletters' ) ?>
			</span>
			<span class="jackmail_bold">
				<span jackmail-content-editable input-value="lc.c_common.campaign.sender_name" place-holder="<?php esc_attr_e( 'Sender', 'jackmail-newsletters' ) ?>"></span>
				<span jackmail-content-editable input-value="lc.c_common.campaign.sender_email" before="<" after=">" place-holder="<?php esc_attr_e( 'sender@example.com', 'jackmail-newsletters' ) ?>" class="jackmail_m_l_20"></span>
			</span>
			<span class="jackmail_right">
				<span class="jackmail_grey"><?php _e( 'To:', 'jackmail-newsletters' ) ?></span>
				<span class="jackmail_bold">
					{{lc.common.list.nb_contacts}}
					<span ng-hide="lc.common.list.nb_contacts > 1"><?php _e( 'contact', 'jackmail-newsletters' ) ?></span>
					<span ng-show="lc.common.list.nb_contacts > 1"><?php _e( 'contacts', 'jackmail-newsletters' ) ?></span>
					<span ng-click="lc.c_common.go_step( 'contacts' )" class="jackmail_link jackmail_pl_5"><?php _e( 'See / Edit', 'jackmail-newsletters' ) ?></span>
				</span>
			</span>
		</div>
		<div ng-show="lc.c_common.show_reply_to" class="ng-hide-animate">
			<span class="jackmail_grey">
				<span class="jackmail_info dashicons dashicons-info" jackmail-tooltip="<?php esc_attr_e( 'The address used to reply to your email', 'jackmail-newsletters' ) ?>"></span>
				<?php _e( 'Reply to:', 'jackmail-newsletters' ) ?>
			</span>
			<span class="jackmail_bold">
				<span jackmail-content-editable input-value="lc.c_common.campaign.reply_to_name" place-holder="<?php esc_attr_e( 'Reply to', 'jackmail-newsletters' ) ?>"></span>
				<span jackmail-content-editable input-value="lc.c_common.campaign.reply_to_email" before="<" after=">" place-holder="<?php esc_attr_e( 'reply_to@example.com', 'jackmail-newsletters' ) ?>" class="jackmail_m_l_20"></span>
			</span>
		</div>
		<div>
			<span class="jackmail_display_table_cell_auto jackmail_grey">
				<span class="jackmail_info dashicons dashicons-info" jackmail-tooltip="<?php esc_attr_e( 'You can personalize your email with data from your list, use ((COLUMN_NAME)) syntax to use it', 'jackmail-newsletters' ) ?>"></span>
				<?php _e( 'Subject:', 'jackmail-newsletters' ) ?>
			</span>
			<div class="jackmail_campaign_subject_customize">
				<div class="jackmail_campaign_subject">
					<div jackmail-dropdown-button button-value="(( ))"
						titles-clicks-array="lc.common.list_fields" titles-clicks-array-event="lc.c_common.insert_subject_customize( key, title )">
					</div>
				</div>
			</div>
			<?php if( $display_emojis ) { ?>
			<div class="jackmail_campaign_emojis_container" ng-mouseleave="lc.c_common.hide_emojis_dropdown()">
				<div class="jackmail_campaign_emojis_title" ng-click="lc.c_common.show_hide_emojis_dropdown()">
					<div class="jackmail_campaign_emojis_img"></div>
				</div>
				<div ng-show="lc.c_common.display_emojis_dropdown" class="jackmail_campaign_emojis">
					<div jackmail-dropdown-button dropdown-left="true"
						button-value="{{lc.c_common.emoji_categories[ lc.c_common.emoji_categorie_selected_key ]}}"
						titles-clicks-array="lc.c_common.emoji_categories"
						titles-clicks-array-event="lc.c_common.select_emoji_categorie( key )">
					</div>
					<div ng-repeat="( key, emoji ) in lc.c_common.emojis | filter:{ category: lc.c_common.emoji_categorie_selected_key }: true track by $index"
						style="background-position:-{{emoji.sheet_x * 16}}px -{{emoji.sheet_y * 16}}px;"
						ng-click="lc.c_common.insert_emoji( key )"
						class="jackmail_campaign_emojis_img">
					</div>
				</div>
			</div>
			<?php } ?>
			<span class="jackmail_bold">
				<span jackmail-content-editable input-value="lc.c_common.campaign.object" place-holder="<?php esc_attr_e( 'Newsletter', 'jackmail-newsletters' ) ?>"></span>
			</span>
		</div>
	</div>
	<div class="jackmail_content_email_emailbuilder" ng-class="lc.c_common.current_content_email_type !== 'emailbuilder' ? 'jackmail_content_email_emailbuilder_html_or_txt' : ''">
		<div class="jackmail_current_content_email_type_choice_container">
			<div>
                <div class="jackmail_left" jackmail-dropdown-button dropdown-left="true"
					button-value="{{lc.c_common.current_content_email_type === 'emailbuilder' ? '<?php echo esc_js( __( 'Emailbuilder', 'jackmail-newsletters' ) ) ?>' : lc.c_common.current_content_email_type === 'html' ? '<?php echo esc_js( __( 'Html code', 'jackmail-newsletters' ) ) ?>' : lc.c_common.current_content_email_type === 'txt' ? '<?php echo esc_js( __( 'Plain text', 'jackmail-newsletters' ) ) ?>' : ''}}"
					titles-clicks-array="lc.shared_campaign.type_choice_select_titles"
					titles-clicks-array-event="lc.shared_campaign.display_current_content_email_type_or_template( key )">
                </div>
				<div ng-show="lc.c_common.current_content_email_type !== 'emailbuilder'" class="jackmail_left" jackmail-dropdown-button dropdown-left="true" button-value="(( ))"
					titles-clicks-array="lc.common.list_fields_plus" titles-clicks-array-event="lc.c_common.insert_email_content_editor_customize( key, title )">
				</div>
				<?php if ( $campaign_type === 'campaign' ) { ?>
				<div ng-show="lc.c_common.current_content_email_type === 'html'" class="jackmail_left">
					<input ng-click="$root.display_html_preview_popup()" class="jackmail_white_button" type="button" value="<?php esc_attr_e( 'Preview', 'jackmail-newsletters' ) ?>"/>
				</div>
				<div ng-show="lc.c_common.current_content_email_type === 'emailbuilder'" class="jackmail_left">
					<input ng-click="lc.only_campaign.reset_emailbuilder_content()" class="jackmail_white_button" type="button" value="<?php esc_attr_e( 'Reset content', 'jackmail-newsletters' ) ?>"/>
				</div>
				<?php } ?>
			</div>
		</div>
		<div ng-show="!$root.show_emailbuilder && lc.c_common.current_content_email_type !== 'emailbuilder'" class="jackmail_content jackmail_content_email">
			<div id="jackmail_content_email"></div>
		</div>
	</div>
</div>
<?php } ?>