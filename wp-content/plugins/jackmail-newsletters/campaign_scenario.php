<?php


class Jackmail_Campaign_Scenario extends Jackmail_Campaign_Scenario_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		add_action( 'jackmail_cron_scenarios', array( $this, 'cron_scenario_call' ) );

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_scenario_choice_page', array( $this, 'scenario_choice_page_callback' ) );

			add_action( 'wp_ajax_jackmail_scenario_page', array( $this, 'scenario_page_callback' ) );

			add_action( 'wp_ajax_jackmail_get_scenario', array( $this, 'get_scenario_callback' ) );

			add_action( 'wp_ajax_jackmail_get_scenario_lists_available', array( $this, 'get_scenario_lists_available_callback' ) );
			
			add_action( 'wp_ajax_jackmail_create_scenario', array( $this, 'create_scenario_callback' ) );

			add_action( 'wp_ajax_jackmail_update_scenario', array( $this, 'update_scenario_callback' ) );

			add_action( 'wp_ajax_jackmail_activate_scenario_link_tracking', array( $this, 'activate_scenario_link_tracking_callback' ) );

			add_action( 'wp_ajax_jackmail_deactivate_scenario_link_tracking', array( $this, 'deactivate_scenario_link_tracking_callback' ) );

			add_action( 'wp_ajax_jackmail_scenario_last_step_checker', array( $this, 'scenario_last_step_checker_callback' ) );

			add_action( 'wp_ajax_jackmail_send_scenario_test', array( $this, 'send_scenario_test_callback' ) );

			add_action( 'wp_ajax_jackmail_activate_scenario', array( $this, 'activate_scenario_callback' ) );

			add_action( 'wp_ajax_jackmail_deactivate_scenario', array( $this, 'deactivate_scenario_callback' ) );

		}

	}

	public function scenario_choice_page_callback() {
		$this->core->check_auth();
		$this->core->include_html_file( 'campaign_type_scenario_choice' );
		die;
	}

	public function scenario_page_callback() {
		$this->core->check_auth();
		$params = array(
			'page_type'      => 'campaign',
			'campaign_type'  => 'scenario',
			'scenario_type'  => isset( $_POST['choice'] ) ? $this->core->request_text_data( $_POST['choice'] ) : '',
			'display_emojis' => $this->display_emojis()
		);
		$this->core->include_html_file( 'campaign', $params );
		die;
	}

	public function get_scenario_callback() {
		$this->core->check_auth();
		$campaign = null;
		if ( isset( $_POST['id'], $_POST['choice'] ) ) {
			$id_campaign = $this->core->request_text_data( $_POST['id'] );
			$choice      = $this->core->request_text_data( $_POST['choice'] );
			$campaign    = $this->get_scenario( $id_campaign, $choice );
		}
		wp_send_json( $campaign );
		die;
	}

	public function get_scenario_lists_available_callback() {
		$this->core->check_auth();
		$lists = array();
		if ( isset( $_POST['id'] ) ) {
			$id_campaign = $this->core->request_text_data( $_POST['id'] );
			$lists       = $this->get_scenario_lists_available( $id_campaign );
		}
		wp_send_json( $lists );
		die;
	}

	public function create_scenario_callback() {
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
		if ( $this->create_update_scenario_check_isset_posts() ) {
			$id_lists           = $this->core->request_text_data( $_POST['id_lists'] );
			$send_option        = $this->core->request_text_data( $_POST['send_option'] );
			$name               = $this->core->request_text_data( $_POST['name'] );
			$object             = $this->core->request_text_data( $_POST['object'] );
			$sender_name        = $this->core->request_text_data( $_POST['sender_name'] );
			$sender_email       = $this->core->request_text_data( $_POST['sender_email'] );
			$reply_to_name      = $this->core->request_text_data( $_POST['reply_to_name'] );
			$reply_to_email     = $this->core->request_text_data( $_POST['reply_to_email'] );
			$link_tracking      = $this->core->request_text_data( $_POST['link_tracking'] );
			$content_email_json = $this->core->request_stripslashes( $_POST['content_email_json'] );
			$post_type          = $this->core->request_text_data( $_POST['post_type'] );
			$post_categories    = $this->core->request_text_data( $_POST['post_categories'] );
			$nb_posts_content   = $this->core->request_text_data( $_POST['nb_posts_content'] );
			$periodicity_type   = $this->core->request_text_data( $_POST['periodicity_type'] );
			$periodicity_value  = $this->core->request_text_data( $_POST['periodicity_value'] );
			$event_date_gmt     = $this->core->request_text_data( $_POST['event_date_gmt'] );
			$result             = $this->create_scenario(
				$id_lists, $send_option, $name, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $link_tracking,
				$content_email_json, $post_type, $post_categories, $nb_posts_content, $periodicity_type, $periodicity_value, $event_date_gmt
			);
		}
		wp_send_json( $result );
		die;
	}

	public function update_scenario_callback() {
		$result = array(
			'success'             => false,
			'content_size'        => false,
			'content_images_size' => false,
			'content_email_json'  => '',
			'content_email_html'  => '',
			'content_email_txt'   => ''
		);
		if ( isset( $_POST['id'] ) && $this->create_update_scenario_check_isset_posts() ) {
			$id_campaign        = $this->core->request_text_data( $_POST['id'] );
			$id_lists           = $this->core->request_text_data( $_POST['id_lists'] );
			$send_option        = $this->core->request_text_data( $_POST['send_option'] );
			$name               = $this->core->request_text_data( $_POST['name'] );
			$object             = $this->core->request_text_data( $_POST['object'] );
			$sender_name        = $this->core->request_text_data( $_POST['sender_name'] );
			$sender_email       = $this->core->request_text_data( $_POST['sender_email'] );
			$reply_to_name      = $this->core->request_text_data( $_POST['reply_to_name'] );
			$reply_to_email     = $this->core->request_text_data( $_POST['reply_to_email'] );
			$link_tracking      = $this->core->request_text_data( $_POST['link_tracking'] );
			$content_email_json = $this->core->request_stripslashes( $_POST['content_email_json'] );
			$post_type          = $this->core->request_text_data( $_POST['post_type'] );
			$post_categories    = $this->core->request_text_data( $_POST['post_categories'] );
			$nb_posts_content   = $this->core->request_text_data( $_POST['nb_posts_content'] );
			$periodicity_type   = $this->core->request_text_data( $_POST['periodicity_type'] );
			$periodicity_value  = $this->core->request_text_data( $_POST['periodicity_value'] );
			$event_date_gmt     = $this->core->request_text_data( $_POST['event_date_gmt'] );
			$result             = $this->update_scenario(
				$id_campaign, $id_lists, $send_option, $name, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $link_tracking,
				$content_email_json, $post_type, $post_categories, $nb_posts_content, $periodicity_type, $periodicity_value, $event_date_gmt
			);
		}
		wp_send_json( $result );
		die;
	}

	public function activate_scenario_link_tracking_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_campaign   = $this->core->request_text_data( $_POST['id'] );
			$update_return = $this->activate_scenario_link_tracking( $id_campaign );
			if ( $update_return !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function deactivate_scenario_link_tracking_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_campaign   = $this->core->request_text_data( $_POST['id'] );
			$update_return = $this->deactivate_scenario_link_tracking( $id_campaign );
			if ( $update_return !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function scenario_last_step_checker_callback() {
		$this->core->check_auth();
		$json = $this->scenario_last_step_checker();
		wp_send_json( $json );
		die;
	}

	public function send_scenario_test_callback() {
		$this->core->check_auth();
		$result = array(
			'success' => false,
			'message' => 'ERROR'
		);
		if ( isset( $_POST['id'], $_POST['test_recipient'] ) ) {
			$id_campaign    = $this->core->request_text_data( $_POST['id'] );
			$test_recipient = $this->core->request_text_data( $_POST['test_recipient'] );
			$result         = $this->send_scenario_test( $id_campaign, $test_recipient );
		}
		wp_send_json( $result );
		die;
	}

	public function activate_scenario_callback() {
		$this->core->check_auth();
		$json = array(
			'success' => false,
			'message' => ''
		);
		if ( isset( $_POST['id'] ) ) {
			$id_campaign = $this->core->request_text_data( $_POST['id'] );
			$json        = $this->activate_scenario( $id_campaign );
		}
		wp_send_json( $json );
		die;
	}

	public function deactivate_scenario_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_campaign   = $this->core->request_text_data( $_POST['id'] );
			$update_return = $this->deactivate_scenario( $id_campaign );
			if ( $update_return !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	

	public function cron_scenario_call() {
		$this->cron_scenario();
	}

}