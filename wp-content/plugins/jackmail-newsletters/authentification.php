<?php

class Jackmail_Authentification extends Jackmail_Authentification_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_account_creation', array( $this, 'account_creation_callback' ) );

			add_action( 'wp_ajax_jackmail_account_connection', array( $this, 'account_connection_callback' ) );

			add_action( 'wp_ajax_jackmail_account_info', array( $this, 'account_info_callback' ) );

			add_action( 'wp_ajax_jackmail_account_reset', array( $this, 'account_reset_callback' ) );

			add_action( 'wp_ajax_jackmail_account_resend_activation_email', array( $this, 'account_resend_activation_email_callback' ) );

		}

	}

	public function account_creation_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['email'], $_POST['password'], $_POST['firstname'], $_POST['lastname'] ) ) {
			$email     = $this->core->request_email_data( $_POST['email'] );
			$password  = $this->core->request_text_data( $_POST['password'] );
			$firstname = $this->core->request_text_data( $_POST['firstname'] );
			$lastname  = $this->core->request_text_data( $_POST['lastname'] );
			$result    = $this->account_creation( $email, $password, $firstname, $lastname );
			if ( $result ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function account_connection_callback() {
		$this->core->check_auth();
		$json = array(
			'success' => false,
			'message' => ''
		);
		if ( isset( $_POST['email'], $_POST['password'] ) ) {
			$email    = $this->core->request_email_data( $_POST['email'] );
			$password = $this->core->request_text_data( $_POST['password'] );
			$json     = $this->account_connection( $email, $password );
		}
		wp_send_json( $json );
		die;
	}

	public function account_info_callback() {
		$this->core->check_auth();
		$json = $this->account_info();
		wp_send_json( $json );
		die;
	}

	public function account_reset_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['email'] ) ) {
			$email  = $this->core->request_email_data( $_POST['email'] );
			$result = $this->account_reset( $email );
			if ( $result ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function account_resend_activation_email_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['email'], $_POST['password'] ) ) {
			$email    = $this->core->request_email_data( $_POST['email'] );
			$password = $this->core->request_text_data( $_POST['password'] );
			$result   = $this->account_resend_activation_email( $email, $password );
			if ( $result ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

}