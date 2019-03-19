<?php
//vue js templates
?>
<template id="single-page" v-if="data.posts.length>0" v-for="(post, index) in data.posts">
  <article  v-bind:id="articleId(post)">
    <figure>
      <img v-if="isSingle()" v-bind:src="post.featured_urls.medium[0]" v-bind:alt="post.title.rendered" />
      <img v-else v-bind:src="post.featured_urls.thumbnail[0]" v-bind:alt="post.title.rendered" />
      <figcaption v-if="isSingle()">{{ post.title.rendered }}</figcaption>
    </figure>
    <h1 v-html="post.title.rendered"></h1>
    <div v-if="isSingle()" v-html="post.content.rendered"></div>
    <div v-else v-html="post.excerpt.rendered"></div>
  </article>
</template>
