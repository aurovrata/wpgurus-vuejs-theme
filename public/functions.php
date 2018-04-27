<?php
/* hooks*/
add_action( 'wp_enqueue_scripts', 'wpgurus_enqueue_styles' );
//add_action( 'wp_head', 'martinhal_landing_site_head');
//modify the rest menus to inlcude the vuejs custom checkbox.
add_action( 'rest_menus_format_menu_item', 'add_menu_rest_fields');
if(defined("POLYLANG_VERSION")){ //filter the home url for translated pages.
  add_filter('wpgurus_theme_multilingual', '__return_true');
  add_filter('wpgurus_theme_home_url', 'filter_polylang_home_url');
  add_filter('wpgurus_theme_language_menu', 'polylang_language_menu');
  add_action( 'rest_api_init', 'register_polylang_route'  );
  add_filter('wpgurus_theme_current_language', 'set_current_language');
  add_filter('wpgurus_theme_language_rest', 'set_polylang_rest_path');
}

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
//function to include vuejs templates.
function include_vue_template($id, $component, $page='index' ){
  switch($component){
    case 'menu':
      if(!has_nav_menu($id)){
        return;
      }
      break;
  }
  set_query_var('template_id', $id);
  get_template_part('templates/'.$page, $component);
  return;
}
/**
*
*
* @param array $rest_menu the menu being returned for the rest api.
*/
function add_menu_rest_fields($rest_menu_item){
  if(empty($rest_menu_item) || !isset($rest_menu_item['id'])){
    return $rest_menu_item;
  }
  $item_id = $rest_menu_item['id'];
  $vjs = get_post_meta( $item_id, '_menu_item_exit_vuejs_router', true);
  if($vjs && 'exit'==$vjs){
    $vjs = false;
  }else{
    $vjs = true;
  }
  $rest_menu_item['isvjslink'] = $vjs;
  return $rest_menu_item;
}

function wpgurus_domain_url(){
  $home = network_site_url(); //in case wpmu.
  if($idx = strpos($home, 'localhost')){
    $home = substr($home, 0,$idx + strlen('localhost') +1);
  }
  return $home;
}
/**
* Setup the home url with the language slugs for multilingual sites using PolyLang plugin.
*/
function filter_polylang_home_url($url){
  if(function_exists('pll_current_language')){
    $slug = pll_current_language();
    if(function_exists('pll_home_url')){
      $url = pll_home_url($slug);
    }
  }
  return $url;
}
/**
* setup the current page request translated permalinks.
*
*/
function polylang_post_menu($request){
  $params     = $request->get_params();
  return polylang_language_menu(array(), $params['id']);
}
function polylang_language_menu($menu=array(), $page_id=0){
  $args = array('raw'=>1);
  if($page_id>0){
    $args['post_id']=$page_id;
  }
  if(function_exists('pll_the_languages')){
    $menu = pll_the_languages($args);
  }
  return $menu;
}
/**
* Register polylang route
*/
function register_polylang_route(){
  register_rest_route( 'wpgurus/v2', '/polylang', array(
      array(
          'methods'  => WP_REST_Server::READABLE,
          'callback' => 'polylang_language_menu' ,
      )
  ));
  register_rest_route( 'wpgurus/v2', '/polylang/(?P<id>\d+)', array(
      array(
          'methods'  => WP_REST_Server::READABLE,
          'callback' => 'polylang_post_menu' ,
          'args'     => array(
              'context' => array(
              'default' => 'view',
              ),
          ),
      )
  ));
}
/**
*
*
*/
function set_current_language($lang){
  if(function_exists('pll_current_language')){
    $lang = pll_current_language();
  }
  return $lang;
}
function set_polylang_rest_path($path){
  return home_url('/wp-json/wpgurus/v2/polylang/');
}
