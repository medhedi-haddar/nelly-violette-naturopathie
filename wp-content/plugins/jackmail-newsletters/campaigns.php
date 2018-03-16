<?php


class Jackmail_Campaigns extends Jackmail_Campaigns_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		add_action( 'jackmail_cron_progress_campaigns', array( $this, 'cron_progress_campaigns_call' ) );

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_campaigns_page', array( $this, 'campaigns_page_callback' ) );

			add_action( 'add_meta_boxes', array( $this, 'jackmail_post_box_call' ) );

			add_action( 'wp_insert_post', array( $this, 'post_jackmail_scenario_exclude_call' ), 20 );

			add_action( 'wp_ajax_jackmail_get_campaigns', array( $this, 'get_campaigns_callback' ) );

			add_action( 'wp_ajax_jackmail_delete_campaign', array( $this, 'delete_campaign_callback' ) );

			add_action( 'wp_ajax_jackmail_cancel_scheduled_campaign', array( $this, 'cancel_scheduled_campaign_callback' ) );

			add_action( 'wp_ajax_jackmail_duplicate_campaign', array( $this, 'duplicate_campaign_callback' ) );

			add_action( 'wp_ajax_jackmail_delete_scenario', array( $this, 'delete_scenario_callback' ) );

			add_action( 'wp_ajax_jackmail_edit_campaign_name', array( $this, 'edit_campaign_name_callback' ) );

			add_action( 'wp_ajax_jackmail_edit_scenario_name', array( $this, 'edit_scenario_name_callback' ) );

		}

	}

	public function campaigns_page_callback() {
		$this->core->check_auth();
		$this->core->include_html_file( 'campaigns' );
		die;
	}

	public function jackmail_post_box_call() {
		$this->jackmail_post_box();
	}

	public function post_jackmail_scenario_exclude_call( $post_id ) {
		if ( isset( $_POST['post_type'] ) ) {
			$post_type = $this->core->request_text_data( $_POST['post_type'] );
			$this->post_jackmail_scenario_exclude( $post_id, $post_type );
		}
	}

	public function get_campaigns_callback() {
		$this->core->check_auth();
		$campaigns = array();
		if ( isset( $_POST['refresh_status'] ) ) {
			$refresh_status = $this->core->request_text_data( $_POST['refresh_status'] );
			$campaigns      = $this->get_campaigns( $refresh_status );
		}
		wp_send_json( $campaigns );
		die;
	}

	public function delete_campaign_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_campaign   = $this->core->request_text_data( $_POST['id'] );
			$delete_return = $this->delete_campaign( $id_campaign );
			if ( $delete_return !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function cancel_scheduled_campaign_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$this->core->progress_campaigns();
			$id_campaign   = $this->core->request_text_data( $_POST['id'] );
			$cancel_result = $this->cancel_scheduled_campaign( $id_campaign );
			if ( $cancel_result !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function duplicate_campaign_callback() {
		$this->core->check_auth();
		$json = array(
			'success' => false
		);
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_campaign     = $this->core->request_text_data( $_POST['id'] );
			$new_id_campaign = $this->duplicate_campaign( $id_campaign );
			if ( $new_id_campaign !== false ) {
				$json = array(
					'success' => true,
					'id'      => $new_id_campaign
				);
			}
		}
		wp_send_json( $json );
		die;
	}

	public function delete_scenario_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_campaign   = $this->core->request_text_data( $_POST['id'] );
			$delete_result = $this->delete_scenario( $id_campaign );
			if ( $delete_result !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function edit_campaign_name_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'], $_POST['name'] ) ) {
			$id_campaign      = $this->core->request_text_data( $_POST['id'] );
			$name             = $this->core->request_text_data( $_POST['name'] );
			$current_date_gmt = $this->core->get_current_time_gmt_sql();
			$update_return    = $this->core->update_campaign( array(
				'name'             => $name,
				'updated_date_gmt' => $current_date_gmt,
				'updated_by'       => get_current_user_id()
			), array(
				'id'     => $id_campaign,
				'status' => 'DRAFT'
			) );
			if ( $update_return !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function edit_scenario_name_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'], $_POST['name'] ) ) {
			$id_campaign      = $this->core->request_text_data( $_POST['id'] );
			$name             = $this->core->request_text_data( $_POST['name'] );
			$current_date_gmt = $this->core->get_current_time_gmt_sql();
			$update_return    = $this->core->update_scenario( array(
				'name'             => $name,
				'updated_date_gmt' => $current_date_gmt,
				'updated_by'       => get_current_user_id()
			), array(
				'id' => $id_campaign
			) );
			if ( $update_return !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function cron_progress_campaigns_call() {
		$this->cron_progress_campaigns();
	}

}