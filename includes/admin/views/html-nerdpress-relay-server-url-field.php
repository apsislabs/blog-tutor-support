<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = get_option( 'blog_tutor_support_settings' );

if ( isset( $options['np_relay_server_url'] ) ) {
	$placeholder = $options['np_relay_server_url'];
} else {
	$placeholder = '';
}
?>

<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $menu ) ?>[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $placeholder ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
