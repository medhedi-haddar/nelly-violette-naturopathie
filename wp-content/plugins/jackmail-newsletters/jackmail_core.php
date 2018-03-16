<?php

class Jackmail_Core {

	public function main_page() {
		if ( ! current_user_can( $this->access_type() ) ) {
			wp_die( __( 'You don\'t have sufficient permissions to access this page.' ) );
		}
		$params = array(
			'is_configured'          => $this->is_configured(),
			'is_authenticated'       => $this->is_authenticated(),
			'emailbuilder_installed' => $this->emailbuilder_installed()
		);
		$this->include_html_file( 'main', $params );
	}

	public function include_class_file( $filename ) {
		include_once plugin_dir_path( __FILE__ ) . $filename . '.php';
	}

	public function include_core_class_file( $filename ) {
		include_once plugin_dir_path( __FILE__ ) . 'core/' . $filename . '.php';
	}

	public function include_html_file( $filename, $params = array() ) {
		foreach ( $params as $key => $param ) {
			$$key = $param;
		}
		include_once plugin_dir_path( __FILE__ ) . 'html/' . $filename . '.php';
	}

	public function is_jackmail_page() {
		global $plugin_page;
		if ( $this->is_admin() && strpos( $plugin_page, 'jackmail_' ) !== false ) {
			return true;
		}
		return false;
	}

	public function is_dashboard_page() {
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
			if ( isset( $current_screen->base ) ) {
				if ( $this->is_admin() && $current_screen->base === 'dashboard' ) {
					return true;
				}
			}
		}
		return false;
	}

	public function is_extensions_page() {
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
			if ( isset( $current_screen->base ) ) {
				if ( $this->is_admin() && $current_screen->base === 'plugins' ) {
					return true;
				}
			}
		}
		return false;
	}

	public function access_type() {
		$configured_access_type = get_option( 'jackmail_access_type', 'administrator' );
		if ( $configured_access_type === 'administrator' ) {
			return 'administrator';
		} else if ( $configured_access_type === 'editor' || $configured_access_type === 'shop_manager' ) {
			if ( function_exists( 'wp_get_current_user' ) ) {
				$current_user = wp_get_current_user();
				if ( isset( $current_user->roles ) ) {
					if ( is_array( $current_user->roles ) ) {
						if ( isset( $current_user->roles[0] ) ) {
							$current_user_role = $current_user->roles[0];
							if ( $current_user_role === 'editor' || $current_user_role === 'shop_manager' ) {
								return $current_user_role;
							}
						}
					}
				}
			}
		}
		return 'administrator';
	}

	public function get_jackmail_version() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( function_exists( 'get_plugin_data' ) ) {
			$plugin_data = get_plugin_data( dirname( __FILE__ ) . '/jackmail-newsletters.php' );
			if ( isset( $plugin_data['Version'] ) ) {
				return $plugin_data['Version'];
			}
		}
		return 'jackmail';
	}

	public function update_jackmail_database() {
		
		$jackmail_id = get_option( 'jackmail_id' );
		update_option( 'jackmail_id', $jackmail_id . '-', 'yes' );
		update_option( 'jackmail_id', $jackmail_id );
		
		
		update_option( 'jackmail_emailbuilder_version', $this->get_emailbuilder_version() );
		
		add_option( 'jackmail_email_images_size_limit', '1500000', '', 'no' );
		add_option( 'jackmail_access_type', 'administrator' );
		
		add_option( 'jackmail_default_template', '', '', 'no' );
		add_option( 'jackmail_default_template_images', '[]', '', 'no' );
		add_option( 'jackmail_default_template_compare', '', '', 'no' );
		add_option( 'jackmail_default_template_check', '0', '', 'no' );
		
		add_option( 'jackmail_support_chat', '1' );
		
		add_option( 'jackmail_display_premium_notification', '0' );
		add_option( 'jackmail_display_premium_notification_last_hide', '' );
		
		
		add_option( 'jackmail_update_available_last_popup_display', '2017-02-01 00:00:00', '', 'no' );
		global $wpdb;
		$sql = "ALTER TABLE `{$wpdb->prefix}jackmail_scenarios` CHANGE `send_option` `send_option` varchar(50) NOT NULL DEFAULT ''";
		$wpdb->query( $sql );
		
		
		$this->check_or_create_list( 'wordpress-users', 1, __( 'WordPress users', 'jackmail-newsletters' ) );
		
		
		$sql = "ALTER TABLE `{$wpdb->prefix}jackmail_campaigns` CHANGE `fields` `fields` text NOT NULL";
		$wpdb->query( $sql );
		$sql = "UPDATE `{$wpdb->prefix}jackmail_lists` SET `nb_contacts` = '-1', `nb_contacts_valids` = '-1' WHERE `type` != '' AND `type` NOT LIKE %s AND `type` NOT LIKE %s";
		$wpdb->query( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( 'contactform7' ) . '%', '%' . $wpdb->esc_like( 'bloom' ) . '%' ) );
		
		
		$scenarios_events_columns = $wpdb->get_col( "DESC {$wpdb->prefix}jackmail_scenarios_events", 0 );
		if ( ! in_array( 'status_error_code', $scenarios_events_columns ) ) {
			$sql = "ALTER TABLE `{$wpdb->prefix}jackmail_scenarios_events` ADD COLUMN `status_error_code` varchar(30) NOT NULL DEFAULT '' AFTER `status`";
			$wpdb->query( $sql );
		}
		
		
		if ( ! in_array( 'status_detail', $scenarios_events_columns ) ) {
			$sql = "ALTER TABLE `{$wpdb->prefix}jackmail_scenarios_events` ADD COLUMN `status_detail` varchar(30) NOT NULL DEFAULT '' AFTER `status`";
			$wpdb->query( $sql );
		}
		$campaigns_columns = $wpdb->get_col( "DESC {$wpdb->prefix}jackmail_campaigns", 0 );
		if ( ! in_array( 'status_detail', $campaigns_columns ) ) {
			$sql = "ALTER TABLE `{$wpdb->prefix}jackmail_campaigns` ADD COLUMN `status_detail` varchar(30) NOT NULL DEFAULT '' AFTER `status`";
			$wpdb->query( $sql );
		}
		
		
		$this->update_jackmail_database_scenario_data();
		
	}

	public function update_jackmail_database_scenario_data() {
		
		global $wpdb;
		$sql       = "SELECT `id`, `send_option`, `data`
		FROM `{$wpdb->prefix}jackmail_scenarios`
		WHERE `data` NOT LIKE %s";
		$campaigns = $wpdb->get_results( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( 'post_type' ) . '%' ) );
		foreach ( $campaigns as $campaign ) {
			if ( isset( $campaign->id, $campaign->send_option, $campaign->data ) ) {
				$data_fields = $this->explode_data( $campaign->data );
				if ( ! isset( $data_fields['post_type'] ) ) {
					if ( $campaign->send_option === 'woocommerce_automated_newsletter' ) {
						$data_fields['post_type'] = 'product';
					} else {
						$data_fields['post_type'] = 'post';
					}
					if ( isset( $data_fields['post_type'], $data_fields['post_categories'], $data_fields['nb_posts_content'],
						$data_fields['periodicity_type'], $data_fields['periodicity_value'], $data_fields['event_date_gmt'] ) ) {
						$data_fields = array(
							'post_type'         => $data_fields['post_type'],
							'post_categories'   => $data_fields['post_categories'],
							'nb_posts_content'  => $data_fields['nb_posts_content'],
							'periodicity_type'  => $data_fields['periodicity_type'],
							'periodicity_value' => $data_fields['periodicity_value'],
							'event_date_gmt'    => $data_fields['event_date_gmt']
						);
						$data        = $this->implode_data( $data_fields );
						$this->update_scenario( array(
							'data' => $data
						), array(
							'id' => $campaign->id
						) );
					}
				}
			}
		}
		
	}

	public function get_jackmail_update_available() {
		global $plugin_page;
		$result         = array(
			'update'       => false,
			'force_update' => false
		);
		$actual_version = $this->get_jackmail_version();
		if ( get_option( 'jackmail_version' ) === '' ) {
			update_option( 'jackmail_version', $actual_version );
		}
		$saved_version = get_option( 'jackmail_version' );
		if ( version_compare( $actual_version, $saved_version, '>' ) ) {
			update_option( 'jackmail_version', $actual_version );
			update_option( 'jackmail_update_available', '0' );
			update_option( 'jackmail_force_update_available', '0' );
			$this->update_jackmail_database();
			return $result;
		}
		if ( get_option( 'jackmail_update_available' ) === '1' ) {
			$result['update'] = true;
		}
		if ( get_option( 'jackmail_force_update_available' ) === '1' ) {
			$result = array(
				'update'       => true,
				'force_update' => true
			);
		}
		if ( $plugin_page === 'jackmail_campaigns' ) {
			$result['force_update'] = false;
		}
		return $result;
	}

	public function check_auth() {
		if ( ! isset( $_POST['key'] ) ) {
			die;
		}
		$key = $this->request_text_data( $_POST['key'] );
		if ( $_POST['key'] !== $this->get_jackmail_key() || $this->str_len( $key ) < 120 ) {
			die;
		}
		if ( ! isset( $_POST['action'] ) ) {
			die;
		}
		$action = $this->request_text_data( $_POST['action'] );
		if ( ! isset( $_POST['nonce'] ) ) {
			die;
		}
		$nonce = $this->request_text_data( $_POST['nonce'] );
		if ( $this->str_len( $nonce ) < 10 ) {
			die;
		}
		if ( ! wp_verify_nonce( $nonce, $action . get_option( 'jackmail_nonce' ) ) ) {
			die;
		}
		if ( ! check_admin_referer( $action . get_option( 'jackmail_nonce' ), 'nonce' ) ) {
			die;
		}
		if ( ! current_user_can( $this->access_type() ) ) {
			die;
		}
		if ( ! $this->is_admin() ) {
			die;
		}
	}

	public function is_visible() {
		if ( $this->is_admin() ) {
			global $submenu;
			if ( isset( $submenu['jackmail_campaigns'] ) ) {
				return true;
			}
		}
		return false;
	}

	public function create_campaign_list_table( $id_campaign ) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "
		DROP TABLE IF EXISTS `{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}`";
		$wpdb->query( $sql );
		
		$sql = "
		CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}` (
			`email` varchar(100) NOT NULL DEFAULT '',
			`blacklist` int(1) NOT NULL DEFAULT '0',
			`id_list` bigint(20) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`email`)
		) {$charset_collate}";
		return $wpdb->query( $sql );
	}

	public function create_list_table( $id_list, $nb_fields = 0 ) {
		global $wpdb;
		$fields = '';
		for ( $i = 0; $i < $nb_fields; $i ++ ) {
			$field  = 'field' . ( $i + 1 );
			$fields .= "`{$field}` varchar(255) NOT NULL DEFAULT '',";
		}
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "
		DROP TABLE IF EXISTS `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}`";
		$wpdb->query( $sql );
		
		$sql = "
		CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` (
			`email` varchar(100) NOT NULL DEFAULT '',
			`blacklist` int(1) NOT NULL DEFAULT '0',
			{$fields}
			PRIMARY KEY (`email`)
		) {$charset_collate}";
		return $wpdb->query( $sql );
	}

	public function create_list_field( $table_list_contacts, $field ) {
		global $wpdb;
		$sql = "ALTER TABLE `{$table_list_contacts}` ADD COLUMN `{$field}` varchar(255) NOT NULL DEFAULT ''";
		return $wpdb->query( $sql );
	}

	public function delete_list_field( $table_list_contacts, $field_id, $nb_fields ) {
		global $wpdb;
		$columns = $this->get_table_columns( $table_list_contacts, false );
		if ( in_array( 'field' . $field_id, $columns ) ) {
			$sql          = "ALTER TABLE `{$table_list_contacts}` DROP COLUMN `field{$field_id}`";
			$alter_return = $wpdb->query( $sql );
			if ( $alter_return !== false ) {
				for ( $i = 1; $i <= $nb_fields; $i ++ ) {
					if ( $i > $field_id ) {
						$field_name     = 'field' . $i;
						$new_field_name = 'field' . ( $i - 1 );
						if ( in_array( $field_name, $columns ) ) {
							$sql = "ALTER TABLE `{$table_list_contacts}` CHANGE `{$field_name}` `{$new_field_name}` varchar(255) NOT NULL DEFAULT ''";
							$wpdb->query( $sql );
						}
					}
				}
				return true;
			}
		}
		return false;
	}

	public function json_encode( $data ) {
		return json_encode( $data, JSON_UNESCAPED_UNICODE );
	}

	public function implode_data( Array $data ) {
		return json_encode( $data, JSON_UNESCAPED_UNICODE );
	}

	public function explode_data( $data ) {
		if ( $data === '' ) {
			return array();
		}
		return json_decode( $data, true );
	}

	public function implode_fields( Array $fields ) {
		$fields = $this->str_to_upper( json_encode( $fields, JSON_UNESCAPED_UNICODE ) );
		return $fields;
	}

	public function explode_fields( $fields ) {
		if ( $fields === '' ) {
			return array();
		}
		$fields = $this->str_to_upper( $fields );
		return json_decode( $fields, true );
	}

	public function check_table_exists( $table, $add_prefix = true ) {
		global $wpdb;
		if ( $add_prefix ) {
			$table = $wpdb->prefix . $table;
		}
		$sql = "SHOW TABLES LIKE '{$table}'";
		if ( $wpdb->get_var( $sql ) === "{$table}" ) {
			return true;
		}
		return false;
	}

	public function get_table_columns( $table, $add_prefix = true, $check_table_exists = false ) {
		global $wpdb;
		if ( $check_table_exists && ! $this->check_table_exists( $table, $add_prefix ) ) {
			return array();
		}
		if ( $add_prefix ) {
			$table = $wpdb->prefix . $table;
		}
		$columns = $wpdb->get_col( "DESC {$table}", 0 );
		return $columns;
	}

	public function get_random() {
		$random         = '';
		$id             = mt_rand( 15, 85 );
		$choices        = 'abcdefghijklmopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$id_max_choices = $this->str_len( $choices ) - 1;
		for ( $i = 0; $i < 100; $i ++ ) {
			if ( $i === $id ) {
				$random .= uniqid();
			}
			$random .= substr( $choices, mt_rand( 0, $id_max_choices ), 1 );
		}
		return $random;
	}

	public function is_admin() {
		if ( is_admin() ) {
			return true;
		}
		return false;
	}

	public function is_configured() {
		if ( get_option( 'jackmail_is_configured' ) === '1' ) {
			return true;
		}
		return false;
	}

	public function is_authenticated() {
		if ( $this->get_account_token() !== '' && $this->get_account_id() !== '' && $this->get_user_id() !== '' ) {
			return true;
		}
		return false;
	}

	public function get_jackmail_key() {
		return str_rot13( get_option( 'jackmail_key' ) );
	}

	public function get_account_token() {
		return $this->get_account_option( get_option( 'jackmail_account_token', '' ) );
	}

	public function get_account_id() {
		return $this->get_account_option( get_option( 'jackmail_account_id', '' ) );
	}

	public function get_user_id() {
		return $this->get_account_option( get_option( 'jackmail_user_id', '' ) );
	}

	public function get_lastname() {
		return $this->get_account_option( get_option( 'jackmail_lastname', '' ) );
	}

	public function get_firstname() {
		return $this->get_account_option( get_option( 'jackmail_firstname', '' ) );
	}

	private function get_account_option( $value ) {
		if ( $value !== '' && $this->gzdecode_gzencode_function_exists() && $this->base64_decode_base64_encode_function_exists() ) {
			return gzdecode( base64_decode( $value ) );
		}
		return '';
	}

	public function set_account_token( $account_token ) {
		$this->set_account_option( 'account_token', $account_token );
	}

	public function set_account_id( $account_id ) {
		$this->set_account_option( 'account_id', $account_id );
	}

	public function set_user_id( $user_id ) {
		$this->set_account_option( 'user_id', $user_id );
	}

	public function set_lastname( $lastname ) {
		$this->set_account_option( 'lastname', $lastname );
	}

	public function set_firstname( $firstname ) {
		$this->set_account_option( 'firstname', $firstname );
	}

	private function set_account_option( $name, $value ) {
		if ( $this->gzdecode_gzencode_function_exists() && $this->base64_decode_base64_encode_function_exists() ) {
			update_option( 'jackmail_' . $name, base64_encode( gzencode( $value ) ) );
		}
	}

	public function get_iso_date( $date ) {
		return str_replace( ' ', 'T', $date ) . '.000Z';
	}

	public function get_mysql_date( $date ) {
		return substr( str_replace( 'T', ' ', $date ), 0, 19 );
	}

	public function generate_jackmail_preview_filename() {
		return $this->get_current_timestamp() . mt_rand( 10000000, 99999999 ) . uniqid();
	}

	public function get_current_language() {
		if ( substr( get_locale(), 0, 2 ) === 'fr' ) {
			return 'fr';
		}
		return 'en';
	}

	public function get_current_time_gmt_sql() {
		return current_time( 'mysql', 1 );
	}

	public function get_current_time_sql() {
		return current_time( 'mysql' );
	}

	public function get_current_timestamp() {
		return current_time( 'timestamp' );
	}

	public function htmlentitiesencode( $content ) {
		return htmlspecialchars_decode( htmlentities( $content ) );
	}

	public function generate_guid() {
		if ( $this->openssl_random_pseudo_bytes_function_exists() ) {
			$guid = base64_encode( openssl_random_pseudo_bytes( 16 ) );
			$guid = str_replace( '=', '', $guid );
			$guid = str_replace( '+', '-', $guid );
			$guid = str_replace( '/', '_', $guid );
			return $guid;
		}
		return '';
	}

	public function get_jackmail_file_path() {
		$wp_paths = wp_upload_dir();
		return $wp_paths['basedir'] . '/jackmail-' . get_option( 'jackmail_file_path' ) . '/';
	}

	public function jackmail_images() {
		if ( isset( $_REQUEST['jackmail_image'], $_REQUEST['jackmail_image_type'] ) ) {
			header( 'Cache-Control: no-cache, must-revalidate' );
			header( 'Expires: Mon, 01 Feb 2017 00:00:00 GMT' );
			$preview    = $this->request_text_data( $_REQUEST['jackmail_image'] );
			$image_type = $this->request_text_data( $_REQUEST['jackmail_image_type'] );
			if ( $image_type === 'png' || $image_type === 'jpeg' || $image_type === 'gif' || $image_type === 'bmp' || $image_type === 'tiff' ) {
				$image_content = $this->get_image_file_content( $preview, $image_type );
				if ( $image_content !== false ) {
					if ( isset( $_REQUEST['jackmail_image_preview'] ) ) {
						$image_preview = $this->request_text_data( $_REQUEST['jackmail_image_preview'] );
						if ( $image_preview === 'campaign' || $image_preview === 'scenario' || $image_preview === 'template' ) {
							if ( md5( $image_content ) === '20ca4b882a5f42a062f42b8eabae4197' ) {
								global $wpdb;
								if ( $image_preview === 'template' ) {
									$table = 'jackmail_templates';
								} else if ( $image_preview === 'campaign' ) {
									$table = 'jackmail_campaigns';
								} else {
									$table = 'jackmail_scenarios';
								}
								$sql           = "
								SELECT `content_email_json`, `content_email_html`, `content_email_txt`
								FROM `{$wpdb->prefix}{$table}`
								WHERE `preview` = %s";
								$content_email = $wpdb->get_row( $wpdb->prepare( $sql, $preview ) );
								if ( isset( $content_email->content_email_json, $content_email->content_email_html, $content_email->content_email_txt ) ) {
									if ( $content_email->content_email_json !== '' || $content_email->content_email_html !== ''
									     || $content_email->content_email_txt !== '' ) {
										$content_email_json = $content_email->content_email_json;
										$content_email_html = $content_email->content_email_html;
										$content_email_txt  = $content_email->content_email_txt;
										$this->generate_content_email_preview( $preview, $content_email_json, $content_email_html, $content_email_txt );
										$image_content = $this->get_image_file_content( $preview, $image_type );
									}
								}
							}
						}
					}
				}
				if ( $image_content !== false ) {
					header( 'Content-Type: image/' . $image_type );
					echo $image_content;
				} else {
					header( 'Content-Type: image/png' );
					status_header( 404 );
				}
			}
			die;
		}
	}

	public function get_image_file_content( $image_id, $image_type ) {
		if ( ! $this->image_create_from_string_get_image_size_from_string_function_exists() ) {
			return false;
		}
		if ( substr( $image_id, - 4 ) === '-IMG' ) {
			$image_id  = substr( $image_id, 0, - 4 );
			$file_path = plugin_dir_path( __FILE__ ) . 'img/';
			$image     = @file_get_contents( $file_path . $image_id . '.' . $image_type );
		} else {
			$image = $this->get_file_content( $image_id . '.' . $image_type );
		}
		$check = @imagecreatefromstring( $image );
		if ( ! $check ) {
			return false;
		}
		$size = @getimagesizefromstring( $image );
		if ( ! is_array( $size ) || ! isset( $size[0] ) || ! isset( $size[1] )
		     || ! isset( $size[2] ) || ! isset( $size[3] ) || ! isset( $size['bits'] ) || ! isset( $size['mime'] ) ) {
			return false;
		} else if ( substr( $size['mime'], 0, 6 ) !== 'image/' ) {
			return false;
		}
		$image_type = substr( $size['mime'], 6 );
		if ( $image_type !== 'png' && $image_type !== 'jpeg' && $image_type !== 'gif' && $image_type !== 'bmp' & $image_type !== 'tiff' ) {
			return false;
		}
		return $image;
	}

	public function get_image_file_size( $image_id, $image_type ) {
		if ( substr( $image_id, - 4 ) === '-IMG' ) {
			$image_id   = substr( $image_id, 0, - 4 );
			$file_path  = plugin_dir_path( __FILE__ ) . 'img/';
			$image_size = @filesize( $file_path . $image_id . '.' . $image_type );
		} else {
			$image_size = $this->get_file_size( $image_id . '.' . $image_type );
		}
		return $image_size;
	}

	private function file_exists( $filename ) {
		$jackmail_file_path = $this->get_jackmail_file_path();
		if ( @file_exists( $jackmail_file_path . $filename ) ) {
			return true;
		}
		return false;
	}

	public function file_put_contents( $filename, $content ) {
		$jackmail_file_path = $this->get_jackmail_file_path();
		return @file_put_contents( $jackmail_file_path . $filename, trim( $content ) );
	}

	public function get_file_content( $filename ) {
		$jackmail_file_path = $this->get_jackmail_file_path();
		$file_path          = $jackmail_file_path . $filename;
		return @file_get_contents( $file_path );
	}

	public function get_file_size( $filename ) {
		$jackmail_file_path = $this->get_jackmail_file_path();
		$file_path          = $jackmail_file_path . $filename;
		return @filesize( $file_path );
	}

	public function get_image_url( $image_id, $image_type, $preview = '' ) {
		$url = '../?jackmail_image=' . $image_id . '&jackmail_image_type=' . $image_type;
		if ( $preview !== '' ) {
			$url .= '&jackmail_image_preview=' . $preview . '&refresh=' . mt_rand( 0, 1500 );
		}
		return $url;
	}

	public function save_image_callback() {
		$this->check_auth();
		$json = array(
			'success' => false,
			'url'     => ''
		);
		if ( isset( $_POST['image'] ) ) {
			$image          = $this->request_text_data( $_POST['image'] );
			$save_image_url = $this->save_image( $image );
			if ( $save_image_url !== false ) {
				$json = array(
					'success' => true,
					'url'     => $save_image_url
				);
			}
		}
		wp_send_json( $json );
		die;
	}

	public function save_image( $image, $image_id = '' ) {
		$error = false;
		if ( $this->image_create_from_string_get_image_size_from_string_function_exists() ) {
			$pos = strpos( $image, 'base64,' );
			if ( $pos !== false ) {
				$image = substr( $image, $pos + 7 );
				$image = base64_decode( $image );
				$check = @imagecreatefromstring( $image );
				if ( ! $check ) {
					$error = true;
				}
				if ( $error === false ) {
					$size = @getimagesizefromstring( $image );
					if ( ! is_array( $size ) || ! isset( $size[0] ) || ! isset( $size[1] )
					     || ! isset( $size[2] ) || ! isset( $size[3] ) || ! isset( $size['bits'] ) || ! isset( $size['mime'] ) ) {
						$error = true;
					} else if ( substr( $size['mime'], 0, 6 ) !== 'image/' ) {
						$error = true;
					}
					if ( $error === false ) {
						if ( $image_id === '' ) {
							$image_id = $this->generate_guid();
						}
						if ( $image_id === '' ) {
							return false;
						}
						if ( $this->str_len( $image_id ) !== 22 ) {
							return true;
						} else {
							if ( $error === false ) {
								$image_type = substr( $size['mime'], 6 );
								if ( $image_type !== 'png' && $image_type !== 'jpeg' && $image_type !== 'gif'
								     && $image_type !== 'bmp' & $image_type !== 'tiff' ) {
									$error = true;
								}
								if ( $error === false ) {
									$write_file = $this->file_put_contents( $image_id . '.' . $image_type, $image );
									if ( $write_file !== false ) {
										return $this->get_image_url( $image_id, $image_type );
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	public function set_content_email( $type, $id, $preview, $content_email_json, $content_email_html, $content_email_txt ) {
		if ( $content_email_html !== '' ) {
			$content_email_html = $this->htmlentitiesencode( $content_email_html );
			$needles            = array(
				' src="data:image/',
				' src=\'data:image/'
			);
			$end_needle_quote1  = '"';
			$end_needle_quote2  = '\'';
			foreach ( $needles as $subkey => $needle ) {
				if ( $subkey === 0 ) {
					$end_needle = $end_needle_quote1;
				} else {
					$end_needle = $end_needle_quote2;
				}
				$strlen_needle = $this->str_len( $needle );
				$begin_diff    = 11;
				$last_pos      = 0;
				$positions     = array();
				while ( ( $last_pos = strpos( $content_email_html, $needle, $last_pos ) ) !== false ) {
					$positions[] = $last_pos;
					$last_pos    = $last_pos + $strlen_needle;
				}
				$positions = array_reverse( $positions );
				foreach ( $positions as $value ) {
					$begin          = $value + $strlen_needle - $begin_diff;
					$substr         = substr( $content_email_html, $begin );
					$end            = strpos( $substr, $end_needle );
					$image          = substr( $content_email_html, $begin, $end );
					$save_image_url = $this->save_image( $image );
					if ( $save_image_url !== false ) {
						$content_email_html = substr( $content_email_html, 0, $begin ) . $save_image_url . substr( $content_email_html, $begin + $end );
					}
				}
			}
		}
		$content_email_images = $this->get_content_email_images( $content_email_json, $content_email_html );
		$content_email_png    = $this->generate_content_email_default_preview(
			$type, $id, $preview, $content_email_json, $content_email_html, $content_email_txt
		);
		if ( $content_email_png !== false ) {
			$content_email_array = array(
				'content_email_json'   => $content_email_json,
				'content_email_html'   => $content_email_html,
				'content_email_txt'    => $content_email_txt,
				'content_email_images' => $content_email_images
			);
			return $content_email_array;
		}
		return false;
	}

	public function get_content_email_images( $content_email_json, $content_email_html ) {
		$content_email_images = array();
		if ( $content_email_json !== '' || $content_email_html !== '' ) {
			$content_email_images_url = array();
			if ( $content_email_json !== '' ) {
				$content          = $content_email_json;
				$needle_array     = array( '../?jackmail_image=' );
				$end_needle_array = array( '"' );
			} else {
				$content          = $content_email_html;
				$needle_array     = array( '"../?jackmail_image=', '\'../?jackmail_image=' );
				$end_needle_array = array( '"', '\'' );
			}
			foreach ( $needle_array as $key => $needle ) {
				$begin_diff    = 0;
				$strlen_needle = $this->str_len( $needle );
				$last_pos      = 0;
				$positions     = array();
				while ( ( $last_pos = strpos( $content, $needle, $last_pos ) ) !== false ) {
					$positions[] = $last_pos;
					$last_pos    = $last_pos + $strlen_needle;
				}
				foreach ( $positions as $value ) {
					$begin       = $value + $strlen_needle - $begin_diff;
					$substr      = substr( $content, $begin );
					$end         = strpos( $substr, $end_needle_array[ $key ] );
					$url         = substr( $content, $begin, $end );
					$url_explode = explode( '&jackmail_image_type=', $url );
					if ( count( $url_explode ) !== 2 ) {
						return false;
					}
					if ( ! in_array( $url, $content_email_images_url ) ) {
						$content_email_images[]     = array(
							'image_id'   => $url_explode[0],
							'image_type' => $url_explode[1]
						);
						$content_email_images_url[] = $url;
					}
				}
			}
		}
		return $this->json_encode( $content_email_images );
	}

	public function content_email_preview_url( $preview_name, $preview_type ) {
		return $this->get_image_url( $preview_name, 'png', $preview_type );
	}

	private function generate_content_email_preview( $preview, $content_email_json, $content_email_html, $content_email_txt ) {
		$write_file    = false;
		$content_email = '';
		if ( $content_email_html !== '' || $content_email_json !== '' ) {
			if ( $content_email_json !== '' ) {
				$content_email = $content_email_json;
				$json          = json_decode( $content_email, true );
				if ( isset( $json['workspace'] ) ) {
					if ( isset( $json['workspace']['structures'] ) && is_array( $json['workspace']['structures'] ) ) {
						foreach ( $json['workspace']['structures'] as $key1 => $structure ) {
							if ( isset( $structure['columns'] ) && is_array( $structure['columns'] ) ) {
								foreach ( $structure['columns'] as $key2 => $column ) {
									if ( isset( $column['contents'] ) && is_array( $column['contents'] ) ) {
										foreach ( $column['contents'] as $key3 => $content ) {
											if ( isset( $content['type'] ) ) {
												if ( $content['type'] === 'super' ) {
													$json['workspace']['structures'][ $key1 ]['columns'][ $key2 ]['contents'][ $key3 ] = array(
														'type'    => 'text',
														'id'      => 'cont-text-' . mt_rand( 100000, 999999 ),
														'content' => '<p style="padding-top:15px;padding-bottom:15px;"><img src="' . get_home_url() . '?jackmail_image=scenario_example-IMG&jackmail_image_type=png" alt=""/></p>'
													);
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$content_email = $this->json_encode( $json );
				$content_email = str_replace( '..\/?jackmail_image=', get_home_url() . '?jackmail_image=', $content_email );
				if ( isset( $json['customTags'] ) ) {
					$json_fields = $json['customTags'];
					if ( is_array( $json_fields ) ) {
						foreach ( $json_fields as $json_field ) {
							if ( isset( $json_field['id'], $json_field['name'] ) ) {
								$content_email = str_replace( '((' . $json_field['name'] . '))', '(&shy;(' . $json_field['name'] . ')&shy;)', $content_email );
							}
						}
					}
				}
			} else {
				$content_email = $content_email_html;
				$content_email = str_replace( '../?jackmail_image=', get_home_url() . '?jackmail_image=', $content_email );
			}
			
		} else if ( $content_email_txt !== '' ) {
			$content_email = $content_email_txt;
		}
		if ( $content_email !== '' ) {
			$url = $this->get_jackmail_url_thumbnail();
			if ( $content_email_json !== '' ) {
				
				$body = array(
					'wizardContent' => $content_email,
					'width'         => 240
				);
			} else if ( $content_email_html !== '' ) {
				$body = array(
					'htmlContent' => $content_email,
					'width'       => 240
				);
			} else {
				$body = array(
					'textContent' => $content_email,
					'width'       => 240
				);
			}
			$headers  = array(
				'content-type' => 'application/json'
			);
			$timeout  = 30;
			$response = $this->remote_post_retry( $url, $headers, $body, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['response'] ) ) {
					if ( isset( $response['response']['message'] ) ) {
						if ( $response['response']['message'] === 'OK' ) {
							if ( isset( $response['body'] ) ) {
								$content_email_png_file = $response['body'];
								$write_file             = $this->file_put_contents( $preview . '.png', trim( $content_email_png_file ) );
							}
						}
					}
				}
			}
		}
		if ( $write_file === false ) {
			$no_preview_file_path   = plugin_dir_path( __FILE__ ) . 'img/no_preview.png';
			$content_email_png_file = @file_get_contents( $no_preview_file_path );
			if ( $content_email_png_file !== false ) {
				$write_file = $this->file_put_contents( $preview . '.png', trim( $content_email_png_file ) );
				return $write_file;
			}
		}
		return $write_file;
	}

	private function generate_content_email_default_preview( $type, $id, $preview, $content_email_json, $content_email_html, $content_email_txt ) {
		global $wpdb;
		$regenerate_preview = true;
		if ( ( $type === 'template' || $type === 'campaign' || $type === 'scenario' ) && $id !== '0' ) {
			if ( $type === 'template' ) {
				$table = 'jackmail_templates';
			} else if ( $type === 'campaign' ) {
				$table = 'jackmail_campaigns';
			} else {
				$table = 'jackmail_scenarios';
			}
			$sql                   = "
			SELECT `content_email_json`, `content_email_html`, `content_email_txt`
			FROM `{$wpdb->prefix}{$table}`
			WHERE `id` = %s";
			$current_content_email = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );
			if ( isset( $current_content_email->content_email_json, $current_content_email->content_email_html, $current_content_email->content_email_txt ) ) {
				$current_content_email_json = $current_content_email->content_email_json;
				$current_content_email_html = $current_content_email->content_email_html;
				$current_content_email_txt  = $current_content_email->content_email_txt;
				$current_content_email_json = str_replace( ' ', '', $current_content_email_json );
				$current_content_email_json = json_decode( $current_content_email_json, true );
				if ( isset( $current_content_email_json['links'] ) ) {
					unset( $current_content_email_json['links'] );
				}
				$current_content_email_json = $this->json_encode( $current_content_email_json );

				$new_content_email_json = $content_email_json;
				$new_content_email_json = str_replace( ' ', '', $new_content_email_json );
				$new_content_email_json = json_decode( $new_content_email_json, true );
				if ( isset( $new_content_email_json['links'] ) ) {
					unset( $new_content_email_json['links'] );
				}
				$new_content_email_json = $this->json_encode( $new_content_email_json );

				if ( $current_content_email_json === $new_content_email_json && $current_content_email_html === $content_email_html
				     && $current_content_email_txt === $content_email_txt ) {
					$regenerate_preview = false;
				}
			}
		}
		if ( $regenerate_preview ) {
			$no_preview_file_path   = plugin_dir_path( __FILE__ ) . 'img/no_preview.png';
			$content_email_png_file = @file_get_contents( $no_preview_file_path );
			if ( $content_email_png_file !== false ) {
				$write_file = $this->file_put_contents( $preview . '.png', trim( $content_email_png_file ) );
				return $write_file;
			}
			return false;
		}
		return true;
	}

	public function duplicate_preview( $old_preview, $new_preview ) {
		$jackmail_file_path         = $this->get_jackmail_file_path();
		$jackmail_file_path_preview = $jackmail_file_path . $old_preview . '.png';
		$content_email_png_file     = @file_get_contents( $jackmail_file_path_preview );
		if ( $content_email_png_file === false ) {
			$no_preview_file_path   = plugin_dir_path( __FILE__ ) . 'img/no_preview.png';
			$content_email_png_file = @file_get_contents( $no_preview_file_path );
		}

		if ( $content_email_png_file !== false ) {
			$write_file = $this->file_put_contents( $new_preview . '.png', trim( $content_email_png_file ) );
			return $write_file;
		}
		return false;
	}

	public function get_campaign_id_list( $id_list, $id_campaign ) {
		$unique_id = get_option( 'jackmail_id' );
		if ( $id_campaign !== '' ) {
			return 'C' . $id_campaign . $unique_id;
		} else {
			return $id_list . $unique_id;
		}
	}

	public function cron_domain() {
		$this->get_jackmail_update_available();
		$this->domain_is_valid();
	}

	public function domain_is_valid() {
		$subdomain = get_option( 'jackmail_domain_sub' );
		if ( $subdomain !== '' ) {
			$url      = $this->get_jackmail_url_domain() . 'customers?search=' . $subdomain;
			$headers  = array(
				'content-type' => 'application/json',
				'token'        => $this->get_account_token(),
				'x-auth-token' => $this->get_account_token(),
				'accountId'    => $this->get_account_id()
			);
			$timeout  = 30;
			$response = $this->remote_get_retry( $url, $headers, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						if ( isset( $results['customers'] ) ) {
							if ( is_array( $results['customers'] ) ) {
								foreach ( $results['customers'] as $customer ) {
									if ( isset( $customer['name'], $customer['state'] ) ) {
										if ( $customer['name'] === $subdomain ) {
											if ( $customer['state'] === 'STATE_VALID' ) {
												return true;
											}
											return false;
										}
									}
								}
							}
						}
					}
				}
			}
			$result = $this->domain_create_delegation( $subdomain );
			if ( isset( $result['is_valid'] ) ) {
				return $result['is_valid'];
			}
			return false;
		}
		return true;
	}

	public function domain_create_delegation( $subdomain ) {
		$result  = array(
			'success'  => false,
			'is_valid' => false
		);
		$url     = $this->get_jackmail_url_domain() . 'customers?search=' . $subdomain;
		$headers = array(
			'content-type' => 'application/json',
			'token'        => $this->get_account_token(),
			'x-auth-token' => $this->get_account_token(),
			'accountId'    => $this->get_account_id()
		);
		$body    = array(
			'name'     => $subdomain,
			'type'     => 'DELEGATION',
			'txtToken' => $this->get_domain_txt( $subdomain )
		);
		$timeout = 30;
		$this->remote_post_retry( $url, $headers, $body, $timeout );
		$response = $this->remote_get_retry( $url, $headers, $timeout );
		if ( is_array( $response ) ) {
			if ( isset( $response['body'] ) ) {
				$results = json_decode( $response['body'], true );
				if ( is_array( $results ) ) {
					if ( isset( $results['customers'] ) ) {
						if ( is_array( $results['customers'] ) ) {
							foreach ( $results['customers'] as $customer ) {
								if ( isset( $customer['name'], $customer['state'] ) ) {
									if ( $customer['name'] === $subdomain ) {
										update_option( 'jackmail_domain_sub', $subdomain );
										$result['success'] = true;
										if ( $customer['state'] === 'STATE_VALID' ) {
											$result['is_valid'] = true;
										}
										return $result;
									}
								}
							}
						}
					}
				}
			}
		}
		return $result;
	}

	public function get_domain_txt( $subdomain ) {
		return base64_encode( sha1( 'txt' . $this->get_account_id() . 'jackmail' . $subdomain . 'subdomain', false ) );
	}

	public function get_blacklist_types() {
		return array(
			'bounces'      => 3,
			'complaints'   => 2,
			'unsubscribes' => 1
		);
	}

	public function emailbuilder_installed() {
		if ( get_option( 'jackmail_emailbuilder' ) === '1' ) {
			return true;
		}
		return false;
	}

	public function display_notice_lte_ie9( $content ) {
		$this->display_notice( $content, '', '', '', false, true );
	}

	public function display_notice_noscript( $content ) {
		$this->display_notice( $content, '', '', '', true, false );
	}

	public function display_notice( $content, $class = '', $id = '', $onclick = '', $no_script = false, $lte_ie9 = false, $height = '' ) {
		$notice_class = 'jackmail_notice';
		if ( $class !== '' ) {
			$notice_class = $notice_class . ' ' . $class;
		}
		if ( $height !== '' ) {
			if ( $height > 100 ) {
				$notice_class .= ' jackmail_big_notice';
			}
		}
		$notice_class = ' class="' . $notice_class . '"';
		$notice_id    = '';
		if ( $id !== '' ) {
			$notice_id = ' id="' . $id . '"';
		}
		$notice_onclick = '';
		if ( $onclick !== '' ) {
			$notice_onclick = ' onclick="' . $onclick . '"';
		}
		$notice_height = '';
		if ( $height !== '' ) {
			$notice_height = ' style="height:' . (int) $height . 'px' . '"';
		}
		$notice = '
		<div class="jackmail_notice_container"' . $notice_height . '>
			<div class="jackmail_notice_subcontainer">
				<div' . $notice_class . $notice_id . $notice_onclick . '>
					<span onclick="jQuery( this ).parent().parent().parent().hide()" class="dashicons dashicons-dismiss"></span>
					<p>' . $content . '</p>
				</div>
			</div>
		</div>';
		if ( $no_script ) {
			$notice = '<noscript>' . $notice . '</noscript>';
		}
		if ( $lte_ie9 ) {
			$notice = '<!--[if lte IE 9]>' . $notice . '<![endif]-->';
		}
		echo $notice;
	}

	public function premium_notification( $campaigns_condition = false ) {
		$install_date_gmt_sql                 = get_option( 'jackmail_install_date' );
		$current_time_gmt_sql                 = $this->get_current_time_gmt_sql();
		$check_last_hide                      = false;
		$refresh_display_premium_notification = false;
		if ( get_option( 'jackmail_premium_notification' ) === '1' ) {
			if ( $campaigns_condition ) {
				if ( strtotime( $current_time_gmt_sql ) - strtotime( $install_date_gmt_sql ) < 86400 ) {
					$check_last_hide = true;
				}
			}
			if ( ! $check_last_hide ) {
				if ( strtotime( $current_time_gmt_sql ) - strtotime( $install_date_gmt_sql ) >= 86400 ) {
					$check_last_hide = true;
				}
			}
			if ( $check_last_hide ) {
				$notification_last_hide_gmt_sql = get_option( 'jackmail_display_premium_notification_last_hide' );
				if ( $notification_last_hide_gmt_sql === '' ) {
					$refresh_display_premium_notification = true;
				} else {
					if ( strtotime( $current_time_gmt_sql ) - strtotime( $notification_last_hide_gmt_sql ) > 604800 ) {
						$refresh_display_premium_notification = true;
					}
				}
			}
		}
		$credits_available = $this->get_credits_available( true );
		if ( $credits_available !== false ) {
			$freemium = false;
			if ( isset( $credits_available['subscription_type'] ) ) {
				if ( $credits_available['subscription_type'] === 'FREE' ) {
					$freemium = true;
				}
				if ( $refresh_display_premium_notification ) {
					if ( $freemium ) {
						update_option( 'jackmail_display_premium_menu', '1' );
						update_option( 'jackmail_display_premium_notification', '1' );
					} else {
						update_option( 'jackmail_display_premium_menu', '0' );
						update_option( 'jackmail_display_premium_notification', '0' );
						update_option( 'jackmail_display_premium_notification_last_hide', '' );
					}
				} else {
					if ( $freemium ) {
						update_option( 'jackmail_display_premium_menu', '1' );
					} else {
						update_option( 'jackmail_display_premium_menu', '0' );
					}
				}
			}
		}
	}

	public function get_jackmail_plugins() {
		return $this->explode_data( get_option( 'jackmail_plugins' ) );
	}

	public function boolval( $val ) {
		if ( $val === '1' || $val === true || $val === 'true' || $val === 1 ) {
			return true;
		}
		return false;
	}

	public function grid_limit() {
		return 100;
	}

	public function export_send_limit() {
		return 3000;
	}

	public function openssl_random_pseudo_bytes_function_exists() {
		return function_exists( 'openssl_random_pseudo_bytes' );
	}

	public function gzdecode_gzencode_function_exists() {
		return function_exists( 'gzdecode' ) && function_exists( 'gzencode' );
	}

	public function base64_decode_base64_encode_function_exists() {
		return function_exists( 'base64_decode' ) && function_exists( 'base64_encode' );
	}

	public function json_encode_json_decode_function_exists() {
		return function_exists( 'json_encode' ) && function_exists( 'json_decode' );
	}

	public function image_create_from_string_get_image_size_from_string_function_exists() {
		return function_exists( 'imagecreatefromstring' ) && function_exists( 'getimagesizefromstring' );
	}

	public function mb_string_function_exists() {
		return function_exists( 'mb_strtoupper' ) && function_exists( 'mb_strtolower' )
		       && function_exists( 'mb_strlen' );
	}

	public function str_to_upper( $str ) {
		if ( function_exists( 'mb_strtoupper' ) ) {
			return mb_strtoupper( $str );
		}
		return strtoupper( $str );
	}

	public function str_to_lower( $str ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return strtolower( $str );
		}
		return strtolower( $str );
	}

	public function str_len( $str ) {
		if ( function_exists( 'mb_strlen' ) ) {
			return mb_strlen( $str );
		}
		return strlen( $str );
	}

	public function request_stripslashes( $data ) {
		return stripslashes_deep( $data );
	}

	public function request_text_data( $data ) {
		$data = stripslashes_deep( sanitize_text_field( $data ) );
		$data = str_replace( '&lt;', '<', $data );
		$data = str_replace( '&gt;', '>', $data );
		$data = str_replace( '&amp;', '&', $data );
		return $data;
	}

	public function request_textarea_data( $data ) {
		if ( function_exists( 'sanitize_textarea_field' ) ) {
			$data = sanitize_textarea_field( $data );
		} else {
			$data = str_replace( "\n", '||||||||||', $data );
			$data = str_replace( "\r\n", '||||||||||', $data );
			$data = sanitize_text_field( $data );
			$data = str_replace( '||||||||||', "\n", $data );
		}
		$data = str_replace( '&lt;', '<', $data );
		$data = str_replace( '&gt;', '>', $data );
		$data = str_replace( '&amp;', '&', $data );
		return stripslashes_deep( $data );
	}

	public function request_email_data( $data ) {
		return stripslashes_deep( sanitize_email( $data ) );
	}

	public function get_custom_posts_categories_json() {
		$result     = array();
		$categories = $this->get_custom_posts_categories_query();
		foreach ( $categories as $category ) {
			if ( isset( $category->name ) && isset( $category->label ) ) {
				$result[] = array(
					'id'    => $category->name,
					'label' => $category->label
				);
			}
		}
		return $result;
	}

	public function get_custom_posts_categories_array() {
		$result     = array();
		$categories = $this->get_custom_posts_categories_query();
		foreach ( $categories as $category ) {
			if ( isset( $category->name ) ) {
				$result[] = $category->name;
			}
		}
		return $result;
	}

	private function get_custom_posts_categories_query() {
		$args = array(
			'public' => true
		);
		return get_post_types( $args, 'objects' );
	}

	public function is_statistics_v2() {
		if ( get_option( 'jackmail_statistics_version', '' ) === 'v2' ) {
			return true;
		}
		return false;
	}

	public function get_jackmail_url_global() {
		return 'https://services.jackmail.com/';
	}

	public function get_jackmail_url_api() {
		return $this->get_jackmail_url_global() . 'routing/';
	}

	public function get_jackmail_url_identity() {
		return $this->get_jackmail_url_global() . 'identity/';
	}

	public function get_jackmail_url_thumbnail() {
		return $this->get_jackmail_url_global() . 'thumbnail';
		
	}

	public function get_jackmail_url_analytics() {
		$version = get_option( 'jackmail_statistics_version', '' );
		if ( $version !== '' ) {
			$version .= '/';
		}
		
		return $this->get_jackmail_url_global() . 'analytics/stats/' . $version;
	}

	public function get_jackmail_url_domain() {
		return $this->get_jackmail_url_global() . 'domains/v3/';
	}

	public function get_jackmail_url_image_library() {
		return 'http://img.jackmail.com/';
	}

	public function get_jackmail_url_ws() {
		return 'https://www.jackmail.com/ws/';
	}

	public function get_jackmail_url_emailbuilder() {
		
		return 'https://emailbuilder.jackmail-cdn.com/' . $this->get_emailbuilder_version() . '/';
	}

	public function get_emailbuilder_version() {
		$min_emailbuilder_version = '1.1.4';
		$emailbuilder_version     = get_option( 'jackmail_emailbuilder_version', $min_emailbuilder_version );
		if ( version_compare( $emailbuilder_version, $min_emailbuilder_version, '<' ) ) {
			$emailbuilder_version = $min_emailbuilder_version;
		}
		return $emailbuilder_version;
	}

	public function get_jackmail_url_img() {
		return 'https://eb-static.jackmail-cdn.com/img/';
	}

	public function get_jackmail_url_doc() {
		return 'https://docs.jackmail.com/';
	}

	private function call_ws( $headers ) {
		if ( $this->check_ws( $headers ) ) {
			if ( $this->is_authenticated() ) {
				return true;
			}
			return false;
		}
		return true;
	}

	private function check_ws( $headers ) {
		if ( isset( $headers['token'] ) || isset( $headers['x-auth-token'] ) ) {
			return true;
		}
		return false;
	}

	private function remote_body( $body ) {
		if ( is_array( $body ) ) {
			if ( isset( $body['beginInterval'] ) && isset( $body['endInterval'] ) ) {
				if ( $this->is_statistics_v2() ) {
					$body['beginInterval'] = substr( $body['beginInterval'], 0, 13 );
					$body['endInterval']   = substr( $body['endInterval'], 0, 13 );
				}
			}
			return $this->json_encode( $body );
		}
		return $body;
	}

	public function remote_post_retry( $url, Array $headers, $body, $timeout ) {
		if ( ! $this->call_ws( $headers ) ) {
			return array();
		}
		$body       = $this->remote_body( $body );
		$response   = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => $body,
			'timeout' => $timeout
		) );
		$check_data = $this->check_data( $headers, $response );
		if ( $check_data === 'ok' ) {
			return $response;
		} else if ( $check_data === 'retry' ) {
			$response = wp_remote_post( $url, array(
				'headers' => $headers,
				'body'    => $body,
				'timeout' => $timeout
			) );
			if ( $this->check_data( $headers, $response ) === 'ok' ) {
				return $response;
			}
		}
		return array();
	}

	public function remote_post_retry_code( $url, Array $headers, $body, $timeout ) {
		if ( ! $this->call_ws( $headers ) ) {
			return 403;
		}
		$body       = $this->remote_body( $body );
		$response   = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => $body,
			'timeout' => $timeout
		) );
		$check_data = $this->check_data( $headers, $response );
		$code       = 'ERROR';
		if ( is_array( $response ) ) {
			if ( isset( $response['response'] ) ) {
				if ( is_array( $response['response'] ) ) {
					if ( isset( $response['response']['code'] ) ) {
						$code = $response['response']['code'];
					}
				}
			}
		}
		if ( $check_data === 'retry' ) {
			$response = wp_remote_post( $url, array(
				'headers' => $headers,
				'body'    => $body,
				'timeout' => $timeout
			) );
			if ( is_array( $response ) ) {
				if ( isset( $response['response'] ) ) {
					if ( is_array( $response['response'] ) ) {
						if ( isset( $response['response']['code'] ) ) {
							$code = $response['response']['code'];
						}
					}
				}
			}
		}
		return $code;
	}

	public function remote_get_retry( $url, Array $headers, $timeout ) {
		if ( ! $this->call_ws( $headers ) ) {
			return array();
		}
		$response   = wp_remote_get( $url, array(
			'headers' => $headers,
			'timeout' => $timeout
		) );
		$check_data = $this->check_data( $headers, $response );
		if ( $check_data === 'ok' ) {
			return $response;
		} else if ( $check_data === 'retry' ) {
			$response = wp_remote_get( $url, array(
				'headers' => $headers,
				'timeout' => $timeout
			) );
			if ( $this->check_data( $headers, $response ) === 'ok' ) {
				return $response;
			}
		}
		return array();
	}

	public function remote_post( $url, Array $headers, $body, $timeout ) {
		if ( ! $this->call_ws( $headers ) ) {
			return array();
		}
		$body     = $this->remote_body( $body );
		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => $body,
			'timeout' => $timeout
		) );
		if ( $this->check_data( $headers, $response ) === 'ok' ) {
			return $response;
		}
		return array();
	}

	public function remote_get( $url, Array $headers, $timeout ) {
		if ( ! $this->call_ws( $headers ) ) {
			return array();
		}
		$response = wp_remote_get( $url, array(
			'headers' => $headers,
			'timeout' => $timeout
		) );
		if ( $this->check_data( $headers, $response ) === 'ok' ) {
			return $response;
		}
		return array();
	}

	private function check_data( $headers, $response ) {
		$result   = 'error';
		$check_ws = $this->check_ws( $headers );
		if ( is_array( $response ) ) {
			if ( isset( $response['response'] ) ) {
				if ( is_array( $response['response'] ) ) {
					if ( isset( $response['response']['code'], $response['body'] ) ) {
						if ( $check_ws ) {
							$result = 'ok';
							if ( $response['response']['code'] === 403 ) {
								
								if ( strpos( $response['body'], 'userId' ) ) {
									$this->update_user_id();
									$result = 'retry';
								} else {
									if ( get_option( 'jackmail_authentification_failed' ) === '0' ) {
										
										update_option( 'jackmail_authentification_failed', '1' );
									}
									if ( $this->generate_token() ) {
										$result = 'retry';
									} else {
										$result = 'no_retry';
									}
								}
							}
						} else {
							$result = 'ok';
						}
					}
				}
			}
		}
		if ( $check_ws && ( $result === 'ok' || $result === 'retry' ) && get_option( 'jackmail_authentification_failed' ) === '1' ) {
			update_option( 'jackmail_authentification_failed', '0' );
		}
		return $result;
	}

	private function get_account_infos_ws() {
		$url     = $this->get_jackmail_url_identity() . 'accounts/' . $this->get_account_id();
		$headers = array(
			'content-type' => 'application/json',
			'token'        => $this->get_account_token(),
			'x-auth-token' => $this->get_account_token()
		);
		$timeout = 30;
		return $this->remote_get_retry( $url, $headers, $timeout );

	}

	private function update_user_id() {
		$response = $this->get_account_infos_ws();
		if ( is_array( $response ) ) {
			if ( isset( $response['body'] ) ) {
				$results = json_decode( $response['body'], true );
				if ( is_array( $results ) ) {
					if ( isset( $results['roles'] ) ) {
						if ( is_array( $results['roles'] ) ) {
							if ( isset( $results['roles'][0] ) ) {
								if ( isset( $results['roles'][0]['user'] ) ) {
									if ( isset( $results['roles'][0]['user']['email'] ) ) {
										$this->set_user_id( $results['roles'][0]['user']['email'] );
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function get_credits_available( $full_data = false ) {
		if ( $this->is_authenticated() ) {
			$response = $this->get_account_infos_ws();
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						if ( isset( $results['credits'] ) ) {
							$credits_array = $results['credits'];
							if ( is_array( $credits_array ) ) {
								foreach ( $credits_array as $credit_array ) {
									if ( isset( $credit_array['creditType'], $credit_array['count'],
										$credit_array['subscriptionType'], $credit_array['expirationDate'] ) ) {
										$nb_credits          = $credit_array['count'];
										$expiration_date_gmt = $this->get_mysql_date( $credit_array['expirationDate'] );
										$current_date_gmt    = $this->get_current_time_gmt_sql();
										if ( strtotime( $current_date_gmt ) - strtotime( $expiration_date_gmt ) > 0 ) {
											$nb_credits = 0;
										}
										$product_key = '';
										if ( isset( $credit_array['productKey'] ) ) {
											$product_key = $credit_array['productKey'];
										}
										if ( $credit_array['creditType'] === 'EMAIL' ) {
											if ( $full_data ) {
												return array(
													'nb_credits'        => $nb_credits,
													'subscription_type' => $credit_array['subscriptionType'],
													'product_key'       => $product_key
												);
											} else {
												return $nb_credits;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	public function check_bloom_plugin_found() {
		if ( get_option( 'et_bloom_options' ) !== false ) {
			return true;
		}
		return false;
	}

	public function check_formidableforms_plugin_found() {
		if ( $this->check_table_exists( 'frm_forms' ) ) {
			$columns = $this->get_table_columns( 'frm_forms' );
			if ( in_array( 'id', $columns ) && in_array( 'name', $columns ) ) {
				if ( $this->check_table_exists( 'frm_fields' ) ) {
					$columns = $this->get_table_columns( 'frm_fields' );
					if ( in_array( 'id', $columns ) && in_array( 'name', $columns ) && in_array( 'type', $columns ) ) {
						if ( $this->check_table_exists( 'frm_item_metas' ) ) {
							$columns = $this->get_table_columns( 'frm_item_metas' );
							if ( in_array( 'meta_value', $columns ) && in_array( 'field_id', $columns )
							     && in_array( 'item_id', $columns ) ) {
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}

	public function check_gravityforms_plugin_found() {
		if ( $this->check_table_exists( 'rg_form' ) ) {
			$columns = $this->get_table_columns( 'rg_form' );
			if ( in_array( 'id', $columns ) && in_array( 'title', $columns ) && in_array( 'is_active', $columns ) ) {
				if ( $this->check_table_exists( 'rg_form_meta' ) ) {
					$columns = $this->get_table_columns( 'rg_form_meta' );
					if ( in_array( 'form_id', $columns ) && in_array( 'display_meta', $columns ) ) {
						if ( $this->check_table_exists( 'rg_lead_detail' ) ) {
							$columns = $this->get_table_columns( 'rg_lead_detail' );
							if ( in_array( 'form_id', $columns ) && in_array( 'field_number', $columns )
							     && in_array( 'value', $columns ) ) {
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}

	public function check_mailpoet2_plugin_found() {
		if ( $this->check_table_exists( 'wysija_list' ) ) {
			$columns = $this->get_table_columns( 'wysija_list' );
			if ( in_array( 'list_id', $columns ) && in_array( 'name', $columns ) && in_array( 'is_enabled', $columns ) ) {
				if ( $this->check_table_exists( 'wysija_user' ) ) {
					$columns = $this->get_table_columns( 'wysija_user' );
					if ( in_array( 'email', $columns ) && in_array( 'firstname', $columns )
					     && in_array( 'lastname', $columns ) && in_array( 'status', $columns ) ) {
						if ( $this->check_table_exists( 'wysija_user_list' ) ) {
							$columns = $this->get_table_columns( 'wysija_user_list' );
							if ( in_array( 'list_id', $columns ) && in_array( 'user_id', $columns ) ) {
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}

	public function check_mailpoet3_plugin_found() {
		if ( $this->check_table_exists( 'mailpoet_segments' ) ) {
			$columns = $this->get_table_columns( 'mailpoet_segments' );
			if ( in_array( 'id', $columns ) && in_array( 'name', $columns )
			     && in_array( 'type', $columns ) && in_array( 'deleted_at', $columns ) ) {
				if ( $this->check_table_exists( 'mailpoet_subscribers' ) ) {
					$columns = $this->get_table_columns( 'mailpoet_subscribers' );
					if ( in_array( 'email', $columns ) && in_array( 'first_name', $columns )
					     && in_array( 'last_name', $columns ) && in_array( 'status', $columns ) ) {
						if ( $this->check_table_exists( 'mailpoet_subscriber_segment' ) ) {
							$columns = $this->get_table_columns( 'mailpoet_subscriber_segment' );
							if ( in_array( 'subscriber_id', $columns ) && in_array( 'segment_id', $columns )
							     && in_array( 'status', $columns ) ) {
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}

	public function check_ninjaforms_plugin_found() {
		if ( $this->check_table_exists( 'nf3_forms' ) ) {
			$columns = $this->get_table_columns( 'nf3_forms' );
			if ( in_array( 'id', $columns ) && in_array( 'title', $columns ) ) {
				if ( $this->check_table_exists( 'nf3_fields' ) ) {
					$columns = $this->get_table_columns( 'nf3_fields' );
					if ( in_array( 'id', $columns ) && in_array( 'label', $columns ) && in_array( 'type', $columns ) ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	public function check_popupbysupsystic_plugin_found() {
		if ( $this->check_table_exists( 'pps_popup' ) ) {
			$columns = $this->get_table_columns( 'pps_popup' );
			if ( in_array( 'original_id', $columns ) && in_array( 'id', $columns ) && in_array( 'label', $columns ) ) {
				if ( $this->check_table_exists( 'pps_subscribers' ) ) {
					$columns = $this->get_table_columns( 'pps_subscribers' );
					if ( in_array( 'username', $columns ) && in_array( 'email', $columns )
					     && in_array( 'activated', $columns ) && in_array( 'popup_id', $columns ) ) {
						return true;
					}
				}
			}
		}
		return false;
	}

	public function check_woo_carts_plugin_found() {
		if ( $this->check_table_exists( 'ac_abandoned_cart_history_lite' ) ) {
			$columns = $this->get_table_columns( 'ac_abandoned_cart_history_lite' );
			if ( in_array( 'user_id', $columns ) && in_array( 'abandoned_cart_time', $columns ) ) {
				return true;
			}
		}
		return false;
	}

	public function get_woocommerce_actived() {
		$plugins = get_option( 'active_plugins' );
		if ( in_array( 'woocommerce/woocommerce.php', $plugins ) ) {
			$actual_plugins_array = $this->get_jackmail_plugins();
			if ( in_array( 'woocommerce', $actual_plugins_array ) ) {
				return true;
			}
		}
		return false;
	}

	public function content_email_json_link_tracking( $content_email_json, $link_tracking ) {
		if ( $link_tracking ) {
			$content_email_json = str_replace( '"isTracked":false', '"isTracked":true', $content_email_json );
			$content_email_json = str_replace( '"linkTracking":false', '"linkTracking":true', $content_email_json );
		} else {
			$content_email_json = str_replace( '"isTracked":true', '"isTracked":false', $content_email_json );
			$content_email_json = str_replace( '"linkTracking":true', '"linkTracking":false', $content_email_json );
		}
		return $content_email_json;
	}

	public function get_gallery_template_json( $gallery_id, $link_tracking ) {
		if ( $this->emailbuilder_installed() ) {
			$url      = $this->get_jackmail_url_ws() . 'gallery.php?product=jackmail&id=' . $gallery_id;
			$headers  = array();
			$timeout  = 30;
			$response = $this->remote_get( $url, $headers, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$images_valids      = true;
					$content_email_json = $response['body'];
					$needle             = '"imageSrc":"';
					$end_needle         = '"';
					$strlen_needle      = $this->str_len( $needle );
					$begin_diff         = $strlen_needle - 12;
					$last_pos           = 0;
					$positions          = array();
					while ( ( $last_pos = strpos( $content_email_json, $needle, $last_pos ) ) !== false ) {
						$positions[] = $last_pos;
						$last_pos    = $last_pos + $strlen_needle;
					}
					$positions = array_reverse( $positions );
					foreach ( $positions as $value ) {
						$begin  = $value + $strlen_needle - $begin_diff;
						$substr = substr( $content_email_json, $begin );
						$end    = strpos( $substr, $end_needle );
						$url    = substr( $content_email_json, $begin, $end );
						if ( strpos( $url, '/assets/' ) === false ) {
							if ( strpos( $url, '.png' ) !== false || strpos( $url, '.jpg' ) !== false
							     || strpos( $url, '.jpeg' ) !== false || strpos( $url, '.gif' ) !== false
							     || strpos( $url, '.bmp' ) !== false || strpos( $url, '.tiff' ) !== false ) {
								$image_id        = pathinfo( basename( $url ), PATHINFO_FILENAME );
								$image_extension = pathinfo( basename( $url ), PATHINFO_EXTENSION );
								if ( ! $this->file_exists( $image_id . '.' . $image_extension ) ) {
									$response = $this->remote_get( $url, $headers, $timeout );
									if ( is_array( $response ) ) {
										if ( isset( $response['body'] ) ) {
											$image          = 'base64,' . base64_encode( $response['body'] );
											$save_image_url = $this->save_image( $image, $image_id );
											if ( $save_image_url !== false ) {
												$content_email_json = substr( $content_email_json, 0, $begin ) . $save_image_url . substr( $content_email_json, $begin + $end );
												continue;
											}
										}
										$images_valids = false;
										break;
									}
								} else {
									$save_image_url     = $this->get_image_url( $image_id, $image_extension );
									$content_email_json = substr( $content_email_json, 0, $begin ) . $save_image_url . substr( $content_email_json, $begin + $end );
								}
							}
						}
					}
					if ( $images_valids === true ) {
						return $this->content_email_json_link_tracking( $content_email_json, $link_tracking );
					}
				}
			}
		}
		return '';
	}

	public function generate_token() {
		$url      = $this->get_jackmail_url_identity() . 'authenticate';
		$headers  = array(
			'content-type' => 'application/json'
		);
		$body     = array(
			'token' => $this->get_account_token()
		);
		$timeout  = 30;
		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => $this->json_encode( $body ),
			'timeout' => $timeout
		) );
		if ( is_array( $response ) ) {
			if ( isset( $response['response'] ) ) {
				if ( is_array( $response['response'] ) ) {
					if ( isset( $response['response']['code'] ) ) {
						if ( $response['response']['code'] === 200 ) {
							if ( isset( $response['body'] ) ) {
								$data = json_decode( $response['body'], true );
								if ( isset( $data['token'] ) ) {
									$this->set_account_token( $data['token'] );
									return true;
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	public function get_account_info() {
		if ( ! $this->is_authenticated() ) {
			$current_user = wp_get_current_user();
			$email        = '';
			$firstname    = '';
			$lastname     = '';
			if ( isset( $current_user->user_email, $current_user->user_firstname, $current_user->user_lastname ) ) {
				$email     = $current_user->user_email;
				$firstname = $current_user->user_firstname;
				$lastname  = $current_user->user_lastname;
			}
		} else {
			$email     = $this->get_user_id();
			$firstname = $this->get_firstname();
			$lastname  = $this->get_lastname();
		}
		return array(
			'email'     => $email,
			'firstname' => $firstname,
			'lastname'  => $lastname
		);
	}

	public function get_sender_name_and_email() {
		$account_info = $this->get_account_info();
		$sender_email = '';
		$sender_name  = '';
		if ( isset( $account_info['email'], $account_info['firstname'], $account_info['lastname'] ) ) {
			$sender_email = $account_info['email'];
			if ( $account_info['firstname'] !== '' && $account_info['lastname'] !== '' ) {
				$sender_name = $account_info['firstname'] . ' ' . $account_info['lastname'];
			}
		}
		return array(
			'sender_email' => $sender_email,
			'sender_name'  => $sender_name
		);
	}

	public function check_or_create_list( $plugin, $form_id, $list_name, $fields = array() ) {
		global $wpdb;
		$sql    = "
		SELECT `id`, `name`
		FROM `{$wpdb->prefix}jackmail_lists`
		WHERE `type` = %s";
		$type   = array(
			'name' => $plugin,
			'id'   => $form_id
		);
		$type   = $this->implode_data( $type );
		$result = $wpdb->get_row( $wpdb->prepare( $sql, $type ) );
		if ( isset( $result->id, $result->name ) ) {
			if ( $result->name !== $list_name ) {
				$this->update_list( array(
					'name' => $list_name
				), array(
					'id' => $result->id
				) );
			}
			return $result->id;
		} else {
			$nb_contacts = '0';
			if ( $this->is_dynamic_nb_contacts( $plugin ) ) {
				$nb_contacts = '-1';
			}
			return $this->create_list( $list_name, $nb_contacts, $type, $fields );
		}
	}

	public function create_list( $list_name = '', $nb_contacts = '0', $type = '', $fields = array() ) {
		$current_date_gmt = $this->get_current_time_gmt_sql();
		if ( $list_name === '' ) {
			$list_name = __( 'List', 'jackmail-newsletters' ) . ' ' . $this->get_current_time_sql();
		}
		$id_list = $this->insert_list( array(
			'name'               => $list_name,
			'fields'             => $this->implode_fields( $fields ),
			'nb_contacts'        => $nb_contacts,
			'nb_contacts_valids' => $nb_contacts,
			'created_date_gmt'   => $current_date_gmt,
			'updated_date_gmt'   => $current_date_gmt,
			'type'               => $type,
			'connector_key'      => $this->get_random()
		) );
		if ( $id_list !== false ) {
			if ( $this->create_list_table( $id_list, count( $fields ) ) !== false ) {
				return $id_list;
			}
		}
		return '0';
		
	}

	public function updated_list_contact_or_campaign_list_contact( $table_lists, $where_id, Array $update ) {
		global $wpdb;
		$update_nb_contacts = true;
		if ( strpos( $table_lists, 'jackmail_campaigns' ) !== false ) {
			$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$where_id}";
			$sql                 = "SELECT `id_lists` FROM `{$table_lists}` WHERE `id` = %s";
			$campaign            = $wpdb->get_row( $wpdb->prepare( $sql, $where_id ) );
			if ( isset( $campaign->id_lists ) ) {
				$id_lists     = $this->explode_data( $campaign->id_lists );
				$new_id_lists = array();
				foreach ( $id_lists as $id_list ) {
					$sql         = "SELECT COUNT( * ) FROM `{$table_list_contacts}` WHERE `id_list` = %s";
					$nb_contacts = $wpdb->get_var( $wpdb->prepare( $sql, $id_list ) );
					if ( $nb_contacts !== '0' ) {
						$new_id_lists[] = $id_list;
					}
				}
				$id_lists     = $this->implode_data( $id_lists );
				$new_id_lists = $this->implode_data( $new_id_lists );
				if ( $id_lists !== $new_id_lists ) {
					$update['id_lists'] = $new_id_lists;
				}
			}
		} else {
			$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$where_id}";
			$sql                 = "SELECT `type` FROM `{$table_lists}` WHERE `id` = %s";
			$list                = $wpdb->get_row( $wpdb->prepare( $sql, $where_id ) );
			if ( isset( $list->type ) ) {
				$list_type   = json_decode( $list->type, true );
				$plugin_name = '';
				if ( isset( $list_type['name'] ) ) {
					$plugin_name = $list_type['name'];
				}
				if ( $this->is_dynamic_nb_contacts( $plugin_name ) ) {
					$update_nb_contacts = false;
				}
			}
		}
		if ( $this->check_table_exists( $table_list_contacts, false ) ) {
			if ( $update_nb_contacts ) {
				$nb_contacts                  = $wpdb->get_var( "SELECT COUNT( * ) FROM `{$table_list_contacts}`" );
				$nb_contacts_valids           = $wpdb->get_var( "SELECT COUNT( * ) FROM `{$table_list_contacts}` WHERE `blacklist` = '0'" );
				$update['nb_contacts']        = $nb_contacts;
				$update['nb_contacts_valids'] = $nb_contacts_valids;
			}
			$update['updated_date_gmt'] = $this->get_current_time_gmt_sql();
			return $wpdb->update( $table_lists, $update, array(
				'id' => $where_id
			) );
		}
		return false;
	}

	public function get_new_campaign_data( $content_email_json = '' ) {
		$sender_name_and_email = $this->get_sender_name_and_email();
		$sender_name           = '';
		$sender_email          = '';
		if ( isset( $sender_name_and_email['sender_name'], $sender_name_and_email['sender_email'] ) ) {
			$sender_name  = $sender_name_and_email['sender_name'];
			$sender_email = $sender_name_and_email['sender_email'];
		}
		$link_tracking = get_option( 'jackmail_link_tracking' );
		if ( $content_email_json === '' ) {
			$content_email_json = get_option( 'jackmail_default_template' );
			if ( $content_email_json === false ) {
				$content_email_json = '';
			}
			if ( $this->emailbuilder_installed() === false ) {
				$content_email_json = '';
			}
		}
		$campaign = array(
			'id'                         => '0',
			'id_lists'                   => '[]',
			'fields'                     => '[]',
			'name'                       => __( 'Campaign with no name', 'jackmail-newsletters' ),
			'object'                     => '',
			'sender_name'                => $sender_name,
			'sender_email'               => $sender_email,
			'reply_to_name'              => '',
			'reply_to_email'             => '',
			'nb_contacts'                => '0',
			'nb_contacts_valids'         => '0',
			'link_tracking'              => $link_tracking,
			'content_email_json'         => $content_email_json,
			'content_email_html'         => '',
			'content_email_txt'          => '',
			'status'                     => 'DRAFT',
			'content_size'               => true,
			'content_images_size'        => true,
			'send_option'                => 'NOW',
			'send_option_date_begin_gmt' => '0000-00-00 00:00:00',
			'send_option_date_end_gmt'   => '0000-00-00 00:00:00'
		);
		return $campaign;
	}

	public function update_list_contact_or_campaign_list_contact( $table_lists, Array $update, Array $where ) {
		global $wpdb;
		return $wpdb->update( $table_lists, $update, $where );
	}

	public function update_list_contact( $id_list, Array $update, Array $where ) {
		global $wpdb;
		return $this->update_list_contact_or_campaign_list_contact( "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}", $update, $where );
	}

	public function update_campaign_list_contact( $id_campaign, Array $update, Array $where ) {
		global $wpdb;
		return $this->update_list_contact_or_campaign_list_contact( "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}", $update, $where );
	}

	public function updated_list_contact( $id_list, Array $update = array() ) {
		global $wpdb;
		return $this->updated_list_contact_or_campaign_list_contact( "{$wpdb->prefix}jackmail_lists", $id_list, $update );
	}

	public function updated_campaign_list_contact( $id_campaign, Array $update = array() ) {
		global $wpdb;
		return $this->updated_list_contact_or_campaign_list_contact( "{$wpdb->prefix}jackmail_campaigns", $id_campaign, $update );
	}

	public function is_defined_nb_contacts( $plugin_name ) {
		return ! $this->is_dynamic_nb_contacts( $plugin_name );
	}

	public function is_dynamic_nb_contacts( $plugin_name ) {
		if ( $plugin_name === '' || $plugin_name === 'bloom' || $plugin_name === 'contactform7' ) {
			return false;
		}
		return true;
	}

	private function update_db( $table, Array $update, Array $where ) {
		global $wpdb;
		return $wpdb->update( $table, $update, $where );
	}

	public function update_list( Array $update, Array $where ) {
		global $wpdb;
		return $this->update_db( "{$wpdb->prefix}jackmail_lists", $update, $where );
	}

	public function update_campaign( Array $update, Array $where ) {
		global $wpdb;
		return $this->update_db( "{$wpdb->prefix}jackmail_campaigns", $update, $where );
	}

	public function update_scenario( Array $update, Array $where ) {
		global $wpdb;
		return $this->update_db( "{$wpdb->prefix}jackmail_scenarios", $update, $where );
	}

	public function update_scenario_event( Array $update, Array $where ) {
		global $wpdb;
		return $this->update_db( "{$wpdb->prefix}jackmail_scenarios_events", $update, $where );
	}

	public function update_template( Array $update, Array $where ) {
		global $wpdb;
		return $this->update_db( "{$wpdb->prefix}jackmail_templates", $update, $where );
	}

	private function delete_db( $table, Array $where ) {
		global $wpdb;
		return $wpdb->delete( $table, $where );
	}

	public function delete_list_contact_or_campaign_list_contact( $table_lists, Array $where ) {
		return $this->delete_db( $table_lists, $where );
	}

	public function delete_campaign_list_contact( $id, Array $where ) {
		global $wpdb;
		return $this->delete_list_contact_or_campaign_list_contact( "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id}", $where );
	}

	public function delete_list_contact( $id, Array $where ) {
		global $wpdb;
		return $this->delete_list_contact_or_campaign_list_contact( "{$wpdb->prefix}jackmail_lists_contacts_{$id}", $where );
	}

	public function delete_campaign( Array $where ) {
		global $wpdb;
		return $this->delete_db( "{$wpdb->prefix}jackmail_campaigns", $where );
	}

	public function delete_scenario( Array $where ) {
		global $wpdb;
		return $this->delete_db( "{$wpdb->prefix}jackmail_scenarios", $where );
	}

	public function delete_scenario_event( Array $where ) {
		global $wpdb;
		return $this->delete_db( "{$wpdb->prefix}jackmail_scenarios_events", $where );
	}

	public function delete_template( Array $where ) {
		global $wpdb;
		return $this->delete_db( "{$wpdb->prefix}jackmail_templates", $where );
	}

	private function insert_db( $table, Array $insert ) {
		global $wpdb;
		$insert_return = $wpdb->insert( $table, $insert );
		if ( $insert_return !== false ) {
			$id = strval( $wpdb->insert_id );
			if ( $id !== false ) {
				return $id;
			}
		}
		return false;
	}

	public function insert_list_contact_or_campaign_list_contact( $table_lists, Array $update ) {
		return $this->insert_db( $table_lists, $update );
	}

	public function insert_list_contact( $id_list, Array $update ) {
		global $wpdb;
		return $this->insert_list_contact_or_campaign_list_contact( "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}", $update );
	}

	public function insert_campaign_list_contact( $id_campaign, Array $update ) {
		global $wpdb;
		return $this->insert_list_contact_or_campaign_list_contact( "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}", $update );
	}

	public function insert_campaign( Array $insert ) {
		global $wpdb;
		return $this->insert_db( "{$wpdb->prefix}jackmail_campaigns", $insert );
	}

	public function insert_list( Array $insert ) {
		global $wpdb;
		return $this->insert_db( "{$wpdb->prefix}jackmail_lists", $insert );
	}

	public function insert_scenario( Array $insert ) {
		global $wpdb;
		return $this->insert_db( "{$wpdb->prefix}jackmail_scenarios", $insert );
	}

	public function insert_scenario_event( Array $insert ) {
		global $wpdb;
		return $this->insert_db( "{$wpdb->prefix}jackmail_scenarios_events", $insert );
	}

	public function insert_template( Array $insert ) {
		global $wpdb;
		return $this->insert_db( "{$wpdb->prefix}jackmail_templates", $insert );
	}

	public function get_plugins_updates_lists_functions() {
		return array(
			array( 'name' => 'bloom', 'function' => 'bloom_update_lists' ),
			array( 'name' => 'contactform7', 'function' => 'cf7_update_lists' ),
			array( 'name' => 'formidableforms', 'function' => 'formidableforms_update_lists' ),
			array( 'name' => 'gravityforms', 'function' => 'gravityforms_update_lists' ),
			array( 'name' => 'mailpoet2', 'function' => 'mailpoet2_update_lists' ),
			array( 'name' => 'mailpoet3', 'function' => 'mailpoet3_update_lists' ),
			array( 'name' => 'ninjaforms', 'function' => 'ninjaforms_update_lists' ),
			array( 'name' => 'popupbysupsystic', 'function' => 'popupbysupsystic_update_lists' ),
			array( 'name' => 'woocommerce-carts', 'function' => 'woo_carts_update_lists' ),
			array( 'name' => 'woocommerce-customers', 'function' => 'woo_customers_update_lists' )
		);
	}

	public function get_plugins_found_functions() {
		return array(
			array( 'name' => 'bloom', 'function' => 'check_bloom_plugin_found', 'file' => 'bloom/bloom.php' ),
			array( 'name' => 'contactform7', 'function' => '', 'file' => 'contact-form-7/wp-contact-form-7.php' ),
			array( 'name' => 'formidableforms', 'function' => 'check_formidableforms_plugin_found', 'file' => 'formidable/formidable.php' ),
			array( 'name' => 'gravityforms', 'function' => 'check_gravityforms_plugin_found', 'file' => 'gravityforms/gravityforms.php' ),
			array( 'name' => 'mailpoet2', 'function' => 'check_mailpoet2_plugin_found', 'file' => 'wysija-newsletters/index.php' ),
			array( 'name' => 'mailpoet3', 'function' => 'check_mailpoet3_plugin_found', 'file' => 'mailpoet/mailpoet.php' ),
			array( 'name' => 'ninjaforms', 'function' => 'check_ninjaforms_plugin_found', 'file' => 'ninja-forms/ninja-forms.php' ),
			array( 'name' => 'popupbysupsystic', 'function' => 'check_popupbysupsystic_plugin_found', 'file' => 'popup-by-supsystic/pps.php' ),
			array( 'name' => 'woocommerce-carts', 'function' => '', 'file' => 'woocommerce/woocommerce.php' ),
			array( 'name' => 'woocommerce-customers', 'function' => 'check_woo_carts_plugin_found', 'file' => 'woocommerce-abandoned-cart/woocommerce-ac.php' )
		);
	}

	public function get_plugins_found_displayed_functions() {
		return array(
			array( 'name' => 'bloom', 'function' => 'check_bloom_plugin_found', 'file' => 'bloom/bloom.php' ),
			array( 'name' => 'contactform7', 'function' => '', 'file' => 'contact-form-7/wp-contact-form-7.php' ),
			array( 'name' => 'formidableforms', 'function' => 'check_formidableforms_plugin_found', 'file' => 'formidable/formidable.php' ),
			array( 'name' => 'gravityforms', 'function' => 'check_gravityforms_plugin_found', 'file' => 'gravityforms/gravityforms.php' ),
			array( 'name' => 'mailpoet2', 'function' => 'check_mailpoet2_plugin_found', 'file' => 'wysija-newsletters/index.php' ),
			array( 'name' => 'mailpoet3', 'function' => 'check_mailpoet3_plugin_found', 'file' => 'mailpoet/mailpoet.php' ),
			array( 'name' => 'ninjaforms', 'function' => 'check_ninjaforms_plugin_found', 'file' => 'ninja-forms/ninja-forms.php' ),
			array( 'name' => 'popupbysupsystic', 'function' => 'check_popupbysupsystic_plugin_found', 'file' => 'popup-by-supsystic/pps.php' ),
			array( 'name' => 'woocommerce', 'function' => '', 'file' => 'woocommerce/woocommerce.php' )
		);
	}

	public function cron_notifications() {
		ini_set( 'max_execution_time', 600 );
		$this->get_jackmail_update_available();
		$actual_version = $this->get_jackmail_version();
		$check_plugins  = json_decode( $this->json_encode( get_option( '_site_transient_update_plugins' ) ), true );
		$plugin_path    = 'jackmail-newsletters/jackmail-newsletters.php';
		
		if ( isset( $check_plugins ['response'] ) ) {
			if ( isset( $check_plugins ['response'][ $plugin_path ] ) ) {
				if ( isset( $check_plugins ['response'][ $plugin_path ]['new_version'] ) ) {
					$new_version = $check_plugins ['response'][ $plugin_path ]['new_version'];
					if ( version_compare( $actual_version, $new_version, '<' ) ) {
						update_option( 'jackmail_update_available', '1' );
					}
				}
			}
		}
		$url      = $this->get_jackmail_url_ws() . 'infos.php';
		$headers  = array();
		$body     = array(
			'account_id'       => $this->get_account_id(),
			'jackmail_version' => $actual_version,
			'language'         => $this->get_current_language()
		);
		$timeout  = 30;
		$response = $this->remote_post( $url, $headers, $body, $timeout );
		if ( is_array( $response ) ) {
			if ( isset( $response['body'] ) ) {
				$data = json_decode( $response['body'], true );
				if ( isset( $data['used_ids'], $data['notifications'], $data['update'], $data['emailbuilder_version'],
					$data['statistics_version'], $data['email_images_size_limit'] ) ) {
					if ( is_array( $data['notifications'] ) ) {
						$notifications = array();
						foreach ( $data['notifications'] as $notification ) {
							if ( isset( $notification['notification_id'], $notification['notification'] ) ) {
								$notifications[] = $notification['notification_id'];
							}
						}
						update_option( 'jackmail_notifications_messages', $data['notifications'] );
						$current_jackmail_notifications_messages_hidden = get_option( 'jackmail_notifications_messages_hidden' );
						$new_jackmail_notifications_messages_hidden     = array();
						foreach ( $current_jackmail_notifications_messages_hidden as $notification_message_hidden ) {
							if ( in_array( $notification_message_hidden, $notifications ) ) {
								$new_jackmail_notifications_messages_hidden[] = $notification_message_hidden;
							}
						}
						update_option( 'jackmail_notifications_messages_hidden', $new_jackmail_notifications_messages_hidden );
					}
					if ( $data['update'] === true ) {
						update_option( 'jackmail_update_available', '1' );
						update_option( 'jackmail_force_update_available', '1' );
					}
					if ( $data['statistics_version'] === 'v2' ) {
						update_option( 'jackmail_statistics_version', 'v2' );
					} else {
						update_option( 'jackmail_statistics_version', '' );
					}
					update_option( 'jackmail_emailbuilder_version', $data['emailbuilder_version'] );
					update_option( 'jackmail_email_images_size_limit', $data['email_images_size_limit'] );
					if ( isset( $data['reset_blacklist'] ) ) {
						$reset_blacklist          = $data['reset_blacklist'];
						$jackmail_reset_blacklist = get_option( 'jackmail_reset_blacklist', '1' );
						if ( $jackmail_reset_blacklist !== $reset_blacklist ) {
							$install_date = get_option( 'jackmail_install_date' );
							update_option( 'jackmail_blacklist_last_update', $install_date );
							update_option( 'jackmail_reset_blacklist', $reset_blacklist, 'no' );
							update_option( 'jackmail_deleted_campaigns_date_gmt', $install_date );
							global $wpdb;
							$sql = "
							UPDATE `{$wpdb->prefix}jackmail_campaigns`
							SET `check_blacklist_date_gmt` = `validation_date_gmt`";
							$wpdb->query( $sql );
							$sql = "
							UPDATE `{$wpdb->prefix}jackmail_scenarios_events`
							SET `check_blacklist_date_gmt` = `validation_date_gmt`";
							$wpdb->query( $sql );
						}
					}
					
					if ( get_option( 'jackmail_used_ids', '1' ) !== $data['used_ids'] ) {
						$account_id = $this->get_account_id();
						if ( $account_id !== '' ) {
							global $wpdb;
							$campaigns_array = array();
							$sql             = "SELECT `campaign_id`, `send_id`
							FROM `{$wpdb->prefix}jackmail_campaigns`
							WHERE `campaign_id` != ''
							AND `send_id` != ''";
							$campaigns       = $wpdb->get_results( $sql );
							foreach ( $campaigns as $campaign ) {
								$campaigns_array[] = array(
									'campaign_id' => $campaign->campaign_id,
									'send_id'     => $campaign->send_id
								);
							}
							$sql       = "SELECT `campaign_id`, `send_id`
							FROM `{$wpdb->prefix}jackmail_scenarios_events`
							WHERE `campaign_id` != ''
							AND `send_id` != ''";
							$campaigns = $wpdb->get_results( $sql );
							foreach ( $campaigns as $campaign ) {
								$campaigns_array[] = array(
									'campaign_id' => $campaign->campaign_id,
									'send_id'     => $campaign->send_id
								);
							}
							$lists_array = array();
							$sql         = "
							SELECT `id`, `id_campaign`
							FROM `{$wpdb->prefix}jackmail_lists`";
							$lists       = $wpdb->get_results( $sql );
							foreach ( $lists as $list ) {
								if ( isset( $list->id, $list->id_campaign ) ) {
									$lists_array[] = $this->get_campaign_id_list( $list->id, $list->id_campaign );
								}
							}
							$url      = $this->get_jackmail_url_ws() . 'infos.php';
							$headers  = array(
								'content-type' => 'application/json'
							);
							$body     = array(
								'account_id' => $account_id,
								'campaigns'  => $campaigns_array,
								'lists'      => $lists_array
							);
							$timeout  = 30;
							$response = $this->remote_post( $url, $headers, $body, $timeout );
							if ( is_array( $response ) ) {
								if ( isset( $response['body'] ) ) {
									$results = json_decode( $response['body'], true );
									if ( is_array( $results ) ) {
										if ( isset( $results['success'] ) ) {
											if ( $results['success'] === true ) {
												update_option( 'jackmail_used_ids', $data['used_ids'], 'no' );
											}
										}
									}
								}
							}
						} else {
							update_option( 'jackmail_used_ids', $data['used_ids'], 'no' );
						}
					}
					
				}
			}
		}
		if ( get_option( 'jackmail_version' ) === '' ) {
			update_option( 'jackmail_version', $actual_version );
		}
		$this->premium_notification();
	}

	public function status_to_draft_campaign( $id_campaign, $old_status ) {
		return $this->update_campaign( array(
			'updated_date_gmt'           => $this->get_current_time_gmt_sql(),
			'updated_by'                 => get_current_user_id(),
			'status'                     => 'DRAFT',
			'send_option'                => 'NOW',
			'send_option_date_begin_gmt' => '0000-00-00 00:00:00',
			'send_option_date_end_gmt'   => '0000-00-00 00:00:00',
			'campaign_id'                => '',
			'send_id'                    => '',
			'validation_date_gmt'        => '0000-00-00 00:00:00',
			'check_blacklist_date_gmt'   => '0000-00-00 00:00:00'
		), array(
			'id'     => $id_campaign,
			'status' => $old_status
		) );
	}

	public function progress_contacts_blacklist() {
		ini_set( 'max_execution_time', 600 );
		global $wpdb;
		if ( $this->is_statistics_v2() ) {
			$blacklist_last_update_gmt = get_option( 'jackmail_blacklist_last_update', date( 'Y-m-d H:i:s', strtotime( '-2 weeks', time() ) ) );
			$current_date_gmt          = $this->get_current_time_gmt_sql();
			if ( strtotime( $current_date_gmt ) - strtotime( $blacklist_last_update_gmt ) > 30 ) {
				$sql   = "
				SELECT `l`.`id`, `l`.`id_campaign`
				FROM `{$wpdb->prefix}jackmail_lists` AS `l`
				LEFT JOIN `{$wpdb->prefix}jackmail_scenarios` AS `s` ON `l`.`id` = `s`.`id_lists`
				GROUP BY `l`.`id`
				ORDER BY `l`.`id` DESC";
				$lists = $wpdb->get_results( $sql );
				if ( count( $lists ) > 0 ) {
					$id_lists = array();
					foreach ( $lists as $list ) {
						$id_lists[] = $this->get_campaign_id_list( $list->id, $list->id_campaign );
					}
					$begin_interval = $this->get_iso_date( $blacklist_last_update_gmt );
					$end_interval   = $this->get_iso_date( $current_date_gmt );
					$url            = $this->get_jackmail_url_analytics() . 'blacklist';
					$headers        = array(
						'content-type' => 'application/json',
						'x-auth-token' => $this->get_account_token(),
						'accountId'    => $this->get_account_id(),
						'userId'       => $this->get_user_id()
					);
					$body           = array(
						'beginInterval' => $begin_interval,
						'endInterval'   => $end_interval,
						'listIds'       => $id_lists
					);
					$timeout        = 30;
					$response       = $this->remote_post_retry( $url, $headers, $body, $timeout );
					if ( is_array( $response ) ) {
						if ( isset( $response['body'] ) ) {
							$results = json_decode( $response['body'], true );
							if ( is_array( $results ) ) {
								$blacklists_types = $this->get_blacklist_types();
								$updated_lists    = array();
								foreach ( $results as $result ) {
									if ( isset( $result['email'], $result['listId'], $result['kind'] ) ) {
										$id_list = substr( $result['listId'], 0, $this->str_len( $result['listId'] ) - $this->str_len( get_option( 'jackmail_id' ) ) );
										if ( substr( $id_list, 0, 1 ) === 'C' ) {
											$id_list = substr( $id_list, 1 );
											$sql     = "
											SELECT `id`
											FROM `{$wpdb->prefix}jackmail_lists`
											WHERE `id_campaign` = %s";
										} else {
											$sql = "
											SELECT `id`
											FROM `{$wpdb->prefix}jackmail_lists`
											WHERE `id` = %s";
										}
										$list = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
										if ( isset( $list->id ) ) {
											$id_list             = $list->id;
											$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
											if ( $this->check_table_exists( $table_list_contacts, false ) ) {
												$sql               = "
												INSERT INTO `{$table_list_contacts}` (`email`, `blacklist`) VALUES
												(%s, %s)
												ON DUPLICATE KEY UPDATE
												`email` = IF( `{$table_list_contacts}`.`blacklist` < VALUES(`blacklist`), VALUES(`email`), `{$table_list_contacts}`.`email` ),
												`blacklist` = IF( `{$table_list_contacts}`.`blacklist` < VALUES(`blacklist`), VALUES(`blacklist`), `{$table_list_contacts}`.`blacklist` )";
												$blacklist_type_id = 0;
												if ( $result['kind'] === 'BOUNCE' ) {
													$blacklist_type_id = $blacklists_types['bounces'];
												} else if ( $result['kind'] === 'COMPLAINT' ) {
													$blacklist_type_id = $blacklists_types['complaints'];
												} else if ( $result['kind'] === 'UNSUBSCRIBE' ) {
													$blacklist_type_id = $blacklists_types['unsubscribes'];
												}
												if ( $blacklist_type_id !== 0 ) {
													if ( is_email( $result['email'] ) ) {
														$wpdb->query( $wpdb->prepare( $sql, $result['email'], $blacklist_type_id ) );
														if ( $wpdb->rows_affected !== 0 ) {
															if ( ! in_array( $id_list, $updated_lists ) ) {
																$updated_lists[] = $id_list;
															}
														}
													}
												}
											}
										}
									}
								}
								foreach ( $updated_lists as $id_list ) {
									$this->updated_list_contact( $id_list );
								}
								update_option( 'jackmail_blacklist_last_update', $current_date_gmt, 'no' );
							}
						}
					}
				}
			}
		} else {
			$sql      = "
			SELECT COUNT( * ) AS `nb_lists`
			FROM `{$wpdb->prefix}jackmail_lists`";
			$nb_lists = $wpdb->get_row( $sql );
			if ( isset( $nb_lists->nb_lists ) ) {
				if ( $nb_lists->nb_lists !== '0' ) {
					$check_blacklist_date_gmt_array = array();
					$sql                            = "
					SELECT `send_id`, `check_blacklist_date_gmt`
					FROM `{$wpdb->prefix}jackmail_campaigns`
					WHERE `status` = 'SENT'";
					$campaigns                      = $wpdb->get_results( $sql );
					foreach ( $campaigns as $campaign ) {
						$check_blacklist_date_gmt_array[ $campaign->check_blacklist_date_gmt ][] = $campaign->send_id;
					}
					$sql       = "
					SELECT `send_id`, `check_blacklist_date_gmt`
					FROM `{$wpdb->prefix}jackmail_scenarios_events`
					WHERE `status` = 'SENT'";
					$campaigns = $wpdb->get_results( $sql );
					foreach ( $campaigns as $campaign ) {
						$check_blacklist_date_gmt_array[ $campaign->check_blacklist_date_gmt ][] = $campaign->send_id;
					}
					$campaigns = get_option( 'jackmail_deleted_campaigns' );
					
					foreach ( $campaigns as $send_id ) {
						$check_blacklist_date_gmt_array[ get_option( 'jackmail_deleted_campaigns_date_gmt' ) . '-deleted' ][] = $send_id;
					}
					
					if ( count( $check_blacklist_date_gmt_array ) > 0 ) {
						foreach ( $check_blacklist_date_gmt_array as $check_blacklist_date_gmt => $send_ids ) {
							$deleted_campaigns = false;
							
							if ( strpos( $check_blacklist_date_gmt, '-deleted' ) !== false ) {
								$deleted_campaigns        = true;
								$check_blacklist_date_gmt = str_replace( '-deleted', '', $check_blacklist_date_gmt );
							}
							
							$current_date_gmt = $this->get_current_time_gmt_sql();
							$begin_interval   = $this->get_iso_date( $check_blacklist_date_gmt );
							$end_interval     = $this->get_iso_date( $current_date_gmt );
							$url              = $this->get_jackmail_url_analytics() . 'blacklist';
							$headers          = array(
								'content-type' => 'application/json',
								'x-auth-token' => $this->get_account_token(),
								'accountId'    => $this->get_account_id(),
								'userId'       => $this->get_user_id()
							);
							$body             = array(
								'beginInterval' => $begin_interval,
								'endInterval'   => $end_interval,
								'sendIds'       => $send_ids
							);
							$timeout          = 30;
							$response         = $this->remote_post_retry( $url, $headers, $body, $timeout );
							if ( is_array( $response ) ) {
								if ( isset( $response['body'] ) ) {
									$results = json_decode( $response['body'], true );
									if ( is_array( $results ) ) {
										$blacklists_types = $this->get_blacklist_types();
										$updated_lists    = array();
										foreach ( $blacklists_types as $blacklist_type => $blacklist_type_id ) {
											if ( isset( $results[ $blacklist_type ] ) ) {
												if ( is_array( $results[ $blacklist_type ] ) ) {
													foreach ( $results[ $blacklist_type ] as $result ) {
														if ( isset( $result['event'] ) ) {
															if ( is_array( $result['event'] ) ) {
																if ( isset( $result['event']['recipientEmail'], $result['event']['listId'] ) ) {
																	$email   = $result['event']['recipientEmail'];
																	$id_list = substr( $result['event']['listId'], 0, $this->str_len( $result['event']['listId'] ) - $this->str_len( get_option( 'jackmail_id' ) ) );
																	if ( substr( $id_list, 0, 1 ) === 'C' ) {
																		$id_list = substr( $id_list, 1 );
																		$sql     = "
																		SELECT `id`
																		FROM `{$wpdb->prefix}jackmail_lists`
																		WHERE `id_campaign` = %s";
																	} else {
																		$sql = "
																		SELECT `id`
																		FROM `{$wpdb->prefix}jackmail_lists`
																		WHERE `id` = %s";
																	}
																	$list = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
																	if ( isset( $list->id ) ) {
																		$id_list             = $list->id;
																		$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
																		if ( $this->check_table_exists( $table_list_contacts, false ) ) {
																			$sql = "
																			INSERT INTO `{$table_list_contacts}` (`email`, `blacklist`) VALUES
																			(%s, %s)
																			ON DUPLICATE KEY UPDATE
																			`email` = IF( `{$table_list_contacts}`.`blacklist` < VALUES(`blacklist`), VALUES(`email`), `{$table_list_contacts}`.`email` ),
																			`blacklist` = IF( `{$table_list_contacts}`.`blacklist` < VALUES(`blacklist`), VALUES(`blacklist`), `{$table_list_contacts}`.`blacklist` )";
																			$wpdb->query( $wpdb->prepare( $sql, $email, $blacklist_type_id ) );
																			if ( $wpdb->rows_affected !== 0 ) {
																				if ( ! in_array( $id_list, $updated_lists ) ) {
																					$updated_lists[] = $id_list;
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
										foreach ( $updated_lists as $id_list ) {
											$this->updated_list_contact( $id_list );
										}
										if ( ! $deleted_campaigns ) {
											
											foreach ( $send_ids as $send_id ) {
												$this->update_campaign( array(
													'check_blacklist_date_gmt' => $current_date_gmt
												), array(
													'send_id' => $send_id
												) );
												$this->update_scenario_event( array(
													'check_blacklist_date_gmt' => $current_date_gmt
												), array(
													'send_id' => $send_id
												) );
											}
										} else {
											
											update_option( 'jackmail_deleted_campaigns_date_gmt', $current_date_gmt );
											
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function progress_campaigns() {
		ini_set( 'max_execution_time', 200 );
		global $wpdb;
		$refresh_progress = false;
		$sql              = "
		SELECT COUNT( * ) AS `nb_campaigns`
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `status` = 'PROCESS_SENDING'
		OR `status` = 'PROCESS_SCHEDULED'
		OR `status` = 'SENDING'
		OR `status` = 'SCHEDULED'";
		$nb_campaigns     = $wpdb->get_row( $sql );
		if ( isset( $nb_campaigns->nb_campaigns ) ) {
			if ( $nb_campaigns->nb_campaigns !== '0' ) {
				$refresh_progress = true;
			}
		}
		if ( ! $refresh_progress ) {
			$sql          = "
			SELECT COUNT( * ) AS `nb_campaigns`
			FROM `{$wpdb->prefix}jackmail_scenarios_events`
			WHERE `status` = 'PROCESS_SENDING'
			OR `status` = 'PROCESS_SCHEDULED'
			OR `status` = 'SENDING'
			OR `status` = 'SCHEDULED'";
			$nb_campaigns = $wpdb->get_row( $sql );
			if ( isset( $nb_campaigns->nb_campaigns ) ) {
				if ( $nb_campaigns->nb_campaigns !== '0' ) {
					$refresh_progress = true;
				}
			}
		}
		if ( $refresh_progress ) {
			$this->premium_notification( true );
			$url      = $this->get_jackmail_url_api() . 'v2/progress/send';
			$headers  = array(
				'content-type' => 'application/json',
				'token'        => $this->get_account_token(),
				'x-auth-token' => $this->get_account_token(),
				'accountId'    => $this->get_account_id(),
				'userId'       => $this->get_user_id(),
				'computerId'   => get_option( 'jackmail_id' )
			);
			$timeout  = 30;
			$response = $this->remote_get_retry( $url, $headers, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$progress_campaigns = json_decode( $response['body'], true );
					if ( isset( $progress_campaigns ['sends'] ) ) {
						if ( is_array( $progress_campaigns ['sends'] ) ) {
							
							$send_ids = array();
							foreach ( $progress_campaigns ['sends'] as $progress_campaign ) {
								if ( isset( $progress_campaign ['id'], $progress_campaign ['sendState'] ) ) {
									$send_ids[] = $progress_campaign ['id'];
								}
							}
							$date_end          = date( 'Y-m-d H:i:s', strtotime( '-3 hours', time() ) );
							$sql               = "
							(
								SELECT `send_id`
								FROM `{$wpdb->prefix}jackmail_campaigns`
								WHERE (`status` = 'SENDING' OR `status` = 'SCHEDULED')
								AND `send_option_date_end_gmt` <= %s
							) UNION ALL  (
								SELECT `send_id`
								FROM `{$wpdb->prefix}jackmail_scenarios_events`
								WHERE `status` = 'SENDING'
								AND `send_option_date_end_gmt` <= %s
							)";
							$sending_campaigns = $wpdb->get_results( $wpdb->prepare( $sql, $date_end, $date_end ) );
							foreach ( $sending_campaigns as $sending_campaign ) {
								if ( ! in_array( $sending_campaign->send_id, $send_ids ) ) {
									$progress_campaigns ['sends'][] = array(
										'id'        => $sending_campaign->send_id,
										'sendState' => 'FINISH'
									);
								}
							}
							
							foreach ( $progress_campaigns ['sends'] as $progress_campaign ) {
								if ( isset( $progress_campaign ['id'], $progress_campaign ['sendState'] ) ) {
									$send_id = $progress_campaign ['id'];
									$status  = $progress_campaign ['sendState'];
									$reason  = '';
									if ( isset( $progress_campaign ['reason'] ) ) {
										$reason = $progress_campaign ['reason'];
									}
									$sql            = "
									SELECT `id`, `nb_contacts`, `nb_contacts_valids`, `status`
									FROM `{$wpdb->prefix}jackmail_campaigns`
									WHERE `send_id` = %s";
									$campaign_event = $wpdb->get_row( $wpdb->prepare( $sql, $send_id ) );
									if ( isset( $campaign_event->id ) ) {
										$campaign_type = 'campaign';
										$sql           = "
										SELECT `name`, `id_lists`, `nb_contacts`, `nb_contacts_valids`, `fields`
										FROM `{$wpdb->prefix}jackmail_campaigns`
										WHERE `id` = %s";
									} else {
										$sql            = "
										SELECT `id`, `nb_contacts`, `nb_contacts_valids`, `status`
										FROM `{$wpdb->prefix}jackmail_scenarios_events`
										WHERE `send_id` = %s";
										$campaign_event = $wpdb->get_row( $wpdb->prepare( $sql, $send_id ) );
										$campaign_type  = 'scenario';
										$sql            = "
										SELECT `name`, `id_lists`, `nb_contacts`, `nb_contacts_valids`, '' AS `fields`
										FROM `{$wpdb->prefix}jackmail_scenarios`
										WHERE `id` = %s";
									}
									if ( isset( $campaign_event->id ) ) {
										$id_campaign        = $campaign_event->id;
										$nb_contacts        = $campaign_event->nb_contacts;
										$nb_contacts_valids = $campaign_event->nb_contacts_valids;
										$current_status     = $campaign_event->status;
										$campaign           = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
										if ( isset( $campaign->name ) ) {
											$name                  = $campaign->name;
											$id_lists              = $campaign->id_lists;
											$total_contacts        = $campaign->nb_contacts;
											$total_contacts_valids = $campaign->nb_contacts_valids;
											$fields                = $campaign->fields;
											if ( $status === 'FINISH' ) {
												if ( $campaign_type === 'campaign' ) {
													$table_campaigns_lists_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
													$update_campaign                = array(
														'status' => 'SENT'
													);
													if ( $this->check_table_exists( $table_campaigns_lists_contacts, false ) ) {
														$sql       = "
														SELECT COUNT( * ) AS `nb_contacts_campaign`
														FROM `{$table_campaigns_lists_contacts}`
														WHERE `id_list` = '0'";
														$campaign  = $wpdb->get_row( $sql );
														$list_name = __( 'List from campaign', 'jackmail-newsletters' ) . ' "' . $name . '"';
														if ( isset( $campaign->nb_contacts_campaign ) ) {
															if ( $campaign->nb_contacts_campaign > 0 ) {
																$columns = $this->get_table_columns( $table_campaigns_lists_contacts, false );
																if ( count( $columns ) > 0 ) {
																	$sql_select = array();
																	foreach ( $columns as $column ) {
																		if ( $column !== 'id_list' ) {
																			$sql_select[] = '`' . $column . '`';
																		}
																	}
																	if ( count( $sql_select ) > 0 ) {
																		$current_date_gmt = $this->get_current_time_gmt_sql();
																		$id_list          = $this->insert_list( array(
																			'name'             => $list_name,
																			'id_campaign'      => $id_campaign,
																			'fields'           => $fields,
																			'created_date_gmt' => $current_date_gmt,
																			'updated_date_gmt' => $current_date_gmt,
																			'connector_key'    => $this->get_random()
																		) );
																		if ( $id_list !== false ) {
																			$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
																			$nb_fields           = count( $sql_select ) - 2;
																			$sql_select          = implode( ', ', $sql_select );
																			if ( $this->create_list_table( $id_list, $nb_fields ) !== false ) {
																				$sql = "
																				INSERT INTO `{$table_list_contacts}`
																				SELECT {$sql_select} FROM `{$table_campaigns_lists_contacts}` WHERE `id_list` = '0'";
																				$wpdb->query( $sql );
																				$this->updated_list_contact( $id_list );
																			}
																			$id_lists                    = $this->explode_data( $id_lists );
																			$id_lists[]                  = $id_list;
																			$id_lists                    = $this->implode_data( $id_lists );
																			$update_campaign['id_lists'] = $id_lists;
																		}
																	}
																}
															}
														}
													}
													$this->update_campaign( $update_campaign, array(
														'id' => $id_campaign
													) );
													$sql = "DROP TABLE IF EXISTS `{$table_campaigns_lists_contacts}`";
													$wpdb->query( $sql );
												} else {
													$this->update_scenario( array(
														'nb_contacts'        => $total_contacts + $nb_contacts,
														'nb_contacts_valids' => $total_contacts_valids + $nb_contacts_valids
													), array(
														'id' => $id_campaign
													) );
													$this->update_scenario_event( array(
														'status' => 'SENT'
													), array(
														'id'      => $id_campaign,
														'send_id' => $send_id
													) );
												}
											} else if ( $status === 'REFUSED_BY_MODERATION' || $status === 'USER_ABORTED' || $status === 'SYSTEM_ABORTED' || $status === 'REFUSED_BY_LICENCE' ) {
												$display_status = 'ERROR';
												if ( $status === 'REFUSED_BY_MODERATION' ) {
													$display_status = 'REFUSED';
												} else if ( $status === 'USER_ABORTED' ) {
													$display_status = 'USER_ABORTED';
												}
												if ( $campaign_type === 'campaign' ) {
													$this->update_campaign( array(
														'status'        => $display_status,
														'status_detail' => $reason
													), array(
														'id' => $id_campaign
													) );
												} else {
													$this->update_scenario_event( array(
														'status'        => $display_status,
														'status_detail' => $reason
													), array(
														'id'      => $id_campaign,
														'send_id' => $send_id
													) );
												}
											} else {
												$new_status = '';
												if ( $current_status === 'PROCESS_SENDING' ) {
													$new_status = 'SENDING';
												} else if ( $current_status === 'PROCESS_SCHEDULED' ) {
													$new_status = 'SCHEDULED';
												}
												if ( $new_status !== '' && $new_status !== $current_status ) {
													if ( $campaign_type === 'campaign' ) {
														$this->update_campaign( array(
															'status'        => $new_status,
															'status_detail' => $reason
														), array(
															'id' => $id_campaign
														) );
													} else {
														$this->update_scenario_event( array(
															'status'        => $new_status,
															'status_detail' => $reason
														), array(
															'id'      => $id_campaign,
															'send_id' => $send_id
														) );
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

}