<?php
//include soem files.
if ( ! defined( 'WPGURUS_APP' ) ) {
	define( 'WPGURUS_APP', 'wpgurus-vue-theme' );
}
if ( ! defined( 'WPGURUS_V2_VERSION' ) ) {
	define( 'WPGURUS_V2_VERSION', '0.8.2' );
}
//dashboard modifications.
require get_parent_theme_file_path('/admin/dashboard.php');
//debug_msg fn.
require get_parent_theme_file_path('/admin/wordpress-gurus-debug-api.php');

//front-end functions.
require get_parent_theme_file_path('/public/functions.php');
