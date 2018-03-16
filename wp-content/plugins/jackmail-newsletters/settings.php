<?php


class Jackmail_Settings extends Jackmail_Settings_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		add_action( 'jackmail_cron_domain', array( $this, 'cron_domain_call' ) );

		add_action( 'jackmail_cron_clean_files', array( $this, 'cron_clean_files_call' ) );

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_settings_page', array( $this, 'settings_page_callback' ) );

			add_action( 'wp_ajax_jackmail_domain_configuration', array( $this, 'domain_configuration_callback' ) );

			add_action( 'wp_ajax_jackmail_domain_get_txt_ns', array( $this, 'domain_get_txt_ns_callback' ) );

			add_action( 'wp_ajax_jackmail_domain_create_delegation', array( $this, 'domain_create_delegation_callback' ) );

			add_action( 'wp_ajax_jackmail_domain_delete', array( $this, 'domain_delete_callback' ) );

			add_action( 'wp_ajax_jackmail_get_link_tracking', array( $this, 'get_link_tracking_callback' ) );

			add_action( 'wp_ajax_jackmail_set_link_tracking', array( $this, 'set_link_tracking_callback' ) );

			add_action( 'wp_ajax_jackmail_get_jackmail_role', array( $this, 'get_jackmail_role_callback' ) );

			add_action( 'wp_ajax_jackmail_set_jackmail_role', array( $this, 'set_jackmail_role_callback' ) );

			add_action( 'wp_ajax_jackmail_uninstall_emailbuilder', array( $this, 'uninstall_emailbuilder_callback' ) );

			add_action( 'wp_ajax_jackmail_credits_available', array( $this, 'credits_available_callback' ) );

			add_action( 'wp_ajax_jackmail_user_disconnect', array( $this, 'user_disconnect_callback' ) );

			add_action( 'wp_ajax_jackmail_get_debug', array( $this, 'get_degug_callback' ) );

			add_action( 'wp_ajax_jackmail_get_debug_data', array( $this, 'get_degug_data_callback' ) );

			add_action( 'wp_ajax_jackmail_get_improvement', array( $this, 'get_improvement_callback' ) );

			add_action( 'wp_ajax_jackmail_set_improvement', array( $this, 'set_improvement_callback' ) );

			add_action( 'wp_ajax_jackmail_get_support_chat', array( $this, 'get_support_chat_callback' ) );

			add_action( 'wp_ajax_jackmail_set_support_chat', array( $this, 'set_support_chat_callback' ) );

			add_action( 'wp_ajax_jackmail_get_premium_notification', array( $this, 'get_premium_notification_callback' ) );

			add_action( 'wp_ajax_jackmail_set_premium_notification', array( $this, 'set_premium_notification_callback' ) );

			add_action( 'wp_ajax_jackmail_manual_update_data', array( $this, 'jackmail_manual_update_data_callback' ) );

			add_action( 'wp_ajax_jackmail_update_cookies', array( $this, 'update_cookies_callback' ) );

		}

	}

	public function settings_page_callback() {
		$this->core->check_auth();
		$this->core->include_html_file( 'settings' );
		die;
	}

	public function domain_configuration_callback() {
		$this->core->check_auth();
		$data = $this->domain_configuration();
		wp_send_json( $data );
		die;
	}

	public function domain_get_txt_ns_callback() {
		$this->core->check_auth();
		$data = array(
			'txt' => '',
			'ns1' => '',
			'ns2' => ''
		);
		if ( isset( $_POST['subdomain'] ) ) {
			$subdomain = $this->core->request_text_data( $_POST['subdomain'] );
			$data      = $this->domain_get_txt_ns( $subdomain );
		}
		wp_send_json( $data );
		die;
	}

	public function domain_create_delegation_callback() {
		$this->core->check_auth();
		$json = array(
			'success'   => false,
			'subdomain' => '',
			'txt'       => '',
			'is_valid'  => false
		);
		if ( isset( $_POST['subdomain'] ) ) {
			$subdomain = $this->core->request_text_data( $_POST['subdomain'] );
			$json      = $this->domain_create_delegation( $subdomain );
		}
		wp_send_json( $json );
		die;
	}

	public function domain_delete_callback() {
		$this->core->check_auth();
		$this->domain_delete();
		wp_send_json_success();
		die;
	}

	public function get_link_tracking_callback() {
		$this->core->check_auth();
		$json = array(
			'active' => get_option( 'jackmail_link_tracking' )
		);
		wp_send_json( $json );
		die;
	}

	public function set_link_tracking_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['active'] ) ) {
			$tracking = $this->core->request_text_data( $_POST['active'] );
			$this->set_link_tracking( $tracking );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function get_jackmail_role_callback() {
		$this->core->check_auth();
		$json = $this->get_jackmail_role();
		wp_send_json( $json );
		die;
	}

	public function set_jackmail_role_callback() {
		$this->core->check_auth();
		$json = array(
			'success' => false
		);
		if ( isset( $_POST['role'] ) ) {
			$role            = $this->core->request_text_data( $_POST['role'] );
			$json['success'] = $this->set_jackmail_role( $role );
		}
		wp_send_json( $json );
		die;
	}

	public function uninstall_emailbuilder_callback() {
		$this->core->check_auth();
		update_option( 'jackmail_emailbuilder', '0' );
		wp_send_json_success();
		die;
	}

	public function credits_available_callback() {
		$this->core->check_auth();
		$json = $this->credits_available();
		wp_send_json( $json );
		die;
	}

	public function user_disconnect_callback() {
		$this->core->check_auth();
		Jackmail_Plugin::uninstall();
		Jackmail_Plugin::install();
		wp_send_json_success();
		die;
	}

	public function get_improvement_callback() {
		$this->core->check_auth();
		$json = $this->get_improvement();
		wp_send_json( $json );
		die;
	}

	public function set_improvement_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['active'] ) ) {
			$improvement = $this->core->request_text_data( $_POST['active'] );
			$this->set_improvement( $improvement );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function get_support_chat_callback() {
		$this->core->check_auth();
		$json = $this->get_support_chat();
		wp_send_json( $json );
		die;
	}

	public function set_support_chat_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['active'] ) ) {
			$support_chat = $this->core->request_text_data( $_POST['active'] );
			$this->set_support_chat( $support_chat );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function get_premium_notification_callback() {
		$this->core->check_auth();
		$json = $this->get_premium_notification();
		wp_send_json( $json );
		die;
	}

	public function set_premium_notification_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['active'] ) ) {
			$premium_notification = $this->core->request_text_data( $_POST['active'] );
			$this->set_premium_notification( $premium_notification );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function get_degug_callback() {
		$this->core->check_auth();
		$json = $this->get_degug();
		wp_send_json( $json );
		die;
	}

	public function get_degug_data_callback() {
		$this->core->check_auth();
		$json = $this->get_debug_data();
		wp_send_json( $json );
		die;
	}

	public function jackmail_manual_update_data_callback() {
		$this->core->check_auth();
		$this->core->update_jackmail_database();
		wp_send_json_success();
		die;
	}

	public function update_cookies_callback() {
		$this->core->check_auth();
		$selected_date1                 = null;
		$selected_date2                 = null;
		$campaign_emailing              = null;
		$campaign_scenario              = null;
		$statistics_campaigns_selection = null;
		if ( isset( $_POST['selected_date1'] ) ) {
			$selected_date1 = $this->core->request_text_data( $_POST['selected_date1'] );
		}
		if ( isset( $_POST['selected_date2'] ) ) {
			$selected_date2 = $this->core->request_text_data( $_POST['selected_date2'] );
		}
		if ( isset( $_POST['campaign_emailing'] ) ) {
			$campaign_emailing = $this->core->request_text_data( $_POST['campaign_emailing'] );
			if ( $campaign_emailing === 'false' || $campaign_emailing === '0' || $campaign_emailing === '' ) {
				$campaign_emailing = 'false';
			} else {
				$campaign_emailing = 'true';
			}
		}
		if ( isset( $_POST['campaign_scenario'] ) ) {
			$campaign_scenario = $this->core->request_text_data( $_POST['campaign_scenario'] );
			if ( $campaign_scenario === 'false' || $campaign_scenario === '0' || $campaign_scenario === '' ) {
				$campaign_scenario = 'false';
			} else {
				$campaign_scenario = 'true';
			}
		}
		if ( isset( $_POST['statistics_campaigns_selection'] ) ) {
			$statistics_campaigns_selection = $this->core->request_text_data( $_POST['statistics_campaigns_selection'] );
			$statistics_campaigns_selection = str_replace( "\"", "", $statistics_campaigns_selection );
			$statistics_campaigns_selection = str_replace( "[", "", $statistics_campaigns_selection );
			$statistics_campaigns_selection = str_replace( "]", "", $statistics_campaigns_selection );
			$statistics_campaigns_selection = str_replace( ",", "|", $statistics_campaigns_selection );
		}
		$this->update_cookies( $selected_date1, $selected_date2, $campaign_emailing, $campaign_scenario, $statistics_campaigns_selection );
		wp_send_json_success();
		die;
	}

	public function cron_domain_call() {
		$this->core->cron_domain();
	}

	public function cron_clean_files_call() {
		$this->cron_clean_files();
	}

}