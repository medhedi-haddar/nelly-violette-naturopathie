<?php


class Jackmail_Statistics_Core {

	protected function get_campaigns( $date_begin, $date_end ) {
		$this->core->progress_campaigns();
		global $wpdb;
		$campaigns   = array();
		$sql         = "
		SELECT `id`, 'campaign' AS `type`, `id_lists`, `name`, `object`, `nb_contacts_valids`, `preview`,
		`updated_date_gmt`, `send_option`, `send_option_date_begin_gmt`, `send_option_date_end_gmt`
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `send_option_date_begin_gmt` >= %s AND `send_option_date_begin_gmt` <= %s
		AND `status` IN ('SENDING', 'SENT')
		ORDER BY `send_option_date_begin_gmt` DESC";
		$campaigns[] = $wpdb->get_results( $wpdb->prepare( $sql, $date_begin, $date_end ) );
		$sql         = "
		SELECT `s`.`id`, 'scenario' AS `type`, `s`.`id_lists`, `s`.`name`, `s`.`object`,
		SUM( `se`.`nb_contacts_valids` ) AS `nb_contacts_valids`, `s`.`preview`, `s`.`updated_date_gmt`, `s`.`send_option`,
		MAX( `se`.`send_option_date_begin_gmt` ) AS `send_option_date_begin_gmt`,
		MAX( `se`.`send_option_date_end_gmt` ) AS `send_option_date_end_gmt`
		FROM `{$wpdb->prefix}jackmail_scenarios` AS `s`,
		`{$wpdb->prefix}jackmail_scenarios_events` AS `se`
		WHERE `s`.`id` = `se`.`id`
		AND `se`.`send_option_date_begin_gmt` >= %s AND `se`.`send_option_date_begin_gmt` <= %s
		AND `se`.`status` IN ('SENDING', 'SENT')
		GROUP BY `s`.`id`
		ORDER BY `se`.`send_option_date_begin_gmt` DESC";
		$campaigns[] = $wpdb->get_results( $wpdb->prepare( $sql, $date_begin, $date_end ) );
		$campaigns   = array_merge( $campaigns[0], $campaigns[1] );
		usort( $campaigns, function ( $a, $b ) {
			return strcmp( $b->send_option_date_begin_gmt, $a->send_option_date_begin_gmt );
		} );
		foreach ( $campaigns as $key => $campaign ) {
			$campaigns[ $key ]->preview = $this->core->content_email_preview_url( $campaign->preview, $campaign->type );
		}
		return $campaigns;
	}

	protected function get_synthesis( $id_campaigns, $period, $selected_date1, $selected_date2 ) {
		global $wpdb;
		$is_statistics_v2   = $this->core->is_statistics_v2();
		$data               = array(
			'period'         => array(),
			'period_openers' => 0
		);
		$id_campaigns_array = $this->core->explode_data( $id_campaigns );
		if ( count( $id_campaigns_array ) > 0 ) {
			$id_campaigns_array = array();
			if ( $period === '1' ) {
				$send_ids           = $this->get_send_ids_from_id_campaigns( $id_campaigns );
				$campaign_ids       = $this->get_campaign_ids_from_id_campaigns( $id_campaigns );
				$id_campaigns_array = $this->core->explode_data( $id_campaigns );
			} else {
				if ( count( $id_campaigns_array ) === 1 ) {
					$campaigns    = $this->get_campaigns( $selected_date1, $selected_date2 );
					$send_ids     = array();
					$campaign_ids = array();
					foreach ( $campaigns as $campaign ) {
						if ( $campaign->id < $id_campaigns_array[0] ) {
							$send_ids             = $this->get_send_ids_from_id_campaigns( $campaign->id );
							$campaign_ids         = $this->get_campaign_ids_from_id_campaigns( $campaign->id );
							$id_campaigns_array[] = $campaign->type . '' . $campaign->id;
							break;
						}
					}
				} else {
					$seconds_in_day       = 60 * 60 * 24;
					$nb_days              = floor( ( strtotime( $selected_date2 ) - strtotime( $selected_date1 ) ) / ( $seconds_in_day ) );
					$selected_date1       = gmdate( 'Y-m-d H:i:s', strtotime( $selected_date1 ) - $seconds_in_day - $nb_days * $seconds_in_day );
					$selected_date2       = gmdate( 'Y-m-d H:i:s', strtotime( $selected_date2 ) - $seconds_in_day - $nb_days * $seconds_in_day );
					$campaigns            = $this->get_campaigns( $selected_date1, $selected_date2 );
					$campaigns_id_period2 = array();
					foreach ( $campaigns as $campaign ) {
						$campaigns_id_period2[] = $campaign->type . '' . $campaign->id;
					}
					$id_campaigns_array = $campaigns_id_period2;
					$send_ids           = $this->get_send_ids_from_id_campaigns( $this->core->implode_data( $id_campaigns_array ) );
					$campaign_ids       = $this->get_campaign_ids_from_id_campaigns( $this->core->implode_data( $id_campaigns_array ) );
				}
			}
			if ( count( $send_ids ) > 0 && count( $campaign_ids ) > 0 ) {
				$id_campaigns = $this->core->implode_data( $id_campaigns_array );
				$sql_ids_data = $this->get_id_campaigns_sql_data( $id_campaigns );
				if ( isset( $sql_ids_data['sql_fields_campaigns'], $sql_ids_data['sql_values_campaigns'], $sql_ids_data['sql_fields_scenarios'], $sql_ids_data['sql_values_scenarios'] ) ) {
					if ( is_array( $sql_ids_data['sql_fields_campaigns'] ) && is_array( $sql_ids_data['sql_values_campaigns'] )
					     && is_array( $sql_ids_data['sql_fields_scenarios'] ) && is_array( $sql_ids_data['sql_values_scenarios'] ) ) {
						if ( count( $sql_ids_data['sql_fields_campaigns'] ) > 0 || count( $sql_ids_data['sql_fields_scenarios'] ) > 0 ) {
							$url     = $this->core->get_jackmail_url_analytics() . 'graphics';
							$headers = array(
								'content-type' => 'application/json',
								'x-auth-token' => $this->core->get_account_token(),
								'accountId'    => $this->core->get_account_id(),
								'userId'       => $this->core->get_user_id()
							);
							if ( $is_statistics_v2 ) {
								$body = array(
									'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
									'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
									'campaignIds'   => $campaign_ids
								);
							} else {
								$body = array(
									'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
									'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
									'sendIds'       => $send_ids
								);
							}
							$timeout  = 30;
							$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
							if ( is_array( $response ) ) {
								if ( isset( $response['body'] ) ) {
									$results = json_decode( $response['body'], true );
									if ( is_array( $results ) ) {
										if ( count( $sql_ids_data['sql_fields_campaigns'] ) > 0 || count( $sql_ids_data['sql_fields_scenarios'] ) > 0 ) {
											$sql_results = array();
											if ( count( $sql_ids_data['sql_fields_campaigns'] ) > 0 ) {
												$sql_ids_data['sql_values_campaigns'][] = $body ['beginInterval'];
												$sql_ids_data['sql_values_campaigns'][] = $body ['endInterval'];
												$sql_ids_data['sql_fields_campaigns']   = implode( ', ', $sql_ids_data['sql_fields_campaigns'] );
												$sql                                    = "
												SELECT `send_option_date_begin_gmt`, `nb_contacts_valids`
												FROM `{$wpdb->prefix}jackmail_campaigns`
												WHERE `id` IN ({$sql_ids_data[ 'sql_fields_campaigns' ]})
												AND `status` IN ('SENDING', 'SENT')
												AND `send_option_date_begin_gmt` >= %s
												AND `send_option_date_begin_gmt` <= %s";
												$sql_results                            = array_merge(
													$sql_results, $wpdb->get_results( $wpdb->prepare( $sql, $sql_ids_data['sql_values_campaigns'] ) )
												);
											}
											if ( count( $sql_ids_data['sql_fields_scenarios'] ) > 0 ) {
												$sql_ids_data['sql_values_scenarios'][] = $body ['beginInterval'];
												$sql_ids_data['sql_values_scenarios'][] = $body ['endInterval'];
												$sql_ids_data['sql_fields_scenarios']   = implode( ', ', $sql_ids_data['sql_fields_scenarios'] );
												$sql                                    = "
												SELECT `send_option_date_begin_gmt`, `nb_contacts_valids`
												FROM `{$wpdb->prefix}jackmail_scenarios_events`
												WHERE `id` IN ({$sql_ids_data[ 'sql_fields_scenarios' ]})
												AND `status` IN ('SENDING', 'SENT')
												AND `send_option_date_begin_gmt` >= %s
												AND `send_option_date_begin_gmt` <= %s";
												$sql_results                            = array_merge(
													$sql_results, $wpdb->get_results( $wpdb->prepare( $sql, $sql_ids_data['sql_values_scenarios'] ) )
												);
											}
											$dates = array();
											foreach ( $sql_results as $result ) {
												$date          = substr( $result->send_option_date_begin_gmt, 0, 14 ) . '00:00';
												$date_position = array_search( $date, $dates );
												if ( $date_position === false ) {
													$dates[]          = $date;
													$data['period'][] = array(
														'date'         => $date,
														'recipients'   => (int) $result->nb_contacts_valids,
														'opens'        => 0,
														'clicks'       => 0,
														'unsubscribes' => 0,
														'bounces'      => 0,
														'read_seconds' => 0
													);
												} else {
													$data['period'][ $date_position ]['recipients'] = $data['period'][ $date_position ]['recipients'] + $result->nb_contacts_valids;
												}
											}
											if ( $is_statistics_v2 ) {
												foreach ( $results as $result ) {
													if ( isset( $result['period'], $result['period_openers'] ) ) {
														if ( is_array( $result['period'] ) && is_array( $result['period_openers'] ) ) {
															foreach ( $result['period'] as $r ) {
																if ( isset( $r['dateHour'], $r['nbHit'], $r['nbOpen'],
																	$r['nbUnsubscribe'], $r['nbBounce'], $r['totalDuration'] ) ) {
																	$date          = str_replace( 'T', ' ', $r['dateHour'] . ':00:00' );
																	$date_position = array_search( $date, $dates );
																	if ( $date_position === false ) {
																		$dates[]          = $date;
																		$data['period'][] = array(
																			'date'         => $date,
																			'recipients'   => 0,
																			'opens'        => $r['nbOpen'],
																			'clicks'       => $r['nbHit'],
																			'unsubscribes' => $r['nbUnsubscribe'],
																			'bounces'      => $r['nbBounce'],
																			'read_seconds' => $r['nbOpen'] > 0 ? intval( $r['totalDuration'] / $r['nbOpen'] ) : 0
																		);
																	} else {
																		$data['period'][ $date_position ]['opens']        = $r['nbOpen'];
																		$data['period'][ $date_position ]['clicks']       = $r['nbHit'];
																		$data['period'][ $date_position ]['unsubscribes'] = $r['nbUnsubscribe'];
																		$data['period'][ $date_position ]['bounces']      = $r['nbBounce'];
																		$data['period'][ $date_position ]['read_seconds'] = $r['totalDuration'];
																	}
																}
															}
															usort( $data['period'], function ( $a, $b ) {
																return strcmp( $a['date'], $b['date'] );
															} );
															$data['period_openers'] = $result['period_openers'][0];
														}
													}
													break;
												}
											} else {
												if ( isset( $results['values'], $results['uniqueOpenCount'] ) ) {
													if ( is_array( $results['values'] ) ) {
														foreach ( $results['values'] as $result ) {
															if ( isset( $result['timestamp'], $result['result'] ) ) {
																if ( is_array( $result['result'] ) ) {
																	if ( isset( $result['result']['hitCount'], $result['result']['openCount'],
																		$result['result']['unsubscribeCount'], $result['result']['bounceCount'], $result['result']['duration'] ) ) {
																		$date          = $this->core->get_mysql_date( $result['timestamp'] );
																		$date_position = array_search( $date, $dates );
																		if ( $date_position === false ) {
																			$dates[]          = $date;
																			$data['period'][] = array(
																				'date'         => $date,
																				'recipients'   => 0,
																				'opens'        => $result['result']['openCount'],
																				'clicks'       => $result['result']['hitCount'],
																				'unsubscribes' => $result['result']['unsubscribeCount'],
																				'bounces'      => $result['result']['bounceCount'],
																				'read_seconds' => $result['result']['openCount'] !== 0 ? intval( $result['result']['duration'] / $result['result']['openCount'] ) : 0
																			);
																		} else {
																			$data['period'][ $date_position ]['opens']        = $result['result']['openCount'];
																			$data['period'][ $date_position ]['clicks']       = $result['result']['hitCount'];
																			$data['period'][ $date_position ]['unsubscribes'] = $result['result']['unsubscribeCount'];
																			$data['period'][ $date_position ]['bounces']      = $result['result']['bounceCount'];
																			$data['period'][ $date_position ]['read_seconds'] = $result['result']['duration'];
																		}
																	}
																}
															}
														}
														usort( $data['period'], function ( $a, $b ) {
															return strcmp( $a['date'], $b['date'] );
														} );
														$data['period_openers'] = $results['uniqueOpenCount'];
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
		return $data;
	}

	protected function get_synthesis_more_actives_contacts( $id_campaigns, $selected_date1, $selected_date2 ) {
		$is_statistics_v2 = $this->core->is_statistics_v2();
		$data             = array();
		$send_ids         = $this->get_send_ids_from_id_campaigns( $id_campaigns );
		$campaign_ids     = $this->get_campaign_ids_from_id_campaigns( $id_campaigns );
		if ( count( $send_ids ) > 0 && count( $campaign_ids ) > 0 ) {
			$url     = $this->core->get_jackmail_url_analytics() . 'mostActiveContact';
			$headers = array(
				'content-type' => 'application/json',
				'x-auth-token' => $this->core->get_account_token(),
				'accountId'    => $this->core->get_account_id(),
				'userId'       => $this->core->get_user_id()
			);
			if ( $is_statistics_v2 ) {
				$body = array(
					'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
					'campaignIds'   => $campaign_ids
				);
			} else {
				$body = array(
					'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
					'sendIds'       => $send_ids
				);
			}
			$timeout       = 30;
			$nb_recipients = $this->get_campaigns_nb_recipients( $id_campaigns, $selected_date1, $selected_date2 );
			$response      = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						if ( $is_statistics_v2 ) {
							foreach ( $results as $key => $result ) {
								if ( isset( $result['email'], $result['nbHit'], $result['nbOpen'] ) ) {
									$data[] = array(
										'email'          => $result['email'],
										'opens_percent'  => $this->get_percent( $result['nbOpen'], $nb_recipients ),
										'clicks_percent' => $this->get_percent( $result['nbHit'], $nb_recipients )
									);
								}
							}
						} else {
							foreach ( $results as $result ) {
								if ( isset( $result['email'], $result['hitCount'], $result['openCount'] ) ) {
									$data[] = array(
										'email'          => $result['email'],
										'opens_percent'  => $this->get_percent( $result['openCount'], $nb_recipients ),
										'clicks_percent' => $this->get_percent( $result['hitCount'], $nb_recipients )
									);
								}
							}
						}
					}
				}
			}
		}
		return $data;
	}

	protected function get_synthesis_timeline( $id_campaigns, $selected_date1, $selected_date2 ) {
		$is_statistics_v2 = $this->core->is_statistics_v2();
		$json             = array();
		$send_ids         = $this->get_send_ids_from_id_campaigns( $id_campaigns );
		$campaign_ids     = $this->get_campaign_ids_from_id_campaigns( $id_campaigns );
		if ( count( $send_ids ) > 0 && count( $campaign_ids ) > 0 ) {
			$url     = $this->core->get_jackmail_url_analytics() . 'timeline';
			$headers = array(
				'content-type' => 'application/json',
				'x-auth-token' => $this->core->get_account_token(),
				'accountId'    => $this->core->get_account_id(),
				'userId'       => $this->core->get_user_id()
			);
			if ( $is_statistics_v2 ) {
				$body = array(
					'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
					'campaignIds'   => $campaign_ids
				);
			} else {
				$body = array(
					'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
					'sendIds'       => $send_ids
				);
			}
			$timeout  = 30;
			$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						if ( $is_statistics_v2 ) {
							foreach ( $results as $key => $result ) {
								if ( isset( $result['email'], $result['date'], $result['kind'] ) ) {
									$event = '';
									if ( $result['kind'] === 'OPEN' ) {
										$event = 'open';
									} else if ( $result['kind'] === 'HIT' ) {
										$event = 'click';
									} else if ( $result['kind'] === 'UNSUBSCRIBE' ) {
										$event = 'unsubscribe';
									}
									if ( $event !== '' ) {
										$json[] = array(
											'date'  => $this->core->get_mysql_date( $result['date'] ),
											'email' => $result['email'],
											'event' => $event
										);
									}
								}
							}
							$json = array_reverse( $json );
						} else {
							foreach ( $results as $result ) {
								if ( isset( $result['email'], $result['date'], $result['event'] ) ) {
									$event = $result['event'];
									if ( $event !== 'SUCCESS' ) {
										$event = $this->core->str_to_lower( $event );
										if ( $event === 'hit' ) {
											$event = 'click';
										}
										$json[] = array(
											'date'  => $this->core->get_mysql_date( $result['date'] ),
											'email' => $result['email'],
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
		return $json;
	}

	protected function add_campaign_contacts_unopened( $new_id_campaign, $id_campaign, $selected_date1, $selected_date2 ) {
		global $wpdb;
		$table_list_contacts = "{$wpdb->prefix}jackmail_campaigns_lists_contacts_{$new_id_campaign}";
		$get_recipients      = $this->get_recipients_unopened( $id_campaign, $selected_date1, $selected_date2, '0', '1' );
		if ( isset( $get_recipients['recipients'], $get_recipients['total_rows'] ) ) {
			$total_rows           = $get_recipients['total_rows'];
			$continue_limit_begin = 0;
			$continue_limit       = $this->core->export_send_limit();
			$nb_calls             = ceil( $total_rows / $continue_limit );
			for ( $i = 0; $i < $nb_calls; $i ++ ) {
				$get_recipients = $this->get_recipients_unopened(
					$id_campaign, $selected_date1, $selected_date2, (string) $continue_limit_begin, (string) $continue_limit
				);
				if ( isset( $get_recipients['recipients'], $get_recipients['total_rows'] ) ) {
					$sql_header_values = array();
					$sql_values        = array();
					foreach ( $get_recipients['recipients'] as $recipient ) {
						$sql_header_values[] = '(%s)';
						$sql_values[]        = $recipient;
					}
					if ( count( $sql_values ) > 0 ) {
						$sql_header_values = implode( ', ', $sql_header_values );
						$sql               = "INSERT IGNORE INTO `{$table_list_contacts}` (`email`) VALUES {$sql_header_values}";
						$wpdb->query( $wpdb->prepare( $sql, $sql_values ) );
					}
				}
				$continue_limit_begin = $continue_limit_begin + $continue_limit;
			}
		}
	}

	private function get_recipients_unopened( $id_campaigns, $selected_date1, $selected_date2, $begin, $limit ) {
		$is_statistics_v2 = $this->core->is_statistics_v2();
		$data             = array(
			'recipients' => array(),
			'total_rows' => 0
		);
		if ( $is_statistics_v2 ) {
			$send_ids     = $this->get_send_ids_from_id_campaigns( $id_campaigns );
			$campaign_ids = $this->get_campaign_ids_from_id_campaigns( $id_campaigns );
			if ( count( $send_ids ) > 0 && count( $campaign_ids ) > 0 ) {
				$url      = $this->core->get_jackmail_url_analytics() . 'unopened' . '?page=' . ( $begin / $limit ) . '&size=' . $limit;
				$headers  = array(
					'content-type' => 'application/json',
					'x-auth-token' => $this->core->get_account_token(),
					'accountId'    => $this->core->get_account_id(),
					'userId'       => $this->core->get_user_id()
				);
				$body     = array(
					'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
					'campaignIds'   => $campaign_ids
				);
				$timeout  = 30;
				$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
				if ( is_array( $response ) ) {
					if ( isset( $response['body'] ) ) {
						$results = json_decode( $response['body'], true );
						if ( is_array( $results ) ) {
							foreach ( $results as $result ) {
								if ( isset( $result['recipients'], $result['total_rows'] ) ) {
									if ( is_array( $result['recipients'] ) && is_array( $result['total_rows'] ) ) {
										$data = array(
											'recipients' => $result['recipients'],
											'total_rows' => $result['total_rows'][0]
										);
									}
								}
							}
						}
					}
				}
			}
		}
		return $data;
	}

	protected function get_recipients( $id_campaigns, $selected_date1, $selected_date2, $begin, $limit, $column = 'email', $order = 'ASC', $search = '' ) {
		$is_statistics_v2 = $this->core->is_statistics_v2();
		$data             = array(
			'recipients' => array(),
			'total_rows' => 0
		);
		$send_ids         = $this->get_send_ids_from_id_campaigns( $id_campaigns );
		$campaign_ids     = $this->get_campaign_ids_from_id_campaigns( $id_campaigns );
		if ( count( $send_ids ) > 0 && count( $campaign_ids ) > 0 ) {
			if ( $is_statistics_v2 ) {
				if ( $column === 'opens' ) {
					$column = 'nbOpen';
				} else if ( $column === 'clicks' ) {
					$column = 'nbHit';
				} else if ( $column === 'desktop' ) {
					$column = 'nbDesktop';
				} else if ( $column === 'mobile' ) {
					$column = 'nbMobile';
				}
				$url = $this->core->get_jackmail_url_analytics() . 'followBehavioural' . '?page=' . ( $begin / $limit ) . '&size=' . $limit . '&column=' . $column . '&order=' . $order;
			} else {
				if ( $column === 'email' ) {
					$column = 'recipient';
				} else if ( $column === 'opens' ) {
					$column = 'open';
				} else if ( $column === 'clicks' ) {
					$column = 'hit';
				}
				$url = $this->core->get_jackmail_url_analytics() . 'followBehavioural' . '?page=' . ( $begin / $limit ) . '&size=' . $limit . '&column=' . $column . '&order=' . $order . '&email=' . urlencode( $search );
			}
			$headers = array(
				'content-type' => 'application/json',
				'x-auth-token' => $this->core->get_account_token(),
				'accountId'    => $this->core->get_account_id(),
				'userId'       => $this->core->get_user_id()
			);
			if ( $is_statistics_v2 ) {
				$body = array(
					'recipientEmail' => $search,
					'beginInterval'  => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'    => $this->core->get_iso_date( $selected_date2 ),
					'campaignIds'    => $campaign_ids
				);
			} else {
				$body = array(
					'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
					'sendIds'       => $send_ids
				);
			}
			$timeout  = 30;
			$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						if ( $is_statistics_v2 ) {
							foreach ( $results as $result ) {
								if ( isset( $result['recipients'], $result['total_rows'] ) ) {
									if ( is_array( $result['recipients'] ) && is_array( $result['total_rows'] ) ) {
										$data = array(
											'recipients' => $result['recipients'],
											'total_rows' => $result['total_rows'][0]
										);
									}
								}
							}
						} else {
							if ( isset( $results['followBehaviourals'], $results['totalSize'] ) ) {
								if ( is_array( $results['followBehaviourals'] ) ) {
									foreach ( $results['followBehaviourals'] as $key => $result ) {
										$results['followBehaviourals'][ $key ]['nbOpen']        = $results['followBehaviourals'][ $key ]['opens'];
										$results['followBehaviourals'][ $key ]['nbOpenDesktop'] = $results['followBehaviourals'][ $key ]['opensDesktop'];
										$results['followBehaviourals'][ $key ]['nbOpenMobile']  = $results['followBehaviourals'][ $key ]['opensMobile'];
										$results['followBehaviourals'][ $key ]['nbHit']         = $results['followBehaviourals'][ $key ]['hits'];
										$results['followBehaviourals'][ $key ]['nbHitDesktop']  = $results['followBehaviourals'][ $key ]['hitsDesktop'];
										$results['followBehaviourals'][ $key ]['nbHitMobile']   = $results['followBehaviourals'][ $key ]['hitsMobile'];
										unset ( $results['followBehaviourals'][ $key ]['opens'], $results['followBehaviourals'][ $key ]['opensDesktop'] );
										unset ( $results['followBehaviourals'][ $key ]['opensMobile'], $results['followBehaviourals'][ $key ]['hits'] );
										unset ( $results['followBehaviourals'][ $key ]['hitsDesktop'], $results['followBehaviourals'][ $key ]['hitsMobile'] );
										unset ( $results['followBehaviourals'][ $key ]['items'] );
									}
								}
								$data = array(
									'recipients' => $results['followBehaviourals'],
									'total_rows' => $results['totalSize']
								);
							}
						}
					}
				}
			}
		}
		return $data;
	}
	protected function get_technologies( $id_campaigns, $selected_date1, $selected_date2 ) {
		$is_statistics_v2 = $this->core->is_statistics_v2();
		$data             = array(
			'browserGroup_browserCategory'            => array(),
			'operatingSystem_operatingSystemCategory' => array(),
			'browserCategory_operatingSystemCategory' => array(),
			'browserGroup_operatingSystem'            => array(),
			'browserGroup_operatingSystemCategory'    => array(),
			'operatingSystem_browserCategory'         => array()
		);
		$send_ids         = $this->get_send_ids_from_id_campaigns( $id_campaigns );
		$campaign_ids     = $this->get_campaign_ids_from_id_campaigns( $id_campaigns );
		if ( count( $send_ids ) > 0 && count( $campaign_ids ) > 0 ) {
			$url     = $this->core->get_jackmail_url_analytics() . 'techno';
			$headers = array(
				'content-type' => 'application/json',
				'x-auth-token' => $this->core->get_account_token(),
				'accountId'    => $this->core->get_account_id(),
				'userId'       => $this->core->get_user_id()
			);
			if ( $is_statistics_v2 ) {
				$body = array(
					'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
					'campaignIds'   => $campaign_ids
				);
			} else {
				$body = array(
					'beginInterval' => $this->core->get_iso_date( $selected_date1 ),
					'endInterval'   => $this->core->get_iso_date( $selected_date2 ),
					'sendIds'       => $send_ids
				);
			}
			$timeout  = 30;
			$response = $this->core->remote_post_retry( $url, $headers, $body, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$results = json_decode( $response['body'], true );
					if ( is_array( $results ) ) {
						if ( $this->core->is_statistics_v2() ) {
							foreach ( $results as $result ) {
								$data = $result;
								break;
							}
						} else {
							if ( isset( $results['browser'], $results['operatingSystem'] ) ) {
								if ( isset( $results['operatingSystemBrowserCateg'], $results['operatingSystemBrowserGroup'] ) ) {
									if ( isset( $results['operatingSystemCategBrowserGroup'], $results['operatingSystemGroupBrowserCateg'] ) ) {
										$data = array(
											'browserGroup_browserCategory'            => $results['browser'],
											'operatingSystem_operatingSystemCategory' => $results['operatingSystem'],
											'browserCategory_operatingSystemCategory' => $results['operatingSystemBrowserCateg'],
											'browserGroup_operatingSystem'            => $results['operatingSystemBrowserGroup'],
											'browserGroup_operatingSystemCategory'    => $results['operatingSystemCategBrowserGroup'],
											'operatingSystem_browserCategory'         => $results['operatingSystemGroupBrowserCateg']
										);
									}
								}
							}
						}
					}
				}
			}
		}
		return $data;
	}

	private function get_send_ids_from_id_campaigns( $id_campaigns ) {
		global $wpdb;
		$sends_ids    = array();
		$sql_ids_data = $this->get_id_campaigns_sql_data( $id_campaigns );
		if ( isset( $sql_ids_data['sql_fields_campaigns'], $sql_ids_data['sql_values_campaigns'],
			$sql_ids_data['sql_fields_scenarios'], $sql_ids_data['sql_values_scenarios'] ) ) {
			if ( is_array( $sql_ids_data['sql_fields_campaigns'] ) && is_array( $sql_ids_data['sql_values_campaigns'] )
			     && is_array( $sql_ids_data['sql_fields_scenarios'] ) && is_array( $sql_ids_data['sql_values_scenarios'] ) ) {
				if ( count( $sql_ids_data['sql_fields_campaigns'] ) > 0 ) {
					$sql_fields_campaigns = implode( ', ', $sql_ids_data['sql_fields_campaigns'] );
					$sql                  = "
					SELECT `send_id`
					FROM `{$wpdb->prefix}jackmail_campaigns`
					WHERE `id` IN ({$sql_fields_campaigns})";
					$results              = $wpdb->get_results( $wpdb->prepare( $sql, $sql_ids_data['sql_values_campaigns'] ) );
					foreach ( $results as $result ) {
						$sends_ids[] = $result->send_id;
					}
				}
				if ( count( $sql_ids_data['sql_fields_scenarios'] ) > 0 ) {
					$sql_fields_scenarios = implode( ', ', $sql_ids_data['sql_fields_scenarios'] );
					$sql                  = "
					SELECT `send_id`
					FROM `{$wpdb->prefix}jackmail_scenarios_events`
					WHERE `id` IN ({$sql_fields_scenarios})";
					$results              = $wpdb->get_results( $wpdb->prepare( $sql, $sql_ids_data['sql_values_scenarios'] ) );
					foreach ( $results as $result ) {
						$sends_ids[] = $result->send_id;
					}
				}
			}
		}
		return $sends_ids;
	}

	private function get_campaign_ids_from_id_campaigns( $id_campaigns ) {
		global $wpdb;
		$campaigns_ids = array();
		$sql_ids_data  = $this->get_id_campaigns_sql_data( $id_campaigns );
		if ( isset( $sql_ids_data['sql_fields_campaigns'], $sql_ids_data['sql_values_campaigns'],
			$sql_ids_data['sql_fields_scenarios'], $sql_ids_data['sql_values_scenarios'] ) ) {
			if ( is_array( $sql_ids_data['sql_fields_campaigns'] ) && is_array( $sql_ids_data['sql_values_campaigns'] )
			     && is_array( $sql_ids_data['sql_fields_scenarios'] ) && is_array( $sql_ids_data['sql_values_scenarios'] ) ) {
				if ( count( $sql_ids_data['sql_fields_campaigns'] ) > 0 ) {
					$sql_fields_campaigns = implode( ', ', $sql_ids_data['sql_fields_campaigns'] );
					$sql                  = "
					SELECT `campaign_id`
					FROM `{$wpdb->prefix}jackmail_campaigns`
					WHERE `id` IN ({$sql_fields_campaigns})";
					$results              = $wpdb->get_results( $wpdb->prepare( $sql, $sql_ids_data['sql_values_campaigns'] ) );
					foreach ( $results as $result ) {
						$campaigns_ids[] = $result->campaign_id;
					}
				}
				if ( count( $sql_ids_data['sql_fields_scenarios'] ) > 0 ) {
					$sql_fields_scenarios = implode( ', ', $sql_ids_data['sql_fields_scenarios'] );
					$sql                  = "
					SELECT `campaign_id`
					FROM `{$wpdb->prefix}jackmail_scenarios_events`
					WHERE `id` IN ({$sql_fields_scenarios})";
					$results              = $wpdb->get_results( $wpdb->prepare( $sql, $sql_ids_data['sql_values_scenarios'] ) );
					foreach ( $results as $result ) {
						$campaigns_ids[] = $result->campaign_id;
					}
				}
			}
		}
		return $campaigns_ids;
	}

	private function get_campaigns_nb_recipients( $id_campaigns, $date_begin, $date_end ) {
		global $wpdb;
		
		$nb_contacts_valids = 0;
		$sql_ids_data       = $this->get_id_campaigns_sql_data( $id_campaigns );
		if ( isset( $sql_ids_data['sql_fields_campaigns'], $sql_ids_data['sql_values_campaigns'],
			$sql_ids_data['sql_fields_scenarios'], $sql_ids_data['sql_values_scenarios'] ) ) {
			if ( is_array( $sql_ids_data['sql_fields_campaigns'] ) && is_array( $sql_ids_data['sql_values_campaigns'] )
			     && is_array( $sql_ids_data['sql_fields_scenarios'] ) && is_array( $sql_ids_data['sql_values_scenarios'] ) ) {
				if ( count( $sql_ids_data['sql_fields_campaigns'] ) > 0 ) {
					$sql_ids_data['sql_values_campaigns'][] = $date_begin;
					$sql_ids_data['sql_values_campaigns'][] = $date_end;
					$sql_ids_data['sql_fields_campaigns']   = implode( ', ', $sql_ids_data['sql_fields_campaigns'] );
					$sql                                    = "
					SELECT SUM( `nb_contacts_valids` ) AS `nb_contacts_valids`
					FROM `{$wpdb->prefix}jackmail_campaigns`
					WHERE `id` IN ({$sql_ids_data[ 'sql_fields_campaigns' ]})
					AND `status` IN ('SENDING', 'SENT')
					AND `send_option_date_begin_gmt` >= %s
					AND `send_option_date_begin_gmt` <= %s";
					$results                                = $wpdb->get_row( $wpdb->prepare( $sql, $sql_ids_data['sql_values_campaigns'] ) );
					if ( isset( $results->nb_contacts_valids ) ) {
						$nb_contacts_valids += $results->nb_contacts_valids;
					}
				}
				if ( count( $sql_ids_data['sql_fields_scenarios'] ) > 0 ) {
					$sql_ids_data['sql_values_scenarios'][] = $date_begin;
					$sql_ids_data['sql_values_scenarios'][] = $date_end;
					$sql_ids_data['sql_fields_scenarios']   = implode( ', ', $sql_ids_data['sql_fields_scenarios'] );
					$sql                                    = "
					SELECT SUM( `nb_contacts_valids` ) AS `nb_contacts_valids`
					FROM `{$wpdb->prefix}jackmail_scenarios_events`
					WHERE `id` IN ({$sql_ids_data[ 'sql_fields_scenarios' ]})
					AND `status` IN ('SENDING', 'SENT')
					AND `send_option_date_begin_gmt` >= %s
					AND `send_option_date_begin_gmt` <= %s";
					$results                                = $wpdb->get_row( $wpdb->prepare( $sql, $sql_ids_data['sql_values_scenarios'] ) );
					if ( isset( $results->nb_contacts_valids ) ) {
						$nb_contacts_valids += $results->nb_contacts_valids;
					}
				}
			}
		}
		return $nb_contacts_valids;
	}

	private function get_percent( $value, $total ) {
		if ( $total !== 0 ) {
			return round( ( $value / $total ) * 100, 2 );
		}
		return 0;
	}

	private function get_id_campaigns_sql_data( $id_campaigns ) {
		$id_campaigns_array   = $this->core->explode_data( $id_campaigns );
		$sql_fields_campaigns = array();
		$sql_values_campaigns = array();
		$sql_fields_scenarios = array();
		$sql_values_scenarios = array();
		foreach ( $id_campaigns_array as $id_campaign ) {
			if ( strpos( $id_campaign, 'campaign' ) !== false ) {
				$id_campaign            = str_replace( 'campaign', '', $id_campaign );
				$sql_fields_campaigns[] = '%s';
				$sql_values_campaigns[] = $id_campaign;
			} else {
				$id_campaign            = str_replace( 'scenario', '', $id_campaign );
				$sql_fields_scenarios[] = '%s';
				$sql_values_scenarios[] = $id_campaign;
			}
		}
		return array(
			'sql_fields_campaigns' => $sql_fields_campaigns,
			'sql_values_campaigns' => $sql_values_campaigns,
			'sql_fields_scenarios' => $sql_fields_scenarios,
			'sql_values_scenarios' => $sql_values_scenarios
		);
	}

}