<?php
/*
File is loaded by functions.php

Hooks used in this file.
*/
//setup custom image sizes.
add_action( 'after_setup_theme', 'setup_wpgurus_theme' );

add_action( 'init', 'wpcodex_add_template_support_for_pages', 100 );

//uses the custom hook from either Menu Itesm Custom Fields plugin or Menu Image plugin.
add_action('wp_nav_menu_item_custom_fields', 'menu_check_to_exit_router', 10, 4);
add_filter( 'manage_nav-menus_columns', 'menu_router_nav_menu_manage_columns' , 11 );
add_action( 'save_post_nav_menu_item','menu_router_save_post_action' , 10, 2 );
/*functions*/
function setup_wpgurus_theme(){
  $menus =  array(
		'primary' => esc_html__( 'Primary Menu', 'wpgurus-vuejs' ),
    'footer' => esc_html__( 'Footer Menu', 'wpgurus-vuejs' )
	);
  if(is_multisite()){
    $menus['network'] = esc_html__( 'Network Menu', 'wpgurus-vuejs' );
  }
	register_nav_menus($menus);
  //add featured image support.
  add_theme_support( 'post-thumbnails' ); 
}
/**
 * Enables the page templates.
 */
function wpcodex_add_template_support_for_pages() {
	add_post_type_support( 'page', 'page-attributes' );
}

/**
*
*
* @param int    $item_id  Menu item ID.
* @param object $item     Menu item data object.
* @param int    $depth    Depth of menu item. Used for padding.
* @param array  $args     Menu item args.
* @param int    $id       Nav menu ID.
*/
function menu_check_to_exit_router($item_id, $item, $depth, $args ){
	if (!$item_id && isset($item->ID)) {
		$item_id = $item->ID;
	}
	$isChecked = get_post_meta( $item_id, '_menu_item_exit_vuejs_router', true );
	if($isChecked && 'exit' ==$isChecked){
		$isChecked = 'checked="checked"';
	}else{
		$isChecked = '';
	}
	?>
	<p class="field-vuejs-router description description-wide">
		<span>
			Menu links are by default loaded in the VueJS Router, the page content is loaded using the WordPress REST api. Selecting the following checkbox allows you force the browser to reload the page.
		</span>
		<label for="menu_item_exit_vuejs_router[<?= $item_id?>]">
			<input type="checkbox" name="menu_item_exit_vuejs_router[<?= $item_id?>]" value="exit" <?= $isChecked ?>/>
		Reload browser on request</label>
	</p>
<?php
}
function menu_router_nav_menu_manage_columns($columns){
	return $columns + array( 'vuejs-router' => __( 'VueJS Router Link', 'wpgurus-vuejs' ) );
}
/**
 * Saving post action.
 *
 * Saving router link type.
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
function menu_router_save_post_action( $post_id, $post ) {
	$name = 'menu_item_exit_vuejs_router';
	if ( isset( $_POST[ $name ][ $post_id ] ) && !empty( $_POST[ $name ][ $post_id ] ) ) {
		if ( $post->{"_$name"} != $_POST[ $name ][ $post_id ] ) {
			update_post_meta( $post_id, "_$name", esc_sql( $_POST[ $name ][ $post_id ] ) );
		}
	}
}
