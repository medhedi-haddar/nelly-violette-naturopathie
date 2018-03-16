<?php if ( defined( 'ABSPATH' ) ) { ?>
<div class="jackmail_center">
	<div>
		<p class="jackmail_title">
			<span ng-show="$root.show_account_connection_popup_form === 'create'"><?php _e( 'Create an account', 'jackmail-newsletters' ) ?></span>
			<span ng-show="$root.show_account_connection_popup_form !== 'create'"><?php _e( 'Connect to my account', 'jackmail-newsletters' ) ?></span>
		</p>
		<?php if ( ! $is_authenticated ) { ?>
		<p class="jackmail_grey jackmail_m_b_25">
			<span ng-show="$root.show_account_connection_popup_form === 'create'">
				<?php _e( 'Create an account, use our high deliverability servers and get statistics of your campaigns.', 'jackmail-newsletters' ) ?>
				<br>
				<?php _e( 'Get <span class="jackmail_bold">100 free credits per day</span> and send your campaign with us.', 'jackmail-newsletters' ) ?>
			</span>
			<span ng-show="$root.show_account_connection_popup_form === 'connection'"><?php _e( 'Sign in with your Jackmail IDs and reach your data easily.', 'jackmail-newsletters' ) ?></span>
		</p>
		<?php } ?>
		<div class="jackmail_settings_login_container jackmail_settings_login_installation_container">
			<?php if ( ! $is_authenticated ) { ?>
			<div ng-show="$root.show_account_connection_popup_form === 'create'" class="jackmail_settings_login">
				<div class="jackmail_input_create_account_container_two_fields">
					<p class="jackmail_input_create_account_container" ng-class="ac.login_error.firstname ? 'jackmail_input_create_account_container_error' : ''">
						<input type="text" ng-model="ac.create_login.firstname" placeholder="<?php esc_attr_e( 'First name', 'jackmail-newsletters' ) ?>" ng-keyup="ac.recheck_account_creation()"/>
						<span class="dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Please enter your last name', 'jackmail-newsletters' ) ?>"></span>
					</p>
					<p class="jackmail_input_create_account_container" ng-class="ac.login_error.lastname ? 'jackmail_input_create_account_container_error' : ''">
						<input type="text" ng-model="ac.create_login.lastname" placeholder="<?php esc_attr_e( 'Last name', 'jackmail-newsletters' ) ?>" ng-keyup="ac.recheck_account_creation()"/>
						<span class="dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Please enter your first name', 'jackmail-newsletters' ) ?>"></span>
					</p>
				</div>
				<p class="jackmail_input_create_account_container" ng-class="ac.login_error.email ? 'jackmail_input_create_account_container_error' : ''">
					<input type="text" ng-model="ac.email" placeholder="<?php esc_attr_e( 'Email', 'jackmail-newsletters' ) ?>" ng-keyup="ac.recheck_account_creation()"/>
					<span class="dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Email not valid', 'jackmail-newsletters' ) ?>"></span>
				</p>
				<div class="jackmail_input_create_account_container_two_fields">
					<p class="jackmail_input_create_account_container" ng-class="ac.login_error.password ? 'jackmail_input_create_account_container_error' : ''">
						<input type="password" ng-model="ac.create_login.password" placeholder="<?php esc_attr_e( 'Password', 'jackmail-newsletters' ) ?>" ng-keyup="ac.recheck_account_creation()"/>
						<span class="dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Password length must be at least 8 characters', 'jackmail-newsletters' ) ?>"></span>
					</p>
					<p class="jackmail_input_create_account_container" ng-class="ac.login_error.password ? 'jackmail_input_create_account_container_error' : ''">
						<input type="password" ng-model="ac.create_login.password_confirmation" placeholder="<?php esc_attr_e( 'Confirmation', 'jackmail-newsletters' ) ?>" ng-keyup="ac.recheck_account_creation()"/>
						<span class="dashicons dashicons-editor-help" title="<?php esc_attr_e( 'Please insert the same password', 'jackmail-newsletters' ) ?>"></span>
					</p>
				</div>
				<p class="jackmail_center jackmail_bold">
					<span jackmail-checkbox="ac.terms" ng-click="ac.check_uncheck_terms()"></span>
					<span ng-click="ac.check_uncheck_terms()"><?php _e( 'I agree with the Jackmail <a href="https://www.jackmail.com/terms-conditions" target="_blank">terms and conditions</a>', 'jackmail-newsletters' ) ?></span>
				</p>
				<p class="jackmail_center">
					<input ng-click="ac.account_creation()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Create my account', 'jackmail-newsletters' ) ?>"/>
					<input ng-click="ac.show_login_form( 'connection' )" type="button" class="jackmail_green_transparent_button jackmail_m_l_10" value="<?php esc_attr_e( 'Sign in', 'jackmail-newsletters' ) ?>"/>
				</p>
			</div>
			<?php } ?>
			<div class="jackmail_settings_login">
				<div ng-show="$root.show_account_connection_popup_form === 'connection'">
					<p ng-show="ac.account_created" class="jackmail_message_info">
						<?php _e( 'Your account has been created successfully. You will receive an activation email shortly', 'jackmail-newsletters' ) ?>
					</p>
					<p ng-show="ac.account_not_actived" class="jackmail_message_info jackmail_message_info_error">
						<?php _e( 'Your account hasn\'t been activated.<br/>In case you haven\'t received the activation email, you can always ask to receive it again:', 'jackmail-newsletters' ) ?>
						<input ng-click="ac.resend_activation_email()" class="jackmail_white_button" type="button" value="<?php esc_attr_e( 'Send the email again', 'jackmail-newsletters' ) ?>"/>
					</p>
					<p ng-show="ac.account_ids_not_valid" class="jackmail_message_info jackmail_message_info_error">
						<?php _e( 'Invalid username or password', 'jackmail-newsletters' ) ?>
					</p>
					<p ng-show="ac.account_resend_activation_email" class="jackmail_message_info jackmail_message_info_error">
						<?php _e( 'Please check your email and click the confirmation link.', 'jackmail-newsletters' ) ?>
					</p>
					<p>
						<input ng-model="ac.email" type="text" placeholder="<?php esc_attr_e( 'Email', 'jackmail-newsletters' ) ?>"/>
					</p>
					<p class="jackmail_m_b_5">
						<input ng-model="ac.login.password" type="password" placeholder="<?php esc_attr_e( 'Password', 'jackmail-newsletters' ) ?>"/>
					</p>
					<p ng-click="ac.show_login_form( 'new_password' )" class="jackmail_green jackmail_pointer jackmail_m_t_5"><?php esc_attr_e( 'Lost your password?', 'jackmail-newsletters' ) ?></p>
					<p class="jackmail_center jackmail_m_t_20">
						<input ng-click="ac.account_connection()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Sign in to my account', 'jackmail-newsletters' ) ?>"/>
						<?php if ( ! $is_authenticated ) { ?>
						<br/>
						<span ng-hide="ac.current_page_type === 'installation' && ac.account_created" ng-click="ac.show_login_form( 'create' )" class="jackmail_connect_account">
							<?php _e( 'Create my account', 'jackmail-newsletters' ) ?>
						</span>
						<?php } ?>
					</p>
				</div>
				<div ng-show="$root.show_account_connection_popup_form === 'new_password'">
					<p ng-click="ac.show_login_form( 'connection' )" class="jackmail_settings_login_back">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
						<?php _e( 'Back', 'jackmail-newsletters' ) ?>
					</p>
					<p class="jackmail_grey jackmail_center jackmail_m_b_25">
						<?php _e( 'We\'ll send you an email to reset your password', 'jackmail-newsletters' ) ?>
					</p>
					<p>
						<input ng-model="ac.email" type="text" placeholder="<?php esc_attr_e( 'Email', 'jackmail-newsletters' ) ?>"/>
					</p>
					<p class="jackmail_center">
						<input ng-click="ac.account_reset()" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Reset password', 'jackmail-newsletters' ) ?>"/>
					</p>
				</div>
				<div ng-show="$root.show_account_connection_popup_form === 'new_password_confirm'" class="jackmail_center jackmail_mt_50">
					<p class="jackmail_grey">
						<?php _e( 'An email has been sent to reset your password.', 'jackmail-newsletters' ) ?>
					</p>
					<br/>
					<p>
						<input ng-click="ac.show_login_form( 'connection' )" type="button" class="jackmail_green_button" value="<?php esc_attr_e( 'Connect', 'jackmail-newsletters' ) ?>"/>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>