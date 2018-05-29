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
		add_filter( 'wp_enqueue_scripts', array( $this, 'print_data' ) );
	}

	/**
	 * Unstick sticky posts to mirror the behavior of the REST API
	 *
	 * @param WP_Query $query The WP_Query object.
	 */
	public function unstick_stickies( $query ) {
		if(is_admin()) return;
		$query->set( 'ignore_sticky_posts', true );
		$query->set( 'posts_per_page', 10 );
	}

	/**
	 * Adds the json-string data to the react app script
	 */
	public function print_data() {
		$data = sprintf(
			'var InitialPage = %s;'.PHP_EOL.'const WPrestPath = %s;'.PHP_EOL.'const VueCustomRoutes = %s;',
			$this->add_json_data(),
      $this->add_json_rest_path(),
      $this->add_json_routes()
		);
		$result = wp_add_inline_script( WPGURUS_APP, $data, 'before' );
	}
  public function add_json_routes(){
    $data = array();
    $post_types = array();
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
					$paths[$api_data] = apply_filters("wpgurus_theme_additional_api_path", '', $api_data);
				}
				$data[$route] = $paths;
				$route = $archive.':postName';
				$apis = apply_filters("wpgurus_theme_additional_api_data", array(), $route);
				$paths = array();
				foreach($apis as $api_data){
					$paths[$api_data] = apply_filters("wpgurus_theme_additional_api_path", '', $api_data);
				}
				$data[$route] = $paths;
	    }
		}
    return \wp_json_encode($data);
  }
	public function add_json_rest_path(){
		$root = rest_url('/wp/v2/');
		$pid  = (int) \get_option( 'page_on_front' );
		$page_id = get_option('page_on_front');
	  if ( $page_id > 0 ) {
	    // Set url for call to retrieve the post, need WP REST API for this
	    $frontpage = rest_url( '/wp/v2/pages/' . $page_id);
		}else{
			$frontpage = rest_url( '/wp/v2/posts/');
		}
		return \wp_json_encode(array(
			'root'=> $root,
			'home' => $root.'pages/'.$pid,
			'menu' => rest_url('/wp-api-menus/v2/menus/'),
      'languages'=>apply_filters('wpgurus_theme_language_rest',home_url()),
			'frontpage'=>$frontpage,

		));
	}

	/**
	 * Dumps the current query response as a JSON-encoded string
	 */
	public function add_json_data() {
    $data =  array(
			'posts' => $this->get_post_data(),
			'paging' => $this->get_total_pages(),
      'homepage' => is_front_page(),
      'lang' => apply_filters('wpgurus_theme_current_language',get_locale())
		) ;
		global $wp;
    $custom_data = array();
    $route = $wp->request ;
    //debug_msg($_SERVER['REQUEST_URI']);
    //debug_msg($route);
    $custom_data = apply_filters('wpgurus_theme_intial_page', $custom_data, $route, $data);
		if(!empty($custom_data)) $data = array_merge($data, $custom_data);
		//debug_msg
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
    //debug_msg($GLOBALS['wp_query']);
    // debug_msg($posts);
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
}
