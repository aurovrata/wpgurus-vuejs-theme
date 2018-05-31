/*
  The following constants are used in this script and are loaded from the php files include/load_menu.php or include/load_page.php in the theme folder,
   - InitialPage : this is the page json object on the initial request to the server.
   - InitialMenu : this is the menus regsitered by the theme.
   - WPrestPath : an array of paths for the various end-points available.  NB: the home page (front page) request path is included as WPrestPath.home.
   - SitePaths : some site url paths, used to distinguish between localhost and domain based isntallations.
*/

let isHomepage = InitialPage.homepage;
let restRequest = WPrestPath.current; //track the rest requst path when a menu is clicked.
let postType = InitialPage.type;
let isArchive = InitialPage.archive;
let isSingle = InitialPage.single;
let isTaxonomy = false;
//strip trailing slash.
const initialLink = SitePaths.root.replace(/\/$/, "") + SitePaths.currentRoute;//.replace(/\/$/, "");
console.log('initialLink:'+initialLink);
console.log('initialRest:'+restRequest);
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
          console.log('rest request: '+item._links.self);
          restRequest=item._links.self;
          postType = item.object;
          isTaxonomy=false;
          isSingle = false;
          isArchive = false;
          switch(item.type){
            case 'post_type':
              isSingle = true;
              break;
            case 'post_type_archive':
              isArchive = true;
              break;
            case 'taxonomy':
              isTaxonomy=true;
              break;
          }
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

//setup menu components and routes.
const routes = [];//VueCustomRoutes.routes;

const componentData = {
  'status':'',
  'menus':{
    'primary': initMenu('primary'),
    'footer': initMenu('footer'),
    'network': initMenu('network'),
    'languages':initMenu('languages')
  },
  'permalink':initialLink,
  'logo':{
    'src':SitePaths.logo,
    'link':rootPath,
    '_links':{'self':WPrestPath.frontpage},
    'object':InitialPage.homelink.object,
    'type':InitialPage.homelink.type
  },
  'posts':[],
  'homepage':false,
  'rest':restRequest,
  'type':postType,
  'single':isSingle,
  'archive':isArchive,
  'istax':isTaxonomy,
  'custom':{}
}
const compLogo = {
  template:'#logo-image',
  props:['logo'],
  methods:{
    restRequest: function(item){
      postType = item.object;
      isSingle = false;
      isArchive = false;
      isTaxonomy = false;
      switch(item.type){
        case 'post_type':
          isSingle = true;
          break;
        case 'post_type_archive':
          isArchive = true;
          break;
      }
      restRequest=item._links.self;
      console.log('logo request:'+item._links.self);
    }
  }
};
const pageComponent = function(){
  return Vue.component('body-content',{
    template: '#body-content',
    components:{
      'primary-menu': vjsMenu('primary'),
      'footer-menu': vjsMenu('footer'),
      'network-menu': vjsMenu('network'),
      'language-menu': vjsLang(),
      'logo-image': compLogo,
      'content-page':{
        template:'#content-page',
      }
    },
    data: function(){
      return {'data':componentData};
    },
    computed:{isPage: function(){
      if('page'== this.data.type){
        console.log('found page');
        return true;
      }else return false;
    }},
    methods:{
      isSingle: function(pType){
        if (this.data.type==pType && this.data.single){
          console.log('found '+pType+' single');
          return true;
        }else return false;
      },
      isArchive: function(pType){
        if(this.data.type==pType && this.data.archive){
          console.log('found '+pType+' archive');
          return true;
        }else return false;
      },
      hasMenu: function(type){
        let menu = true;
        if('undefined' == typeof this.data.menus[type]){
          menu = false;
        }
        return menu;
      },
      restRequest: function(path, ptype=null, otype=null){
        console.log('restRequest: '+path);
        if('undefined' !== typeof ptype) postType = ptype;
        else postType = '';
        isArchive = false;
        isSingle = false;
        isTaxonomy =false;
        if('undefined' !== typeof otype){
          switch(otype){
            case 'single':
              isSingle = true;
              break;
            case 'archive':
              isArchive = true;
              break;
            case 'taxonomy':
              isTaxonomy = true;
              break;
          }
        }
        if(path.length>0)restRequest=path;
      },
      articleId: function(post){
        return 'post-'+post.id;
      },
      childLink: function(slug){
        return SitePaths.root.replace(/\/$/, "") + this.$route.path + slug;
      }
    },
    created: function(){
      let path = SitePaths.root.replace(/\/$/, "") + this.$route.path;
      let home = SitePaths.home;
      //get rest data.
      let arrPromises = [this.$http.get(restRequest)];
      let rIdx =0;
      if('undefined' != typeof InitialMenu['languages'] && 'undefined' != typeof WPrestPath['languages'] ){
        rIdx++;
        let getpath = WPrestPath.languages;
        if(path !== home){ //inner page request/
          let slugs = this.$route.path.split('/');
          let pageSlug = slugs[slugs.length-1];
          if(0==pageSlug.length) pageSlug = slugs[slugs.length-2];
          getpath = WPrestPath.languages+pageSlug;
        }
        arrPromises[rIdx]=this.$http.get(getpath);
      }
      //extra custom request.
      if('undefined' != typeof VueCustomRoutes[this.$route.path]){
        for(let key in VueCustomRoutes[this.$route.path]){
          rIdx++;
          let path = VueCustomRoutes[this.$route.path][key];
          arrPromises[rIdx] = this.$http.get(path)
        }
      }
      Promise.all(arrPromises).then( (data) => {
        rIdx = 0;
        if(data[rIdx].body instanceof Array){
          componentData.posts = data[rIdx].body;
        }else{
          componentData.posts = [data[rIdx].body];
        }
        componentData.single = isSingle; //set in restRequest();
        componentData.archive = isArchive;
        componentData.type = postType;
        componentData.homepage = (path === home);
        //language menus for new page.
        if('undefined' != typeof InitialMenu['languages'] && 'undefined' != typeof WPrestPath['languages'] ){
          rIdx++;
          componentData.menus.languages = data[rIdx].body;
        }
        //extra custom request.
        componentData.custom={};
        if('undefined' != typeof VueCustomRoutes[this.$route.path]){
          for(let key in VueCustomRoutes[this.$route.path]){
            rIdx++;
            componentData.custom[key] = data[rIdx].body;
            console.log('added custom data: '+key);
            console.log(componentData.custom[key]);
          }
        }
        console.log('vue page component created, data:');
        console.log(componentData);
        this.data = componentData;
      }, (data) => {
        console.log('ERROR,failed to get api data');
        console.log(data);
        this.status = { error: "failed to load the page"};
      });
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
