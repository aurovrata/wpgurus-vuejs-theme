<?php
/* hooks*/
add_action( 'wp_enqueue_scripts', 'wpgurus_enqueue_styles' );
//add_action( 'wp_head', 'martinhal_landing_site_head');
//add_action( 'rest_api_init', 'add_page_body_class_to_rest_api_fields');

//includes: load initial page data, saves an extra request by the vueJS controller.
require_once plugin_dir_path(__DIR__).'include/load-data.php';
new Initial_LoadData();
//inlcudes: load initial data for menus.
require_once plugin_dir_path(__DIR__).'include/load-menu.php';
new Initial_LoadMenu();

/*function */
function wpgurus_enqueue_styles() {
  $theme_folder = get_template_directory_uri();
  wp_enqueue_script( 'vue-js', $theme_folder . '/js/vue/vue.js', null, '2.5.16', true);
  wp_enqueue_script( 'vue-resource-js', $theme_folder . '/js/vue/vue-resource.js', array('vue-js'), '1.5.0', true);
  wp_enqueue_script( 'vue-router-js', $theme_folder . '/js/vue/vue-router.js', array('vue-js'), '3.0.1', true);
  wp_enqueue_script('jquery');
  wp_enqueue_script( WPGURUS_APP, $theme_folder . '/js/app.js', array('vue-router-js', 'jquery'), WPGURUS_V2_VERSION, true);
  wp_enqueue_style( WPGURUS_APP, $theme_folder . '/css/main.css', null,WPGURUS_V2_VERSION,'all');
}

function add_page_body_class_to_rest_api_fields(){
      register_rest_field( 'page', 'bodyClass', array(
          'get_callback' => function( $page_arr ) {
              $page_obj = get_post( $page_arr['id'] );
              $classes = get_body_class();
              return join(' ', $classes);
          },
          'schema' => array(
              'description' => __( 'Body classes.' ),
              'type'        => 'string'
          ),
      ) );
  }

function wpgurus_domain_url(){
  $home = network_site_url(); //in case wpmu.
  if($idx = strpos($home, 'localhost')){
    $home = substr($home, 0,$idx + strlen('localhost') +1);
  }
  return $home;
}
