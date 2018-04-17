<?php /*main theme file*/ ?>
<!DOCTYPE html>
<html>
<head>
  <?php wp_head();?>
</head>
<body <?= body_class()?>>
  <div id="main-vue">
    <header>
      <logo-image></logo-image>
      <primary-menu></primary-menu>
    </header>
    <router-view v-bind:key="$route.fullPath"></router-view>
    <footer>
      <footer-menu></footer-menu>
    </footer>
  </div>
  <!-- VueJs templates -->
  <?php
  /*load templates for vue components*/
  get_template_part( 'templates/content', 'primary-menu' );
  get_template_part( 'templates/content', 'footer-menu' );
  get_template_part( 'templates/content', 'logo' );
  get_template_part( 'templates/content', 'page' );

  ?>
  <!-- end VueJs templates -->
  <?php wp_footer();?>
</body>
</html>
