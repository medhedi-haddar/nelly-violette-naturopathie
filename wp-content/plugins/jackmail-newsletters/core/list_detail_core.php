<?php


class Jackmail_List_Detail_Core extends Jackmail_List_And_Campaign_Core {


	protected function campaign_get_email_lists_detail( $id_campaign, $email ) {
		return $this->get_email_lists_detail( $id_campaign, $email );
	}

	protected function list_get_email_lists_detail( $email ) {
		return $this->get_email_lists_detail( false, $email );
	}

	private function get_email_lists_detail( $id_campaign, $email ) {
		global $wpdb;
		$json = array();
		if ( $id_campaign !== false ) {
			$table_campaigns = "{$wpdb->prefix}jackmail_campaigns";
			$sql             = "
			SELECT `id`, `name`, `fields`
			FROM `{$table_campaigns}`
			WHERE `id` = %s";
			$campaign        = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $campaign->id ) ) {
				$name                = $campaign->name;
				$fields              = $campaign->fields;
				$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
				$contact             = $this->get_list_or_get_campaign_contacts(
					$table_list_contacts, '0', '1', 'email',
					'ASC', $email, ''
				);
				if ( isset( $contact['nb_contacts_search'], $contact['contacts'][0] ) ) {
					$json[] = array(
						'id'      => $id_campaign,
						'type'    => 'campaign',
						'name'    => __( 'View from', 'jackmail-newsletters' ) . ' "' . $name . '"',
						'contact' => $contact['contacts'][0],
						'fields'  => $fields
					);
				}
			}
		}
		$table_lists = "{$wpdb->prefix}jackmail_lists";
		$sql         = "
		SELECT `id`, `name`, `fields`, `type`
		FROM `{$table_lists}`
		ORDER BY `id` DESC";
		$lists       = $wpdb->get_results( $sql );
		foreach ( $lists as $list ) {
			$contact = array();
			$id_list = $list->id;
			$name    = $list->name;
			$fields  = $list->fields;
			$type    = $list->type;
			if ( $type !== '' ) {
				$plugin = $this->core->explode_data( $type );
				if ( isset( $plugin['name'], $plugin['id'] ) ) {
					$plugin_name = $plugin['name'];
					$plugin_id   = $plugin['id'];
					$contact     = $this->get_plugin_list2(
						$plugin_name, '0', '1', $plugin_id, $list, 'email', 'ASC', $email
					);
				}
			} else {
				$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
				$contact             = $this->get_list_or_get_campaign_contacts(
					$table_list_contacts, '0', '1', 'email', 'ASC', $email, ''
				);
			}
			if ( isset( $contact['nb_contacts_search'], $contact['contacts'][0] ) ) {
				$json[] = array(
					'id'      => $id_list,
					'type'    => 'list',
					'name'    => __( 'View from', 'jackmail-newsletters' ) . ' "' . $name . '"',
					'contact' => $contact['contacts'][0],
					'fields'  => $fields
				);
			}
		}
		return $json;
	}

	protected function get_email_detail( $id, $email ) {
		global $wpdb;
		$json  = array(
			'sends'    => 0,
			'clicks'   => 0,
			'opens'    => 0,
			'timeline' => array()
		);
		$sql   = "
		SELECT `id`, `id_campaign`
		FROM `{$wpdb->prefix}jackmail_lists`
		WHERE `id` = %s";
		$lists = $wpdb->get_results( $wpdb->prepare( $sql, $id ) );
		if ( count( $lists ) > 0 ) {
			$id_lists = array();
			foreach ( $lists as $list ) {
				$id_lists[] = $this->core->get_campaign_id_list( $list->id, $list->id_campaign );
			}
			$url      = $this->core->get_jackmail_url_analytics() . 'recipient';
			$headers  = array(
				'content-type' => 'application/json',
				'x-auth-token' => $this->core->get_account_token(),
				'accountId'    => $this->core->get_account_id(),
				'userId'       => $this->core->get_user_id()
			);
			$body     = array(
				'beginInterval'  => $this->core->get_iso_date( date( 'Y-m-d H:i:s', strtotime( '-1 day', strtotime( get_option( 'jackmail_install_date' ) ) ) ) ),
				'endInterval'    => $this->core->get_iso_date( date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ) ),
				'listIds'        => $id_lists,
				'recipientEmail' => $email
			);
			$timeout  = 30;
			$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						foreach ( $results as $result ) {
							if ( $this->core->is_statistics_v2() ) {
								if ( isset( $result['nbOpen'], $result['nbHit'], $result['nbSuccess'] ) ) {
									$json             = array(
										'sends'  => $result['nbSuccess'],
										'clicks' => $result['nbHit'],
										'opens'  => $result['nbOpen']
									);
									$json['timeline'] = array();
									$url              = $this->core->get_jackmail_url_analytics() . 'recipient/timeline';
									$timeout          = 30;
									$response         = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
									if ( is_array( $response ) ) {
										if ( isset( $response['body'] ) ) {
											$subresults = json_decode( $response['body'], true );
											if ( is_array( $subresults ) ) {
												foreach ( $subresults as $subresult ) {
													if ( isset( $subresult['date'] ) && ( isset( $subresult['kind'] ) ) ) {
														$event = '';
														if ( $subresult['kind'] === 'OPEN' ) {
															$event = 'open';
														} else if ( $subresult['kind'] === 'HIT' ) {
															$event = 'click';
														} else if ( $subresult['kind'] === 'UNSUBSCRIBE' ) {
															$event = 'unsubscribe';
														}
														if ( $event !== '' ) {
															$json['timeline'][] = array(
																'date'  => $this->core->get_mysql_date( $subresult['date'] ),
																'event' => $event
															);
														}
													}
												}
												$json['timeline'] = array_reverse( $json['timeline'] );
											}
										}
									}
								}
							} else {
								if ( isset( $result['opens'], $result['hits'], $result['sendIds'] ) ) {
									if ( is_array( $result['sendIds'] ) ) {
										$json             = array(
											'sends'  => count( $result['sendIds'] ),
											'clicks' => $result['hits'],
											'opens'  => $result['opens']
										);
										$json['timeline'] = array();
										$url              = $this->core->get_jackmail_url_analytics() . 'recipient/timeline';
										$timeout          = 30;
										$response         = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
										if ( is_array( $response ) ) {
											if ( isset( $response['body'] ) ) {
												$subresults = json_decode( $response['body'], true );
												if ( is_array( $subresults ) ) {
													foreach ( $subresults as $subresult ) {
														if ( isset( $subresult['event'], $subresult['date'] ) ) {
															$event = $subresult['event'];
															if ( $event !== 'SUCCESS' ) {
																$event = $this->core->str_to_lower( $event );
																if ( $event === 'hit' ) {
																	$event = 'click';
																}
																$json['timeline'][] = array(
																	'date'  => $this->core->get_mysql_date( $subresult['date'] ),
																	'event' => $event
																);
																if ( count( $json ) === 500 ) {
																	break;
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
							break;
						}
					}
				}
			}
		}
		return $json;
	}

	protected function unsubscribe_or_unblacklist_contact( $action ) {
		global $wpdb;
		if ( ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) ) && isset( $_POST['email'] ) ) {
			$email       = $this->core->request_email_data( $_POST['email'] );
			$plugin_name = '';
			if ( isset( $_POST['id_list'] ) ) {
				$id_list             = $this->core->request_text_data( $_POST['id_list'] );
				$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
				$sql                 = "SELECT `type` FROM `{$wpdb->prefix}jackmail_lists` WHERE `id` = %s";
				$list                = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
				if ( isset( $list->type ) ) {
					$list_type = $list->type;
					if ( $list_type !== '' ) {
						$list_type = $this->core->explode_data( $list_type );
						if ( isset( $list_type['name'] ) ) {
							$plugin_name = $list_type['name'];
						}
					}
				}
			} else {
				$id_campaign         = $this->core->request_text_data( $_POST['id_campaign'] );
				$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$id_campaign}";
			}
			$blacklist = '0';
			if ( $action === 'unsubscribe' ) {
				$blacklist = '1';
			}
			if ( $this->core->is_defined_nb_contacts( $plugin_name ) ) {
				$return = $this->core->update_list_contact_or_campaign_list_contact( $table_list_contacts, array(
					'blacklist' => $blacklist
				), array(
					'email' => $email
				) );
			} else {
				if ( $blacklist === '0' ) {
					$return = $this->core->delete_list_contact_or_campaign_list_contact( $table_list_contacts, array(
						'email' => $email
					) );
				} else {
					$sql_values   = array();
					$sql_values[] = $email;
					$sql_values[] = $blacklist;
					$sql          = "
					INSERT INTO `{$table_list_contacts}` (`email`, `blacklist`)
					VALUES (%s, %s)
					ON DUPLICATE KEY UPDATE `email` = VALUES(`email`), `blacklist` = VALUES(`blacklist`)";
					$return       = $wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
				}
			}
			if ( $return !== false ) {
				return true;
			}
		}
		return false;
	}

}