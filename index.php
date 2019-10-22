<?php
/**
* main index.
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
      <!-- main page content, keep alive element will cache page content -->
      <section class="page-content">
        <?php if(apply_filters('wpgurus_theme_page_content_keep_alive', true)):?>
        <keep-alive>
        <?php endif;?>
          <component v-bind:is="currentComponent" v-bind:rootdata="this.data">
            <template slot="content">
              <div v-if="isPage()" class="single-page">
                <?php get_template_part( 'templates/index', 'page' );?>
              </div>
              <div v-else-if="isArchive('post')"  class="archive-post">
                <?php get_template_part( 'templates/index', 'page' );?>
              </div>
              <div v-else-if="isSingle('post')" class="single-post">
                <?php get_template_part( 'templates/index', 'page' );?>
              </div>
            </template>
          </component>
        <?php if(apply_filters('wpgurus_theme_page_content_keep_alive', true)):?>
        </keep-alive>
        <?php endif;?>
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
