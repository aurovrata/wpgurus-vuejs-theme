<?php
//menu template.
 ?>
 <template id="vue-menu">
   <nav>
     <ul v-bind:class="menuClass()">
           <router-link v-for="item in menu" v-bind:key="linKey(item)" tag="li" v-bind:to="relativeUrl(item)" exact>
             <a v-html="item.title"></a>
          </router-link>
     </ul>
   </nav>
 </template>
