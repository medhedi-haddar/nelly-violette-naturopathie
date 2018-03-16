<?php


class Jackmail_Lists_Core extends Jackmail_List_And_Campaign_Common_Core {

	protected function get_lists() {
		global $wpdb;
		$this->core->progress_campaigns();
		$this->core->progress_contacts_blacklist();
		$this->actualize_plugins_lists();
		$sql   = "
		SELECT `l`.`id`, `l`.`name`, `l`.`id_campaign`, `l`.`nb_contacts`, `l`.`created_date_gmt`, `l`.`type`, IF( `s`.`id`, '1', '0' ) AS `in_scenario`
		FROM `{$wpdb->prefix}jackmail_lists` AS `l`
		LEFT JOIN `{$wpdb->prefix}jackmail_scenarios` AS `s` ON `l`.`id` = `s`.`id_lists`
		GROUP BY `l`.`id`
		ORDER BY `l`.`id` DESC";
		$lists = $wpdb->get_results( $sql );
		if ( count( $lists ) > 0 ) {
			$widgets_id_list = $this->widgets_id_list();
			$id_lists        = array();
			foreach ( $lists as $list ) {
				$id_lists[] = $this->core->get_campaign_id_list( $list->id, $list->id_campaign );
			}
			$lists_statistics = array();
			if ( count( $id_lists ) > 0 ) {
				$url      = $this->core->get_jackmail_url_analytics() . 'lists';
				$headers  = array(
					'content-type' => 'application/json',
					'x-auth-token' => $this->core->get_account_token(),
					'accountId'    => $this->core->get_account_id(),
					'userId'       => $this->core->get_user_id()
				);
				$body     = array(
					'beginInterval' => $this->core->get_iso_date( date( 'Y-m-d H:i:s', strtotime( '-1 day', strtotime( get_option( 'jackmail_install_date' ) ) ) ) ),
					'endInterval'   => $this->core->get_iso_date( date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ) ),
					'listIds'       => $id_lists
				);
				$timeout  = 30;
				$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
				if ( is_array( $response ) ) {
					if ( isset( $response['body'] ) ) {
						$results = json_decode( $response['body'], true );
						if ( is_array( $results ) ) {
							$lists_statistics = $results;
						}
					}
				}
			}
			$searched_lists_ids = array();
			$search             = '';
			if ( isset( $_POST['search'] ) ) {
				$search = $this->core->request_text_data( $_POST['search'] );
			}
			foreach ( $lists as $key => $list ) {
				$lists[ $key ]->in_scenario = (bool) $lists[ $key ]->in_scenario;
				$id_list                    = $list->id;
				if ( $this->is_plugin_special_list( $list->nb_contacts, $list->type ) ) {
					$plugin = $this->core->explode_data( $list->type );
					if ( isset( $plugin['name'], $plugin['id'] ) ) {
						$plugin_name = $plugin['name'];
						$plugin_id   = $plugin['id'];
						if ( $search === '' ) {
							$lists[ $key ]->nb_contacts = $this->get_plugin_list_nb_contacts( $plugin_name, $id_list, $plugin_id );
						} else {
							$plugin_list = $this->get_plugin_list2(
								$plugin_name, '0', '1', $plugin_id, $list, 'email', 'ASC', $search
							);
							if ( isset( $plugin_list['nb_contacts'], $plugin_list['nb_contacts_search'] ) ) {
								$lists[ $key ]->nb_contacts = $plugin_list['nb_contacts'];
								if ( $plugin_list['nb_contacts_search'] !== '0' ) {
									$searched_lists_ids[] = $id_list;
								}
							}
						}
					}
				} else {
					if ( $search !== '' ) {
						$list_data = $this->get_list_data(
							$id_list, '0', '1', 'email', 'ASC', $search, '[]'
						);
						if ( $list_data['nb_contacts_search'] !== '0' ) {
							$searched_lists_ids[] = $id_list;
						}
					}
				}
				$lists[ $key ]->in_widget            = in_array( $id_list, $widgets_id_list );
				$lists[ $key ]->opens_percent        = '0';
				$lists[ $key ]->clicks_percent       = '0';
				$lists[ $key ]->unsubscribes_percent = '0';
				$list_id                             = $this->core->get_campaign_id_list( $list->id, $list->id_campaign );
				foreach ( $lists_statistics as $list_statistics ) {
					if ( isset( $list_statistics['listId'] ) ) {
						if ( $list_statistics['listId'] === $list_id ) {
							if ( $this->core->is_statistics_v2() ) {
								if ( isset( $list_statistics['nbOpen'], $list_statistics['nbHit'], $list_statistics['nbUnsubscribe'] ) ) {
									$lists[ $key ]->opens_percent        = round( ( $list_statistics['nbOpen'] / $list->nb_contacts * 100 ), 2 );
									$lists[ $key ]->clicks_percent       = round( ( $list_statistics['nbHit'] / $list->nb_contacts * 100 ), 2 );
									$lists[ $key ]->unsubscribes_percent = round( ( $list_statistics['nbUnsubscribe'] / $list->nb_contacts * 100 ), 2 );
								}
							} else {
								if ( isset( $list_statistics['opens'], $list_statistics['hits'], $list_statistics['unsubscribe'] ) ) {
									$lists[ $key ]->opens_percent        = round( ( $list_statistics['opens'] / $list->nb_contacts * 100 ), 2 );
									$lists[ $key ]->clicks_percent       = round( ( $list_statistics['hits'] / $list->nb_contacts * 100 ), 2 );
									$lists[ $key ]->unsubscribes_percent = round( ( $list_statistics['unsubscribe'] / $list->nb_contacts * 100 ), 2 );
								}
							}
							break;
						}
					}
				}
			}
			if ( $search !== '' ) {
				$searched_lists = array();
				foreach ( $lists as $key => $list ) {
					if ( in_array( $list->id, $searched_lists_ids ) ) {
						$searched_lists[] = $lists[ $key ];
					}
				}
				$lists = $searched_lists;
			}
		}
		return $lists;
	}

	private function widgets_id_list() {
		$widgets         = get_option( 'widget_jackmail_widget' );
		$widgets_id_list = array();
		if ( is_array( $widgets ) ) {
			foreach ( $widgets as $key => $widget ) {
				if ( is_array( $widget ) ) {
					if ( isset( $widget['id_list'] ) ) {
						$widgets_id_list[] = $widget['id_list'];
					}
				}
			}
		}
		return $widgets_id_list;
	}

	protected function plugins_events() {
		$actual_plugins_array = $this->core->get_jackmail_plugins();
		if ( count( $actual_plugins_array ) > 0 ) {
			$plugin = 'bloom';
			if ( $this->is_selected_plugin( $plugin, $actual_plugins_array ) ) {
				add_action( 'wp_ajax_bloom_subscribe', array( $this, 'bloom_submit_form' ), 1 );
				add_action( 'wp_ajax_nopriv_bloom_subscribe', array( $this, 'bloom_submit_form' ), 1 );
			}
			$plugin = 'contactform7';
			if ( $this->is_selected_plugin( $plugin, $actual_plugins_array ) ) {
				
				add_action( 'wpcf7_mail_sent', array( $this, 'cf7_submit_form' ), 20 );
			}
		}
	}

	public function bloom_submit_form() {
		global $wpdb;
		try {
			if ( $this->core->json_encode_json_decode_function_exists() ) {
				if ( isset( $_POST['subscribe_nonce'], $_POST['subscribe_data_array'] ) ) {
					$nonce = $this->core->request_text_data( $_POST['subscribe_nonce'] );
					if ( wp_verify_nonce( $nonce, 'subscribe' ) ) {
						$data = $this->core->request_text_data( $_POST['subscribe_data_array'] );
						$data = json_decode( $data, true );
						if ( isset( $data['email'], $data['optin_id'] ) ) {
							$email = $data['email'];
							if ( is_email( $email ) ) {
								$form_id   = substr( $data['optin_id'], 6 );
								$name      = '';
								$last_name = '';
								if ( isset( $data['name'] ) ) {
									$name = $data['name'];
								}
								if ( isset( $data['last_name'] ) ) {
									$last_name = $data['last_name'];
								}
								$id_list             = $this->get_list( 'bloom', $form_id );
								$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
								if ( $this->core->check_table_exists( $table_list_contacts, false ) ) {
									$sql_values = array( $email, $name, $last_name );
									$sql        = "REPLACE INTO `$table_list_contacts` (`email`, `field1`, `field2`) VALUES (%s, %s, %s)";
									$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
									$this->core->updated_list_contact( $id_list );
								}
							}
						}
					}
				}
			}
		} catch ( Exception $e ) {

		}
	}

	public function cf7_submit_form( $data ) {
		global $wpdb;
		try {
			if ( $this->core->mb_string_function_exists() && $this->core->json_encode_json_decode_function_exists() ) {
				if ( method_exists( $data, 'id' ) && method_exists( $data, 'prop' ) ) {
					$form_id = $data->id();
					$form    = $data->prop( 'form' );
					$email   = '';
					$fields  = array();
					$types   = array( 'text', 'email', 'url', 'tel', 'number', 'date' );
					foreach ( $_POST as $key => $value ) {
						$value       = $this->core->request_text_data( $value );
						$keyposition = strpos( $form, $key );
						if ( $keyposition !== false ) {
							$begin = strrpos( substr( $form, 0, $keyposition ), '[' ) + 1;
							$end   = $keyposition - $begin;
							$type  = str_replace( ' ', '', substr( $form, $begin, $end ) );
							$type  = str_replace( '*', '', $type );
							if ( $type === 'email' && $email === '' && is_email( $value ) ) {
								$email = $value;
							} else if ( in_array( $type, $types ) ) {
								$fields[ $this->core->str_to_upper( $key ) ] = $value;
							}
						}
					}
					$fields['SUBMITTED-DATE-GMT'] = $this->core->get_current_time_gmt_sql();
					if ( $email !== '' ) {
						if ( $this->core->check_table_exists( 'jackmail_lists_contacts_cf7_data' ) ) {
							$sql_values = array( $form_id, $email, $this->core->json_encode( $fields ) );
							$sql        = "REPLACE INTO `{$wpdb->prefix}jackmail_lists_contacts_cf7_data` VALUES (%s, %s, %s)";
							$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
						}
					}
				}
			}
		} catch ( Exception $e ) {

		}
	}

	protected function cron_progress_contacts_blacklist() {
		$this->core->get_jackmail_update_available();
		$this->core->progress_contacts_blacklist();
	}

	protected function cron_actualize_plugins_lists() {
		$this->core->get_jackmail_update_available();
		$this->actualize_plugins_lists();
	}

}