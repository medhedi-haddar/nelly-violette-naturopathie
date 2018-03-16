<?php


class Jackmail_List_Core extends Jackmail_List_And_Campaign_Common_Core {

	protected function get_or_export_list( $part ) {
		$data = $this->get_list_or_get_campaign_contacts_post_data();
		if ( isset( $data ['id'], $data ['begin'], $data ['sort_by'], $data ['sort_order'], $data ['search'], $data ['targeting_rules'] ) ) {
			if ( $data ['id'] === '0' ) {
				return array(
					'list'               => array(
						'name'   => __( 'List', 'jackmail-newsletters' ) . ' ' . $this->core->get_current_time_sql(),
						'id'     => '',
						'fields' => '[]'
					),
					'contacts'           => array(),
					'nb_contacts'        => '0',
					'nb_contacts_search' => '0'
				);
			} else {
				return $this->get_list_data(
					$data ['id'], $data ['begin'], $part, $data ['sort_by'],
					$data ['sort_order'], $data ['search'], $data ['targeting_rules']
				);
			}
		}
		return array();
	}

	protected function create_list( $name ) {
		$json = array(
			'success' => false,
			'id'      => '0'
		);
		if ( $this->check_list_name_unique( $name ) ) {
			$id = $this->core->create_list( $name );
			if ( $id !== '0' ) {
				$json = array(
					'success' => true,
					'id'      => $id
				);
			}
		}
		return $json;
	}

	protected function save_name( $id_list, $name ) {
		if ( $this->check_list_name_unique( $name ) ) {
			$data = array(
				'name' => $name
			);
			$this->core->updated_list_contact( $id_list, $data );
			return true;
		}
		return false;
	}

	private function check_list_name_unique( $name ) {
		global $wpdb;
		$sql = "SELECT COUNT(*) AS `nb` FROM `{$wpdb->prefix}jackmail_lists` WHERE `name` = %s";
		$nb  = $wpdb->get_row( $wpdb->prepare( $sql, $name ) );
		if ( isset( $nb->nb ) ) {
			if ( $nb->nb === '0' ) {
				return true;
			}
		}
		return false;
	}

}