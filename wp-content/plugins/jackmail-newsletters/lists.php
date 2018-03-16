<?php


class Jackmail_Lists extends Jackmail_Lists_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		add_action( 'jackmail_cron_progress_contacts_blacklist', array( $this, 'cron_progress_contacts_blacklist_call' ) );

		add_action( 'jackmail_cron_actualize_plugins_lists', array( $this, 'cron_actualize_plugins_lists_call' ) );

		$this->plugins_events_call();

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_lists_page', array( $this, 'lists_page_callback' ) );

			add_action( 'wp_ajax_jackmail_get_lists', array( $this, 'get_lists_callback' ) );

			add_action( 'wp_ajax_jackmail_delete_lists', array( $this, 'delete_lists_callback' ) );
		}
	}

	public function lists_page_callback() {
		$this->core->check_auth();
		$this->core->include_html_file( 'lists' );
		die;
	}

	public function get_lists_callback() {
		$this->core->check_auth();
		$lists = $this->get_lists();
		wp_send_json( $lists );
		die;
	}

	public function delete_lists_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id_lists'] ) ) {
			$id_lists = $this->core->request_text_data( $_POST['id_lists'] );
			$id_lists = $this->core->explode_data( $id_lists );
			if ( is_array( $id_lists ) ) {
				$this->delete_lists( $id_lists );
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	private function plugins_events_call() {
		$this->plugins_events();
	}

	public function cron_progress_contacts_blacklist_call() {
		$this->cron_progress_contacts_blacklist();
	}

	public function cron_actualize_plugins_lists_call() {
		$this->cron_actualize_plugins_lists();
	}

}