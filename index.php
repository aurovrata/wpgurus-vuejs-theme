<?php
/**
* main index.
*/
get_header();
?>
  <!-- VueJs templates -->
  <template id="main-section">
    <main>
      <header>
        <logo-image v-bind:logo="this.logo"></logo-image>
        <network-menu  v-if="hasMenu('network')" v-bind:menu="this.menus.network"></network-menu>
        <language-menu v-if="hasMenu('languages')" v-bind:current="this.lang" v-bind:languages="this.menus.languages"></language-menu>
        <primary-menu  v-if="hasMenu('primary')" v-bind:menu="this.menus.primary"></primary-menu>
      </header>
      <content-page v-bind:type="this.posts[0].type" v-bind:count="this.posts.length">
        <!-- page -->
        <template slot="page">
          <main v-bind:id="articleId(this.posts[0])">
            <h1 v-if="homepage" v-thml="posts[0].title.rendered"></h1>
            <div v-html="posts[0].content.rendered"></div>
          </main>
        </template>
        <!-- posts -->
        <template slot="default">
          <article v-for="{post in this.posts}" v-bind:id="articleId(post)">
            <h1 v-thml="post.title.rendered"></h1>
            <div v-html="post.content.rendered"></div>
          </article>
        </template>
      <footer>
        <footer-menu  v-if="hasMenu('footer')" v-bind:menu="this.menus.footer"></footer-menu>
      </footer>
    </main>
  </template>
  <?php
  /*load templates for vue components*/
  if(apply_filters('wpgurus_theme_multilingual', false) || defined ("POLYLANG_VERSION")){
    include_vue_template('main', 'languages');
  }
  include_vue_template('network', 'menu');
  include_vue_template('footer', 'menu');
  include_vue_template('primary', 'menu');
  include_vue_template('main', 'logo');
  include_vue_template('content', 'page');

  ?>
  <!-- end VueJs templates -->
  <?php get_footer();?>
