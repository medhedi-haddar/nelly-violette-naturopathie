<?php if ( defined( 'ABSPATH' ) ) { ?>
<div class="jackmail_content">
	<div ng-show="lc.common.show_grid === 0">
		<p ng-show="lc.page_type === 'campaign' && !lc.show_import_lists" class="jackmail_title jackmail_center"><?php _e( 'Select contacts', 'jackmail-newsletters' ) ?></p>
		<div ng-hide="lc.show_import_lists" ng-class="lc.page_type === 'campaign' ? 'jackmail_import_3_columns' : 'jackmail_import_2_columns'" class="jackmail_import">
			<?php if ( $page_type === 'campaign' ) { ?>
			<div ng-click="lc.c_common.display_import_lists()" class="jackmail_import_list">
				<p class="jackmail_bold"><?php _e( 'Select a list', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_grey"><?php _e( 'Select a contact list from your WordPress', 'jackmail-newsletters' ) ?></p>
				<span><?php _e( 'Import', 'jackmail-newsletters' ) ?></span>
			</div>
			<?php } ?>
			<div ng-click="lc.create_list()" class="jackmail_import_manual">
				<p class="jackmail_bold"><?php _e( 'Manual entry', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_grey"><?php _e( 'Select your contacts\' emails from the recipients list', 'jackmail-newsletters' ) ?></p>
				<span><?php _e( 'Import', 'jackmail-newsletters' ) ?></span>
			</div>
			<div ng-click="lc.common.display_copy_paste()" class="jackmail_import_copy_paste">
				<p class="jackmail_bold"><?php _e( 'Copy/paste', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_grey"><?php _e( 'Copy/paste your contacts.', 'jackmail-newsletters' ) ?></p>
				<span><?php _e( 'Import', 'jackmail-newsletters' ) ?></span>
			</div>
			<div class="jackmail_import_file">
				<p class="jackmail_bold"><?php _e( 'Select a file', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_grey"><?php _e( 'Import your contacts from a CSV, TXT or JSON file.', 'jackmail-newsletters' ) ?></p>
				<span><?php _e( 'Import', 'jackmail-newsletters' ) ?></span>
				<input onchange="angular.element( this ).scope().lc.common.add_contacts_file( event )" type="file"/>
			</div>
		</div>
		<?php if ( $page_type === 'campaign' ) { ?>
		<div ng-show="lc.show_import_lists" class="jackmail_import_list_selection jackmail_import_list_selection_{{lc.campaign_type}}">
			<div>
				<p class="jackmail_bold"><?php _e( 'Select a list', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_grey"><?php _e( 'Select a contact list from your WordPress', 'jackmail-newsletters' ) ?></p>
				<input ng-click="lc.only_campaign.hide_grid()" type="button" value="<?php esc_attr_e( 'Back', 'jackmail-newsletters' ) ?>" class="jackmail_bold"/>
			</div>
			<div>
				<p class="jackmail_import_list_selection_title">
					<span class="jackmail_bold"><?php _e( 'Select my lists', 'jackmail-newsletters' ) ?></span>
					<span class="jackmail_grey jackmail_right">
						{{lc.c_common.lists.length}}
						<span ng-hide="lc.c_common.lists.length > 1"><?php _e( 'list available', 'jackmail-newsletters' ) ?></span>
						<span ng-show="lc.c_common.lists.length > 1"><?php _e( 'lists available', 'jackmail-newsletters' ) ?></span>
					</span>
				</p>
				<div ng-show="lc.c_common.lists.length > 0">
					<div class="jackmail_import_list_selector" ng-style="lc.c_common.import_lists_grid_height">
						<div ng-repeat="( key, list ) in lc.c_common.lists track by $index">
							<span jackmail-checkbox="list.selected"
								ng-click="lc.c_common.select_list( key )">
							</span>
							<span ng-click="lc.c_common.select_list( key )" class="jackmail_import_list_name">
								{{list.name}}
							</span>
							<span ng-click="$root.change_page_with_parameters( 'list', list.id )" class="jackmail_import_list_details">
								<span class="dashicons dashicons-search"></span>
								<span><?php _e( 'See', 'jackmail-newsletters' ) ?></span>
							</span>
							<span ng-click="lc.c_common.select_list( key )" class="jackmail_import_list_selector_contacts">
								{{list.nb_display_contacts | numberSeparator}}
								<span ng-hide="list.nb_display_contacts > 1"><?php _e( 'contact', 'jackmail-newsletters' ) ?></span>
								<span ng-show="list.nb_display_contacts > 1"><?php _e( 'contacts', 'jackmail-newsletters' ) ?></span>
							</span>
						</div>
					</div>
					<div class="jackmail_import_total">
						<span class="jackmail_uppercase jackmail_grey"><?php _e( 'Total', 'jackmail-newsletters' ) ?></span>
						<span class="jackmail_right">
							<?php if ( $campaign_type === 'campaign' ) { ?>
							<span class="jackmail_grey">
								{{lc.c_common.nb_contacts_from_lists | numberSeparator}}
								<span ng-hide="lc.c_common.nb_contacts_from_lists > 1"><?php _e( 'contact', 'jackmail-newsletters' ) ?></span>
								<span ng-show="lc.c_common.nb_contacts_from_lists > 1"><?php _e( 'contacts', 'jackmail-newsletters' ) ?></span>
							</span>
							<input ng-click="lc.c_common.go_step( 'contacts' )" class="jackmail_bold" value="<?php esc_attr_e( 'Import', 'jackmail-newsletters' ) ?>" ng-class="lc.c_common.nb_contacts_from_lists > 0 ? 'jackmail_green_button' : ''" type="button"/>
							<?php } ?>
							<?php if ( $campaign_type === 'scenario' ) { ?>
							<span class="jackmail_grey">
								{{lc.c_common.nb_selected_lists | numberSeparator}}
								<span ng-hide="lc.c_common.nb_selected_lists > 1"><?php _e( 'list', 'jackmail-newsletters' ) ?></span>
								<span ng-show="lc.c_common.nb_selected_lists > 1"><?php _e( 'lists', 'jackmail-newsletters' ) ?></span>
							</span>
							<?php } ?>
						</span>
					</div>
				</div>
				<div ng-show="lc.c_common.lists.length === 0">
					<p class="jackmail_center jackmail_pt_80">
						<?php _e( 'No list available', 'jackmail-newsletters' ) ?>
					</p>
					<p class="jackmail_center">
						<input ng-click="lc.c_common.create_list_and_go()" type="button" class="jackmail_bold jackmail_pointer jackmail_green_button" value="<?php esc_attr_e( 'Create a list', 'jackmail-newsletters' ) ?>"/>
					</p>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
	<div ng-show="lc.common.show_grid === 1">
		<div>
			<p class="jackmail_title">
				<?php if ( $page_type === 'list' ) { ?>
				<span class="dashicons dashicons-undo jackmail_lists_return" title="{{lc.param_option ? '<?php esc_attr_e( 'Go to campaign page', 'jackmail-newsletters' ) ?>' : '<?php esc_attr_e( 'Go to lists page', 'jackmail-newsletters' ) ?>'}}" ng-click="lc.common.go_back()"></span>
				<?php } ?>
				<span ng-show="lc.page_type === 'list'"><?php _e( 'Contact management', 'jackmail-newsletters' ) ?></span>
				<span ng-class="lc.page_type === 'list' ? 'jackmail_list_title' : ''">
					<span ng-show="lc.page_type === 'list'">-</span>
					<span>{{lc.common.list.nb_contacts | numberSeparator}}</span>
					<span ng-hide="lc.common.list.nb_contacts > 1"><?php _e( 'contact', 'jackmail-newsletters' ) ?></span>
					<span ng-show="lc.common.list.nb_contacts > 1"><?php _e( 'contacts', 'jackmail-newsletters' ) ?></span>
				</span>
			</p>
			<div class="jackmail_grid_buttons jackmail_grid_buttons_search">
				<div ng-show="lc.common.list_full_editable" class="jackmail_left jackmail_mr_10">
					<input ng-click="lc.common.add_contact_manual()" type="button" value="<?php esc_attr_e( 'Add a contact', 'jackmail-newsletters' ) ?>" class="jackmail_green_button"/>
				</div>
				<div ng-show="lc.common.list_full_editable" class="jackmail_left jackmail_mr_10">
					<div button-class="jackmail_white_button jackmail_grid_columns_button" jackmail-dropdown-button button-value="<?php esc_attr_e( 'Import', 'jackmail-newsletters' ) ?>"
						titles-clicks-array="lc.import_choices" titles-clicks-array-event="lc.display_import( key )"
						title-file-event="lc.common.add_contacts_file" title-file="<?php esc_attr_e( 'From a file', 'jackmail-newsletters' ) ?>">
					</div>
				</div>
				<div ng-show="lc.common.list_editable" button-class="jackmail_left jackmail_mr_10" jackmail-dropdown-button button-value="<?php esc_attr_e( 'Export', 'jackmail-newsletters' ) ?>"
					titles-clicks-array="lc.common.export_select" titles-clicks-array-event="lc.common.export_all_or_export_selection( key )">
				</div>
				<input ng-hide="lc.common.list_editable" ng-click="lc.common.export_all()" class="jackmail_search_button jackmail_left" type="button" value="<?php esc_attr_e( 'Export', 'jackmail-newsletters' ) ?>"/>
				<div jackmail-grid-search jackmail-action="lc.common.get_list_data_search_reset" class="jackmail_left"></div>
				<div class="jackmail_right">
					<?php if ( $page_type === 'list' ) { ?>
					<input ng-show="lc.common.list_full_editable" ng-click="lc.only_list.display_hide_connectors()" class="jackmail_input_transparent" type="button" value="<?php esc_attr_e( 'Connectors', 'jackmail-newsletters' ) ?>"/>
					<input ng-click="lc.only_list.create_campaign_with_list()" class="jackmail_green_button" type="button" value="<?php esc_attr_e( 'Create a campaign', 'jackmail-newsletters' ) ?>"/>
					<input ng-click="$root.change_page_with_parameters( 'statistics', 'list/' + lc.common.list.list.id )" class="jackmail_m_l_10" type="button" value="<?php esc_attr_e( 'Statistics', 'jackmail-newsletters' ) ?>"/>
					<?php } ?>
					<span ng-show="lc.common.list_editable && $root.grid_service.nb_selected > 0"
						jackmail-button-delete class="jackmail_m_l_10"
						delete-value="<?php if ( $page_type === 'list' ) { esc_attr_e( 'Delete', 'jackmail-newsletters' ); } else { esc_attr_e( 'Delete from the campaign', 'jackmail-newsletters' ); } ?>"
						when-delete="lc.common.delete_contacts_selection_confirmation()">
					</span>
				</div>
			</div>
			<div class="jackmail_grid_container jackmail_grid_contacts_container">
				<div class="jackmail_grid_header">
					<?php if ( $page_type === 'list' ) { ?>
					<span ng-show="lc.common.list_targeting" ng-click="lc.only_list.display_hide_targeting_settings()" class="jackmail_targeting_display_hide">
						<span ng-show="!lc.only_list.show_targeting_settings">+</span>
						<span ng-show="lc.only_list.show_targeting_settings">-</span>
						<span><?php _e( 'Targeting', 'jackmail-newsletters' ) ?></span>
						<span ng-show="lc.only_list.nb_searched_targeting_rules === 1">({{lc.only_list.nb_searched_targeting_rules}} <?php _e( 'rule', 'jackmail-newsletters' ) ?>)</span>
						<span ng-show="lc.only_list.nb_searched_targeting_rules > 1">({{lc.only_list.nb_searched_targeting_rules}} <?php _e( 'rules', 'jackmail-newsletters' ) ?>)</span>
					</span>
					<?php } ?>
					<div class="jackmail_right" jackmail-dropdown-button button-value="<?php esc_attr_e( 'Manage the columns', 'jackmail-newsletters' ) ?>{{lc.common.list_fields.length > 1 ? ' (' + lc.common.list_fields.length + ' <?php esc_attr_e( 'columns', 'jackmail-newsletters' ) ?>)' :  ''}}"
					    titles-clicks-grid="lc.common.list_fields" titles-clicks-grid-checked="$root.grid_service.grid_classes" titles-clicks-grid-event="$root.grid_service.display_or_hide_column( key )"
						title-clicks-grid-add="lc.common.list_full_editable || lc.common.columns_editable" title-clicks-grid-add-event="lc.common.add_header_column()">
					</div>
				</div>
				<?php if ( $page_type === 'list' ) { ?>
				<div ng-show="lc.only_list.show_targeting_settings && lc.common.list_targeting" class="jackmail_targeting_container">
					<div class="jackmail_targeting_content">
						<div ng-repeat="( key, targeting_rule ) in lc.only_list.targeting_rules track by $index" ng-click="lc.only_list.get_targeting_rule_current( key )">
							<div>
								<div ng-show="key !== 0" jackmail-dropdown-button dropdown-left="true" button-value="{{lc.only_list.get_targeting_rule_and_or_title( targeting_rule.rule_column, targeting_rule.rule_and_or )}}"
									button-class="jackmail_white_button jackmail_grid_columns_button jackmail_left jackmail_mr_10"
									titles-clicks-array="lc.only_list.targeting_rule_and_or" titles-clicks-array-event="lc.only_list.select_targeting_rule_and_or( key )">
								</div>
								<div jackmail-dropdown-button dropdown-left="true" button-value="{{lc.common.list_fields[ targeting_rule.rule_column ]}}"
									button-class="jackmail_white_button jackmail_grid_columns_button jackmail_left jackmail_mr_10"
									titles-clicks-array="lc.common.list_fields" titles-clicks-array-event="lc.only_list.select_targeting_rule_column( key )">
								</div>
								<div jackmail-dropdown-button dropdown-left="true"
								    button-value="{{lc.only_list.get_targeting_rule_option_title( targeting_rule.rule_column, targeting_rule.rule_option )}}"
									button-class="jackmail_white_button jackmail_grid_columns_button jackmail_left jackmail_mr_10"
									titles-clicks-array="targeting_rule.rule_column === 0 ? lc.only_list.targeting_rule_options_email : lc.only_list.targeting_rule_options"
									titles-clicks-array-event="lc.only_list.select_targeting_rule_option( key )">
								</div>
								<div ng-show="targeting_rule.rule_option !== 'EMPTY' && targeting_rule.rule_option !== 'UNSUBSCRIBED'"
									jackmail-input-interval input-value="targeting_rule.rule_content" jackmail-action="lc.only_list.get_list_data_targeting_rules_reset" class="jackmail_left">
								</div>
								<span ng-click="lc.only_list.remove_targeting_rule( key )" class="dashicons dashicons-no-alt"></span>
							</div>
						</div>
						<div>
							<div>
								<input ng-click="lc.only_list.add_targeting_rule()" type="button" value="+ <?php _e( 'Add a rule', 'jackmail-newsletters' ) ?>"/>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
				<div class="jackmail_grid {{$root.grid_service.grid_class}}">
					<table>
						<tr>
							<th ng-show="lc.common.list_editable" class="jackmail_column_selector">
								<span jackmail-checkbox="$root.grid_service.nb_selected === lc.common.list.contacts.length"
									ng-click="lc.common.grid_select_or_unselect_all()"
									ng-hide="lc.common.list.contacts.length === 0">
								</span>
							</th>
							<th ng-show="$root.grid_service.grid_classes[ 0 ]" ng-click="lc.common.range_by( 'email' )" class="jackmail_column_0 jackmail_column_ordering">
								<span><?php _e( 'EMAIL', 'jackmail-newsletters' ) ?></span>
							</th>
							<th ng-repeat="( field_id, field ) in lc.common.list_fields track by $index" ng-if="field_id !== 0" ng-show="$root.grid_service.grid_classes[ field_id ]" ng-click="lc.common.range_by( 'field' + field_id )" ng-class="'jackmail_column_' + field_id" class="jackmail_column_ordering">
								<span jackmail-input-edit input-value="lc.common.list.list[ 'field' + field_id ]"
								    not-editable="{{lc.common.columns_editable === 0 ? 'true' : ''}}"
								    when-enter="lc.common.edit_header_column( field_id )"
								    is-deletable="{{lc.common.columns_editable === 0 ? '' : 'true'}}" when-delete="lc.common.delete_header_column( field_id )">
								</span>
							</th>
							<?php if ( $campaign_type === 'campaign' ) { ?>
							<th ng-click="lc.common.range_by( 'id_list' )" class="jackmail_column_ordering">
								<span class="jackmail_bold"><?php _e( 'Source', 'jackmail-newsletters' ) ?></span>
							</th>
							<?php } ?>
							<th class="jackmail_column_details">
								<span class="jackmail_bold"><?php _e( 'Details', 'jackmail-newsletters' ) ?></span>
							</th>
						</tr>
					</table>
				</div>
				<div class="jackmail_grid jackmail_grid_content" ng-class="$root.grid_service.grid_class" grid-scroll="$root.grid_service" grid-load="lc.common.get_list_data" grid-total="lc.common.list.nb_contacts_search">
					<table>
						<tr ng-repeat="( key, contact ) in lc.common.list.contacts | limitTo: $root.grid_service.nb_lines_grid track by $index">
							<td ng-show="lc.common.list_editable" class="jackmail_column_selector">
								<span jackmail-checkbox="contact.selected"
									checkbox-title="{{column.name}}"
									ng-click="lc.common.grid_select_or_unselect_row( key )">
								</span>
							</td>
							<td class="jackmail_column_0 jackmail_column_0_email" ng-class="contact.blacklist !== '0' ? 'jackmail_column_0_blacklisted' : ''">
								<span ng-class="contact.blacklist !== '0' ? 'jackmail_column_0_blacklisted_' + contact.blacklist : ''" title="{{contact.blacklist | blacklistType}}"></span>
								<input type="text" ng-disabled="!lc.common.list_editable" ng-model="contact.email" ng-enter-up-down ng-focus="lc.common.focus_contact( key )" ng-blur="lc.common.update_contact( key, -1 )" ng-keyup="lc.common.blur_contact( $event )"/>
							</td>
							<td ng-repeat="( field_id, field ) in lc.common.list_fields track by $index" ng-if="field_id !== 0" ng-show="$root.grid_service.grid_classes[ field_id ]" ng-class="'jackmail_column_' + field_id">
								<input type="text" ng-disabled="!lc.common.list_editable" ng-model="contact[ 'field' + field_id ]" ng-enter-up-down ng-focus="lc.common.focus_contact( key )" ng-blur="lc.common.update_contact( key, field_id )" ng-keyup="lc.common.blur_contact( $event )"/>
							</td>
							<?php if ( $campaign_type === 'campaign' ) { ?>
							<td>
								{{ contact.id_list | campaignListName : lc.c_common.lists : lc.campaign_default_id_list }}
							</td>
							<?php } ?>
							<td class="jackmail_column_details jackmail_column_buttons">
								<span ng-click="lc.common_list_detail.display_list_contact_detail( key )" class="dashicons dashicons-admin-users" title="<?php esc_attr_e( 'Details', 'jackmail-newsletters' ) ?>"></span>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div ng-show="lc.common.show_copy_paste" class="jackmail_confirmation">
		<div class="jackmail_confirmation_background"></div>
		<div class="jackmail_confirmation_message jackmail_confirmation_large">
			<div class="jackmail_confirmation_large_center_content">
				<p class="jackmail_title"><?php _e( 'Copy paste your contacts', 'jackmail-newsletters' ) ?></p>
				<textarea class="jackmail_copy_paste" ng-model="lc.common.copy_paste_content"></textarea>
				<p class="jackmail_align_left jackmail_grey jackmail_margin_none">
					<span class="jackmail_info dashicons dashicons-info jackmail_info_grey"></span>
					<?php _e( 'Field separator is semicolon, comma, vertical bar or tabulation', 'jackmail-newsletters' ) ?>
				</p>
				<p>
					<span ng-click="lc.common.confirm_copy_paste()" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
					<span ng-click="lc.common.hide_copy_paste()" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
				</p>
			</div>
		</div>
	</div>
	<div ng-show="$root.settings.update_available" class="jackmail_confirmation">
		<div class="jackmail_confirmation_background"></div>
		<div class="jackmail_confirmation_message jackmail_confirmation_large jackmail_confirmation_update">
			<div ng-hide="$root.settings.force_update_available" ng-click="$root.hide_update_available_popup()" class="dashicons dashicons-no"></div>
			<div class="jackmail_confirmation_large_content jackmail_center">
				<p class="jackmail_bold"><?php _e( 'A new version of Jackmail is available.', 'jackmail-newsletters' ) ?></p>
				<p class="jackmail_bold"><a href="plugins.php?jackmail"><?php _e( 'Update', 'jackmail-newsletters' ) ?></a></p>
			</div>
		</div>
	</div>
</div>
<?php } ?>