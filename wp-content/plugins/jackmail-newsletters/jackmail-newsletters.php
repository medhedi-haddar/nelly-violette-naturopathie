<?php
/*
Plugin Name: Jackmail Newsletters
Plugin URI: https://www.jackmail.com
Description: Create and send responsive newsletter with a professional routing platform and a lot of features: automated emails and newsletters, statistics, email & Live Support etc.
Version: 1.0.52
Author: Jackmail
Author URI: https://www.jackmail.com
Text Domain: jackmail-newsletters
Domain Path: /languages/
License: GPLv2
*/



class Jackmail_Plugin {

	public function __construct() {

		include_once plugin_dir_path( __FILE__ ) . 'jackmail_core.php';
		$this->core = new Jackmail_Core();

		if ( $this->core->is_admin() ) {

			register_activation_hook( __FILE__, array( 'Jackmail_Plugin', 'install' ) );

			register_deactivation_hook( __FILE__, array( 'Jackmail_Plugin', 'deactivation' ) );

			register_uninstall_hook( __FILE__, array( 'Jackmail_Plugin', 'uninstall' ) );

			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

			add_action( 'plugins_loaded', array( $this, 'load_languages' ) );

			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'get_scripts' ), 11 );

			add_action( 'admin_enqueue_scripts', array( $this, 'get_styles' ) );

		}

		if ( isset( $_REQUEST['action'] ) ) {
			$action = $this->core->request_text_data( $_REQUEST['action'] );
			if ( strpos( $action, 'jackmail_' ) !== false ) {
				add_action( 'wp_ajax_' . $action, array( $this->core, 'check_auth' ), 1 );
			}
		}

		if ( $this->core->is_configured() ) {

			$this->core->include_core_class_file( 'emailcontent_common_core' );

			$this->core->include_core_class_file( 'list_and_campaign_common_core' );

			$this->core->include_core_class_file( 'list_and_campaign_core' );
			$this->core->include_class_file( 'list_and_campaign' );
			new Jackmail_List_And_Campaign( $this->core );

			$this->core->include_core_class_file( 'campaigns_core' );
			$this->core->include_class_file( 'campaigns' );
			new Jackmail_Campaigns( $this->core );

			$this->core->include_core_class_file( 'campaign_core' );
			$this->core->include_class_file( 'campaign' );
			new Jackmail_Campaign( $this->core );

			$this->core->include_core_class_file( 'campaign_scenario_core' );
			$this->core->include_class_file( 'campaign_scenario' );
			new Jackmail_Campaign_Scenario( $this->core );

			$this->core->include_core_class_file( 'lists_core' );
			$this->core->include_class_file( 'lists' );
			new Jackmail_Lists( $this->core );

			$this->core->include_core_class_file( 'list_core' );
			$this->core->include_class_file( 'list' );
			new Jackmail_List( $this->core );

			$this->core->include_core_class_file( 'list_detail_core' );
			$this->core->include_class_file( 'list_detail' );
			new Jackmail_List_Detail( $this->core );

			$this->core->include_core_class_file( 'templates_core' );
			$this->core->include_class_file( 'templates' );
			new Jackmail_Templates( $this->core );

			$this->core->include_core_class_file( 'template_core' );
			$this->core->include_class_file( 'template' );
			new Jackmail_Template( $this->core );

			$this->core->include_core_class_file( 'emailcontent_core' );
			$this->core->include_class_file( 'emailcontent' );
			new Jackmail_Emailcontent( $this->core );

			$this->core->include_core_class_file( 'statistics_core' );
			$this->core->include_class_file( 'statistics' );
			new Jackmail_Statistics( $this->core );

			$this->core->include_core_class_file( 'settings_core' );
			$this->core->include_class_file( 'settings' );
			new Jackmail_Settings( $this->core );

			$this->core->include_core_class_file( 'search_core' );
			$this->core->include_class_file( 'search' );
			new Jackmail_Search( $this->core );

			$this->core->include_class_file( 'widget' );
			new Jackmail_Widget();
			
			$this->core->include_core_class_file( 'list_connectors_core' );
			$this->core->include_class_file( 'list_connectors' );
			new Jackmail_List_Connectors( $this->core );

			

			add_action( 'pre_get_posts', array( $this->core, 'jackmail_images' ) );

			add_action( 'wp_ajax_jackmail_save_image', array( $this->core, 'save_image_callback' ) );

		}

		$this->core->include_core_class_file( 'installation_core' );
		$this->core->include_class_file( 'installation' );
		new Jackmail_Installation( $this->core );

		$this->core->include_core_class_file( 'authentification_core' );
		$this->core->include_class_file( 'authentification' );
		new Jackmail_Authentification( $this->core );

		add_filter( 'auto_update_plugin', array( $this, 'update_jackmail' ), 1, 2 );
		add_filter( 'auto_update_plugin', array( $this, 'update_jackmail' ), 150, 2 );
		
	}

	
	

	public static function install() {
		global $wpdb;

		if ( version_compare( get_bloginfo( 'version' ), '4', '<' ) ) {
			wp_die( __( '<p>The <strong>Jackmail</strong> plugin requires WordPress 4.0 version or greater.</p>', 'jackmail-newsletters' ), __( 'Error with the activation process of the Jackmail plugin', 'jackmail-newsletters' ), array(
				'response'  => 200,
				'back_link' => true
			) );
		}
		if ( version_compare( phpversion(), '5.4.0', '<' ) ) {
			wp_die( __( '<p>The <strong>Jackmail</strong> plugin requires php 5.4.0 version or greater.</p>', 'jackmail-newsletters' ), __( 'Error with the activation process of the Jackmail plugin', 'jackmail-newsletters' ), array(
				'response'  => 200,
				'back_link' => true
			) );
		}

		$core = new Jackmail_Core();

		if ( ! $core->mb_string_function_exists() ) {
			wp_die( __( '<p>The <strong>Jackmail</strong> plugin requires php "mb_strtoupper", "mb_strtolower" and "mb_strlen" features.</p>', 'jackmail-newsletters' ), __( 'Error with the activation process of the Jackmail plugin', 'jackmail-newsletters' ), array(
				'response'  => 200,
				'back_link' => true
			) );
		}

		if ( ! $core->json_encode_json_decode_function_exists() ) {
			wp_die( __( '<p>The <strong>Jackmail</strong> plugin requires php "json_encode" and "json_decode" features.</p>', 'jackmail-newsletters' ), __( 'Error with the activation process of the Jackmail plugin', 'jackmail-newsletters' ), array(
				'response'  => 200,
				'back_link' => true
			) );
		}
		

		$current_time_gmt = $core->get_current_time_gmt_sql();
		$choices          = 'abcdefghijklmopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$id_max_choices   = $core->str_len( $choices ) - 1;
		$jackmail_id      = '';
		for ( $i = 0; $i < 6; $i ++ ) {
			$jackmail_id .= substr( $choices, mt_rand( 0, $id_max_choices ), 1 );
		}
		$choices        = 'abcdefghijklmopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$id_max_choices = $core->str_len( $choices ) - 1;
		add_option( 'jackmail_id', $jackmail_id );
		add_option( 'jackmail_install_date', $current_time_gmt, '', 'no' );
		add_option( 'jackmail_is_configured', '0' );
		add_option( 'jackmail_account_token', '', '', 'no' );
		add_option( 'jackmail_account_id', '', '', 'no' );
		add_option( 'jackmail_user_id', '', '', 'no' );
		add_option( 'jackmail_lastname', '', '', 'no' );
		add_option( 'jackmail_firstname', '', '', 'no' );
		add_option( 'jackmail_authentification_failed', '0', '', 'no' );
		add_option( 'jackmail_improvement', '1', '', 'no' );
		add_option( 'jackmail_plugins_list', '', '', 'no' );
		add_option( 'jackmail_plugins', '', '', 'no' );
		$file_path = '';
		for ( $i = 0; $i < 20; $i ++ ) {
			$file_path .= substr( $choices, mt_rand( 0, $id_max_choices ), 1 );
		}
		add_option( 'jackmail_file_path', $file_path, '', 'no' );
		add_option( 'jackmail_connectors', '0', '', 'no' );
		add_option( 'jackmail_connectors_errors', '0', '', 'no' );
		add_option( 'jackmail_connectors_ip_restriction', '0', '', 'no' );
		add_option( 'jackmail_connectors_allowed_ips', '', '', 'no' );
		add_option( 'jackmail_domain_sub', '', '', 'no' );
		$key   = '';
		$nonce = '';
		for ( $i = 0; $i < 120; $i ++ ) {
			$key   .= substr( $choices, mt_rand( 0, $id_max_choices ), 1 );
			$nonce .= substr( $choices, mt_rand( 0, $id_max_choices ), 1 );
		}
		add_option( 'jackmail_key', str_rot13( $key ), '', 'no' );
		add_option( 'jackmail_nonce', str_rot13( $nonce ), '', 'no' );
		add_option( 'jackmail_version', $core->get_jackmail_version() );
		add_option( 'jackmail_update_available', '0' );
		add_option( 'jackmail_force_update_available', '0' );
		add_option( 'jackmail_emailbuilder_version', $core->get_emailbuilder_version() );
		add_option( 'jackmail_update_available_last_popup_display', '2017-02-01 00:00:00', '', 'no' );
		add_option( 'jackmail_notifications_messages', '', '', 'no' );
		add_option( 'jackmail_notifications_messages_hidden', array(), '', 'no' );
		add_option( 'jackmail_link_tracking', '1', '', 'no' );
		add_option( 'jackmail_emailbuilder', '0', '', 'no' );
		add_option( 'jackmail_default_template', '', '', 'no' );
		add_option( 'jackmail_default_template_images', '[]', '', 'no' );
		add_option( 'jackmail_default_template_check', '', '', 'no' );
		add_option( 'jackmail_default_template_compare', '', '', 'no' );
		
		add_option( 'jackmail_deleted_campaigns', array(), '', 'no' );
		add_option( 'jackmail_deleted_campaigns_date_gmt', $core->get_current_time_gmt_sql(), '', 'no' );
		
		add_option( 'jackmail_last_not_enough_credits_send', '0', '', 'no' );
		add_option( 'jackmail_support_chat', '1', '', 'no' );
		add_option( 'jackmail_display_premium_menu', '1' );
		add_option( 'jackmail_premium_notification', '1', '', 'no' );
		add_option( 'jackmail_display_premium_notification', '0' );
		add_option( 'jackmail_display_premium_notification_last_hide', '', '', 'no' );
		add_option( 'jackmail_statistics_version', 'v2' );
		add_option( 'jackmail_blacklist_last_update', $current_time_gmt, '', 'no' );
		add_option( 'jackmail_reset_blacklist', '1', '', 'no' );
		add_option( 'jackmail_email_images_size_limit', '1500000', '', 'no' );
		add_option( 'jackmail_access_type', 'administrator' );
		add_option( 'jackmail_used_ids', '1', '', 'no' );

		$charset_collate = $wpdb->get_charset_collate();

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jackmail_campaigns` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`id_lists` varchar(255) NOT NULL DEFAULT '',
			`fields` text NOT NULL,
			`name` varchar(255) NOT NULL DEFAULT '',
			`object` varchar(255) NOT NULL DEFAULT '',
			`sender_name` varchar(255) NOT NULL DEFAULT '',
			`sender_email` varchar(255) NOT NULL DEFAULT '',
			`reply_to_name` varchar(255) NOT NULL DEFAULT '',
			`reply_to_email` varchar(255) NOT NULL DEFAULT '',
			`content_email_json` mediumtext NOT NULL,
			`content_email_html` mediumtext NOT NULL,
			`content_email_txt` mediumtext NOT NULL,
			`content_email_images` text NOT NULL,
			`nb_contacts` varchar(30) NOT NULL DEFAULT '0',
			`nb_contacts_valids` varchar(30) NOT NULL DEFAULT '0',
			`preview` varchar(255) NOT NULL DEFAULT '',
			`link_tracking` varchar(1) NOT NULL DEFAULT '1',
			`created_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`updated_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`updated_by` varchar(30) NOT NULL DEFAULT '',
			`status` varchar(30) NOT NULL DEFAULT '',
			`status_detail` varchar(30) NOT NULL DEFAULT '',
			`send_option` varchar(30) NOT NULL DEFAULT '',
			`send_option_date_begin_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`send_option_date_end_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`campaign_id` varchar(30) NOT NULL DEFAULT '',
			`send_id` varchar(30) NOT NULL DEFAULT '',
			`validation_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`check_blacklist_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (`id`)
		) {$charset_collate}" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jackmail_scenarios` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`id_lists` varchar(255) NOT NULL DEFAULT '',
			`name` varchar(255) NOT NULL DEFAULT '',
			`object` varchar(255) NOT NULL DEFAULT '',
			`sender_name` varchar(255) NOT NULL DEFAULT '',
			`sender_email` varchar(255) NOT NULL DEFAULT '',
			`reply_to_name` varchar(255) NOT NULL DEFAULT '',
			`reply_to_email` varchar(255) NOT NULL DEFAULT '',
			`content_email_json` mediumtext NOT NULL,
			`content_email_html` mediumtext NOT NULL,
			`content_email_txt` mediumtext NOT NULL,
			`content_email_images` text NOT NULL,
			`nb_contacts` varchar(30) NOT NULL DEFAULT '0',
			`nb_contacts_valids` varchar(30) NOT NULL DEFAULT '0',
			`preview` varchar(255) NOT NULL DEFAULT '',
			`link_tracking` varchar(1) NOT NULL DEFAULT '1',
			`created_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`updated_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`updated_by` varchar(30) NOT NULL DEFAULT '',
			`status` varchar(30) NOT NULL DEFAULT '',
			`send_option` varchar(50) NOT NULL DEFAULT '',
			`data` text NOT NULL,
			PRIMARY KEY (`id`)
		) {$charset_collate}" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jackmail_scenarios_events` (
			`id` bigint(20) unsigned NOT NULL DEFAULT 0,
			`campaign_id` varchar(30) NOT NULL DEFAULT '',
			`send_id` varchar(30) NOT NULL DEFAULT '',
			`nb_contacts` varchar(30) NOT NULL DEFAULT '0',
			`nb_contacts_valids` varchar(30) NOT NULL DEFAULT '0',
			`data` text NOT NULL,
			`status` varchar(30) NOT NULL DEFAULT '',
			`status_detail` varchar(30) NOT NULL DEFAULT '',
			`status_error_code` varchar(30) NOT NULL DEFAULT '',
			`send_option_date_begin_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`send_option_date_end_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`validation_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  		`check_blacklist_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (`id`, `campaign_id`, `send_id`)
		) {$charset_collate}" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jackmail_lists` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL DEFAULT '',
			`id_campaign` varchar(255) NOT NULL DEFAULT '',
			`nb_contacts` varchar(255) NOT NULL DEFAULT '0',
			`nb_contacts_valids` varchar(30) NOT NULL DEFAULT '0',
			`fields` text NOT NULL,
			`created_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`updated_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`type` varchar(255) NOT NULL DEFAULT '',
			`connector_key` varchar(255) NOT NULL DEFAULT '',
			`connector_activation_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`connector_used_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (`id`)
		) {$charset_collate}" );

		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jackmail_templates` (
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL DEFAULT '',
			`content_email_json` mediumtext NOT NULL,
			`content_email_html` mediumtext NOT NULL,
			`content_email_txt` mediumtext NOT NULL,
			`content_email_images` text NOT NULL,
			`preview` varchar(255) NOT NULL DEFAULT '',
			`created_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`updated_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (`id`)
		) {$charset_collate}" );

		$core->check_or_create_list( 'wordpress-users', 1, __( 'WordPress users', 'jackmail-newsletters' ) );

		if ( ! wp_next_scheduled( 'jackmail_cron_progress_contacts_blacklist' ) ) {
			wp_schedule_event( $core->get_current_timestamp(), 'hourly', 'jackmail_cron_progress_contacts_blacklist' );
		}

		if ( ! wp_next_scheduled( 'jackmail_cron_progress_campaigns' ) ) {
			wp_schedule_event( $core->get_current_timestamp(), 'hourly', 'jackmail_cron_progress_campaigns' );
		}

		if ( ! wp_next_scheduled( 'jackmail_cron_domain' ) ) {
			wp_schedule_event( $core->get_current_timestamp(), 'hourly', 'jackmail_cron_domain' );
		}

		if ( ! wp_next_scheduled( 'jackmail_cron_scenarios' ) ) {
			wp_schedule_event( $core->get_current_timestamp(), 'hourly', 'jackmail_cron_scenarios' );
		}

		if ( ! wp_next_scheduled( 'jackmail_cron_actualize_plugins_lists' ) ) {
			wp_schedule_event( $core->get_current_timestamp(), 'hourly', 'jackmail_cron_actualize_plugins_lists' );
		}

		if ( ! wp_next_scheduled( 'jackmail_cron_notifications' ) ) {
			wp_schedule_event( $core->get_current_timestamp(), 'daily', 'jackmail_cron_notifications' );
		}

		if ( ! wp_next_scheduled( 'jackmail_cron_default_template' ) ) {
			wp_schedule_event( $core->get_current_timestamp(), 'daily', 'jackmail_cron_default_template' );
		}

		if ( ! wp_next_scheduled( 'jackmail_cron_clean_files' ) ) {
			wp_schedule_event( $core->get_current_timestamp(), 'daily', 'jackmail_cron_clean_files' );
		}

	}

	public static function deactivation() {
		
	}

	public static function uninstall() {
		global $wpdb;

		$core = new Jackmail_Core();

		$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}jackmail_scenarios`,
		`{$wpdb->prefix}jackmail_scenarios_events`,
		`{$wpdb->prefix}jackmail_lists_contacts_cf7_data`,
		`{$wpdb->prefix}jackmail_templates`" );

		if ( $core->check_table_exists( 'jackmail_lists' ) ) {
			$sql   = "SELECT `id` FROM `{$wpdb->prefix}jackmail_lists`";
			$lists = $wpdb->get_results( $sql );
			$sql   = 'DROP TABLE IF EXISTS ';
			foreach ( $lists as $list ) {
				$id_list = $list->id;
				$sql     .= "`{$wpdb->prefix}jackmail_lists_contacts_{$id_list}`, ";
			}
			$sql .= "`{$wpdb->prefix}jackmail_lists`";
			$wpdb->query( $sql );
		}

		if ( $core->check_table_exists( 'jackmail_campaigns' ) ) {
			$sql       = "SELECT `id` FROM `{$wpdb->prefix}jackmail_campaigns`";
			$campaigns = $wpdb->get_results( $sql );
			$sql       = 'DROP TABLE IF EXISTS ';
			foreach ( $campaigns as $campaign ) {
				$id_campaign = $campaign->id;
				$sql         .= "`{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}`, ";
			}
			$sql .= "`{$wpdb->prefix}jackmail_campaigns`";
			$wpdb->query( $sql );
		}

		$jackmail_file_path = $core->get_jackmail_file_path();
		if ( file_exists( $jackmail_file_path ) ) {
			if ( is_readable( $jackmail_file_path ) ) {
				$files = @scandir( $jackmail_file_path );
				foreach ( $files as $file ) {
					if ( $file !== '.' && $file !== '..' && $file !== 'index.php' && $file !== '.htaccess' ) {
						if ( filetype( $jackmail_file_path . $file ) === 'file' ) {
							
							@unlink( $jackmail_file_path . $file );
						}
					}
				}
				$files = @scandir( $jackmail_file_path );
				if ( count( $files ) === 4 ) {
					$error = false;
					if ( @unlink( $jackmail_file_path . 'index.php' ) && @unlink( $jackmail_file_path . '.htaccess' ) ) {
						if ( ! @rmdir( $jackmail_file_path ) ) {
							$error = true;
						}
					}
					if ( $error ) {
						@file_put_contents( $jackmail_file_path . 'index.php', '<?php' . "\n" . '// Silence is golden.' );
						$file_content = 'Deny from all';
						@file_put_contents( $jackmail_file_path . '.htaccess', '<?php' . "\n" . $file_content );
					}
				}
			}
		}

		delete_option( 'jackmail_id' );
		delete_option( 'jackmail_install_date' );
		delete_option( 'jackmail_is_configured' );
		delete_option( 'jackmail_account_token' );
		delete_option( 'jackmail_account_id' );
		delete_option( 'jackmail_user_id' );
		delete_option( 'jackmail_lastname' );
		delete_option( 'jackmail_firstname' );
		delete_option( 'jackmail_authentification_failed' );
		delete_option( 'jackmail_improvement' );
		delete_option( 'jackmail_plugins_list' );
		delete_option( 'jackmail_plugins' );
		delete_option( 'jackmail_file_path' );
		delete_option( 'jackmail_connectors' );
		delete_option( 'jackmail_connectors_errors' );
		delete_option( 'jackmail_connectors_ip_restriction' );
		delete_option( 'jackmail_connectors_allowed_ips' );
		delete_option( 'jackmail_domain_sub' );
		delete_option( 'jackmail_key' );
		delete_option( 'jackmail_nonce' );
		delete_option( 'jackmail_version' );
		delete_option( 'jackmail_update_available' );
		delete_option( 'jackmail_force_update_available' );
		delete_option( 'jackmail_emailbuilder_version' );
		delete_option( 'jackmail_update_available_last_popup_display' );
		delete_option( 'jackmail_notifications_messages' );
		delete_option( 'jackmail_notifications_messages_hidden' );
		delete_option( 'jackmail_link_tracking' );
		delete_option( 'jackmail_emailbuilder' );
		delete_option( 'jackmail_default_template' );
		delete_option( 'jackmail_default_template_images' );
		delete_option( 'jackmail_default_template_check' );
		delete_option( 'jackmail_default_template_compare' );
		delete_option( 'jackmail_deleted_campaigns' );
		
		delete_option( 'jackmail_deleted_campaigns_date_gmt' );
		
		delete_option( 'jackmail_last_not_enough_credits_send' );
		delete_option( 'jackmail_support_chat' );
		delete_option( 'jackmail_display_premium_menu' );
		delete_option( 'jackmail_premium_notification' );
		delete_option( 'jackmail_display_premium_notification' );
		delete_option( 'jackmail_display_premium_notification_last_hide' );
		delete_option( 'jackmail_statistics_version' );
		delete_option( 'jackmail_blacklist_last_update' );
		delete_option( 'jackmail_reset_blacklist' );
		delete_option( 'jackmail_email_images_size_limit' );
		delete_option( 'jackmail_access_type' );
		delete_option( 'jackmail_used_ids' );
		delete_option( 'widget_jackmail_widget' );

		delete_post_meta_by_key( 'jackmail_scenario_exclude' );

		delete_option( 'jackmail_domain' );
		delete_option( 'jackmail_domain_check' );
		delete_option( 'jackmail_domain_test' );

		wp_clear_scheduled_hook( 'jackmail_cron_progress_contacts_blacklist' );
		wp_clear_scheduled_hook( 'jackmail_cron_progress_campaigns' );
		wp_clear_scheduled_hook( 'jackmail_cron_domain' );
		wp_clear_scheduled_hook( 'jackmail_cron_scenarios' );
		wp_clear_scheduled_hook( 'jackmail_cron_actualize_plugins_lists' );
		wp_clear_scheduled_hook( 'jackmail_cron_notifications' );
		wp_clear_scheduled_hook( 'jackmail_cron_default_template' );
		wp_clear_scheduled_hook( 'jackmail_cron_clean_files' );

	}

	public function update_jackmail( $update, $item ) {
		$plugins = array(
			'jackmail-newsletters/jackmail-newsletters.php',
			'jackmail-newsletters',
			'jackmail'
		);
		if ( in_array( $item->slug, $plugins ) ) {
			return true;
		} else {
			return $update;
		}
	}

	private function create_configuration() {
		$jackmail_file_path = $this->core->get_jackmail_file_path();
		@mkdir( $jackmail_file_path );
		$file1        = $this->core->file_put_contents( 'index.php', '<?php' . "\n" . '// Silence is golden.' );
		$file_content = 'Deny from all';
		$file2        = $this->core->file_put_contents( '.htaccess', $file_content );
		if ( $file1 === false || $file2 === false ) {
			@unlink( $jackmail_file_path . $file1 );
			@unlink( $jackmail_file_path . $file2 );
			@rmdir( $jackmail_file_path );
		}
	}

	public function get_scripts() {

		if ( $this->core->is_admin() ) {

			$jackmail_version = $this->core->get_jackmail_version();

			if ( $this->core->is_jackmail_page() ) {

				$load_email_editor = $this->load_email_editor();

				$analytics = get_option( 'jackmail_improvement' );

				if ( $load_email_editor ) {

					if ( $this->core->emailbuilder_installed() ) {
						wp_register_script( 'emailbuilder-path', plugins_url( 'js/emailbuilder.config.js', __FILE__ ), array(), $jackmail_version, true );
						wp_enqueue_script( 'emailbuilder-path' );
					}
					if ( $this->load_email_editor_html_code() ) {
						wp_register_script( 'sb-editor', plugins_url( 'libs/sb-editor.js', __FILE__ ), array(), $jackmail_version, false );
						wp_enqueue_script( 'sb-editor' );
					}

				}

				if ( $analytics ) {
					wp_register_script( 'analytics', plugins_url( 'libs/analytics.js', __FILE__ ), array(), $jackmail_version, false );
					wp_enqueue_script( 'analytics' );
				}
				
				if ( get_option( 'jackmail_support_chat' ) === '1' ) {
					wp_register_script( 'crisp', plugins_url( 'libs/crisp.js', __FILE__ ), array(), $jackmail_version, false );
					wp_enqueue_script( 'crisp' );
				}

				wp_register_script( 'chart', plugins_url( 'libs/chart.js', __FILE__ ), array(), $jackmail_version, false );
				wp_register_script( 'angular-core', plugins_url( 'libs/angular.min.js', __FILE__ ), array(), $jackmail_version, false );
				wp_register_script( 'angular-route', plugins_url( 'libs/angular-route.min.js', __FILE__ ), array( 'angular-core' ), $jackmail_version, false );
				wp_register_script( 'jackmail-app', plugins_url( 'js/app.js', __FILE__ ), array( 'angular-core' ), $jackmail_version, false );

				

				
				wp_register_script( 'jackmail-controllers', plugins_url( 'js/controllers.js', __FILE__ ), array( 'angular-core' ), $jackmail_version, false );
				wp_register_script( 'jackmail-directives', plugins_url( 'js/directives.js', __FILE__ ), array( 'angular-core' ), $jackmail_version, false );
				wp_register_script( 'jackmail-filters', plugins_url( 'js/filters.js', __FILE__ ), array( 'angular-core' ), $jackmail_version, false );
				wp_register_script( 'jackmail-services', plugins_url( 'js/services.js', __FILE__ ), array( 'angular-core' ), $jackmail_version, false );
				

				wp_enqueue_script( 'chart' );
				wp_enqueue_script( 'angular-core' );
				wp_enqueue_script( 'angular-route' );
				wp_enqueue_script( 'jackmail-app' );

				

				
				wp_enqueue_script( 'jackmail-controllers' );
				wp_enqueue_script( 'jackmail-directives' );
				wp_enqueue_script( 'jackmail-filters' );
				wp_enqueue_script( 'jackmail-services' );
				

				wp_localize_script( 'jackmail-app', 'jackmail_translations_object', $this->get_translations() );
				if ( $this->is_jackmail_installation_page() ) {
					$this->create_configuration();
				}
				$jackmail_url                = plugins_url( '/', __FILE__ );
				$jackmail_file_path          = $this->core->get_jackmail_file_path();
				$jackmail_file_path_exists   = false;
				$jackmail_file_path_writable = false;
				if ( file_exists( $jackmail_file_path ) && file_exists( $jackmail_file_path . 'index.php' ) ) {
					$jackmail_file_path_exists = true;
					if ( is_writable( $jackmail_file_path ) ) {
						$jackmail_file_path_writable = true;
					}
				}
				$blacklists_types       = $this->core->get_blacklist_types();
				$update_available_array = $this->core->get_jackmail_update_available();
				$update_available       = $update_available_array['update'];
				$force_update_available = $update_available_array['force_update'];
				if ( $force_update_available === false ) {
					$last_time_gmt_sql    = get_option( 'jackmail_update_available_last_popup_display' );
					$current_time_gmt_sql = $this->core->get_current_time_gmt_sql();
					if ( strtotime( $current_time_gmt_sql ) - strtotime( $last_time_gmt_sql ) < 3600 ) {
						$update_available = false;
					}
				}
				$selected_date1                 = '0000-00-00 00:00:00';
				$selected_date2                 = '0000-00-00 00:00:00';
				$campaign_emailing              = true;
				$campaign_scenario              = false;
				$statistics_campaigns_selection = 'ALL';
				if ( isset( $_COOKIE['jackmail_selected_date1'] ) ) {
					$selected_date1 = $_COOKIE['jackmail_selected_date1'];
				}
				if ( isset( $_COOKIE['jackmail_selected_date2'] ) ) {
					$selected_date2 = $_COOKIE['jackmail_selected_date2'];
				}
				if ( isset( $_COOKIE['jackmail_campaign_emailing'] ) ) {
					$campaign_emailing = $_COOKIE['jackmail_campaign_emailing'] === 'true' ? true : false;
				}
				if ( isset( $_COOKIE['jackmail_campaign_scenario'] ) ) {
					$campaign_scenario = $_COOKIE['jackmail_campaign_scenario'] === 'true' ? true : false;
				}
				if ( isset( $_COOKIE['jackmail_statistics_campaigns_selection'] ) ) {
					$statistics_campaigns_selection = $_COOKIE['jackmail_statistics_campaigns_selection'];
				}
				$wp_paths    = wp_upload_dir();
				$upload_path = $wp_paths['baseurl'] . '/';

				$strs = $this->get_strs( $jackmail_file_path_exists, $jackmail_file_path_writable );

				$is_authenticated = $this->core->is_authenticated();

				wp_localize_script( 'jackmail-app', 'jackmail_ajax_object', array(
					'is_authenticated'                                                    => $is_authenticated,
					'website_url'                                                         => get_site_url(),
					'ajax_url'                                                            => admin_url( 'admin-ajax.php' ),
					'version'                                                             => $this->core->get_jackmail_version(),
					'analytics'                                                           => $this->core->boolval( $analytics ),
					'analytics_account'                                                   => 'UA-85606728-2',
					'update_available'                                                    => $update_available,
					'force_update_available'                                              => $force_update_available,
					'key'                                                                 => $strs['key'],
					'language'                                                            => $this->core->get_current_language(),
					'timezone'                                                            => get_option( 'gmt_offset' ),
					'current_time'                                                        => gmdate( 'Y-m-d H:i:s', strtotime( $this->core->get_current_time_gmt_sql() ) ),
					'openssl_random_pseudo_bytes_extension_function_exists'               => $this->core->openssl_random_pseudo_bytes_function_exists(),
					'gzdecode_gzencode_function_exists'                                   => $this->core->gzdecode_gzencode_function_exists(),
					'base64_decode_base64_encode_function_exists'                         => $this->core->base64_decode_base64_encode_function_exists(),
					'json_encode_json_decode_function_exists'                             => $this->core->json_encode_json_decode_function_exists(),
					'image_create_from_string_get_image_size_from_string_function_exists' => $this->core->image_create_from_string_get_image_size_from_string_function_exists(),
					'upload_url'                                                          => $upload_path,
					'jackmail_url'                                                        => $jackmail_url,
					'jackmail_file_path_name'                                             => $strs['jackmail_file_path_name'],
					'jackmail_file_path_exists'                                           => $jackmail_file_path_exists,
					'jackmail_file_path_writable'                                         => $jackmail_file_path_writable,
					'emailbuilder_installed'                                              => $this->core->emailbuilder_installed(),
					'emailbuilder_path'                                                   => $this->core->get_jackmail_url_emailbuilder(),
					'emailbuilder_api_url'                                                => $this->core->get_jackmail_url_global(),
					'jackmail_doc_url'                                                    => $this->core->get_jackmail_url_doc(),
					'emailbuilder_image_library_url'                                      => $this->core->get_jackmail_url_image_library(),
					'emailbuilder_display_product_button'                                 => $this->core->get_woocommerce_actived(),
					'blacklist_type_bounces'                                              => $blacklists_types['bounces'],
					'blacklist_type_complaints'                                           => $blacklists_types['complaints'],
					'blacklist_type_unsubscribes'                                         => $blacklists_types['unsubscribes'],
					'selected_date1'                                                      => $selected_date1,
					'selected_date2'                                                      => $selected_date2,
					'campaign_emailing'                                                   => $campaign_emailing,
					'campaign_scenario'                                                   => $campaign_scenario,
					'statistics_campaigns_selection'                                      => $statistics_campaigns_selection,
					'urls'                                                                => $this->get_urls( $is_authenticated ),
					'is_statistics_v2'                                                    => $this->core->is_statistics_v2()
				) );

			} else {
				$is_extension_page = $this->core->is_extensions_page();
				$is_dashboard_page = $this->core->is_dashboard_page();
				if ( $is_extension_page || $is_dashboard_page ) {
					wp_register_script( 'jackmail-admin', plugins_url( 'js/uninstall.js', __FILE__ ), array(), $jackmail_version, false );
					if ( $is_extension_page ) {
						wp_localize_script( 'jackmail-admin', 'jackmail_uninstall_translations_object', $this->get_translations_uninstall() );
					}
					wp_localize_script( 'jackmail-admin', 'jackmail_ajax_object', array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'version'  => $this->core->get_jackmail_version(),
						'key'      => $this->core->get_jackmail_key(),
						'urls'     => $this->get_urls_uninstall( $is_extension_page )
					) );
					wp_enqueue_script( 'jackmail-admin' );
				}

				if ( $this->is_widget_page() ) {
					wp_register_script( 'jackmail-widget', plugins_url( 'js/widget.js', __FILE__ ), array(), $jackmail_version, false );
					wp_enqueue_script( 'jackmail-widget' );
				}

			}

		}

	}

	private function get_strs( $jackmail_file_path_exists, $jackmail_file_path_writable ) {
		$strs = array(
			'key'                     => $this->core->get_jackmail_key(),
			'jackmail_file_path_name' => ''
		);
		if ( ! $jackmail_file_path_exists || ! $jackmail_file_path_writable ) {
			$strs['jackmail_file_path_name'] = 'uploads/jackmail-' . get_option( 'jackmail_file_path' );
		}
		foreach ( $strs as $key => $str ) {
			$str        = htmlentities( $str );
			$random     = $this->core->get_random();
			$random_ids = $this->core->str_len( $random ) - 1;
			$str_length = $this->core->str_len( $str );
			$new_str    = '';
			for ( $i = 0; $i < $str_length; $i ++ ) {
				$new_str .= $str[ $i ] . $random[ mt_rand( 0, $random_ids ) ];
			}
			$strs[ $key ] = mt_rand( 10000, 99999 ) . $new_str . mt_rand( 10000, 99999 );
		}
		return $strs;
	}

	private function get_urls_uninstall( $is_extension_page ) {
		$urls      = array();
		$urls_used = array();
		if ( $is_extension_page ) {
			$urls_used[] = 'jackmail_uninstall_reason';
		} else {
			$urls_used[] = 'jackmail_hide_notification';
		}
		$urls_used[] = 'jackmail_hide_premium_notification';
		foreach ( $urls_used as $url ) {
			$urls[ $url ] = wp_create_nonce( $url . get_option( 'jackmail_nonce' ) );
		}
		return $urls;
	}

	private function get_urls( $is_authenticated ) {
		global $plugin_page;
		$urls      = array();
		$urls_used = array();
		if ( $plugin_page === 'jackmail_campaign' || $plugin_page === 'jackmail_scenario' || $plugin_page === 'jackmail_list' ) {
			$urls_used = array(
				'jackmail_create_campaign',
				'jackmail_get_list',
				'jackmail_export_list',
				'jackmail_get_campaign_lists_available',
				'jackmail_get_scenario_lists_available',
				'jackmail_get_campaign',
				'jackmail_create_campaign',
				'jackmail_update_campaign',
				'jackmail_create_campaign_with_data',
				'jackmail_get_campaign_contacts',
				'jackmail_export_campaign_contacts',
				'jackmail_activate_campaign_link_tracking',
				'jackmail_deactivate_campaign_link_tracking',
				'jackmail_get_scenario',
				'jackmail_create_scenario',
				'jackmail_update_scenario',
				'jackmail_activate_scenario_link_tracking',
				'jackmail_deactivate_scenario_link_tracking',
				'jackmail_import_contacts',
				'jackmail_update_contact_email',
				'jackmail_update_contact_field',
				'jackmail_add_header_column',
				'jackmail_edit_header_column',
				'jackmail_delete_header_column',
				'jackmail_delete_all_contacts',
				'jackmail_delete_contacts_selection',
				'jackmail_export_contacts_selection',
				'jackmail_display_connectors',
				'jackmail_save_name',
				'jackmail_set_campaign_list_selection',
				'jackmail_create_list',
				'jackmail_get_template_json',
				'jackmail_get_images',
				'jackmail_get_gallery_template_json',
				'jackmail_get_post_types',
				'jackmail_get_post_categories',
				'jackmail_set_campaign_lists',
				'jackmail_send_campaign_test',
				'jackmail_send_scenario_test',
				'jackmail_send_campaign',
				'jackmail_campaign_last_step_checker',
				'jackmail_campaign_last_step_checker_analysis',
				'jackmail_scenario_last_step_checker',
				'jackmail_create_template',
				'jackmail_activate_scenario',
				'jackmail_deactivate_scenario'
			);
		} else if ( $plugin_page === 'jackmail_campaigns' ) {
			$urls_used = array(
				'jackmail_get_campaigns',
				'jackmail_update_cookies',
				'jackmail_duplicate_campaign',
				'jackmail_cancel_scheduled_campaign',
				'jackmail_delete_campaign',
				'jackmail_delete_scenario',
				'jackmail_deactivate_scenario',
				'jackmail_edit_campaign_name',
				'jackmail_edit_scenario_name'
			);
		} else if ( $plugin_page === 'jackmail_scenario_choice' ) {
			$urls_used = array(
				'jackmail_get_plugins'
			);
		} else if ( $plugin_page === 'jackmail_settings' ) {
			$urls_used = array(
				'jackmail_get_plugins',
				'jackmail_connectors_configuration',
				'jackmail_get_link_tracking',
				'jackmail_get_jackmail_role',
				'jackmail_get_improvement',
				'jackmail_import_plugins',
				'jackmail_domain_configuration',
				'jackmail_domain_get_txt_ns',
				'jackmail_domain_delete',
				'jackmail_domain_create_delegation',
				'jackmail_connectors_configure',
				'jackmail_connectors_configure_ip_restriction',
				'jackmail_connectors_configure_allowed_ips',
				'jackmail_set_link_tracking',
				'jackmail_set_jackmail_role',
				'jackmail_uninstall_emailbuilder',
				'jackmail_credits_available',
				'jackmail_user_disconnect',
				'jackmail_get_debug',
				'jackmail_get_debug_data',
				'jackmail_set_improvement',
				'jackmail_get_support_chat',
				'jackmail_set_support_chat',
				'jackmail_get_premium_notification',
				'jackmail_set_premium_notification',
				'jackmail_manual_update_data'
			);
		} else if ( $plugin_page === 'jackmail_statistics' ) {
			$urls_used = array(
				'jackmail_update_cookies',
				'jackmail_get_sent_campaigns',
				'jackmail_get_synthesis_more_actives_contacts',
				'jackmail_get_synthesis',
				'jackmail_get_recipients',
				'jackmail_get_synthesis_timeline',
				'jackmail_get_technologies',
				'jackmail_duplicate_campaign',
				'jackmail_add_campaign_contacts_unopened'
			);
		} else if ( $plugin_page === 'jackmail_templates' ) {
			$urls_used = array(
				'jackmail_get_templates',
				'jackmail_get_templates_gallery',
				'jackmail_duplicate_template',
				'jackmail_delete_template',
				'jackmail_create_campaign_with_template'
			);
		} else if ( $plugin_page === 'jackmail_template' ) {
			$urls_used = array(
				'jackmail_get_images',
				'jackmail_get_gallery_template_json',
				'jackmail_get_template',
				'jackmail_create_template',
				'jackmail_update_template',
				'jackmail_create_campaign_with_template'
			);
		} else if ( $plugin_page === 'jackmail_lists' ) {
			$urls_used = array(
				'jackmail_get_new_plugins',
				'jackmail_get_lists',
				'jackmail_delete_lists',
				'jackmail_export_list',
				'jackmail_create_list',
				'jackmail_save_name',
				'jackmail_save_new_plugins'
			);
		} else if ( $plugin_page === 'jackmail_list_detail' ) {
			$urls_used = array(
				'jackmail_get_email_lists_detail',
				'jackmail_get_email_detail',
				'jackmail_update_contact_field',
				'jackmail_unsubscribe_contact',
				'jackmail_unblacklist_contact'
			);
		} else if ( $plugin_page === 'jackmail_installation' ) {
			$urls_used = array(
				'jackmail_get_plugins_init',
				'jackmail_import_plugins',
				'jackmail_is_configured',
				'jackmail_get_emailbuilder_licence',
				'jackmail_install_emailbuilder'
			);
		}
		$urls_used[] = 'jackmail_hide_premium_notification';
		$urls_used[] = 'jackmail_hide_update_popup';
		if ( ! $this->core->emailbuilder_installed() ) {
			$urls_used[] = 'jackmail_get_emailbuilder_licence';
			$urls_used[] = 'jackmail_install_emailbuilder';
		} else if ( $plugin_page === 'jackmail_campaign' || $plugin_page === 'jackmail_scenario' || $plugin_page === 'jackmail_template' ) {
			$urls_used[] = 'jackmail_get_post_types';
			$urls_used[] = 'jackmail_get_post_categories';
			$urls_used[] = 'jackmail_get_posts';
			$urls_used[] = 'jackmail_get_post_or_page_or_custom_post_full_content';
			$urls_used[] = 'jackmail_get_pages';
			$urls_used[] = 'jackmail_get_woocommerce_product_categories';
			$urls_used[] = 'jackmail_get_woocommerce_products';
			$urls_used[] = 'jackmail_get_woocommerce_product_full_content';
			$urls_used[] = 'jackmail_get_custom_posts_categories';
			$urls_used[] = 'jackmail_get_custom_posts';
			$urls_used[] = 'jackmail_save_image';
			$urls_used[] = 'jackmail_get_fields_and_ids';
			if ( $plugin_page === 'jackmail_campaign' || $plugin_page === 'jackmail_scenario' ) {
				$urls_used[] = 'jackmail_get_templates';
				$urls_used[] = 'jackmail_get_templates_gallery';
				$urls_used[] = 'jackmail_get_gallery_template_json';
				$urls_used[] = 'jackmail_get_template';
			}
		}
		$urls_used[] = 'jackmail_search_faq';
		$urls_used[] = 'jackmail_search_campaigns';
		$urls_used[] = 'jackmail_search_all';
		$urls_used[] = 'jackmail_suggestion_faq';
		$urls_used[] = 'jackmail_suggestion_forum';
		$urls_used[] = 'jackmail_get_forum';
		if ( ! $is_authenticated || get_option( 'jackmail_authentification_failed' ) === '1' || $plugin_page === 'jackmail_settings' ) {
			$urls_used[] = 'jackmail_account_connection';
			$urls_used[] = 'jackmail_account_reset';
		}
		if ( ! $is_authenticated ) {
			$urls_used[] = 'jackmail_account_creation';
			$urls_used[] = 'jackmail_account_resend_activation_email';
		}
		$urls_used[] = 'jackmail_campaigns_page';
		$urls_used[] = 'jackmail_campaign_page';
		$urls_used[] = 'jackmail_scenario_choice_page';
		$urls_used[] = 'jackmail_scenario_page';
		$urls_used[] = 'jackmail_lists_page';
		$urls_used[] = 'jackmail_list_page';
		$urls_used[] = 'jackmail_list_detail_page';
		$urls_used[] = 'jackmail_templates_page';
		$urls_used[] = 'jackmail_template_page';
		$urls_used[] = 'jackmail_statistics_page';
		$urls_used[] = 'jackmail_settings_page';
		$urls_used[] = 'jackmail_installation_page';
		$urls_used[] = 'jackmail_account_info';
		foreach ( $urls_used as $url ) {
			$urls[ $url ] = wp_create_nonce( $url . get_option( 'jackmail_nonce' ) );
		}
		return $urls;
	}

	private function get_translations_uninstall() {
		return array(
			'uninstall'    => __( 'Uninstall', 'jackmail-newsletters' ),
			'cancel'       => __( 'Cancel', 'jackmail-newsletters' ),
			'introduction' => __( 'You are about to deactivate Jackmail', 'jackmail-newsletters' ),
			'warning'      => __( 'You will loose all your data and will need to set up Jackmail again.', 'jackmail-newsletters' ),
			'reason'       => __( 'Let us know why:', 'jackmail-newsletters' ),
			'reason1'      => __( 'My campaigns have been refused by the moderation team', 'jackmail-newsletters' ),
			'reason2'      => __( 'I think Jackmail is expensive', 'jackmail-newsletters' ),
			'reason3'      => __( 'I find the plugin difficult to use', 'jackmail-newsletters' ),
			'reason4'      => __( 'I do not need to send campaigns each month', 'jackmail-newsletters' ),
			'reason5'      => __( 'It\'s a dev version or a test to fix issues', 'jackmail-newsletters' ),
			'reason_other' => __( 'Other', 'jackmail-newsletters' )
		);
	}

	private function get_translations() {
		return array(
			'actions'                                                                                    => __( 'Actions', 'jackmail-newsletters' ),
			'activate_scenario'                                                                          => __( 'Activate workflow?', 'jackmail-newsletters' ),
			'add_a_recipient'                                                                            => __( 'Add a recipient', 'jackmail-newsletters' ),
			'administrators'                                                                             => __( 'Administrators', 'jackmail-newsletters' ),
			'after'                                                                                      => __( 'after', 'jackmail-newsletters' ),
			'an_error_occurred_the_campaign_was_not_found'                                               => __( 'An error occurred.<br/>The campaign was not found.', 'jackmail-newsletters' ),
			'and'                                                                                        => __( 'and', 'jackmail-newsletters' ),
			'and_from'                                                                                   => __( 'and from', 'jackmail-newsletters' ),
			'animals'                                                                                    => __( 'Animals', 'jackmail-newsletters' ),
			'apply'                                                                                      => __( 'Apply', 'jackmail-newsletters' ),
			'automated_newsletter'                                                                       => __( 'Automated newsletter', 'jackmail-newsletters' ),
			'automated_newsletter_description'                                                           => __( 'Create your template, configure your send periodicity, select your recipients and let Jackmail do the rest. ', 'jackmail-newsletters' ),
			'account'                                                                                    => __( 'Account', 'jackmail-newsletters' ),
			'actived'                                                                                    => __( 'Actived', 'jackmail-newsletters' ),
			'add_a_column'                                                                               => __( 'Add a column', 'jackmail-newsletters' ),
			'all'                                                                                        => __( 'All', 'jackmail-newsletters' ),
			'all_categories'                                                                             => __( 'All categories', 'jackmail-newsletters' ),
			'an_error_occurred'                                                                          => __( 'An error occurred', 'jackmail-newsletters' ),
			'an_error_occurred_while_the_campaign_was_canceled'                                          => __( 'An error occurred while the campaign was canceled', 'jackmail-newsletters' ),
			'apr'                                                                                        => __( 'apr.', 'jackmail-newsletters' ),
			'article_link'                                                                               => __( 'Article link', 'jackmail-newsletters' ),
			'astronomy'                                                                                  => __( 'Astronomy', 'jackmail-newsletters' ),
			'at'                                                                                         => __( 'at', 'jackmail-newsletters' ),
			'aug'                                                                                        => __( 'aug', 'jackmail-newsletters' ),
			'a_valid_email_address_is_required'                                                          => __( 'A valid email address is required', 'jackmail-newsletters' ),
			'a_valid_reply_to_email_address_is_required'                                                 => __( 'A valid "Reply to" email address is required', 'jackmail-newsletters' ),
			'a_valid_sender_email_address_is_required'                                                   => __( 'A valid "Sender" email address is required', 'jackmail-newsletters' ),
			'a_valid_test_recipient_email_address_is_required'                                           => __( 'A valid test recipient email address is required', 'jackmail-newsletters' ),
			'base64_encode_or_base64_decode_php_function_not_found'                                      => __( 'The Php features "base64_encode" or "base64_decode" were not found', 'jackmail-newsletters' ),
			'before'                                                                                     => __( 'before', 'jackmail-newsletters' ),
			'blacklist'                                                                                  => __( 'Blacklist', 'jackmail-newsletters' ),
			'business'                                                                                   => __( 'Business', 'jackmail-newsletters' ),
			'by_copy_pasting'                                                                            => __( 'By copy/pasting', 'jackmail-newsletters' ),
			'campaign'                                                                                   => __( 'Campaign', 'jackmail-newsletters' ),
			'campaign_with_no_name'                                                                      => __( 'Campaign with no name', 'jackmail-newsletters' ),
			'campaigns'                                                                                  => __( 'Campaigns', 'jackmail-newsletters' ),
			'cancel'                                                                                     => __( 'Cancel', 'jackmail-newsletters' ),
			'calendar_period_1'                                                                          => __( 'Period 1:', 'jackmail-newsletters' ),
			'calendar_period_2'                                                                          => __( 'Period 2:', 'jackmail-newsletters' ),
			'campaigns_from'                                                                             => __( 'Campaigns from', 'jackmail-newsletters' ),
			'categories'                                                                                 => __( 'Categories', 'jackmail-newsletters' ),
			'characters'                                                                                 => __( 'Characters', 'jackmail-newsletters' ),
			'clicks'                                                                                     => __( 'Clicks', 'jackmail-newsletters' ),
			'clothing'                                                                                   => __( 'Clothing', 'jackmail-newsletters' ),
			'compare'                                                                                    => __( 'Compare', 'jackmail-newsletters' ),
			'complained'                                                                                 => __( 'Complained', 'jackmail-newsletters' ),
			'confirm'                                                                                    => __( 'Confirm', 'jackmail-newsletters' ),
			'comparison'                                                                                 => __( '(Comparison)', 'jackmail-newsletters' ),
			'contact_list_selection'                                                                     => __( 'Contact list selection', 'jackmail-newsletters' ),
			'contact_saved'                                                                              => __( 'Contact saved', 'jackmail-newsletters' ),
			'contains'                                                                                   => __( 'contains', 'jackmail-newsletters' ),
			'content_is_empty'                                                                           => __( 'Content is empty', 'jackmail-newsletters' ),
			'correct'                                                                                    => __( 'Correct', 'jackmail-newsletters' ),
			'creating_campaign'                                                                          => __( 'Creating campaign', 'jackmail-newsletters' ),
			'custom_scenario'                                                                            => __( 'Custom workflow', 'jackmail-newsletters' ),
			'data_displayed_are_not_up_to_date_click_ok_to_reload_it_before_you_send_the_campaign'       => __( 'Data displayed are not up-to-date.<br/>Click ok to reload it before you send the campaign.', 'jackmail-newsletters' ),
			'data_displayed_are_not_up_to_date_click_ok_to_reload_it_before_you_schedule_the_campaign'   => __( 'Data displayed are not up-to-date.<br/>Click ok to reload it before you schedule the campaign.', 'jackmail-newsletters' ),
			'date'                                                                                       => __( 'date', 'jackmail-newsletters' ),
			'day'                                                                                        => __( 'day', 'jackmail-newsletters' ),
			'days'                                                                                       => __( 'days', 'jackmail-newsletters' ),
			'deactivate_scenario'                                                                        => __( 'Deactivate workflow?', 'jackmail-newsletters' ),
			'dec'                                                                                        => __( 'dec.', 'jackmail-newsletters' ),
			'delete'                                                                                     => __( 'Delete', 'jackmail-newsletters' ),
			'delete_a_contact'                                                                           => __( 'Delete a contact', 'jackmail-newsletters' ),
			'delete_list'                                                                                => __( 'Delete list?', 'jackmail-newsletters' ),
			'delete_column'                                                                              => __( 'Delete column', 'jackmail-newsletters' ),
			'delete_selection'                                                                           => __( 'Delete selection', 'jackmail-newsletters' ),
			'deleted_contacts'                                                                           => __( 'Deleted contacts', 'jackmail-newsletters' ),
			'deleted_contact'                                                                            => __( 'Deleted contact', 'jackmail-newsletters' ),
			'desktop'                                                                                    => __( 'Desktop', 'jackmail-newsletters' ),
			'detailed_reading'                                                                           => __( 'Detailed reading', 'jackmail-newsletters' ),
			'device_category'                                                                            => __( 'Device category', 'jackmail-newsletters' ),
			'do_you_confirm_this_scheduling'                                                             => __( 'Do you confirm this scheduling?', 'jackmail-newsletters' ),
			'do_you_confirm_this_sending'                                                                => __( 'Do you confirm this sending?', 'jackmail-newsletters' ),
			'draft'                                                                                      => __( 'Draft', 'jackmail-newsletters' ),
			'edit'                                                                                       => __( 'Edit', 'jackmail-newsletters' ),
			'editors_and_administrators'                                                                 => __( 'Editors and administrators', 'jackmail-newsletters' ),
			'email_at_example_com'                                                                       => __( 'email@example.com', 'jackmail-newsletters' ),
			'email'                                                                                      => __( 'Email', 'jackmail-newsletters' ),
			'emailbuilder'                                                                               => __( 'Emailbuilder', 'jackmail-newsletters' ),
			'emailbuilder_is_now_installed'                                                              => __( 'Emailbuilder is now installed', 'jackmail-newsletters' ),
			'emailbuilder_is_now_uninstalled'                                                            => __( 'Emailbuilder is now uninstalled', 'jackmail-newsletters' ),
			'email_client'                                                                               => __( 'Email client', 'jackmail-newsletters' ),
			'email_client_category'                                                                      => __( 'Email client category', 'jackmail-newsletters' ),
			'emoticons'                                                                                  => __( 'Emoticons', 'jackmail-newsletters' ),
			'entry_to_a_list'                                                                            => __( 'Entry to a list', 'jackmail-newsletters' ),
			'error'                                                                                      => __( 'Error', 'jackmail-newsletters' ),
			'error_content_is_too_long'                                                                  => __( 'Error: content is too long', 'jackmail-newsletters' ),
			'error_images_are_too_large'                                                                 => __( 'Error: images are too large', 'jackmail-newsletters' ),
			'error_while_checking_credits_available'                                                     => __( 'Error while checking credits available', 'jackmail-newsletters' ),
			'error_while_loading_images'                                                                 => __( 'Error while loading images', 'jackmail-newsletters' ),
			'error_while_loading_emailbuilder_licence'                                                   => __( 'Can\'t create new template. Error while loading Emailbuilder licence', 'jackmail-newsletters' ),
			'error_while_save_campaign'                                                                  => __( 'Error while save campaign', 'jackmail-newsletters' ),
			'feb'                                                                                        => __( 'feb.', 'jackmail-newsletters' ),
			'fields_separator_must_be_semicolon_comma_vertical_bar_or_tabulation'                        => __( '(fields separator must be semicolon, comma, vertical bar or tabulation)', 'jackmail-newsletters' ),
			'food'                                                                                       => __( 'Food', 'jackmail-newsletters' ),
			'firstname'                                                                                  => __( 'Firstname', 'jackmail-newsletters' ),
			'from_a_list'                                                                                => __( 'From a list', 'jackmail-newsletters' ),
			'from_campaign'                                                                              => __( 'From campaign', 'jackmail-newsletters' ),
			'games'                                                                                      => __( 'Games', 'jackmail-newsletters' ),
			'gestures'                                                                                   => __( 'Gestures', 'jackmail-newsletters' ),
			'gzencode_or_gzdecode_php_function_not_found'                                                => __( 'The Php features "gzdecode" or "gzencode" were not found', 'jackmail-newsletters' ),
			'hardbounced'                                                                                => __( 'Hardbounced', 'jackmail-newsletters' ),
			'health'                                                                                     => __( 'Health', 'jackmail-newsletters' ),
			'heart'                                                                                      => __( 'Heart', 'jackmail-newsletters' ),
			'higher_than'                                                                                => __( 'higher than', 'jackmail-newsletters' ),
			'hobbies'                                                                                    => __( 'Hobbies', 'jackmail-newsletters' ),
			'hour'                                                                                       => __( 'Hour', 'jackmail-newsletters' ),
			'hours'                                                                                      => __( 'Hours', 'jackmail-newsletters' ),
			'html_code'                                                                                  => __( 'Html code', 'jackmail-newsletters' ),
			'import_contacts'                                                                            => __( 'Import contacts', 'jackmail-newsletters' ),
			'incorrect_sub_domain'                                                                       => __( 'Incorrect sub-domain', 'jackmail-newsletters' ),
			'information_saved'                                                                          => __( 'Information saved', 'jackmail-newAsletters' ),
			'invalid_ip_address'                                                                         => __( 'Invalid IP address', 'jackmail-newsletters' ),
			'is_empty'                                                                                   => __( 'is empty', 'jackmail-newsletters' ),
			'is_equal_to'                                                                                => __( 'is equal to', 'jackmail-newsletters' ),
			'is_different_from'                                                                          => __( 'is different from', 'jackmail-newsletters' ),
			'is_unsubscribed'                                                                            => __( 'is unsubscribed', 'jackmail-newsletters' ),
			'jan'                                                                                        => __( 'jan.', 'jackmail-newsletters' ),
			'json_encode_or_json_decode_php_function_not_found'                                          => __( 'The Php features "json_encode" or "json_decode" were not found', 'jackmail-newsletters' ),
			'june'                                                                                       => __( 'june', 'jackmail-newsletters' ),
			'july'                                                                                       => __( 'july.', 'jackmail-newsletters' ),
			'last_month'                                                                                 => __( 'Last month', 'jackmail-newsletters' ),
			'lastname'                                                                                   => __( 'Lastname', 'jackmail-newsletters' ),
			'last_week_mon_fri'                                                                          => __( 'Last week (mon-fri)', 'jackmail-newsletters' ),
			'last_week_mon_sun'                                                                          => __( 'Last week (mon-sun)', 'jackmail-newsletters' ),
			'list_is_empty'                                                                              => __( 'List is empty', 'jackmail-newsletters' ),
			'list_name'                                                                                  => __( 'List name', 'jackmail-newsletters' ),
			'list_name_must_be_unique'                                                                   => __( 'List name must be unique', 'jackmail-newsletters' ),
			'lists'                                                                                      => __( 'Lists', 'jackmail-newsletters' ),
			'log_out_when_login_out_you_will_loose_all_yout_data_and_will_need_to_set_up_jackmail_again' => __( '<span class="jackmail_title">Log out?</span><br/><br/><span class="jackmail_grey">When logging out, you will loose all your data and will need to set up Jackmail again.</span>', 'jackmail-newsletters' ),
			'lower_than'                                                                                 => __( 'lower than', 'jackmail-newsletters' ),
			'mail'                                                                                       => __( 'Mail', 'jackmail-newsletters' ),
			'mar'                                                                                        => __( 'mar.', 'jackmail-newsletters' ),
			'may'                                                                                        => __( 'may', 'jackmail-newsletters' ),
			'minute'                                                                                     => __( 'Minute', 'jackmail-newsletters' ),
			'mobile'                                                                                     => __( 'Mobile', 'jackmail-newsletters' ),
			'monsters'                                                                                   => __( 'Monsters', 'jackmail-newsletters' ),
			'my_templates'                                                                               => __( 'My templates', 'jackmail-newsletters' ),
			'music'                                                                                      => __( 'Music', 'jackmail-newsletters' ),
			'name'                                                                                       => __( 'Name', 'jackmail-newsletters' ),
			'nature'                                                                                     => __( 'Nature', 'jackmail-newsletters' ),
			'needHelp'                                                                                   => __( 'Need help?', 'jackmail-newsletters' ),
			'new_at_example_com'                                                                         => __( 'new@example.com', 'jackmail-newsletters' ),
			'no_list_associated_in_the_campaign'                                                         => __( 'No list associated in the campaign', 'jackmail-newsletters' ),
			'no_recipient_included_in_the_campaign'                                                      => __( 'No recipient included in the campaign', 'jackmail-newsletters' ),
			'no_valids_recipients'                                                                       => __( 'No valids recipients', 'jackmail-newsletters' ),
			'no_valid_contacts_found'                                                                    => __( 'No valid contacts found', 'jackmail-newsletters' ),
			'not_enough_credits'                                                                         => __( 'Not enough credits', 'jackmail-newsletters' ),
			'nov'                                                                                        => __( 'nov.', 'jackmail-newsletters' ),
			'number'                                                                                     => __( 'number', 'jackmail-newsletters' ),
			'objects'                                                                                    => __( 'Objects', 'jackmail-newsletters' ),
			'office'                                                                                     => __( 'Office', 'jackmail-newsletters' ),
			'o_s'                                                                                        => __( 'O.S.', 'jackmail-newsletters' ),
			'oct'                                                                                        => __( 'oct.', 'jackmail-newsletters' ),
			'one_or_more_campaign_fields_are_missing'                                                    => __( 'One or more campaign fields are missing', 'jackmail-newsletters' ),
			'openings'                                                                                   => __( 'Openings', 'jackmail-newsletters' ),
			'unsubscribers'                                                                              => __( 'Unsubscribers', 'jackmail-newsletters' ),
			'or'                                                                                         => __( 'or', 'jackmail-newsletters' ),
			'page_link'                                                                                  => __( 'Page link', 'jackmail-newsletters' ),
			'password_confirmation_doesn_t_match_password'                                               => __( 'Password confirmation doesn\'t match password', 'jackmail-newsletters' ),
			'period'                                                                                     => __( 'Period', 'jackmail-newsletters' ),
			'period_1'                                                                                   => __( '(Period 1):', 'jackmail-newsletters' ),
			'period_2'                                                                                   => __( '(Period 2):', 'jackmail-newsletters' ),
			'plain_text'                                                                                 => __( 'Plain text', 'jackmail-newsletters' ),
			'please_activate_the_extension_openssl_random_pseudo_bytes_on_your_web_server'               => __( 'Please activate the extension "openssl_random_pseudo_bytes" on your web server', 'jackmail-newsletters' ),
			'plugin_bloom_description'                                                                   => __( 'A simple, comprehensive and beautifully constructed email opt-in plugin built to help you quickly grow your mailing list.', 'jackmail-newsletters' ),
			'plugin_contactform7_description'                                                            => __( 'Just another contact form plugin. Simple but flexible.', 'jackmail-newsletters' ),
			'plugin_formidableforms_description'                                                         => __( 'Quickly and easily create drag-and-drop forms', 'jackmail-newletters' ),
			'plugin_gravityforms_description'                                                            => __( 'Easily create web forms and manage form entries within the WordPress admin.', 'jackmail-newsletters' ),
			'plugin_mailpoet2_description'                                                               => __( 'Create and send newsletters or automated emails. Capture subscribers with a widget. Import and manage your lists.', 'jackmail-newsletters' ),
			'plugin_mailpoet3_description'                                                               => __( 'Create and send email newsletters, autoresponders, and post notifications without leaving WordPress.', 'jackmail-newsletters' ),
			'plugin_ninjaforms_description'                                                              => __( 'Ninja Forms is a ease of use webform builder with a lot of features.', 'jackmail-newsletters' ),
			'plugin_popupbysupsystic_description'                                                        => __( 'The Best WordPress popup plugin to help you gain more subscribers, social followers or advertisement. Responsive popups with friendly options', 'jackmail-newsletters' ),
			'plugin_woocommerce_description'                                                             => __( 'Import your contact list and send targeted messages to this list.', 'jackmail-newsletters' ),
			'publish_a_post'                                                                             => __( 'Publish a content', 'jackmail-newsletters' ),
			'publish_a_post_description'                                                                 => __( 'Send an automatic email to your recipients whenever you publish new content in any category.', 'jackmail-newsletters' ),
			'Recipients'                                                                                 => __( 'Recipients', 'jackmail-newsletters' ),
			'recipients'                                                                                 => __( 'recipients', 'jackmail-newsletters' ),
			'recipient'                                                                                  => __( 'recipient', 'jackmail-newsletters' ),
			'refused'                                                                                    => __( 'Refused by moderation', 'jackmail-newsletters' ),
			'refused_aborted_by_user'                                                                    => __( 'Sending canceled by the user', 'jackmail-newsletters' ),
			'refused_bad_content'                                                                        => __( 'Content nonconform mailing list charter', 'jackmail-newsletters' ),
			'refused_bad_list'                                                                           => __( 'Contact list against Emailing list charter', 'jackmail-newsletters' ),
			'refused_bad_topic'                                                                          => __( 'Subject against Emailing list charter', 'jackmail-newsletters' ),
			'refused_image_problem'                                                                      => __( 'Erroneous images', 'jackmail-newsletters' ),
			'refused_js_found'                                                                           => __( 'Javascript found', 'jackmail-newsletters' ),
			'refused_no_unsubscribe'                                                                     => __( 'No opt-out link found', 'jackmail-newsletters' ),
			'refused_phishing'                                                                           => __( 'Phishing', 'jackmail-newsletters' ),
			'refused_text_format'                                                                        => __( 'Invalid text format of the message', 'jackmail-newsletters' ),
			'refused_too_many_autobounces'                                                               => __( 'Suspicious addresses', 'jackmail-newsletters' ),
			'remove_the_selection'                                                                       => __( 'Remove the selection?', 'jackmail-newsletters' ),
			'permissions'                                                                                => __( 'Permissions', 'jackmail-newsletters' ),
			'save_as_template'                                                                           => __( 'Save as template', 'jackmail-newsletters' ),
			'save_campaign'                                                                              => __( 'Save campaign', 'jackmail-newsletters' ),
			'save_template'                                                                              => __( 'Save template', 'jackmail-newsletters' ),
			'save_template_and_start_my_campaign'                                                        => __( 'Save template & start my campaign', 'jackmail-newsletters' ),
			'scheduled'                                                                                  => __( 'Scheduled', 'jackmail-newsletters' ),
			'scheduled_sending_confirmation'                                                             => __( 'Scheduled sending confirmation', 'jackmail-newsletters' ),
			'scenario'                                                                                   => __( 'Workflow', 'jackmail-newsletters' ),
			'search'                                                                                     => __( 'Search', 'jackmail-newsletters' ),
			'searching'                                                                                  => __( 'Searching', 'jackmail-newsletters' ),
			'selected_category'                                                                          => __( 'selected category', 'jackmail-newsletters' ),
			'selected_categories'                                                                        => __( 'selected categories', 'jackmail-newsletters' ),
			'selection'                                                                                  => __( 'Selection', 'jackmail-newsletters' ),
			'selection_is_empty'                                                                         => __( 'Selection is empty', 'jackmail-newsletters' ),
			'sending'                                                                                    => __( 'Sending', 'jackmail-newsletters' ),
			'sending_confirmation'                                                                       => __( 'Sending confirmation', 'jackmail-newsletters' ),
			'select_a_period_to_send_out'                                                                => __( 'Select a period to send out:', 'jackmail-newsletters' ),
			'selected_date_is_not_valid'                                                                 => __( 'Selected date is not valid', 'jackmail-newsletters' ),
			'selected_lists_have_been_deleted'                                                           => __( 'Selected lists have been deleted', 'jackmail-newsletters' ),
			'sept'                                                                                       => __( 'sept.', 'jackmail-newsletters' ),
			'sent'                                                                                       => __( 'Sent', 'jackmail-newsletters' ),
			'server_message'                                                                             => __( 'Server message:', 'jackmail-newsletters' ),
			'settings'                                                                                   => __( 'Settings', 'jackmail-newsletters' ),
			'shop_managers_and_administrators'                                                           => __( 'Shop managers and administrators', 'jackmail-newsletters' ),
			'should_be_editable'                                                                         => __( 'should be editable', 'jackmail-newsletters' ),
			'simplified_reading'                                                                         => __( 'Simplified reading', 'jackmail-newsletters' ),
			'sport'                                                                                      => __( 'Sport', 'jackmail-newsletters' ),
			'downloading'                                                                                => __( 'Downloading', 'jackmail-newsletters' ),
			'statistics'                                                                                 => __( 'Statistics', 'jackmail-newsletters' ),
			'submit_form'                                                                                => __( 'Submit form', 'jackmail-newsletters' ),
			'symbols'                                                                                    => __( 'Symbols', 'jackmail-newsletters' ),
			'technology'                                                                                 => __( 'Technology', 'jackmail-newsletters' ),
			'templates'                                                                                  => __( 'Templates', 'jackmail-newsletters' ),
			'template_from'                                                                              => __( 'Template from', 'jackmail-newsletters' ),
			'templates_gallery'                                                                          => __( 'Templates gallery', 'jackmail-newsletters' ),
			'template_with_no_name'                                                                      => __( 'Template with no name', 'jackmail-newsletters' ),
			'the_campaign_name_has_been_saved'                                                           => __( 'The campaign name has been saved', 'jackmail-newsletters' ),
			'the_campaign_named'                                                                         => __( 'The campaign named', 'jackmail-newsletters' ),
			'the_campaign_has_been_canceled'                                                             => __( 'The campaign has been canceled', 'jackmail-newsletters' ),
			'the_campaign_has_been_created'                                                              => __( 'The campaign has been created', 'jackmail-newsletters' ),
			'the_campaign_has_been_deleted'                                                              => __( 'The campaign has been deleted', 'jackmail-newsletters' ),
			'the_campaign_has_been_duplicated'                                                           => __( 'The campaign has been duplicated', 'jackmail-newsletters' ),
			'the_campaign_has_been_saved'                                                                => __( 'The campaign has been saved', 'jackmail-newsletters' ),
			'the_column_has_been_saved'                                                                  => __( 'The column has been saved', 'jackmail-newsletters' ),
			'the_column_has_been_deleted'                                                                => __( 'The column has been deleted', 'jackmail-newsletters' ),
			'the_email_has_been_opened'                                                                  => __( 'The email has been opened', 'jackmail-newsletters' ),
			'the_email_has_not_been_opened'                                                              => __( 'The email has not been opened', 'jackmail-newsletters' ),
			'the_email_has_been_opened_and_clicked'                                                      => __( 'The email has been opened and clicked', 'jackmail-newsletters' ),
			'the_email_has_been_opened_but_has_not_been_clicked'                                         => __( 'The email has been opened but has not been clicked', 'jackmail-newsletters' ),
			'the_email_has_been_opened_on_a_mobile'                                                      => __( 'The email has been opened on a mobile', 'jackmail-newsletters' ),
			'the_email_has_been_opened_on_a_desktop'                                                     => __( 'The email has been opened on a desktop', 'jackmail-newsletters' ),
			'the_file'                                                                                   => __( 'The file', 'jackmail-newsletters' ),
			'the_file_format_is_not_valid'                                                               => __( 'The file format is not valid', 'jackmail-newsletters' ),
			'the_last_7_days'                                                                            => __( 'The last 7 days', 'jackmail-newsletters' ),
			'the_message_field_is_required'                                                              => __( 'The "Message" field is required', 'jackmail-newsletters' ),
			'the_reply_to_field_is_required'                                                             => __( 'The "Reply to" field is required', 'jackmail-newsletters' ),
			'the_scenario_name_has_been_saved'                                                           => __( 'The scenario name has been saved', 'jackmail-newsletters' ),
			'the_sender_field_is_required'                                                               => __( 'The "Sender" field is required', 'jackmail-newsletters' ),
			'the_subject_field_is_required'                                                              => __( 'The "Subject" field is required', 'jackmail-newsletters' ),
			'the_json_file_is_not_valid'                                                                 => __( 'The JSON file is not valid', 'jackmail-newsletters' ),
			'the_last_14_days'                                                                           => __( 'The last 14 days', 'jackmail-newsletters' ),
			'the_last_30_days'                                                                           => __( 'The last 30 days', 'jackmail-newsletters' ),
			'the_last_90_days'                                                                           => __( 'The last 90 days', 'jackmail-newsletters' ),
			'the_last_180_days'                                                                          => __( 'The last 180 days', 'jackmail-newsletters' ),
			'the_list_is_empty'                                                                          => __( 'The list is empty', 'jackmail-newsletters' ),
			'the_list_was_deleted'                                                                       => __( 'The list was deleted', 'jackmail-newsletters' ),
			'the_list_was_saved'                                                                         => __( 'The list was saved', 'jackmail-newsletters' ),
			'the_name_of_the_list_has_been_saved'                                                        => __( 'The name of the list has been saved', 'jackmail-newsletters' ),
			'the_template_has_been_duplicated'                                                           => __( 'The template has been duplicated', 'jackmail-newsletters' ),
			'the_template_was_deleted'                                                                   => __( 'The template was deleted', 'jackmail-newsletters' ),
			'the_template_was_saved'                                                                     => __( 'The template was saved', 'jackmail-newsletters' ),
			'the_test_email_has_been_sent'                                                               => __( 'The test email has been sent', 'jackmail-newsletters' ),
			'the_test_recipient_is_required'                                                             => __( 'The test recipient is required', 'jackmail-newsletters' ),
			'the_user_name_or_the_password_is_incorrect'                                                 => __( 'The user name or the password is incorrect', 'jackmail-newsletters' ),
			'the_scenario_was_activated'                                                                 => __( 'The workflow was activated', 'jackmail-newsletters' ),
			'the_scenario_was_deleted'                                                                   => __( 'The workflow was deleted', 'jackmail-newsletters' ),
			'the_scenario_was_disabled'                                                                  => __( 'The workflow was disabled', 'jackmail-newsletters' ),
			'the_scenario_was_duplicated'                                                                => __( 'The workflow was duplicated', 'jackmail-newsletters' ),
			'this_month'                                                                                 => __( 'This month', 'jackmail-newsletters' ),
			'this_week'                                                                                  => __( 'This week', 'jackmail-newsletters' ),
			'to'                                                                                         => __( 'to', 'jackmail-newsletters' ),
			'today'                                                                                      => __( 'Today', 'jackmail-newsletters' ),
			'tools'                                                                                      => __( 'Tools', 'jackmail-newsletters' ),
			'tourism'                                                                                    => __( 'Tourism', 'jackmail-newsletters' ),
			'transport'                                                                                  => __( 'Transport', 'jackmail-newsletters' ),
			'txt_record_not_found'                                                                       => __( 'TXT record not found', 'jackmail-newsletters' ),
			'unsubscribe_link'                                                                           => __( 'UNSUBSCRIBE LINK', 'jackmail-newsletters' ),
			'unsubscribed'                                                                               => __( 'Unsubscribed', 'jackmail-newsletters' ),
			'unsubscribe'                                                                                => __( 'Unsubscribe', 'jackmail-newsletters' ),
			'update_contact_details'                                                                     => __( 'Update contact details', 'jackmail-newsletters' ),
			'webcopy'                                                                                    => __( 'Webcopy', 'jackmail-newsletters' ),
			'webcopy_link'                                                                               => __( 'WEBCOPY LINK', 'jackmail-newsletters' ),
			'weather'                                                                                    => __( 'Weather', 'jackmail-newsletters' ),
			'widget_which_use_this_list_will_be_deactived'                                               => __( '(Widgets which use this list will be deactived)', 'jackmail-newsletters' ),
			'will_be_scheduled_to'                                                                       => __( 'will be scheduled to', 'jackmail-newsletters' ),
			'will_be_sent_to'                                                                            => __( 'will be sent to', 'jackmail-newsletters' ),
			'workflows_which_use_only_this_list_will_be_deactived'                                       => __( '(Workflows which use only this list will be deactived)', 'jackmail-newsletters' ),
			'woocommerce_automated_newsletter'                                                           => __( 'WooCommerce - automated newsletter', 'jackmail-newsletters' ),
			'woocommerce_automated_newsletter_description'                                               => __( 'Advise your visitors when new products are available in your WooCommerce store.', 'jackmail-newsletters' ),
			'woocommerce'                                                                                => __( 'WooCommerce', 'jackmail-newsletters' ),
			'yesterday'                                                                                  => __( 'Yesterday', 'jackmail-newsletters' ),
			'you_don_t_have_enough_credits_available'                                                    => __( 'You don\'t have enough credits available', 'jackmail-newsletters' ),
			'you_may_select_to_to_3_segments'                                                            => __( 'You may select up to 3 segments', 'jackmail-newsletters' ),
			'you_will_loose_your_html_content'                                                           => __( 'You will loose your html content', 'jackmail-newsletters' ),
			'you_will_loose_your_emailbuilder_content'                                                   => __( 'You will loose your Emailbuilder content', 'jackmail-newsletters' ),
			'you_must_login_or_create_a_jackmail_account'                                                => __( 'You must login or create a Jackmail account', 'jackmail-newsletters' ),
			'you_must_accept_the_general_terms_of_jackmail'                                              => __( 'You must accept the general terms of Jackmail', 'jackmail-newsletters' )
		);
	}

	public function get_styles() {
		if ( $this->core->is_admin() ) {
			$jackmail_version = $this->core->get_jackmail_version();
			wp_register_style( 'jackmail-style', plugins_url( 'css/jackmail.css', __FILE__ ), array(), $jackmail_version );
			wp_enqueue_style( 'jackmail-style' );
		}
	}

	public function add_admin_menu() {
		if ( $this->core->is_admin() ) {
			if ( ! $this->core->is_configured() ) {
				$this->add_menu_page( 'jackmail_installation' );
				$this->add_submenu_page( __( 'Installation', 'jackmail-newsletters' ), 'jackmail_installation', 'jackmail_installation' );
				$this->add_submenu_page( __( 'Installation', 'jackmail-newsletters' ), 'jackmail_installation', 'jackmail_installation' );
			} else {
				$this->add_menu_page( 'jackmail_campaigns' );
				$this->add_submenu_page( __( 'Campaigns', 'jackmail-newsletters' ), 'jackmail_campaigns' );
				$this->add_submenu_page( __( 'Create a campaign', 'jackmail-newsletters' ), 'jackmail_campaign' );
				$this->add_submenu_page( __( 'Workflow', 'jackmail-newsletters' ), 'jackmail_scenario_choice' );
				$this->add_submenu_page( __( 'Workflow', 'jackmail-newsletters' ), 'jackmail_scenario' );
				$this->add_submenu_page( __( 'Lists', 'jackmail-newsletters' ), 'jackmail_lists' );
				$this->add_submenu_page( __( 'Create a list', 'jackmail-newsletters' ), 'jackmail_list' );
				$this->add_submenu_page( __( 'List detail', 'jackmail-newsletters' ), 'jackmail_list_detail' );
				$this->add_submenu_page( __( 'Templates', 'jackmail-newsletters' ), 'jackmail_templates' );
				$this->add_submenu_page( __( 'Template', 'jackmail-newsletters' ), 'jackmail_template' );
				$this->add_submenu_page( __( 'Statistics', 'jackmail-newsletters' ), 'jackmail_statistics' );
				$this->add_submenu_page( __( 'Settings', 'jackmail-newsletters' ), 'jackmail_settings' );
				if ( $this->core->is_visible() ) {
					global $submenu;
					$submenu['jackmail_campaigns'][] = array(
						'<span onclick="event.preventDefault();window.open( this.parentNode.href );">' . __( 'Account', 'jackmail-newsletters' ) . '</span>',
						$this->core->access_type(),
						'https://my.jackmail.com'
					);
					if ( get_option( 'jackmail_display_premium_menu' ) === '1' ) {
						$submenu['jackmail_campaigns'][] = array(
							'<span onclick="event.preventDefault();window.open( this.parentNode.href );">' . __( 'Break the limit', 'jackmail-newsletters' ) . '</span>',
							$this->core->access_type(),
							'https://www.jackmail.com/pricing'
						);
					}
				}
			}
		}
	}

	private function add_menu_page( $page ) {
		add_menu_page( __( 'Jackmail', 'jackmail-newsletters' ), __( 'Jackmail', 'jackmail-newsletters' ), $this->core->access_type(), $page, array( $this->core, 'main_page' ), plugins_url( 'img/plugin.png', __FILE__ ) );
	}

	private function add_submenu_page( $title, $page, $parent_page = 'jackmail_campaigns' ) {
		add_submenu_page( $parent_page, $title, $title, $this->core->access_type(), $page, array( $this->core, 'main_page' ) );
	}

	public function admin_body_class() {
		if ( $this->core->is_jackmail_page() ) {
			return 'jackmail_plugin';
		}
		return '';
	}

	private function load_email_editor() {
		global $plugin_page;
		if ( $this->core->is_admin() && ( $plugin_page === 'jackmail_campaign' || $plugin_page === 'jackmail_scenario' || $plugin_page === 'jackmail_template' ) ) {
			return true;
		}
		return false;
	}

	private function load_email_editor_html_code() {
		global $plugin_page;
		if ( $this->core->is_admin() && ( $plugin_page === 'jackmail_campaign' || $plugin_page === 'jackmail_scenario' ) ) {
			return true;
		}
		return false;
	}

	private function is_jackmail_installation_page() {
		global $plugin_page;
		if ( $this->core->is_admin() && $plugin_page === 'jackmail_installation' ) {
			return true;
		}
		return false;
	}

	private function is_widget_page() {
		$current_screen = get_current_screen();
		if ( isset( $current_screen->base ) ) {
			if ( $this->core->is_admin() && $current_screen->base === 'widgets' ) {
				return true;
			}
		}
		return false;
	}

	public function load_languages() {
		load_plugin_textdomain( 'jackmail-newsletters', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

}

if ( defined( 'ABSPATH' ) ) {
	new Jackmail_Plugin();
}