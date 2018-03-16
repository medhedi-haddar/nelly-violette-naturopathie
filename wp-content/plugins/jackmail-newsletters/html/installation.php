<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="InstallationController as i">
	<div ng-hide="$root.show_help2">
		<div ng-show="i.current_step === 1" class="jackmail_installation_content_460 jackmail_mh_360">
			<p class="jackmail_logo"></p>
			<p class="jackmail_title"><?php _e( 'Welcome to Jackmail', 'jackmail-newsletters' ) ?></p>
			<p class="jackmail_grey jackmail_m_b_50"><?php _e( 'Thanks for using Jackmail to send your emails!<br/>You are just few clicks away to use the best newsletter plugin.', 'jackmail-newsletters' ) ?></p>
			<div ng-show="!i.configuration_ok">
				<div ng-show="!$root.settings.jackmail_file_path_exists" class="jackmail_installation_missing_configuration">
					<p><?php _e( 'The file', 'jackmail-newsletters' ) ?> "{{$root.settings.jackmail_file_path_name}}" <?php _e( 'in the Jackmail folder doesn\'t exist.', 'jackmail-newsletters' ) ?></p>
				</div>
				<div ng-show="!$root.settings.jackmail_file_path_writable" class="jackmail_installation_missing_configuration">
					<p><?php _e( 'The file', 'jackmail-newsletters' ) ?> "{{$root.settings.jackmail_file_path_name}}" <?php _e( 'in the Jackmail folder has to be editable.', 'jackmail-newsletters' ) ?></p>
				</div>
				<div ng-show="!$root.settings.openssl_random_pseudo_bytes_extension_function_exists" class="jackmail_installation_missing_configuration">
					<p><?php _e( 'You have to activate the extension "openssl_random_pseudo_bytes" on your web server to be able to continue.', 'jackmail-newsletters' ) ?></p>
				</div>
				<div ng-show="!$root.settings.gzdecode_gzencode_function_exists" class="jackmail_installation_missing_configuration">
					<p><?php _e( 'The Php features "gzdecode" or "gzencode" were not found.', 'jackmail-newsletters' ) ?>
				</div>
				<div ng-show="!$root.settings.base64_decode_base64_encode_function_exists" class="jackmail_installation_missing_configuration">
					<p><?php _e( 'The Php features "base64_decode" or "base64_encode" were not found.', 'jackmail-newsletters' ) ?>
				</div>
				<div ng-show="!$root.settings.json_encode_json_decode_function_exists" class="jackmail_installation_missing_configuration">
					<p><?php _e( 'The Php features "json_encode" or "json_decode" were not found.', 'jackmail-newsletters' ) ?>
				</div>
				<div ng-show="!$root.settings.image_create_from_string_get_image_size_from_string_function_exists" class="jackmail_installation_missing_configuration">
					<p><?php _e( 'The Php features "imagecreatefromstring" or "getimagesizefromstring" were not found.', 'jackmail-newsletters' ) ?>
				</div>
				<p><input ng-click="i.reload_page()" type="button" class="jackmail_green_button jackmail_w_185" value="<?php esc_attr_e( 'Reload the page', 'jackmail-newsletters' ) ?>"/></p>
			</div>
			<div ng-show="i.configuration_ok">
				<p><input ng-click="i.go_step( 2 )" type="button" class="jackmail_green_button jackmail_w_185" value="<?php esc_attr_e( 'Continue', 'jackmail-newsletters' ) ?>"/></p>
			</div>
		</div>
		<div ng-show="i.current_step === 2" class="jackmail_installation_content_460 jackmail_mh_360">
			<p class="jackmail_logo"></p>
			<p class="jackmail_title"><?php _e( 'Install Emailbuilder', 'jackmail-newsletters' ) ?></p>
			<p class="jackmail_grey"><?php _e( 'To use Emailbuilder, you need to accept the general terms and conditions of Emailbuilder.', 'jackmail-newsletters' ) ?></p>
			<div ng-show="$root.emailbuilder_popup_licence !== ''">
				<div class="jackmail_emailbuider_conditions" ng-bind-html="$root.emailbuilder_popup_licence"></div>
				<div>
					<input ng-click="i.go_step( 3 )" type="button" class="jackmail_input_transparent" value="<?php esc_attr_e( 'Decline', 'jackmail-newsletters' ) ?>"/>
					<input ng-click="i.install_emailbuilder()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'I accept', 'jackmail-newsletters' ) ?>"/>
				</div>
			</div>
			<div ng-show="$root.emailbuilder_popup_licence === ''">
				<p class="jackmail_grey"><?php _e( 'Error while loading Emailbuilder licence.', 'jackmail-newsletters' ) ?></p>
				<p><input ng-click="i.go_step( 3 )" type="button" class="jackmail_green_button jackmail_w_185" value="<?php esc_attr_e( 'Continue', 'jackmail-newsletters' ) ?>"/></p>
			</div>
		</div>
		<div ng-show="i.current_step === 3" class="jackmail_installation_content_460 jackmail_mh_360">
			<p class="jackmail_logo"></p>
			<p class="jackmail_title"><?php _e( 'Synchronize your WordPress with Jackmail', 'jackmail-newsletters' ) ?></p>
			<p class="jackmail_grey"><?php _e( 'With Jackmail, you can always synchronize your account with other plugins and enjoy the efficiency of using your WordPress data without any export/import needed.', 'jackmail-newsletters' ) ?></p>
			<p ng-show="i.nb_plugins_actived > 0" class="jackmail_grey"><?php _e( 'Here is the list of plugins you can activate and synchronize with Jackmail:', 'jackmail-newsletters' ) ?></p>
			<div ng-show="i.nb_plugins_actived > 0" class="jackmail_installation_plugins">
				<div ng-repeat="( key, plugin ) in i.plugins track by $index" ng-show="plugin.active">
					<div class="jackmail_settings_selector">
						<span jackmail-checkbox="plugin.selected" ng-click="i.select_or_unselect_plugin( key )"></span>
					</div>
					<div class="jackmail_settings_description">
						<p ng-click="i.select_or_unselect_plugin( key )" class="jackmail_pointer">{{plugin.name}}</p>
						<p class="jackmail_grey">{{plugin.description}}</p>
					</div>
				</div>
			</div>
			<div ng-show="i.nb_plugins_actived === 0" class="jackmail_installation_plugins">
				<p class="jackmail_center jackmail_bold">
					<?php _e( 'You haven\'t data but Jackmail is compatible with a lot of plugins.', 'jackmail-newsletters' ) ?>
					<br/>
					<?php _e( '<a href="https://www.jackmail.com/connectors" target="_blank">Discover it</a>.', 'jackmail-newsletters' ) ?>
				</p>
			</div>
			<p class="jackmail_mt_40">
				<input ng-click="i.import_plugins()"
				    ng-show="i.nb_plugins_actived > 0"
					type="button" class="jackmail_green_button jackmail_w_185" value="<?php esc_attr_e( 'Import data', 'jackmail-newsletters' ) ?>"/>
				<input ng-click="i.go_step( 4 )" type="button" class="jackmail_green_transparent_button jackmail_w_185" value="<?php esc_attr_e( 'Skip this step', 'jackmail-newsletters' ) ?>"/>
			</p>
			<p class="jackmail_center"><?php _e( 'Any plugins missing? <a href="mailto:hey@jackmail.com">Let us know!</a>', 'jackmail-newsletters' ) ?></p>
		</div>
		<div ng-show="i.current_step === 4" class="jackmail_installation_content_460 jackmail_mh_360">
			<p class="jackmail_logo"></p>
			<div ng-hide="$root.settings.is_authenticated">
				<div ng-controller="AccountConnectionController as ac">
					<?php include_once plugin_dir_path( __FILE__ ) . 'account_connection.inc.php' ?>
					<p>
						<span ng-click="i.skip_account_creation()" class="jackmail_connect_account">
							<?php _e( 'Skip this step', 'jackmail-newsletters' ) ?>
						</span>
					</p>
				</div>
			</div>
			<div ng-show="$root.settings.is_authenticated">
				<p class="jackmail_installation_connected">
					<?php _e( 'You are actually connected with', 'jackmail-newsletters' ) ?> <span class="jackmail_bold">{{$root.email}}</span>
				</p>
				<p>
					<input ng-click="i.go_step( 5 )" type="button" class="jackmail_green_button jackmail_w_185" value="<?php esc_attr_e( 'Continue', 'jackmail-newsletters' ) ?>"/>
				</p>
			</div>
		</div>
		<div ng-show="i.current_step === 5">
			<div class="jackmail_installation_content_460">
				<p class="jackmail_logo"></p>
				<p class="jackmail_title"><?php _e( 'Thanks for your installation', 'jackmail-newsletters' ) ?></p>
				<p><input ng-click="$root.change_page( 'campaigns' )" value="<?php esc_attr_e( 'Start my first campaign', 'jackmail-newsletters' ) ?>" class="jackmail_green_button" type="button"/></p>
			</div>
			<div class="jackmail_installation_end_container">
				<div class="jackmail_installation_end">
					<div class="jackmail_installation_end_title">
						<?php _e( 'Jackmail', 'jackmail-newsletters' ) ?>
					</div>
					<div class="jackmail_installation_end_link" ng-click="$root.change_page( 'campaigns' )"><?php _e( 'Campaigns', 'jackmail-newsletters' ) ?></div>
					<div class="jackmail_installation_end_link" ng-click="$root.change_page( 'lists' )"><?php _e( 'Lists', 'jackmail-newsletters' ) ?></div>
					<div class="jackmail_installation_end_link" ng-click="$root.change_page( 'templates' )"><?php _e( 'Templates', 'jackmail-newsletters' ) ?></div>
					<div class="jackmail_installation_end_link" ng-click="$root.change_page( 'statistics' )"><?php _e( 'Statistics', 'jackmail-newsletters' ) ?></div>
					<div class="jackmail_installation_end_link" ng-click="$root.change_page( 'settings' )"><?php _e( 'Settings', 'jackmail-newsletters' ) ?></div>
				</div>
				<div class="jackmail_installation_end_campaigns" ng-click="$root.change_page( 'campaigns' )">
					<?php _e( 'Send your first newsletter in less than 5 minutes', 'jackmail-newsletters' ) ?>
				</div>
				<div class="jackmail_installation_end_campaigns_arrow"></div>
				<div class="jackmail_installation_end_lists" ng-click="$root.change_page( 'lists' )">
					<?php _e( 'Organize your contacts, and target your sends', 'jackmail-newsletters' ) ?>
				</div>
				<div class="jackmail_installation_end_lists_arrow"></div>
				<div class="jackmail_installation_end_templates" ng-click="$root.change_page( 'templates' )">
					<?php _e( 'With our Drag\'n\'Drop editor, creating an email is a child\'s play', 'jackmail-newsletters' ) ?>
				</div>
				<div class="jackmail_installation_end_templates_arrow"></div>
				<div class="jackmail_installation_end_statistics" ng-click="$root.change_page( 'statistics' )">
					<?php _e( 'Measure your ROI\'s and boost your success', 'jackmail-newsletters' ) ?>
				</div>
				<div class="jackmail_installation_end_statistics_arrow"></div>
			</div>
		</div>
		<div ng-show="i.current_step < 5" class="jackmail_installation_content_460 jackmail_installation_steps">
			<div class="jackmail_installation_step"></div>
			<div class="jackmail_installation_step_border"></div>
			<div class="jackmail_installation_step_1" ng-class="i.current_step === 1 ? 'jackmail_installation_step_current' : ''"></div>
			<div class="jackmail_installation_step_2" ng-class="i.current_step === 2 ? 'jackmail_installation_step_current' : ''"></div>
			<div class="jackmail_installation_step_3" ng-class="i.current_step === 3 ? 'jackmail_installation_step_current' : ''"></div>
			<div class="jackmail_installation_step_4" ng-class="i.current_step === 4 ? 'jackmail_installation_step_current' : ''"></div>
			{{i.current_step}} / 4
		</div>
		<p ng-show="i.current_step === 1" class="jackmail_installation_content_460 jackmail_mt_50 jackmail_conditions">
			<?php _e( 'Any time Jackmail and its Services are used, you expressly acknowledge, knowledge, understanding and acceptance of the Terms and Conditions and EmailBuilder License, without any reservations.', 'jackmail-newsletters' ) ?>
		</p>
	</div>
</div>
<?php } ?>