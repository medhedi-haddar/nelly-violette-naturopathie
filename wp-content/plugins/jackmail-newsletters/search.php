<?php


class Jackmail_Search extends Jackmail_Search_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_search_faq', array( $this, 'search_faq_callback' ) );

			add_action( 'wp_ajax_jackmail_search_campaigns', array( $this, 'search_campaigns_callback' ) );

			add_action( 'wp_ajax_jackmail_search_all', array( $this, 'search_all_callback' ) );

			add_action( 'wp_ajax_jackmail_suggestion_faq', array( $this, 'suggestion_faq_callback' ) );

			add_action( 'wp_ajax_jackmail_suggestion_forum', array( $this, 'suggestion_forum_callback' ) );

		}

	}

	public function search_faq_callback() {
		$this->core->check_auth();
		$json = array();
		if ( isset( $_POST['search'] ) ) {
			$search = $this->core->request_text_data( $_POST['search'] );
			$json   = $this->search_faq( $search );
		}
		wp_send_json( $json );
		die;
	}

	public function search_campaigns_callback() {
		$this->core->check_auth();
		$json = array();
		if ( isset( $_POST['search'] ) ) {
			$search = $this->core->request_text_data( $_POST['search'] );
			$json   = $this->search_campaigns( $search );
		}
		wp_send_json( $json );
		die;
	}

	public function search_all_callback() {
		$this->core->check_auth();
		$json = array();
		if ( isset( $_POST['search'] ) ) {
			$search = $this->core->request_text_data( $_POST['search'] );
			$json   = $this->search_all( $search );
		}
		wp_send_json( $json );
		die;
	}

	public function suggestion_faq_callback() {
		$this->core->check_auth();
		$json = $this->suggestion_faq();
		wp_send_json( $json );
		die;
	}

	public function suggestion_forum_callback() {
		$this->core->check_auth();
		$json = array();
		if ( isset( $_POST['search'] ) ) {
			$search = $this->core->request_text_data( $_POST['search'] );
			$json   = $this->suggestion_forum( $search );
		}
		wp_send_json( $json );
		die;
	}

}