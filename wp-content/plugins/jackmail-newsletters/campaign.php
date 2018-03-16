<?php


class Jackmail_Campaign extends Jackmail_Campaign_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_campaign_page', array( $this, 'campaign_page_callback' ) );

			add_action( 'wp_ajax_jackmail_get_campaign', array( $this, 'get_campaign_callback' ) );

			add_action( 'wp_ajax_jackmail_get_fields_and_ids', array( $this, 'get_fields_and_ids_callback' ) );

			add_action( 'wp_ajax_jackmail_get_campaign_lists_available', array( $this, 'get_campaign_lists_available_callback' ) );

			add_action( 'wp_ajax_jackmail_get_campaign_contacts', array( $this, 'get_campaign_contacts_callback' ) );

			add_action( 'wp_ajax_jackmail_export_campaign_contacts', array( $this, 'export_campaign_contacts_callback' ) );

			add_action( 'wp_ajax_jackmail_set_campaign_lists', array( $this, 'set_campaign_lists_callback' ) );

			add_action( 'wp_ajax_jackmail_create_campaign', array( $this, 'create_campaign_callback' ) );

			add_action( 'wp_ajax_jackmail_update_campaign', array( $this, 'update_campaign_callback' ) );

			add_action( 'wp_ajax_jackmail_create_campaign_with_data', array( $this, 'create_campaign_with_data_callback' ) );

			add_action( 'wp_ajax_jackmail_campaign_last_step_checker', array( $this, 'campaign_last_step_checker_callback' ) );

			add_action( 'wp_ajax_jackmail_campaign_last_step_checker_analysis', array( $this, 'campaign_last_step_checker_analysis_callback' ) );

			add_action( 'wp_ajax_jackmail_send_campaign_test', array( $this, 'send_campaign_test_callback' ) );

			add_action( 'wp_ajax_jackmail_send_campaign', array( $this, 'send_campaign_callback' ) );

		}

	}

	public function campaign_page_callback() {
		$this->core->check_auth();
		$params = array(
			'page_type'      => 'campaign',
			'campaign_type'  => 'campaign',
			'scenario_type'  => '',
			'display_emojis' => $this->display_emojis()
		);
		$this->core->include_html_file( 'campaign', $params );
		die;
	}

	public function get_campaign_callback() {
		$this->core->check_auth();
		$campaign = null;
		if ( isset( $_POST['id'] ) ) {
			$id_campaign = $this->core->request_text_data( $_POST['id'] );
			$campaign    = $this->get_campaign( $id_campaign );
		}
		wp_send_json( $campaign );
		die;
	}

	public function get_fields_and_ids_callback() {
		$this->core->check_auth();
		$json = array();
		if ( isset( $_POST['fields'] ) ) {
			$fields = $this->core->request_text_data( $_POST['fields'] );
			$json   = $this->get_fields_and_ids( $fields );

		}
		wp_send_json( $json );
		die;
	}

	public function get_campaign_lists_available_callback() {
		$this->core->check_auth();
		$lists = array();
		if ( isset( $_POST['id'] ) ) {
			$id_campaign = $this->core->request_text_data( $_POST['id'] );
			$lists       = $this->get_campaign_lists_available( $id_campaign );
		}
		wp_send_json( $lists );
		die;
	}

	public function get_campaign_contacts_callback() {
		$this->core->check_auth();
		$json = $this->get_or_export_campaign_contacts( (string) $this->core->grid_limit() );
		wp_send_json( $json );
		die;
	}

	public function export_campaign_contacts_callback() {
		$this->core->check_auth();
		$json = $this->get_or_export_campaign_contacts( (string) $this->core->export_send_limit() );
		wp_send_json( $json );
		die;
	}

	public function set_campaign_lists_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id_campaign'], $_POST['id_lists'] ) ) {
			$id_campaign = $this->core->request_text_data( $_POST['id_campaign'] );
			$id_lists    = $this->core->request_text_data( $_POST['id_lists'] );
			$this->set_campaign_lists( $id_campaign, $id_lists );
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function create_campaign_callback() {
		$this->core->check_auth();
		$result = array(
			'success'             => false,
			'id'                  => '0',
			'content_size'        => false,
			'content_images_size' => false,
			'content_email_json'  => '',
			'content_email_html'  => '',
			'content_email_txt'   => ''
		);
		if ( isset( $_POST['name'], $_POST['object'], $_POST['sender_name'], $_POST['sender_email'],
			$_POST['reply_to_name'], $_POST['reply_to_email'], $_POST['content_email_json'],
			$_POST['content_email_html'], $_POST['content_email_txt'], $_POST['send_option'],
			$_POST['send_option_date_begin_gmt'], $_POST['send_option_date_end_gmt'] ) ) {
			$name           = $this->core->request_text_data( $_POST['name'] );
			$object         = $this->core->request_text_data( $_POST['object'] );
			$sender_name    = $this->core->request_text_data( $_POST['sender_name'] );
			$sender_email   = $this->core->request_text_data( $_POST['sender_email'] );
			$reply_to_name  = $this->core->request_text_data( $_POST['reply_to_name'] );
			$reply_to_email = $this->core->request_text_data( $_POST['reply_to_email'] );
			if ( isset( $_POST['link_tracking'] ) ) {
				$link_tracking = $this->core->request_text_data( $_POST['link_tracking'] );
			} else {
				$link_tracking = get_option( 'jackmail_link_tracking' );
			}
			$content_email_json         = $this->core->request_stripslashes( $_POST['content_email_json'] );
			$content_email_html         = $this->core->request_stripslashes( $_POST['content_email_html'] );
			$content_email_txt          = $this->core->request_textarea_data( $_POST['content_email_txt'] );
			$send_option                = $this->core->request_text_data( $_POST['send_option'] );
			$send_option_date_begin_gmt = $this->core->request_text_data( $_POST['send_option_date_begin_gmt'] );
			$send_option_date_end_gmt   = $this->core->request_text_data( $_POST['send_option_date_end_gmt'] );
			$result                     = $this->create_campaign(
				$name, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email,
				$link_tracking, $content_email_json, $content_email_html, $content_email_txt,
				$send_option, $send_option_date_begin_gmt, $send_option_date_end_gmt
			);
		}
		wp_send_json( $result );
		die;
	}

	public function update_campaign_callback() {
		$this->core->check_auth();
		$result = array(
			'success'             => false,
			'content_size'        => false,
			'content_images_size' => false,
			'content_email_json'  => '',
			'content_email_html'  => '',
			'content_email_txt'   => ''
		);
		if ( isset( $_POST['id'], $_POST['name'], $_POST['object'], $_POST['sender_name'],
			$_POST['sender_email'], $_POST['reply_to_name'], $_POST['reply_to_email'],
			$_POST['link_tracking'], $_POST['content_email_json'], $_POST['content_email_html'],
			$_POST['content_email_txt'], $_POST['send_option'],
			$_POST['send_option_date_begin_gmt'], $_POST['send_option_date_end_gmt'] ) ) {
			$id_campaign                = $this->core->request_text_data( $_POST['id'] );
			$name                       = $this->core->request_text_data( $_POST['name'] );
			$object                     = $this->core->request_text_data( $_POST['object'] );
			$sender_name                = $this->core->request_text_data( $_POST['sender_name'] );
			$sender_email               = $this->core->request_text_data( $_POST['sender_email'] );
			$reply_to_name              = $this->core->request_text_data( $_POST['reply_to_name'] );
			$reply_to_email             = $this->core->request_text_data( $_POST['reply_to_email'] );
			$link_tracking              = $this->core->request_text_data( $_POST['link_tracking'] );
			$content_email_json         = $this->core->request_stripslashes( $_POST['content_email_json'] );
			$content_email_html         = $this->core->request_stripslashes( $_POST['content_email_html'] );
			$content_email_txt          = $this->core->request_textarea_data( $_POST['content_email_txt'] );
			$send_option                = $this->core->request_text_data( $_POST['send_option'] );
			$send_option_date_begin_gmt = $this->core->request_text_data( $_POST['send_option_date_begin_gmt'] );
			$send_option_date_end_gmt   = $this->core->request_text_data( $_POST['send_option_date_end_gmt'] );
			$result                     = $this->update_campaign(
				$id_campaign, $name, $object, $sender_name, $sender_email, $reply_to_name,
				$reply_to_email, $link_tracking, $content_email_json, $content_email_html,
				$content_email_txt, $send_option, $send_option_date_begin_gmt, $send_option_date_end_gmt
			);
		}
		wp_send_json( $result );
		die;
	}

	public function create_campaign_with_data_callback() {
		$this->core->check_auth();
		$result = $this->create_campaign_with_data();
		wp_send_json( $result );
		die;
	}

	public function campaign_last_step_checker_callback() {
		$this->core->check_auth();
		$json = array(
			'nb_contacts_valids' => '0',
			'nb_credits_before'  => '0',
			'nb_credits_after'   => '0',
			'nb_credits_checked' => false,
			'subscription_type'  => '',
			'domain_is_valid'    => $this->core->domain_is_valid()
		);
		if ( isset( $_POST['id'] ) ) {
			$id_campaign = $this->core->request_text_data( $_POST['id'] );
			$json        = $this->campaign_last_step_checker( $id_campaign );
		}
		wp_send_json( $json );
		die;
	}

	public function campaign_last_step_checker_analysis_callback() {
		$this->core->check_auth();
		$json = array(
			'analysis_checked' => false,
			'analysis'         => array()
		);
		if ( isset( $_POST['id'] ) ) {
			$id_campaign = $this->core->request_text_data( $_POST['id'] );
			$json        = $this->campaign_last_step_checker_analysis( $id_campaign );
		}
		wp_send_json( $json );
		die;
	}

	public function send_campaign_test_callback() {
		$this->core->check_auth();
		$result = array(
			'success' => false,
			'message' => 'ERROR'
		);
		if ( isset( $_POST['id'], $_POST['test_recipient'] ) ) {
			$id_campaign    = $this->core->request_text_data( $_POST['id'] );
			$test_recipient = $this->core->request_text_data( $_POST['test_recipient'] );
			$result         = $this->send_campaign_test( $id_campaign, $test_recipient );
		}
		wp_send_json( $result );
		die;
	}

	public function send_campaign_callback() {
		$this->core->check_auth();
		ini_set( 'max_execution_time', 600 );
		$result = array(
			'success' => false,
			'message' => 'ERROR'
		);
		if ( isset( $_POST['id'], $_POST['send_option'], $_POST['send_option_date_begin_gmt'],
			$_POST['send_option_date_end_gmt'], $_POST['nb_contacts_valids_displayed'] ) ) {
			$id_campaign                  = $this->core->request_text_data( $_POST['id'] );
			$send_option                  = $this->core->request_text_data( $_POST['send_option'] );
			$send_option_date_begin_gmt   = $this->core->request_text_data( $_POST['send_option_date_begin_gmt'] );
			$send_option_date_end_gmt     = $this->core->request_text_data( $_POST['send_option_date_end_gmt'] );
			$nb_contacts_valids_displayed = $this->core->request_text_data( $_POST['nb_contacts_valids_displayed'] );
			$result                       = $this->send_campaign(
				$id_campaign, $send_option, $send_option_date_begin_gmt, $send_option_date_end_gmt, $nb_contacts_valids_displayed
			);
		}
		wp_send_json( $result );
		die;
	}

}