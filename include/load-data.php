<?php
/**
 * Pre-load the first page's query response as a JSON object
 * Skips the need for an API query on the initial load of a page
 *
 * @package WPgurusWPtheme
 */

/**
 * Class wrapper for data loading
 */
class Initial_LoadData {
	/**
	 * Set up actions
	 */
	public function __construct() {
		add_action( 'pre_get_posts', array( $this, 'unstick_stickies' ) );
		add_filter( 'wp_enqueue_scripts', array( $this, 'print_data' ),20 );
		//debug_msg('load data');
    //add_filter( 'posts_request', array($this, 'bail_main_wp_query'), 10, 2 );
	}

  public function bail_main_wp_query( $sql, $wpQuery ) {
    if ( $wpQuery->is_main_query() ) {
        /* prevent SELECT FOUND_ROWS() query*/
        $wpQuery->query_vars['no_found_rows'] = true;

        /* prevent post term and meta cache update queries */
        $wpQuery->query_vars['cache_results'] = false;

        return false;
    }
    return $sql;
}

	/**
	 * Unstick sticky posts to mirror the behavior of the REST API
	 *
	 * @param WP_Query $query The WP_Query object.
	 */
	public function unstick_stickies( $query ) {
		if(is_admin()) return;
		$query->set( 'ignore_sticky_posts', true );
		$query->set( 'posts_per_page', 10 ); //limit return for now.
	}

	/**
	 * Adds the json-string data to the react app script
	 */
	public function print_data() {
		$data = sprintf(
			'const InitialPage = %s;'.PHP_EOL.'const WPrestPath = %s;'.PHP_EOL.'const VueCustomRoutes = %s;',
			$this->add_json_data(),
      $this->add_json_rest_path(),
      $this->add_json_routes()
		);
		$result = wp_add_inline_script( WPGURUS_APP, $data, 'before' );
	}
  public function add_json_routes(){
		/*
		* Add custom api rest paths to the vuejs component data tree for specific routes.
		* $data[<relative route path>] = array(<data-unique-key>=><rest api path>)
		*/
		$lang = $this->get_lang();
    $data = array();
    $post_types = array('post');
    $post_types = apply_filters('wpgurus_theme_custom_post_routes', $post_types);
    $root = wpgurus_domain_url();
		if(!empty($post_types)){
	    foreach($post_types as $type){
	      $archive = get_post_type_archive_link($type);
	      $archive = str_replace($root,'/',$archive);
				$route = $archive;
				$apis = apply_filters("wpgurus_theme_additional_api_data", array(), $route);
				$paths = array();
				foreach($apis as $api_data){
					$path = apply_filters("wpgurus_theme_additional_api_path", '', $api_data);
					if(empty($path)) continue;
					if(false === strpos($path, 'lang=')){
						if(false === strpos($path, '?') ) $path .='?lang='.$lang;
						else $path .='&lang='.$lang;
					}
					$paths[$api_data] = $path;
				}

				$data[$route] = $paths;
				$route = $archive.':postName';
				$apis = apply_filters("wpgurus_theme_additional_api_data", array(), $route);
				$paths = array();
				foreach($apis as $api_data){
					$path = apply_filters("wpgurus_theme_additional_api_path", '', $api_data);
					if(empty($path)) continue;
					if(false === strpos($path, 'lang=')){
						if(false === strpos($path, '?') ) $path .='?lang='.$lang;
						else $path .='&lang='.$lang;
					}
					$paths[$api_data] = $path;
				}
				$data[$route] = $paths;
	    }
		}
    /**
    * add all published pages to custom routes.
    * add all published posts to custom routes.
    * add all public published cpt to custom routes
    * @since v0.6
    */
		//get cpt post_types.
		$cpt_args = array(
		   'public'   => true,
		   '_builtin' => false,
			 'show_ui'  => true
		);
		$data_pages = array();
		$cpt_types = get_post_types( $cpt_args, 'objects', 'and' );
		//by default get the page & post
    $types = array('page', 'post');
		$rest_bases = array('post'=>'posts', 'page'=>'pages');
		$front_page = get_option('page_on_front',0);

		if($front_page>0){
			$posts_page = get_option('page_for_posts',0);
			if($posts_page>0){
				$archive = get_post_type_archive_link('post');
				$route = str_replace($root,'/',$archive);
				$data_pages[$route] = array(
					'rest'=>rest_url('/wp/v2/posts/?lang='.$lang),
					'post'=>'post',
					'type'=>'archive'
				);
			}
		}
    foreach($cpt_types as $cpt_type){
			$type = $cpt_type->name;
      $types[] = $type;
			$obj = get_post_type_object($type);
			$rest = empty($obj->rest_base) ? $type : $obj->rest_base;
			$rest_bases[$type] = $rest;
			$archive = get_post_type_archive_link($type);
			if(false !== $archive){
				$route = str_replace($root,'/',$archive);
				$data_pages[$route] = array(
					'rest'=>rest_url('/wp/v2/'.$rest.'/?lang='.$lang),
					'post'=>$type,
					'type'=>'archive'
				);
			}
		}
		/**
		* @todo apply a filter for the type of posts to capture in the vuejs router.
		*/
    $query = array(
      'post_type' => $types,
      'status' => 'published',
			'nopaging' => true
    );
    $pages = get_posts($query);
    if($pages){
			$posts_page = get_option( 'page_for_posts' );
      foreach($pages as $page){
				/**
				*v0.6 check if the page is used as the posts archive page.
				*/
				if($posts_page == $page->ID){
					continue;
				}
				$route = str_replace($root,'/',get_permalink($page));
        $data_pages[$route] = array(
					'rest'=> rest_url('/wp/v2/'.$rest_bases[$page->post_type].'/'.$page->ID.'?lang='.$lang),
					'post'=>$page->post_type,
					'type'=>'single',
					'ID'=>$page->ID,
          'async'=> apply_filters('wpgurus_themes_asynchronous_vuejs_template', false, $page->post_name, $page->post_type),
          'script'=> apply_filters('wpgurus_themes_asynchronous_scripts', array(), $page->post_name, $page->post_type )
				);
				/** @since 2.0.0 add custom routes to pages too.*/
				$apis = apply_filters("wpgurus_theme_additional_api_data", array(), $route);
				$paths = array();
				foreach($apis as $api_data){
					$path = apply_filters("wpgurus_theme_additional_api_path", '', $api_data);
					if(empty($path)) continue;
					if(false === strpos($path, 'lang=')){
						if(false === strpos($path, '?') ) $path .='?lang='.$lang;
						else $path .='&lang='.$lang;
					}
					$paths[$api_data] = $path;
				}
				$data[$route] = $paths;
      }
    }
		//front page.
		$page_id = get_option('page_on_front');
		$home = str_replace($root,'/',home_url('/'));
	  if ( $page_id > 0 ) {
	    // Set url for call to retrieve the post, need WP REST API for this
	    $data_pages[$home] = array(
				'rest'=>rest_url( '/wp/v2/pages/' . $page_id.'?lang='.$lang),
				'post'=>'page',
				'type'=>'single'
			);
		}else{
			$data_pages[$home] =array(
				'rest'=> rest_url( '/wp/v2/posts/?lang='.$lang),
				'post'=>'post',
				'type'=>'archive'
			);
		}
    /** add permalinks=>rest paths for taxonomy terms.
    * @since v0.7
    */
    foreach($types as $type){
      $taxonomies = get_object_taxonomies( $type, 'object' );
      foreach($taxonomies as $taxonomy => $tax_obj){
        if(apply_filters('wpgurus_include_taxonomy_routes',false, $taxonomy, $type)){
					$terms = get_terms( array(
				    'taxonomy' => $taxonomy,
				    'hide_empty' => false,
					) );
					foreach($terms as $term){
						$route = str_replace($root,'/', get_term_link($term, $taxonomy));
						$data_pages[$route] = array(
							'rest'=>rest_url( '/wp/v2/'.$rest_bases[$type].'/?'.$taxonomy.'='.$term->term_id.'&lang='.$lang),
							'post'=>$type,
							'type'=>'archive',
							'taxonomy'=>$taxonomy,
							'term'=>$term->term_id,
						);
						//in addition add the custom route to this term.
						$base = empty($tax_obj->rest_base) ? $taxonomy : $tax_obj->rest_base ;
						$path = array(
							$taxonomy => rest_url('/wp/v2/'. $base .'/'. $term->term_id)
						);
            //let user add additional routes.
            $apis = apply_filters("wpgurus_theme_additional_api_data", array(), $route);
    				foreach($apis as $api_data){
    					$path = apply_filters("wpgurus_theme_additional_api_path", '', $api_data);
              if(empty($path)) continue;
    					if(false === strpos($path, 'lang=')){
    						if(false === strpos($path, '?') ) $path .='?lang='.$lang;
    						else $path .='&lang='.$lang;
    					}
              $paths[$api_data] = $path;
    				}
            $data[$route] = $paths;
					}
        }
      }
    }
		/** @since 1.1.0 if current page is 404, stick it in the routes*/
		$page404id = $this->get_404_page();
		$is404 = is_404();
		if( !empty($GLOBALS['wp_query']->posts) && $page404id == $GLOBALS['wp_query']->posts[0]->ID ){
			$is404 = true;
		}
		if($is404){ //add to routes for vuejs to load.
			$route = $_SERVER['REQUEST_URI'];
			$data_pages[$route] = array(
				'rest'=> '',
				'post'=>'page',
				'type'=>'single'
			);
		}
		/*const VueCustomRoutes*/
    return \wp_json_encode(array(
			'routes'=>apply_filters('wpgurus_theme_vuejs_custom_routes', $data), //custom extra rest requests.
			'vues'=>apply_filters('wpgurus_theme_vuejs_routes', $data_pages) //default url_path=>rest_path Vue JS routes.
		));
  }
	public function add_json_rest_path(){
		$root = rest_url('/wp/v2/');
		// $pid  = (int) \get_option( 'page_on_front' );
		$frontpage='';
		$page_id = get_option('page_on_front');
	  if ( $page_id > 0 ) {
	    // Set url for call to retrieve the post, need WP REST API for this
	    $frontpage = rest_url( '/wp/v2/pages/' . $page_id);
		}else{
			$frontpage = rest_url( '/wp/v2/posts/');
		}
		$current = '';
		if( is_front_page() ) $current = $frontpage;
		else{
			$post_type = '';
			if( !empty($GLOBALS['wp_query']->posts)){
				$post_type = $GLOBALS['wp_query']->posts[0]->post_type;
			}
			$obj = get_post_type_object($post_type);
			$rest = empty($obj->rest_base) ? $post_type : $obj->rest_base;

			if(is_single() || is_page()) $rest = $rest.'/'.$GLOBALS['wp_query']->posts[0]->ID;
			$current = rest_url('/wp/v2/'.$rest);
		}
		/* const WPrestPath */
		return \wp_json_encode(array(
			'root'=> $root,
			'menu' => rest_url('/wp-api-menus/v2/menus/'),
      'languages'=>apply_filters('wpgurus_theme_language_rest',home_url()),
			'frontpage'=>$frontpage,
			'current'=>$current
		));
	}

	/**
	 * Dumps the current query response as a JSON-encoded string
	 */
	public function add_json_data() {
		$post_type = '';
		if( !empty($GLOBALS['wp_query']->posts)){
			$post_type = $GLOBALS['wp_query']->posts[0]->post_type;
		}
    //check what kind of homepage ths site has.
    $page_id = get_option('page_on_front');
    if($page_id>0) $home=array('type'=>'post_type', 'object'=>'page');
    else $home=array('type'=>'post_type_archive', 'object'=>'post');
		/** @since 1.1.0 handle 404page plugin*/
		$page404 = $this->get_404_page();
		$is404 = is_404();
		if( !empty($GLOBALS['wp_query']->posts) && $page404 == $GLOBALS['wp_query']->posts[0]->ID ){
			$is404 = true;
		}

    $data =  array(
			'single' => is_single(),
			'archive'=> is_archive() || is_home(),
			'type'=> $is404?'page':$post_type ,
			'paging' => $this->get_total_pages(),
      'homepage' => is_front_page(),
      'homelink' => $home,
      'lang' => apply_filters('wpgurus_theme_current_language',$this->get_lang()),
      'initime' => time(),
			'is404' => $is404,
			'content404'=> apply_filters('wpgurus_404_page_html_content',
																		array(
																			'title'=>array('rendered'=>'<h1>Page Not Found (404)</h1>'),
																			'content'=>array('rendered'=>'<p>This page does not exists</p>')
																		)),
			'page404'=>$page404,
		) ;
		/*const InitialPage*/
    return wp_json_encode($data);
	}

	/**
	 * Gets current posts data from the JSON API server
	 *
	 * @return array
	 */
	public function get_post_data() {
		// if ( ! ( ( is_home() && ! is_paged() ) || is_page() || is_singular() ) ) {
		// 	return array();
		// }

		$posts = $GLOBALS['wp_query']->posts;
    //debug_msg($GLOBALS['wp_query']->queried_object);
    // debug_msg($posts, 'posts');
		$rest_server        = rest_get_server();
		$data               = array();
		$request            = new \WP_REST_Request();
		$request['context'] = 'view';
		foreach ( (array) $posts as $post ) {
			$controller = new \WP_REST_Posts_Controller( $post->post_type );
			$data[]     = $rest_server->response_to_data( $controller->prepare_item_for_response( $post, $request ), true );
		}
		//debug_msg($data);
		return $data;
	}

	/**
	 * Gets current posts data from the JSON API server
	 *
	 * @return int
	 */
	public function get_total_pages() {
		if ( is_404() ) {
			return 0;
		}

		return intval( $GLOBALS['wp_query']->max_num_pages );
	}
	/**
	*  Return the language code
	*
	*@since 0.9
	*@return string language code
	*/
	public function get_lang(){
		$lang = explode('_',get_locale());
		$lang = $lang[0];
		if(function_exists('pll_current_language')) $lang = pll_current_language();
		return $lang;
	}
	/**
	* Function to retrieve the 404 page IDs.
	*
	*@since 2.2.3
	*@param string $param text_description
	*@return array array of $lang=>page_ID key value pair
	*/
	public function get_404_page(){
		// $page404id = get_option( '404page_page_id', 0 );
    if(function_exists('pp_404_get_page_id')) return pp_404_get_page_id();
		$page404id = 0;
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if(is_plugin_active('404page/404page.php')){
			$page404_settings = get_option( '404page_settings', array() );
			if(isset($page404_settings['page_id'])){
				if(function_exists('pll_get_post') && pll_default_language() != pll_current_language()){
					$page404id = pll_get_post($page404_settings['page_id'], pll_current_language());
				}else{
					$page404id = $page404_settings['page_id'];
				}
			}
		}else $page404id = apply_filters('wpgurus_404_page_id', 0, $this->get_lang());
		return $page404id;
	}
}
