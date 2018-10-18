<?php
/* hooks*/
add_action( 'wp_enqueue_scripts', 'wpgurus_enqueue_styles' );
add_filter( 'body_class', 'wpgurus_clear_body_class',0,1);
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
  add_action( 'rest_api_init', 'add_language_menu_to_api' );
  add_filter('rest_pre_echo_response', 'remove_archive_language_menus');
}

//includes: load initial page data, saves an extra request by the vueJS controller.
require_once plugin_dir_path(__DIR__).'include/load-data.php';
new Initial_LoadData();
//inlcudes: load initial data for menus.
require_once plugin_dir_path(__DIR__).'include/load-menu.php';
new Initial_LoadMenu();

/*function */
function wpgurus_clear_body_class( $classes ) {
  return array();
}
function wpgurus_enqueue_styles() {
  $theme_folder = get_template_directory_uri();
  if(WP_DEBUG){
    wp_enqueue_script( 'vue-js', $theme_folder . '/js/vue/vue.js', null, '2.5.16', true);
    wp_enqueue_script( 'vue-resource-js', $theme_folder . '/js/vue/vue-resource.js', array('vue-js'), '1.5.0', true);
    wp_enqueue_script( 'vue-router-js', $theme_folder . '/js/vue/vue-router.js', array('vue-js'), '3.0.1', true);
  }else{
    wp_enqueue_script( 'vue-js', $theme_folder . '/js/vue/prod/v2.5/vue.min.js', null, '2.5.17', true);
    wp_enqueue_script( 'vue-resource-js', $theme_folder . '/js/vue/prod/vue-resource.min.js', array('vue-js'), '1.5.1', true);
    wp_enqueue_script( 'vue-router-js', $theme_folder . '/js/vue/prod/vue-router.min.js', array('vue-js'), '3.0.1', true);
  }
  wp_enqueue_script('jquery');
  /** include custo vuejs methods/data from child theme
  *@since v0.7
  */
  $custom_vuejs = get_stylesheet_directory().'/js/custom-vuejs.js';
  $dep = array('vue-router-js', 'vue-resource-js'); //, 'wp-api'
  if(file_exists($custom_vuejs)){
    wp_enqueue_script( 'custom-apps', get_stylesheet_directory_uri().'/js/custom-vuejs.js', array(), null, true);
    $dep[] = 'custom-apps';
  }
  wp_enqueue_script( WPGURUS_APP, $theme_folder . '/js/app.js', $dep, WPGURUS_V2_VERSION, true);
  wp_localize_script(WPGURUS_APP, 'wpGurusVueJSlocal',array('debug'=>WP_GURUS_DEBUG));

  wp_enqueue_style( WPGURUS_APP, $theme_folder . '/css/main.css', array() , WPGURUS_V2_VERSION,'all');
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
* This is hooked to 'rest_menus_format_menu_item', a filter from wp-api-menus plugin which allows the menu link to be modified before it is sent to the rest request.
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
    /**
    * in order to force a relative link to reload we need to absolute link.
    *@since v0.8.1
    */
    if(isset($rest_menu_item['url']) && strpos($rest_menu_item['url'], '/')==0){
      if(is_multisite()) $rest_menu_item['url'] = network_home_url($rest_menu_item['url']);
      else $rest_menu_item['url'] = home_url($rest_menu_item['url']);
      if(substr($rest_menu_item['url'],-1) != '/') $rest_menu_item['url'] .='/';
    }
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
  return polylang_language_menu(array(), $params);
}
function polylang_language_menu($menu=array(), $args=array()){
  $pll_args = array('raw'=>1);
  $is_post = false;
  $links = array();
  $build_raw = false;
  $home = home_url();

  if(isset($args['id'])){
    /**
    *@since 0.9.2
    * allow for archive pages from taxonomy.
    */
    switch(true){
      case isset($_GET['tax']):
        $build_raw = true;
        if(function_exists('pll_languages_list')){
          $langs = pll_languages_list();
          $link = get_term_link( $args['id'], $_GET['tax']);
          foreach($langs as $lang){
            if(function_exists('pll_is_translated_taxonomy') && pll_is_translated_taxonomy( $_GET['tax'] ) ){
              $term_id = pll_get_term( $args['id'], $lang );
              $links[$lang] = get_term_link($term_id, $_GET['tax']);
              // debug_msg($links[$lang], $lang.':');
            }else { /* all links are the same.*/
              $links[$lang] = str_replace($home, pll_home_url( $lang ), $link);
            }
          }
        }
        break;
      default:/* assume this is a post/page */
        $pll_args['post_id']=$args['id'];
        break;
    }
  }else if(isset($args['slug'])){
    /** Fix for translation menu.
    * @since v0.6
    */
    $post_type = 'any';
    switch(true){
      case isset($_GET['ptype']):
        $post_type = $_GET['ptype'];
        $is_post = true;
        break;
      case isset($_GET['archive']) && 'post' != $_GET['archive']: /*post archives lkely pages*/
        $build_raw = true;
        $post_type_obj = get_post_type_object( $_GET['archive'] );
        if(function_exists('pll_languages_list')){
          $langs = pll_languages_list();
          global $wp_rewrite;
          foreach($langs as $lang){
            if ( get_option( 'permalink_structure' ) && is_array( $post_type_obj->rewrite ) ) {
              if(function_exists('pll_is_translated_post_type') && pll_is_translated_post_type( $_GET['archive'] )){
        		    $struct = apply_filters( 'wpgurus_theme_polylang_translate_archive_slug-'. $_GET['archive'], $post_type_obj->rewrite['slug'], $lang) ;
              }else { /* all links are the same.*/
                $struct = $post_type_obj->rewrite['slug'];
              }
          		if ( $post_type_obj->rewrite['with_front'] )
          			$struct = $wp_rewrite->front . $struct;
          		else
          			$struct = $wp_rewrite->root . $struct;
          		 $link = home_url(  $struct );
          	} else {
          		$link = home_url( '?post_type=' . $_GET['archive'], $lang );
          	}
            $links[$lang] = user_trailingslashit(str_replace($home.'/', pll_home_url($lang), $link));
          }
        }
        break;
      default: /*assume its a post/page*/
        $is_post = true;
        break;
    }
    if($is_post){
      $post_args = array(
        'name'           => $args['slug'],
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 1
      );
      $my_posts = get_posts( $post_args );
      if( $my_posts ) {
        $pll_args['post_id']=$my_posts[0]->ID;
      }else $pll_args['force_home']=1;
    }
  }
  if(function_exists('pll_the_languages')){
    $menu = pll_the_languages($pll_args);
  }
  if($build_raw && function_exists('pll_languages_list')){
    $langs = pll_languages_list();
    foreach($langs as $lang){
      if( isset($links[$lang]) ) $menu[$lang]['url'] = $links[$lang];
    }
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
  register_rest_route( 'wpgurus/v2', '/polylang/(?P<slug>[a-zA-Z0-9_-]+)', array(
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
  return rest_url('/wpgurus/v2/polylang/');
}

/**
* Adds language menu to each post/page request.
* TODO: this is only called with polylang, hence need to get all translated posts.
*/
function add_language_menu_to_api($request) {
  //debug_msg($GLOBALS['wp']->query_vars['rest_route']);
  // register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
  register_rest_field( array('post','page'), 'language_menu', array(
      'get_callback'    => function($object){
       return polylang_language_menu(array(), $object['id']);
      },
      'schema'          => null,
    )
  );
}
function remove_archive_language_menus($results){
  if(is_array($results) && count($results)>1){
    foreach($results as &$post){
      if(isset($post['language_menu'])) unset($post['language_menu']);
    }
  }
  return $results;
}
