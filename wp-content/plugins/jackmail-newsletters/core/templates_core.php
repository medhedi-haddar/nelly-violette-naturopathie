<?php


class Jackmail_Templates_Core {

	protected function get_templates() {
		global $wpdb;
		$sql       = "
		SELECT `id`, `name`, `preview`, `updated_date_gmt`
		FROM `{$wpdb->prefix}jackmail_templates`
		ORDER BY `updated_date_gmt` DESC";
		$templates = $wpdb->get_results( $sql );
		foreach ( $templates as $key => $template ) {
			$templates[ $key ]->preview = $this->core->content_email_preview_url( $template->preview, 'template' );
		}
		return $templates;
	}

	protected function get_templates_gallery() {
		$json     = array();
		$lang     = $this->core->get_current_language();
		$url      = $this->core->get_jackmail_url_ws() . 'gallery.php?product=jackmail&lang=' . $lang;
		$headers  = array();
		$timeout  = 30;
		$response = $this->core->remote_get( $url, $headers, $timeout );
		if ( is_array( $response ) ) {
			if ( isset( $response['body'] ) ) {
				$json = json_decode( $response['body'], true );
				if ( $json === null ) {
					$json = array();
				}
				foreach ( $json as $key => $data ) {
					$json[ $key ]['preview'] = $this->core->get_jackmail_url_img() . $data['preview'];
				}
			}
		}
		return $json;
	}

	protected function delete_template( $id_template ) {
		$delete_return = $this->core->delete_template( array(
			'id' => $id_template
		) );
		if ( $delete_return !== false ) {
			return true;
		}
		return false;
	}

	protected function duplicate_template( $id_template ) {
		global $wpdb;
		$sql      = "
		SELECT *
		FROM `{$wpdb->prefix}jackmail_templates`
		WHERE `id` = %s";
		$template = $wpdb->get_row( $wpdb->prepare( $sql, $id_template ) );
		if ( isset( $template->id ) ) {
			$current_date_gmt = $this->core->get_current_time_gmt_sql();
			$preview          = $this->core->generate_jackmail_preview_filename();
			if ( $this->core->duplicate_preview( $template->preview, $preview ) ) {
				$id_template = $this->core->insert_template( array(
					'name'                 => $template->name,
					'content_email_json'   => $template->content_email_json,
					'content_email_html'   => $template->content_email_html,
					'content_email_txt'    => $template->content_email_txt,
					'content_email_images' => $template->content_email_images,
					'preview'              => $preview,
					'created_date_gmt'     => $current_date_gmt,
					'updated_date_gmt'     => $current_date_gmt
				) );
				if ( $id_template !== false ) {
					return true;
				}
			}
		}
		return false;
	}

}