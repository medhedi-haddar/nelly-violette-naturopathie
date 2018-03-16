<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="ListsController as l">
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
	<div ng-hide="$root.show_help2" class="jackmail_content">
		<div class="jackmail_grid_buttons jackmail_grid_buttons_search">
			<p class="jackmail_title jackmail_left jackmail_mt_0">
				<?php _e( 'Contact lists', 'jackmail-newsletters' ) ?>
			</p>
			<div jackmail-grid-search jackmail-action="l.get_lists_search"
			    search-title="<?php esc_attr_e( 'Search contact in lists', 'jackmail-newsletters' ) ?>"
				class="jackmail_m_l_10 jackmail_left">
			</div>
			<div class="jackmail_right jackmail_mt_0">
				<input ng-click="l.create_list()" class="jackmail_green_button" type="button" value="<?php esc_attr_e( 'Create a list', 'jackmail-newsletters' ) ?>"/>
				<span ng-show="$root.grid_service.nb_selected > 0" jackmail-button-delete delete-value="<?php esc_attr_e( 'Delete', 'jackmail-newsletters' ) ?>" when-delete="l.delete_selection_confirmation()"></span>
			</div>
		</div>
		<div class="jackmail_grid_container jackmail_grid_action jackmail_grid_lists">
			<div class="jackmail_grid_header_lists"></div>
			<div class="jackmail_grid {{$root.grid_service.grid_class}}">
				<table>
					<tr>
						<th class="jackmail_column_selector">
							<span jackmail-checkbox="$root.grid_service.nb_selected === l.nb_deletable_lists && $root.grid_service.nb_selected > 0"
								ng-click="l.grid_select_or_unselect_all()"
								ng-hide="l.lists.length === 0 || l.nb_deletable_lists === 0">
							</span>
						</th>
						<th ng-repeat="( key, column ) in l.columns track by $index" ng-click="column.field !== '' ? l.range_by( column.field ) : ''" class="{{'jackmail_column_' + key}}" ng-class="column.field !== '' ? 'jackmail_column_ordering' : ''">
							<span>{{column.name}}</span>
						</th>
					</tr>
				</table>
			</div>
			<div ng-show="l.nb_lists > 0" class="jackmail_grid jackmail_grid_content {{$root.grid_service.grid_class}}">
				<table>
					<tr ng-repeat="( key, list ) in l.lists track by $index">
						<td class="jackmail_column_selector">
							<span jackmail-checkbox="list.selected"
								checkbox-click="l.grid_select_or_unselect_row( key )"
								checkbox-disabled="list.type !== '' ? 'true' : ''">
							</span>
						</td>
						<td ng-click="$root.change_page_with_parameters( 'list', list.id )" class="jackmail_column_0 jackmail_pointer">
							<span jackmail-input-edit input-value="list.name" not-editable="{{list.type === '' ? '' : 'true'}}" when-enter="l.edit_list_name( list.name, list.id )"></span>
							<br/>
							<span class="jackmail_grey"><?php _e( 'Created on', 'jackmail-newsletters' ) ?> {{list.created_date_gmt | formatedDate : 'gmt_to_timezone' : 'hours'}}</span>
							<span ng-show="list.type !== ''" class="jackmail_plugin_list_name">
								<a ng-href="{{list.type | pluginUrl}}" title="<?php esc_attr_e( 'See it', 'jackmail-newsletters' ) ?>">(<?php _e( 'via', 'jackmail-newsletters' ) ?> {{list.type | pluginName}})</a>
							</span>
							<span ng-show="list.in_widget" class="jackmail_plugin_list_name">
								<a href="widgets.php">(<?php _e( 'via Jackmail widget', 'jackmail-newsletters' ) ?>)</a>
							</span>
						</td>
						<td ng-click="$root.change_page_with_parameters( 'list', list.id )" class="jackmail_column_1 jackmail_pointer">
							<span class="jackmail_bold">{{list.nb_contacts | numberSeparator}}</span>
							<br/>
							<span ng-hide="list.nb_contacts > 1" class="jackmail_grey"><?php _e( 'recipient', 'jackmail-newsletters' ) ?></span>
							<span ng-show="list.nb_contacts > 1" class="jackmail_grey"><?php _e( 'recipients', 'jackmail-newsletters' ) ?></span>
						</td>
						<td class="jackmail_column_2">
							<div>
								<div>
									<span class="jackmail_statistics_opens">{{list.opens_percent}} %</span>
									<br/>
									<span class="jackmail_grey"><?php _e( 'opens', 'jackmail-newsletters' ) ?></span>
								</div>
								<div>
									<span class="jackmail_statistics_clicks">{{list.clicks_percent}} %</span>
									<br/>
									<span class="jackmail_grey"><?php _e( 'clicks', 'jackmail-newsletters' ) ?></span>
								</div>
								<div>
									<span class="jackmail_statistics_unsubscribes">{{list.unsubscribes_percent}} %</span>
									<br/>
									<span class="jackmail_grey"><?php _e( 'unsubscribes', 'jackmail-newsletters' ) ?></span>
								</div>
							</div>
						</td>
						<td class="jackmail_column_3 jackmail_column_buttons">
							<span ng-click="$root.change_page_with_parameters( 'list', list.id )" class="dashicons dashicons-admin-users" title="<?php esc_attr_e( 'Recipients', 'jackmail-newsletters' ) ?>"></span>
							<span ng-click="$root.change_page_with_parameters( 'statistics', 'list/' + list.id )" class="dashicons dashicons-chart-bar" title="<?php esc_attr_e( 'Statistics', 'jackmail-newsletters' ) ?>"></span>
							<span ng-click="l.export_all( list.id, list.nb_contacts )" class="dashicons dashicons-migrate" title="<?php esc_attr_e( 'Export', 'jackmail-newsletters' ) ?>"></span>
							<span ng-show="list.type === ''" jackmail-button-delete when-delete="l.delete_list_confirmation_validation( key, list.id, list.in_widget, list.in_scenario )"></span>
						</td>
					</tr>
				</table>
			</div>
			<div ng-show="l.nb_lists === 0" class="jackmail_none">
				<p class="jackmail_none_title"><?php _e( 'You haven\'t added a contact list yet', 'jackmail-newsletters' ) ?></p>
				<p><input ng-click="l.create_list()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Create a list', 'jackmail-newsletters' ) ?>"/></p>
				<p class="jackmail_none_help">
					<a ng-href="{{$root.settings.jackmail_doc_url}}article/manage-the-subscribers-list" target="_blank">
						<span class="dashicons dashicons-info"></span>
						<?php _e( 'What is a contact list?', 'jackmail-newsletters' ) ?>
					</a>
				</p>
			</div>
		</div>
	</div>
	<div ng-show="l.show_new_plugins_confirmation" class="jackmail_confirmation">
		<div class="jackmail_confirmation_background"></div>
		<div class="jackmail_confirmation_message jackmail_confirmation_large">
			<div ng-click="l.cancel_new_plugins_confirmation()" class="dashicons dashicons-no"></div>
			<div ng-repeat="( key, plugin ) in l.new_plugins track by $index" ng-show="plugin.active && !plugin.hide" class="jackmail_confirmation_large_content">
				<p class="jackmail_title"><?php _e( 'Good news!', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_grey"><?php _e( 'Jackmail Connect allows you to sync your', 'jackmail-newsletters' ) ?> {{plugin.name}} <?php _e( 'to your lists in Jackmail.', 'jackmail-newsletters' ) ?></p>
				<br/>
				<p>
					<input ng-click="l.select_new_plugin( key )" class="jackmail_green_button" type="button" value="<?php esc_attr_e( 'Integrate', 'jackmail-newsletters' ) ?>"/>
					<input ng-click="l.unselect_new_plugin( key )" class="jackmail_button" type="button" value="<?php esc_attr_e( 'Don\'t sync', 'jackmail-newsletters' ) ?>"/>
				</p>
			</div>
			<div class="jackmail_confirmation_large_content_loading"><?php _e( 'Loading...', 'jackmail-newsletters' ) ?></div>
		</div>
	</div>
</div>
<?php } ?>