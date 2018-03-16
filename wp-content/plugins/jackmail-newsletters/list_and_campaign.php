<?php


class Jackmail_List_And_Campaign extends Jackmail_List_And_Campaign_Core {

	public function __construct( Jackmail_Core $core ) {

		$this->core = $core;

		if ( $this->core->is_admin() ) {

			add_action( 'wp_ajax_jackmail_export_contacts_selection', array( $this, 'export_contacts_selection_callback' ) );

			add_action( 'wp_ajax_jackmail_import_contacts', array( $this, 'import_contacts_callback' ) );

			add_action( 'wp_ajax_jackmail_update_contact_email', array( $this, 'update_contact_email_callback' ) );

			add_action( 'wp_ajax_jackmail_update_contact_field', array( $this, 'update_contact_field_callback' ) );

			add_action( 'wp_ajax_jackmail_add_header_column', array( $this, 'add_header_column_callback' ) );

			add_action( 'wp_ajax_jackmail_edit_header_column', array( $this, 'edit_header_column_callback' ) );

			add_action( 'wp_ajax_jackmail_delete_header_column', array( $this, 'delete_header_column_callback' ) );

			add_action( 'wp_ajax_jackmail_delete_contacts_selection', array( $this, 'delete_contacts_selection_callback' ) );

			add_action( 'wp_ajax_jackmail_delete_all_contacts', array( $this, 'delete_all_contacts_callback' ) );

		}

	}

	public function export_contacts_selection_callback() {
		$this->core->check_auth();
		if ( ( isset( $_POST['id_campaign'] ) || isset( $_POST['id_list'] ) ) && isset( $_POST['begin'],
				$_POST['search'], $_POST['targeting_rules'], $_POST['contacts_selection'] ) ) {
			$begin              = $this->core->request_text_data( $_POST['begin'] );
			$search             = $this->core->request_text_data( $_POST['search'] );
			$targeting_rules    = $this->core->request_text_data( $_POST['targeting_rules'] );
			$contacts_selection = $this->core->request_text_data( $_POST['contacts_selection'] );
			if ( isset( $_POST['id_list'] ) ) {
				$id   = $this->core->request_text_data( $_POST['id_list'] );
				$data = $this->list_export_contacts_selection( $id, $begin, $search, $targeting_rules, $contacts_selection );
			} else {
				$id   = $this->core->request_text_data( $_POST['id_campaign'] );
				$data = $this->campaign_export_contacts_selection( $id, $begin, $search, $targeting_rules, $contacts_selection );
			}
			wp_send_json( $data );
		}
		die;
	}

	public function import_contacts_callback() {
		$this->core->check_auth();
		if ( ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) )
		     && isset( $_POST['field_separator'], $_POST['email_position'], $_POST['contacts'] ) ) {
			$field_separator = $this->core->request_text_data( $_POST['field_separator'] );
			if ( $field_separator === 'TAB' ) {
				$field_separator = '    ';
			}
			$email_position = $this->core->request_text_data( $_POST['email_position'] );
			$contacts       = $this->core->request_textarea_data( $_POST['contacts'] );
			if ( isset( $_POST['id_list'] ) ) {
				$id = $this->core->request_text_data( $_POST['id_list'] );
				$this->list_import_contacts( $id, $field_separator, $email_position, $contacts );
			} else {
				$id = $this->core->request_text_data( $_POST['id_campaign'] );
				$this->campaign_import_contacts( $id, $field_separator, $email_position, $contacts );
			}
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

	public function update_contact_email_callback() {
		$this->core->check_auth();
		$json = array(
			'success' => false,
			'message' => ''
		);
		if ( ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) ) && isset( $_POST['email'], $_POST['new_email'] ) ) {
			$email     = $this->core->str_to_lower( $this->core->request_email_data( $_POST['email'] ) );
			$new_email = $this->core->str_to_lower( $this->core->request_text_data( $_POST['new_email'] ) );
			if ( isset( $_POST['id_list'] ) ) {
				$id   = $this->core->request_text_data( $_POST['id_list'] );
				$json = $this->list_update_contact_email( $id, $email, $new_email );
			} else {
				$id   = $this->core->request_text_data( $_POST['id_campaign'] );
				$json = $this->campaign_update_contact_email( $id, $email, $new_email );
			}
		}
		wp_send_json( $json );
		die;
	}

	public function update_contact_field_callback() {
		$this->core->check_auth();
		$json = array(
			'success' => false,
			'message' => ''
		);
		if ( ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) ) && isset( $_POST['email'], $_POST['field_id'], $_POST['field'] ) ) {
			$email    = $this->core->str_to_lower( $this->core->request_email_data( $_POST['email'] ) );
			$field_id = $this->core->request_text_data( $_POST['field_id'] );
			$field    = $this->core->request_text_data( $_POST['field'] );
			if ( isset( $_POST['id_list'] ) ) {
				$id   = $this->core->request_text_data( $_POST['id_list'] );
				$json = $this->list_update_contact_field( $id, $email, $field_id, $field );
			} else {
				$id   = $this->core->request_text_data( $_POST['id_campaign'] );
				$json = $this->campaign_update_contact_field( $id, $email, $field_id, $field );
			}
		}
		wp_send_json( $json );
		die;
	}

	public function add_header_column_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) ) {
			if ( isset( $_POST['id_list'] ) ) {
				$id_list = $this->core->request_text_data( $_POST['id_list'] );
				$result  = $this->list_add_header_column( $id_list );
			} else {
				$id_campaign = $this->core->request_text_data( $_POST['id_campaign'] );
				$result      = $this->campaign_add_header_column( $id_campaign );
			}
			if ( $result === true ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function edit_header_column_callback() {
		$this->core->check_auth();
		$json = array(
			'message' => '',
			'success' => false
		);
		if ( ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) ) && isset( $_POST['field_id'], $_POST['field'] ) ) {
			$field_id = $this->core->request_text_data( $_POST['field_id'] );
			$field    = $this->core->request_text_data( $_POST['field'] );
			if ( isset( $_POST['id_list'] ) ) {
				$id_list = $this->core->request_text_data( $_POST['id_list'] );
				$json    = $this->list_edit_header_column( $id_list, $field_id, $field );
			} else {
				$id_campaign = $this->core->request_text_data( $_POST['id_campaign'] );
				$json        = $this->campaign_edit_header_column( $id_campaign, $field_id, $field );
			}

		}
		wp_send_json( $json );
		die;
	}

	public function delete_header_column_callback() {
		$this->core->check_auth();
		if ( ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) ) && isset( $_POST['field_id'] ) ) {
			$field_id = $this->core->request_text_data( $_POST['field_id'] );
			if ( isset( $_POST['id_list'] ) ) {
				$id_list = $this->core->request_text_data( $_POST['id_list'] );
				$result  = $this->list_delete_header_column( $id_list, $field_id );
			} else {
				$id_campaign = $this->core->request_text_data( $_POST['id_campaign'] );
				$result      = $this->campaign_delete_header_column( $id_campaign, $field_id );
			}
			if ( $result === true ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function delete_contacts_selection_callback() {
		$this->core->check_auth();
		if ( ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) )
		     && isset( $_POST['search'], $_POST['targeting_rules'], $_POST['contacts_selection'], $_POST['contacts_selection_type'] ) ) {
			$search                  = $this->core->request_text_data( $_POST['search'] );
			$targeting_rules         = $this->core->request_text_data( $_POST['targeting_rules'] );
			$contacts_selection      = $this->core->request_text_data( $_POST['contacts_selection'] );
			$contacts_selection_type = $this->core->request_text_data( $_POST['contacts_selection_type'] );
			if ( isset( $_POST['id_list'] ) ) {
				$id     = $this->core->request_text_data( $_POST['id_list'] );
				$result = $this->list_delete_contacts_selection( $id, $search, $targeting_rules, $contacts_selection, $contacts_selection_type );
			} else {
				$id     = $this->core->request_text_data( $_POST['id_campaign'] );
				$result = $this->campaign_delete_contacts_selection( $id, $search, $targeting_rules, $contacts_selection, $contacts_selection_type );
			}
			if ( $result === true ) {
				wp_send_json_success();
			}
		}
		wp_send_json_error();
		die;
	}

	public function delete_all_contacts_callback() {
		$this->core->check_auth();
		if ( isset( $_POST['id_list'] ) || isset( $_POST['id_campaign'] ) ) {
			if ( isset( $_POST['id_list'] ) ) {
				$id = $this->core->request_text_data( $_POST['id_list'] );
				$this->list_delete_all_contacts( $id );
			} else {
				$id = $this->core->request_text_data( $_POST['id_campaign'] );
				$this->campaign_delete_all_contacts( $id );
			}
			wp_send_json_success();
		}
		wp_send_json_error();
		die;
	}

}