<?php
/**
* main index.
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
          <div v-if="isPage">
            <?php get_template_part( 'templates/index', 'page' );?>
          </div>
          <div v-else-if="isArchive('post')">
            <?php get_template_part( 'templates/index', 'page' );?>
          </div>
          <div v-else-if="isSingle('post')">
            <?php get_template_part( 'templates/index', 'page' );?>
          </div>
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
    include_vue_template('main', 'languages');
  }
  include_vue_template('network', 'menu');
  include_vue_template('footer', 'menu');
  include_vue_template('primary', 'menu');
  get_template_part('templates/index', 'logo');

  ?>
  <!-- end VueJs templates -->
  <?php get_footer();?>
