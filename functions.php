<?php
//include soem files.
if ( ! defined( 'WPGURUS_APP' ) ) {
	define( 'WPGURUS_APP', 'wpgurus-vue-theme' );
}
  if ( ! defined( 'WPGURUS_APP_CUSTOM' ) ) {
  	define( 'WPGURUS_APP_CUSTOM', 'wpgurus-vue-custom' );
  }
if ( ! defined( 'WPGURUS_V2_VERSION' ) ) {
	define( 'WPGURUS_V2_VERSION', '2.2.3' );
}
//dashboard modifications.
require get_parent_theme_file_path().'/admin/dashboard.php';
//debug_msg fn.
require get_parent_theme_file_path().'/admin/wordpress-gurus-debug-api.php';

//front-end functions.
require get_parent_theme_file_path().'/public/functions.php';

//includes: load initial page data, saves an extra request by the vueJS controller.
require get_parent_theme_file_path().'/include/load-data.php';
// debug_msg(get_parent_theme_file_path().'/include/load-data.php', 'debug ');
new Initial_LoadData();
//inlcudes: load initial data for menus.
require get_parent_theme_file_path('/include/load-menu.php');
new Initial_LoadMenu();
