<?php


class Jackmail_Templates extends Jackmail_Templates_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_templates_page', array( $this, 'templates_page_callback' ) );

			add_action( 'wp_ajax_jackmail_get_templates', array( $this, 'get_templates_callback' ) );

			add_action( 'wp_ajax_jackmail_get_templates_gallery', array( $this, 'get_templates_gallery_callback' ) );

			add_action( 'wp_ajax_jackmail_delete_template', array( $this, 'delete_template_callback' ) );

			add_action( 'wp_ajax_jackmail_duplicate_template', array( $this, 'duplicate_template_callback' ) );

		}

	}

	public function templates_page_callback() {
		$this->core->check_auth();
		$params = array(
			'current_page' => 'templates'
		);
		$this->core->include_html_file( 'templates', $params );
		die;
	}

	public function get_templates_callback() {
		$this->core->check_auth();
		$templates = $this->get_templates();
		wp_send_json( $templates );
		die;
	}

	public function get_templates_gallery_callback() {
		$this->core->check_auth();
		$json = $this->get_templates_gallery();
		wp_send_json( $json );
		die;
	}

	public function delete_template_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_template   = $this->core->request_text_data( $_POST['id'] );
			$delete_return = $this->delete_template( $id_template );
			if ( $delete_return !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function duplicate_template_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'] ) ) {
			$id_template      = $this->core->request_text_data( $_POST['id'] );
			$duplicate_return = $this->duplicate_template( $id_template );
			if ( $duplicate_return !== false ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

}