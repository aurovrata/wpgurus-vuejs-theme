<?php
//vue js templates
?>
<template id="<?= $template_id?>-logo">
  <div id="logo">
    <router-link v-bind:to="logo.link" v-on:click="restRequest(logo._links.self)"><img v-bind:src="this.logo.src"/></router-link>
  </div>
</template>
