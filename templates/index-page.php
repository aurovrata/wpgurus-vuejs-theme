<?php
//vue js templates
?>
<template id="content-page">
    <main v-bind:id="articleId()">
      <h1 v-if="homepage" v-thml="page.title.rendered"></h1>
      <div v-html="page.content.rendered"></div>
    </main>
</template>
