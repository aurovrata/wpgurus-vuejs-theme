<?php
//menu template.
 ?>
 <template id="std-menu">
   <nav>
     <ul v-bind:class="menuClass()">
           <li v-for="item in menu" v-bind:class="itemClass(item)">
             <a v-html="item.title" v-bind:href="item.url"></a>
          </li>
     </ul>
   </nav>
 </template>
