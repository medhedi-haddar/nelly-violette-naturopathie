<?php


class Jackmail_List_And_Campaign_Common_Core extends Jackmail_Emailcontent_Common_Core {

	protected function get_list_data( $id_list, $begin, $limit, $sort_by, $sort_order, $search, $targeting_rules ) {//search post => %%
		global $wpdb;
		$sql  = "SELECT * FROM `{$wpdb->prefix}jackmail_lists` WHERE `id` = %s";
		$list = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
		if ( isset( $list->type ) ) {
			if ( $this->is_plugin_special_list( $list->nb_contacts, $list->type ) ) {
				$plugin = $this->core->explode_data( $list->type );
				if ( isset( $plugin['name'], $plugin['id'] ) ) {
					$plugin_name = $plugin['name'];
					$plugin_id   = $plugin['id'];
					return $this->get_plugin_list2( $plugin_name, $begin, $limit, $plugin_id, $list, $sort_by, $sort_order, $search );
				}
			}
			$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
			$contacts            = $this->get_list_or_get_campaign_contacts( $table_list_contacts, $begin, $limit, $sort_by, $sort_order, $search, $targeting_rules );
			if ( isset( $contacts['contacts'], $contacts['nb_contacts'], $contacts['nb_contacts_search'] ) ) {
				return array(
					'list'               => $list,
					'contacts'           => $contacts['contacts'],
					'nb_contacts'        => $contacts['nb_contacts'],
					'nb_contacts_search' => $contacts['nb_contacts_search']
				);
			}
		}
		return array(
			'list'               => null,
			'contacts'           => array(),
			'nb_contacts'        => '0',
			'nb_contacts_search' => '0'
		);
	}

	protected function get_list_or_get_campaign_contacts_post_data() {
		if ( isset( $_POST['id'], $_POST['begin'], $_POST['sort_by'], $_POST['sort_order'], $_POST['search'], $_POST['targeting_rules'] ) ) {
			$id              = $this->core->request_text_data( $_POST['id'] );
			$search          = $this->core->request_text_data( $_POST['search'] );
			$targeting_rules = $this->core->request_text_data( $_POST['targeting_rules'] );
			$sort_by         = $this->core->request_text_data( $_POST['sort_by'] );
			$sort_order      = $this->core->request_text_data( $_POST['sort_order'] );
			if ( $sort_by === '' ) {
				$sort_by = 'email';
			}
			if ( $sort_order !== 'ASC' && $sort_order !== 'DESC' ) {
				$sort_order = 'ASC';
			}
			$begin = $this->core->request_text_data( $_POST['begin'] );
			return array(
				'id'              => $id,
				'begin'           => $begin,
				'sort_by'         => $sort_by,
				'sort_order'      => $sort_order,
				'search'          => $search,
				'targeting_rules' => $targeting_rules
			);
		}
		return array();
	}

	protected function get_list_or_get_campaign_contacts( $table_list_contacts, $begin, $limit, $sort_by, $sort_order, $search, $targeting_rules ) {

		global $wpdb;
		$contacts           = array();
		$nb_contacts        = '0';
		$nb_contacts_search = '0';
		$contacts_sql       = $this->get_list_or_get_campaign_contacts_sql( $table_list_contacts, $begin, $limit, $sort_by, $sort_order, $search, $targeting_rules, '[]', '' );
		if ( isset( $contacts_sql['begin'], $contacts_sql['limit'], $contacts_sql['sql_contacts'], $contacts_sql['sql_nb_contacts'], $contacts_sql['sql_nb_contacts_search'], $contacts_sql['sql_values'] ) && is_array( $contacts_sql['sql_values'] ) ) {
			$begin                  = $contacts_sql['begin'];
			$limit                  = $contacts_sql['limit'];
			$sql_contacts           = $contacts_sql['sql_contacts'];
			$sql_nb_contacts        = $contacts_sql['sql_nb_contacts'];
			$sql_nb_contacts_search = $contacts_sql['sql_nb_contacts_search'];
			$sql_values             = $contacts_sql['sql_values'];
			if ( $begin === 'ALL' ) {
				$continue             = true;
				$continue_limit_begin = 0;
				$continue_limit       = $this->core->export_send_limit();
				while ( $continue ) {
					$contacts_temp        = $wpdb->get_results( $sql_contacts . ' LIMIT ' . $continue_limit_begin . ', ' . $continue_limit );
					$continue_limit_begin = $continue_limit_begin + $continue_limit;
					if ( count( $contacts_temp ) < $continue_limit ) {
						$continue = false;
					}
					$contacts = array_merge( $contacts, $contacts_temp );
				}
				$nb_contacts        = count( $contacts );
				$nb_contacts_search = $nb_contacts;
			} else {
				$nb_contacts = $wpdb->get_var( $sql_nb_contacts );
				if ( count( $sql_values ) === 0 ) {
					$nb_contacts_search = $nb_contacts;
				} else {
					$nb_contacts_search = $wpdb->get_var( $wpdb->prepare( $sql_nb_contacts_search, $sql_values ) );
				}
				if ( $begin !== 'ALL_FILTERED' ) {
					$sql_values[] = intval( $begin );
					$sql_values[] = intval( $limit );
					$contacts     = $wpdb->get_results( $wpdb->prepare( $sql_contacts, $sql_values ) );
				} else {
					if ( count( $sql_values ) === 0 ) {

						$contacts = $wpdb->get_results( $sql_contacts );
					} else {
						$contacts = $wpdb->get_results( $wpdb->prepare( $sql_contacts, $sql_values ) );
					}
				}
			}
		}
		return array(
			'contacts'           => $contacts,
			'nb_contacts'        => $nb_contacts,
			'nb_contacts_search' => $nb_contacts_search
		);
	}

	protected function get_list_or_get_campaign_contacts_sql( $table_list_contacts, $begin, $limit, $sort_by, $sort_order, $search, $targeting_rules, $contacts_selection = '[]', $contacts_selection_type = '' ) {
		global $wpdb;
		$sql_contacts           = '';
		$sql_delete_contacts    = '';
		$sql_nb_contacts        = '';
		$sql_nb_contacts_search = '';
		$sql_values             = array();
		$table_exists           = $this->core->check_table_exists( $table_list_contacts, false );
		if ( $table_exists ) {
			$columns = $this->core->get_table_columns( $table_list_contacts, false );
			if ( $sort_by === '' || ! in_array( $sort_by, $columns ) ) {
				$sort_by = 'email';
			}
			if ( $sort_order !== 'ASC' && $sort_order !== 'DESC' ) {
				$sort_order = 'ASC';
			}
			$sql_contacts        = "SELECT * FROM `{$table_list_contacts}` ORDER BY `{$sort_by}` {$sort_order}";
			$sql_delete_contacts = "TRUNCATE TABLE `{$table_list_contacts}`";
			if ( $begin !== 'ALL' ) {
				$sql_nb_contacts_search = '';
				$sql_values             = array();
				if ( $targeting_rules !== '[]' || $search !== '' || $contacts_selection !== '[]' ) {
					$sql_search_targeting_rules    = '';
					$sql_search_contacts_selection = '';
					$sql_search_fields             = '';
					if ( $targeting_rules !== '[]' ) {
						$targeting_rules = json_decode( $targeting_rules, true );
						if ( ! is_null( $targeting_rules ) ) {
							if ( count( $targeting_rules ) > 0 ) {
								foreach ( $targeting_rules as $key => $targeting_rule ) {
									if ( isset( $targeting_rule['rule_and_or'], $targeting_rule['rule_column'], $targeting_rule['rule_option'], $targeting_rule['rule_content'] ) ) {
										if ( $targeting_rule['rule_and_or'] === 'AND' || $targeting_rule['rule_and_or'] === 'OR' ) {
											if ( $targeting_rule['rule_column'] >= 0 ) {
												if ( $targeting_rule['rule_option'] === '=' || $targeting_rule['rule_option'] === '!=' || $targeting_rule['rule_option'] === 'LIKE' || $targeting_rule['rule_option'] === 'UNSUBSCRIBED' || $targeting_rule['rule_option'] === 'EMPTY' || $targeting_rule['rule_option'] === 'NUMBER>' || $targeting_rule['rule_option'] === 'NUMBER<' || $targeting_rule['rule_option'] === 'DATE>' || $targeting_rule['rule_option'] === 'DATE<' ) {
													if ( $targeting_rule['rule_content'] !== '' || $targeting_rule['rule_option'] === 'EMPTY' || $targeting_rule['rule_option'] === 'UNSUBSCRIBED' ) {
														$rule_content = $targeting_rule['rule_content'];
														$rule_and_or  = ' ' . $targeting_rule['rule_and_or'] . ' ';
														if ( $sql_search_targeting_rules === '' ) {
															$rule_and_or = '';
														}
														if ( $targeting_rule['rule_option'] === 'UNSUBSCRIBED' ) {
															$rule_column  = '`blacklist`';
															$rule_option  = " = %s";
															$sql_values[] = '1';
														} else {
															$rule_column = '`email`';
															if ( $targeting_rule['rule_column'] > 0 ) {
																if ( in_array( 'field' . $targeting_rule['rule_column'], $columns ) ) {
																	$rule_column = "`field{$targeting_rule['rule_column']}`";
																} else {
																	continue;
																}
															}
															if ( ( $targeting_rule['rule_option'] === 'DATE>' || $targeting_rule['rule_option'] === 'DATE<' ) ) {
																if ( strtotime( $rule_content ) ) {
																	$rule_column = "STR_TO_DATE( {$rule_column}, '%%Y-%%m-%%d %%H:%%i:%%s' )";
																	if ( $targeting_rule['rule_option'] === 'DATE<' ) {
																		$rule_option = " BETWEEN '0000-00-00 00:00:01' AND %s";
																	} else {
																		$rule_option = " > %s";
																	}
																} else {
																	continue;
																}
															} else if ( ( $targeting_rule['rule_option'] === 'NUMBER>' || $targeting_rule['rule_option'] === 'NUMBER<' ) ) {
																if ( is_numeric( $rule_content ) ) {
																	if ( $targeting_rule['rule_option'] === 'NUMBER>' ) {
																		$rule_option = '>';
																	} else {
																		$rule_option = '<';
																	}
																	$rule_option = " {$rule_option} %s AND CONCAT( '', {$rule_column} * 1 ) = {$rule_column}";
																} else {
																	continue;
																}
															} else if ( $targeting_rule['rule_option'] === 'EMPTY' ) {
																$rule_option = " = %s";
															} else {
																$rule_option = " {$targeting_rule['rule_option']} %s";
															}
															if ( $rule_option === ' LIKE %s' ) {
																$sql_values[] = "%{$wpdb->esc_like( $rule_content )}%";
															} else {
																$sql_values[] = $rule_content;
															}
														}
														$sql_search_targeting_rules .= "{$rule_and_or}{$rule_column}{$rule_option}";
													}
												}
											}
										}
									}
								}
							}
						}
					}
					if ( $search !== '' ) {
						foreach ( $columns as $key => $column ) {
							if ( $column !== 'blacklist' ) {
								if ( $sql_search_fields !== '' ) {
									$sql_search_fields .= ' OR ';
								}
								$sql_search_fields .= '`' . $column . '` LIKE %s';
								$sql_values[]      = '%' . $wpdb->esc_like( $search ) . '%';
							}
						}
					}
					if ( $contacts_selection !== '[]' && ( $contacts_selection_type === 'SELECTED' || $contacts_selection_type === 'NOT_SELECTED' ) ) {
						$contacts_selection            = json_decode( $contacts_selection, true );
						$sql_search_contacts_selection = array();
						foreach ( $contacts_selection as $contact ) {
							$sql_search_contacts_selection[] = "%s";
							$sql_values[]                    = $contact;
						}
						$sql_search_contacts_selection = implode( ', ', $sql_search_contacts_selection );
						if ( $sql_search_contacts_selection !== '' ) {
							if ( $contacts_selection_type === 'SELECTED' ) {
								$sql_search_contacts_selection = "`email` IN ( {$sql_search_contacts_selection} )";
							} else {
								$sql_search_contacts_selection = "`email` NOT IN ( {$sql_search_contacts_selection} )";
							}
						}
					}
					if ( $sql_search_targeting_rules !== '' ) {
						$sql_search_targeting_rules = " WHERE ( ( {$sql_search_targeting_rules} )";
					}
					if ( $sql_search_fields !== '' ) {
						if ( $sql_search_targeting_rules === '' ) {
							$sql_search_fields = " WHERE ( ( {$sql_search_fields} ) )";
						} else {
							$sql_search_fields = " AND ( {$sql_search_fields} ) )";
						}
					} else {
						if ( $sql_search_targeting_rules !== '' ) {
							$sql_search_targeting_rules = $sql_search_targeting_rules . ')';
						}
					}
					if ( $sql_search_contacts_selection !== '' ) {
						if ( $sql_search_targeting_rules === '' && $sql_search_fields === '' ) {
							$sql_search_contacts_selection = " WHERE ( {$sql_search_contacts_selection} )";
						} else {
							$sql_search_contacts_selection = " AND ( {$sql_search_contacts_selection} )";
						}
					}
					$sql_contacts           = "SELECT * FROM `{$table_list_contacts}`{$sql_search_targeting_rules}{$sql_search_fields}{$sql_search_contacts_selection} ORDER BY `{$sort_by}` {$sort_order}";
					$sql_delete_contacts    = "DELETE FROM `{$table_list_contacts}`{$sql_search_targeting_rules}{$sql_search_fields}{$sql_search_contacts_selection}";
					$sql_nb_contacts        = "SELECT COUNT( * ) FROM `{$table_list_contacts}`";
					$sql_nb_contacts_search = "SELECT COUNT( * ) FROM `{$table_list_contacts}`{$sql_search_targeting_rules}{$sql_search_fields}{$sql_search_contacts_selection}";
				}
				if ( $begin !== 'ALL_FILTERED' ) {
					$sql_contacts    = $sql_contacts . ' LIMIT %d, %d';
					$sql_nb_contacts = "SELECT COUNT( * ) FROM `{$table_list_contacts}`";
				}
			}
		}
		if ( $begin === 'ALL_FILTERED' && $sql_nb_contacts === '' ) {
			$begin = 'ALL';
		}
		return array(
			'begin'                  => $begin,
			'limit'                  => $limit,
			'sql_contacts'           => $sql_contacts,
			'sql_delete_contacts'    => $sql_delete_contacts,
			'sql_nb_contacts'        => $sql_nb_contacts,
			'sql_nb_contacts_search' => $sql_nb_contacts_search,
			'sql_values'             => $sql_values
		);
	}

	protected function get_plugin_list( $sql, $begin, $list, $search ) {
		global $wpdb;
		$list->fields = $sql['fields'];
		if ( $sql['rows'] !== '' && $sql['rows_for_insert'] !== '' && $sql['count_total'] !== '' && $sql['count_search'] !== '' ) {
			$sql_rows         = $sql['rows'];
			$sql_count_total  = $sql['count_total'];
			$sql_count_search = $sql['count_search'];
			if ( $begin === 'ALL' ) {
				$contacts           = $wpdb->get_results( $sql_rows );
				$nb_contacts        = $wpdb->get_var( $sql_count_total );
				$nb_contacts_search = $nb_contacts;
			} else {
				$sql_values = array();
				if ( $search !== '' ) {
					$nb_fields = count( $this->core->explode_fields( $list->fields ) ) + 1;
					for ( $i = 0; $i < $nb_fields; $i ++ ) {
						$sql_values[] = '%' . $wpdb->esc_like( $search ) . '%';
					}
				}
				$nb_contacts = $wpdb->get_var( $sql_count_total );
				if ( count( $sql_values ) === 0 ) {
					$nb_contacts_search = $wpdb->get_var( $sql_count_search );
				} else {
					$nb_contacts_search = $wpdb->get_var( $wpdb->prepare( $sql_count_search, $sql_values ) );
				}
				$sql_values[] = intval( $begin );
				$contacts     = $wpdb->get_results( $wpdb->prepare( $sql_rows, $sql_values ) );
			}
			return array(
				'list'               => $list,
				'contacts'           => $contacts,
				'nb_contacts'        => $nb_contacts,
				'nb_contacts_search' => $nb_contacts_search
			);
		}
		return array(
			'list'               => $list,
			'contacts'           => array(),
			'nb_contacts'        => '0',
			'nb_contacts_search' => '0'
		);
	}

	protected function get_plugin_list_sql( $plugin_name, $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search, $targeting_rules = '[]' ) {
		if ( $plugin_name === 'formidableforms' ) {
			return $this->get_formidableforms_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'gravityforms' ) {
			return $this->get_gravityforms_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'mailpoet2' ) {
			return $this->get_mailpoet2_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'mailpoet3' ) {
			return $this->get_mailpoet3_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'ninjaforms' ) {
			return $this->get_ninjaforms_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'popupbysupsystic' ) {
			return $this->get_popupbysupsystic_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'woocommerce-customers' ) {
			return $this->get_woo_list_customers_sql( $begin, $limit, $id_list, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'woocommerce-carts' ) {
			return $this->get_woo_list_carts_sql( $begin, $limit, $id_list, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'wordpress-users' ) {
			return $this->get_wordpress_list_sql( $begin, $limit, $id_list, $sort_by, $sort_order, $search, $targeting_rules );
		}
		return array();
	}

	protected function get_formidableforms_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search ) {
		global $wpdb;
		if ( $this->core->check_formidableforms_plugin_found() && $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$form_id     = (int) $form_id;
			$sql         = "SELECT `id`, `name`, `type`
			FROM `{$wpdb->prefix}frm_fields`
			WHERE `type` IN ('email', 'text')
			AND `form_id` = %s";
			$form_fields = $wpdb->get_results( $wpdb->prepare( $sql, $form_id ) );
			$has_email   = false;
			$email_id    = '';
			foreach ( $form_fields as $form_field ) {
				if ( $form_field->type === 'email' ) {
					$has_email = true;
					$email_id  = $form_field->id;
					break;
				}
			}
			if ( $has_email ) {
				$fields     = array();
				$fields_ids = array();
				foreach ( $form_fields as $form_field ) {
					if ( $form_field->type !== 'email' ) {
						if ( count( $fields ) < 12 ) {
							$fields[]     = $this->core->str_to_upper( $form_field->name );
							$fields_ids[] = (int) $form_field->id;
						}
					}
				}
				$sql_select         = "`field{$email_id}`.`meta_value` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
				$sql_select_id_list = ", '{$id_list}' AS `id_list`";
				$sql_select_details = "";
				$sql_search         = array();
				$sql_count_total    = "COUNT( DISTINCT `field{$email_id}`.`meta_value` ) AS `count`";
				$sql_from           = "
				FROM `{$wpdb->prefix}frm_item_metas` AS `field{$email_id}`
				LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `field{$email_id}`.`meta_value` = `blacklist`.`email`";
				$sql_order_by       = '';
				$sql_where          = "
				WHERE `field{$email_id}`.`field_id` = '{$email_id}'
				AND `field{$email_id}`.`meta_value` REGEXP '^[A-Z0-9._%%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'";
				$sql_group_by       = "GROUP BY `field{$email_id}`.`meta_value`";
				if ( $sort_by === 'email' ) {
					$sql_order_by = "ORDER BY `{$sort_by}` {$sort_order}";
				}
				$sql_search[] = "`field{$email_id}`.`meta_value` LIKE %s";
				$field_id     = 0;
				foreach ( $fields_ids as $fid ) {
					if ( $fid !== $email_id ) {
						$sql_search[] = "`field{$fid}`.`meta_value` LIKE %s";
						$sql_from     .= " LEFT JOIN `{$wpdb->prefix}frm_item_metas` AS `field{$fid}` ON `field{$fid}`.`field_id` = '{$fid}' AND `field{$email_id}`.`item_id` = `field{$fid}`.`item_id`";
						$field_id ++;
						$sql_select_details .= ", IFNULL( `field{$fid}`.`meta_value`, '' ) AS `field{$field_id}`";
						if ( $sort_by === 'field' . $field_id ) {
							$sql_order_by = "ORDER BY `{$sort_by}` {$sort_order}";
						}
					}
				}
				if ( $sql_where !== '' && $sql_group_by !== '' ) {
					$sql_rows_for_send_scenario = "SELECT {$sql_select}{$sql_select_id_list} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
					$sql_rows_for_insert        = "SELECT {$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
					$sql_count_total            = "SELECT {$sql_count_total} {$sql_from} {$sql_where}";
					if ( $begin === 'ALL' ) {
						$sql_rows         = "SELECT {$sql_select}{$sql_select_details} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
						$sql_count_search = $sql_count_total;
					} else {
						if ( $search !== '' ) {
							if ( count( $sql_search ) > 0 ) {
								$sql_search = " AND ( " . implode( ' OR ', $sql_search ) . " )";
							} else {
								$sql_search = '';
							}
						} else {
							$sql_search = '';
						}
						$sql_rows         = "SELECT {$sql_select}{$sql_select_details} {$sql_from} {$sql_where}{$sql_search} {$sql_group_by} {$sql_order_by} LIMIT %d, {$limit}";
						$sql_count_search = "{$sql_count_total}{$sql_search}";
					}
					return array(
						'rows'                   => $sql_rows,
						'rows_for_send_scenario' => $sql_rows_for_send_scenario,
						'rows_for_insert'        => $sql_rows_for_insert,
						'count_total'            => $sql_count_total . ';',
						'count_search'           => $sql_count_search . ';',
						'fields'                 => $this->core->implode_fields( $fields ),
						'nb_fields'              => count( $fields )
					);
				}
			}
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function get_gravityforms_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search ) {
		global $wpdb;
		if ( $this->core->check_gravityforms_plugin_found() && $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$form_id     = (int) $form_id;
			$sql         = "SELECT `display_meta`
			FROM `{$wpdb->prefix}rg_form_meta`
			WHERE `display_meta` LIKE %s
			AND `form_id` = %s";
			$form_fields = $wpdb->get_row( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( '"type":"email"' ) . '%', $form_id ) );
			if ( isset( $form_fields->display_meta ) ) {
				$display_meta = json_decode( $form_fields->display_meta, true );
				if ( $display_meta !== false ) {
					if ( isset( $display_meta['fields'] ) ) {
						if ( is_array( $display_meta['fields'] ) ) {
							$has_email  = false;
							$has_fields = false;
							$email_id   = '';
							$fields     = array();
							$fields_ids = array();
							foreach ( $display_meta['fields'] as $field ) {
								if ( isset( $field['type'], $field['id'] ) ) {
									if ( ! $has_email && $field['type'] === 'email' ) {
										$has_email = true;
										$email_id  = (int) $field['id'];
									} else if ( ! $has_fields && $field['type'] === 'name' ) {
										if ( isset( $field['inputs'] ) ) {
											if ( is_array( $field['inputs'] ) ) {
												foreach ( $field['inputs'] as $form_field ) {
													if ( isset( $form_field['id'], $form_field['label'] ) && ( ! isset( $form_field['isHidden'] ) || ( isset( $form_field['isHidden'] ) && $form_field['isHidden'] === false ) ) ) {
														if ( count( $fields ) < 12 ) {
															$fields[]     = $this->core->str_to_upper( $form_field['label'] );
															$fields_ids[] = $form_field['id'];
															$has_fields   = true;
														}
													}
												}
											}
										}

									}
								}
							}
							if ( $has_email ) {
								$sql_select         = "`rg`.`value` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
								$sql_select_id_list = ", '{$id_list}' AS `id_list`";
								$sql_select_details = "";
								$sql_search         = array();
								$sql_count_total    = "COUNT( DISTINCT `rg`.`value` ) AS `count`";
								$sql_from           = "
								FROM `{$wpdb->prefix}rg_lead_detail` AS `rg`
								LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `rg`.`value` = `blacklist`.`email`";
								$sql_order_by       = '';
								$sql_where          = "
								WHERE `rg`.`field_number` = '{$email_id}'
								AND `rg`.`form_id` = '{$form_id}'
								AND `rg`.`value` REGEXP '^[A-Z0-9._%%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'";
								$sql_group_by       = "GROUP BY `rg`.`value`";
								$field_id           = 0;
								foreach ( $fields_ids as $fid ) {
									$fid          = (float) $fid;
									$sql_search[] = "`field{$field_id}`.`value` LIKE %s";
									$field_id ++;
									$sql_from           .= "
									LEFT JOIN `{$wpdb->prefix}rg_lead_detail` AS `field{$field_id}`
										ON `field{$field_id}`.`form_id` = `rg`.`form_id`
										AND `field{$field_id}`.`lead_id` = `rg`.`lead_id`
										AND CAST(`field{$field_id}`.`field_number` AS CHAR) = '{$fid}'";
									$sql_select_details .= ", IFNULL( `field{$field_id}`.`value`, '' ) AS `field{$field_id}`";
									if ( $sort_by === 'field' . $field_id ) {
										$sql_order_by = "ORDER BY `{$sort_by}` {$sort_order}";
									}
								}
								if ( $sort_by === 'email' ) {
									$sql_order_by = "ORDER BY `{$sort_by}` {$sort_order}";
								}
								if ( $sql_where !== '' && $sql_group_by !== '' ) {
									$sql_rows_for_send_scenario = "SELECT {$sql_select}{$sql_select_id_list} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
									$sql_rows_for_insert        = "SELECT {$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
									$sql_count_total            = "SELECT {$sql_count_total} {$sql_from} {$sql_where}";
									if ( $begin === 'ALL' ) {
										$sql_rows         = "SELECT {$sql_select}{$sql_select_details} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
										$sql_count_search = $sql_count_total;
									} else {
										if ( $search !== '' ) {
											if ( count( $sql_search ) > 0 ) {
												$sql_search = " AND ( " . implode( ' OR ', $sql_search ) . " )";
											} else {
												$sql_search = '';
											}
										} else {
											$sql_search = '';
										}
										$sql_rows         = "SELECT {$sql_select}{$sql_select_details} {$sql_from} {$sql_where}{$sql_search} {$sql_group_by} {$sql_order_by} LIMIT %d, {$limit}";
										$sql_count_search = "{$sql_count_total}{$sql_search}";
									}
									return array(
										'rows'                   => $sql_rows,
										'rows_for_send_scenario' => $sql_rows_for_send_scenario,
										'rows_for_insert'        => $sql_rows_for_insert,
										'count_total'            => $sql_count_total . ';',
										'count_search'           => $sql_count_search . ';',
										'fields'                 => $this->core->implode_fields( $fields ),
										'nb_fields'              => count( $fields )
									);
								}
							}
						}
					}
				}
			}
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function get_mailpoet2_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search ) {
		global $wpdb;
		if ( $this->core->check_mailpoet2_plugin_found() && $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$form_id                    = (int) $form_id;
			$sql_select                 = "SELECT `u`.`email` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
			$sql_select_id_list         = ", '{$id_list}' AS `id_list`";
			$sql_select_details         = ", `u`.`firstname` AS `field1`, `u`.`lastname` AS `field2`";
			$sql_count_total            = "SELECT COUNT( DISTINCT `u`.`email` ) AS `count`";
			$sql_from                   = "
			FROM `{$wpdb->prefix}wysija_user` AS `u`
			INNER JOIN `{$wpdb->prefix}wysija_user_list` AS `ul` ON `ul`.`user_id` = `u`.`user_id` AND `ul`.`list_id` = {$form_id}
			LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `u`.`email` = `blacklist`.`email`
			WHERE `u`.`email` REGEXP '^[A-Z0-9._%%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'";
			$sql_group_by               = "GROUP BY `u`.`email`";
			$sql_order_by               = "ORDER BY `{$sort_by}` {$sort_order}";
			$sql_rows_for_send_scenario = "{$sql_select}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
			$sql_rows_for_insert        = "{$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
			$sql_count_total            = "{$sql_count_total} {$sql_from}";
			if ( $begin === 'ALL' ) {
				$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from} {$sql_group_by} {$sql_order_by}";
				$sql_count_search = $sql_count_total;
			} else {
				$sql_search = '';
				if ( $search !== '' ) {
					$sql_search = '
					AND (
						`u`.`email` LIKE %s
						OR `u`.`firstname` LIKE %s
						OR `u`.`lastname` LIKE %s
					)';
				}
				$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from}{$sql_search} {$sql_group_by} {$sql_order_by} LIMIT %d, {$limit}";
				$sql_count_search = "{$sql_count_total}{$sql_search}";
			}
			$fields = array(
				$this->core->str_to_upper( __( 'Firstname', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Lastname', 'jackmail-newsletters' ) )
			);
			return array(
				'rows'                   => $sql_rows,
				'rows_for_send_scenario' => $sql_rows_for_send_scenario,
				'rows_for_insert'        => $sql_rows_for_insert,
				'count_total'            => $sql_count_total . ';',
				'count_search'           => $sql_count_search . ';',
				'fields'                 => $this->core->implode_fields( $fields ),
				'nb_fields'              => '2'
			);
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function get_mailpoet3_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search ) {
		global $wpdb;
		if ( $this->core->check_mailpoet3_plugin_found() && $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$form_id                    = (int) $form_id;
			$sql_select                 = "SELECT `ms`.`email` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
			$sql_select_id_list         = ", '{$id_list}' AS `id_list`";
			$sql_select_details         = ", `ms`.`first_name` AS `field1`, `ms`.`last_name` AS `field2`";
			$sql_count_total            = "SELECT COUNT( DISTINCT `ms`.`email` ) AS `count`";
			$sql_from                   = "
			FROM `{$wpdb->prefix}mailpoet_subscribers` AS `ms`
			INNER JOIN `{$wpdb->prefix}mailpoet_subscriber_segment` AS `mss` ON `mss`.`subscriber_id` = `ms`.`id` AND `mss`.`segment_id` = {$form_id} AND `mss`.`status` = `ms`.`status`
			LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `ms`.`email` = `blacklist`.`email`
			WHERE `ms`.`status` = 'subscribed'
			AND `ms`.`email` REGEXP '^[A-Z0-9._%%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'";
			$sql_group_by               = "GROUP BY `ms`.`email`";
			$sql_order_by               = "ORDER BY `{$sort_by}` {$sort_order}";
			$sql_rows_for_send_scenario = "{$sql_select}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
			$sql_rows_for_insert        = "{$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
			$sql_count_total            = "{$sql_count_total} {$sql_from}";
			if ( $begin === 'ALL' ) {
				$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from} {$sql_group_by} {$sql_order_by}";
				$sql_count_search = $sql_count_total;
			} else {
				$sql_search = '';
				if ( $search !== '' ) {
					$sql_search = '
					AND (
						`ms`.`email` LIKE %s
						OR `ms`.`first_name` LIKE %s
						OR `ms`.`last_name` LIKE %s
					)';
				}
				$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from}{$sql_search} {$sql_group_by} {$sql_order_by} LIMIT %d, {$limit}";
				$sql_count_search = "{$sql_count_total}{$sql_search}";
			}
			$fields = array(
				$this->core->str_to_upper( __( 'Firstname', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Lastname', 'jackmail-newsletters' ) )
			);
			return array(
				'rows'                   => $sql_rows,
				'rows_for_send_scenario' => $sql_rows_for_send_scenario,
				'rows_for_insert'        => $sql_rows_for_insert,
				'count_total'            => $sql_count_total . ';',
				'count_search'           => $sql_count_search . ';',
				'fields'                 => $this->core->implode_fields( $fields ),
				'nb_fields'              => '2'
			);
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function get_ninjaforms_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search ) {

		global $wpdb;
		if ( $this->core->check_ninjaforms_plugin_found() && $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$form_id     = (int) $form_id;
			$sql         = "
			SELECT `id`, `label`, `type`
			FROM `{$wpdb->prefix}nf3_fields`
			WHERE `type` IN ( 'email', 'firstname', 'lastname', 'textbox' )
			AND `parent_id` = %s";
			$form_fields = $wpdb->get_results( $wpdb->prepare( $sql, $form_id ) );
			$has_email   = false;
			$email_id    = '';
			foreach ( $form_fields as $form_field ) {
				if ( $form_field->type === 'email' ) {
					$has_email = true;
					$email_id  = $form_field->id;
					break;
				}
			}
			if ( $has_email ) {
				$fields     = array();
				$fields_ids = array();
				foreach ( $form_fields as $form_field ) {
					if ( $form_field->type !== 'email' ) {
						if ( count( $fields ) < 12 ) {
							$fields[]     = $this->core->str_to_upper( $form_field->label );
							$fields_ids[] = (int) $form_field->id;
						}
					}
				}
				$sql_select         = "`field{$email_id}`.`meta_value` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
				$sql_select_id_list = ", '{$id_list}' AS `id_list`";
				$sql_select_details = "";
				$sql_search         = array();
				$sql_count_total    = "COUNT( DISTINCT `field{$email_id}`.`meta_value` ) AS `count`";
				$sql_from           = "
				FROM `{$wpdb->prefix}postmeta` AS `field{$email_id}`
				INNER JOIN `{$wpdb->prefix}posts` AS `posts` ON `posts`.`post_type` = 'nf_sub' AND `field{$email_id}`.`post_id` = `posts`.`ID`
				LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `field{$email_id}`.`meta_value` = `blacklist`.`email`";
				$sql_order_by       = '';
				$sql_where          = "
				WHERE `field{$email_id}`.`meta_key` = '_field_{$email_id}'
				AND `field{$email_id}`.`meta_value` REGEXP '^[A-Z0-9._%%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'";
				$sql_group_by       = "GROUP BY `field{$email_id}`.`meta_value`";
				if ( $sort_by === 'email' ) {
					$sql_order_by = "ORDER BY `{$sort_by}` {$sort_order}";
				}
				$sql_search[] = "`field{$email_id}`.`meta_value` LIKE %s";
				$field_id     = 0;
				foreach ( $fields_ids as $fid ) {
					if ( $fid !== $email_id ) {
						$sql_search[] = "`field{$fid}`.`meta_value` LIKE %s";
						$sql_from     .= " LEFT JOIN `{$wpdb->prefix}postmeta` AS `field{$fid}` ON `field{$fid}`.`meta_key` = '_field_{$fid}' AND `field{$fid}`.`post_id` = `field{$email_id}`.`post_id`";
						$field_id ++;
						$sql_select_details .= ", IFNULL( `field{$fid}`.`meta_value`, '' ) AS `field{$field_id}`";
						if ( $sort_by === 'field' . $field_id ) {
							$sql_order_by = "ORDER BY `{$sort_by}` {$sort_order}";
						}
					}
				}
				if ( $sql_where !== '' && $sql_group_by !== '' ) {
					$sql_rows_for_send_scenario = "SELECT {$sql_select}{$sql_select_id_list} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
					$sql_rows_for_insert        = "SELECT {$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
					$sql_count_total            = "SELECT {$sql_count_total} {$sql_from} {$sql_where}";
					if ( $begin === 'ALL' ) {
						$sql_rows         = "SELECT {$sql_select}{$sql_select_details} {$sql_from} {$sql_where} {$sql_group_by} {$sql_order_by}";
						$sql_count_search = $sql_count_total;
					} else {
						if ( $search !== '' ) {
							if ( count( $sql_search ) > 0 ) {
								$sql_search = " AND ( " . implode( ' OR ', $sql_search ) . " )";
							} else {
								$sql_search = '';
							}
						} else {
							$sql_search = '';
						}
						$sql_rows         = "SELECT {$sql_select}{$sql_select_details} {$sql_from} {$sql_where}{$sql_search} {$sql_group_by} {$sql_order_by} LIMIT %d, {$limit}";
						$sql_count_search = "{$sql_count_total}{$sql_search}";
					}
					return array(
						'rows'                   => $sql_rows,
						'rows_for_send_scenario' => $sql_rows_for_send_scenario,
						'rows_for_insert'        => $sql_rows_for_insert,
						'count_total'            => $sql_count_total . ';',
						'count_search'           => $sql_count_search . ';',
						'fields'                 => $this->core->implode_fields( $fields ),
						'nb_fields'              => count( $fields )
					);
				}
			}
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function get_popupbysupsystic_list_sql( $begin, $limit, $form_id, $id_list, $sort_by, $sort_order, $search ) {
		global $wpdb;
		if ( $this->core->check_popupbysupsystic_plugin_found() && $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$form_id                    = (int) $form_id;
			$sql_select                 = "SELECT `s`.`email` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
			$sql_select_id_list         = ", '{$id_list}' AS `id_list`";
			$sql_select_details         = ", `s`.`username` AS `field1`";
			$sql_count_total            = "SELECT COUNT( DISTINCT `s`.`email` ) AS `count`";
			$sql_from                   = "
			FROM `{$wpdb->prefix}pps_subscribers` AS `s`
			LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `s`.`email` = `blacklist`.`email`
			WHERE `s`.`email` REGEXP '^[A-Z0-9._%%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'
			AND `s`.`popup_id` = {$form_id}";
			$sql_group_by               = "GROUP BY `s`.`email`";
			$sql_order_by               = "ORDER BY `{$sort_by}` {$sort_order}";
			$sql_rows_for_send_scenario = "{$sql_select}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
			$sql_rows_for_insert        = "{$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
			$sql_count_total            = "{$sql_count_total} {$sql_from}";
			if ( $begin === 'ALL' ) {
				$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from} {$sql_group_by} {$sql_order_by}";
				$sql_count_search = $sql_count_total;
			} else {
				$sql_search = '';
				if ( $search !== '' ) {
					$sql_search = '
					AND (
						`s`.`email` LIKE %s
						OR `s`.`username` LIKE %s
					)';
				}
				$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from}{$sql_search} {$sql_group_by} {$sql_order_by} LIMIT %d, {$limit}";
				$sql_count_search = "{$sql_count_total}{$sql_search}";
			}
			$fields = array(
				$this->core->str_to_upper( __( 'Name', 'jackmail-newsletters' ) )
			);
			return array(
				'rows'                   => $sql_rows,
				'rows_for_send_scenario' => $sql_rows_for_send_scenario,
				'rows_for_insert'        => $sql_rows_for_insert,
				'count_total'            => $sql_count_total . ';',
				'count_search'           => $sql_count_search . ';',
				'fields'                 => $this->core->implode_fields( $fields ),
				'nb_fields'              => '1'
			);
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function get_woo_list_customers_sql( $begin, $limit, $id_list, $sort_by, $sort_order, $search ) {
		global $wpdb;
		if ( $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$sql_select                 = "SELECT `email`.`meta_value` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
			$sql_select_id_list         = ", '{$id_list}' AS `id_list`";
			$sql_select_details         = ", MAX( `first_name`.`meta_value` ) AS `field1`, MAX( `last_name`.`meta_value` ) AS `field2`, COUNT( `email`.`meta_value` ) AS `field3`";
			$sql_select_details         .= ", ROUND( SUM( `order_total`.`meta_value` ), 2 ) AS `field4`, ROUND( AVG( `order_total`.`meta_value` ), 2 ) AS `field5`, MAX( `posts`.`post_date` ) AS `field6`";
			$sql_count_total            = "SELECT COUNT( DISTINCT `email`.`meta_value` ) AS `count`";
			$sql_from                   = "
			FROM `{$wpdb->prefix}posts` AS `posts`,
			`{$wpdb->prefix}postmeta` AS `email`
			LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `email`.`meta_value` = `blacklist`.`email`
			INNER JOIN `{$wpdb->prefix}postmeta` AS `first_name` ON `email`.`post_id` = `first_name`.`post_id` AND `first_name`.`meta_key` = '_billing_first_name' AND `first_name`.`meta_value` != ''
			INNER JOIN `{$wpdb->prefix}postmeta` AS `last_name` ON `email`.`post_id` = `last_name`.`post_id` AND `last_name`.`meta_key` = '_billing_last_name' AND `last_name`.`meta_value` != ''
			INNER JOIN `{$wpdb->prefix}postmeta` AS `order_total` ON `email`.`post_id` = `order_total`.`post_id` AND `order_total`.`meta_key` = '_order_total'
			WHERE `posts`.`post_type` = 'shop_order'
			AND `email`.`post_id` = `posts`.`ID`
			AND `email`.`meta_key` = '_billing_email'
			AND `email`.`meta_value` REGEXP '^[A-Z0-9._%%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'";
			$sql_group_by               = "GROUP BY `email`.`meta_value`";
			$sql_order_by               = "ORDER BY `{$sort_by}` {$sort_order}";
			$sql_rows_for_send_scenario = "{$sql_select}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
			$sql_rows_for_insert        = "{$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
			$sql_count_total            = "{$sql_count_total} {$sql_from}";
			if ( $begin === 'ALL' ) {
				$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from} {$sql_group_by} {$sql_order_by}";
				$sql_count_search = $sql_count_total;
			} else {
				$sql_search = '';
				if ( $search !== '' ) {
					$sql_search = '
					AND (
						`email`.`meta_value` LIKE %s
						OR `first_name`.`meta_value` LIKE %s
						OR `last_name`.`meta_value` LIKE %s
						OR `email`.`meta_value` LIKE %s
						OR `order_total`.`meta_value` LIKE %s
						OR `order_total`.`meta_value` LIKE %s
						OR `posts`.`post_date` LIKE %s
					)';
				}
				$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from}{$sql_search} {$sql_group_by} {$sql_order_by} LIMIT %d, {$limit}";
				$sql_count_search = "{$sql_count_total}{$sql_search}";
			}
			$fields = array(
				$this->core->str_to_upper( __( 'First name', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Last name', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Transactions', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Income', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Average cart value', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Last order (gmt)', 'jackmail-newsletters' ) )
			);
			return array(
				'rows'                   => $sql_rows,
				'rows_for_send_scenario' => $sql_rows_for_send_scenario,
				'rows_for_insert'        => $sql_rows_for_insert,
				'count_total'            => $sql_count_total . ';',
				'count_search'           => $sql_count_search . ';',
				'fields'                 => $this->core->implode_fields( $fields ),
				'nb_fields'              => '6'
			);
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function get_woo_list_carts_sql( $begin, $limit, $id_list, $sort_by, $sort_order, $search ) {
		global $wpdb;
		if ( $this->core->check_table_exists( 'ac_abandoned_cart_history_lite' ) && $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$columns = $this->core->get_table_columns( 'ac_abandoned_cart_history_lite' );
			if ( in_array( 'user_id', $columns ) && in_array( 'abandoned_cart_time', $columns ) ) {
				$sql_select                 = "SELECT `email`.`meta_value` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
				$sql_select_id_list         = ", '{$id_list}' AS `id_list`";
				$sql_select_details         = ", MAX( `first_name`.`meta_value` ) AS `field1`, MAX( `last_name`.`meta_value` ) AS `field2`, FROM_UNIXTIME( MAX( `carts`.`abandoned_cart_time` ) ) AS `field3`";
				$sql_count_total            = "SELECT COUNT( DISTINCT `email`.`meta_value` ) AS `count`";
				$sql_from                   = "
				FROM `{$wpdb->prefix}ac_abandoned_cart_history_lite` AS `carts`,
				`{$wpdb->prefix}usermeta` AS `email`
				LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `email`.`meta_value` = `blacklist`.`email`
				INNER JOIN `{$wpdb->prefix}usermeta` AS `first_name` ON `email`.`user_id` = `first_name`.`user_id` AND `first_name`.`meta_key` = 'billing_first_name' AND `first_name`.`meta_value` != ''
				INNER JOIN `{$wpdb->prefix}usermeta` AS `last_name` ON `email`.`user_id` = `last_name`.`user_id` AND `last_name`.`meta_key` = 'billing_last_name' AND `last_name`.`meta_value` != ''
				WHERE `carts`.`cart_ignored` = '0'
				AND `email`.`user_id` = `carts`.`user_id`
				AND `email`.`meta_key` = 'billing_email'
				AND `email`.`meta_value` REGEXP '^[A-Z0-9._%%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$'";
				$sql_group_by               = "GROUP BY `email`.`meta_value`";
				$sql_order_by               = "ORDER BY `{$sort_by}` {$sort_order}";
				$sql_rows_for_send_scenario = "{$sql_select}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
				$sql_rows_for_insert        = "{$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_group_by} {$sql_order_by}";
				$sql_count_total            = "{$sql_count_total} {$sql_from}";
				if ( $begin === 'ALL' ) {
					$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from} {$sql_group_by} {$sql_order_by}";
					$sql_count_search = $sql_count_total;
				} else {
					$sql_search = '';
					if ( $search !== '' ) {
						$sql_search = '
						AND (
							`email`.`meta_value` LIKE %s
							OR `first_name`.`meta_value` LIKE %s
							OR `last_name`.`meta_value` LIKE %s
							OR `carts`.`abandoned_cart_time` LIKE %s
						)';
					}
					$sql_rows         = "{$sql_select}{$sql_select_details} {$sql_from}{$sql_search} {$sql_group_by} {$sql_order_by} LIMIT %d, {$limit}";
					$sql_count_search = "{$sql_count_total}{$sql_search}";
				}
				$fields = array(
					$this->core->str_to_upper( __( 'First name', 'jackmail-newsletters' ) ),
					$this->core->str_to_upper( __( 'Last name', 'jackmail-newsletters' ) ),
					$this->core->str_to_upper( __( 'Last abandoned cart (gmt)', 'jackmail-newsletters' ) )
				);
				return array(
					'rows'                   => $sql_rows,
					'rows_for_send_scenario' => $sql_rows_for_send_scenario,
					'rows_for_insert'        => $sql_rows_for_insert,
					'count_total'            => $sql_count_total . ';',
					'count_search'           => $sql_count_search . ';',
					'fields'                 => $this->core->implode_fields( $fields ),
					'nb_fields'              => '3'
				);
			}
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function get_wordpress_list_sql( $begin, $limit, $id_list, $sort_by, $sort_order, $search, $targeting_rules ) {
		global $wpdb;
		if ( $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
			$sql_select                 = "
			`u`.`user_email` AS `email`, IFNULL( `blacklist`.`blacklist`, '0' ) AS `blacklist`";
			$sql_select_id_list         = ", '{$id_list}' AS `id_list`";
			$sql_select_details         = ", `u`.`display_name` AS `field1`, `u`.`user_registered` AS `field2`, SUBSTRING_INDEX(SUBSTRING_INDEX(`um`.`meta_value`, '\"', 2), '\"', -1) AS `field3`";
			$sql_count_total            = "COUNT(*) AS `count`";
			$sql_from                   = "
			FROM `{$wpdb->prefix}users` AS `u`
			INNER JOIN `{$wpdb->prefix}usermeta` AS `um` ON `um`.`user_id` = `u`.`ID` AND `um`.`meta_key` = '{$wpdb->prefix}capabilities'
			LEFT JOIN `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}` AS `blacklist` ON `u`.`user_email` = `blacklist`.`email`";
			$sql_order_by               = "ORDER BY `{$sort_by}` {$sort_order}";
			$sql_rows_for_send_scenario = "SELECT {$sql_select}{$sql_select_id_list} {$sql_from} {$sql_order_by}";
			$sql_rows_for_insert        = "SELECT {$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_order_by}";
			$sql_count_total            = "SELECT {$sql_count_total} {$sql_from}";
			if ( $begin === 'ALL' ) {
				$sql_rows         = "SELECT {$sql_select}{$sql_select_details} {$sql_from} {$sql_order_by}";
				$sql_count_search = $sql_count_total;
			} else {
				if ( $search !== '' ) {
					$sql_search = "
					WHERE `user_email` LIKE %s
					OR `display_name` LIKE %s
					OR `user_registered` LIKE %s
					OR SUBSTRING_INDEX(SUBSTRING_INDEX(`um`.`meta_value`, '\"', 2), '\"', -1) LIKE %s";
				} else {
					$sql_search = '';
				}
				$sql_rows         = "SELECT {$sql_select}{$sql_select_details}{$sql_select_id_list} {$sql_from} {$sql_search} {$sql_order_by} LIMIT %d, {$limit}";
				$sql_count_search = "{$sql_count_total}{$sql_search}";
			}
			$fields = array(
				$this->core->str_to_upper( __( 'Name', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Registered date (gmt)', 'jackmail-newsletters' ) ),
				$this->core->str_to_upper( __( 'Role', 'jackmail-newsletters' ) )
			);
			return array(
				'rows'                   => $sql_rows,
				'rows_for_send_scenario' => $sql_rows_for_send_scenario,
				'rows_for_insert'        => $sql_rows_for_insert,
				'count_total'            => $sql_count_total . ';',
				'count_search'           => $sql_count_search . ';',
				'fields'                 => $this->core->implode_fields( $fields ),
				'nb_fields'              => count( $fields )
			);
		}
		return array(
			'rows'                   => '',
			'rows_for_send_scenario' => '',
			'rows_for_insert'        => '',
			'count_total'            => '',
			'count_search'           => '',
			'fields'                 => '',
			'nb_fields'              => ''
		);
	}

	protected function str_getcsv( $content, $field_separator ) {
		$array = str_getcsv( $content, $field_separator );
		if ( ! is_array( $array ) ) {
			return array();
		}
		if ( count( $array ) === 1 && $array[0] === null ) {
			return array();
		}
		return $array;
	}

	public function actualize_plugins_lists() {
		$actual_plugins_array = $this->core->get_jackmail_plugins();
		$plugins              = $this->core->get_plugins_updates_lists_functions();
		foreach ( $plugins as $plugin ) {
			if ( $this->is_selected_plugin( $plugin['name'], $actual_plugins_array ) ) {
				if ( method_exists( $this, $plugin['function'] ) ) {
					$this->{$plugin['function']}();
				}
			} else {
				$this->delete_or_change_all_plugin_lists( $plugin['name'] );
			}
		}
	}

	protected function is_selected_plugin( $plugin_name, $actual_plugins_array ) {
		$plugins      = $this->core->get_plugins_found_functions();
		$plugin_name2 = $plugin_name;
		if ( $plugin_name2 === 'woocommerce-carts' || $plugin_name2 === 'woocommerce-customers' ) {
			$plugin_name2 = 'woocommerce';
		}
		if ( in_array( $plugin_name2, $actual_plugins_array ) ) {
			foreach ( $plugins as $plugin ) {
				if ( $plugin['name'] === $plugin_name ) {
					if ( $plugin['file'] !== '' ) {
						$active_plugins = get_option( 'active_plugins' );
						if ( in_array( $plugin['file'], $active_plugins ) ) {
							if ( $plugin['function'] === '' ) {
								return true;
							} else {
								if ( method_exists( $this->core, $plugin['function'] ) ) {
									if ( $this->core->{$plugin['function']}() ) {
										return true;
									}
								}
							}
						}
					}
					break;
				}
			}
		}
		return false;
	}

	private function insert_cf7_lists_contacts_temp() {
		global $wpdb;
		if ( $this->core->check_table_exists( 'jackmail_lists_contacts_cf7_data' ) ) {
			$sql     = "SELECT * FROM `{$wpdb->prefix}jackmail_lists_contacts_cf7_data`";
			$results = $wpdb->get_results( $sql );
			$wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}jackmail_lists_contacts_cf7_data`" );
			foreach ( $results as $result ) {
				$form_id             = $result->form_id;
				$email               = $result->email;
				$fields              = json_decode( $result->fields );
				$id_list             = $this->get_list( 'contactform7', $form_id );
				$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
				if ( $id_list !== '0' ) {
					if ( $this->core->check_table_exists( $table_list_contacts, false ) && $this->core->check_table_exists( 'jackmail_lists' ) ) {
						$columns = $this->core->get_table_columns( $table_list_contacts, false );
						$sql     = "SELECT * FROM `{$wpdb->prefix}jackmail_lists` WHERE `id` = %s";
						$result  = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
						if ( isset( $result->id, $result->fields ) ) {
							$header_fields = $result->fields;
							$header_fields = $this->core->explode_fields( $header_fields );
							foreach ( $fields as $header => $field ) {
								if ( ! in_array( $header, $header_fields ) ) {
									$header_fields[] = $header;
								}
							}
							$insert_fields = array();
							foreach ( $header_fields as $header_field ) {
								$insert_fields[ $header_field ] = '';
							}
							foreach ( $fields as $header => $field ) {
								foreach ( $header_fields as $key => $current_field ) {
									if ( $current_field === $header ) {
										$insert_fields[ $header ] = $field;
										break;
									}
								}
							}
							$sql_values   = array();
							$sql_values[] = $this->core->str_to_lower( $email );
							foreach ( $insert_fields as $field ) {
								$sql_values[] = $field;
							}
							$nb_header_fields = count( $header_fields );
							$nb_insert_fields = count( $insert_fields );
							if ( $nb_header_fields === $nb_insert_fields && count( $sql_values ) > 0 ) {
								$nb_fields = $nb_insert_fields;
								for ( $i = 1; $i <= $nb_fields; $i ++ ) {
									$field = 'field' . $i;
									if ( ! in_array( $field, $columns ) ) {
										$this->core->create_list_field( $table_list_contacts, $field );
									}
								}
								$sql_fields           = array();
								$sql_headers_fields   = array();
								$sql_headers_fields[] = 'email';
								foreach ( $sql_values as $i => $value ) {
									$sql_fields[] = '%s';
									if ( $i > 0 ) {
										$sql_headers_fields[] = 'field' . $i;
									}
								}
								$sql_fields         = implode( ', ', $sql_fields );
								$sql_headers_fields = implode( ', ', $sql_headers_fields );
								$sql                = "REPLACE INTO `$table_list_contacts` ({$sql_headers_fields}) VALUES ({$sql_fields})";
								$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
								$update = array(
									'fields' => $this->core->implode_fields( $header_fields )
								);
								$this->core->updated_list_contact( $id_list, $update );
							}
						}
					}
				}
			}
		}
	}

	private function bloom_update_lists() {
		$plugin     = 'bloom';
		$sql_values = array();
		$options    = get_option( 'et_bloom_options', array() );
		foreach ( $options as $key => $option ) {
			if ( is_array( $option ) ) {
				if ( strpos( $key, 'optin_' ) !== false ) {
					if ( isset( $option['optin_name'] ) ) {
						$id           = substr( $key, 6 );
						$name         = $option['optin_name'];
						$fields       = array(
							$this->core->str_to_upper( __( 'First name', 'jackmail-newsletters' ) ),
							$this->core->str_to_upper( __( 'Last name', 'jackmail-newsletters' ) )
						);
						$sql_values[] = $this->core->check_or_create_list( $plugin, $id, $name, $fields );
					}
				}
			}
		}
		$this->delete_old_plugin_lists( $plugin, $sql_values );
	}

	private function cf7_update_lists() {
		global $wpdb;
		$this->insert_cf7_lists_contacts_temp();
		$sql   = "
		SELECT `ID` AS `id`, `post_title` AS `name`
		FROM `{$wpdb->prefix}posts`
		WHERE `post_type` = 'wpcf7_contact_form'
		ORDER BY `id` ASC";
		$forms = $wpdb->get_results( $sql );
		$this->check_or_create_list_and_delete_old_plugin_lists( 'contactform7', $forms );
	}

	private function formidableforms_update_lists() {
		global $wpdb;
		$sql   = "
		SELECT `forms`.`id`, `forms`.`name`
		FROM `{$wpdb->prefix}frm_forms` AS `forms`
		INNER JOIN `{$wpdb->prefix}frm_fields` AS `fields` ON `fields`.`form_id` = `forms`.`id` AND `fields`.`type` = 'email'
		WHERE `forms`.`is_template` = 0
		GROUP BY `forms`.`id`";
		$forms = $wpdb->get_results( $sql );
		$this->check_or_create_list_and_delete_old_plugin_lists( 'formidableforms', $forms );
	}

	private function gravityforms_update_lists() {
		global $wpdb;
		$sql   = "
		SELECT `f`.`id`, `f`.`title` AS `name`
		FROM `{$wpdb->prefix}rg_form` AS `f`
		INNER JOIN `{$wpdb->prefix}rg_form_meta` AS `fm` ON `fm`.`form_id` = `f`.`id`
		WHERE `fm`.`display_meta` LIKE %s
		ORDER BY `f`.`id` ASC";
		$forms = $wpdb->get_results( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( '"type":"email"' ) . '%' ) );
		$this->check_or_create_list_and_delete_old_plugin_lists( 'gravityforms', $forms );
	}

	private function mailpoet2_update_lists() {
		global $wpdb;
		$sql   = "
		SELECT `list_id` AS `id`, `name`
		FROM `{$wpdb->prefix}wysija_list`
		WHERE `is_enabled` = 1";
		$forms = $wpdb->get_results( $sql );
		$this->check_or_create_list_and_delete_old_plugin_lists( 'mailpoet2', $forms );
	}

	private function mailpoet3_update_lists() {
		global $wpdb;
		$sql   = "
		SELECT `id`, `name`
		FROM `{$wpdb->prefix}mailpoet_segments`
		WHERE `type` = 'default'
		AND `deleted_at` IS NULL";
		$forms = $wpdb->get_results( $sql );
		$this->check_or_create_list_and_delete_old_plugin_lists( 'mailpoet3', $forms );
	}

	private function ninjaforms_update_lists() {
		global $wpdb;
		$sql   = "
		SELECT `fo`.`id`, `fo`.`title` AS `name`
		FROM `{$wpdb->prefix}nf3_forms` AS `fo`
		INNER JOIN `{$wpdb->prefix}nf3_fields` AS `fi` ON `fi`.`parent_id` = `fo`.`id` AND `fi`.`type` = 'email'
		GROUP BY `fo`.`id`
		ORDER BY `id` ASC";
		$forms = $wpdb->get_results( $sql );
		$this->check_or_create_list_and_delete_old_plugin_lists( 'ninjaforms', $forms );
	}

	private function popupbysupsystic_update_lists() {
		global $wpdb;
		$sql   = "
		SELECT `id`, `label` AS `name`
		FROM `{$wpdb->prefix}pps_popup`
		WHERE `original_id` != 0
		ORDER BY `id` ASC";
		$forms = $wpdb->get_results( $sql );
		$this->check_or_create_list_and_delete_old_plugin_lists( 'popupbysupsystic', $forms );
	}

	private function woo_customers_update_lists() {
		$this->core->check_or_create_list( 'woocommerce-customers', 1, __( 'WooCommerce clients', 'jackmail-newsletters' ) );
	}

	private function woo_carts_update_lists() {
		$this->core->check_or_create_list( 'woocommerce-carts', 2, __( 'WooCommerce abandoned carts', 'jackmail-newsletters' ) );
	}

	private function check_or_create_list_and_delete_old_plugin_lists( $plugin, Array $forms ) {
		$sql_values = array();
		foreach ( $forms as $form ) {
			if ( isset( $form->id, $form->name ) ) {
				$sql_values[] = $this->core->check_or_create_list( $plugin, $form->id, $form->name );
			}
		}
		$this->delete_old_plugin_lists( $plugin, $sql_values );
	}

	private function delete_old_plugin_lists( $plugin, Array $sql_values ) {
		global $wpdb;
		if ( count( $sql_values ) > 0 ) {
			$id_lists_conditions = array();
			$nb_sql_values       = count( $sql_values );
			for ( $i = 0; $i < $nb_sql_values; $i ++ ) {
				$id_lists_conditions[] = '%s';
			}
			$id_lists_conditions = implode( ', ', $id_lists_conditions );
			$sql_values[]        = '{"name":"' . $wpdb->esc_like( $plugin ) . '"%';
			$sql                 = "
			SELECT `id`
			FROM `{$wpdb->prefix}jackmail_lists`
			WHERE `id` NOT IN ({$id_lists_conditions})
			AND `type` LIKE %s";
			$results             = $wpdb->get_results( $wpdb->prepare( $sql, $sql_values ) );
			if ( count( $results ) > 0 ) {
				$id_lists = array();
				foreach ( $results as $result ) {
					$id_lists[] = $result->id;
				}
				$this->delete_lists( $id_lists );
			}
		}
	}

	private function delete_or_change_all_plugin_lists( $plugin ) {
		global $wpdb;
		$type            = '{"name":"' . $wpdb->esc_like( $plugin ) . '"%';
		$sql             = "
		SELECT `id`, `nb_contacts`, `nb_contacts_valids`
		FROM `{$wpdb->prefix}jackmail_lists`
		WHERE `type` LIKE %s";
		$results         = $wpdb->get_results( $wpdb->prepare( $sql, $type ) );
		$id_lists_delete = array();
		$id_lists_change = array();
		foreach ( $results as $result ) {
			if ( $result->nb_contacts === '-1' && $result->nb_contacts_valids === '-1' ) {
				$id_lists_delete[] = $result->id;
			} else {
				$id_lists_change[] = $result->id;
			}
		}
		$this->delete_lists( $id_lists_delete );
		$this->change_lists( $id_lists_change );
	}

	protected function delete_lists( Array $id_lists ) {
		global $wpdb;
		if ( count( $id_lists ) > 0 ) {
			$sql_conditions = array();
			$sql_values     = array();
			$sql_drop       = array();
			foreach ( $id_lists as $id_list ) {
				$sql_conditions[] = '%s';
				$sql_values[]     = $id_list;
				$sql_drop[]       = "`{$wpdb->prefix}jackmail_lists_contacts_{$id_list}`";
			}
			$sql_conditions = implode( ', ', $sql_conditions );
			$sql            = "
			DELETE FROM `{$wpdb->prefix}jackmail_lists`
			WHERE `id` IN ({$sql_conditions})";
			$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
			if ( count( $sql_drop ) !== 0 ) {
				$sql_drop = implode( ', ', $sql_drop );
				$sql      = "DROP TABLE IF EXISTS {$sql_drop}";
				$wpdb->query( $sql );
			}
			$this->check_widgets( $sql_values );
			$this->check_scenarios( $sql_values );
		}
	}

	private function change_lists( Array $id_lists ) {
		global $wpdb;
		if ( count( $id_lists ) > 0 ) {
			$sql_conditions = array();
			$sql_values     = array();
			foreach ( $id_lists as $id_list ) {
				$sql_conditions[] = '%s';
				$sql_values[]     = $id_list;
			}
			$sql_conditions = implode( ', ', $sql_conditions );
			$sql            = "
			UPDATE `{$wpdb->prefix}jackmail_lists`
			SET `type` = ''
			WHERE `id` IN ({$sql_conditions})";
			$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
		}
	}

	protected function get_list( $plugin, $form_id ) {
		global $wpdb;
		$sql    = "
		SELECT `id`
		FROM `{$wpdb->prefix}jackmail_lists`
		WHERE `type` = %s";
		$type   = array(
			'name' => $plugin,
			'id'   => $form_id
		);
		$type   = $this->core->implode_data( $type );
		$result = $wpdb->get_row( $wpdb->prepare( $sql, $type ) );
		if ( isset( $result->id ) ) {
			return $result->id;
		}
		return '0';
	}

	private function check_widgets( Array $id_lists ) {

		$widgets = get_option( 'widget_jackmail_widget' );
		if ( is_array( $widgets ) ) {
			$widgets_temp = $widgets;
			foreach ( $widgets_temp as $i => $widget ) {
				if ( is_array( $widget ) ) {
					if ( isset( $widget['id_list'] ) ) {
						foreach ( $id_lists as $id_list ) {
							if ( $widget['id_list'] === $id_list ) {
								unset ( $widgets[ $i ] );
							}
						}
					}
				}
			}
			if ( $this->core->json_encode( $widgets ) !== $this->core->json_encode( $widgets_temp ) ) {
				update_option( 'widget_jackmail_widget', $widgets );
			}
		}
	}

	private function check_scenarios( Array $id_lists ) {
		global $wpdb;
		$sql_conditions = array();
		$sql_values     = array();
		foreach ( $id_lists as $id_list ) {
			$sql_conditions[] = "`id_lists` = %s";
			$sql_values[]     = $id_list;
		}
		$sql_conditions = '(' . implode( ' OR ', $sql_conditions ) . ')';
		$sql            = "
		UPDATE `{$wpdb->prefix}jackmail_scenarios`
		SET `status` = 'DRAFT'
		WHERE {$sql_conditions}
		AND `status` = 'ACTIVED'";
		$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
	}

	protected function is_plugin_special_list( $nb_contacts, $type ) {
		if ( $nb_contacts === '-1' && $type !== '' ) {
			return true;
		}
		return false;
	}

	protected function get_plugin_list2( $plugin_name, $begin, $limit, $plugin_id, $list, $sort_by, $sort_order, $search, $targeting_rules = '[]' ) {
		$sql = null;
		if ( $plugin_name === 'formidableforms' ) {
			$sql = $this->get_formidableforms_list_sql( $begin, $limit, $plugin_id, $list->id, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'gravityforms' ) {
			$sql = $this->get_gravityforms_list_sql( $begin, $limit, $plugin_id, $list->id, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'mailpoet2' ) {
			$sql = $this->get_mailpoet2_list_sql( $begin, $limit, $plugin_id, $list->id, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'mailpoet3' ) {
			$sql = $this->get_mailpoet3_list_sql( $begin, $limit, $plugin_id, $list->id, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'ninjaforms' ) {
			$sql = $this->get_ninjaforms_list_sql( $begin, $limit, $plugin_id, $list->id, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'popupbysupsystic' ) {
			$sql = $this->get_popupbysupsystic_list_sql( $begin, $limit, $plugin_id, $list->id, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'woocommerce-carts' ) {
			$sql = $this->get_woo_list_carts_sql( $begin, $limit, $list->id, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'woocommerce-customers' ) {
			$sql = $this->get_woo_list_customers_sql( $begin, $limit, $list->id, $sort_by, $sort_order, $search );
		} else if ( $plugin_name === 'wordpress-users' ) {
			$sql = $this->get_wordpress_list_sql( $begin, $limit, $list->id, $sort_by, $sort_order, $search, $targeting_rules );
		}
		if ( $sql !== null ) {
			return $this->get_plugin_list( $sql, $begin, $list, $search );
		}
		return array();
	}

	protected function get_plugin_list_nb_contacts( $plugin_name, $id_list, $plugin_id ) {
		$sql = null;
		if ( $plugin_name === 'formidableforms' ) {
			$sql = $this->get_formidableforms_list_sql( 'ALL', '', $plugin_id, $id_list, '', '', '' );
		} else if ( $plugin_name === 'gravityforms' ) {
			$sql = $this->get_gravityforms_list_sql( 'ALL', '', $plugin_id, $id_list, '', '', '' );
		} else if ( $plugin_name === 'mailpoet2' ) {
			$sql = $this->get_mailpoet2_list_sql( 'ALL', '', $plugin_id, $id_list, '', '', '' );
		} else if ( $plugin_name === 'mailpoet3' ) {
			$sql = $this->get_mailpoet3_list_sql( 'ALL', '', $plugin_id, $id_list, '', '', '' );
		} else if ( $plugin_name === 'ninjaforms' ) {
			$sql = $this->get_ninjaforms_list_sql( 'ALL', '', $plugin_id, $id_list, '', '', '' );
		} else if ( $plugin_name === 'popupbysupsystic' ) {
			$sql = $this->get_popupbysupsystic_list_sql( 'ALL', '', $plugin_id, $id_list, '', '', '' );
		} else if ( $plugin_name === 'woocommerce-carts' ) {
			$sql = $this->get_woo_list_carts_sql( 'ALL', '', $id_list, '', '', '' );
		} else if ( $plugin_name === 'woocommerce-customers' ) {
			$sql = $this->get_woo_list_customers_sql( 'ALL', '', $id_list, '', '', '' );
		} else if ( $plugin_name === 'wordpress-users' ) {
			$sql = $this->get_wordpress_list_sql( 'ALL', '', $id_list, '', '', '', '[]' );
		}
		if ( $sql !== null ) {
			return $this->get_plugin_nb_contacts( $sql );
		}
		return 0;
	}

	private function get_plugin_nb_contacts( $request ) {
		global $wpdb;
		if ( $request['count_total'] !== '' ) {
			$sql         = $request['count_total'];
			$count_total = $wpdb->get_row( $sql );
			return $count_total->count;
		}
		return '0';
	}

	protected function display_emojis() {
		global $wpdb;
		if ( $wpdb->charset === 'utf8mb4' ) {
			return true;
		}
		return false;
	}

	protected function get_content_size( $content_email_json, $content_email_html, $content_email_txt ) {
		$content = $content_email_txt;
		if ( $content_email_json !== '' ) {
			$content .= $content_email_json;
		} else if ( $content_email_html !== '' ) {
			$content .= $content_email_html;
		}
		return $this->core->str_len( $content ) < 400000 ? true : false;
	}

	protected function get_content_images_size( $content_email_images ) {
		$images_size = 0;
		if ( ! is_array( $content_email_images ) ) {
			$content_email_images = json_decode( $content_email_images, true );
		}
		foreach ( $content_email_images as $image ) {
			$image_size = $this->core->get_image_file_size( $image['image_id'], $image['image_type'] );
			if ( $image_size !== false ) {
				$images_size += $image_size;
			}
		}
		if ( $images_size > (int) get_option( 'jackmail_email_images_size_limit' ) ) {
			return false;
		}
		return true;
	}

	protected function get_campaign_contacts( $id_campaign, $begin, $limit, $sort_by, $sort_order, $search, $targeting_rules ) {
		global $wpdb;
		$sql   = "
		SELECT `id_lists`, `fields`
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `id` = %s";
		$lists = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		if ( isset( $lists->id_lists ) ) {

			$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
			if ( $this->core->check_table_exists( $table_list_contacts, false ) ) {
				$list     = array(
					'id'     => $lists->id_lists,
					'fields' => $lists->fields
				);
				$contacts = $this->get_list_or_get_campaign_contacts( $table_list_contacts, $begin, $limit, $sort_by, $sort_order, $search, $targeting_rules );
				if ( isset( $contacts['contacts'], $contacts['nb_contacts'], $contacts['nb_contacts_search'] ) ) {
					return array(
						'list'               => $list,
						'contacts'           => $contacts['contacts'],
						'nb_contacts'        => $contacts['nb_contacts'],
						'nb_contacts_search' => $contacts['nb_contacts_search']
					);
				}
			}
		}
		return array(
			'list'               => null,
			'contacts'           => array(),
			'nb_contacts'        => '0',
			'nb_contacts_search' => '0'
		);
	}

	private function generate_campaign_content( $campaign_type, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html, $content_email_txt, $content_email_images, $link_tracking, $fields, $test_recipient ) {
		$list_fields          = $this->core->explode_fields( $fields );
		$content_email_images = json_decode( $content_email_images, true );
		if ( $content_email_json !== '' ) {
			$content_email_html = '';
		} else {
			$content_email_json = '';
		}
		if ( $campaign_type === 'campaign' ) {
			$display_name = 'Jackmail ' . __( '(campaign):', 'jackmail-newsletters' ) . ' ' . $object;
		} else {
			$display_name = 'Jackmail ' . __( '(workflow):', 'jackmail-newsletters' ) . ' ' . $object;
		}
		if ( $reply_to_email === '' || $reply_to_name === '' || ! is_email( $reply_to_email ) ) {
			$reply_to_email = $sender_email;
			$reply_to_name  = $sender_name;
		}
		$custom_fields_encoded = array();
		$content_search        = array();
		if ( $content_email_json !== '' ) {
			$content_search[] = 'content_email_json';
		}
		if ( $content_email_html !== '' ) {
			$content_search[] = 'content_email_html';
		}
		if ( $content_email_txt !== '' ) {
			$content_search[] = 'content_email_txt';
		}
		$content_search[] = 'object';
		foreach ( $content_search as $search ) {
			if ( $search === 'content_email_json' ) {
				$content_email = $content_email_json;
			} else if ( $search === 'content_email_html' ) {
				$content_email = $content_email_html;
				$content_email = str_replace( '{{', '{&shy;{', $content_email );
				$content_email = str_replace( '}}', '}&shy;}', $content_email );
			} else if ( $search === 'content_email_txt' ) {
				$content_email = $content_email_txt;
			} else {
				$content_email = $object;
			}
			$content_email = str_replace( '{{tracking.unsubscribe}}', '{{unsubscribe}}', $content_email );
			$content_email = str_replace( '((UNSUBSCRIBE_LINK))', '{{unsubscribe}}', $content_email );
			$content_email = str_replace( '{{tracking.unsubscribe.real}}', '{{unsubscribe}}', $content_email );
			$content_email = str_replace( '((WEBCOPY_LINK))', '{{tracking.webcopy}}', $content_email );
			if ( $search === 'content_email_json' ) {
				$json = json_decode( $content_email, true );
				if ( isset( $json['customTags'] ) ) {
					$json_fields = $json['customTags'];
					if ( is_array( $json_fields ) ) {
						foreach ( $json_fields as $json_field ) {
							if ( isset( $json_field['id'], $json_field['name'] ) ) {
								$field_uppercase              = $json_field['name'];
								$field_uppercase_htmlentities = htmlentities( $json_field['name'] );
								if ( strpos( $content_email, '((' . $field_uppercase . '))' ) !== false || strpos( $content_email, '((' . $field_uppercase_htmlentities . '))' ) !== false ) {
									if ( $test_recipient === '' ) {
										if ( $field_uppercase !== 'EMAIL' ) {
											$field_encoded           = $json_field['id'];
											$content_email           = str_replace( '((' . $field_uppercase_htmlentities . '))', '((' . $field_uppercase . '))', $content_email );
											$custom_fields_encoded[] = $field_encoded;
										}
									} else {
										$content_email = str_replace( '((' . $field_uppercase . '))', '(&shy;(' . $field_uppercase . ')&shy;)', $content_email );
										$content_email = str_replace( '((' . $field_uppercase_htmlentities . '))', '(&shy;(' . $field_uppercase_htmlentities . ')&shy;)', $content_email );
									}
								}
							}
						}
					}
				}
				$content_email = $this->core->content_email_json_link_tracking( $content_email, $link_tracking );
			} else {
				$custom_fields_found = array();
				if ( strpos( $content_email, '((EMAIL))' ) !== false ) {
					$custom_fields_found[] = '((EMAIL))';
				}
				foreach ( $list_fields as $field ) {
					$field_uppercase              = '((' . $field . '))';
					$field_uppercase_htmlentities = htmlentities( $field_uppercase );
					if ( strpos( $content_email, $field_uppercase ) !== false || strpos( $content_email, $field_uppercase_htmlentities ) !== false ) {
						$custom_fields_found[] = $field_uppercase;
					}
				}
				$custom_fields_found = array_unique( $custom_fields_found );
				$custom_fields       = array();
				foreach ( $custom_fields_found as $field ) {
					$field_uppercase              = $this->core->str_to_upper( substr( $field, 2, - 2 ) );
					$field_uppercase_htmlentities = htmlentities( $field_uppercase );
					if ( ! in_array( $field_uppercase, $custom_fields ) ) {
						$custom_fields[] = $field_uppercase;
						if ( $test_recipient === '' ) {
							if ( $field_uppercase !== 'EMAIL' ) {
								$field_encoded           = str_replace( '=', '', base64_encode( $field_uppercase ) );
								$custom_fields_encoded[] = $field_encoded;
							} else {
								$field_encoded = 'email';
							}
							$content_email = str_replace( '((' . $field_uppercase . '))', '{{recipient.' . $field_encoded . '}}', $content_email );
							$content_email = str_replace( '((' . $field_uppercase_htmlentities . '))', '{{recipient.' . $field_encoded . '}}', $content_email );
						}
					}
				}
			}
			if ( $search === 'content_email_json' ) {
				$content_email_json = $content_email;
			} else if ( $search === 'content_email_html' ) {
				$content_email_html = $content_email;
			} else if ( $search === 'content_email_txt' ) {
				$content_email_txt = $content_email;
			} else {
				$object = $content_email;
			}
		}
		$custom_fields_encoded = array_unique( $custom_fields_encoded );

		$images_base64   = array();
		$images_urls_ids = array();
		$urls            = array();
		if ( $content_email_json !== '' || $content_email_html !== '' ) {
			if ( $content_email_json !== '' ) {
				$content_email = array(
					'type'    => 'json',
					'content' => $content_email_json
				);
			} else {
				$content_email = array(
					'type'    => 'html',
					'content' => $content_email_html
				);
			}
			$type    = $content_email['type'];
			$content = $content_email['content'];
			$tag     = 'image.cloud.';

			foreach ( $content_email_images as $image ) {
				if ( isset( $image['image_id'], $image['image_type'] ) ) {
					$image_url = '../?jackmail_image=' . $image['image_id'] . '&jackmail_image_type=' . $image['image_type'];
					if ( strpos( $content, $image_url ) !== false ) {
						$url_id            = $this->core->generate_guid();
						$image_url_id      = $tag . $url_id;
						$images_base64[]   = array( 'image_url_id' => $image_url_id, 'image_id' => $image['image_id'], 'image_type' => $image['image_type'] );
						$images_urls_ids[] = $image_url_id;
						$content           = str_replace( $image_url, '{{' . $image_url_id . '}}', $content );
					}
				}
			}
			if ( $type === 'json' ) {
				$content_email_json = $content;
			} else {
				if ( $link_tracking ) {
					$needles           = array(
						' href="',
						' href=\''
					);
					$end_needle_quote1 = '"';
					$end_needle_quote2 = '\'';
					foreach ( $needles as $key => $needle ) {
						$strlen_needle = $this->core->str_len( $needle );
						if ( $key === 0 ) {
							$end_needle = $end_needle_quote1;
						} else {
							$end_needle = $end_needle_quote2;
						}
						$begin_diff = 0;
						$tag        = 'tracking.link.';
						$last_pos   = 0;
						$positions  = array();
						while ( ( $last_pos = strpos( $content, $needle, $last_pos ) ) !== false ) {
							$positions[] = $last_pos;
							$last_pos    = $last_pos + $strlen_needle;
						}
						$positions = array_reverse( $positions );
						foreach ( $positions as $value ) {
							$url_id = $this->core->generate_guid();
							$begin  = $value + $strlen_needle - $begin_diff;
							$substr = substr( $content, $begin );
							$end    = strpos( $substr, $end_needle );
							$url    = substr( $content, $begin, $end );
							if ( $url !== '{{unsubscribe}}' && $url !== '{{tracking.webcopy}}' ) {
								$urls[]  = array(
									'id'       => $url_id,
									'url'      => $url,
									'tracking' => (int) $link_tracking

								);
								$content = substr( $content, 0, $begin ) . '{{' . $tag . $url_id . '}}' . substr( $content, $begin + $end );
							}
						}
					}
				}
				$content_email_html = $content;
			}
		}

		$kind = 'NORMAL';
		if ( $test_recipient !== '' ) {
			$object = '[TEST] ' . $object;
			$kind   = 'TEST';
		}
		$content_size        = $this->get_content_size( $content_email_json, $content_email_html, $content_email_txt );
		$content_images_size = $this->get_content_images_size( $content_email_images );
		return array(
			'kind'                  => $kind,
			'display_name'          => $display_name,
			'object'                => $object,
			'reply_to_email'        => $reply_to_email,
			'reply_to_name'         => $reply_to_name,
			'content_email_json'    => utf8_encode( $content_email_json ),
			'content_email_html'    => $content_email_html,
			'content_email_txt'     => $content_email_txt,
			'custom_fields_encoded' => $custom_fields_encoded,
			'images_base64'         => $images_base64,
			'images_urls_ids'       => $images_urls_ids,
			'urls'                  => $urls,
			'content_size'          => $content_size,
			'content_images_size'   => $content_images_size
		);
	}

	private function generate_campaign_contacts( $id_campaign, $campaign_type, $fields, $test_recipient, $custom_fields_encoded ) {
		global $wpdb;
		$json_contacts      = '';
		$nb_contacts        = 0;
		$nb_contacts_valids = 0;
		$nb_contacts_error  = false;
		$list_fields        = $this->core->explode_fields( $fields );
		if ( $test_recipient === '' ) {
			$fields = array();
			if ( count( $custom_fields_encoded ) > 0 ) {
				$list_fields_encoded = array();
				foreach ( $list_fields as $value ) {
					$list_fields_encoded[] = str_replace( '=', '', base64_encode( $value ) );
				}
				foreach ( $custom_fields_encoded as $field ) {
					$key = array_search( $field, $list_fields_encoded );
					if ( $key !== false ) {
						$fields[ $field ] = 'field' . ( $key + 1 );
					}
				}
			}
			if ( $campaign_type === 'campaign' ) {
				$continue                    = true;
				$continue_limit_begin        = 0;
				$continue_limit              = $this->core->export_send_limit();
				$nb_contacts_check           = 0;
				$unique_contacts_check_array = array();
				while ( $continue ) {
					$continue      = true;
					$contacts_temp = $this->get_campaign_contacts( $id_campaign, (string) $continue_limit_begin, (string) $continue_limit, '', '', '', '' );
					if ( isset( $contacts_temp['nb_contacts'], $contacts_temp['contacts'] ) ) {
						if ( count( $contacts_temp['contacts'] ) < $continue_limit ) {
							$continue = false;
						}
						if ( $continue_limit_begin === 0 ) {
							$nb_contacts_check = $contacts_temp['nb_contacts'];
						} else {
							if ( $contacts_temp['nb_contacts'] !== $nb_contacts_check ) {
								$nb_contacts_error = true;
								break;
							}
						}
						$continue_limit_begin = $continue_limit_begin + $continue_limit;
						foreach ( $contacts_temp['contacts'] as $key => $contact ) {
							$nb_contacts ++;
							if ( $key === $continue_limit - 10 ) {
								$unique_contacts_check_array = array();
							}
							if ( $contact->blacklist === '0' ) {
								if ( $key >= $continue_limit - 10 ) {
									$unique_contacts_check_array[] = $contact->email;
								}
								$insert_contact = true;
								if ( $key <= 10 ) {
									if ( in_array( $contact->email, $unique_contacts_check_array ) ) {
										$insert_contact = false;
									}
								}
								if ( $insert_contact ) {
									$nb_contacts_valids ++;
									$data           = array(
										'id'     => $this->core->generate_guid(),
										'email'  => $contact->email,
										'listId' => $contact->id_list
									);
									$contact_fields = array();
									foreach ( $fields as $subkey => $field ) {
										if ( isset( $contact->$field ) ) {
											$contact_fields[ $subkey ] = $contact->$field;
										}
									}
									if ( count( $contact_fields ) > 0 ) {
										$data['fields'] = $contact_fields;
									}
									$json_contacts .= $this->core->json_encode( $data ) . "\r\n";
								}
							}
						}
					} else {
						$continue = false;
					}
				}
			} else {
				$sql      = "
				SELECT `id_lists`
				FROM `{$wpdb->prefix}jackmail_scenarios`
				WHERE `id` = %s";
				$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
				if ( isset( $campaign->id_lists ) ) {
					$id_lists       = $campaign->id_lists;
					$id_lists_array = $this->core->explode_data( $id_lists );
					$sql_rows       = array();
					foreach ( $id_lists_array as $id_list ) {
						$sql        = "
						SELECT `fields`, `type`, `nb_contacts`
						FROM `{$wpdb->prefix}jackmail_lists`
						WHERE `id` = %s";
						$list       = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
						$list_found = 'normal';
						if ( isset( $list->type ) ) {
							if ( $this->is_plugin_special_list( $list->nb_contacts, $list->type ) ) {
								$plugin = $this->core->explode_data( $list->type );
								if ( isset( $plugin['name'], $plugin['id'] ) ) {
									$plugin_name = $plugin['name'];
									$plugin_id   = $plugin['id'];
									$list_found  = $plugin_name;
									$sql_request = $this->get_plugin_list_sql( $plugin_name, 'ALL', '', $plugin_id, $id_list, 'email', 'ASC', '' );
									if ( isset( $sql_request['rows_for_send_scenario'] ) ) {
										$sql_rows[] = $sql_request['rows_for_send_scenario'];
									}
								}
							}
						}
						if ( $list_found === 'normal' ) {
							if ( $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
								$sql_rows[] = "
								SELECT `email`, `blacklist`, '{$id_list}' AS `id_list`
								FROM `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}`";
							}
						}
					}
					if ( count( $sql_rows ) > 0 ) {
						$sql = '';
						foreach ( $sql_rows as $sql_row ) {
							if ( $sql !== '' ) {
								$sql .= ' UNION ALL ';
							}
							$sql .= '( ' . $sql_row . ' )';
						}
						$sql                         = "
						SELECT `email`, `id_list`, MAX(`blacklist`) AS `blacklist`
						FROM ({$sql}) AS `contacts`
						GROUP BY `email`
						LIMIT %d, %d";
						$continue                    = true;
						$continue_limit_begin        = 0;
						$continue_limit              = $this->core->export_send_limit();
						$unique_contacts_check_array = array();
						while ( $continue ) {
							$contacts_temp        = $wpdb->get_results( $wpdb->prepare( $sql, $continue_limit_begin, $continue_limit ) );
							$continue_limit_begin = $continue_limit_begin + $continue_limit;
							if ( count( $contacts_temp ) < $continue_limit ) {
								$continue = false;
							}
							foreach ( $contacts_temp as $key => $contact ) {
								$nb_contacts ++;
								if ( $key === $continue_limit - 10 ) {
									$unique_contacts_check_array = array();
								}
								if ( $contact->blacklist === '0' ) {
									if ( $key >= $continue_limit - 10 ) {
										$unique_contacts_check_array[] = $contact->email;
									}
									$insert_contact = true;
									if ( $key <= 10 ) {
										if ( in_array( $contact->email, $unique_contacts_check_array ) ) {
											$insert_contact = false;
										}
									}
									if ( $insert_contact ) {
										$nb_contacts_valids ++;
										$data          = array(
											'id'     => $this->core->generate_guid(),
											'email'  => $contact->email,
											'listId' => $contact->id_list
										);
										$json_contacts .= $this->core->json_encode( $data ) . "\r\n";
									}
								}
							}
						}
						unset( $contacts_temp );
					}
				}
			}
			if ( $campaign_type === 'campaign' ) {
				$sql = "
				SELECT `id_lists`
				FROM `{$wpdb->prefix}jackmail_campaigns`
				WHERE `id` = %s";
			} else {
				$sql = "
				SELECT `id_lists`
				FROM `{$wpdb->prefix}jackmail_scenarios`
				WHERE `id` = %s";
			}
			$id_lists = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $id_lists->id_lists ) ) {
				$id_lists = $this->core->explode_data( $id_lists->id_lists );
				if ( count( $id_lists ) > 0 ) {
					$id_lists = implode( ', ', $id_lists );
					$sql      = "
					SELECT `id`, `id_campaign`
					FROM `{$wpdb->prefix}jackmail_lists`
					WHERE `id` IN ({$id_lists})";
					$lists    = $wpdb->get_results( $sql );
					foreach ( $lists as $list ) {
						$unique_list_id = $this->core->get_campaign_id_list( $list->id, $list->id_campaign );
						$json_contacts  = str_replace( '"listId":"' . $list->id . '"', '"listId":"' . $unique_list_id . '"', $json_contacts );
					}
				}
			}
			$unique_list_id = $this->core->get_campaign_id_list( '', $id_campaign );
			$json_contacts  = str_replace( '"listId":"0"', '"listId":"' . $unique_list_id . '"', $json_contacts );
		} else {
			$nb_contacts        = 1;
			$nb_contacts_valids = 1;
			$data               = array(
				'id'     => 'test',
				'email'  => $test_recipient,
				'listId' => 0
			);
			$json_contacts      = $this->core->json_encode( $data ) . "\r\n";
		}
		return array(
			'json_contacts'      => $json_contacts,
			'nb_contacts'        => $nb_contacts,
			'nb_contacts_valids' => $nb_contacts_valids,
			'nb_contacts_error'  => $nb_contacts_error
		);
	}

	private function get_campaign_json( $campaign_id, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html, $content_email_txt, $link_tracking, $send_id, $display_name, $kind, $domain, $urls, $images_urls_ids ) {
		return array(
			'id'          => $campaign_id,
			'displayName' => $display_name,
			'account'     => array(
				'class' => 'SAAccount',
				'id'    => $this->core->get_account_id()
			),
			'sends'       => array(
				array(
					'id'             => $send_id,
					'webcopy'        => 'true',
					'body'           => array(
						'text'   => array(
							'content' => $content_email_txt
						),
						'html'   => array(
							'content' => $content_email_html,
							'urls'    => $content_email_html !== '' ? $urls : array()
						),
						'wizard' => array(
							'content' => $content_email_json
						)
					),
					'kind'           => $kind,
					'unsubscription' => array(
						'template'     => 84,
						'confirmation' => false,
						'adminEmail'   => $sender_email
					),
					'from'           => array(
						'email' => $sender_email,
						'alias' => $sender_name
					),
					'to'             => array(
						'email' => '{{recipient.email}}',
						'alias' => ''
					),
					'replyTo'        => array(
						'email' => $reply_to_email,
						'alias' => $reply_to_name
					),
					'subject'        => $object,
					'priority'       => 1,
					'company'        => '',
					'authenticated'  => true,
					'bodySize'       => 0,
					'domains'        => $domain,
					'checksum'       => base64_encode( sha1( $object . '|¤|' . $content_email_html . '|¤|' . $content_email_txt . '|¤|' . '0', true ) ),
					'infos'          => array(
						'tracking' => $link_tracking ? 1 : 0
					),
					'cloudImages'    => $images_urls_ids
				)
			),
			'infos'       => array(
				'version'  => $this->core->get_jackmail_version(),
				'software' => 'Jackmail'
			)
		);
	}

	private function get_campaign_domain( $is_test ) {
		$domain_found = false;
		$domain       = array();
		$subdomain    = get_option( 'jackmail_domain_sub' );
		if ( $subdomain !== '' ) {
			$domain_is_valid = $this->core->domain_is_valid();
			if ( $domain_is_valid === true ) {
				$domain_found = true;
				$domain       = array(
					'sending'  => $subdomain,
					'tracking' => 'eye.' . $subdomain,
					'images'   => 'img.' . $subdomain
				);
			}
		}
		if ( $domain_found === false ) {
			$type = 'DEFAULT';
			if ( $is_test ) {
				$type = 'TEST';
			}

			$url_global = $this->core->get_jackmail_url_global();
			if ( $url_global === 'http://services.jackmail.local/' ) {
				$type = 'DEFAULT';
			}

			$url      = $this->core->get_jackmail_url_domain() . 'commons/random?premium=true&type=' . $type;
			$headers  = array(
				'content-type' => 'application/json',
				'token'        => $this->core->get_account_token(),
				'x-auth-token' => $this->core->get_account_token(),
				'accountId'    => $this->core->get_account_id()
			);
			$timeout  = 30;
			$response = $this->core->remote_get_retry( $url, $headers, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						if ( isset( $results['common'] ) ) {
							if ( isset( $results['common']['name'] ) ) {
								$subdomain    = $results['common']['name'];
								$domain_found = true;
								$domain       = array(
									'tracking' => 'eye.' . $subdomain,
									'images'   => 'img.' . $subdomain
								);
							}
						}
					}
				}
			}
		}
		if ( $domain_found === true ) {
			$url      = $this->core->get_jackmail_url_domain() . 'commons/random?premium=true&type=SSL';
			$headers  = array(
				'content-type' => 'application/json',
				'token'        => $this->core->get_account_token(),
				'x-auth-token' => $this->core->get_account_token(),
				'accountId'    => $this->core->get_account_id()
			);
			$timeout  = 30;
			$response = $this->core->remote_get_retry( $url, $headers, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						if ( isset( $results['common'] ) ) {
							if ( isset( $results['common']['name'] ) ) {
								$domain['read'] = 'eye.' . $results['common']['name'];
							}
						}
					}
				}
			}
			return $domain;
		}
		return false;
	}

	protected function get_campaign_analysis( $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html, $content_email_txt, $content_email_images, $link_tracking, $fields ) {
		$campaign_id = $this->core->generate_guid();
		$send_id     = $this->core->generate_guid();
		if ( ! is_email( $sender_email ) ) {
			return false;
		}
		if ( $this->core->str_len( $campaign_id ) !== 22 || $this->core->str_len( $send_id ) !== 22 ) {
			return false;
		}
		if ( ! $this->core->json_encode_json_decode_function_exists() ) {
			return false;
		}
		if ( ! $this->core->base64_decode_base64_encode_function_exists() ) {
			return false;
		}
		if ( ! $this->core->gzdecode_gzencode_function_exists() ) {
			return false;
		}
		if ( $content_email_json === '' && $content_email_html === '' && $content_email_txt === '' ) {
			$content_email_html = ' ';
		}
		$campaign_content = $this->generate_campaign_content( 'campaign', $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html, $content_email_txt, $content_email_images, $link_tracking, $fields, '' );
		if ( ! isset( $campaign_content['kind'], $campaign_content['display_name'], $campaign_content['object'],
			$campaign_content['reply_to_email'], $campaign_content['reply_to_name'], $campaign_content['content_email_json'],
			$campaign_content['content_email_html'], $campaign_content['content_email_txt'],
			$campaign_content['custom_fields_encoded'], $campaign_content['images_base64'], $campaign_content['images_urls_ids'],
			$campaign_content['urls'], $campaign_content['content_size'], $campaign_content['content_images_size'] ) ) {
			return false;
		}
		$kind               = 'TEST';
		$display_name       = $campaign_content['display_name'];
		$object             = $campaign_content['object'];
		$reply_to_email     = $campaign_content['reply_to_email'];
		$reply_to_name      = $campaign_content['reply_to_name'];
		$content_email_json = $campaign_content['content_email_json'];
		$content_email_html = $campaign_content['content_email_html'];
		$content_email_txt  = $campaign_content['content_email_txt'];
		$images_urls_ids    = $campaign_content['images_urls_ids'];
		$urls               = $campaign_content['urls'];
		$content_size       = $campaign_content['content_size'];
		if ( $content_size === false ) {
			return false;
		}
		$domain = $this->get_campaign_domain( true );
		if ( $domain === false ) {
			return false;
		}
		if ( ! is_array( $domain ) ) {
			return false;
		}
		$images_contents = array();
		foreach ( $images_urls_ids as $image_url_id ) {
			$images_contents[] = array(
				'id'   => $image_url_id,
				'size' => 1
			);
		}
		$json_campaign                                = $this->get_campaign_json( $campaign_id, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html, $content_email_txt, $link_tracking, $send_id, $display_name, $kind, $domain, $urls, $images_urls_ids );
		$json_campaign['sends'][0]['recipientsCount'] = 1;
		$json_campaign['contents']                    = $images_contents;

		$boundary = $this->core->generate_guid();

		$body = '';

		$body .= '--' . $boundary;
		$body .= "\r\n";
		$body .= 'Content-Disposition: form-data; name="campaign"' . "\r\n";
		$body .= 'Content-Type: application/octet-stream' . "\r\n";
		$body .= "\r\n";
		$body .= gzencode( $this->core->json_encode( $json_campaign ) );
		$body .= "\r\n";

		$body .= '--' . $boundary . '--';

		$url      = $this->core->get_jackmail_url_global() . 'analysis/spam-assassin';
		$headers  = array(
			'content-type' => 'multipart/form-data; boundary=' . $boundary,
			'token'        => $this->core->get_account_token(),
			'x-auth-token' => $this->core->get_account_token(),
			'accountId'    => $this->core->get_account_id(),
			'userId'       => $this->core->get_user_id()
		);
		$timeout  = 600;
		$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
		if ( ! is_array( $response ) ) {
			return false;
		}
		if ( ! isset( $response['body'] ) ) {
			return false;
		}
		$results = json_decode( $response['body'], true );
		if ( ! is_array( $results ) ) {
			return false;
		}
		if ( ! isset( $results['sends'] ) ) {
			return false;
		}
		if ( ! isset( $results['sends'][0] ) ) {
			return false;
		}
		if ( ! isset( $results['sends'][0]['spamAssassin'] ) ) {
			return false;
		}
		$analysis = $results['sends'][0]['spamAssassin'];
		if ( ! isset( $analysis['score'], $analysis['reports'] ) ) {
			return false;
		}
		if ( ! is_array( $analysis['reports'] ) ) {
			return false;
		}
		$analysis_return = array(
			'score'        => max( 0, 10 - $analysis['score'] ),
			'improvements' => array()
		);
		if ( $analysis_return['score'] <= 5 ) {
			if ( isset( $analysis['reports'][0]['score'], $analysis['reports'][0]['report'] ) ) {
				usort( $analysis['reports'], function ( $a, $b ) {
					return strcmp( $b['score'], $a['score'] );
				} );
				$improvements = array();
				foreach ( $analysis['reports'] as $key => $report ) {
					if ( (int) $report['score'] !== 0 ) {
						$improvements[] = $report['report'];
						if ( count( $improvements ) === 10 ) {
							break;
						}
					}
				}
				if ( count( $improvements ) ) {
					$url      = $this->core->get_jackmail_url_ws() . 'content-analysis.php';
					$headers  = array(
						'content-type' => 'application/json'
					);
					$body     = array(
						'language'     => $this->core->get_current_language(),
						'improvements' => $improvements
					);
					$timeout  = 30;
					$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
					if ( is_array( $response ) ) {
						if ( isset( $response['body'] ) ) {
							$results = json_decode( $response['body'], true );
							if ( is_array( $results ) ) {
								$analysis_return['improvements'] = $results;
							}
						}
					}
				}
			}
		}
		return $analysis_return;
	}

	protected function generate_campaign( $id_campaign, $campaign_type, $campaign_id, $data_to_save, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html, $content_email_txt, $content_email_images, Array $content_email_articles, $link_tracking, $send_option, $send_option_date_begin_gmt, $send_option_date_end_gmt, $fields, $nb_contacts_valids_displayed, $test_recipient = '' ) {
		if ( $campaign_id === '' ) {
			$campaign_id = $this->core->generate_guid();
		}
		$send_id = $this->core->generate_guid();
		if ( is_email( $sender_email ) ) {
			if ( $this->core->str_len( $campaign_id ) === 22 && $this->core->str_len( $send_id ) === 22 ) {
				if ( $this->core->json_encode_json_decode_function_exists() ) {
					if ( $this->core->base64_decode_base64_encode_function_exists() ) {
						if ( $this->core->gzdecode_gzencode_function_exists() ) {
							$campaign_content = $this->generate_campaign_content( $campaign_type, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html, $content_email_txt, $content_email_images, $link_tracking, $fields, $test_recipient );
							if ( isset( $campaign_content['kind'], $campaign_content['display_name'], $campaign_content['object'], $campaign_content['reply_to_email'], $campaign_content['reply_to_name'] ) ) {
								if ( isset( $campaign_content['content_email_json'], $campaign_content['content_email_html'], $campaign_content['content_email_txt'] ) ) {
									if ( isset( $campaign_content['custom_fields_encoded'], $campaign_content['images_base64'], $campaign_content['images_urls_ids'] ) ) {
										if ( isset( $campaign_content['urls'], $campaign_content['content_size'], $campaign_content['content_images_size'] ) ) {
											$kind                  = $campaign_content['kind'];
											$display_name          = $campaign_content['display_name'];
											$object                = $campaign_content['object'];
											$reply_to_email        = $campaign_content['reply_to_email'];
											$reply_to_name         = $campaign_content['reply_to_name'];
											$content_email_json    = $campaign_content['content_email_json'];
											$content_email_html    = $campaign_content['content_email_html'];
											$content_email_txt     = $campaign_content['content_email_txt'];
											$custom_fields_encoded = $campaign_content['custom_fields_encoded'];
											$images_base64         = $campaign_content['images_base64'];
											$images_urls_ids       = $campaign_content['images_urls_ids'];
											$urls                  = $campaign_content['urls'];
											$content_size          = $campaign_content['content_size'];
											$content_images_size   = $campaign_content['content_images_size'];
											if ( is_email( $sender_email ) && is_email( $reply_to_email ) ) {
												if ( $content_images_size ) {
													if ( $content_size ) {
														$campaign_contacts = $this->generate_campaign_contacts( $id_campaign, $campaign_type, $fields, $test_recipient, $custom_fields_encoded );
														if ( isset( $campaign_contacts['json_contacts'] ) ) {
															if ( isset( $campaign_contacts['nb_contacts'], $campaign_contacts['nb_contacts_valids'], $campaign_contacts['nb_contacts_error'] ) ) {
																$json_contacts      = $campaign_contacts['json_contacts'];
																$nb_contacts        = $campaign_contacts['nb_contacts'];
																$nb_contacts_valids = $campaign_contacts['nb_contacts_valids'];
																$nb_contacts_error  = $campaign_contacts['nb_contacts_error'];
																if ( ! $nb_contacts_error || ( $nb_contacts_valids_displayed !== (string) $nb_contacts_valids && $nb_contacts_valids_displayed !== '-1' ) ) {
																	if ( $nb_contacts !== 0 && $nb_contacts_valids !== 0 && $json_contacts !== '' ) {
																		$total_credits = $this->core->get_credits_available();
																		if ( $total_credits !== false ) {
																			if ( $total_credits >= $nb_contacts_valids ) {
																				$domain = $this->get_campaign_domain( $test_recipient === '' ? false : true );
																				if ( $domain !== false ) {
																					if ( is_array( $domain ) ) {

																						$boundary = $this->core->generate_guid();

																						$body = '';

																						$body .= '--' . $boundary;
																						$body .= "\r\n";
																						$body .= 'Content-Disposition: form-data; name="recipients-' . $send_id . '"' . "\r\n";
																						$body .= 'Content-Type: application/octet-stream' . "\r\n";
																						$body .= "\r\n";
																						$body .= gzencode( $json_contacts );
																						$body .= "\r\n";

																						if ( $campaign_type === 'scenario' && count( $content_email_articles ) > 0 ) {
																							foreach ( $content_email_articles as $key => $content_email_article ) {
																								$content_email_article = array(
																									'id'          => $content_email_article['id'],
																									'title'       => $content_email_article['title'],
																									'description' => $content_email_article['description'],
																									'link'        => array(
																										'id'  => $this->core->generate_guid(),
																										'url' => $content_email_article['link']
																									),
																									'imageUrl'    => $content_email_article['imageUrl'],
																									'isTracked'   => $link_tracking
																								);
																								$body                  .= '--' . $boundary;
																								$body                  .= "\r\n";
																								$body                  .= 'Content-Disposition: form-data; name="article.' . ( $key + 1 ) . '"' . "\r\n";
																								$body                  .= 'Content-Type: application/octet-stream' . "\r\n";
																								$body                  .= "\r\n";
																								$body                  .= gzencode( $this->core->json_encode( $content_email_article ) );
																								$body                  .= "\r\n";
																							}
																						}

																						$images_files_loaded = true;
																						foreach ( $images_base64 as $image ) {
																							$image_content = $this->core->get_image_file_content( $image['image_id'], $image['image_type'] );
																							if ( $image_content !== false ) {
																								$body .= '--' . $boundary;
																								$body .= "\r\n";
																								$body .= 'Content-Disposition: form-data; name="' . $image['image_url_id'] . '"' . "\r\n";
																								$body .= 'Content-Type: ' . $image['image_type'] . "\r\n";
																								$body .= "\r\n";
																								$body .= $image_content;
																								$body .= "\r\n";
																							} else {
																								$images_files_loaded = false;
																							}
																						}
																						if ( $images_files_loaded ) {
																							$json_campaign = $this->get_campaign_json( $campaign_id, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html, $content_email_txt, $link_tracking, $send_id, $display_name, $kind, $domain, $urls, $images_urls_ids );
																							if ( $test_recipient === '' ) {
																								$current_date_gmt = $this->core->get_current_time_gmt_sql();
																								$status           = 'PROCESS_SENDING';
																								if ( $send_option === 'DATE' ) {

																									$json_campaign['sends'][0]['requestedSendDate'] = $this->core->get_iso_date( $send_option_date_begin_gmt );
																									$status                                         = 'PROCESS_SCHEDULED';
																								}
																								if ( $campaign_type === 'campaign' ) {
																									$update = array(
																										'nb_contacts'              => $nb_contacts,
																										'nb_contacts_valids'       => $nb_contacts_valids,
																										'status'                   => $status,
																										'campaign_id'              => $campaign_id,
																										'send_id'                  => $send_id,
																										'validation_date_gmt'      => $current_date_gmt,
																										'check_blacklist_date_gmt' => $current_date_gmt
																									);
																									if ( $send_option === 'NOW' ) {
																										$update['send_option_date_begin_gmt'] = $current_date_gmt;
																										$update['send_option_date_end_gmt']   = $current_date_gmt;
																									}
																									$this->core->update_campaign( $update, array(
																										'id' => $id_campaign
																									) );
																								} else {
																									$send_option_date_begin_gmt = $current_date_gmt;
																									$send_option_date_end_gmt   = $current_date_gmt;
																									$this->core->insert_scenario_event( array(
																										'id'                         => $id_campaign,
																										'campaign_id'                => $campaign_id,
																										'send_id'                    => $send_id,
																										'nb_contacts'                => $nb_contacts,
																										'nb_contacts_valids'         => $nb_contacts_valids,
																										'data'                       => $data_to_save,
																										'status'                     => $status,
																										'send_option_date_begin_gmt' => $send_option_date_begin_gmt,
																										'send_option_date_end_gmt'   => $send_option_date_end_gmt,
																										'validation_date_gmt'        => $current_date_gmt,
																										'check_blacklist_date_gmt'   => $current_date_gmt
																									) );

																								}
																							} else {
																								if ( $campaign_type === 'campaign' ) {
																									$this->core->update_campaign( array(
																										'campaign_id' => $campaign_id
																									), array(
																										'id' => $id_campaign
																									) );
																								}

																							}

																							$body .= '--' . $boundary;
																							$body .= "\r\n";
																							$body .= 'Content-Disposition: form-data; name="campaign"' . "\r\n";
																							$body .= 'Content-Type: application/octet-stream' . "\r\n";
																							$body .= "\r\n";
																							$body .= gzencode( $this->core->json_encode( $json_campaign ) );
																							$body .= "\r\n";

																							$body .= '--' . $boundary . '--';

																							$url           = $this->core->get_jackmail_url_api() . 'v2/campaigns';
																							$headers       = array(
																								'content-type' => 'multipart/form-data; boundary=' . $boundary,
																								'token'        => $this->core->get_account_token(),
																								'x-auth-token' => $this->core->get_account_token(),
																								'accountId'    => $this->core->get_account_id(),
																								'userId'       => $this->core->get_user_id()
																							);
																							$timeout       = 600;
																							$response_code = $this->core->remote_post_retry_code( $url, $headers, $body, $timeout );
																							if ( $response_code === 202 ) {
																								return array(
																									'message'     => 'OK',
																									'campaign_id' => $campaign_id,
																									'send_id'     => $send_id
																								);
																							} else {
																								if ( $response_code === 403 ) {
																									$message = 'FORBIDDEN';
																								} else {
																									$message = $response_code;
																								}
																								return array(
																									'message'     => $message,
																									'campaign_id' => $campaign_id,
																									'send_id'     => $send_id
																								);
																							}
																						} else {
																							$message = 'ERROR_WHILE_LOADING_IMAGES';
																						}
																					} else {
																						$message = 'ERROR_2';
																					}
																				} else {
																					$message = 'ERROR_3';
																				}
																			} else {
																				$message = 'NOT_ENOUGH_CREDITS';
																			}
																		} else {
																			$message = 'ERROR_WHILE_CHECKING_CREDITS';
																		}
																	} else {
																		$message = 'NO_VALIDS_RECIPIENTS';
																	}
																} else {
																	$message = 'NB_DISPLAYED_CONTACTS';
																}
															} else {
																$message = 'ERROR_4';
															}
														} else {
															$message = 'ERROR_5';
														}
													} else {
														$message = 'MESSAGE_CONTENT_IS_TOO_LARGE';
													}
												} else {
													$message = 'MESSAGE_IMAGES_SIZE_IS_TOO_LARGE';
												}
											} else {
												$message = 'ERROR_6';
											}
										} else {
											$message = 'ERROR_7';
										}
									} else {
										$message = 'ERROR_8';
									}
								} else {
									$message = 'ERROR_9';
								}
							} else {
								$message = 'ERROR_10';
							}
						} else {
							$message = 'MISSING_GZDECODE_OR_GZENCODE';
						}
					} else {
						$message = 'MISSING_BASE64_ENCODE_OR_BASE64_DECODE';
					}
				} else {
					$message = 'MISSING_JSON_ENCODE_OR_JSON_DECODE';
				}
			} else {
				$message = 'MISSING_PHP_OPENSSL';
			}
		} else {
			$message = 'ERROR_1';
		}
		return array(
			'message'     => $message,
			'campaign_id' => $campaign_id,
			'send_id'     => $send_id
		);
	}

}