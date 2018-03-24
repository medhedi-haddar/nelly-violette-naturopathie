<?php


class Jackmail_Campaign_Core extends Jackmail_List_And_Campaign_Common_Core {

	protected function get_campaign( $id_campaign ) {
		global $wpdb;
		if ( $id_campaign === '0' ) {
			$campaign = $this->core->get_new_campaign_data();
		} else {
			$sql      = "
			SELECT *
			FROM `{$wpdb->prefix}jackmail_campaigns`
			WHERE `id` = %s
			AND (`status` = 'DRAFT' OR `status` = 'REFUSED')";
			$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $campaign->id ) ) {
				if ( $campaign->status === 'REFUSED' ) {
					$update_return = $this->core->status_to_draft_campaign( $id_campaign, $campaign->status );
					if ( $update_return !== false ) {
						$sql      = "
						SELECT *
						FROM `{$wpdb->prefix}jackmail_campaigns`
						WHERE `id` = %s
						AND `status` = 'DRAFT'";
						$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
						if ( ! isset( $campaign->id ) ) {
							return null;
						}
					} else {
						return null;
					}
				}
				$campaign->content_size        = $this->get_content_size( $campaign->content_email_json, $campaign->content_email_html, $campaign->content_email_txt );
				$campaign->content_images_size = $this->get_content_images_size( $campaign->content_email_images );
			}
		}
		return $campaign;
	}

	protected function get_fields_and_ids( $fields ) {
		$json   = array();
		$fields = $this->core->explode_fields( $fields );
		if ( is_array( $fields ) ) {
			foreach ( $fields as $field ) {
				$field = $this->core->str_to_upper( $field );
				$id    = str_replace( '=', '', base64_encode( $field ) );
				if ( $field === 'EMAIL' ) {
					$id = 'email';
				}
				$json[] = array(
					'id'   => $id,
					'name' => $field
				);
			}
		}
		return $json;
	}

	protected function get_campaign_lists_available( $id_campaign ) {
		global $wpdb;
		$sql   = "
		SELECT `l`.`id`, `l`.`name`, `l`.`id_campaign`, `l`.`nb_contacts` AS `nb_display_contacts`, `l`.`type`
		FROM `{$wpdb->prefix}jackmail_lists` AS `l`
		ORDER BY `l`.`id` DESC";
		$lists = $wpdb->get_results( $sql );
		foreach ( $lists as $key => $list ) {
			$lists[ $key ]->selected = false;
			$id_list                 = $list->id;
			if ( $this->is_plugin_special_list( $list->nb_display_contacts, $list->type ) ) {
				$plugin = $this->core->explode_data( $list->type );
				if ( isset( $plugin['name'], $plugin['id'] ) ) {
					$plugin_name                        = $plugin['name'];
					$plugin_id                          = $plugin['id'];
					$lists[ $key ]->nb_display_contacts = $this->get_plugin_list_nb_contacts( $plugin_name, $id_list, $plugin_id );
				}
			}
		}
		if ( $id_campaign !== '0' ) {
			$table_lists         = "{$wpdb->prefix}jackmail_campaigns";
			$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
			$sql                 = "SELECT `id_lists` FROM `{$table_lists}` WHERE `id` = %s";
			$campaign            = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $campaign->id_lists ) ) {
				$id_lists = $this->core->explode_data( $campaign->id_lists );
				foreach ( $id_lists as $id_list ) {
					$sql         = "SELECT COUNT( * ) FROM `{$table_list_contacts}` WHERE `id_list` = %s";
					$nb_contacts = $wpdb->get_var( $wpdb->prepare( $sql, $id_list ) );
					if ( $nb_contacts !== '0' ) {
						foreach ( $lists as $key => $list ) {
							if ( $list->id === $id_list ) {
								$lists[ $key ]->nb_display_contacts = $nb_contacts;
								$lists[ $key ]->selected            = true;
							}
						}
					}
				}
			}
		}
		return $lists;
	}

	protected function get_or_export_campaign_contacts( $part ) {
		$data = $this->get_list_or_get_campaign_contacts_post_data();
		if ( isset( $data ['id'], $data ['begin'], $data ['sort_by'], $data ['sort_order'], $data ['search'], $data ['targeting_rules'] ) ) {
			if ( $data ['id'] === '0' ) {
				$json = array(
					'list'               => array(
						'id'     => '',
						'fields' => '[]'
					),
					'contacts'           => array(),
					'nb_contacts'        => '0',
					'nb_contacts_search' => '0'
				);
			} else {
				if ( $part === (string) $this->core->grid_limit() ) {
					$this->update_campaign_contacts_blacklists( $data ['id'] );
				}
				$json = $this->get_campaign_contacts(
					$data ['id'], $data ['begin'], $part, $data ['sort_by'],
					$data ['sort_order'], $data['search'], $data ['targeting_rules']
				);
			}
			return $json;
		}
		return array();
	}

	protected function set_campaign_lists( $id_campaign, $id_lists ) {
		global $wpdb;
		$id_lists            = $this->core->explode_data( $id_lists );
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
		$sql                 = "
		SELECT `id_lists`, `fields`
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `id` = %s";
		$current_campaign    = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		if ( isset( $current_campaign->id_lists, $current_campaign->fields ) ) {
			$all_id_lists     = $this->core->explode_data( $current_campaign->id_lists );
			$new_fields       = $this->core->explode_fields( $current_campaign->fields );
			$deleted_id_lists = array_diff( $all_id_lists, $id_lists );
			foreach ( $deleted_id_lists as $id_list ) {
				$this->core->delete_campaign_list_contact( $id_campaign, array(
					'id_list' => $id_list
				) );
			}
			$new_id_lists = array();
			$this->actualize_plugins_lists();
			foreach ( $id_lists as $id_list ) {
				if ( ! in_array( $id_list, $all_id_lists ) ) {
					$sql           = "
					SELECT `fields`, `type`, `nb_contacts`
					FROM `{$wpdb->prefix}jackmail_lists`
					WHERE `id` = %s";
					$list          = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
					$list_found    = '';
					$column_fields = '';
					$sql_rows      = '';
					if ( isset( $list->type ) ) {
						$column_fields = $list->fields;
						$list_found    = 'normal';
						if ( $this->is_plugin_special_list( $list->nb_contacts, $list->type ) ) {
							$plugin = $this->core->explode_data( $list->type );
							if ( isset( $plugin['name'], $plugin['id'] ) ) {
								$plugin_name = $plugin['name'];
								$plugin_id   = $plugin['id'];
								$list_found  = $plugin_name;
								$sql_request = $this->get_plugin_list_sql(
									$plugin_name, 'ALL', '', $plugin_id,
									$id_list, 'email', 'ASC', ''
								);
								if ( isset( $sql_request['rows_for_insert'], $sql_request['fields'] ) ) {
									$sql_rows      = $sql_request['rows_for_insert'];
									$column_fields = $sql_request['fields'];
								}
							}
						}
					}
					if ( $list_found !== '' ) {
						$select_fields          = array();
						$select_fields[]        = '`email`';
						$select_fields[]        = '`blacklist`';
						$select_fields_insert   = array();
						$select_fields_insert[] = '`email`';
						$select_fields_insert[] = '`blacklist`';
						$columns                = $this->core->get_table_columns( 'jackmail_campaigns_lists_contacts_' . $id_campaign );
						if ( $column_fields !== '' ) {
							$fields_array = $this->core->explode_fields( $column_fields );
							foreach ( $fields_array as $i => $value ) {
								$nb_fields = count( $new_fields );
								$field_id  = array_search( $value, $new_fields );
								if ( $field_id === false ) {
									$field = 'field' . ( $nb_fields + 1 );
								} else {
									$field = 'field' . ( $field_id + 1 );
								}
								if ( $field_id === false ) {
									$new_fields[] = $value;
									if ( ! in_array( $field, $columns ) ) {
										$this->core->create_list_field( $table_list_contacts, $field );
										$columns = $this->core->get_table_columns( 'jackmail_campaigns_lists_contacts_' . $id_campaign );
									}
								}
								$field_select           = 'field' . ( $i + 1 );
								$select_fields[]        = '`' . $field . '`';
								$select_fields_insert[] = '`' . $field_select . '` AS `' . $field . '`';
							}
						}
						$select_fields_implode = implode( ', ', $select_fields );
						if ( count( $select_fields ) === count( $select_fields_insert ) ) {
							$duplicate_key_update = array();
							foreach ( $columns as $column ) {
								if ( $column !== 'email' && $column !== 'blacklist' ) {
									if ( in_array( '`' . $column . '`', $select_fields ) ) {
										$duplicate_key_update[] = "`{$column}` = IF( `{$table_list_contacts}`.`blacklist` < VALUES(`blacklist`), VALUES(`{$column}`), `{$table_list_contacts}`.`{$column}` )";
									} else if ( $column === 'id_list' ) {
										$duplicate_key_update[] = "`id_list` = IF( `{$table_list_contacts}`.`blacklist` < VALUES(`blacklist`), VALUES(`id_list`), `{$table_list_contacts}`.`id_list` )";
									} else {
										$duplicate_key_update[] = "`{$column}` = IF( `{$table_list_contacts}`.`blacklist` < VALUES(`blacklist`), '', `{$table_list_contacts}`.`{$column}` )";
									}
								}
							}
							$duplicate_key_update[] = "`blacklist` = IF( `{$table_list_contacts}`.`blacklist` < VALUES(`blacklist`), VALUES(`blacklist`), `{$table_list_contacts}`.`blacklist` )";
							$duplicate_key_update   = implode( ', ', $duplicate_key_update );
							if ( $list_found === 'normal' ) {
								if ( $this->core->check_table_exists( 'jackmail_lists_contacts_' . $id_list ) ) {
									if ( isset( $_POST['search'], $_POST['targeting_rules'], $_POST['contacts_selection'], $_POST['contacts_selection_type'] ) ) {
										$search                  = $this->core->request_text_data( $_POST['search'] );
										$targeting_rules         = $this->core->request_text_data( $_POST['targeting_rules'] );
										$contacts_selection      = $this->core->request_text_data( $_POST['contacts_selection'] );
										$contacts_selection_type = $this->core->request_text_data( $_POST['contacts_selection_type'] );
										$table_list_from         = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
										$contacts_sql            = $this->get_list_or_get_campaign_contacts_sql(
											$table_list_from, 'ALL_FILTERED', '', '', '', $search,
											$targeting_rules, $contacts_selection, $contacts_selection_type
										);
										if ( isset( $contacts_sql['begin'], $contacts_sql['sql_contacts'], $contacts_sql['sql_nb_contacts'],
											     $contacts_sql['sql_nb_contacts_search'], $contacts_sql['sql_values'] ) && is_array( $contacts_sql['sql_values'] ) ) {
											$sql_contacts  = $contacts_sql['sql_contacts'];
											$sql_values    = $contacts_sql['sql_values'];
											$select_fields = '(' . implode( ', ', $select_fields ) . ')';
											$wpdb->query( "TRUNCATE `{$table_list_contacts}`" );
											if ( count( $sql_values ) === 0 ) {
												$sql = "INSERT INTO `{$table_list_contacts}` {$select_fields} {$sql_contacts}";
												$wpdb->query( $sql );
											} else {
												$sql = "INSERT INTO `{$table_list_contacts}` {$select_fields} {$sql_contacts}";
												$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
											}
											$this->core->update_list_contact_or_campaign_list_contact( $table_list_contacts, array(
												'id_list' => $id_list
											), array(
												'id_list' => '0'
											) );
											$update = array(
												'id_lists' => json_encode( array( $id_list ) ),
												'fields'   => $column_fields
											);
											$this->core->updated_campaign_list_contact( $id_campaign, $update );
										}
									} else {
										$select_fields_insert_implode = implode( ', ', $select_fields_insert );
										$sql                          = "
										INSERT INTO `{$table_list_contacts}` ( {$select_fields_implode}, `id_list` ) (
											SELECT {$select_fields_insert_implode}, %s AS `id_list` FROM `{$wpdb->prefix}jackmail_lists_contacts_{$id_list}`
										) ON DUPLICATE KEY UPDATE {$duplicate_key_update}";
										$wpdb->query( $wpdb->prepare( $sql, $id_list ) );
									}
								}
							} else {
								if ( $sql_rows !== '' ) {
									foreach ( $select_fields_insert as $select_field_insert ) {
										$select_field_insert = explode( ' AS ', $select_field_insert );
										if ( count( $select_field_insert ) === 2 ) {
											$field    = $select_field_insert [0];
											$field_as = $select_field_insert [1];
											$sql_rows = str_replace( 'AS ' . $field, 'AS ' . $field_as, $sql_rows );
										}
									}
									$sql = "
									INSERT INTO `{$table_list_contacts}` ( {$select_fields_implode}, `id_list` ) ( {$sql_rows} )
									ON DUPLICATE KEY UPDATE {$duplicate_key_update}";
									$wpdb->query( $sql );
								}
							}
							$new_id_lists[] = $id_list;
						}
					}
				} else {
					$new_id_lists[] = $id_list;
				}
			}
			$new_id_lists = array_unique( $new_id_lists );
			$update       = array(
				'id_lists' => $this->core->implode_data( $new_id_lists ),
				'fields'   => $this->core->implode_fields( $new_fields )
			);
			$this->core->updated_campaign_list_contact( $id_campaign, $update );
		}
	}

	protected function create_campaign(
		$name, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $link_tracking,
		$content_email_json, $content_email_html, $content_email_txt, $send_option,
		$send_option_date_begin_gmt, $send_option_date_end_gmt
	) {
		$result = array(
			'success'             => false,
			'id'                  => '0',
			'content_size'        => false,
			'content_images_size' => false,
			'content_email_json'  => '',
			'content_email_html'  => '',
			'content_email_txt'   => ''
		);
		if ( $send_option === 'NOW' ) {
			$send_option_date_begin_gmt = '0000-00-00 00:00:00';
			$send_option_date_end_gmt   = '0000-00-00 00:00:00';
		}
		$preview       = $this->core->generate_jackmail_preview_filename();
		$content_email = $this->core->set_content_email( 'campaign', '0', $preview, $content_email_json, $content_email_html, $content_email_txt );
		if ( $content_email !== false ) {
			if ( isset( $content_email['content_email_json'], $content_email['content_email_html'], $content_email['content_email_txt'], $content_email['content_email_images'] ) ) {
				$content_email_json   = $content_email['content_email_json'];
				$content_email_html   = $content_email['content_email_html'];
				$content_email_txt    = $content_email['content_email_txt'];
				$content_email_images = $content_email['content_email_images'];
				$current_date_gmt     = $this->core->get_current_time_gmt_sql();
				$id_campaign          = $this->core->insert_campaign( array(
					'id_lists'                   => '[]',
					'fields'                     => '[]',
					'name'                       => $name,
					'object'                     => $object,
					'sender_name'                => $sender_name,
					'sender_email'               => $sender_email,
					'reply_to_name'              => $reply_to_name,
					'reply_to_email'             => $reply_to_email,
					'link_tracking'              => $link_tracking,
					'content_email_json'         => $content_email_json,
					'content_email_html'         => $content_email_html,
					'content_email_txt'          => $content_email_txt,
					'content_email_images'       => $content_email_images,
					'preview'                    => $preview,
					'created_date_gmt'           => $current_date_gmt,
					'updated_date_gmt'           => $current_date_gmt,
					'updated_by'                 => get_current_user_id(),
					'status'                     => 'DRAFT',
					'send_option'                => $send_option,
					'send_option_date_begin_gmt' => $send_option_date_begin_gmt,
					'send_option_date_end_gmt'   => $send_option_date_end_gmt
				) );
				if ( $id_campaign !== false && $id_campaign !== 0 && $id_campaign !== '0' ) {
					if ( $this->core->create_campaign_list_table( $id_campaign ) !== false ) {
						$result = array(
							'success'             => true,
							'id'                  => $id_campaign,
							'content_size'        => $this->get_content_size( $content_email_json, $content_email_html, $content_email_txt ),
							'content_images_size' => $this->get_content_images_size( $content_email_images ),
							'content_email_json'  => $content_email_json,
							'content_email_html'  => $content_email_html,
							'content_email_txt'   => $content_email_txt
						);
					}
				}
			}
		}
		return $result;
	}

	protected function update_campaign(
		$id_campaign, $name, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email,
		$link_tracking, $content_email_json, $content_email_html, $content_email_txt, $send_option,
		$send_option_date_begin_gmt, $send_option_date_end_gmt
	) {
		global $wpdb;
		$result = array(
			'success'             => false,
			'content_size'        => false,
			'content_images_size' => false,
			'content_email_json'  => '',
			'content_email_html'  => '',
			'content_email_txt'   => ''
		);
		if ( $send_option === 'NOW' ) {
			$send_option_date_begin_gmt = '0000-00-00 00:00:00';
			$send_option_date_end_gmt   = '0000-00-00 00:00:00';
		}
		$current_date_gmt    = $this->core->get_current_time_gmt_sql();
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
		if ( $this->core->check_table_exists( $table_list_contacts, false ) ) {
			$sql      = "
			SELECT `preview`
			FROM `{$wpdb->prefix}jackmail_campaigns`
			WHERE `id` = %s";
			$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $campaign->preview ) ) {
				$content_email = $this->core->set_content_email(
					'campaign', $id_campaign, $campaign->preview,
					$content_email_json, $content_email_html, $content_email_txt
				);
				if ( $content_email !== false ) {
					if ( isset( $content_email['content_email_json'], $content_email['content_email_html'],
						$content_email['content_email_txt'], $content_email['content_email_images'] ) ) {
						$content_email_json   = $content_email['content_email_json'];
						$content_email_html   = $content_email['content_email_html'];
						$content_email_txt    = $content_email['content_email_txt'];
						$content_email_images = $content_email['content_email_images'];
						$nb_contacts          = $wpdb->get_var( "SELECT COUNT( * ) FROM `{$table_list_contacts}`" );
						$nb_contacts_valids   = $wpdb->get_var( "SELECT COUNT( * ) FROM `{$table_list_contacts}` WHERE `blacklist` = '0'" );
						$update_return        = $this->core->update_campaign( array(
							'name'                       => $name,
							'object'                     => $object,
							'sender_name'                => $sender_name,
							'sender_email'               => $sender_email,
							'reply_to_name'              => $reply_to_name,
							'reply_to_email'             => $reply_to_email,
							'link_tracking'              => $link_tracking,
							'content_email_json'         => $content_email_json,
							'content_email_html'         => $content_email_html,
							'content_email_txt'          => $content_email_txt,
							'content_email_images'       => $content_email_images,
							'nb_contacts'                => $nb_contacts,
							'nb_contacts_valids'         => $nb_contacts_valids,
							'send_option'                => $send_option,
							'send_option_date_begin_gmt' => $send_option_date_begin_gmt,
							'send_option_date_end_gmt'   => $send_option_date_end_gmt,
							'updated_date_gmt'           => $current_date_gmt,
							'updated_by'                 => get_current_user_id()
						), array(
							'id'     => $id_campaign,
							'status' => 'DRAFT'
						) );
						if ( $update_return !== false ) {
							$result = array(
								'success'             => true,
								'content_size'        => $this->get_content_size( $content_email_json, $content_email_html, $content_email_txt ),
								'content_images_size' => $this->get_content_images_size( $content_email_images ),
								'content_email_json'  => $content_email_json,
								'content_email_html'  => $content_email_html,
								'content_email_txt'   => $content_email_txt
							);
						}
					}
				}
			}
		}
		return $result;
	}

	protected function create_campaign_with_data() {
		$result   = array(
			'success' => false,
			'id'      => '0'
		);
		$campaign = $this->core->get_new_campaign_data();
		$preview  = $this->core->generate_jackmail_preview_filename();
		if ( isset( $campaign['content_email_json'], $campaign['content_email_html'], $campaign['content_email_txt'] ) ) {
			$content_email = $this->core->set_content_email(
				'campaign', '0', $preview, $campaign['content_email_json'],
				$campaign['content_email_html'], $campaign['content_email_txt']
			);
			if ( $content_email !== false ) {
				if ( isset( $content_email['content_email_json'], $content_email['content_email_html'],
					$content_email['content_email_txt'], $content_email['content_email_images'] ) ) {
					$current_date_gmt = $this->core->get_current_time_gmt_sql();
					$id_campaign      = $this->core->insert_campaign( array(
						'id_lists'                   => $campaign['id_lists'],
						'fields'                     => $campaign['fields'],
						'name'                       => $campaign['name'],
						'object'                     => $campaign['object'],
						'sender_name'                => $campaign['sender_name'],
						'sender_email'               => $campaign['sender_email'],
						'reply_to_name'              => $campaign['reply_to_name'],
						'reply_to_email'             => $campaign['reply_to_email'],
						'link_tracking'              => $campaign['link_tracking'],
						'content_email_json'         => $content_email['content_email_json'],
						'content_email_html'         => $content_email['content_email_html'],
						'content_email_txt'          => $content_email['content_email_txt'],
						'content_email_images'       => $content_email['content_email_images'],
						'preview'                    => $preview,
						'created_date_gmt'           => $current_date_gmt,
						'updated_date_gmt'           => $current_date_gmt,
						'updated_by'                 => get_current_user_id(),
						'status'                     => $campaign['status'],
						'send_option'                => $campaign['send_option'],
						'send_option_date_begin_gmt' => $campaign['send_option_date_begin_gmt'],
						'send_option_date_end_gmt'   => $campaign['send_option_date_end_gmt']
					) );
					if ( $id_campaign !== false && $id_campaign !== 0 && $id_campaign !== '0' ) {
						if ( $this->core->create_campaign_list_table( $id_campaign ) !== false ) {
							$result = array(
								'success' => true,
								'id'      => $id_campaign
							);
						}
					}
				}
			}
		}
		return $result;
	}

	protected function campaign_last_step_checker( $id_campaign ) {
		global $wpdb;
		$json                         = array(
			'nb_contacts_valids' => '0',
			'nb_credits_before'  => '0',
			'nb_credits_after'   => '0',
			'nb_credits_checked' => false,
			'subscription_type'  => '',
			'domain_is_valid'    => $this->core->domain_is_valid()
		);
		$table_campaign_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
		if ( $this->core->check_table_exists( $table_campaign_list_contacts, false ) ) {
			$this->update_campaign_contacts_blacklists( $id_campaign );
			$sql      = "
			SELECT COUNT( * ) AS `nb_contacts_valids`
			FROM `{$table_campaign_list_contacts}`
			WHERE `blacklist` = '0'";
			$campaign = $wpdb->get_row( $sql );
			if ( isset( $campaign->nb_contacts_valids ) ) {
				$json['nb_contacts_valids'] = $campaign->nb_contacts_valids;
				$credits_available          = $this->core->get_credits_available( true );
				if ( $credits_available !== false ) {
					$json['nb_credits_before']  = (string) $credits_available['nb_credits'];
					$json['nb_credits_after']   = (string) ( $credits_available['nb_credits'] - $json['nb_contacts_valids'] );
					$json['nb_credits_checked'] = true;
					$json['subscription_type']  = $credits_available['subscription_type'];
				}
			}
		}
		return $json;
	}

	protected function campaign_last_step_checker_analysis( $id_campaign ) {
		global $wpdb;
		$json = array(
			'analysis_checked' => false,
			'analysis'         => array()
		);
		$sql  = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `id` = %s
		AND `status` = 'DRAFT'";
		$data = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		if ( isset( $data->id ) ) {
			$fields                   = $data->fields;
			$object                   = $data->object;
			$sender_name              = $data->sender_name;
			$sender_email             = $data->sender_email;
			$reply_to_name            = $data->reply_to_name;
			$reply_to_email           = $data->reply_to_email;
			$content_email_json       = $data->content_email_json;
			$content_email_html       = $data->content_email_html;
			$content_email_txt        = $data->content_email_txt;
			$content_email_images     = $data->content_email_images;
			$link_tracking            = $data->link_tracking;
			$campaign_analysis_result = $this->get_campaign_analysis(
				$object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json,
				$content_email_html, $content_email_txt, $content_email_images, $link_tracking, $fields
			);
			if ( $campaign_analysis_result !== false ) {
				$json['analysis_checked'] = true;
				$json['analysis']         = $campaign_analysis_result;
			}
		}
		return $json;
	}

	private function update_campaign_contacts_blacklists( $id_campaign ) {
		global $wpdb;
		$table_campaign_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
		if ( $this->core->check_table_exists( $table_campaign_list_contacts, false ) ) {
			$sql      = "
			SELECT `id_lists`
			FROM `{$wpdb->prefix}jackmail_campaigns`
			WHERE `id` = %s
			AND `status` = 'DRAFT'";
			$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $campaign->id_lists ) ) {
				$id_lists = $this->core->explode_data( $campaign->id_lists );
				foreach ( $id_lists as $key => $id_list ) {
					$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
					if ( $this->core->check_table_exists( $table_list_contacts, false ) ) {
						$sql = "
						UPDATE `{$table_campaign_list_contacts}` AS `cl`
						INNER JOIN `{$table_list_contacts}` AS `l` ON `l`.`email` = `cl`.`email` AND `l`.`blacklist` != `cl`.`blacklist`
						SET `cl`.`blacklist` = `l`.`blacklist`
						WHERE `cl`.`id_list` = %s";
						$wpdb->query( $wpdb->prepare( $sql, $id_list ) );
					}
				}
			}
		}
	}

	protected function send_campaign_test( $id_campaign, $test_recipient ) {
		global $wpdb;
		$result = array(
			'success' => false,
			'message' => 'ERROR'
		);
		$sql    = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `id` = %s
		AND `status` = 'DRAFT'";
		$data   = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		if ( isset( $data->id ) ) {
			$campaign_id                = $data->campaign_id;
			$fields                     = $data->fields;
			$object                     = $data->object;
			$sender_name                = $data->sender_name;
			$sender_email               = $data->sender_email;
			$reply_to_name              = $data->reply_to_name;
			$reply_to_email             = $data->reply_to_email;
			$content_email_json         = $data->content_email_json;
			$content_email_html         = $data->content_email_html;
			$content_email_txt          = $data->content_email_txt;
			$content_email_images       = $data->content_email_images;
			$link_tracking              = $data->link_tracking;
			$send_option                = 'NOW';
			$send_option_date_begin_gmt = '';
			$send_option_date_end_gmt   = '';
			if ( is_email( $test_recipient ) ) {
				$campaign_result = $this->generate_campaign(
					$id_campaign, 'campaign', $campaign_id, '', $object, $sender_name,
					$sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html,
					$content_email_txt, $content_email_images, array(), $link_tracking, $send_option,
					$send_option_date_begin_gmt, $send_option_date_end_gmt,
					$fields, '1', $test_recipient
				);
				$message         = $campaign_result['message'];
				$result          = array(
					'success' => $message === 'OK' ? true : false,
					'message' => $message
				);
			}
		}
		return $result;
	}

	protected function send_campaign(
		$id_campaign, $send_option, $send_option_date_begin_gmt,
		$send_option_date_end_gmt, $nb_contacts_valids_displayed
	) {
		global $wpdb;
		$result = array(
			'success' => false,
			'message' => 'ERROR'
		);
		if ( $send_option === 'NOW' ) {
			$send_option_date_begin_gmt = '0000-00-00 00:00:00';
			$send_option_date_end_gmt   = '0000-00-00 00:00:00';
		}
		$current_date_gmt = $this->core->get_current_time_gmt_sql();
		$update_return    = $this->core->update_campaign( array(
			'send_option'                => $send_option,
			'send_option_date_begin_gmt' => $send_option_date_begin_gmt,
			'send_option_date_end_gmt'   => $send_option_date_end_gmt,
			'updated_date_gmt'           => $current_date_gmt
		), array(
			'id'     => $id_campaign,
			'status' => 'DRAFT'
		) );
		if ( $update_return !== false ) {
			$sql  = "
			SELECT *
			FROM `{$wpdb->prefix}jackmail_campaigns`
			WHERE `id` = %s
			AND `status` = 'DRAFT'";
			$data = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $data->id ) ) {
				$campaign_id                = $data->campaign_id;
				$fields                     = $data->fields;
				$object                     = $data->object;
				$sender_name                = $data->sender_name;
				$sender_email               = $data->sender_email;
				$reply_to_name              = $data->reply_to_name;
				$reply_to_email             = $data->reply_to_email;
				$content_email_json         = $data->content_email_json;
				$content_email_html         = $data->content_email_html;
				$content_email_txt          = $data->content_email_txt;
				$content_email_images       = $data->content_email_images;
				$link_tracking              = $data->link_tracking;
				$send_option                = $data->send_option;
				$send_option_date_begin_gmt = $data->send_option_date_begin_gmt;
				$send_option_date_end_gmt   = $data->send_option_date_end_gmt;
				$nb_contacts_valids         = $data->nb_contacts_valids;
				if ( $nb_contacts_valids <= $nb_contacts_valids_displayed ) {
					$campaign_result = $this->generate_campaign(
						$id_campaign, 'campaign', $campaign_id, '', $object, $sender_name,
						$sender_email, $reply_to_name, $reply_to_email, $content_email_json, $content_email_html,
						$content_email_txt, $content_email_images, array(), $link_tracking, $send_option,
						$send_option_date_begin_gmt, $send_option_date_end_gmt, $fields, $nb_contacts_valids_displayed
					);
					$message         = $campaign_result['message'];
					if ( $message === 'OK' ) {
						$result = array(
							'success' => true,
							'message' => $message
						);
					} else {
						$this->core->update_campaign( array(
							'status' => 'DRAFT'
						), array(
							'id' => $id_campaign
						) );
						$result = array(
							'success' => false,
							'message' => $message
						);
					}
				} else {
					$result = array(
						'success' => false,
						'message' => 'NB_DISPLAYED_CONTACTS'
					);
				}
			}
		}
		return $result;
	}

}