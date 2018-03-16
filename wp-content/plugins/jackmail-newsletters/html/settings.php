<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="SettingsController as s" ng-class="!s.show_menu ? 'jackmail_my_account_hide_menu' : ''">
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
	<div ng-hide="$root.show_help2">
		<div class="jackmail_content">
			<div class="jackmail_settings_container">
				<p class="jackmail_my_account_title"><?php _e( 'Integrated extensions', 'jackmail-newsletters' ) ?></p>
				<p><?php _e( 'With Jackmail you can synchronize your account with other plugins and enjoy the efficiency of using your WordPress data without any export/import needed.', 'jackmail-newsletters' ) ?></p>
				<div ng-repeat="( key, plugin ) in s.plugins track by $index" ng-show="plugin.active">
					<div class="jackmail_settings_selector">
						<span jackmail-checkbox="plugin.selected" ng-click="s.import_plugins( key )">
						</span>
					</div>
					<div class="jackmail_settings_description">
						<p ng-click="s.import_plugins( key )" class="jackmail_pointer">{{plugin.name}}</p>
						<p class="jackmail_grey">
							{{plugin.description}}
							<span ng-show="s.plugin_info( plugin.name )" class="jackmail_info dashicons dashicons-info" jackmail-tooltip="<?php esc_attr_e( 'Only forms with an email field are visible in Jackmail', 'jackmail-newsletters' ) ?>"></span>
						</p>
					</div>
				</div>
				<div ng-show="s.nb_plugins_actived === 0" class="jackmail_settings_selector jackmail_settings_selector_no_plugins">
					<p>
						<?php _e( 'You haven\'t data but Jackmail is compatible with a lot of plugins.', 'jackmail-newsletters' ) ?>
						<?php _e( '<a href="https://www.jackmail.com/connectors" target="_blank">Discover it</a>.', 'jackmail-newsletters' ) ?>
					</p>
				</div>
			</div>
			<div class="jackmail_settings_container">
				<p class="jackmail_my_account_title"><?php _e( 'Connectors', 'jackmail-newsletters' ) ?></p>
				<p><?php _e( 'Warning: these requests should be handled by expert developers. They should not be associated with public webpages (html/form, javascript) but rather with redirection scripts or asynchronous calls hosted on servers (PHP, ASP, JSP…). Please use POST requests and activate IP restriction.', 'jackmail-newsletters' ) ?></p>
				<div>
					<div class="jackmail_settings_selector">
						<span jackmail-checkbox="s.connectors.active" ng-click="s.connectors_change_status()">
						</span>
					</div>
					<div class="jackmail_settings_description">
						<p ng-click="s.connectors_change_status()" class="jackmail_pointer">
							<?php _e( 'Activate connectors', 'jackmail-newsletters' ) ?>
						</p>
						<p class="jackmail_grey"><?php _e( 'Add / Modify / Delete data from your contact lists from your app with our connectors.', 'jackmail-newsletters' ) ?></p>
					</div>
				</div>
				<div>
					<div class="jackmail_settings_selector">
						<span jackmail-checkbox="s.connectors.ip_restriction"
							checkbox-click="s.connectors_configure_ip_restriction()"
							checkbox-disabled="!s.connectors.active ? 'true' : ''">
						</span>
					</div>
					<div class="jackmail_settings_description">
						<p ng-click="s.connectors_configure_ip_restriction()" ng-class="s.connectors.active ? 'jackmail_pointer' : ''">
							<?php _e( 'Add an IP restriction', 'jackmail-newsletters' ) ?>
						</p>
						<p class="jackmail_grey"><?php _e( 'Allow certain IPs to monitor your contact lists and make sure your data in Jackmail are safe.', 'jackmail-newsletters' ) ?></p>
						<div ng-show="s.connectors.active && s.connectors.ip_restriction" class="jackmail_settings_restriction_allowed_ips">
							<div ng-repeat="( i, name ) in s.connectors.allowed_ips track by $index" class="jackmail_settings_restriction_allowed_ip">
								<span ng-hide="s.connectors.allowed_ips[ i ] === ''" ng-click="s.delete_connectors_allowed_ip( i )" class="dashicons dashicons-no-alt"></span>
								<span jackmail-content-editable when-enter="s.connectors_configure_allowed_ips()" input-value="s.connectors.allowed_ips[ i ]"></span>
							</div>
							<div ng-show="s.display_button_add_connectors_allowed_ip" class="jackmail_settings_restriction_add_allowed_ip">
								<input ng-click="s.add_connectors_allowed_ip()" class="jackmail_green_button" type="button" value="+"/>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="jackmail_settings_container jackmail_settings_plr_15">
				<p class="jackmail_my_account_title"><?php _e( 'Customize the domain', 'jackmail-newsletters' ) ?></p>
				<p>
					<?php _e( 'Jackmail allows you to personalize the sender domain name and any links included to your campaigns with your own company name.', 'jackmail-newsletters' ) ?>
					<br/>
					<?php _e( 'By personalizing the name of your domain, you will reassure your subscribers, which will in turn improve the deliverability of your campaigns as well as their click rates.', 'jackmail-newsletters' ) ?>
				</p>
				<div ng-show="$root.settings.is_authenticated">
					<div ng-show="s.edit_domain && s.show_steps" class="jackmail_settings_domain_info">
						<span></span>
						<div>
							<p><?php _e( 'Sub-domain:', 'jackmail-newsletters' ) ?></p>
							<div ng-class="s.domain.subdomain !== '' ? 'jackmail_settings_domain_info_with_data' : 'jackmail_settings_domain_info_without_data'">
								<span>{{s.domain.subdomain}}</span>
								<span ng-click="s.go_domain_step( 1 )" class="dashicons dashicons-edit"></span>
							</div>
						</div>
						<div>
							<p><?php _e( 'TXT record:', 'jackmail-newsletters' ) ?></p>
							<div ng-class="s.domain.txt !== '' ? 'jackmail_settings_domain_info_with_data' : 'jackmail_settings_domain_info_without_data'">
								<span>{{s.domain.txt}}</span>
								<span ng-click="s.go_domain_step( 2 )" class="dashicons dashicons-edit"></span>
							</div>
						</div>
						<div>
							<p><?php _e( 'NS record:', 'jackmail-newsletters' ) ?></p>
							<div ng-class="s.domain.ns1 !== '' ? 'jackmail_settings_domain_info_with_data' : 'jackmail_settings_domain_info_without_data'">
								<span>{{s.domain.ns1}}</span>
								<span ng-click="s.go_domain_step( 3 )" class="dashicons dashicons-edit"></span>
							</div>
						</div>
					</div>
					<div ng-hide="s.edit_domain">
						<div>
							<p class="jackmail_bold"><?php _e( 'Sub-domain:', 'jackmail-newsletters' ) ?></p>
							<div class="jackmail_m_b_15">{{s.domain.subdomain}}</div>
						</div>
						<div>
							<p class="jackmail_bold"><?php _e( 'TXT record:', 'jackmail-newsletters' ) ?></p>
							<div class="jackmail_code">{{s.domain.txt}}</div>
						</div>
						<div>
							<p class="jackmail_bold"><?php _e( 'NS record:', 'jackmail-newsletters' ) ?></p>
							<div class="jackmail_code">{{s.domain.ns1}}<br/>{{s.domain.ns2}}</div>
						</div>
						<p>
							<input ng-click="s.show_edit_domain()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Modify', 'jackmail-newsletters' ) ?>"/>
							<span jackmail-button-delete delete-value="<?php esc_attr_e( 'Delete', 'jackmail-newsletters' ) ?>" when-delete="s.delete_domain()"></span>
						</p>
						<p ng-show="s.domain.is_valid === false" class="jackmail_domain_info_available jackmail_bold">
							<span><?php _e( 'Custom domain is actually not available.', 'jackmail-newsletters' ) ?></span>
						</p>
					</div>
					<div ng-show="s.edit_domain">
						<div ng-show="s.domain_step === 1">
							<p class="jackmail_bold"><?php _e( 'Step', 'jackmail-newsletters' ) ?> 1 - <?php _e( 'Create a sub-domain', 'jackmail-newsletters' ) ?></p>
							<p><input type="text" class="jackmail_settings_subdomain jackmail_input" ng-model="s.domain.subdomain" placeholder="<?php esc_attr_e( 'Ex: link.example.com', 'jackmail-newsletters' ) ?>"/></p>
							<p>
								<input ng-show="s.edit_domain && s.domain_configured" ng-click="s.cancel_edit_domain()" type="button" value="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"/>
								<input ng-click="s.go_domain_next_step()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Next', 'jackmail-newsletters' ) ?>"/>
							</p>
						</div>
						<div ng-show="s.domain_step === 2">
							<p class="jackmail_bold"><?php _e( 'Step', 'jackmail-newsletters' ) ?> 2 - <?php _e( 'Add this TXT record with your provider', 'jackmail-newsletters' ) ?></p>
							<div class="jackmail_code">{{s.domain.txt}}</div>
							<p>
								<input ng-click="s.go_domain_previous_step()" type="button" value="<?php esc_attr_e( 'Back', 'jackmail-newsletters' ) ?>"/>
								<input ng-click="s.go_domain_next_step()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Next', 'jackmail-newsletters' ) ?>"/>
							</p>
						</div>
						<div ng-show="s.domain_step === 3">
							<p class="jackmail_bold"><?php _e( 'Step', 'jackmail-newsletters' ) ?> 3 - <?php _e( 'Add these NS records with your provider', 'jackmail-newsletters' ) ?></p>
							<div class="jackmail_code">{{s.domain.ns1}}<br/>{{s.domain.ns2}}</div>
							<p><?php _e( 'Check your sub-domain settings', 'jackmail-newsletters' ) ?></p>
							<p><?php _e( 'Updates may take up to two days before being live.', 'jackmail-newsletters' ) ?></p>
							<p>
								<input ng-click="s.go_domain_previous_step()" type="button" value="<?php esc_attr_e( 'Back', 'jackmail-newsletters' ) ?>"/>
								<input ng-click="s.create_domain_delegation()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Save', 'jackmail-newsletters' ) ?>"/>
							</p>
						</div>
					</div>
				</div>
				<div ng-hide="$root.settings.is_authenticated">
					<p><?php _e( 'To customize your domain, you must be logged into your Jackmail account.', 'jackmail-newsletters' ) ?></p>
				</div>
			</div>
			<div class="jackmail_settings_container">
				<p class="jackmail_my_account_title"><?php _e( 'Tracking', 'jackmail-newsletters' ) ?></p>
				<p><?php _e( 'Use our professionnal sender platform and get data about your recipients. Opens, clicks, technologies, see what happens to their emails after they hit send.', 'jackmail-newsletters' ) ?></p>
				<div>
					<div class="jackmail_settings_selector">
						<span jackmail-checkbox="s.link_tracking.active === '1'" ng-click="s.change_link_tracking()"></span>
					</div>
					<div class="jackmail_settings_description">
						<p ng-click="s.change_link_tracking()" class="jackmail_pointer">
							<?php _e( 'Activate tracking', 'jackmail-newsletters' ) ?>
						</p>
					</div>
				</div>
			</div>
            <div class="jackmail_settings_container">
                <p class="jackmail_my_account_title"><?php _e( 'User permissions', 'jackmail-newsletters' ) ?></p>
                <p><?php _e( 'Choose which users can access to Jackmail.', 'jackmail-newsletters' ) ?></p>
                <div class="jackmail_settings_role">
                    <div jackmail-dropdown-button dropdown-left="true" button-value="{{s.selected_role_text}}"
                         titles-clicks-grid="s.roles_dropdown"
                         titles-clicks-grid-checked="s.selected_role_array"
                         titles-clicks-grid-event="s.change_jackmail_role( key )">
                    </div>
                </div>
            </div>
			<div class="jackmail_settings_container" ng-show="!$root.settings.emailbuilder_installed">
				<p class="jackmail_my_account_title"><?php _e( 'Emailbuilder', 'jackmail-newsletters' ) ?></p>
				<p>
					<?php _e( 'Create newsletter without knowledges in HTML. Just drag-and-drop your content, import your WordPress posts and create beautiful responsives newsletter.', 'jackmail-newsletters' ) ?>
					<br/>
					<?php _e( 'With Emailbuilder you can also create automated workflows and templates.', 'jackmail-newsletters' ) ?>
					<span class="jackmail_bold"><?php _e( 'It\'s free!', 'jackmail-newsletters' ) ?></span>
				</p>
				<div>
					<p><input ng-click="$root.display_emailbuilder_popup()" class="jackmail_green_button" value="<?php esc_attr_e( 'Install Emailbuilder', 'jackmail-newsletters' ) ?>" type="button"></p>
				</div>
			</div>
			<div class="jackmail_settings_container" ng-show="$root.settings.emailbuilder_installed">
				<p class="jackmail_my_account_title"><?php _e( 'Emailbuilder', 'jackmail-newsletters' ) ?></p>
				<p>
					<?php _e( 'If you don\'t want use Emailbuilder anymore, click on link bellow.', 'jackmail-newsletters' ) ?>
				</p>
				<div>
					<p><input ng-click="s.uninstall_emailbuilder()" class="jackmail_green_button" value="<?php esc_attr_e( 'Uninstall Emailbuilder', 'jackmail-newsletters' ) ?>" type="button"></p>
				</div>
			</div>
			<div class="jackmail_settings_container jackmail_settings_plr_15">
				<p class="jackmail_my_account_title"><?php _e( 'Language', 'jackmail-newsletters' ) ?></p>
				<div>
					<p><?php _e( 'You want to translate the Jackmail plugin to your language ? Get in touch at <a href="mailto:hey@jackmail.com">hey@jackmail.com</a>', 'jackmail-newsletters' ) ?></p>
				</div>
			</div>
			<div ng-show="$root.settings.is_authenticated" class="jackmail_settings_container jackmail_settings_plr_15">
				<p class="jackmail_my_account_title"><?php _e( 'Change Jackmail account', 'jackmail-newsletters' ) ?></p>
				<p><?php _e( 'When logging out, you will loose all your data and will need to set up Jackmail again.', 'jackmail-newsletters' ) ?></p>
				<div>
					<p><?php _e( 'You are actually connected with', 'jackmail-newsletters' ) ?> <span class="jackmail_bold">{{s.email}}</span></p>
					<p ng-show="s.formula.nb_credits !== ''">
						<span class="jackmail_m_r_15">
							<?php _e( 'You have', 'jackmail-newsletters' ) ?>
							<span class="jackmail_bold">{{s.formula.nb_credits | numberSeparator}}</span>
							<span ng-show="s.formula.nb_credits <= 1"><?php _e( 'credit available', 'jackmail-newsletters' ) ?></span>
							<span ng-show="s.formula.nb_credits > 1"><?php _e( 'credits available', 'jackmail-newsletters' ) ?></span>
						</span>
					</p>
					<p ng-show="s.formula.product_key !== '' || s.formula.subscription_type !== ''">
						<span ng-show="s.formula.product_key !== '' && s.formula.subscription_type !== 'FREE'">
							<?php _e( 'Your plan:', 'jackmail-newsletters' ) ?>
							<span class="jackmail_bold">{{s.formula.product_key}}</span>
							<br/>
						</span>
						<span ng-show="s.formula.subscription_type === 'FREE'">
							<?php _e( 'This plan allows you to send up to', 'jackmail-newsletters' ) ?> <span class="jackmail_bold">100</span> <?php _e( 'emails per day', 'jackmail-newsletters' ) ?>
							<a href="https://www.jackmail.com/pricing" target="_blank" class="jackmail_button"><?php _e( 'Subscribe to a premium plan', 'jackmail-newsletters' ) ?></a>
						</span>
					</p>
					<p>
						<input ng-click="s.user_disconnect()" class="jackmail_green_button" value="<?php esc_attr_e( 'Log out', 'jackmail-newsletters' ) ?>" type="button"/>
					</p>
				</div>
			</div>
			<div ng-hide="$root.settings.is_authenticated" class="jackmail_settings_container jackmail_settings_plr_15">
				<p class="jackmail_my_account_title"><?php _e( 'My Jackmail account', 'jackmail-newsletters' ) ?></p>
				<div>
					<p>
						<input ng-click="$root.display_account_connection_popup( 'create' )" class="jackmail_green_button" value="<?php esc_attr_e( 'Create an account', 'jackmail-newsletters' ) ?>" type="button"/>
						<span class="jackmail_m_l_r_5"><?php _e( 'or', 'jackmail-newsletters' ) ?></span>
						<span ng-click="$root.display_account_connection_popup( 'connection' )" class="jackmail_connect_account"><?php _e( 'Sign in to my account', 'jackmail-newsletters' ) ?></span>
					</p>
				</div>
			</div>
			<div class="jackmail_settings_container jackmail_settings_plr_15">
				<span jackmail-checkbox="s.improvement.active" ng-click="s.change_improvement()"
					checkbox-title="<?php esc_attr_e( 'I agree to take part in anonymous data collection related to my interaction with the app to improve Jackmail\'s services.', 'jackmail-newsletters' ) ?>">
				</span>
			</div>
			<div class="jackmail_settings_container jackmail_settings_plr_15">
				<span jackmail-checkbox="s.support_chat.active" ng-click="s.change_support_chat()"
				    checkbox-title="<?php esc_attr_e( 'Enable support chat', 'jackmail-newsletters' ) ?>">
				</span>
			</div>
			<div class="jackmail_settings_container jackmail_settings_plr_15">
				<span jackmail-checkbox="s.premium_notification.active" ng-click="s.change_premium_notification()"
				    checkbox-title="<?php esc_attr_e( 'Enable notification for subscribe to a', 'jackmail-newsletters' ) ?>">
				</span>
				<span><a href="https://www.jackmail.com/pricing" target="_blank"><?php _e( 'premium plan', 'jackmail-newsletters' ) ?></a></span>
			</div>
			<div class="jackmail_settings_container jackmail_settings_plr_15">
				<span jackmail-checkbox="s.debug_displayed" ng-click="s.display_hide_debug()"
				    checkbox-title="<?php esc_attr_e( 'Show server informations', 'jackmail-newsletters' ) ?>">
				</span>
				<div ng-show="s.debug_displayed" class="jackmail_m_t_20">
					<p><input ng-click="s.display_debug()" class="jackmail_green_button" value="<?php esc_attr_e( 'Refresh display', 'jackmail-newsletters' ) ?>" type="button"/></p>
					<div>
						<div class="jackmail_settings_debug_table">
							<div>
								<div><?php _e( 'Server', 'jackmail-newsletters' ) ?></div>
								<div>{{s.debug.server}}</div>
							</div>
							<div>
								<div><?php _e( 'Database', 'jackmail-newsletters' ) ?></div>
								<div>{{s.debug.database}}</div>
							</div>
							<div>
								<div><?php _e( 'PHP', 'jackmail-newsletters' ) ?></div>
								<div>{{s.debug.php}}</div>
							</div>
							<div>
								<div><?php _e( 'Jackmail', 'jackmail-newsletters' ) ?></div>
								<div>{{s.debug.jackmail}}</div>
							</div>
							<div>
								<div><?php _e( 'Jackmail data', 'jackmail-newsletters' ) ?></div>
								<div>
									<input ng-click="s.manual_update_data()" type="button" value="<?php esc_attr_e( 'Rebuild', 'jackmail-newsletters' ) ?>"/>
								</div>
							</div>
							<div>
								<div><?php _e( 'Token', 'jackmail-newsletters' ) ?></div>
								<div>
									<input ng-click="$root.show_account_connection_popup = true" type="button" value="<?php esc_attr_e( 'Renew', 'jackmail-newsletters' ) ?>"/>
								</div>
							</div>
							<div>
								<div><?php _e( 'WordPress', 'jackmail-newsletters' ) ?></div>
								<div>{{s.debug.wordpress}}</div>
							</div>
							<div>
								<div><?php _e( 'Browser', 'jackmail-newsletters' ) ?></div>
								<div>{{s.debug.browser}}</div>
							</div>
							<div ng-show="s.debug.wordfence">
								<div><?php _e( 'Wordfence plugin', 'jackmail-newsletters' ) ?></div>
								<div><?php _e( 'Wordfence plugin detected', 'jackmail-newsletters' ) ?></div>
							</div>
						</div>
						<div ng-show="s.debug.logs !== ''">
							<span class="jackmail_bold"><?php _e( 'Logs:', 'jackmail-newsletters' ) ?></span>
							<textarea class="jackmail_settings_debug_logs" readonly>{{s.debug.logs}}</textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="jackmail_settings_container jackmail_settings_plr_15">
				<span jackmail-checkbox="s.debug_data_displayed" ng-click="s.display_hide_debug_data()"
				      checkbox-title="<?php esc_attr_e( 'Show data informations', 'jackmail-newsletters' ) ?>">
				</span>
				<div ng-show="s.debug_data_displayed" class="jackmail_m_t_20">
					<p><input ng-click="s.display_debug_data()" class="jackmail_green_button" value="<?php esc_attr_e( 'Refresh display', 'jackmail-newsletters' ) ?>" type="button"/></p>
					<div>
						<div>
							<span class="jackmail_bold"><?php _e( 'Campaigns data:', 'jackmail-newsletters' ) ?></span>
							<textarea class="jackmail_settings_debug_logs" readonly>{{s.debug_data.campaigns_data}}</textarea>
						</div>
						<div>
							<span class="jackmail_bold"><?php _e( 'Workflows data:', 'jackmail-newsletters' ) ?></span>
							<textarea class="jackmail_settings_debug_logs" readonly>{{s.debug_data.scenarios_data}}</textarea>
						</div>
						<div>
							<span class="jackmail_bold"><?php _e( 'Workflows details data:', 'jackmail-newsletters' ) ?></span>
							<textarea class="jackmail_settings_debug_logs" readonly>{{s.debug_data.scenarios_details_data}}</textarea>
						</div>
						<div>
							<span class="jackmail_bold"><?php _e( 'Lists data:', 'jackmail-newsletters' ) ?></span>
							<textarea class="jackmail_settings_debug_logs" readonly>{{s.debug_data.lists_data}}</textarea>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>