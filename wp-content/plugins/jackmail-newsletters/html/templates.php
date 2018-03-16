<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="TemplatesController as t">
	<?php if ( $current_page === 'templates' ) { ?>
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
	<?php } ?>
	<div ng-hide="$root.show_help2" class="jackmail_content">
		<div class="jackmail_templates_header">
			<div class="jackmail_title"><?php _e( 'Templates', 'jackmail-newsletters' ) ?></div>
			<div class="jackmail_grid_search_container jackmail_templates_header_field">
				<span class="jackmail_grid_search_input_container">
					<input class="jackmail_grid_search_input" ng-keyup="t.search_on_templates()" ng-model="t.templates_search" placeholder="<?php esc_attr_e( 'Search', 'jackmail-newsletters' ) ?>" type="text"/>
					<span class="dashicons dashicons-search"></span>
				</span>
			</div>
			<div class="jackmail_templates_header_field">
				<div jackmail-dropdown-button button-value="{{t.displayed_templates.templates ? '<?php echo esc_js( __( 'My templates', 'jackmail-newsletters' ) ) ?>' : t.displayed_templates.templates_gallery ? '<?php echo esc_js( __( 'Templates gallery', 'jackmail-newsletters' ) ) ?>' : ''}}"
				     titles-clicks-array="t.templates_type" titles-clicks-array-event="t.display_templates_or_templates_gallery( key )" >
				</div>
			</div>
			<div ng-show="t.displayed_templates.templates_gallery && t.templates_gallery_categories.length > 0" class="jackmail_templates_header_field">
				<div jackmail-dropdown-button button-value="{{t.selected_templates_gallery_category === '' ? '<?php echo esc_js( __( 'Categories', 'jackmail-newsletters' ) ) ?>' : t.selected_templates_gallery_category}}"
				     titles-clicks-array="t.templates_gallery_categories" titles-clicks-array-event="t.select_gallery_template_category( key, title )">
				</div>
			</div>
			<?php if ( $current_page === 'campaign' ) { ?>
			<div ng-show="t.current_page === 'campaign_page'" ng-click="lc.c_common.close_templates()" class="jackmail_close">
				<span class="dashicons dashicons-no-alt"></span>
			</div>
			<?php } ?>
		</div>
		<div class="jackmail_previews_grid_container">
			<div>
				<div ng-repeat="( key, templates ) in t.templates_grid track by $index" class="jackmail_previews_grid_column">
					<?php if ( $current_page === 'templates' ) { ?>
					<div ng-show="key === 0" ng-click="t.create_new_template()" class="jackmail_create_template">
						<img ng-src="{{$root.settings.jackmail_url}}img/create_template.png" alt=""/>
						<div>
							<div>
								<input type="button" value="<?php esc_attr_e( 'Create template', 'jackmail-newsletters' ) ?>"/>
							</div>
						</div>
					</div>
					<?php } ?>
					<div ng-repeat="( subkey, template ) in templates track by $index" ng-hide="key === 0 && subkey === 0 && t.current_page === 'templates_page'">
						<span class="jackmail_previews_grid_preview">
							<img ng-src="{{template.preview}}" alt=""/>
						</span>
						<div>
							<div>
								<p class="jackmail_previews_grid_title">{{template.name}}</p>
								<p ng-show="t.displayed_templates.templates">{{template.updated_date_gmt | formatedDate : 'gmt_to_timezone' : 'hours'}}</p>
								<br/>
								<div class="jackmail_previews_grid_buttons">
									<?php if ( $current_page === 'templates' ) { ?>
									<div ng-show="t.displayed_templates.templates" class="jackmail_previews_grid_dropdown_actions">
										<div ng-hide="template.show_delete_confirmation" ng-mouseleave="t.hide_actions( key, subkey )">
											<div ng-click="t.display_actions( key, subkey )">
												<span><?php _e( 'Actions', 'jackmail-newsletters' ) ?></span>
												<span class="dashicons dashicons-arrow-down-alt2"></span>
											</div>
											<div ng-show="template.show_actions">
												<div>
													<span ng-click="t.create_campaign_with_template( template.id )">
														<?php _e( 'Create a campaign', 'jackmail-newsletters' ) ?>
													</span>
													<span ng-click="t.edit_template( template.id )">
														<?php _e( 'Edit', 'jackmail-newsletters' ) ?>
													</span>
													<span ng-click="t.duplicate_template( template.id )">
														<?php _e( 'Duplicate', 'jackmail-newsletters' ) ?>
													</span>
													<span ng-click="t.delete_confirm( key, subkey )">
														<?php _e( 'Delete', 'jackmail-newsletters' ) ?>
													</span>
												</div>
											</div>
										</div>
									</div>
									<p ng-show="template.show_delete_confirmation" class="jackmail_previews_grid_buttons_confirm">
										<span ng-click="t.delete_template( template.id )" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
										<span ng-click="t.delete_cancel( key, subkey )" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
									</p>
									<div ng-show="t.displayed_templates.templates_gallery" class="jackmail_previews_grid_buttons">
										<input ng-click="t.import_gallery_template( template.id )" type="button" value="<?php esc_attr_e( 'Create', 'jackmail-newsletters' ) ?>"/>
									</div>
									<?php } else { ?>
									<div class="jackmail_previews_grid_buttons">
										<input ng-click="lc.c_common.import_template_in_campaign( t.displayed_templates.templates ? 'template' : 'gallery_template', template.id )" type="button" value="<?php esc_attr_e( 'Import template', 'jackmail-newsletters' ) ?>"/>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="jackmail_clear_both"></div>
		<div ng-show="( t.current_page === 'templates_page' && t.nb_templates_grid === 1 ) || ( t.current_page === 'campaign_page' && t.nb_templates_grid === 0 )" class="jackmail_none">
			<p class="jackmail_none_title"><?php _e( 'No templates for this selection', 'jackmail-newsletters' ) ?></p>
		</div>
	</div>
</div>
<?php } ?>