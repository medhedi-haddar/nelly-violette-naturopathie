<?php if ( defined( 'ABSPATH' ) ) { ?>
<?php
$widget_id      = $params['widget_id'];
$before_widget  = $params['before_widget'];
$title          = $params['title'];
$after_widget   = $params['after_widget'];
$fields         = $params['fields'];
$widget_message = $params['widget_message'];
$id             = $params['id'];
$list_fields    = $params['list_fields'];
echo $before_widget;
echo $title;
echo $widget_message;
?>
<p>
	<label for="jackmail_widget_email_<?php esc_attr_e( $id ) ?>"><?php _e( 'Email', 'jackmail-newsletters' ) ?></label>
	<input id="jackmail_widget_email_<?php esc_attr_e( $id ) ?>" name="jackmail_widget_email" type="text" autocomplete="off"/>
</p>
<?php
foreach ( $list_fields as $key => $field ) {
	$i = $key + 1;
	if ( in_array ( $i, $fields ) ) {
		?>
		<p>
			<label for="jackmail_widget_field<?php echo $i ?>_<?php esc_attr_e( $id ) ?>">
				<?php echo htmlentities( ucfirst( mb_strtolower( $field ) ) ) ?>
			</label>
			<input id="jackmail_widget_field<?php echo $i ?>_<?php esc_attr_e( $id ) ?>" name="jackmail_widget_field<?php echo $i ?>" type="text" autocomplete="off"/>
		</p>
		<?php
	}
}
?>
<p>
	<input onclick="submit_jackmail_widget_form_<?php esc_attr_e( $id ) ?>()" type="button" value="<?php esc_attr_e( 'OK', 'jackmail-newsletters' ) ?>"/>
</p>
<div id="jackmail_widget_container_form_<?php esc_attr_e( $id ) ?>"></div>
<script type="text/javascript">
	function submit_jackmail_widget_form_<?php esc_attr_e( $id ) ?>() {
		var form = document.createElement( 'form' );
		form.setAttribute( 'method', 'post' );
		form.setAttribute( 'action', '' );
		form.setAttribute( 'id', 'jackmail_widget_form_<?php esc_attr_e( $id ) ?>' );
		var i;
		var nb_list_fields = <?php echo count ( $list_fields ) ?>;
		var fields = [];
		for ( i = 0; i < nb_list_fields; i++ ) {
			var value = '';
			if ( document.getElementById( 'jackmail_widget_field' + ( i + 1 ) + '_<?php esc_attr_e( $id ) ?>' ) ) {
				value = document.getElementById( 'jackmail_widget_field' + ( i + 1 ) + '_<?php esc_attr_e( $id ) ?>' ).value;
			}
			fields.push( value );
		}
		var id = document.createElement( 'input' );
		id.setAttribute( 'type', 'hidden' );
		id.setAttribute( 'name', 'jackmail_widget_id' );
		id.setAttribute( 'value', '<?php esc_attr_e( $widget_id ) ?>' );
		form.appendChild( id );
		var email = document.createElement( 'input' );
		email.setAttribute( 'type', 'hidden' );
		email.setAttribute( 'name', 'jackmail_widget_email' );
		email.setAttribute( 'value', document.getElementById( 'jackmail_widget_email_<?php esc_attr_e( $id ) ?>' ).value );
		form.appendChild( email );
		var field = document.createElement( 'input' );
		field.setAttribute( 'type', 'hidden' );
		field.setAttribute( 'name', 'jackmail_widget_fields' );
		field.setAttribute( 'value', JSON.stringify( fields ) );
		form.appendChild( field );
		field = document.createElement( 'input' );
		field.setAttribute( 'type', 'hidden' );
		field.setAttribute( 'name', 'nonce' );
		field.setAttribute( 'value', '<?php echo wp_create_nonce( 'jackmail_widget' ) ?>' );
		form.appendChild( field );
		document.getElementById( 'jackmail_widget_container_form_<?php esc_attr_e( $id ) ?>' ).appendChild( form );
		document.getElementById( 'jackmail_widget_form_<?php esc_attr_e( $id ) ?>' ).submit();
	}
</script>
<?php
echo $after_widget;
?>
<?php } ?>