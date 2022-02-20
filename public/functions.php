<?php
/* hooks*/
add_action( 'wp_enqueue_scripts', 'wpgurus_enqueue_styles' );
add_filter( 'body_class', 'wpgurus_clear_body_class',0,1);
//add_action( 'wp_head', 'martinhal_landing_site_head');
//modify the rest menus to inlcude the vuejs custom checkbox.
add_action( 'rest_menus_format_menu_item', 'add_menu_rest_fields');
add_action( 'rest_api_init', 'add_featured_image_urls_to_posts_pages' );

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

/*function */
function wpgurus_clear_body_class( $classes ) {
  return array();
}
function wpgurus_enqueue_styles() {
  $theme_folder = get_template_directory_uri();
  $min = '';
  if(WP_DEBUG){
    wp_register_script( 'vue-js', $theme_folder . '/js/vue/vue.js', null, '2.5.16', true);
    wp_register_script( 'vue-resource-js', $theme_folder . '/js/vue/vue-resource.js', array('vue-js'), '1.5.0', true);
    wp_register_script( 'vue-router-js', $theme_folder . '/js/vue/vue-router.js', array('vue-js'), '3.0.1', true);
  }else{
    $min = '.min';
    wp_register_script( 'vue-js', $theme_folder . '/js/vue/prod/v2.5/vue.min.js', null, '2.5.17', true);
    wp_register_script( 'vue-resource-js', $theme_folder . '/js/vue/prod/vue-resource.min.js', array('vue-js'), '1.5.1', true);
    wp_register_script( 'vue-router-js', $theme_folder . '/js/vue/prod/vue-router.min.js', array('vue-js'), '3.0.1', true);
  }
  wp_enqueue_script('jquery');
  /** include custo vuejs methods/data from child theme
  *@since v0.7
  */
  $custom_vuejs = get_stylesheet_directory()."/js/custom-vuejs{$min}.js";
  $dep = array('vue-router-js', 'vue-resource-js'); //, 'wp-api'
  if(file_exists($custom_vuejs)){
    wp_register_script( WPGURUS_APP_CUSTOM, get_stylesheet_directory_uri()."/js/custom-vuejs{$min}.js", array(), null, true);
    $dep[] = WPGURUS_APP_CUSTOM;
  }
  wp_enqueue_script( WPGURUS_APP, $theme_folder . "/js/app{$min}.js", $dep, WPGURUS_V2_VERSION, true);
  wp_localize_script(WPGURUS_APP, 'wpGurusVueJSlocal',array('debug'=>WP_GURUS_DEBUG));

  wp_enqueue_style( WPGURUS_APP, $theme_folder . "/css/main{$min}.css", array() , WPGURUS_V2_VERSION,'all');
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
  $is_post = $home_link = false;

  $post_id = null;
  $links = array();
  $home = home_url();

  $translated_menu = array();
  $current_lang = '';
  if(isset($args['lang'])){
    $current_lang = $args['lang'];
  }
  if(function_exists('pll_languages_list')){
    $names = pll_languages_list(array('hide_empty'=>1,'fields'=>'name'));
    $langs = pll_languages_list(array('hide_empty'=>1));
    foreach($langs as $idx=>$lang){
      $translated_menu[$lang]=array(
        'slug'=>$lang,
        'name'=>$names[$idx],
        'url'=>'',
        'current_lang'=>($current_lang==$lang),
      );
    }
      // if( isset($links[$lang]) ) $menu[$lang]['url'] = $links[$lang];
  }
  switch(true){
    case isset($args['id']):
      /**
      *@since 0.9.2
      * allow for archive pages from taxonomy.
      */
      switch(true){
        case isset($_GET['tax']):
          $link = get_term_link( $args['id'], $_GET['tax']);
          foreach($translated_menu as $lang=>&$lmenu){
            if(function_exists('pll_is_translated_taxonomy') && pll_is_translated_taxonomy( $_GET['tax'] ) ){
              $term_id = pll_get_term( $args['id'], $lang );
              $lmenu['url'] = get_term_link($term_id, $_GET['tax']);
              // debug_msg($links[$lang], $lang.':');
            }else { /* all links are the same.*/
              $lmenu['url'] = str_replace($home, pll_home_url( $lang ), $link);
            }
          }
          break;
        default:/* assume this is a post/page */
          $post_id=$args['id'];
          break;
      }
      break;
    case isset($args['slug']):
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

          global $wp_rewrite;
          foreach($translated_menu as $lang=>&$lmenu){
            if ( get_option( 'permalink_structure' ) && is_array( $post_type_obj->rewrite ) ) {
              if(function_exists('pll_is_translated_post_type') && pll_is_translated_post_type( $_GET['archive'] )){
        		    $struct = apply_filters( 'wpgurus_theme_polylang_translate_archive_slug-'. $_GET['archive'], $post_type_obj->rewrite['slug'], $lang) ;
              }else { /* all links are the same.*/
                $struct = $post_type_obj->rewrite['slug'];
              }
          		if ( $post_type_obj->rewrite['with_front'] ) $struct = $wp_rewrite->front . $struct;
          		else $struct = $wp_rewrite->root . $struct;
          		$link = home_url(  $struct );
          	} else {
          		$link = home_url( '?post_type=' . $_GET['archive'], $lang );
          	}
            $lmenu['url'] = user_trailingslashit(str_replace($home.'/', pll_home_url($lang), $link));
          }
          break;
        default: /*assume its a post/page*/
          $is_post = true;
          break;
      }

      if($is_post){ //get the id of the post.
        $post_args = array(
          'name'           => $args['slug'],
          'post_type'      => $post_type,
          'post_status'    => 'publish',
          'posts_per_page' => 1,
        );
        $my_posts = get_posts( $post_args );
        if( $my_posts ) {
          $post_id = $my_posts[0]->ID;
        }else $home_link = true;
      }
      break;
    default: //home link.
      $home_link = true;
      break;
  }

  if(isset($post_id) && function_exists('pll_get_post_translations')){
    $tposts = pll_get_post_translations($post_id);
    foreach($translated_menu as $lang=>&$lmenu){
      if(isset($tposts[$lang])){
        $lmenu['url'] = get_permalink($tposts[$lang]);
      }
    }

    // debug_msg($pll_args, 'args ');
    // debug_msg($menu, 'restult ');
  }else if($home_link && function_exists('pll_home_url')){
    foreach($translated_menu as $lang=>&$lmenu) $lmenu['url'] = pll_home_url($lang);
  }
  return apply_filters('wpgurus_theme_polylang_menu', $translated_menu, $current_lang);
}
/**
* Register polylang route
*/
function register_polylang_route(){
  register_rest_route( 'wpgurus/v2', '/polylang/(?P<lang>[a-z]{2})', array(
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
  register_rest_route( 'wpgurus/v2', '/polylang/(?P<lang>[a-z]{2})/(?P<id>\d+)', array(
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
  register_rest_route( 'wpgurus/v2', '/polylang/(?P<lang>[a-z]{2})/(?P<slug>[a-zA-Z0-9_-]+)', array(
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
  return rest_url('/wpgurus/v2/polylang/'.set_current_language('').'/');
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
      if(is_array($post) && isset($post['language_menu'])){
        unset($post['language_menu']);
        // debug_msg($results, 'language_menu results ');

      }
    }
  }
  return $results;
}

//add feathured image thumbnail to posts/pages.
function add_featured_image_urls_to_posts_pages($object){
  // register_rest_field ( 'name-of-post-type', 'name-of-field-to-return', array-of-callbacks-and-schema() )
    register_rest_field( array('post','page'), 'featured_urls', array(
        'get_callback'    => function($object){
          //$sizes = get_intermediate_image_sizes();
          //$sizes = array('thumbnail','medium', 'landing_header_large');
          $tid = get_post_meta($object['id'], '_thumbnail_id', true);
          $results = array('thumbnail'=>'', 'medium'=>'','large'=>'');
          $results = apply_filters('wpgurus_theme_featured_image_sizes', $results);
          if(!empty($tid)){
            foreach($results as $size=>$value){
              $results[$size] = wp_get_attachment_image_src($tid, $size);
            }
          }
          return $results;
        },
        'schema'          => null,
      )
    );
}
