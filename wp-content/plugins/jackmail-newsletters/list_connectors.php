<?php


class Jackmail_List_Connectors extends Jackmail_List_Connectors_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		add_action( 'pre_get_posts', array( $this, 'init_connectors_call' ) );

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_display_connectors', array( $this, 'display_connectors_callback' ) );

			add_action( 'wp_ajax_jackmail_connectors_configuration', array( $this, 'connectors_configuration_callback' ) );

			add_action( 'wp_ajax_jackmail_connectors_configure', array( $this, 'connectors_configure_callback' ) );

			add_action( 'wp_ajax_jackmail_connectors_configure_ip_restriction', array( $this, 'connectors_configure_ip_restriction_callback' ) );

			add_action( 'wp_ajax_jackmail_connectors_configure_allowed_ips', array( $this, 'connectors_configure_allowed_ips_callback' ) );

		}

	}

	public function init_connectors_call() {
		if ( isset( $_REQUEST['jackmail'], $_REQUEST['list'], $_REQUEST['action'] ) ) {
			$action        = $this->core->request_text_data( $_REQUEST['action'] );
			$connector_key = $this->core->request_text_data( $_REQUEST['list'] );
			$email         = false;
			$new_email     = false;
			if ( isset( $_REQUEST['email'] ) ) {
				$email     = $this->core->request_email_data( $_REQUEST['email'] );
				$new_email = $email;
			}
			if ( isset( $_REQUEST['new_email'] ) ) {
				$new_email = $this->core->request_email_data( $_REQUEST['new_email'] );
			}
			$data = $this->init_connectors( $action, $connector_key, $email, $new_email );
			wp_send_json( $data );
			die;
		}
	}

	public function display_connectors_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_list = $this->core->request_text_data( $_POST['id'] );
			$data    = $this->display_connectors( $id_list );
			wp_send_json( $data );
		}
		die;
	}

	public function connectors_configuration_callback() {
		$this->core->check_auth();
		$data = $this->connectors_configuration();
		wp_send_json( $data );
		die;
	}

	public function connectors_configure_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['active'] ) ) {
			$active = $this->core->request_text_data( $_POST['active'] );
			$this->connectors_configure( $active );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function connectors_configure_ip_restriction_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['ip_restriction'] ) ) {
			$ip_restriction = $this->core->request_text_data( $_POST['ip_restriction'] );
			$this->connectors_configure_ip_restriction( $ip_restriction );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function connectors_configure_allowed_ips_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['allowed_ips'] ) ) {
			$allowed_ips = $this->core->explode_data( $this->core->request_text_data( $_POST['allowed_ips'] ) );
			$data        = $this->connectors_configure_allowed_ips( $allowed_ips );
			wp_send_json( $data );
		}
		die;
	}

}