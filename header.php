<?php
/**
* header.php
*/
 ?>
 <!DOCTYPE html>
 <html <?php language_attributes(); ?>>
 <head>
   <meta charset="<?php bloginfo( 'charset' ); ?>" />
   <?php do_action( 'wpgurus_head_meta' ); ?>
   <?php wp_head();?>
 </head>
 <body <?= body_class()?>>
   <div id="main-vue">
     <router-view v-bind:key="$route.fullPath"></router-view>
   </div>
