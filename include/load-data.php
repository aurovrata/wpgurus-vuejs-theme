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
		$query->set( 'ignore_sticky_posts', true );
		$query->set( 'posts_per_page', 10 );
	}

	/**
	 * Adds the json-string data to the react app script
	 */
	public function print_data() {
		$data = sprintf(
			'var InitialPage = %s;'.PHP_EOL.'const WPrestPath = %s;',
			$this->add_json_data(),
      $this->add_json_rest_path()
		);
		$result = wp_add_inline_script( WPGURUS_APP, $data, 'before' );


	}

	public function add_json_rest_path(){
		$root = home_url('/wp-json/wp/v2/');
		$pid  = (int) \get_option( 'page_on_front' );

		return \wp_json_encode(array(
			'root'=> $root,
			'home' => $root.'pages/'.$pid,
			'menu' => home_url('/wp-json/wp-api-menus/v2/menus/')
		));
	}

	/**
	 * Dumps the current query response as a JSON-encoded string
	 */
	public function add_json_data() {
    $data = wp_json_encode( array(
			'data' => $this->get_post_data(),
			'paging' => $this->get_total_pages(),
      'homepage' => is_front_page()
		) );
    return $data;
	}

	/**
	 * Gets current posts data from the JSON API server
	 *
	 * @return array
	 */
	public function get_post_data() {
		if ( ! ( ( is_home() && ! is_paged() ) || is_page() || is_singular() ) ) {
			return array();
		}

		$posts = $GLOBALS['wp_query']->posts;

		$rest_server        = rest_get_server();
		$data               = array();
		$request            = new \WP_REST_Request();
		$request['context'] = 'view';
		foreach ( (array) $posts as $post ) {
			$controller = new \WP_REST_Posts_Controller( $post->post_type );
			$data[]     = $rest_server->response_to_data( $controller->prepare_item_for_response( $post, $request ), true );
		}

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
