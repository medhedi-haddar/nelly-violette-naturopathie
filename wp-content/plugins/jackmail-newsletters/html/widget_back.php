<?php if ( defined( 'ABSPATH' ) ) { ?>
<?php
$title = $params['title'];
$fields = $params['fields'];
$html_id_list = $params['html_id_list'];
$lists = $params['lists'];
$lists_details = $params['lists_details'];
$js_lists_details = $params['js_lists_details'];
$id = $params['id'];
$id_list = $params['id_list'];
?>
<div id="jackmail_widget_content_<?php esc_attr_e( $id ) ?>">
	<p>
		<label for="<?php echo $this->get_field_id( 'title' ) ?>_<?php echo htmlentities( $id ) ?>"><?php _e( 'Title:', 'jackmail-newsletters' ) ?></label>
		<input autocomplete="off" id="<?php echo $this->get_field_id( 'title' ) ?>_<?php echo htmlentities( $id ) ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php esc_attr_e( $title ) ?>" />
	</p>
	<p>
		<label for="jackmail_widget_list_<?php echo $id ?>"><?php _e( 'List:', 'jackmail-newsletters' ) ?></label>
		<select autocomplete="off" class="widefat" id="jackmail_widget_list_<?php echo $id ?>" name="<?php echo $this->get_field_name( 'id_list' ) ?>"
			onkeyup="select_jackmail_list( '<?php esc_attr_e( $id ) ?>', false, '<?php esc_attr_e( $fields ) ?>', '<?php esc_attr_e( $html_id_list ) ?>', jackmail_lists_details )"
			onchange="select_jackmail_list( '<?php esc_attr_e( $id ) ?>', false, '<?php esc_attr_e( $fields ) ?>', '<?php esc_attr_e( $html_id_list ) ?>', jackmail_lists_details )">
			<option value=""><?php _e( 'Select a list', 'jackmail-newsletters' ) ?></option>
			<?php
			foreach ( $lists as $list ) { ?>
			<option value="<?php esc_attr_e( $list->id ) ?>"><?php echo htmlentities( $list->name ) ?></option>
			<?php } ?>
		</select>
	</p>
	<p id="jackmail_widget_fields_<?php echo $id ?>_container">
		<input autocomplete="off" type="hidden" id="jackmail_widget_fields_<?php esc_attr_e( $id ) ?>" name="<?php echo $this->get_field_name( 'fields' ) ?>"/>
		<label><?php _e( 'Fields:', 'jackmail-newsletters' ) ?></label>
		<br/>
		<?php
		foreach ( $lists_details as $list_detail ) {
		?>
		<span id="jackmail_widget_field_<?php esc_attr_e( $list_detail[ 'id' ] ) ?>_<?php esc_attr_e( $id ) ?>_container">
			<?php
			$all_fields = $list_detail[ 'all_fields' ];
			foreach ( $all_fields as $key => $field ) {
			$id_html = $list_detail[ 'id' ] . '_' . ( $key + 1 ) . '_' . $id;
			?>
			<span id="jackmail_widget_field_<?php esc_attr_e( $id_html ) ?>_container">
				<input autocomplete="off" type="checkbox" id="jackmail_widget_field_<?php esc_attr_e( $id_html ) ?>_checkbox" onchange="select_jackmail_list_field( '<?php esc_attr_e( $id ) ?>', <?php echo ( $key + 1 ) ?>, jackmail_lists_details )"/>
				<label id="jackmail_widget_field_<?php esc_attr_e( $id_html ) ?>" for="jackmail_widget_field_<?php esc_attr_e( $id_html ) ?>_checkbox">
					<?php echo htmlentities( ucfirst( mb_strtolower( $field ) ) ) ?>
				</label>
			</span>
			<br/>
			<?php } ?>
		</span>
		<?php } ?>
	</p>
	<script>
		var jackmail_lists_details = <?php echo $js_lists_details ?>;
		select_jackmail_list( '<?php esc_attr_e( $id ) ?>', true, '<?php esc_attr_e( $fields ) ?>', '<?php esc_attr_e( $id_list ) ?>', jackmail_lists_details );
	</script>
</div>
<?php } ?>