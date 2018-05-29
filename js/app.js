/*
  The following constants are used in this script and are loaded from the php files include/load_menu.php or include/load_page.php in the theme folder,
   - InitialPage : this is the page json object on the initial request to the server.
   - InitialMenu : this is the menus regsitered by the theme.
   - WPrestPath : an array of paths for the various end-points available.  NB: the home page (front page) request path is included as WPrestPath.home.
   - SitePaths : some site url paths, used to distinguish between localhost and domain based isntallations.
*/

let isHomepage = InitialPage.homepage;
let restRequest = WPrestPath.root; //track the rest requst path when a menu is clicked.
//strip trailing slash.
const initialLink = SitePaths.root.replace(/\/$/, "") + SitePaths.currentRoute;//.replace(/\/$/, "");
console.log('initialLink:'+initialLink);
//declare an event bus (https://alligator.io/vuejs/global-event-bus/).
const eventQ = new Vue();
// main content component.

const wpArchive = function(templateId){
  return{
    template: templateId,
    props:['post'],
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
      restRequest: function(item){
        if(item.isvjslink){
          restRequest=item._links.self;
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
const logo = {
  template:'#main-logo',
  props:['logo'],
  methods:{
    restRequest: function(path){
      if(path.length>0){
        restRequest=path;
      }
    }
  }
}

//setup menu components and routes.
const routes = [];//VueCustomRoutes.routes;

//const allMenu = ;

let postType = '';
let postCount = 0;
if(InitialPage.posts.length > 0 && 'undefined' !== typeof InitialPage.posts[0].type){
  postType = InitialPage.posts[0].type;
  postCount = InitialPage.posts.length;
}else console.log('wpGurus: WARNING, type is undefined in the initial page');

const componentData = InitialPage;
componentData['status'] = '';
componentData['menus'] = {
  primary: initMenu('primary'),
  footer: initMenu('footer'),
  network: initMenu('network'),
  languages:initMenu('languages')
};
componentData ['permalink']= initialLink;
componentData['logo']= {
  src:SitePaths.logo,
  link:rootPath,
  _links:{self:WPrestPath.homepage}
};
componentData['rest'] = restRequest;
componentData['type'] = postType;
componentData['count'] = postCount;
//
// //additional data.
// if('undefined' != typeof VueCustomRoutes[SitePaths.currentRoute]){
//   for(let key in VueCustomRoutes[SitePaths.currentRoute]){
//     componentData[key] = [];
//     if('undefined' != typeof InitialPage[key]){
//       componentData[key] =  InitialPage[key];
//     }
//   }
// }

const pageComponent = function(){
  const defComponents = {
    'primary-menu': vjsMenu('primary'),
    'footer-menu': vjsMenu('footer'),
    'network-menu': vjsMenu('network'),
    'language-menu': vjsLang(),
    'logo-image': logo,
    'content-page':{
      template:'#content-page',
    }
  }

  /*
  props:['type','count'],
  computed:{isPage: function(){console.log('isPage: '+this.type);return 'page'== this.type;}},
  methods:{
    isSingle: function(pType){console.log('isSingle: '+pType);return this.type==pType},
    isArchive: function(pType){console.log('isArchive: '+pType+this.count);return  (this.type==pType && this.count>1)},
  }
  */
  return Vue.component('body-content',{
    template: '#body-content',
    components:defComponents,
    data: function(){
      console.log('data');
      console.log(componentData);
      // if(InitialPage.data.length > 0 && 'undefined' !== typeof InitialPage.data[0].type){
      //   componentData['type'] = InitialPage.data[0].type;
      // }else console.log('wpGurus: WARNING, type is undefined in the initial page');
      // componentData['posts']=InitialPage.data;
      return componentData;
    },
    computed:{isPage: function(){console.log('isPage: '+this.type);return 'page'== this.type;}},
    methods:{
      isSingle: function(pType){console.log('isSingle: '+pType);return this.type==pType},
      isArchive: function(pType){console.log('isArchive: '+pType+this.count);return  (this.type==pType && this.count>1)},
      hasMenu: function(type){
        let menu = true;
        if('undefined' == typeof this.menus[type]){
          menu = false;
        }
        return menu;
      },
      restRequest: function(path){
        if(path.length>0){
          restRequest=path;
        }
      },
      articleId: function(post){
        return 'post-'+post.id;
      },
      childLink: function(slug){
        return SitePaths.root.replace(/\/$/, "") + this.$route.path + slug;
      }
    },
    created: function(){
      console.log('creating...');
        //stretchFullWidthRows(); //SO PageBuilder.
        //this.posts = [{title:{rendered:''},content:{rendered:''}}];
        let path = SitePaths.root.replace(/\/$/, "") + this.$route.path.replace(/\/$/, "");
        //console.log(path);
        //strip trailing slash.
        //path = path.replace(/\/$/, "");
        if(InitialPage && InitialPage.posts.length>0 && this.$route.path === SitePaths.currentRoute){
          //console.log('setup initial data');
          for(let key in componentData){
            this[key] = componentData[key];
          }
          //this.posts = componentData.posts;
        }else{
          this.$http.get(restRequest,{}).then( (data) => {
            if(data.body instanceof Array){
              this.posts = data.body;
            }else{
              this.posts = [data.body];
            }
            this.homepage = (path === SitePaths.home);
            if('undefined' != typeof InitialMenu['languages'] && 'undefined' != typeof WPrestPath['languages'] ){
              if(data.body instanceof Array){
                let home = SitePaths.home.replace(/\/$/, "");
                let getpath = WPrestPath.languages;
                if(path !== home){ //inner page request/
                  let slugs = this.$route.path.split('/');
                  let pageSlug = slugs[slugs.length-1];
                  if(0==pageSlug.length) pageSlug = slugs[slugs.length-2];
                  getpath = WPrestPath.languages+pageSlug;
                }
                this.$http.get(getpath).then( (data) => {
                  this.menus.languages = data.body;
                }, (data) => {
                  this.status = { error: "failed to load the languages menu"};
                });
              }else{
                this.menus.languages = data.body.language_menu;
              }
            }
          }, (data) => {
            this.status = { error: "failed to load the page"};
          });
          //end get.
        }
    }
  });
}


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
console.log(VueCustomRoutes.routes);
//setup routes and components.
const bodyComponent = pageComponent(VueCustomRoutes.routes);
routes[routes.length]={
  path:rootPath,
  component: bodyComponent
}
getRoutes('primary', bodyComponent);
getRoutes('footer', bodyComponent);
getRoutes('network', bodyComponent);
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
