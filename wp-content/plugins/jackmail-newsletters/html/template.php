<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="TemplateController as t">
	<div class="jackmail_header_container">
		<div class="jackmail_header">
			<div>
				<div class="jackmail_header_menu" jackmail-header-menu></div>
				<div class="jackmail_name">
					<span jackmail-content-editable ng-click="t.focus_template_name()" when-enter="t.blur_template_name()" input-value="t.template.name"></span>
					<span ng-hide="t.name_editing" ng-click="t.focus_template_name()" class="dashicons dashicons-edit"></span>
				</div>
				<div class="jackmail_header_buttons">
					<div jackmail-search></div>
                    <div class="jackmail_campaign_save" jackmail-dropdown-button button-value="<?php esc_attr_e( 'Save', 'jackmail-newsletters' ) ?>"
						titles-clicks-array="t.save_choice_select_title_template"
						titles-clicks-array-event="t.save_template_or_create_campaign( key )">
                    </div>
				</div>
			</div>
		</div>
	</div>
	<div ng-hide="$root.show_help2">
		<div class="jackmail_content_email_emailbuilder">
			<div class="jackmail_current_content_email_type_choice_container">
				<div>
					<div class="jackmail_left">
						<input ng-click="t.reset_emailbuilder_content()" class="jackmail_white_button" type="button" value="<?php esc_attr_e( 'Reset content', 'jackmail-newsletters' ) ?>"/>
					</div>
				</div>
			</div>
		</div>
		<div ng-show="t.show_name_popup" class="jackmail_confirmation">
			<div class="jackmail_confirmation_background"></div>
			<div class="jackmail_confirmation_message">
				<div>
					<p class="jackmail_title"><?php _e( 'What\'s your template name?', 'jackmail-newsletters' ) ?></p>
					<p class="jackmail_text_left"><?php _e( 'Name:', 'jackmail-newsletters' ) ?></p>
					<p><input ng-model="t.template.name" ng-enter="t.hide_name_popup()" ng-echap="t.hide_name_popup()" class="jackmail_name_popup" type="text"/></p>
					<p>
						<span ng-click="t.hide_name_popup()" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
						<span ng-click="t.hide_name_popup()" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>