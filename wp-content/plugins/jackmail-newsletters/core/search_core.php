<?php


class Jackmail_Search_Core {

	protected function search_faq( $search ) {
		$json     = array();
		$lang     = $this->core->get_current_language();
		$url      = $this->core->get_jackmail_url_ws() . 'search.php?search=' . urlencode( $search ) . '&type=faq&lang=' . $lang;
		$headers  = array();
		$timeout  = 30;
		$response = $this->core->remote_get( $url, $headers, $timeout );
		if ( is_array( $response ) ) {
			if ( isset( $response['body'] ) ) {
				$json = json_decode( $response['body'], true );
				if ( $json === null ) {
					$json = array();
				}
			}
		}
		return $json;
	}

	protected function search_campaigns( $search ) {
		global $wpdb;
		$sql     = "
		SELECT `id`, `name`, `preview`, `updated_date_gmt`, `status`
		FROM `{$wpdb->prefix}jackmail_campaigns` WHERE `name` LIKE %s
		ORDER BY `updated_date_gmt` DESC";
		$results = $wpdb->get_results( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( $search ) . '%' ) );
		foreach ( $results as $key => $result ) {
			$results[ $key ]->preview = $this->core->content_email_preview_url( $result->preview, 'campaign' );
		}
		return $results;
	}

	protected function search_all( $search ) {
		global $wpdb;
		$sql        = "
		SELECT `id`, `name`, 'campaign' AS `type`, `updated_date_gmt`
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `name` LIKE %s
		ORDER BY `updated_date_gmt` DESC";
		$campaigns  = $wpdb->get_results( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( $search ) . '%' ) );
		$sql        = "
		SELECT `id`, `name`, 'scenario' AS `type`, `updated_date_gmt`
		FROM `{$wpdb->prefix}jackmail_scenarios`
		WHERE `name` LIKE %s
		ORDER BY `updated_date_gmt` DESC";
		$scenarios  = $wpdb->get_results( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( $search ) . '%' ) );
		$sql        = "
		SELECT `id`, `name`, 'statistics' AS `type`, `updated_date_gmt`
		FROM `{$wpdb->prefix}jackmail_campaigns`
		WHERE `name` LIKE %s AND `status` != 'DRAFT'
		ORDER BY `updated_date_gmt` DESC";
		$statistics = $wpdb->get_results( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( $search ) . '%' ) );
		$sql        = "
		SELECT `id`, `name`, 'list' AS `type`, `updated_date_gmt`
		FROM `{$wpdb->prefix}jackmail_lists`
		WHERE `name` LIKE %s
		ORDER BY `updated_date_gmt` DESC";
		$lists      = $wpdb->get_results( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( $search ) . '%' ) );
		$sql        = "
		SELECT `id`, `name`, 'template' AS `type`, `updated_date_gmt`
		FROM `{$wpdb->prefix}jackmail_templates`
		WHERE `name` LIKE %s
		ORDER BY `updated_date_gmt` DESC";
		$templates  = $wpdb->get_results( $wpdb->prepare( $sql, '%' . $wpdb->esc_like( $search ) . '%' ) );
		$results    = array_merge( $campaigns, $scenarios, $statistics, $lists, $templates );
		usort( $results, function ( $a, $b ) {
			return strcmp( $b->updated_date_gmt, $a->updated_date_gmt );
		} );
		return $results;
	}

	protected function suggestion_faq() {
		$json     = array();
		$lang     = $this->core->get_current_language();
		$url      = $this->core->get_jackmail_url_ws() . 'suggestion.php?type=faq&lang=' . $lang;
		$headers  = array();
		$timeout  = 30;
		$response = $this->core->remote_get( $url, $headers, $timeout );
		if ( is_array( $response ) ) {
			if ( isset( $response['body'] ) ) {
				$json = json_decode( $response['body'], true );
				if ( $json === null ) {
					$json = array();
				}
			}
		}
		return $json;
	}

	protected function suggestion_forum( $search ) {
		$json     = array();
		$lang     = $this->core->get_current_language();
		$url      = $this->core->get_jackmail_url_ws() . 'search.php?search=' . urlencode( $search ) . '&type=forum&lang=' . $lang;
		$headers  = array();
		$timeout  = 30;
		$response = $this->core->remote_get( $url, $headers, $timeout );
		if ( is_array( $response ) ) {
			if ( isset( $response['body'] ) ) {
				$json = json_decode( $response['body'], true );
				if ( $json === null ) {
					$json = array();
				}
			}
		}
		return $json;
	}

}