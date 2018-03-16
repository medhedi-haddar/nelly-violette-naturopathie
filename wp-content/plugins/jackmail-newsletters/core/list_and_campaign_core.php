<?php


class Jackmail_List_And_Campaign_Core extends Jackmail_List_And_Campaign_Common_Core {

	protected function list_export_contacts_selection( $id, $begin, $search, $targeting_rules, $contacts_selection ) {
		global $wpdb;
		$table_lists         = "{$wpdb->prefix}jackmail_lists";
		$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id}";
		return $this->export_contacts_selection( $id, $begin, $search, $targeting_rules, $contacts_selection, $table_lists, $table_list_contacts );
	}

	protected function campaign_export_contacts_selection( $id, $begin, $search, $targeting_rules, $contacts_selection ) {
		global $wpdb;
		$table_lists         = "{$wpdb->prefix}jackmail_campaigns";
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id}";
		return $this->export_contacts_selection( $id, $begin, $search, $targeting_rules, $contacts_selection, $table_lists, $table_list_contacts );
	}

	private function export_contacts_selection( $id, $begin, $search, $targeting_rules, $contacts_selection, $table_lists, $table_list_contacts ) {
		global $wpdb;
		$list         = null;
		$contacts     = array();
		$sql          = "
		SELECT `fields`
		FROM `{$table_lists}`
		WHERE `id` = %s";
		$list         = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );
		$contacts_sql = $this->get_list_or_get_campaign_contacts_sql(
			$table_list_contacts, 'ALL_FILTERED', '', '', '', $search, $targeting_rules,
			$contacts_selection, 'NOT_SELECTED'
		);
		if ( isset( $contacts_sql['sql_contacts'], $contacts_sql['sql_values'] ) ) {
			$sql_contacts   = $contacts_sql['sql_contacts'];
			$sql_values     = $contacts_sql['sql_values'];
			$sql_values[]   = (int) $begin;
			$continue_limit = $this->core->export_send_limit();
			$contacts       = $wpdb->get_results( $wpdb->prepare( $sql_contacts . ' LIMIT %d, ' . $continue_limit, $sql_values ) );
		}
		return array(
			'list'     => $list,
			'contacts' => $contacts
		);
	}

	protected function list_import_contacts( $id, $field_separator, $email_position, $contacts ) {
		$type           = 'list';
		$contacts       = str_getcsv( $contacts, "\n" );
		$blacklist_type = 'normal';
		$this->import_contacts( $id, $type, $field_separator, $email_position, $contacts, $blacklist_type );
	}

	protected function campaign_import_contacts( $id, $field_separator, $email_position, $contacts ) {
		$type           = 'campaign';
		$contacts       = str_getcsv( $contacts, "\n" );
		$blacklist_type = 'normal';
		$this->import_contacts( $id, $type, $field_separator, $email_position, $contacts, $blacklist_type );
	}

	private function import_contacts( $id, $type, $field_separator, $email_position, Array $contacts, $blacklist_type = 'normal' ) {
		global $wpdb;
		$email_position = (int) $email_position;
		$where_id       = $id;
		if ( $type === 'campaign' ) {
			$table_lists         = "{$wpdb->prefix}jackmail_campaigns";
			$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id}";
		} else {
			$table_lists         = "{$wpdb->prefix}jackmail_lists";
			$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id}";
		}
		if ( $this->core->check_table_exists( $table_list_contacts, false ) ) {
			$sql_content     = array();
			$sql_values      = array();
			$first_is_header = true;
			$nb_contacts     = count( $contacts );
			$sql             = "SELECT `fields` FROM `{$table_lists}` WHERE `id` = %s";
			$list            = $wpdb->get_row( $wpdb->prepare( $sql, $where_id ) );
			if ( isset( $list->fields ) ) {
				$has_blacklist_column     = false;
				$header_columns           = $this->core->explode_fields( $list->fields );
				$new_header_columns_array = $header_columns;
				$nb_header_columns        = count( $header_columns );
				$columns_positions        = array();
				$used_column_positions    = array();
				$columns_indices          = array();
				$nb_affected              = 0;
				$columns                  = $this->core->get_table_columns( $table_list_contacts, false );
				foreach ( $contacts as $key => $contact ) {
					if ( $key === 0 ) {
						$file_header            = $this->str_getcsv( $contact, $field_separator );
						$nb_file_header_columns = count( $file_header );
						if ( $nb_file_header_columns === 0 ) {
							break;
						}
						if ( $nb_file_header_columns > 12 ) {
							$nb_file_header_columns = 12;
						}
						if ( isset( $file_header[ $email_position ] ) ) {
							if ( is_email( $file_header[ $email_position ] ) ) {
								$file_header[ $email_position ] = 'EMAIL';
								$first_is_header                = false;
							}
							if ( $blacklist_type === 'normal' ) {
								$blacklist_position = array_search( 'BLACKLIST', $file_header );
								if ( $blacklist_position !== false ) {
									$has_blacklist_column = true;
								}
							}
							for ( $i = 0; $i < $nb_file_header_columns; $i ++ ) {
								
								if ( $i !== $email_position ) {
									if ( $first_is_header ) {
										$field = $this->core->str_to_upper( $file_header[ $i ] );
										if ( $field === 'EMAIL' ) {
											continue;
										}
										if ( $field === 'BLACKLIST' ) {
											continue;
										}
									} else {
										$field = '( ' . $i . ' )';
									}
									$column_position = - 1;
									foreach ( $header_columns as $key2 => $header_column ) {
										if ( $header_column === $field ) {
											$column_position = $key2;
										}
									}
									if ( $column_position === - 1 ) {
										if ( ! in_array( $field, $new_header_columns_array ) ) {
											$new_header_columns_array[] = $field;
											$nb_header_columns ++;
											$field_id = 'field' . $nb_header_columns;
											if ( ! in_array( $field_id, $columns ) ) {
												$this->core->create_list_field( $table_list_contacts, $field_id );
												$columns = $this->core->get_table_columns( $table_list_contacts, false );
											}
											$columns_positions[] = $field_id;
											$columns_indices[]   = $i;
										}
									} else {
										if ( ! in_array( $column_position, $used_column_positions ) ) {
											$field_id = 'field' . ( $column_position + 1 );
											if ( ! in_array( $field_id, $columns_positions ) ) {
												$columns_positions[]     = $field_id;
												$used_column_positions[] = $column_position;
												$columns_indices[]       = $i;
											}
										}
									}
								}
							}
						}
						if ( $has_blacklist_column || $blacklist_type !== 'normal' ) {
							$blacklist_types = $this->core->get_blacklist_types();
						}
						$nb_file_header_columns = count( $columns_indices );
						$sql_headers_fields     = array();
						$sql_headers_fields[]   = '`email`';
						if ( $has_blacklist_column || $blacklist_type !== 'normal' ) {
							$sql_headers_fields[] = '`blacklist`';
						}
						$sql_content_template   = array();
						$sql_content_template[] = '%s';
						if ( $has_blacklist_column || $blacklist_type !== 'normal' ) {
							$sql_content_template[] = '%s';
						}
						$sql_headers_duplicate   = array();
						$sql_headers_duplicate[] = '`email` = VALUES( `email` )';
						if ( $has_blacklist_column || $blacklist_type !== 'normal' ) {
							$sql_headers_duplicate[] = '`blacklist` = VALUES( `blacklist` )';
						}
						foreach ( $columns_positions as $field_id ) {
							$sql_headers_fields[]    = '`' . $field_id . '`';
							$sql_content_template[]  = '%s';
							$sql_headers_duplicate[] = '`' . $field_id . '` = VALUES( `' . $field_id . '` )';
						}
						$sql_content_template = '( ' . implode( ',', $sql_content_template ) . ' )';
					}
					if ( ! $first_is_header || $key > 0 ) {
						$current_contact = $this->str_getcsv( $contact, $field_separator );
						if ( isset( $current_contact[ $email_position ] ) ) {
							if ( is_email( $current_contact[ $email_position ] ) ) {
								$current_contact[ $email_position ] = $this->core->str_to_lower( $current_contact[ $email_position ] );
								$sql_values[]                       = $current_contact[ $email_position ];
								if ( $has_blacklist_column ) {
									if ( isset( $current_contact[ $blacklist_position ] ) ) {
										if ( $current_contact[ $blacklist_position ] === 'bounce' ) {
											$sql_values[] = $blacklist_types['bounces'];
										} else if ( $current_contact[ $blacklist_position ] === 'complaint' ) {
											$sql_values[] = $blacklist_types['complaints'];
										} else if ( $current_contact[ $blacklist_position ] === 'unsubscribe' ) {
											$sql_values[] = $blacklist_types['unsubscribes'];
										} else {
											$sql_values[] = '';
										}
									} else {
										$sql_values[] = '';
									}
								} else if ( $blacklist_type !== 'normal' ) {
									if ( in_array( $blacklist_type, array( 'bounces', 'complaints', 'unsubscribes' ) ) ) {
										$sql_values[] = $blacklist_types[ $blacklist_type ];
									} else {
										$sql_values[] = '';
									}
								}
								for ( $i = 0; $i < $nb_file_header_columns; $i ++ ) {
									if ( isset( $columns_indices[ $i ] ) ) {
										$key_id = $columns_indices[ $i ];
										if ( isset( $current_contact[ $key_id ] ) ) {
											$sql_values[] = $current_contact[ $key_id ];
										} else {
											$sql_values[] = '';
										}
									} else {
										$sql_values[] = '';
									}
								}
								$sql_content[] = $sql_content_template;
							}
						}
					}
					if ( ( $key % 1000 === 0 && $key !== 0 ) || ( $key + 1 === $nb_contacts ) ) {
						if ( count( $sql_values ) > 0 && count( $sql_content ) > 0 ) {
							$sql_headers_fields_implode    = implode( ',', $sql_headers_fields );
							$sql_headers_duplicate_implode = implode( ',', $sql_headers_duplicate );
							$sql_content                   = implode( ',', $sql_content );
							$sql                           = "INSERT INTO `{$table_list_contacts}` ({$sql_headers_fields_implode}) VALUES {$sql_content} ON DUPLICATE KEY UPDATE {$sql_headers_duplicate_implode}";
							$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
							$nb_affected = $nb_affected + $wpdb->rows_affected;
							$sql_content = array();
							$sql_values  = array();
						}
					}
				}
				$update = array(
					'fields' => $this->core->implode_fields( $new_header_columns_array )
				);
				$this->core->updated_list_contact_or_campaign_list_contact( $table_lists, $where_id, $update );
				if ( $type === 'list' ) {
					do_action( 'jackmail_list_contacts_imported' );
				}
			}
		}
	}

	protected function list_update_contact_email( $id, $email, $new_email ) {
		global $wpdb;
		$data                = array();
		$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id}";
		$type                = 'list';
		return $this->update_contact_email( $id, $email, $new_email, $table_list_contacts, $type, $data );
	}

	protected function campaign_update_contact_email( $id, $email, $new_email ) {
		global $wpdb;
		$data                = array();
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id}";
		$data['id_list']     = '0';
		$data['blacklist']   = '0';
		$this->check_if_campaign_is_draft( $id );
		$type = 'campaign';
		return $this->update_contact_email( $id, $email, $new_email, $table_list_contacts, $type, $data );
	}

	private function update_contact_email( $id, $email, $new_email, $table_list_contacts, $type, $data ) {
		global $wpdb;
		$json          = array(
			'success' => false,
			'message' => ''
		);
		$data['email'] = $new_email;
		$do_action     = array(
			'email'     => $email,
			'new_email' => $new_email
		);
		if ( is_email( $new_email ) ) {
			if ( $email !== $new_email ) {
				$sql         = "
				SELECT COUNT( * )
				FROM `{$table_list_contacts}`
				WHERE `email` = %s";
				$nb_contacts = $wpdb->get_var( $wpdb->prepare( $sql, $new_email ) );
				if ( $nb_contacts === '0' ) {
					if ( $email === '' ) {
						$insert_return = $this->core->insert_list_contact_or_campaign_list_contact( $table_list_contacts, $data );
						if ( $insert_return !== false ) {
							if ( $type === 'list' ) {
								do_action( 'jackmail_list_contact_email_added', $do_action );
							}
						} else {
							wp_send_json_error();
						}
					} else {
						$update_return = $this->core->update_list_contact_or_campaign_list_contact( $table_list_contacts, $data, array(
							'email' => $email
						) );
						if ( $update_return !== false && $update_return > 0 ) {
							if ( $type === 'list' ) {
								do_action( 'jackmail_list_contact_email_updated', $do_action );
							}
						} else {
							wp_send_json_error();
						}
					}
					if ( $type === 'list' ) {
						$this->core->updated_list_contact( $id );
					} else {
						$this->core->updated_campaign_list_contact( $id );
					}
					wp_send_json_success();
				} else {
					$json ['message'] = __( 'This email already exists in the list', 'jackmail-newsletters' );
				}
			}
		} else {
			$json ['message'] = __( 'A valid email address is required', 'jackmail-newsletters' );
		}
		return $json;
	}

	protected function list_update_contact_field( $id, $email, $field_id, $field ) {
		global $wpdb;
		$table_lists         = "{$wpdb->prefix}jackmail_lists";
		$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id}";
		$type                = 'list';
		return $this->update_contact_field( $id, $email, $field_id, $field, $table_lists, $table_list_contacts, $type );
	}

	protected function campaign_update_contact_field( $id, $email, $field_id, $field ) {
		global $wpdb;
		$table_lists         = "{$wpdb->prefix}jackmail_campaigns";
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id}";
		$this->check_if_campaign_is_draft( $id );
		$type = 'campaign';
		return $this->update_contact_field( $id, $email, $field_id, $field, $table_lists, $table_list_contacts, $type );
	}

	private function update_contact_field( $id, $email, $field_id, $field, $table_lists, $table_list_contacts, $type ) {
		$json = array(
			'success' => false,
			'message' => ''
		);
		if ( is_email( $email ) ) {
			$columns = $this->core->get_table_columns( $table_list_contacts, false );
			if ( in_array( 'field' . $field_id, $columns ) && $email !== '' ) {
				$this->core->update_list_contact_or_campaign_list_contact( $table_list_contacts, array(
					'email'             => $email,
					'field' . $field_id => $field
				), array(
					'email' => $email
				) );
				$do_action = array(
					'email'             => $email,
					'field' . $field_id => $field
				);
				if ( $type === 'list' ) {
					do_action( 'jackmail_list_contact_field_updated', $do_action );
				}
				$this->updated_list_contact_or_campaign_list_contact_time( $table_lists, $id );
				$json ['success'] = true;
			}
		} else {
			$json = array(
				'success' => false,
				'message' => __( 'A valid email address is required', 'jackmail-newsletters' )
			);
		}
		return $json;
	}

	protected function campaign_add_header_column( $id_campaign ) {
		$header_data = $this->get_header_campaign_params( $id_campaign );
		$this->check_if_campaign_is_draft( $id_campaign );
		return $this->add_header_column( $header_data );
	}

	protected function list_add_header_column( $id_list ) {
		$header_data = $this->get_header_list_params( $id_list );
		return $this->add_header_column( $header_data );
	}

	private function add_header_column( $header_data ) {
		$table_lists         = $header_data ['table_lists'];
		$table_list_contacts = $header_data ['table_lists_contacts'];
		$where_id            = $header_data ['where_id'];
		$fields              = $header_data['fields'];
		$fields_array        = $this->core->explode_fields( $fields );
		$nb_fields           = count( $fields_array );
		$field               = 'field' . ( $nb_fields + 1 );
		$columns             = $this->core->get_table_columns( $table_list_contacts, false );
		if ( ! in_array( $field, $columns ) ) {
			$alter_return = $this->core->create_list_field( $table_list_contacts, $field );
			if ( $alter_return !== false ) {
				$fields          = $this->core->explode_fields( $fields );
				$fields_name_try = '( ' . ( $nb_fields + 1 ) . ' )';
				$i               = 1;
				while ( in_array( $fields_name_try, $fields_array ) ) {
					$fields_name_try = '( ' . ( $nb_fields + 1 ) . '-' . $i . ' )';
					$i ++;
				}
				$fields[] = $fields_name_try;
				$fields   = $this->core->implode_fields( $fields );
				$this->updated_list_contact_or_campaign_list_contact_fields( $table_lists, $where_id, $fields );
				return true;
			}
		}
		return false;
	}

	protected function campaign_edit_header_column( $id_campaign, $field_id, $field ) {
		$header_data = $this->get_header_campaign_params( $id_campaign );
		$this->check_if_campaign_is_draft( $id_campaign );
		return $this->edit_header_column( $field_id, $field, $header_data );
	}

	protected function list_edit_header_column( $id_list, $field_id, $field ) {
		$header_data = $this->get_header_list_params( $id_list );
		return $this->edit_header_column( $field_id, $field, $header_data );
	}

	private function edit_header_column( $field_id, $field, $header_data ) {
		$json         = array(
			'message' => '',
			'success' => false
		);
		$table_lists  = $header_data ['table_lists'];
		$where_id     = $header_data ['where_id'];
		$fields       = $header_data['fields'];
		$fields_array = $this->core->explode_fields( $fields );
		if ( ( ! in_array( $field, $fields_array ) || $fields_array[ $field_id - 1 ] === $field ) && $field !== 'EMAIL' && $field !== 'BLACKLIST' ) {
			$fields_array[ $field_id - 1 ] = $field;
			$fields                        = $this->core->implode_fields( $fields_array );
			$this->updated_list_contact_or_campaign_list_contact_fields( $table_lists, $where_id, $fields );
			$json ['success'] = true;
		} else {
			$json ['message'] = __( 'The column name has been used already', 'jackmail-newsletters' );
		}
		return $json;
	}

	protected function campaign_delete_header_column( $id_campaign, $field_id ) {
		$header_data = $this->get_header_campaign_params( $id_campaign );
		$this->check_if_campaign_is_draft( $id_campaign );
		return $this->delete_header_column( $field_id, $header_data );
	}

	protected function list_delete_header_column( $id_list, $field_id ) {
		$header_data = $this->get_header_list_params( $id_list );
		return $this->delete_header_column( $field_id, $header_data );
	}

	private function delete_header_column( $field_id, $header_data ) {
		$table_lists         = $header_data ['table_lists'];
		$table_list_contacts = $header_data ['table_lists_contacts'];
		$where_id            = $header_data ['where_id'];
		$fields_array        = $this->core->explode_fields( $header_data['fields'] );
		$fields              = array();
		foreach ( $fields_array as $key => $field ) {
			$i = strval( $key + 1 );
			if ( $i !== $field_id ) {
				$fields[] = $field;
			}
		}
		$fields       = $this->core->implode_fields( $fields );
		$nb_fields    = count( $fields_array );
		$alter_return = $this->core->delete_list_field( $table_list_contacts, $field_id, $nb_fields );
		if ( $alter_return !== false ) {
			$this->updated_list_contact_or_campaign_list_contact_fields( $table_lists, $where_id, $fields );
			return true;
		}
		return false;
	}

	protected function campaign_delete_contacts_selection( $id, $search, $targeting_rules, $contacts_selection, $contacts_selection_type ) {
		global $wpdb;
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id}";
		$this->check_if_campaign_is_draft( $id );
		$type = 'campaign';
		return $this->delete_contacts_selection(
			$id, $search, $targeting_rules, $contacts_selection, $contacts_selection_type, $table_list_contacts, $type
		);
	}

	protected function list_delete_contacts_selection( $id, $search, $targeting_rules, $contacts_selection, $contacts_selection_type ) {
		global $wpdb;
		$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id}";
		$type                = 'list';
		return $this->delete_contacts_selection(
			$id, $search, $targeting_rules, $contacts_selection, $contacts_selection_type, $table_list_contacts, $type
		);
	}

	private function delete_contacts_selection(
		$id, $search, $targeting_rules, $contacts_selection, $contacts_selection_type, $table_list_contacts, $type
	) {
		global $wpdb;
		$contacts_sql = $this->get_list_or_get_campaign_contacts_sql(
			$table_list_contacts, 'ALL_FILTERED', '', '', '', $search,
			$targeting_rules, $contacts_selection, $contacts_selection_type
		);
		if ( isset( $contacts_sql['sql_delete_contacts'], $contacts_sql['sql_values'] ) ) {
			$sql_delete_contacts = $contacts_sql['sql_delete_contacts'];
			$sql_values          = $contacts_sql['sql_values'];
			if ( count( $sql_values ) === 0 ) {
				$delete = $wpdb->query( $sql_delete_contacts );
			} else {
				$delete = $wpdb->query( $wpdb->prepare( $sql_delete_contacts, $sql_values ) );
			}
			if ( $delete !== false ) {
				if ( $type === 'list' ) {
					$this->core->updated_list_contact( $id );
					do_action( 'jackmail_list_contacts_selection_deleted' );
				} else {
					$this->core->updated_campaign_list_contact( $id );
				}
				return true;
			}
		}
		return false;
	}

	protected function campaign_delete_all_contacts( $id ) {
		global $wpdb;
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id}";
		$this->check_if_campaign_is_draft( $id );
		$this->delete_all_contacts( $table_list_contacts );
		$this->core->updated_campaign_list_contact( $id );
	}

	protected function list_delete_all_contacts( $id ) {
		global $wpdb;
		$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id}";
		$this->delete_all_contacts( $table_list_contacts );
		$this->core->updated_list_contact( $id );
		do_action( 'jackmail_list_contacts_all_deleted' );
	}

	private function delete_all_contacts( $table_list_contacts ) {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE `{$table_list_contacts}`" );
	}

	private function check_if_campaign_is_draft( $id_campaign ) {
		global $wpdb;
		$sql = "
		SELECT COUNT( * )
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `id` = %s
		AND `status` = 'DRAFT'";
		if ( $wpdb->get_var( $wpdb->prepare( $sql, $id_campaign ) ) !== '1' ) {
			$json_error = array(
				'success' => false,
				'message' => __( 'You can\'t edit the campaign.', 'jackmail-newsletters' )
			);
			wp_send_json( $json_error );
		}
	}

	private function updated_list_contact_or_campaign_list_contact_time( $table_lists, $where_id ) {
		return $this->core->updated_list_contact_or_campaign_list_contact( $table_lists, $where_id, array() );
	}

	private function updated_list_contact_or_campaign_list_contact_fields( $table_lists, $where_id, $fields ) {
		$update = array(
			'fields' => $fields
		);
		return $this->core->updated_list_contact_or_campaign_list_contact( $table_lists, $where_id, $update );
	}

	private function get_header_campaign_params( $id_campaign ) {
		global $wpdb;
		$table_lists         = "{$wpdb->prefix}jackmail_campaigns";
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
		$sql                 = "
		SELECT `fields`
		FROM `{$table_lists}`
		WHERE `id` = %s";
		$list                = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		$fields              = '';
		if ( isset( $list->fields ) ) {
			$fields = $list->fields;
		}
		return array(
			'id'                   => $id_campaign,
			'table_lists'          => $table_lists,
			'table_lists_contacts' => $table_list_contacts,
			'where_id'             => $id_campaign,
			'fields'               => $fields
		);
	}

	private function get_header_list_params( $id_list ) {
		global $wpdb;
		$table_lists         = "{$wpdb->prefix}jackmail_lists";
		$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
		$sql                 = "
		SELECT `fields`
		FROM `{$table_lists}`
		WHERE `id` = %s";
		$list                = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
		$fields              = '';
		if ( isset( $list->fields ) ) {
			$fields = $list->fields;
		}
		return array(
			'id'                   => $id_list,
			'table_lists'          => $table_lists,
			'table_lists_contacts' => $table_list_contacts,
			'where_id'             => $id_list,
			'fields'               => $fields
		);
	}

}