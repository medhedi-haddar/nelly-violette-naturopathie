<?php

class Jackmail_Widget extends WP_Widget {

	public function __construct() {

		parent::__construct( 'jackmail_widget', 'Jackmail', array( 'description' => __( 'Create your form and collect submit into your Jackmail contacts list.', 'jackmail-newsletters' ), 'customize_selective_refresh' => true ) );

		add_action( 'widgets_init', array( $this, 'jackmail_widget' ) );

	}

	public function jackmail_widget() {
		register_widget( 'Jackmail_Widget' );
	}

	private function get_lists() {
		global $wpdb;
		$sql   = "
		SELECT `id`, `name`, `fields`
		FROM `{$wpdb->prefix}jackmail_lists`
		WHERE `type` = ''
		ORDER BY `created_date_gmt` DESC";
		$lists = $wpdb->get_results( $sql );
		return $lists;
	}

	private function get_json_js( Array $array ) {
		$core = new Jackmail_Core();
		return $core->json_encode( $array );
	}

	private function get_html_escape( $string ) {
		return str_replace( '"', '&quot;', $string );
	}

	public function form( $instance ) {
		$core          = new Jackmail_Core();
		$id_list       = isset( $instance['id_list'] ) ? $instance['id_list'] : '';
		$lists         = $this->get_lists();
		$lists_details = array();
		foreach ( $lists as $key => $list ) {
			$fields_explode  = $core->explode_fields( $list->fields );
			$lists_details[] = array(
				'id'         => $list->id,
				'all_fields' => $fields_explode
			);
		}
		$params = array(
			'title'            => isset( $instance['title'] ) ? $instance['title'] : '',
			'fields'           => isset( $instance['fields'] ) ? $instance['fields'] : '[]',
			'html_id_list'     => $this->get_html_escape( $id_list ),
			'lists'            => $lists,
			'lists_details'    => $lists_details,
			'js_lists_details' => $this->get_json_js( $lists_details ),
			'id'               => $core->get_current_timestamp() . mt_rand( 1000000, 9999999 ),
			'id_list'          => $id_list
		);
		include plugin_dir_path( __FILE__ ) . 'html/widget_back.php';
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array(
			'id_list' => isset( $new_instance['id_list'] ) ? $new_instance['id_list'] : '',
			'title'   => isset( $new_instance['title'] ) ? $new_instance['title'] : '',
			'fields'  => isset( $new_instance['fields'] ) ? $new_instance['fields'] : '[]'
		);
		return $instance;
	}

	public function widget( $args, $instance ) {
		$core = new Jackmail_Core();
		global $wpdb;
		if ( isset( $instance['id_list'], $instance['title'], $instance['fields'] ) ) {
			if ( isset( $args['before_widget'], $args['before_title'], $args['after_title'], $args['after_widget'] ) ) {
				$id_list        = $instance['id_list'];
				$widget_id      = $args['widget_id'];
				$widget_message = '';
				if ( isset( $_POST['jackmail_widget_id'], $_POST['jackmail_widget_email'], $_POST['jackmail_widget_fields'], $_POST['nonce'] ) ) {
					$widget_id_post = $core->request_text_data( $_POST['jackmail_widget_id'] );
					$email_post     = $core->request_email_data( $_POST['jackmail_widget_email'] );
					$fields_post    = $core->request_text_data( $_POST['jackmail_widget_fields'] );
					$nonce          = $core->request_text_data( $_POST['nonce'] );
					if ( wp_verify_nonce( $nonce, 'jackmail_widget' ) && is_email( $email_post ) ) {
						if ( $widget_id === $widget_id_post ) {
							$result = $this->insert_data( $id_list, $email_post, $fields_post );
							if ( $result ) {
								$widget_message = '<p>' . __( 'Email saved', 'jackmail-newsletters' ) . '.</p>';
							} else {
								$widget_message = '<p>' . __( 'Email was not saved', 'jackmail-newsletters' ) . '.</p>';
							}
						}
					} else {
						$widget_message = '<p>' . __( 'Email was not saved', 'jackmail-newsletters' ) . '.</p>';
					}
				}
				$sql  = "
				SELECT `fields`
				FROM `{$wpdb->prefix}jackmail_lists`
				WHERE `id` = %s";
				$list = $wpdb->get_row( $wpdb->prepare( $sql, $id_list ) );
				if ( isset( $list->fields ) ) {
					$params = array(
						'widget_id'      => $widget_id,
						'before_widget'  => $args['before_widget'],
						'title'          => $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'],
						'after_widget'   => $args['after_widget'],
						'fields'         => $core->explode_fields( $instance['fields'] ),
						'widget_message' => $widget_message,
						'id'             => $core->get_current_timestamp() . mt_rand( 1000000, 9999999 ),
						'list_fields'    => $core->explode_fields( $list->fields )
					);
					include plugin_dir_path( __FILE__ ) . 'html/widget_front.php';
				}
			}
		}
	}

	private function insert_data( $id_list, $email, $fields_values ) {
		$core = new Jackmail_Core();
		global $wpdb;
		$fields_values       = $core->explode_data( $fields_values );
		$header_fields       = array();
		$values_fields       = array();
		$values              = array();
		$header_fields[]     = '`email`';
		$values_fields[]     = '%s';
		$values[]            = $email;
		$table_list_contacts = "{$wpdb->prefix}jackmail_lists_contacts_{$id_list}";
		if ( $core->check_table_exists( $table_list_contacts, false ) ) {
			$columns = $core->get_table_columns( $table_list_contacts, false );
			foreach ( $fields_values as $key => $field_value ) {
				$i = $key + 1;
				if ( in_array( 'field' . $i, $columns ) ) {
					$header_fields[] = '`field' . $i . '`';
					$values_fields[] = '%s';
					$values[]        = $field_value;
				}
			}
			$header_fields = implode( ', ', $header_fields );
			$values_fields = implode( ', ', $values_fields );
			$sql           = "INSERT IGNORE INTO `{$table_list_contacts}` ( {$header_fields} ) VALUES ( {$values_fields} )";
			$wpdb->query( $wpdb->prepare( $sql, $values ) );
			$update_return = $core->updated_list_contact( $id_list );
			if ( $update_return !== false ) {
				return true;
			}
		}
		return false;
	}

}