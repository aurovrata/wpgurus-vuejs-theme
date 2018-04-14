<?php
/*
File is loaded by functions.php

Hooks used in this file.
*/
//setup custom image sizes.
add_action( 'after_setup_theme', 'setup_wpgurus_theme' );

add_action( 'init', 'wpcodex_add_template_support_for_pages', 100 );


/*functions*/
function setup_wpgurus_theme(){
  // This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'martinhal2' ),
    'footer' => esc_html__( 'Footer Menu', 'martinhal2' )
	) );
}
/**
 * Enables the page templates.
 */
function wpcodex_add_template_support_for_pages() {
	add_post_type_support( 'page', 'page-attributes' );
}
