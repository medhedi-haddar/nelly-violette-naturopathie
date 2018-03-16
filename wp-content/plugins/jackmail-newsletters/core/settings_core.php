<?php


class Jackmail_Settings_Core {

	protected function domain_configuration() {
		$subdomain = get_option( 'jackmail_domain_sub' );
		$txt       = $this->get_domain_txt( $subdomain );
		$data      = array(
			'subdomain' => '',
			'txt'       => '',
			'ns1'       => '',
			'ns2'       => '',
			'is_valid'  => false
		);
		if ( $subdomain !== '' && $txt !== '' ) {
			$ns1  = $this->get_domain_ns1( $subdomain );
			$ns2  = $this->get_domain_ns2( $subdomain );
			$data = array(
				'subdomain' => $subdomain,
				'txt'       => $txt,
				'ns1'       => $ns1,
				'ns2'       => $ns2,
				'is_valid'  => $this->core->domain_is_valid()
			);
		}
		return $data;
	}

	protected function domain_get_txt_ns( $subdomain ) {
		return array(
			'txt' => $this->get_domain_txt( $subdomain ),
			'ns1' => $this->get_domain_ns1( $subdomain ),
			'ns2' => $this->get_domain_ns2( $subdomain )
		);
	}

	private function get_domain_txt( $subdomain, $full = true ) {
		$txt = $this->core->get_domain_txt( $subdomain );
		if ( $full ) {
			$subdomain_array = explode( '.', $subdomain );
			$subdomain_txt   = '';
			if ( count( $subdomain_array ) === 1 ) {
				$subdomain_txt = $subdomain_array[0];
			} else {
				foreach ( $subdomain_array as $key => $data ) {
					if ( $key !== 0 ) {
						if ( $subdomain_txt !== '' ) {
							$subdomain_txt .= '.';
						}
						$subdomain_txt .= $data;
					}
				}
			}
			return $subdomain_txt . ' IN TXT "' . $txt . '"';
		}
		return $txt;
	}

	private function get_domain_ns1( $subdomain ) {
		$ns = $this->get_ns( $subdomain );
		return $ns . ' IN NS dns.customizedurl.com.';
	}

	private function get_domain_ns2( $subdomain ) {
		$ns = $this->get_ns( $subdomain );
		return $ns . ' IN NS dns2.customizedurl.com.';
	}

	private function get_ns( $subdomain ) {
		$subdomain_array = explode( '.', $subdomain );
		$ns              = '';
		if ( count( $subdomain_array ) !== 0 ) {
			$ns = $subdomain_array[0];
		}
		return $ns;
	}

	protected function domain_create_delegation( $subdomain ) {
		$result              = $this->core->domain_create_delegation( $subdomain );
		$result['subdomain'] = $subdomain;
		$result['txt']       = $this->get_domain_txt( $subdomain );
		return $result;
	}

	protected function domain_delete() {
		update_option( 'jackmail_domain_sub', '' );
	}

	protected function set_link_tracking( $tracking ) {
		update_option( 'jackmail_link_tracking', $tracking );
	}

	protected function get_jackmail_role() {
		return array(
			'role' => get_option( 'jackmail_access_type' )
		);
	}

	protected function set_jackmail_role( $role ) {
		if ( $role === 'administrator' || $role === 'editor' || $role === 'shop_manager' ) {
			update_option( 'jackmail_access_type', $role );
			return true;
		}
		return false;
	}

	protected function credits_available() {
		$this->core->cron_notifications();
		$json              = array(
			'nb_credits'        => '',
			'subscription_type' => '',
			'product_key'       => ''
		);
		$credits_available = $this->core->get_credits_available( true );
		if ( $credits_available !== false ) {
			$json = $credits_available;
		}
		return $json;
	}

	protected function get_improvement() {
		return array(
			'active' => $this->core->boolval( get_option( 'jackmail_improvement' ) )
		);
	}

	protected function set_improvement( $improvement ) {
		update_option( 'jackmail_improvement', (int) ( $improvement === 'true' ) );
	}

	protected function get_support_chat() {
		return array(
			'active' => $this->core->boolval( get_option( 'jackmail_support_chat' ) )
		);
	}

	protected function set_support_chat( $support_chat ) {
		update_option( 'jackmail_support_chat', (int) ( $support_chat === 'true' ) );
	}

	protected function get_premium_notification() {
		return array(
			'active' => $this->core->boolval( get_option( 'jackmail_premium_notification' ) )
		);
	}

	protected function set_premium_notification( $premium_notification ) {
		if ( $premium_notification === 'true' ) {
			update_option( 'jackmail_premium_notification', '1' );
		} else {
			update_option( 'jackmail_premium_notification', '0' );
			update_option( 'jackmail_display_premium_notification', '0' );
			update_option( 'jackmail_display_premium_notification_last_hide', '' );
		}
	}

	protected function get_degug() {
		global $wpdb;
		$server = 'unknown';
		$php    = 'unknown';
		if ( function_exists( 'apache_get_version' ) ) {
			$server = apache_get_version();
		}
		if ( function_exists( 'phpversion' ) ) {
			$php = phpversion();
		}
		$wordfence = false;
		$plugins   = get_option( 'active_plugins' );
		foreach ( $plugins as $plugin_url ) {
			if ( $plugin_url === 'wordfence/wordfence.php' ) {
				$wordfence = true;
			}
		}
		$json       = array(
			'server'    => $server,
			'database'  => $wpdb->db_version(),
			'php'       => $php,
			'jackmail'  => $this->core->get_jackmail_version(),
			'wordpress' => get_bloginfo( 'version' ),
			'wordfence' => $wordfence,
			'logs'      => ''
		);
		$error_path = @ini_get( 'error_log' );
		if ( $error_path !== false && $error_path !== '' ) {
			if ( file_exists( $error_path ) ) {
				if ( is_readable( $error_path ) ) {
					$logs  = '';
					$size  = filesize( $error_path );
					$begin = $size - 100000;
					if ( $begin < 0 ) {
						$begin = 0;
					}
					$content      = @file_get_contents( $error_path, false, null, $begin );
					$content      = str_replace( "\r\n", "\n", $content );
					$lines        = explode( "\n", $content );
					$plugin_path  = substr( get_home_path(), 0, - 1 );
					$plugin_path2 = str_replace( '/', '\\', $plugin_path );
					foreach ( $lines as $line ) {
						if ( strpos( $line, 'jackmail' ) !== false ) {
							$line = str_replace( $plugin_path, '', $line );
							$line = str_replace( $plugin_path2, '', $line );
							$logs .= $line . "\n";
						}
					}
					$json['logs'] = $logs;
				}
			}
		}
		return $json;
	}

	protected function get_debug_data() {
		global $wpdb;
		$campaigns_data         = array();
		$scenarios_data         = array();
		$scenarios_details_data = array();
		$lists_data             = array();
		$sql                    = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_campaigns`";
		$campaigns              = $wpdb->get_results( $sql );
		foreach ( $campaigns as $campaign ) {
			$campaigns_data[] = json_encode( $campaign );
		}
		$campaigns_data = '[' . implode( ",\n", $campaigns_data ) . ']';
		$sql            = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_scenarios`";
		$scenarios      = $wpdb->get_results( $sql );
		foreach ( $scenarios as $scenario ) {
			$scenarios_data[] = json_encode( $scenario );
		}
		$scenarios_data    = '[' . implode( ",\n", $scenarios_data ) . ']';
		$sql               = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_scenarios_events`";
		$scenarios_details = $wpdb->get_results( $sql );
		foreach ( $scenarios_details as $scenario_detail ) {
			$scenarios_details_data[] = json_encode( $scenario_detail );
		}
		$scenarios_details_data = '[' . implode( ",\n", $scenarios_details_data ) . ']';
		$sql                    = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_lists`";
		$lists_details          = $wpdb->get_results( $sql );
		foreach ( $lists_details as $list_detail ) {
			$lists_data[] = json_encode( $list_detail );
		}
		$lists_data = '[' . implode( ",\n", $lists_data ) . ']';
		$json       = array(
			'campaigns_data'         => $campaigns_data,
			'scenarios_data'         => $scenarios_data,
			'scenarios_details_data' => $scenarios_details_data,
			'lists_data'             => $lists_data
		);
		return $json;
	}

	protected function update_cookies( $selected_date1, $selected_date2, $campaign_emailing, $campaign_scenario, $statistics_campaigns_selection ) {
		$expiration = time() + 3600;
		if ( ! is_null( $selected_date1 ) ) {
			setcookie( 'jackmail_selected_date1', $selected_date1, $expiration, COOKIEPATH, COOKIE_DOMAIN );
		}
		if ( ! is_null( $selected_date2 ) ) {
			setcookie( 'jackmail_selected_date2', $selected_date2, $expiration, COOKIEPATH, COOKIE_DOMAIN );
		}
		if ( ! is_null( $campaign_emailing ) ) {
			setcookie( 'jackmail_campaign_emailing', $campaign_emailing, $expiration, COOKIEPATH, COOKIE_DOMAIN );
		}
		if ( ! is_null( $campaign_scenario ) ) {
			setcookie( 'jackmail_campaign_scenario', $campaign_scenario, $expiration, COOKIEPATH, COOKIE_DOMAIN );
		}
		if ( ! is_null( $statistics_campaigns_selection ) ) {
			setcookie( 'jackmail_statistics_campaigns_selection', $statistics_campaigns_selection, $expiration, COOKIEPATH, COOKIE_DOMAIN );
		}
	}

	protected function cron_clean_files() {
		$this->core->get_jackmail_update_available();
		global $wpdb;
		$sql           = "
		( SELECT `preview`, `content_email_images` FROM `{$wpdb->prefix}jackmail_campaigns` )
		UNION
		( SELECT `preview`, `content_email_images` FROM `{$wpdb->prefix}jackmail_scenarios` )
		UNION
		( SELECT `preview`, `content_email_images` FROM `{$wpdb->prefix}jackmail_templates` )";
		$results       = $wpdb->get_results( $sql );
		$all_images_db = array();
		$images_name   = array();
		foreach ( $results as $result ) {
			$all_images_db[] = $result->preview . '.png';
			$images_name[]   = $result->preview . '.png';
			$images          = json_decode( $result->content_email_images, true );
			foreach ( $images as $image ) {
				if ( isset( $image['image_id'], $image['image_type'] ) ) {
					$image_info = $image['image_id'] . '.' . $image['image_type'];
					if ( ! in_array( $image_info, $images_name ) ) {
						$all_images_db[] = $image_info;
						$images_name[]   = $image_info;
					}
				}
			}
		}
		$images = json_decode( get_option( 'jackmail_default_template_images' ), true );
		foreach ( $images as $image ) {
			if ( isset( $image['image_id'], $image['image_type'] ) ) {
				$image_info = $image['image_id'] . '.' . $image['image_type'];
				if ( ! in_array( $image_info, $images_name ) ) {
					$all_images_db[] = $image_info;
					$images_name[]   = $image_info;
				}
			}
		}
		$nb_images_db              = count( $all_images_db );
		$jackmail_file_path        = $this->core->get_jackmail_file_path();
		$all_images_files          = glob( $jackmail_file_path . "*.{jpeg,png,gif,bmp,tiff}", GLOB_BRACE );
		$jackmail_file_path_length = $this->core->str_len( $jackmail_file_path );
		if ( $all_images_files !== false ) {
			$nb_images_files = count( $all_images_files );
			if ( $nb_images_files > $nb_images_db ) {
				$current_time_gmt  = $this->core->get_current_time_gmt_sql();
				$current_timestamp = strtotime( $current_time_gmt );
				foreach ( $all_images_files as $image_path ) {
					$image_name = substr( $image_path, $jackmail_file_path_length );
					if ( ! in_array( $image_name, $all_images_db ) ) {
						$file_updated_date = @filemtime( $image_path );
						if ( $file_updated_date !== false ) {
							if ( $current_timestamp - $file_updated_date > 172800 ) {
								@unlink( $image_path );
							}
						}
					}
				}
			}
		}
	}

}