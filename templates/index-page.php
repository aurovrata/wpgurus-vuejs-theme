<?php
//vue js templates
?>
<template id="content-page">
  <section class="bg-white mt-px10 pb-px200">
    <slot v-if="isPage" name="page"></slot>
    <slot v-else name="default"></slot>
  </section>
</template>
