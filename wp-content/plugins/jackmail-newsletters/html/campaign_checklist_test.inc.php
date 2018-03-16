<?php if ( defined( 'ABSPATH' ) ) { ?>
	<p class="jackmail_title jackmail_center"><?php _e( 'Send a test', 'jackmail-newsletters' ) ?></p>
	<div class="jackmail_check_campaign">
		<div ng-hide="lc.c_common.display_send_test_confirmation">
			<div class="jackmail_icon_ok dashicons" title="{{lc.c_common.send_test_status.error_email}}" ng-class="lc.c_common.send_test_status.error_email === '' ? 'dashicons-yes' : 'dashicons-no-alt'"></div>
			<input type="text" class="jackmail_test_campaign" ng-keyup="lc.c_common.send_test_check()" placeholder="<?php esc_attr_e( 'email@example.com', 'jackmail-newsletters' ) ?>" ng-model="lc.c_common.test_campaign_recipient"/>
			<div class="jackmail_right">
				<input ng-click="lc.c_common.send_test_confirmation_validation()" type="button" value="<?php esc_attr_e( 'Send a test', 'jackmail-newsletters' ) ?>"
					ng-class="lc.c_common.send_test_status.campaign_ok ? 'jackmail_green_button' : 'jackmail_white_button'"/>
			</div>
		</div>
		<div ng-show="lc.c_common.display_send_test_confirmation" class="jackmail_overflow_hidden ng-hide-animate">
			<div class="jackmail_left jackmail_m_t_6"><?php _e( '1 credit will be used for this test. Do you wish to continue?', 'jackmail-newsletters' ) ?></div>
			<div class="jackmail_right">
				<span ng-click="lc.c_common.send_test()" class="jackmail_confirm_icon dashicons dashicons-yes" title="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"></span>
				<span ng-click="lc.c_common.cancel_test()" class="jackmail_confirm_icon dashicons dashicons-no-alt" title="<?php esc_attr_e( 'Cancel', 'jackmail-newsletters' ) ?>"></span>
			</div>
		</div>
	</div>
<?php } ?>