<?php
/**
* main index. page compatible with https://wordpress.org/plugins/404page/
*/
get_header();
?>
<!-- VueJs templates -->
<template id="body-content">
  <main v-bind:class="pageClass()">
    <header>
      <logo-image v-bind:logo="this.data.logo"></logo-image>

    <?php if ( has_nav_menu( 'network' ) ) :?>
      <network-menu v-bind:menu="this.data.menus.network"></network-menu>
    <?php endif;?>

    <?php if ( has_nav_menu( 'languages' ) ) :?>
      <language-menu v-if="hasMenu('languages')" v-bind:current="this.data.lang" v-bind:languages="this.data.menus.languages"></language-menu>
    <?php endif;?>

    <?php if ( has_nav_menu( 'primary' ) ) :?>
      <primary-menu  v-if="hasMenu('primary')" v-bind:menu="this.data.menus.primary"></primary-menu>
    <?php endif;?>

    </header>
      <section class="page-content page-404">
          <component v-bind:is="currentComponent" v-bind:rootdata="this.data">
            <template slot="content">
              <div class="single-page">
                <?php get_template_part( 'templates/index', 'page' );?>
              </div>
            </template>
          </component>
      </section>
      <footer>
      <?php if ( has_nav_menu( 'footer' ) ) :?>
        <footer-menu  v-if="hasMenu('footer')" v-bind:menu="this.data.menus.footer"></footer-menu>
      <?php endif;?>
      </footer>

    </main>
  </template>
  <!-- VueJs templates: the static-template is using slots
  and therefore the page content templates have direct access
  to the parent component data, ie this.data.posts -->
  <template id="static-template">
    <div class="vue-content">
      <slot name="content"></slot>
    </div>
  </template>
  <?php
  /*load templates for vue components*/
  if(apply_filters('wpgurus_theme_multilingual', false) || defined ("POLYLANG_VERSION")){
    get_template_part( 'templates/index', 'languages' );
  }

  if ( has_nav_menu( 'network' ) ){
    set_query_var('template_id', 'network');
    get_template_part('templates/index', 'menu');
  }
  if ( has_nav_menu( 'footer' ) ){
    set_query_var('template_id', 'footer');
    get_template_part('templates/index', 'menu');
  }
  if ( has_nav_menu( 'primary' ) ){
    set_query_var('template_id', 'primary');
    get_template_part('templates/index', 'menu');
  }
  get_template_part('templates/index', 'logo');
  /** @since 1.2.0 allow plugins to print templates */
  do_action('wpgurus_theme_print_vuejs_templates');
  ?>
  <!-- end VueJs templates -->
  <?php get_footer();?>
