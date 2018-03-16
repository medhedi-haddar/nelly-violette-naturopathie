<?php


class Jackmail_Emailcontent extends Jackmail_Emailcontent_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_get_images', array( $this, 'get_images_callback' ) );

			add_action( 'wp_ajax_jackmail_get_post_types', array( $this, 'get_post_types_callback' ) );

			add_action( 'wp_ajax_jackmail_get_post_categories', array( $this, 'get_post_categories_callback' ) );

			add_action( 'wp_ajax_jackmail_get_posts', array( $this, 'get_posts_callback' ) );

			add_action( 'wp_ajax_jackmail_get_post_or_page_or_custom_post_full_content', array( $this, 'get_post_or_page_or_custom_post_full_content_callback' ) );

			add_action( 'wp_ajax_jackmail_get_pages', array( $this, 'get_pages_callback' ) );

			add_action( 'wp_ajax_jackmail_get_woocommerce_product_categories', array( $this, 'get_woocommerce_product_categories_callback' ) );

			add_action( 'wp_ajax_jackmail_get_woocommerce_products', array( $this, 'get_products_woocommerce_callback' ) );

			add_action( 'wp_ajax_jackmail_get_woocommerce_product_full_content', array( $this, 'get_woocommerce_product_full_content_callback' ) );

			add_action( 'wp_ajax_jackmail_get_custom_posts_categories', array( $this, 'get_custom_posts_categories_callback' ) );

			add_action( 'wp_ajax_jackmail_get_custom_posts', array( $this, 'get_custom_posts_callback' ) );

		}

	}

	public function get_images_callback() {
		$this->core->check_auth();
		$json = $this->get_images();
		wp_send_json( $json );
		die;
	}

	public function get_post_types_callback() {
		$this->core->check_auth();
		$json = $this->core->get_custom_posts_categories_json();
		wp_send_json( $json );
		die;
	}

	public function get_post_categories_callback() {
		$this->core->check_auth();
		$json = $this->get_post_categories();
		wp_send_json( $json );
		die;
	}

	public function get_posts_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['title'], $_POST['categories'] ) ) {
			$title            = $this->core->request_text_data( $_POST['title'] );
			$categories       = $this->core->request_text_data( $_POST['categories'] );
			$categories_array = $this->core->explode_data( $categories );
			$json             = $this->get_posts( $title, $categories_array );
			wp_send_json( $json );
		}
		die;
	}

	public function get_post_or_page_or_custom_post_full_content_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['post_id'] ) ) {
			$post_id = $this->core->request_text_data( $_POST['post_id'] );
			$json    = $this->get_post_or_page_or_custom_post_full_content( $post_id );
			wp_send_json( $json );
		}
		die;
	}

	public function get_pages_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['title'] ) ) {
			$title = $this->core->request_text_data( $_POST['title'] );
			$json  = $this->get_pages( $title );
			wp_send_json( $json );
		}
		die;
	}

	public function get_woocommerce_product_categories_callback() {
		$this->core->check_auth();
		$json = $this->get_woocommerce_product_categories();
		wp_send_json( $json );
		die;
	}

	public function get_products_woocommerce_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['title'], $_POST['categories'] ) ) {
			$title      = $this->core->request_text_data( $_POST['title'] );
			$categories = $this->core->request_text_data( $_POST['categories'] );
			$json       = $this->get_products_woocommerce( $title, $categories );
			wp_send_json( $json );
		}
		die;
	}

	public function get_woocommerce_product_full_content_callback() {
		$this->core->check_auth();
		$json = array();
		if ( isset( $_POST['post_id'] ) ) {
			if ( $this->core->get_woocommerce_actived() ) {
				$post_id = $this->core->request_text_data( $_POST['post_id'] );
				$json    = $this->get_woocommerce_product_full_content( $post_id );
			}
			wp_send_json( $json );
		}
		die;
	}

	public function get_custom_posts_categories_callback() {
		$this->core->check_auth();
		$json = $this->get_custom_posts_categories();
		wp_send_json( $json );
		die;
	}

	public function get_custom_posts_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['title'], $_POST['post_type'] ) ) {
			$title     = $this->core->request_text_data( $_POST['title'] );
			$post_type = $this->core->request_text_data( $_POST['post_type'] );
			$json      = $this->get_custom_posts( $title, $post_type );
			wp_send_json( $json );
		}
		die;
	}

}