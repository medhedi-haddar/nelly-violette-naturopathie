<?php


class Jackmail_Template_Core {

	protected function get_template( $id_template ) {
		global $wpdb;
		if ( $id_template === '0' ) {
			$template = array(
				'id'                 => '0',
				'name'               => __( 'Template with no name', 'jackmail-newsletters' ),
				'content_email_json' => '',
				'content_email_html' => '',
				'content_email_txt'  => ''
			);
		} else {
			$sql      = "SELECT * FROM `{$wpdb->prefix}jackmail_templates` WHERE `id` = %s";
			$template = $wpdb->get_row( $wpdb->prepare( $sql, $id_template ) );
		}
		return $template;
	}

	protected function get_template_json( $id_template, $link_tracking ) {
		global $wpdb;
		$json     = array(
			'content_email_json' => '',
		);
		$sql      = "
		SELECT `content_email_json`
		FROM `{$wpdb->prefix}jackmail_templates`
		WHERE `id` = %s";
		$template = $wpdb->get_row( $wpdb->prepare( $sql, $id_template ) );
		if ( isset( $template->content_email_json ) ) {
			$json['content_email_json'] = $this->core->content_email_json_link_tracking( $template->content_email_json, $link_tracking );
		}
		return $json;
	}

	protected function get_gallery_template_json( $gallery_id, $link_tracking ) {
		$json                       = array(
			'content_email_json' => ''
		);
		$json['content_email_json'] = $this->core->get_gallery_template_json( $gallery_id, $link_tracking );
		return $json;
	}

	protected function create_template( $name, $content_email_json ) {
		$result             = array(
			'success'            => false,
			'id'                 => '0',
			'content_email_json' => ''
		);
		$content_email_html = '';
		$content_email_txt  = '';
		$preview            = $this->core->generate_jackmail_preview_filename();
		$content_email      = $this->core->set_content_email(
			'template', '0', $preview, $content_email_json, $content_email_html, $content_email_txt
		);
		if ( $content_email !== false ) {
			if ( isset( $content_email['content_email_json'], $content_email['content_email_images'] ) ) {
				$content_email_json   = $content_email['content_email_json'];
				$content_email_images = $content_email['content_email_images'];
				$current_date_gmt     = $this->core->get_current_time_gmt_sql();
				$id_template          = $this->core->insert_template( array(
					'name'                 => $name,
					'content_email_json'   => $content_email_json,
					'content_email_images' => $content_email_images,
					'preview'              => $preview,
					'created_date_gmt'     => $current_date_gmt,
					'updated_date_gmt'     => $current_date_gmt
				) );
				if ( $id_template !== false ) {
					$result = array(
						'success'            => true,
						'id'                 => $id_template,
						'content_email_json' => $content_email_json
					);
				}
			}
		}
		return $result;
	}

	protected function update_template( $id_template, $name, $content_email_json ) {
		global $wpdb;
		$result             = array(
			'success'            => false,
			'content_email_json' => ''
		);
		$content_email_html = '';
		$content_email_txt  = '';
		$current_date_gmt   = $this->core->get_current_time_gmt_sql();
		$sql                = "
		SELECT `preview`
		FROM `{$wpdb->prefix}jackmail_templates`
		WHERE `id` = %s";
		$template           = $wpdb->get_row( $wpdb->prepare( $sql, $id_template ) );
		if ( isset( $template->preview ) ) {
			$content_email = $this->core->set_content_email(
				'template', $id_template, $template->preview, $content_email_json, $content_email_html, $content_email_txt
			);
			if ( $content_email !== false ) {
				if ( isset( $content_email['content_email_json'], $content_email['content_email_images'] ) ) {
					$content_email_json   = $content_email['content_email_json'];
					$content_email_images = $content_email['content_email_images'];
					$update_return        = $this->core->update_template( array(
						'name'                 => $name,
						'content_email_json'   => $content_email_json,
						'content_email_images' => $content_email_images,
						'updated_date_gmt'     => $current_date_gmt
					), array(
						'id' => $id_template
					) );
					if ( $update_return !== false ) {
						$result = array(
							'success'            => true,
							'content_email_json' => $content_email_json
						);
					}
				}
			}
		}
		return $result;
	}

	protected function create_campaign_with_template( $id_template ) {
		$link_tracking = get_option( 'jackmail_link_tracking' );
		$template      = $this->get_template_json( $id_template, $link_tracking );
		if ( isset( $template['content_email_json'] ) ) {
			$campaign = $this->core->get_new_campaign_data( $template['content_email_json'] );
			if ( isset( $campaign['id'] ) ) {
				$preview       = $this->core->generate_jackmail_preview_filename();
				$content_email = $this->core->set_content_email(
					'campaign', '0', $preview, $template['content_email_json'], '', ''
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
								return $id_campaign;
							}
						}
					}
				}
			}
		}
		return false;
	}

}