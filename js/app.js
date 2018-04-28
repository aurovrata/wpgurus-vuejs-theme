/*
  The following constants are used in this script and are loaded from the php files include/load_menu.php or include/load_page.php in the theme folder,
   - InitialPage : this is the page json object on the initial request to the server.
   - InitialMenu : this is the menus regsitered by the theme.
   - WPrestPath : an array of paths for the various end-points available.  NB: the home page (front page) request path is included as WPrestPath.home.
   - SitePaths : some site url paths, used to distinguish between localhost and domain based isntallations.
*/

let isHomepage = InitialPage.homepage;
//strip trailing slash.
const initialLink = InitialPage.data[0].link.replace(/\/$/, "");
//declare an event bus (https://alligator.io/vuejs/global-event-bus/).
const eventQ = new Vue();
// main content component.
const wpPage = {
  template:'#content-page',
  props:['page','homepage'],
  methods: {
    articleId: function(){
      return 'post-'+this.page.id;
    }
  }
}
let rootPath = '/';
if(SitePaths.home.length > SitePaths.root.length){
  rootPath = SitePaths.home.replace(SitePaths.root,'/');
}

//menu.
const vjsMenu = function(type){
  if('undefined' == typeof type) type = 'primary';
  if('undefined' == typeof InitialMenu[type]){
    return null;
  }

  return {
    template:'#'+type+'-menu',
    props:['menu'],
    methods:{
      queueId: function(item){
        if(item.isvjslink){
          eventQ.$emit('linkpid', item.object_id);
        }
      },
      relativeUrl: function(item){
        return item.url.replace(SitePaths.root, '/');
      },
      linKey: function(item){
        return item.object+'s/'+item.object_id;
      },
      itemClass: function(item){
        let classes = 'menu-item';
        if(item.url === document.location){
          classes += ' menu-selected';
        }
        return classes;
      }
    }
  } //end component.
}
//get initial menu data.
const initMenu = function(type){
  if('undefined' == typeof InitialMenu[type]){
    return null;
  }
  return InitialMenu[type]
}

const vjsLang = function(){
  if('undefined' == typeof InitialMenu['languages']){
    return null;
  }
  return {
    template:'#language-menu',
    props:['languages', 'current']
  } //end component.
}
const logo = { template:'#main-logo', props:['logo']}

//setup menu components and routes.
const routes = [];
//setup menu routes.
const getRoutes = function(menu, vuec){
  if('undefined' == typeof InitialMenu[menu]){
    return;
  }
  if(InitialMenu.enabled && InitialMenu[menu].items.length > 0){
    for(let idx = 0; idx< InitialMenu[menu].items.length; idx++){
      if('undefined' !== typeof InitialMenu[menu].items[idx].isvjslink && InitialMenu[menu].items[idx].isvjslink){
        routes[routes.length] = {
          path: InitialMenu[menu].items[idx].url.replace(SitePaths.root, '/'),
          component: vuec
        };
      }
    }
  }
}
const allMenu = {
  primary: initMenu('primary'),
  footer: initMenu('footer'),
  network: initMenu('network'),
  languages:initMenu('languages')
};
const mainSection = Vue.component('main-section',{
  template: '#main-section',
  components:{
    'primary-menu': vjsMenu('primary'),
    'footer-menu': vjsMenu('footer'),
    'network-menu': vjsMenu('network'),
    'language-menu': vjsLang(),
    'logo-image': logo,
    'content-page': wpPage
  },
  data: function(){
    return {
      page: InitialPage.data[0],
      homepage: isHomepage,
      lang: InitialPage.lang,
      status:'',
      menus: allMenu,
      permalink: initialLink,
      logo:{
        src:SitePaths.logo,
        link:rootPath
      }
    };
  },
  methods:{
    hasMenu: function(type){
      if('undefined' == typeof this.menus[type]){
        return false;
      }
      return (null != this.menus[type]);
    }
  },
  created: function(){
    //stretchFullWidthRows(); //SO PageBuilder.
    this.page = {title:{rendered:''},content:{rendered:''}};
    let path = SitePaths.root + this.$route.path.substr(1);
    //strip trailing slash.
    path = path.replace(/\/$/, "");
    if(InitialPage && InitialPage.data.length>0 && path === initialLink){
      this.page = InitialPage.data[0];
      this.homepage = isHomepage;
    }else{
      let data ={};
      let restPath = WPrestPath.home;
      let home = SitePaths.home.replace(/\/$/, "");
      if(path !== home){ //inner page request/
        let slugs = this.$route.path.split('/');
        let pageSlug = slugs[slugs.length-1];
        if(0==pageSlug.length) pageSlug = slugs[slugs.length-2];
        restPath = WPrestPath.root+'pages/';
        data = {params:{slug: pageSlug}};
      }else{ //assuming homepage.
        restPath = WPrestPath.frontpage;
      }
      this.$http.get(restPath,data).then( (data) => {
        if(data.body instanceof Array){
          this.page = data.body[0];
        }else{
          this.page = data.body;
        }
        this.homepage = (path === SitePaths.home);
      }, (data) => {
        this.status = { error: "failed to load the page"};
      });
      //end get.
    }
    if('undefined' != typeof InitialMenu['languages'] && 'undefined' != typeof WPrestPath['languages'] ){
      eventQ.$on('linkpid', (pid) =>{
        let getpath = WPrestPath.languages+pid;
        this.$http.get(getpath).then( (data) => {
          this.menus.languages = data.body;
        }, (data) => {
          this.status = { error: "failed to load the languages menu"};
        });
      });
    }
  }
});
//setup root path.
routes[routes.length]={
  path:rootPath,
  component: mainSection
}
getRoutes('primary', mainSection);
getRoutes('footer', mainSection);
getRoutes('network', mainSection);
console.log('page routes');
console.log(routes);
const router  = new VueRouter({
  routes: routes,
  mode:'history'
});
// const setRoutesComponent = function(path){
//   router.push
// }
const mv = new Vue({
  router: router
}).$mount('#main-vue');
