<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="ListAndCampaignController as lc">
	<div class="jackmail_header_container">
		<div class="jackmail_header">
			<div>
				<div class="jackmail_header_menu" jackmail-header-menu></div>
				<div class="jackmail_name">
					<span jackmail-content-editable ng-click="lc.only_list.focus_list_name()" when-enter="lc.only_list.blur_list_name()" input-value="lc.common.list.list.name" ng-hide="lc.common.list_full_editable <= 0"></span>
					<span ng-hide="lc.name_editing || lc.common.list_full_editable <= 0" ng-click="lc.only_list.focus_list_name()" class="dashicons dashicons-edit"></span>
					<span ng-hide="lc.common.list_full_editable" class="jackmail_name_not_editable">{{lc.common.list.list.name}}</span>
				</div>
				<div class="jackmail_header_buttons">
					<div jackmail-search></div>
				</div>
			</div>
		</div>
	</div>
	<div ng-hide="$root.show_help2">
		<?php include_once plugin_dir_path( __FILE__ ) . 'list_and_campaign.inc.php'; ?>
		<div ng-show="lc.only_list.show_connectors" class="jackmail_confirmation jackmail_confirmation_connectors">
			<div ng-click="lc.only_list.display_hide_connectors()" class="jackmail_confirmation_background"></div>
			<div class="jackmail_confirmation_message jackmail_confirmation_large">
				<div ng-click="lc.only_list.display_hide_connectors()" class="dashicons dashicons-no"></div>
				<div class="jackmail_confirmation_large_content">
					<p class="jackmail_title"><?php _e( 'API connectors', 'jackmail-newsletters' ) ?></p>
					<p><?php _e( 'Below are the HTTP requests that allow you to include, update or delete data from your lists.', 'jackmail-newsletters' ) ?></p>
					<p><?php _e( 'The settings boxes correspond to the columns titles in your list.', 'jackmail-newsletters' ) ?></p>
					<p><select ng-options="action.id as action.name for action in lc.only_list.connectors_actions" ng-change="lc.only_list.connectors_calculate_position()" ng-model="lc.only_list.display_connectors_action"></select></p>
					<div ng-repeat="( key, example ) in lc.only_list.connectors_example track by $index" ng-show="lc.only_list.display_connectors_action === key">
						<p><?php _e( 'Url:', 'jackmail-newsletters' ) ?></p>
						<div ng-show="lc.only_list.connectors_actived === 1" class="jackmail_code" ng-bind-html="example.url"></div>
						<div ng-show="lc.only_list.connectors_actived === 0" class="jackmail_code"><?php _e( 'Connectors are not currently activated.', 'jackmail-newsletters' ) ?></div>
						<p ng-show="example.parameters.length === 1"><?php _e( 'Settings example:', 'jackmail-newsletters' ) ?></p>
						<p ng-show="example.parameters.length > 1"><?php _e( 'Settings examples:', 'jackmail-newsletters' ) ?></p>
						<div ng-repeat="parameters in example.parameters">
							<div ng-show="lc.only_list.connectors_actived === 1" class="jackmail_code" ng-bind-html="parameters"></div>
							<div ng-show="lc.only_list.connectors_actived === 0" class="jackmail_code"><?php _e( 'Connectors are not currently activated.', 'jackmail-newsletters' ) ?></div>
						</div>
					</div>
					<p><?php _e( 'Warning: these requests should be handled by expert developers. They should not be associated with public webpages (html/form, javascript) but rather with redirection scripts or asynchronous calls hosted on servers (PHP, ASP, JSP...). Please use POST requests and activate IP restriction.', 'jackmail-newsletters' ) ?></p>
					<p ng-show="lc.only_list.connectors_actived === 0" class="jackmail_bold"><?php _e( 'Connectors are not currently activated.', 'jackmail-newsletters' ) ?></p>
					<p><input ng-click="$root.change_page( 'settings' )" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Manage connectors and IP restrictions', 'jackmail-newsletters' ) ?>"/></p>
				</div>
			</div>
		</div>
		<div ng-show="lc.common.show_name_popup" class="jackmail_confirmation">
			<div class="jackmail_confirmation_background"></div>
			<div class="jackmail_confirmation_message">
				<div>
					<p class="jackmail_title"><?php _e( 'What\'s your list name?', 'jackmail-newsletters' ) ?></p>
					<p class="jackmail_text_left"><?php _e( 'Name:', 'jackmail-newsletters' ) ?></p>
					<p><input ng-model="lc.common.list.list.name" ng-enter="lc.only_list.ok_list_name_popup()" ng-echap="lc.only_list.hide_list_name_popup()" class="jackmail_name_popup" type="text"/></p>
					<p>
						<span ng-click="lc.only_list.ok_list_name_popup()" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
						<span ng-click="lc.only_list.cancel_list_name_popup()" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>