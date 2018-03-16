<?php


class Jackmail_Template extends Jackmail_Template_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_template_page', array( $this, 'template_page_callback' ) );

			add_action( 'wp_ajax_jackmail_get_template', array( $this, 'get_template_callback' ) );

			add_action( 'wp_ajax_jackmail_get_template_json', array( $this, 'get_template_json_callback' ) );

			add_action( 'wp_ajax_jackmail_get_gallery_template_json', array( $this, 'get_gallery_template_json_callback' ) );

			add_action( 'wp_ajax_jackmail_create_template', array( $this, 'create_template_callback' ) );

			add_action( 'wp_ajax_jackmail_update_template', array( $this, 'update_template_callback' ) );

			add_action( 'wp_ajax_jackmail_create_campaign_with_template', array( $this, 'create_campaign_with_template_callback' ) );

		}

	}

	public function template_page_callback() {
		$this->core->check_auth();
		$this->core->include_html_file( 'template' );
		die;
	}

	public function get_template_callback() {
		$this->core->check_auth();
		$template = null;
		if ( isset( $_POST['id'] ) ) {
			$id_template = $this->core->request_text_data( $_POST['id'] );
			$template    = $this->get_template( $id_template );
		}
		wp_send_json( $template );
		die;
	}

	public function get_template_json_callback() {
		$this->core->check_auth();
		$json = array(
			'content_email_json' => '',
		);
		if ( isset( $_POST['id'], $_POST['link_tracking'] ) ) {
			$id_template   = $this->core->request_text_data( $_POST['id'] );
			$link_tracking = $this->core->request_text_data( $_POST['link_tracking'] );
			$json          = $this->get_template_json( $id_template, $link_tracking );

		}
		wp_send_json( $json );
		die;
	}

	public function get_gallery_template_json_callback() {
		$this->core->check_auth();
		ini_set( 'max_execution_time', 200 );
		$json = array(
			'content_email_json' => ''
		);
		if ( isset( $_POST['gallery_id'], $_POST['link_tracking'] ) ) {
			$gallery_id    = $this->core->request_text_data( $_POST['gallery_id'] );
			$link_tracking = $this->core->request_text_data( $_POST['link_tracking'] );
			$json          = $this->get_gallery_template_json( $gallery_id, $link_tracking );
		}
		wp_send_json( $json );
		die;
	}

	public function create_template_callback() {
		$this->core->check_auth();
		$result = array(
			'success'            => false,
			'id'                 => '0',
			'content_email_json' => ''
		);
		if ( isset( $_POST['name'], $_POST['content_email_json'], $_POST['content_email_html'], $_POST['content_email_txt'] ) ) {
			$name               = $this->core->request_text_data( $_POST['name'] );
			$content_email_json = $this->core->request_stripslashes( $_POST['content_email_json'] );
			$result             = $this->create_template( $name, $content_email_json );
		}
		wp_send_json( $result );
		die;
	}

	public function update_template_callback() {
		$this->core->check_auth();
		$result = array(
			'success'            => false,
			'content_email_json' => ''
		);
		if ( isset( $_POST['id'], $_POST['name'], $_POST['content_email_json'], $_POST['content_email_html'], $_POST['content_email_txt'] ) ) {
			$id_template        = $this->core->request_text_data( $_POST['id'] );
			$name               = $this->core->request_text_data( $_POST['name'] );
			$content_email_json = $this->core->request_stripslashes( $_POST['content_email_json'] );
			$result             = $this->update_template( $id_template, $name, $content_email_json );
		}
		wp_send_json( $result );
		die;
	}

	public function create_campaign_with_template_callback() {
		$this->core->check_auth();
		$json = array(
			'success' => false
		);
		if ( isset( $_POST['id'] ) ) {
			$id_template     = $this->core->request_text_data( $_POST['id'] );
			$new_id_campaign = $this->create_campaign_with_template( $id_template );
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

}