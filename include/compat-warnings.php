<?php


/**
 * Adds a message for unsuccessful theme switch.
 *
 * Prints an update nag after an unsuccessful attempt to switch to
 * Foxhound on WordPress versions prior to 4.7.
 */
function wpgurus_upgrade_notice() {
	$message = __( 'WPGurus requires WordPress 4.7 or higher. Please update your site and try again.', 'wpgurus-vuejs-theme' );
	printf( '<div class="error"><p>%s</p></div>', $message ); /* WPCS: xss ok. */
}

/**
 * Prevents the Customizer from being loaded on WordPress versions prior to 4.7.
 *
 * @since Foxhound 1.0
 */
function wpgurus_customize() {
	wp_die( __( 'WPGurus requires WordPress 4.7 or higher. Please update your site and try again.', 'wpgurus-vuejs-theme' ), '', array(
		'back_link' => true,
	) );
}
add_action( 'load-customize.php', 'wpgurus_customize' );

/**
 * Prevents the Theme Preview from being loaded on WordPress versions prior to 4.7.
 *
 * @since Foxhound 1.0
 */
function wpgurus_preview() {
	if ( isset( $_GET['preview'] ) ) {
		wp_die( __( 'WPGurus requires WordPress 4.7 or higher. Please update your site and try again.', 'wpgurus-vuejs-theme' ) );
	}
}
add_action( 'template_redirect', 'wpgurus_preview' );
