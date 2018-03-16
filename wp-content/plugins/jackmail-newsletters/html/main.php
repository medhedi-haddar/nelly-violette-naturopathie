<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-app="jackmail_app" ng-controller="MainController as m" class="jackmail jackmail_angular" ng-cloak>
	<div ng-show="$root.jackmail_error" ng-click="$root.hide_success_error()" class="jackmail_message jackmail_error">
		<div>
			<span>{{$root.jackmail_message}}</span>
			<span class="dashicons dashicons-no-alt"></span>
		</div>
	</div>
	<div ng-show="$root.jackmail_success" ng-click="$root.hide_success_error()" class="jackmail_message jackmail_success">
		<div>
			<span>
				<span>{{$root.jackmail_message}}</span>
				<span class="dashicons dashicons-no-alt"></span>
			</span>
		</div>
	</div>
	<div ng-show="$root.loading" class="jackmail_loading"></div>
	<div ng-view></div>
	<?php include_once plugin_dir_path( __FILE__ ) . 'search.inc.php'; ?>
	<div ng-show="$root.validation_popup" class="jackmail_confirmation">
		<div class="jackmail_confirmation_background"></div>
		<div class="jackmail_confirmation_message jackmail_confirmation_large">
			<div class="jackmail_confirmation_large_center_content">
				<p class="jackmail_bold" ng-bind-html="$root.validation_popup_message"></p>
				<p>
					<span ng-click="$root.ok_validation()" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
					<span ng-click="$root.cancel_validation()" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
				</p>
			</div>
		</div>
	</div>
	<?php if ( ! $emailbuilder_installed ) { ?>
	<div ng-show="$root.show_emailbuilder_popup" class="jackmail_confirmation">
		<div class="jackmail_confirmation_background"></div>
		<div class="jackmail_confirmation_message jackmail_confirmation_large">
			<div class="jackmail_confirmation_large_center_content">
				<p class="jackmail_title"><?php _e( 'Install Emailbuilder', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_grey"><?php _e( 'To use Emailbuilder, you need to accept the general terms and conditions of Emailbuilder.', 'jackmail-newsletters' ) ?></p>
				<div class="jackmail_emailbuider_conditions" ng-bind-html="$root.emailbuilder_popup_licence"></div>
				<div>
					<input ng-click="$root.cancel_emailbuilder_popup()" type="button" class="jackmail_input_transparent" value="<?php esc_attr_e( 'Decline', 'jackmail-newsletters' ) ?>"/>
					<input ng-click="$root.ok_emailbuilder_popup()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'I accept', 'jackmail-newsletters' ) ?>"/>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>
	<div ng-show="$root.settings.update_available" class="jackmail_confirmation">
		<div class="jackmail_confirmation_background"></div>
		<div class="jackmail_confirmation_message jackmail_confirmation_large jackmail_confirmation_update">
			<div ng-hide="$root.settings.force_update_available" ng-click="$root.hide_update_available_popup()" class="dashicons dashicons-no"></div>
			<div class="jackmail_center jackmail_m_t_40">
				<p class="jackmail_title"><?php _e( 'Please update your Jackmail version', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_grey"><?php _e( 'A new version of Jackmail is available. Please update your plugin.', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_bold jackmail_m_t_30">
					<a href="plugins.php?jackmail" class="jackmail_button"><?php _e( 'Update Jackmail', 'jackmail-newsletters' ) ?></a>
				</p>
			</div>
		</div>
	</div>
	<div ng-show="$root.show_html_preview_popup" class="jackmail_confirmation">
		<div class="jackmail_confirmation_background"></div>
		<div class="jackmail_confirmation_message jackmail_confirmation_preview">
			<div ng-click="$root.hide_html_preview_popup()" class="dashicons dashicons-no"></div>
			<iframe class="template_html_preview" ng-src="{{$root.html_preview_content_popup}}"></iframe>
		</div>
	</div>
	<a href="#" class="jackmail_download" target="_blank"></a>
	<?php if ( $is_configured ) { ?>
	<div ng-controller="AccountConnectionController as ac" ng-if="$root.show_account_connection_popup">
		<div class="jackmail_confirmation">
			<div class="jackmail_confirmation_background"></div>
			<div class="jackmail_confirmation_message jackmail_confirmation_reconnection">
				<div ng-click="$root.hide_account_connection_popup()" class="dashicons dashicons-no"></div>
				<?php include_once plugin_dir_path( __FILE__ ) . 'account_connection.inc.php' ?>
			</div>
		</div>
	</div>
	<?php } ?>
	<div ng-class="!$root.show_emailbuilder || $root.show_help2 ? 'jackmail_emailbuilder_container_hidden' : ''" id="jackmail_emailbuilder_container">
		<email-builder></email-builder>
	</div>
</div>
<?php } ?>