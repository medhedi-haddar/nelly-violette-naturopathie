<?php


class Jackmail_List_Connectors_Core {

	protected function init_connectors( $action, $connector_key, $email, $new_email ) {
		$id_list = 0;
		$message = 'Wrong parameters';
		if ( get_option( 'jackmail_connectors' ) === '1' && $this->ip_autorized() ) {
			if ( $action === 'add' || $action === 'update' || $action === 'delete' ) {
				$id_list = $this->get_id_list( $connector_key );
				if ( $id_list !== false ) {
					if ( $action === 'add' || $action === 'delete' ) {
						if ( $email !== false ) {
							if ( is_email( $email ) ) {
								if ( $action === 'add' ) {
									$message = $this->add_contact( $id_list, $email );
								} else if ( $action === 'delete' ) {
									$message = $this->delete_contact( $id_list, $email );
								}
							} else {
								$message = 'Email isn\'t valid';
							}
						}
					} else if ( $action === 'update' ) {
						if ( $email !== false && $new_email !== false ) {
							if ( is_email( $email ) && is_email( $new_email ) ) {
								if ( $email !== $new_email ) {
									$message = $this->update_contact( $id_list, $email, $new_email );
								} else {
									$message = 'Current email and new email are equals';
								}
							} else {
								$message = 'Current email or new email isn\'t valid';
							}
						}
					}
				}
			}
		}
		return $this->get_status_message( $action, $id_list, $message );
	}

	protected function get_status_message( $action, $id_list, $message ) {
		$data = array();
		if ( $message !== 'ok' ) {
			$data['success'] = false;
			$data['message'] = $message;
			status_header( 404 );
		} else {
			$data['success'] = true;
			if ( $action === 'add' || $action === 'update' || $action === 'delete' ) {
				$this->core->updated_list_contact( $id_list );
			}
		}
		return $data;
	}

	protected function ip_autorized() {
		if ( get_option( 'jackmail_connectors_ip_restriction' ) === '1' ) {
			$ip = '';
			if ( getenv( 'HTTP_CLIENT_IP' ) ) {
				$ip = getenv( 'HTTP_CLIENT_IP' );
			} else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
				$ip = getenv( 'HTTP_X_FORWARDED_FOR' );
			} else if ( getenv( 'HTTP_X_FORWARDED' ) ) {
				$ip = getenv( 'HTTP_X_FORWARDED' );
			} else if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
				$ip = getenv( 'HTTP_FORWARDED_FOR' );
			} else if ( getenv( 'HTTP_FORWARDED' ) ) {
				$ip = getenv( 'HTTP_FORWARDED' );
			} else if ( getenv( 'REMOTE_ADDR' ) ) {
				$ip = getenv( 'REMOTE_ADDR' );
			}
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				$allowed_ips = $this->core->explode_data( get_option( 'jackmail_connectors_allowed_ips' ) );
				$ip_found    = array_search( $ip, $allowed_ips );
				if ( $ip_found !== false && filter_var( $allowed_ips [ $ip_found ], FILTER_VALIDATE_IP ) ) {
					return true;
				}
			}
			return false;
		}
		return true;
	}

	protected function get_id_list( $connector_key ) {
		global $wpdb;
		if ( $this->core->str_len( $connector_key ) >= 100 ) {
			$sql    = "
			SELECT `id`, `connector_activation_date_gmt`, `connector_used_date_gmt`
			FROM `{$wpdb->prefix}jackmail_lists`
			WHERE `connector_key` = %s
			AND `type` = ''
			AND `connector_activation_date_gmt` != '0000-00-00 00:00:00'";
			$result = $wpdb->get_row( $wpdb->prepare( $sql, $connector_key ) );
			if ( isset( $result->id ) ) {
				$id_list                       = $result->id;
				$connector_activation_date_gmt = $result->connector_activation_date_gmt;
				$connector_used_date_gmt       = $result->connector_used_date_gmt;
				if ( $connector_used_date_gmt === '0000-00-00 00:00:00' ) {
					$connector_activation_timestamp = strtotime( $connector_activation_date_gmt );
					$current_time_gmt               = $this->core->get_current_time_gmt_sql();
					$current_timestamp              = strtotime( $current_time_gmt );
					if ( $current_timestamp - $connector_activation_timestamp > 2500000 ) {
						return '0';
					} else {
						$sql = "
						UPDATE `{$wpdb->prefix}jackmail_lists`
						SET `connector_used_date_gmt` = %s
						WHERE `id` = %s
						AND `type` = ''
						AND `connector_activation_date_gmt` != '0'";
						$wpdb->query( $wpdb->prepare( $sql, $current_time_gmt, $id_list ) );
						update_option( 'jackmail_connectors_errors', '0' );
					}
				}
				if ( $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
					return $id_list;
				} else {
					return false;
				}
			}
		}
		$errors = ( int ) get_option( 'jackmail_connectors_errors' ) + 1;
		update_option( 'jackmail_connectors_errors', $errors );
		if ( $errors >= 500000 ) {
			update_option( 'jackmail_connectors', '0' );
		}
		return false;
	}

	protected function add_contact( $id_list, $email ) {
		global $wpdb;
		$sql    = "
		SELECT `fields`
		FROM `{$wpdb->prefix}jackmail_lists`
		WHERE `id` = %s";
		$result = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
		if ( isset( $result->fields ) ) {
			$fields   = $this->core->explode_fields( $result->fields );
			$requests = array();
			foreach ( $_REQUEST as $key => $request ) {
				if ( $key !== 'jackmail' && $key !== 'list' && $key !== 'action' && $key !== 'email' ) {
					$requests[ $this->core->str_to_upper( str_replace( '_', ' ', $key ) ) ] = $this->core->request_text_data( $request );
					
				}
			}
			$sql_header_fields    = array();
			$sql_header_values    = array();
			$sql_values           = array();
			$sql_header_fields[]  = '`email`';
			$sql_header_values[]  = '%s';
			$sql_values[]         = $email;
			$do_action            = array();
			$do_action['id_list'] = $id_list;
			$do_action['email']   = $email;
			foreach ( $requests as $key => $value ) {
				$field_id = array_search( $key, $fields );
				if ( $field_id !== false ) {
					$sql_header_fields[] = '`field' . ( $field_id + 1 ) . '`';
					$sql_header_values[] = '%s';
					$sql_values[]        = $value;
					$do_action[ $key ]   = $value;
				}
			}
			if ( count( $sql_header_fields ) > 0 && count( $sql_header_values ) > 0 && count( $sql_values ) > 0 ) {
				$sql_header_fields = implode( ', ', $sql_header_fields );
				$sql_header_values = implode( ', ', $sql_header_values );
				$sql               = "
				INSERT IGNORE INTO `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` ( {$sql_header_fields} ) VALUES ( {$sql_header_values} )";
				$result            = $wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
				if ( $result > 0 ) {
					do_action( 'jackmail_connector_contact_added', $do_action );
					return 'ok';
				}
			}
		}
		return 'Email already exists';
	}

	protected function update_contact( $id_list, $email, $new_email ) {
		global $wpdb;
		$sql    = "
		SELECT `fields`
		FROM `{$wpdb->prefix}jackmail_lists`
		WHERE `id` = %s";
		$result = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
		if ( isset( $result->fields ) ) {
			$fields   = $this->core->explode_fields( $result->fields );
			$requests = array();
			foreach ( $_REQUEST as $key => $request ) {
				if ( $key !== 'jackmail' && $key !== 'list' && $key !== 'action' && $key !== 'email' && $key !== 'new_email' ) {
					$requests[ $this->core->str_to_upper( str_replace( '_', ' ', $key ) ) ] = $this->core->request_text_data( $request );
				}
			}
			$sql_header_fields      = array();
			$sql_values             = array();
			$sql_header_fields[]    = '`email` = %s';
			$sql_values[]           = $new_email;
			$do_action              = array();
			$do_action['id_list']   = $id_list;
			$do_action['email']     = $email;
			$do_action['new_email'] = $new_email;
			foreach ( $requests as $key => $value ) {
				$field_id = array_search( $key, $fields );
				if ( $field_id !== false ) {
					$sql_header_fields[] = '`field' . ( $field_id + 1 ) . '` = %s';
					$sql_values[]        = $value;
					$do_action[ $key ]   = $value;
				}
			}
			$sql_values[] = $email;
			if ( count( $sql_header_fields ) > 0 && count( $sql_values ) > 0 ) {
				$sql_header_fields = implode( ', ', $sql_header_fields );
				$sql               = "
				UPDATE IGNORE `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}`
				SET {$sql_header_fields}
				WHERE `email` = %s";
				$result            = $wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
				if ( $result > 0 ) {
					do_action( 'jackmail_connector_contact_updated', $do_action );
					return 'ok';
				} else {
					$sql         = "
					SELECT COUNT( * )
					FROM `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}`
					WHERE `email` = %s";
					$nb_contacts = $wpdb->get_var( $wpdb->prepare( $sql, $email ) );
					if ( $nb_contacts === '0' ) {
						return 'Current email doesn\'t exist';
					}
					if ( $email !== $new_email ) {
						$nb_contacts = $wpdb->get_var( $wpdb->prepare( $sql, $new_email ) );
						if ( $nb_contacts > 0 ) {
							return 'New email already exists';
						}
					}
					return 'No contact changes';
				}
			}
		}
		return 'Current email doesn\'t exists';
	}

	protected function delete_contact( $id_list, $email ) {
		global $wpdb;
		$delete_return = $this->core->delete_list_contact( $id_list, array(
			'email' => $email
		) );
		if ( $delete_return !== false && $wpdb->rows_affected > 0 ) {
			$do_action            = array();
			$do_action['id_list'] = $id_list;
			$do_action['email']   = $email;
			do_action( 'jackmail_connector_contact_deleted', $do_action );
			return 'ok';
		}
		return 'Email doesn\'t found';
	}

	protected function display_connectors( $id_list ) {
		$this->core->update_list( array(
			'connector_activation_date_gmt' => $this->core->get_current_time_gmt_sql()
		), array(
			'id'   => $id_list,
			'type' => ''
		) );
		return array(
			'active' => ( bool ) get_option( 'jackmail_connectors' )
		);
	}

	protected function connectors_configuration() {
		return array(
			'active'         => ( bool ) get_option( 'jackmail_connectors' ),
			'ip_restriction' => ( bool ) get_option( 'jackmail_connectors_ip_restriction' ),
			'allowed_ips'    => get_option( 'jackmail_connectors_allowed_ips', '' )
		);
	}

	protected function connectors_configure( $active ) {
		update_option( 'jackmail_connectors', $active );
		update_option( 'jackmail_connectors_errors', '0' );
	}

	protected function connectors_configure_ip_restriction( $ip_restriction ) {
		update_option( 'jackmail_connectors_ip_restriction', $ip_restriction );
	}

	protected function connectors_configure_allowed_ips( $allowed_ips ) {
		$new_allowed_ips = array();
		foreach ( $allowed_ips as $allowed_ip ) {
			if ( filter_var( $allowed_ip, FILTER_VALIDATE_IP ) ) {
				if ( ! in_array( $allowed_ip, $new_allowed_ips ) ) {
					$new_allowed_ips[] = $allowed_ip;
				}
			}
		}
		$new_allowed_ips     = $this->core->implode_data( $new_allowed_ips );
		$current_allowed_ips = get_option( 'jackmail_connectors_allowed_ips', '' );
		$success             = false;
		if ( $new_allowed_ips !== $current_allowed_ips ) {
			update_option( 'jackmail_connectors_allowed_ips', $new_allowed_ips );
			$success = true;
		}
		return array(
			'success'     => $success,
			'allowed_ips' => $new_allowed_ips
		);
	}

}