<?php
/**
* main index. page compatible with https://wordpress.org/plugins/404page/
*/
get_header();
?>
  <!-- VueJs templates -->
  <template id="body-content">
    <main>
      <header>
        <logo-image v-bind:logo="this.data.logo"></logo-image>
        <network-menu  v-if="hasMenu('network')" v-bind:menu="this.data.menus.network"></network-menu>
        <language-menu v-if="hasMenu('languages')" v-bind:current="this.data.lang" v-bind:languages="this.data.menus.languages"></language-menu>
        <primary-menu  v-if="hasMenu('primary')" v-bind:menu="this.data.menus.primary"></primary-menu>
      </header>
      <content-page>
        <template slot="content">
            <?php get_template_part( 'templates/index', 'page' );?>
        </template>
      </content-page>
      <footer>
        <footer-menu  v-if="hasMenu('footer')" v-bind:menu="this.data.menus.footer"></footer-menu>
      </footer>
    </main>
  </template>
  <!-- VueJs templates -->
  <template id="content-page">
    <div class="vue-content">
      <slot name="content"></slot>
    </div>
  </template>
  <?php
  /*load templates for vue components*/
  if(apply_filters('wpgurus_theme_multilingual', false) || defined ("POLYLANG_VERSION")){
    get_template_part('templates/index', 'languages');
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

  ?>
  <!-- end VueJs templates -->
  <?php get_footer();?>
