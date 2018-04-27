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
		$paths = array(
      'home' => apply_filters('wpgurus_theme_home_url',home_url()),
			'root' => wpgurus_domain_url(), //public/functions.php
      'logo' => apply_filters('wpgurus_theme_logo', get_stylesheet_directory_uri().'/images/icons.svg'), //TODO: make dynamic.
		);
		$others = apply_filters('wpgurus_themes_add_sitepaths', array());
		foreach($others as $key=>$path){
			$paths[$key] = $path;
		}
    return wp_json_encode( $paths );
  }
	/**
	 * Dumps the current query response as a JSON-encoded string
	 */
	public function add_json_data() {
    $menus = apply_filters('wpgurus_theme_vuejs_menu', get_nav_menu_locations());
    $data = array('enabled' => class_exists( 'WP_REST_Menus' ));
    foreach($menus as $location=>$menu_id){
			$menu_data = $this->get_menu_data($menu_id);
			if(!empty($menu_data)){
	      $data[$location] = $menu_data;
			}
    }
		if(apply_filters('wpgurus_theme_multilingual', false)){
			$languages = apply_filters('wpgurus_theme_language_menu', array());
      if(!empty($languages)){
        $data['languages']=$languages;
      }
		}
		return wp_json_encode( $data );
	}

	/**
	 * Gets menu data from the JSON API server
	 *
	 * @return array
	 */
	public function get_menu_data($menu_id) {
		$menu = array();

		$request = new \WP_REST_Request();
		$request['context'] = 'view';
		$request->set_url_params(array('id'=> $menu_id));
		//$request['location'] = $location;

		if ( class_exists( 'WP_REST_Menus' ) ) {
			$menu_api = new WP_REST_Menus();
			$menu = $menu_api->get_menu( $request );
		}
		return $menu;
	}
}
