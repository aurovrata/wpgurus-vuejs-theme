<?php
//vue js templates
?>
<template id="content-page">
    <main v-bind:id="articleId()" class="bg-white mt-px10 pb-px200">
      <h1 v-if="homepage" v-thml="page.title.rendered"></h1>
      <div v-html="page.content.rendered" data-section="image_text" class="row"></div>
    </main>
</template>
