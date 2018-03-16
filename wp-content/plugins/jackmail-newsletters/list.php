<?php


class Jackmail_List extends Jackmail_List_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_list_page', array( $this, 'list_page_callback' ) );

			add_action( 'wp_ajax_jackmail_get_list', array( $this, 'get_list_callback' ) );

			add_action( 'wp_ajax_jackmail_create_list', array( $this, 'create_list_callback' ) );

			add_action( 'wp_ajax_jackmail_export_list', array( $this, 'export_list_callback' ) );

			add_action( 'wp_ajax_jackmail_save_name', array( $this, 'save_name_callback' ) );

		}

	}

	public function list_page_callback() {
		$this->core->check_auth();
		$params = array(
			'page_type'     => 'list',
			'campaign_type' => ''
		);
		$this->core->include_html_file( 'list', $params );
		die;
	}

	public function get_list_callback() {
		$this->core->check_auth();
		$json = $this->get_or_export_list( (string) $this->core->grid_limit() );
		wp_send_json( $json );
		die;
	}

	public function create_list_callback() {
		$this->core->check_auth();
		$json = array(
			'success' => false,
			'id'      => '0'
		);
		if ( isset( $_POST['name'] ) ) {
			$name = $this->core->request_text_data( $_POST['name'] );
			$json = $this->create_list( $name );
		}
		wp_send_json( $json );
		die;
	}

	public function export_list_callback() {
		$this->core->check_auth();
		$json = $this->get_or_export_list( (string) $this->core->export_send_limit() );
		wp_send_json( $json );
		die;
	}

	public function save_name_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id'], $_POST['name'] ) ) {
			$id_list = $this->core->request_text_data( $_POST['id'] );
			$name    = $this->core->request_text_data( $_POST['name'] );
			$result  = $this->save_name( $id_list, $name );
			if ( $result ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}


}