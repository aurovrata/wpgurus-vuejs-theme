<?php
//vue js templates
?>
<template id="<?= $template_id?>-logo">
  <div id="logo">
    <router-link v-bind:to="logo.link" v-on:click="restRequest(logo)"><img v-bind:src="logo.src"/></router-link>
  </div>
</template>
