<?php


class Jackmail_Installation extends Jackmail_Installation_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		add_action( 'jackmail_cron_notifications', array( $this->core, 'cron_notifications' ) );

		add_action( 'jackmail_cron_default_template', array( $this, 'cron_default_template_call' ) );

		if ( $this->core->is_admin() ) {

			if ( ! $this->core->is_configured() ) {

				add_action( 'wp_ajax_jackmail_installation_page', array( $this, 'installation_page_callback' ) );

				add_action( 'wp_ajax_jackmail_is_configured', array( $this, 'is_configured_callback' ) );

			}

			add_action( 'wp_ajax_jackmail_get_emailbuilder_licence', array( $this, 'get_emailbuilder_licence_callback' ) );

			add_action( 'wp_ajax_jackmail_install_emailbuilder', array( $this, 'install_emailbuilder_callback' ) );

			add_action( 'wp_ajax_jackmail_get_plugins_init', array( $this, 'get_plugins_init_callback' ) );

			add_action( 'wp_ajax_jackmail_get_plugins', array( $this, 'get_plugins_callback' ) );

			add_action( 'wp_ajax_jackmail_import_plugins', array( $this, 'import_plugins_callback' ) );

			add_action( 'wp_ajax_jackmail_get_new_plugins', array( $this, 'get_new_plugins_callback' ) );

			add_action( 'wp_ajax_jackmail_save_new_plugins', array( $this, 'save_new_plugins_callback' ) );

			add_action( 'wp_ajax_jackmail_uninstall_reason', array( $this, 'uninstall_reason_callback' ) );

			add_action( 'wp_ajax_jackmail_hide_notification', array( $this, 'hide_notification_callback' ) );

			add_action( 'wp_ajax_jackmail_hide_premium_notification', array( $this, 'hide_premium_notification_callback' ) );

			add_action( 'wp_ajax_jackmail_hide_update_popup', array( $this, 'hide_update_popup_callback' ) );

			add_action( 'in_admin_footer', array( $this, 'in_admin_footer_call' ) );

			add_action( 'admin_notices', array( $this, 'jackmail_notices_call' ) );

		}

	}

	public function installation_page_callback() {
		$this->core->check_auth();
		$params = array(
			'is_authenticated' => $this->core->is_authenticated()
		);
		$this->core->include_html_file( 'installation', $params );
		die;
	}

	public function is_configured_callback() {
		$this->core->check_auth();
		update_option( 'jackmail_is_configured', '1' );
		wp_send_json_success();
		die;
	}

	public function get_emailbuilder_licence_callback() {
		$this->core->check_auth();
		$json = $this->get_emailbuilder_licence();
		wp_send_json( $json );
		die;
	}

	public function install_emailbuilder_callback() {
		$this->core->check_auth();
		update_option( 'jackmail_emailbuilder', '1' );
		$this->cron_default_template();
		wp_send_json_success();
		die;
	}

	public function get_plugins_init_callback() {
		$this->core->check_auth();
		$result = $this->get_plugins( true );
		wp_send_json( $result );
		die;
	}

	public function get_plugins_callback() {
		$this->core->check_auth();
		$result = $this->get_plugins();
		wp_send_json( $result );
		die;
	}

	public function import_plugins_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['plugins'] ) ) {
			$plugins = $this->core->request_text_data( $_POST['plugins'] );
			$this->import_plugins( $plugins );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function get_new_plugins_callback() {
		$this->core->check_auth();
		$json = $this->get_new_plugins();
		wp_send_json( $json );
		die;
	}

	public function save_new_plugins_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['plugins'] ) ) {
			$new_plugins = $this->core->request_text_data( $_POST['plugins'] );
			$this->save_new_plugins( $new_plugins );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function uninstall_reason_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['reason'], $_POST['reason_detail'] ) ) {
			$reason        = $this->core->request_text_data( $_POST['reason'] );
			$reason_detail = $this->core->request_text_data( $_POST['reason_detail'] );
			$this->uninstall_reason( $reason, $reason_detail );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function hide_notification_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['notification_id'] ) ) {
			$notification_id = $this->core->request_text_data( $_POST['notification_id'] );
			$this->hide_notification( $notification_id );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function hide_premium_notification_callback() {
		$this->core->check_auth();
		$this->hide_premium_notification();
		wp_send_json_success();
		die;
	}

	public function hide_update_popup_callback() {
		$this->core->check_auth();
		$this->hide_update_popup();
		wp_send_json_success();
		die;
	}

	public function cron_default_template_call() {
		$this->cron_default_template();
	}

	public function in_admin_footer_call() {
		$this->in_admin_footer();
	}

	public function jackmail_notices_call() {
		$this->jackmail_notices();
	}

}