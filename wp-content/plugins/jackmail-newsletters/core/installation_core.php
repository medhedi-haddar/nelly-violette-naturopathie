<?php


class Jackmail_Installation_Core {

	protected function get_emailbuilder_licence() {
		$json     = array(
			'licence' => ''
		);
		$url      = $this->core->get_jackmail_url_ws() . 'emailbuilder-licence.php';
		$headers  = array();
		$timeout  = 30;
		$response = $this->core->remote_get( $url, $headers, $timeout );
		if ( is_array( $response ) ) {
			if ( isset( $response['body'] ) ) {
				$json['licence'] = $response['body'];
			}
		}
		return $json;
	}

	protected function get_plugins( $update = false ) {
		$plugins              = $this->core->get_plugins_found_displayed_functions();
		$active_plugins       = get_option( 'active_plugins' );
		$actual_plugins_array = $this->core->get_jackmail_plugins();
		$result               = array();
		foreach ( $plugins as $plugin ) {
			if ( in_array( $plugin['file'], $active_plugins ) ) {
				$continue = false;
				if ( $plugin['function'] === '' ) {
					$continue = true;
				} else {
					if ( method_exists( $this->core, $plugin['function'] ) ) {
						if ( $this->core->{$plugin['function']}() ) {
							$continue = true;
						}
					}
				}
				if ( $continue ) {
					$selected = false;
					if ( in_array( $plugin['name'], $actual_plugins_array ) ) {
						$selected = true;
					}
					$result[] = array(
						'plugin'   => $plugin['name'],
						'selected' => $selected
					);
				}
			}
		}
		if ( $update ) {
			$jackmail_plugins_list = array();
			foreach ( $result as $plugin ) {
				$jackmail_plugins_list[] = $plugin['plugin'];
			}
			$jackmail_plugins_list = $this->core->implode_data( $jackmail_plugins_list );
			if ( get_option( 'jackmail_plugins_list' ) !== $jackmail_plugins_list ) {
				update_option( 'jackmail_plugins_list', $jackmail_plugins_list );
			}
		}
		return $result;
	}

	protected function create_cf7_table_data() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}jackmail_lists_contacts_cf7_data` (
			`form_id` bigint(20) unsigned NOT NULL,
			`email` varchar(100) NOT NULL DEFAULT '',
			`fields` text NOT NULL,
			PRIMARY KEY (`form_id`, `email`)
		) {$charset_collate}" );
	}

	protected function import_plugins( $plugins ) {
		global $wpdb;
		update_option( 'jackmail_plugins', $plugins );
		$plugins_array = $this->core->explode_data( $plugins );
		if ( in_array( 'contactform7', $plugins_array ) ) {
			$this->create_cf7_table_data();
		} else {
			if ( $this->core->check_table_exists( 'jackmail_lists_contacts_cf7_data' ) ) {
				$wpdb->query( "TRUNCATE `{$wpdb->prefix}jackmail_lists_contacts_cf7_data`" );
			}
			
		}
	}

	protected function get_new_plugins() {
		$json = array();
		if ( $this->core->is_configured() ) {
			$memorized_plugins       = get_option( 'jackmail_plugins_list' );
			$actual_plugins_array    = $this->get_plugins();
			$memorized_plugins_array = $this->core->explode_data( $memorized_plugins );
			foreach ( $actual_plugins_array as $plugin ) {
				if ( ! in_array( $plugin['plugin'], $memorized_plugins_array ) ) {
					$json[] = array(
						'plugin' => $plugin['plugin']
					);
				}
			}
		}
		return $json;
	}

	protected function save_new_plugins( $new_plugins ) {
		$new_plugins_array    = $this->core->explode_data( $new_plugins );
		$actual_plugins_array = $this->core->get_jackmail_plugins();
		$plugins_array        = array();
		foreach ( $actual_plugins_array as $plugin ) {
			$plugins_array[] = $plugin;
		}
		foreach ( $new_plugins_array as $plugin ) {
			if ( $plugin !== '' ) {
				$plugins_array[] = $plugin;
			}
		}
		$plugins_array = array_unique( $plugins_array );
		if ( in_array( 'contactform7', $plugins_array ) ) {
			$this->create_cf7_table_data();
		}
		asort( $plugins_array );
		$plugins = $this->core->implode_data( $plugins_array );
		update_option( 'jackmail_plugins', $plugins );
		$this->get_plugins( true );
	}

	protected function uninstall_reason( $reason, $reason_detail ) {
		$url     = $this->core->get_jackmail_url_ws() . 'unsubscribe.php';
		$headers = array(
			'content-type' => 'application/json'
		);
		$body    = array(
			'account_id'    => $this->core->get_account_id(),
			'reason'        => $reason,
			'reason_detail' => $reason_detail,
			'version'       => $this->core->get_jackmail_version(),
			'language'      => $this->core->get_current_language(),
			'timestamp'     => $this->core->get_current_timestamp()
		);
		$timeout = 30;
		$this->core->remote_post( $url, $headers, $body, $timeout );
	}

	protected function hide_notification( $notification_id ) {
		$notifications_messages_hide   = get_option( 'jackmail_notifications_messages_hidden' );
		$notifications_messages_hide[] = $notification_id;
		update_option( 'jackmail_notifications_messages_hidden', $notifications_messages_hide );
		$url     = $this->core->get_jackmail_url_ws() . 'infos.php';
		$headers = array();
		$body    = array(
			'account_id'      => $this->core->get_account_id(),
			'notification_id' => $notification_id
		);
		$timeout = 30;
		$this->core->remote_post( $url, $headers, $body, $timeout );
	}

	protected function hide_premium_notification() {
		update_option( 'jackmail_display_premium_notification', '0' );
		update_option( 'jackmail_display_premium_notification_last_hide', $this->core->get_current_time_gmt_sql() );
	}

	protected function hide_update_popup() {
		$current_time_gmt_sql = $this->core->get_current_time_gmt_sql();
		update_option( 'jackmail_update_available_last_popup_display', $current_time_gmt_sql );
	}

	protected function cron_default_template() {
		$this->core->get_jackmail_update_available();
		ini_set( 'max_execution_time', 600 );
		if ( $this->core->emailbuilder_installed() ) {
			$jackmail_default_template_check = (int) get_option( 'jackmail_default_template_check' );
			if ( $jackmail_default_template_check === 0 ) {
				$url      = $this->core->get_jackmail_url_ws() . 'gallery.php?product=jackmail&id=default';
				$headers  = array();
				$timeout  = 30;
				$response = $this->core->remote_get( $url, $headers, $timeout );
				if ( is_array( $response ) ) {
					if ( isset( $response['body'] ) ) {
						$jackmail_default_template_compare = md5( $response['body'] );
						if ( get_option( 'jackmail_default_template_compare' ) !== $jackmail_default_template_compare ) {
							update_option( 'jackmail_default_template_compare', $jackmail_default_template_compare );
							$link_tracking      = get_option( 'jackmail_link_tracking' );
							$content_email_json = $this->core->get_gallery_template_json( 'default', $link_tracking );
							update_option( 'jackmail_default_template', $content_email_json );
							$content_email_images = $this->core->get_content_email_images( $content_email_json, '' );
							update_option( 'jackmail_default_template_images', $content_email_images );
						}
					}
				}
			}
			$jackmail_default_template_check = $jackmail_default_template_check + 1;
			if ( $jackmail_default_template_check > 2 ) {
				update_option( 'jackmail_default_template_check', '0' );
			} else {
				update_option( 'jackmail_default_template_check', $jackmail_default_template_check );
			}
		}
	}

	protected function in_admin_footer() {
		if ( $this->core->is_jackmail_page() ) {
			global $plugin_page;
			$pages = array(
				'jackmail_campaigns',
				'jackmail_lists',
				'jackmail_templates',
				'jackmail_statistics',
				'jackmail_settings'
			);
			if ( in_array( $plugin_page, $pages ) ) {
				echo '
				<div class="jackmail_footer_review">
					<span>' . __( 'You like Jackmail?', 'jackmail-newsletters' ) . '</span>
					<a href="https://wordpress.org/support/plugin/jackmail-newsletters/reviews/#new-post" target="_blank">
						<span>' . __( 'Please give a review!', 'jackmail-newsletters' ) . '</span>
					</a>
					<a href="https://wordpress.org/support/plugin/jackmail-newsletters/reviews/#new-post" target="_blank">
						<span class="dashicons dashicons-star-filled"></span>
						<span class="dashicons dashicons-star-filled"></span>
						<span class="dashicons dashicons-star-filled"></span>
						<span class="dashicons dashicons-star-filled"></span>
						<span class="dashicons dashicons-star-filled"></span>
					</a>
				</div>';
			}
		}
	}

	protected function jackmail_notices() {
		if ( ! current_user_can( $this->core->access_type() ) ) {
			return;
		}
		if ( $this->core->is_visible() ) {
			$is_jackmail_page   = $this->core->is_jackmail_page();
			$is_dashboard_page  = $this->core->is_dashboard_page();
			$is_extensions_page = $this->core->is_extensions_page();
			if ( $this->core->is_configured() ) {
				$memorized_plugins       = get_option( 'jackmail_plugins_list' );
				$actual_plugins_array    = $this->get_plugins();
				$memorized_plugins_array = $this->core->explode_data( $memorized_plugins );
				if ( is_array( $actual_plugins_array ) && is_array( $memorized_plugins_array ) ) {
					foreach ( $actual_plugins_array as $plugin ) {
						if ( ! in_array( $plugin['plugin'], $memorized_plugins_array ) ) {
							$this->core->display_notice( __( 'A new plugin has been activated, do you want to synchronize it with Jackmail?', 'jackmail-newsletters' ) . ' <a href="admin.php?page=jackmail_lists">' . __( 'Read more', 'jackmail-newsletters' ) . '</a>', 'jackmail_notice_plugins' );
							break;
						}
					}
				}
				if ( $is_jackmail_page ) {
					$this->core->display_notice_lte_ie9( __( 'You\'re currently using an older version of Internet Explorer. Update your browser and enjoy all of Jackmail\'s features!', 'jackmail-newsletters' ) );
					$this->core->display_notice_noscript( __( 'Javascript is currently disabled in your browser. Enable it and enjoy all of Jackmail\'s features!', 'jackmail-newsletters' ) );
					
					if ( $this->core->is_authenticated() && get_option( 'jackmail_authentification_failed' ) === '1' ) {
						if ( ! $this->core->generate_token() ) {
							$this->core->display_notice( __( 'Please', 'jackmail-newsletters' ) . ' <a onclick="jackmail_reconnect( event )" href="#">' . __( 'log in again', 'jackmail-newsletters' ) . '</a>' );
							echo '
							<script>
								function jackmail_reconnect( event ) {
									var scope = angular.element(\'.jackmail_angular\').scope();
									scope.$parent.show_account_connection_popup = true;
									event.preventDefault();
								}
							</script>';
						}
					}
					if ( ! $this->core->openssl_random_pseudo_bytes_function_exists() ) {
						$this->core->display_notice( __( 'Please activate the extension "openssl_random_pseudo_bytes" on your web server', 'jackmail-newsletters' ) );
					}
					if ( ! $this->core->gzdecode_gzencode_function_exists() ) {
						$this->core->display_notice( __( 'The Php features "gzdecode" or "gzencode" were not found', 'jackmail-newsletters' ) );
					}
					if ( ! $this->core->base64_decode_base64_encode_function_exists() ) {
						$this->core->display_notice( __( 'The Php features "base64_encode" or "base64_decode" were not found', 'jackmail-newsletters' ) );
					}
					if ( ! $this->core->json_encode_json_decode_function_exists() ) {
						$this->core->display_notice( __( 'The Php features "json_encode" or "json_decode" were not found', 'jackmail-newsletters' ) );
					}
					if ( ! $this->core->image_create_from_string_get_image_size_from_string_function_exists() ) {
						$this->core->display_notice( __( 'The Php features "imagecreatefromstring" or "getimagesizefromstring" were not found', 'jackmail-newsletters' ) );
					}
					$jackmail_file_path = $this->core->get_jackmail_file_path();
					if ( ! is_writable( $jackmail_file_path ) ) {
						$this->core->display_notice( __( 'The file', 'jackmail-newsletters' ) . ' "uploads/jackmail-' . get_option( 'jackmail_file_path' ) . '" ' . __( 'should be editable.', 'jackmail-newsletters' ) );
					}
				}
			}
			if ( ! $is_jackmail_page ) {
				if ( $is_dashboard_page ) {
					$notifications_messages = get_option( 'jackmail_notifications_messages' );
					if ( is_array( $notifications_messages ) ) {
						if ( count( $notifications_messages ) > 0 ) {
							echo "
							<script>
								function jackmail_hide_notification( notification_id ) {
								    jQuery( '#jackmail_notification_' + notification_id ).parent().parent().hide();
								    var action = 'jackmail_hide_notification';
								    var data_parameters = {
										'action': action,
										'key': jackmail_ajax_object.key,
										'nonce': jackmail_ajax_object.urls[ action ],
										'notification_id': notification_id
									};
									jQuery.post( jackmail_ajax_object.ajax_url, data_parameters, function () {
									} );
								}
							</script>";
						}
						$notifications_messages_hide = get_option( 'jackmail_notifications_messages_hidden' );
						foreach ( $notifications_messages as $notification ) {
							if ( isset( $notification['notification_id'], $notification['notification'] ) ) {
								if ( ! in_array( $notification['notification_id'], $notifications_messages_hide ) ) {
									$this->core->display_notice( $notification['notification'], '', 'jackmail_notification_' . $notification['notification_id'], 'jackmail_hide_notification( ' . $notification['notification_id'] . ' )' );
								}
							}
						}
					}
				}
				if ( $is_dashboard_page || $is_extensions_page ) {
					$update_available = $this->core->get_jackmail_update_available();
					if ( $update_available['update'] ) {
						$this->core->display_notice( __( 'A new version of Jackmail is available.', 'jackmail-newsletters' ) . ' <a href="plugins.php?jackmail">' . __( 'Update', 'jackmail-newsletters' ) . '</a>' );
					}
				}
			}
			if ( get_option( 'jackmail_display_premium_notification' ) === '1' ) {
				if ( $is_jackmail_page || $is_dashboard_page || $is_extensions_page ) {
					$message = '<span class="jackmail_title">' . __( 'Help Jackmail, become premium!', 'jackmail-newsletters' ) . '</span>';
					$message .= '<br/><br/>' . __( 'You want to send more than 100 emails at once?', 'jackmail-newsletters' );
					$message .= '<br/>' . __( 'With our <a href="https://www.jackmail.com/pricing" target="_blank">premium plans</a>, send as much emails as you want!', 'jackmail-newsletters' );
					$message .= '<br/><br/>' . '<a href="https://www.jackmail.com/pricing" target="_blank" class="jackmail_button">' . __( 'Become premium!', 'jackmail-newsletters' ) . '</a>';
					$message .= ' <span class="jackmail_connect_account jackmail_m_l_10" onclick="jackmail_hide_premium_notification()">' . __( 'Not now', 'jackmail-newsletters' ) . '</span>';
					$this->core->display_notice( $message, 'jackmail', 'jackmail_premium_notification', '', false, false, 166 );
					echo "
					<script>
						function jackmail_hide_premium_notification() {
						    jQuery( '#jackmail_premium_notification' ).parent().parent().hide();
						    var action = 'jackmail_hide_premium_notification';
						    var data_parameters = {
								'action': action,
								'key': jackmail_ajax_object.key,
								'nonce': jackmail_ajax_object.urls[ action ]
							};
							jQuery.post( jackmail_ajax_object.ajax_url, data_parameters, function () {
							} );
						}
					</script>";
				}
			}
		}
	}

}