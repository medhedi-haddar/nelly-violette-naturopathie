<?php


class Jackmail_List_Detail extends Jackmail_List_Detail_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_list_detail_page', array( $this, 'list_email_detail_page_callback' ) );

			add_action( 'wp_ajax_jackmail_get_email_lists_detail', array( $this, 'get_email_lists_detail_callback' ) );

			add_action( 'wp_ajax_jackmail_get_email_detail', array( $this, 'get_email_detail_callback' ) );

			add_action( 'wp_ajax_jackmail_unsubscribe_contact', array( $this, 'unsubscribe_contact_callback' ) );

			add_action( 'wp_ajax_jackmail_unblacklist_contact', array( $this, 'unblacklist_contact_callback' ) );
		}

	}

	public function list_email_detail_page_callback() {
		$this->core->check_auth();
		$this->core->include_html_file( 'list_detail' );
		die;
	}

	public function get_email_lists_detail_callback() {
		$this->core->check_auth();
		$json = array();
		if ( isset( $_POST['email'] ) ) {
			$email = $this->core->request_email_data( $_POST['email'] );
			if ( isset( $_POST['id_campaign'] ) ) {
				$id_campaign = $this->core->request_text_data( $_POST['id_campaign'] );
				$json        = $this->campaign_get_email_lists_detail( $id_campaign, $email );
			} else {
				$json = $this->list_get_email_lists_detail( $email );
			}
		}
		wp_send_json( $json );
		die;
	}

	public function get_email_detail_callback() {
		$this->core->check_auth();
		$json = array(
			'sends'    => 0,
			'clicks'   => 0,
			'opens'    => 0,
			'timeline' => array()
		);
		if ( isset( $_POST['id'], $_POST['email'] ) ) {
			$id    = $this->core->request_text_data( $_POST['id'] );
			$email = $this->core->request_email_data( $_POST['email'] );
			$json  = $this->get_email_detail( $id, $email );
		}
		wp_send_json( $json );
		die;
	}

	public function unsubscribe_contact_callback() {
		$this->core->check_auth();
		if ( $this->unsubscribe_or_unblacklist_contact( 'unsubscribe' ) ) {
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function unblacklist_contact_callback() {
		$this->core->check_auth();
		if ( $this->unsubscribe_or_unblacklist_contact( 'unblacklist' ) ) {
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

}