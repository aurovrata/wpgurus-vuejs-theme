<?php
//vue js templates
?>
<article v-if="data.posts.length>0" v-bind:id="articleId(data.posts[0])">
      <div v-html="data.posts[0].content.rendered"></div>
</article>
