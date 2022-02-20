<template id="language-menu">
  <nav class="navigation-menu navigation-languages">
    <span class="current-lang">
      <span class="title" v-html="this.current"></span><span class="raquo">&rsaquo;</span>
    </span>
    <ul class="language-menu">
      <li v-for="(item,lang) in languages" v-if="lang != current">
        <a v-bind:href="item.url" v-html="lang"></a>
      </li>
    </ul>
  </nav>
</template>
