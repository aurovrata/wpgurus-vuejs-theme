<?php
//vue js templates
?>
<template id="<?= $template_id?>-logo">
  <div id="logo">
    <router-link v-bind:to="this.logo.link"><img v-bind:src="this.logo.src"/></router-link>
  </div>
</template>
