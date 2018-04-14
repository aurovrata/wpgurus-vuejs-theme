<?php
/**
 * Pre-load the navigation menu as a JSON object
 *
 * @package WPgurusWPtheme
 */

/**
 * Class wrapper for menu loading
 */
class Initial_LoadMenu {
	/**
	 * Set up actions
	 */
	public function __construct() {
		add_filter( 'wp_enqueue_scripts', array( $this, 'print_data' ) );
	}

	/**
	 * Adds the json-string data to the react app script
	 */
	public function print_data() {
		$menu_data = sprintf(
			'var InitialMenu = %s;'.PHP_EOL.'var SitePaths = %s;',
			$this->add_json_data(),
      $this->add_json_paths()
		);
		wp_add_inline_script( WPGURUS_APP, $menu_data, 'before' );
	}
  function add_json_paths(){
    return wp_json_encode( array(
      'home' => network_site_url(),
			'root' => wpgurus_domain_url(), //public/functions.php
      'logo' => apply_filters('wpgurus_theme_logo', get_stylesheet_directory_uri().'/images/icons.svg#logo'),
		) );
  }
	/**
	 * Dumps the current query response as a JSON-encoded string
	 */
	public function add_json_data() {
    $menus = apply_filters('wpgurus_theme_vuejs_menu', array('primary', 'footer'));
    $data = array('enabled' => class_exists( 'WP_REST_Menus' ));
    foreach($menus as $menu){
      $data[$menu] = $this->get_menu_data($menu);
    }
		return wp_json_encode( $data );
	}

	/**
	 * Gets menu data from the JSON API server
	 *
	 * @return array
	 */
	public function get_menu_data($location) {
		$menu = array();

		$request = new \WP_REST_Request();
		$request['context'] = 'view';
		$request['location'] = $location;

		if ( class_exists( 'WP_REST_Menus' ) ) {
			$menu_api = new WP_REST_Menus();
			$menu = $menu_api->get_menu_location( $request );
		}

		return $menu;
	}
}
