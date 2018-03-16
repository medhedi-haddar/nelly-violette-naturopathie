<?php


class Jackmail_Campaign_Scenario_Core extends Jackmail_List_And_Campaign_Common_Core {

	protected function get_scenario( $id_campaign, $choice ) {
		global $wpdb;
		if ( $id_campaign === '0' ) {
			$sender_name_and_email = $this->core->get_sender_name_and_email();
			$sender_name           = '';
			$sender_email          = '';
			if ( isset( $sender_name_and_email['sender_name'], $sender_name_and_email['sender_email'] ) ) {
				$sender_name  = $sender_name_and_email['sender_name'];
				$sender_email = $sender_name_and_email['sender_email'];
			}
			$campaign = array(
				'id'                  => '0',
				'id_lists'            => '[]',
				'name'                => __( 'Campaign with no name', 'jackmail-newsletters' ),
				'object'              => '',
				'sender_name'         => $sender_name,
				'sender_email'        => $sender_email,
				'reply_to_name'       => '',
				'reply_to_email'      => '',
				'nb_contacts'         => '0',
				'nb_contacts_valids'  => '0',
				'link_tracking'       => get_option( 'jackmail_link_tracking' ),
				'content_email_json'  => '',
				'content_email_html'  => '',
				'content_email_txt'   => '',
				'status'              => 'DRAFT',
				'content_size'        => true,
				'content_images_size' => true,
				'send_option'         => $choice,
				'already_send'        => false
			);
			if ( $choice === 'publish_a_post' ) {
				$campaign['post_type']         = 'post';
				$campaign['post_categories']   = '[]';
				$campaign['nb_posts_content']  = '1';
				$campaign['periodicity_type']  = 'NOW';
				$campaign['periodicity_value'] = '1';
				$campaign['event_date_gmt']    = '0000-00-00 00:00:00';
			} else if ( $choice === 'automated_newsletter' || $choice === 'woocommerce_automated_newsletter' ) {
				$campaign['post_type'] = 'post';
				if ( $choice === 'woocommerce_automated_newsletter' ) {
					$campaign['post_type'] = 'product';
				}
				$campaign['post_categories']   = '[]';
				$campaign['nb_posts_content']  = '5';
				$campaign['periodicity_type']  = 'POSTS';
				$campaign['periodicity_value'] = '5';
				$campaign['event_date_gmt']    = '0000-00-00 00:00:00';
			}
		} else {
			$sql      = "
			SELECT *
			FROM `{$wpdb->prefix}jackmail_scenarios`
			WHERE `id` = %s";
			$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $campaign->id ) ) {
				$campaign->content_size        = $this->get_content_size( $campaign->content_email_json, $campaign->content_email_html, $campaign->content_email_txt );
				$campaign->content_images_size = $this->get_content_images_size( $campaign->content_email_images );
				$campaign                      = $this->explode_scenario_data_field( $campaign );
				if ( $campaign->send_option === 'publish_a_post' ) {
					$campaign->event_date_gmt = '0000-00-00 00:00:00';
				}
				$sql     = "
				SELECT COUNT( * ) AS `nb_send`
				FROM `{$wpdb->prefix}jackmail_scenarios_events`
				WHERE `id` = %s";
				$nb_send = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
				if ( isset( $nb_send->nb_send ) ) {
					$campaign->already_send = ( $nb_send->nb_send === '0' ) ? false : true;
				} else {
					$campaign->already_send = false;
				}
			}
		}
		return $campaign;
	}

	protected function get_scenario_lists_available( $id_campaign ) {
		global $wpdb;
		$this->core->progress_contacts_blacklist();
		$this->actualize_plugins_lists();
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
			$table_lists = "{$wpdb->prefix}jackmail_scenarios";
			$sql         = "SELECT `id_lists` FROM `{$table_lists}` WHERE `id` = %s";
			$campaign    = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
			if ( isset( $campaign->id_lists ) ) {
				$id_lists = $this->core->explode_data( $campaign->id_lists );
				foreach ( $id_lists as $id_list ) {
					foreach ( $lists as $key => $list ) {
						if ( $list->id === $id_list ) {
							$lists[ $key ]->selected = true;
						}
					}
				}
			}
		}
		return $lists;
	}

	protected function create_scenario(
		$id_lists, $send_option, $name, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $link_tracking,
		$content_email_json, $post_type, $post_categories, $nb_posts_content, $periodicity_type, $periodicity_value, $event_date_gmt
	) {
		$result             = array(
			'success'             => false,
			'id'                  => '0',
			'content_size'        => false,
			'content_images_size' => false,
			'content_email_json'  => '',
			'content_email_html'  => '',
			'content_email_txt'   => ''
		);
		$content_email_html = '';
		$content_email_txt  = '';
		$data               = '';
		$current_date_gmt   = $this->core->get_current_time_gmt_sql();
		if ( $send_option === 'publish_a_post' || $send_option === 'automated_newsletter' || $send_option === 'woocommerce_automated_newsletter' ) {
			if ( ( $send_option === 'automated_newsletter' || $send_option === 'woocommerce_automated_newsletter' ) && $periodicity_type !== 'POSTS' ) {

			} else {
				$event_date_gmt = $current_date_gmt;
			}
			if ( ( $send_option === 'publish_a_post' || $send_option === 'automated_newsletter' ) && $post_type !== 'post' ) {
				$post_categories = '[]';
			}
			$data_fields = array(
				'post_type'         => $post_type,
				'post_categories'   => $post_categories,
				'nb_posts_content'  => $nb_posts_content,
				'periodicity_type'  => $periodicity_type,
				'periodicity_value' => $periodicity_value,
				'event_date_gmt'    => $event_date_gmt
			);
			$data        = $this->core->implode_data( $data_fields );
		}
		$preview       = $this->core->generate_jackmail_preview_filename();
		$content_email = $this->core->set_content_email( 'scenario', '0', $preview, $content_email_json, $content_email_html, $content_email_txt );
		if ( $content_email !== false ) {
			if ( isset( $content_email['content_email_json'], $content_email['content_email_html'], $content_email['content_email_txt'],
				$content_email['content_email_images'] ) ) {
				$content_email_json   = $content_email['content_email_json'];
				$content_email_html   = $content_email['content_email_html'];
				$content_email_txt    = $content_email['content_email_txt'];
				$content_email_images = $content_email['content_email_images'];
				$id_campaign          = $this->core->insert_scenario( array(
					'id_lists'             => $id_lists,
					'name'                 => $name,
					'object'               => $object,
					'sender_name'          => $sender_name,
					'sender_email'         => $sender_email,
					'reply_to_name'        => $reply_to_name,
					'reply_to_email'       => $reply_to_email,
					'link_tracking'        => $link_tracking,
					'content_email_json'   => $content_email_json,
					'content_email_html'   => $content_email_html,
					'content_email_txt'    => $content_email_txt,
					'content_email_images' => $content_email_images,
					'preview'              => $preview,
					'created_date_gmt'     => $current_date_gmt,
					'updated_date_gmt'     => $current_date_gmt,
					'updated_by'           => get_current_user_id(),
					'status'               => 'DRAFT',
					'send_option'          => $send_option,
					'data'                 => $data
				) );
				if ( $id_campaign !== false && $id_campaign !== 0 && $id_campaign !== '0' ) {
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
		return $result;
	}

	protected function update_scenario(
		$id_campaign, $id_lists, $send_option, $name, $object, $sender_name, $sender_email, $reply_to_name, $reply_to_email, $link_tracking,
		$content_email_json, $post_type, $post_categories, $nb_posts_content, $periodicity_type, $periodicity_value, $event_date_gmt
	) {
		global $wpdb;
		$result             = array(
			'success'             => false,
			'content_size'        => false,
			'content_images_size' => false,
			'content_email_json'  => '',
			'content_email_html'  => '',
			'content_email_txt'   => ''
		);
		$content_email_html = '';
		$content_email_txt  = '';
		$data               = '';
		$current_date_gmt   = $this->core->get_current_time_gmt_sql();
		$sql                = "
		SELECT `preview`, `data`
		FROM `{$wpdb->prefix}jackmail_scenarios`
		WHERE `id` = %s";
		$campaign           = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		if ( isset( $campaign->preview ) ) {
			$content_email = $this->core->set_content_email(
				'scenario', $id_campaign, $campaign->preview,
				$content_email_json, $content_email_html, $content_email_txt
			);
			if ( $content_email !== false ) {
				if ( isset( $content_email['content_email_json'], $content_email['content_email_html'],
					$content_email['content_email_txt'], $content_email['content_email_images'] ) ) {
					$content_email_json   = $content_email['content_email_json'];
					$content_email_html   = $content_email['content_email_html'];
					$content_email_txt    = $content_email['content_email_txt'];
					$content_email_images = $content_email['content_email_images'];
					$update               = false;
					if ( $send_option === 'publish_a_post' || $send_option === 'automated_newsletter'
					     || $send_option === 'woocommerce_automated_newsletter' ) {
						$campaign = $this->explode_scenario_data_field( $campaign );
						if ( isset( $campaign->post_type, $campaign->post_categories, $campaign->nb_posts_content,
							$campaign->periodicity_type, $campaign->periodicity_value, $campaign->event_date_gmt ) ) {
							if ( $send_option === 'automated_newsletter' || $send_option === 'woocommerce_automated_newsletter' ) {
								
							} else {
								$event_date_gmt = $campaign->event_date_gmt;
							}
							if ( ( $send_option === 'publish_a_post' || $send_option === 'automated_newsletter' ) && $post_type !== 'post' ) {
								$post_categories = '[]';
							}
							$data_fields = array(
								'post_type'         => $post_type,
								'post_categories'   => $post_categories,
								'nb_posts_content'  => $nb_posts_content,
								'periodicity_type'  => $periodicity_type,
								'periodicity_value' => $periodicity_value,
								'event_date_gmt'    => $event_date_gmt
							);
							$data        = $this->core->implode_data( $data_fields );
							$update      = true;
						}
					} else {
						$update = true;
					}
					if ( $update === true ) {
						$update_return = $this->core->update_scenario( array(
							'id_lists'             => $id_lists,
							'name'                 => $name,
							'object'               => $object,
							'sender_name'          => $sender_name,
							'sender_email'         => $sender_email,
							'reply_to_name'        => $reply_to_name,
							'reply_to_email'       => $reply_to_email,
							'link_tracking'        => $link_tracking,
							'content_email_json'   => $content_email_json,
							'content_email_html'   => $content_email_html,
							'content_email_txt'    => $content_email_txt,
							'content_email_images' => $content_email_images,
							'updated_date_gmt'     => $current_date_gmt,
							'updated_by'           => get_current_user_id(),
							'data'                 => $data
						), array(
							'id' => $id_campaign
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

	protected function activate_scenario_link_tracking( $id_campaign ) {
		return $this->activate_or_deactivate_scenario_link_tracking( $id_campaign, '1' );
	}

	protected function deactivate_scenario_link_tracking( $id_campaign ) {
		return $this->activate_or_deactivate_scenario_link_tracking( $id_campaign, '0' );
	}

	private function activate_or_deactivate_scenario_link_tracking( $id_campaign, $link_tracking ) {
		$current_date_gmt = $this->core->get_current_time_gmt_sql();
		$update_return    = $this->core->update_scenario( array(
			'link_tracking'    => $link_tracking,
			'updated_date_gmt' => $current_date_gmt,
			'updated_by'       => get_current_user_id()
		), array(
			'id'     => $id_campaign,
			'status' => 'DRAFT'
		) );
		return $update_return;
	}

	protected function scenario_last_step_checker() {
		$json              = array(
			'nb_credits_before'  => '0',
			'nb_credits_checked' => false
		);
		$credits_available = $this->core->get_credits_available( true );
		if ( $credits_available !== false ) {
			$json['nb_credits_before']  = (string) $credits_available['nb_credits'];
			$json['nb_credits_checked'] = true;
		}
		return $json;
	}

	protected function send_scenario_test( $id_campaign, $test_recipient ) {
		global $wpdb;
		$result   = array(
			'success' => false,
			'message' => 'ERROR'
		);
		$sql      = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_scenarios`
		WHERE `id` = %s
		AND `data` != '[]'
		AND `send_option` IN ('automated_newsletter', 'publish_a_post', 'woocommerce_automated_newsletter')";
		$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		if ( isset( $campaign->id ) ) {
			$campaign             = $this->explode_scenario_data_field( $campaign );
			$fields               = '';
			$object               = $campaign->object;
			$sender_name          = $campaign->sender_name;
			$sender_email         = $campaign->sender_email;
			$reply_to_name        = $campaign->reply_to_name;
			$reply_to_email       = $campaign->reply_to_email;
			$content_email_json   = $campaign->content_email_json;
			$content_email_html   = $campaign->content_email_html;
			$content_email_txt    = $campaign->content_email_txt;
			$content_email_images = $campaign->content_email_images;
			$link_tracking        = $campaign->link_tracking;
			$send_option          = $campaign->send_option;
			$post_type            = $campaign->post_type;
			$post_categories      = $campaign->post_categories;
			$nb_posts_content     = (int) $campaign->nb_posts_content;
			$current_time_gmt     = $this->core->get_current_time_gmt_sql();
			if ( $send_option === 'publish_a_post' || $send_option === 'automated_newsletter' ) {
				$posts = $this->get_scenario_posts_or_custom_posts_nb( $post_type, $post_categories, $nb_posts_content, $current_time_gmt );
			} else {
				$posts = $this->get_scenario_woocommerce_products_nb( $post_categories, $nb_posts_content, $current_time_gmt );
			}
			$posts_configuration    = $this->get_scenario_posts_configuration( $content_email_json );
			$content_email_articles = $this->get_content_email_posts( $send_option, $posts, $posts_configuration );
			if ( count( $posts_configuration ) > count( $content_email_articles ) ) {
				$nb_example_articles = count( $posts_configuration ) - count( $content_email_articles );
				for ( $i = 0; $i < $nb_example_articles; $i ++ ) {
					$content_email_articles[] = array(
						'id'          => '0',
						'title'       => 'Lorem ipsum',
						'description' => 'Hacque adfabilitate confisus cum eadem postridie feceris, ut incognitus haerebis et repentinus, hortatore illo hesterno clientes numerando, qui sis vel unde venias diutius ambigente agnitus vero tandem et adscitus in amicitiam si te salutandi.',
						'link'        => get_home_url(),
						'imageUrl'    => ''
					);
				}
			}
			if ( count( $posts_configuration ) === count( $content_email_articles ) ) {
				$campaign_result = $this->generate_campaign(
					$id_campaign, 'scenario', '', '', $object,
					$sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json,
					$content_email_html, $content_email_txt, $content_email_images, $content_email_articles,
					$link_tracking, 'NOW', '0000-00-00 00:00:00', '0000-00-00 00:00:00',
					$fields, '-1', $test_recipient
				);
				$message         = $campaign_result['message'];
				if ( $message === 'OK' ) {
					$result = array(
						'success' => true,
						'message' => $message
					);
				} else {
					$result = array(
						'success' => false,
						'message' => $message
					);
				}
			}
		}
		return $result;
	}

	protected function activate_scenario( $id_campaign ) {
		$json = array(
			'success' => false,
			'message' => ''
		);
		
		$credits_available = $this->core->get_credits_available();
		if ( $credits_available !== false ) {
			if ( $credits_available === 0 ) {
				$json = array(
					'success' => false,
					'message' => __( 'You don\'t have enough credits to activate the workflow', 'jackmail-newsletters' )
				);
			} else {
				$update_return = $this->activate_deactivate_scenario( $id_campaign, 'ACTIVED' );
				if ( $update_return !== false ) {
					$json['success'] = true;
				}
			}
		} else {
			$json = array(
				'success' => false,
				'message' => __( 'Error while checking credits available', 'jackmail-newsletters' )
			);
		}
		return $json;
	}

	protected function deactivate_scenario( $id_campaign ) {
		return $this->activate_deactivate_scenario( $id_campaign, 'DRAFT' );
	}

	private function activate_deactivate_scenario( $id_campaign, $status ) {
		global $wpdb;
		$sql      = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_scenarios`
		WHERE `id` = %s";
		$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		if ( isset( $campaign->id, $campaign->send_option ) ) {
			$current_date_gmt = $this->core->get_current_time_gmt_sql();
			$campaign         = $this->explode_scenario_data_field( $campaign );
			if ( isset( $campaign->post_type, $campaign->post_categories, $campaign->nb_posts_content,
				$campaign->periodicity_type, $campaign->periodicity_value, $campaign->event_date_gmt ) ) {
				if ( $campaign->send_option === 'publish_a_post' || $campaign->send_option === 'automated_newsletter'
				     || $campaign->send_option === 'woocommerce_automated_newsletter' ) {
					$event_date_gmt = '0000-00-00 00:00:00';
					if ( $status === 'ACTIVED' ) {
						$event_date_gmt = $this->core->get_current_time_gmt_sql();
					}
					$data_fields = array(
						'post_type'         => $campaign->post_type,
						'post_categories'   => $campaign->post_categories,
						'nb_posts_content'  => $campaign->nb_posts_content,
						'periodicity_type'  => $campaign->periodicity_type,
						'periodicity_value' => $campaign->periodicity_value,
						'event_date_gmt'    => $event_date_gmt
					);
					$data        = $this->core->implode_data( $data_fields );
					return $this->core->update_scenario( array(
						'status'           => $status,
						'data'             => $data,
						'updated_date_gmt' => $current_date_gmt,
						'updated_by'       => get_current_user_id()
					), array(
						'id' => $id_campaign
					) );
				}
			} else {
				return $this->core->update_scenario( array(
					'status'           => $status,
					'updated_date_gmt' => $current_date_gmt,
					'updated_by'       => get_current_user_id()
				), array(
					'id' => $id_campaign
				) );
			}
		}
		return false;
	}

	private function get_scenario_woocommerce_products_nb( $categories, $nb_posts, $maximal_date_gmt ) {
		return $this->get_woocommerce_products_selection( $categories, $nb_posts, '', '', $maximal_date_gmt );
	}

	private function get_scenario_woocommerce_products_between( $categories, $minimal_date_gmt, $maximal_date_gmt ) {
		return $this->get_woocommerce_products_selection( $categories, '', '', $minimal_date_gmt, $maximal_date_gmt );
	}

	private function get_scenario_woocommerce_products_nb_posts_before_post_id( $categories, $nb_posts, $post_id ) {
		$data = $this->get_woocommerce_products_selection_posts_data( $categories, 1, '', '', '', $post_id );
		if ( isset( $data['posts_ids'], $data['min_date'], $data['max_date'], $data['categories_slug'] ) ) {
			$posts_ids       = $data['posts_ids'];
			$categories_slug = $data['categories_slug'];
			if ( count( $posts_ids ) === 1 ) {
				$args = array(
					'status'   => array( 'publish' ),
					'type'     => array( 'simple' ),
					'limit'    => 1,
					'category' => $categories_slug,
					'include'  => $posts_ids
				);
				if ( function_exists( 'wc_get_products' ) ) {
					$products = wc_get_products( $args );
					foreach ( $products as $product ) {
						if ( method_exists( $product, 'get_date_created' ) ) {
							$date_created_local = $product->get_date_created();
							if ( strlen( $date_created_local ) >= 19 ) {
								$maximal_date_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $date_created_local ) );
								return $this->get_woocommerce_products_selection( $categories, $nb_posts, '', '', $maximal_date_gmt );
							}
						}
					}
				}
			}
		}
		return array();
	}

	private function get_scenario_posts_or_custom_posts_nb( $post_type, $categories, $nb_posts, $maximal_date_gmt ) {
		return $this->get_scenario_posts_or_custom_posts( $post_type, $categories, $nb_posts, '', '', $maximal_date_gmt, true );
	}

	private function get_scenario_posts_or_custom_posts_between( $post_type, $categories, $minimal_date_gmt, $maximal_date_gmt, $automated_newsletter ) {
		return $this->get_scenario_posts_or_custom_posts( $post_type, $categories, '', '', $minimal_date_gmt, $maximal_date_gmt, $automated_newsletter );
	}

	private function get_scenario_posts_or_custom_posts_nb_before_post_id( $post_type, $categories, $nb_posts, $post_id, $automated_newsletter ) {
		$post = get_post( $post_id );
		if ( isset( $post->ID, $post->post_date_gmt ) ) {
			$maximal_date_gmt = $post->post_date_gmt;
			return $this->get_scenario_posts_or_custom_posts( $post_type, $categories, $nb_posts, '', '', $maximal_date_gmt, $automated_newsletter );
		}
		return array();
	}

	private function get_scenario_posts_or_custom_posts( $post_type, $categories, $nb_posts, $title, $minimal_date_gmt, $maximal_date_gmt, $automated_newsletter ) {
		$categories_array = $this->core->explode_data( $categories );
		$args             = array(
			'post_type'     => $post_type,
			'post_status'   => 'publish',
			'post_password' => '',
			'numberposts'   => 10000
		);
		if ( count( $categories_array ) > 0 ) {
			$args['category'] = implode( ',', $categories_array );
		}
		if ( $title !== '' ) {
			$args['s'] = $title;
		}
		if ( $automated_newsletter ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'jackmail_scenario_exclude',
					'compare' => 'NOT EXISTS'
				)
			);
		}
		if ( $nb_posts !== '' ) {
			$args['numberposts'] = $nb_posts;
		}
		$min_date = false;
		if ( $minimal_date_gmt !== '' && $this->core->str_len( $minimal_date_gmt ) === 19 ) {
			$min_date = true;
		}
		$max_date = true;
		if ( $maximal_date_gmt !== '' && $this->core->str_len( $maximal_date_gmt ) === 19 ) {
			$max_date = true;
		}
		if ( $min_date || $max_date ) {
			$args['date_query'] = array(
				'column'    => 'post_date_gmt',
				'inclusive' => true
			);
			if ( $min_date ) {
				$args['date_query']['after'] = $minimal_date_gmt;
			}
			if ( $max_date ) {
				if ( $min_date ) {
					$maximal_date_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $maximal_date_gmt ) - 1 );
				}
				$args['date_query']['before'] = $maximal_date_gmt;
			}
		}
		$posts     = get_posts( $args );
		$posts_ids = array();
		foreach ( $posts as $post ) {
			if ( isset( $post->ID, $post->post_title, $post->post_excerpt, $post->post_content ) ) {
				$posts_ids[] = array(
					'id'           => $post->ID,
					'post_title'   => $post->post_title,
					'post_excerpt' => $post->post_excerpt,
					'post_content' => $post->post_content
				);
			}
		}
		return $posts_ids;
	}

	protected function create_update_scenario_check_isset_posts() {
		if ( isset( $_POST['id_lists'], $_POST['send_option'], $_POST['name'], $_POST['object'], $_POST['sender_name'],
			$_POST['sender_email'], $_POST['reply_to_name'], $_POST['reply_to_email'], $_POST['link_tracking'],
			$_POST['content_email_json'], $_POST['content_email_html'], $_POST['content_email_txt'] ) ) {
			if ( ( $_POST['send_option'] === 'publish_a_post' || $_POST['send_option'] === 'automated_newsletter'
			       || $_POST['send_option'] === 'woocommerce_automated_newsletter' )
			     && isset( $_POST['post_type'], $_POST['post_categories'], $_POST['nb_posts_content'],
				     $_POST['periodicity_type'], $_POST['periodicity_value'], $_POST['event_date_gmt'] ) ) {
				return true;
			}
		}
		return false;
	}

	protected function explode_scenario_data_field( $campaign ) {
		if ( isset( $campaign->data ) ) {
			$data_fields = $this->core->explode_data( $campaign->data );
			foreach ( $data_fields as $key => $field ) {
				$campaign->$key = $field;
			}
			unset ( $campaign->data );
		}
		return $campaign;
	}

	private function scenario_send_not_enough_credits( $message ) {
		if ( $message === 'NOT_ENOUGH_CREDITS' ) {
			$last_not_enough_credits_send = get_option( 'jackmail_last_not_enough_credits_send', '0' );
			$current_date_gmt_sql         = $this->core->get_current_time_gmt_sql();
			$last_send_date_gmt           = 0;
			if ( $last_not_enough_credits_send !== false ) {
				$last_send_date_gmt = strtotime( $last_not_enough_credits_send );
			}
			$current_date_gmt = strtotime( $current_date_gmt_sql );
			if ( $current_date_gmt - $last_send_date_gmt > 86400 ) {
				$url     = $this->core->get_jackmail_url_ws() . 'send-email.php';
				$headers = array(
					'content-type' => 'application/json'
				);
				$body    = array(
					'accountId' => $this->core->get_account_id()
				);
				$timeout = 30;
				$this->core->remote_post( $url, $headers, $body, $timeout );
				update_option( 'jackmail_last_not_enough_credits_send', $current_date_gmt_sql, false );
			}
		}
	}

	private function get_scenario_posts_configuration( $content_email_json ) {
		$json                = json_decode( $content_email_json, true );
		$posts_configuration = array();
		if ( isset( $json['workspace']['structures'] ) && is_array( $json['workspace']['structures'] ) ) {
			foreach ( $json['workspace']['structures'] as $structure ) {
				if ( isset( $structure['columns'] ) && is_array( $structure['columns'] ) ) {
					foreach ( $structure['columns'] as $columns ) {
						if ( isset( $columns['contents'] ) && is_array( $columns['contents'] ) ) {
							foreach ( $columns['contents'] as $contents ) {
								if ( isset( $contents['type'], $contents['articleNumber'] ) ) {
									if ( $contents['type'] === 'super' ) {
										if ( isset( $contents['settings'] ) ) {
											if ( isset( $contents['settings']['content'] ) ) {
												if ( $contents['settings']['content'] === 'SAMPLE' ) {
													$posts_configuration[] = 'description';
												} else {
													$posts_configuration[] = 'full_description';
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
		return $posts_configuration;
	}

	private function get_content_email_posts( $send_option, $posts, $posts_configuration ) {
		$content_email_articles = array();
		foreach ( $posts as $key => $post ) {
			$full_description = false;
			if ( $posts_configuration[ $key ] === 'full_description' ) {
				$full_description = true;
			}
			if ( $send_option === 'woocommerce_automated_newsletter' ) {
				if ( isset( $post['id'], $post['post_title'], $post['post_content'], $post['post_price'] ) ) {
					$content_email_articles[] = $this->get_woocommerce_product_data(
						$post['id'], $post['post_title'],
						$post['post_content'], $post['post_price'], $full_description
					);
				}
			} else {
				if ( isset( $post['id'], $post['post_type'], $post['post_title'],
					$post['post_excerpt'], $post['post_content'], $post['guid'] ) ) {
					$content_email_articles[] = $this->get_post_or_page_or_custom_post_data(
						$post['id'], $post['post_type'], $post['post_title'], $post['post_excerpt'],
						$post['post_content'], $full_description, $post['guid']
					);
				}
			}
		}
		return $content_email_articles;
	}

	private function scenario_send( $id_campaign, Array $posts, $data_to_save_event = '' ) {
		global $wpdb;
		$sql      = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_scenarios`
		WHERE `id` = %s
		AND `status` = 'ACTIVED'
		AND `id_lists` != '[]'";
		$campaign = $wpdb->get_row( $wpdb->prepare( $sql, $id_campaign ) );
		if ( isset( $campaign->object ) ) {
			$fields               = '';
			$object               = $campaign->object;
			$sender_name          = $campaign->sender_name;
			$sender_email         = $campaign->sender_email;
			$reply_to_name        = $campaign->reply_to_name;
			$reply_to_email       = $campaign->reply_to_email;
			$content_email_json   = $campaign->content_email_json;
			$content_email_html   = $campaign->content_email_html;
			$content_email_txt    = $campaign->content_email_txt;
			$content_email_images = $campaign->content_email_images;
			$link_tracking        = $campaign->link_tracking;
			$send_option          = $campaign->send_option;
			$sql_fields_lists     = array();
			$id_lists             = $this->core->explode_data( $campaign->id_lists );
			foreach ( $id_lists as $id_list ) {
				$sql_fields_lists[] = '%s';
			}
			$sql_fields_lists = implode( ', ', $sql_fields_lists );
			$sql              = "
			SELECT COUNT( * ) AS `nb_lists`
			FROM `{$wpdb->prefix}jackmail_lists`
			WHERE `id` IN ({$sql_fields_lists})";
			$nb_lists         = $wpdb->get_var( $wpdb->prepare( $sql, $id_lists ) );
			if ( $nb_lists > 0 ) {
				$posts_configuration = $this->get_scenario_posts_configuration( $content_email_json );
				if ( count( $posts_configuration ) === count( $posts ) ) {
					
					$content_email_articles = $this->get_content_email_posts( $send_option, $posts, $posts_configuration );
					if ( count( $posts_configuration ) === count( $content_email_articles ) ) {
						$this->core->progress_contacts_blacklist();
						$campaign_result = $this->generate_campaign(
							$id_campaign, 'scenario', '', $data_to_save_event, $object,
							$sender_name, $sender_email, $reply_to_name, $reply_to_email, $content_email_json,
							$content_email_html, $content_email_txt, $content_email_images, $content_email_articles,
							$link_tracking, 'NOW', '0000-00-00 00:00:00', '0000-00-00 00:00:00',
							$fields, '-1'
						);
						$message         = $campaign_result['message'];
						$campaign_id     = $campaign_result['campaign_id'];
						$send_id         = $campaign_result['send_id'];
						$this->scenario_send_not_enough_credits( $message );
						if ( $message !== 'OK' ) {
							$this->core->update_scenario_event( array(
								'status'            => 'ERROR',
								'status_error_code' => $message
							), array(
								'id'          => $id_campaign,
								'campaign_id' => $campaign_id,
								'send_id'     => $send_id
							) );
						} else {
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	private function scenario_update_event_date_gmt( $id_campaign, Array $data_field, $event_date_gmt ) {
		$data_field['event_date_gmt'] = $event_date_gmt;
		return $this->core->update_scenario( array(
			'data' => $this->core->implode_data( $data_field )
		), array(
			'id' => $id_campaign
		) );
	}

	protected function cron_scenario() {
		ini_set( 'max_execution_time', 600 );
		
		$this->core->update_jackmail_database_scenario_data();
		
		$this->core->get_jackmail_update_available();
		global $wpdb;
		$sql                     = "
		SELECT `id`, `send_option`, `data`
		FROM `{$wpdb->prefix}jackmail_scenarios`
		WHERE `status` = 'ACTIVED'
		AND `id_lists` != '[]'
		AND `data` != '[]'
		AND `send_option` IN ('automated_newsletter', 'publish_a_post', 'woocommerce_automated_newsletter')";
		$campaigns               = $wpdb->get_results( $sql );
		$custom_posts_categories = $this->core->get_custom_posts_categories_array();
		foreach ( $campaigns as $campaign ) {
			$campaign = $this->explode_scenario_data_field( $campaign );
			if ( isset( $campaign->id, $campaign->post_type, $campaign->post_categories, $campaign->nb_posts_content,
				$campaign->periodicity_type, $campaign->periodicity_value, $campaign->event_date_gmt ) ) {
				if ( in_array( $campaign->post_type, $custom_posts_categories ) ) {
					$data_field        = array(
						'post_type'         => $campaign->post_type,
						'post_categories'   => $campaign->post_categories,
						'nb_posts_content'  => $campaign->nb_posts_content,
						'periodicity_type'  => $campaign->periodicity_type,
						'periodicity_value' => $campaign->periodicity_value
					);
					$id_campaign       = $campaign->id;
					$send_option       = $campaign->send_option;
					$post_type         = $campaign->post_type;
					$post_categories   = $campaign->post_categories;
					$nb_posts_content  = (int) $campaign->nb_posts_content;
					$periodicity_type  = $campaign->periodicity_type;
					$periodicity_value = (int) $campaign->periodicity_value;
					$event_date_gmt    = $campaign->event_date_gmt;
					if ( $event_date_gmt !== '0000-00-00 00:00:00' && $this->core->str_len( $event_date_gmt ) === 19 ) {
						$event_timestamp   = strtotime( $event_date_gmt );
						$current_time_gmt  = $this->core->get_current_time_gmt_sql();
						$current_timestamp = strtotime( $current_time_gmt );
						if ( $periodicity_value > 0 ) {
							if ( $send_option === 'automated_newsletter' || $send_option === 'woocommerce_automated_newsletter' ) {
								if ( $current_timestamp - $event_timestamp >= 0 ) {
									if ( $periodicity_type === 'DAYS' || $periodicity_type === 'MONTHS' ) {
										if ( $periodicity_type === 'DAYS' ) {
											$diff = floor( ( $current_timestamp - $event_timestamp ) / 86400 ) + 1;
										} else {
											$diff = floor( ( date( 'Y', $current_timestamp ) - date( 'Y', $event_timestamp ) ) * 12 + ( date( 'm', $current_timestamp ) - date( 'm', $event_timestamp ) ) ) + 1;
										}
										if ( $diff > 0 ) {
											$periodicity        = '+ ' . ( $periodicity_value * ceil( $diff / $periodicity_value ) ) . ' ' . $periodicity_type;
											$new_event_date_gmt = strtotime( $periodicity, strtotime( $event_date_gmt ) );
											$update             = $this->scenario_update_event_date_gmt( $id_campaign, $data_field, date( 'Y-m-d H:i:s', $new_event_date_gmt ) );
											if ( $update !== false ) {
												if ( $send_option === 'automated_newsletter' ) {
													$posts = $this->get_scenario_posts_or_custom_posts_nb( $post_type, $post_categories, $nb_posts_content, $current_time_gmt );
												} else {
													$posts = $this->get_scenario_woocommerce_products_nb( $post_categories, $nb_posts_content, $current_time_gmt );
												}
												if ( count( $posts ) === $nb_posts_content ) {
													$data_to_save_event = array(
														'posts' => array()
													);
													foreach ( $posts as $post ) {
														$data_to_save_event['posts'][] = $post['id'];
													}
													$data_to_save_event = $this->core->implode_data( $data_to_save_event );
													if ( $this->check_nb_campaign_events( $id_campaign, $data_to_save_event ) === '0' ) {
														$campaign_result = $this->scenario_send( $id_campaign, $posts, $data_to_save_event );
														if ( ! $campaign_result ) {
															$this->scenario_update_event_date_gmt( $id_campaign, $data_field, $event_date_gmt );
														}
													}
												}
											}
										}
									} else if ( $periodicity_type === 'POSTS' ) {
										if ( $send_option === 'automated_newsletter' ) {
											$posts = $this->get_scenario_posts_or_custom_posts_between( $post_type, $post_categories, $event_date_gmt, $current_time_gmt, true );
										} else {
											$posts = $this->get_scenario_woocommerce_products_between( $post_categories, $event_date_gmt, $current_time_gmt );
										}
										$posts = array_reverse( $posts );
										if ( count( $posts ) > 0 ) {
											if ( count( $posts ) >= $periodicity_value ) {
												foreach ( $posts as $key => $post ) {
													if ( $key % $periodicity_value === 0 ) {
														if ( isset( $posts[ $key + $periodicity_value - 1 ] ) ) {
															$post_id = $posts[ $key + $periodicity_value - 1 ]['id'];
															if ( $send_option === 'automated_newsletter' ) {
																$post_posts = $this->get_scenario_posts_or_custom_posts_nb_before_post_id( $post_type, $post_categories, $nb_posts_content, $post_id, true );
															} else {
																$post_posts = $this->get_scenario_woocommerce_products_nb_posts_before_post_id( $post_categories, $nb_posts_content, $post_id );
															}
															if ( count( $post_posts ) === $nb_posts_content ) {
																$data_to_save_event = array(
																	'posts' => array()
																);
																$sql_header         = array();
																$sql_values         = array();
																foreach ( $post_posts as $post_post ) {
																	$data_to_save_event['posts'][] = $post_post['id'];
																	$sql_header[]                  = '%s';
																	$sql_values[]                  = $post_post['id'];
																}
																$data_to_save_event = $this->core->implode_data( $data_to_save_event );
																if ( $this->check_nb_campaign_events( $id_campaign, $data_to_save_event ) === '0' ) {
																	$campaign_result = $this->scenario_send( $id_campaign, $post_posts, $data_to_save_event );
																	if ( ! $campaign_result ) {
																		break;
																	} else {
																		$sql_header      = implode( ',', $sql_header );
																		$sql             = "
																	SELECT max(`post_date_gmt`) AS `update_date_gmt`
																	FROM `{$wpdb->prefix}posts`
																	WHERE `ID` IN (" . $sql_header . ")";
																		$update_date_gmt = $wpdb->get_var( $wpdb->prepare( $sql, $sql_values ) );
																		$update_date_gmt = gmdate( 'Y-m-d H:i:s', strtotime( $update_date_gmt ) + 1 );
																	}
																}
															}
														}
													}
												}
											}
											if ( isset( $update_date_gmt ) ) {
												$this->scenario_update_event_date_gmt( $id_campaign, $data_field, $update_date_gmt );
											}
										}
									}
								}
							} else if ( $send_option === 'publish_a_post' ) {
								$seconds = 0;
								if ( $periodicity_type === 'HOURS' ) {
									$seconds = $periodicity_value * 3600;
								} else if ( $periodicity_type === 'DAYS' ) {
									$seconds = $periodicity_value * 86400;
								}
								$event_date_gmt_begin = gmdate( 'Y-m-d H:i:s', strtotime( $event_date_gmt ) - $seconds );
								$event_date_gmt_end   = gmdate( 'Y-m-d H:i:s', strtotime( $current_time_gmt ) - $seconds );
								$update               = $this->scenario_update_event_date_gmt( $id_campaign, $data_field, $current_time_gmt );
								if ( $update !== false ) {
									$posts = $this->get_scenario_posts_or_custom_posts_between( $post_type, $post_categories, $event_date_gmt_begin, $event_date_gmt_end, false );
									if ( count( $posts ) > 0 ) {
										foreach ( $posts as $post ) {
											$post_posts         = $this->get_scenario_posts_or_custom_posts_nb_before_post_id( $post_type, $post_categories, $nb_posts_content, $post['id'], false );
											$data_to_save_event = array(
												'posts' => array()
											);
											foreach ( $post_posts as $post_post ) {
												$data_to_save_event['posts'][] = $post_post['id'];
											}
											$data_to_save_event = $this->core->implode_data( $data_to_save_event );
											if ( $this->check_nb_campaign_events( $id_campaign, $data_to_save_event ) === '0' ) {
												$campaign_result = $this->scenario_send( $id_campaign, $post_posts, $data_to_save_event );
												if ( ! $campaign_result ) {
													$this->scenario_update_event_date_gmt( $id_campaign, $data_field, $event_date_gmt );
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

	private function check_nb_campaign_events( $id_campaign, $data_to_save_event ) {
		global $wpdb;
		$sql = "
		SELECT COUNT( * ) AS `nb_campaigns`
		FROM `{$wpdb->prefix}jackmail_scenarios_events`
		WHERE `id` = %s
		AND `data` = %s
		AND `status` != 'ERROR'";
		return $wpdb->get_var( $wpdb->prepare( $sql, $id_campaign, $data_to_save_event ) );
	}

}