/*
  The following constants are used in this script and are loaded from the php files include/load_menu.php or include/load_page.php in the theme folder,
   - InitialPage : this is the page json object on the initial request to the server.
   - InitialMenu : this is the menus regsitered by the theme.
   - WPrestPath : an array of paths for the various end-points available.  NB: the home page (front page) request path is included as WPrestPath.home.
   - SitePaths : some site url paths, used to distinguish between localhost and domain based isntallations.
*/

//strip trailing slash.
const initialLink = SitePaths.root.replace(/\/$/, "") + SitePaths.currentRoute;//.replace(/\/$/, "");
// if(wpGurusVueJSlocal.debug) console.log('initialLink:'+initialLink);
// if(wpGurusVueJSlocal.debug) console.log('initialRest:'+restRequest);
//declare an event bus (https://alligator.io/vuejs/global-event-bus/).
const eventQ = new Vue();
// main content component.

const wpArchive = function(templateId){
  return{
    template: templateId,
    props:['post'],
  }
}
let initError = false;
let rootPath = '/';
if(SitePaths.home.length > SitePaths.root.length){
  rootPath = SitePaths.home.replace(SitePaths.root,'/');
}

//menu.
/*
menu component vuejs methods functions.
*/
var vueJSmenuMethodsModule = (function (vmmm) {
  vmmm.relativeUrl = function(item){
    return item.url.replace(SitePaths.root, '/');
  }
  vmmm.linKey = function(item){
    return item.object+'s/'+item.object_id;
  }
  vmmm.itemClass = function(item){
    let classes = 'menu-item';
    if(item.url === document.location){
      classes += ' menu-selected';
    }
    return classes;
  }
  return vmmm
 }(vueJSmenuMethodsModule || {}));
/*
menu component vuejs computed functions.
*/
var vueJSmenuComputedModule = (function (vmcm) { return vmcm }(vueJSmenuComputedModule || {}));

const vjsMenu = function(type){
  if('undefined' == typeof type) type = 'primary';
  if('undefined' == typeof InitialMenu[type]){
    return null;
  }
  return {
    template:'#'+type+'-menu',
    props:['menu'],
    methods: vueJSmenuMethodsModule,
    computed: vueJSmenuComputedModule
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
//custom reactive data.
var customReactiveData = (function (crd){
  //add something
  return crd;
}(customReactiveData || {}));

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
  'rest':WPrestPath.current,
  'type':InitialPage.type,
  'single':InitialPage.single,
  'archive':InitialPage.archive,
  'istax':false,
  'taxonomy':'',
  'term':'',
  'lang':'en',
  'custom':{},
  'form':customReactiveData,
  'styles':''
}
if('undefined' != typeof InitialMenu['languages']){
  componentData.lang = InitialPage.lang;
}
/** @since 1.1.0 default handling of 404 pages, set content to simple message.
* If a plugin is handling 404 pages, this is take care on ocmponent update  cycle.
*/
if(InitialPage.is404) componentData.posts[0]=InitialPage.content404;

/** @since 1.1.3  display an error is menus are not enabled*/
if(wpGurusVueJSlocal.debug && !InitialMenu.enabled){
  console.log('--------- SETUP ERROR ---------');
  console.log(InitialMenu.error);
  componentData.posts[0]={
    'title':{'rendered':'Setup Error'},
    'content':{'rendered':'<p>'+InitialMenu.error+'</p>'}
  }
  initError = true;
}
/*
logo component vuejs methods functions.
*/
var vueJSlogoMethodsModule = (function (vlmm) { return vlmm }(vueJSlogoMethodsModule || {}));
/*
logo component vuejs computed functions.
*/
var vueJSlogoComputedModule = (function (vlcm) { return vlcm}(vueJSlogoComputedModule || {}));

const compLogo = {
  template:'#logo-image',
  props:['logo'],
  methods: vueJSlogoMethodsModule,
  computed: vueJSlogoComputedModule
};

//methods functions.
var vueJSmethodsModule = (function (vmm) {
  // add capabilities...
  vmm.isPage = function(path){
    if('page'== this.data.type && path == this.$route.path) return true;
    else if('page'== this.data.type) return true;
    else return false;
  }
  vmm.isSingle = function(pType){
    console.log('isSingle');
    console.log(pType);
    console.log(this.data.type);
    if(this.data.type==pType && this.data.single) return true;
    else if('undefined' == typeof pType && this.data.single) return true;
    else  return false;
  }
  vmm.isArchive = function(pType){
    if(this.data.type==pType && this.data.archive) return true;
    else if('undefined' == typeof pType && this.data.archive) return true;
    else return false;
  }
  vmm.isTaxonomy = function(tax){
    if(this.data.taxonomy==tax && this.data.istax) return true;
    else if('undefined' == typeof tax && this.data.istax) return true;
    else return false;
  }
  vmm.hasMenu = function(type){
    let menu = true;
    if('undefined' == typeof this.data.menus[type]){
      menu = false;
    }
    return menu;
  }
  vmm.articleId = function(post){
    return 'post-'+post.id;
  }
  vmm.childLink = function(slug){
    return SitePaths.root.replace(/\/$/, "") + this.$route.path + slug;
  }
  vmm.pageClass = function(){
    let pclass = '';
    switch(true){
      case this.data.istax:
        pclass= 'taxonomy-'+this.data.taxonomy+' taxonomy';
        break
      default:
        pclass=this.data.type;
        break;
    }
    switch(true){
      case this.data.archive:
        pclass+='-archive';
        break;
      case this.data.single:
        pclass+='-single';
        break;
    }
    return pclass;
  }
	return vmm;
}(vueJSmethodsModule || {}));
//async component modules.
var vueJSasyncMethodsModule = (function (vamm) { return vamm }(vueJSasyncMethodsModule || {}));
var vueJSasyncComputedModule = (function (vacm) { return vacm}(vueJSasyncComputedModule || {}));

const pageComponent = function(pageTemplate){
  //the default template from static html.
  let pc = 'async-template';
  if('undefined' == typeof pageTemplate){
    pageTemplate = '<em>No Data</em>';
    pc = 'static-template';
  }
  return Vue.component('body-content', {
    template: '#body-content',
    components:{
      'primary-menu': vjsMenu('primary'),
      'footer-menu': vjsMenu('footer'),
      'network-menu': vjsMenu('network'),
      'language-menu': vjsLang(),
      'logo-image': compLogo,
      'static-template':{
        template:'#static-template',
        computed:vueJScomputedModule,
        methods: vueJSmethodsModule
      },
      'async-template':{
        template:pageTemplate,
        props:['rootdata'],
        computed:vueJSasyncComputedModule,
        methods: vueJSasyncMethodsModule
      }
    },
    data: function(){
      return {'currentComponent':pc,'data':componentData};
    },
    computed:vueJScomputedModule,
    methods: vueJSmethodsModule,
    created: function(){

      let path = SitePaths.root.replace(/\/$/, "") + this.$route.path;
      let home = SitePaths.home;

      if(wpGurusVueJSlocal.debug) console.log('Route path:'+this.$route.path);
      //get rest data.
      let restRequest='';
      let restpath, routePath = this.$route.path
      /** @since 1.1.0 handle 404 pages*/
      let is404 = (InitialPage.is404 && SitePaths.currentRoute == routePath);

      if('undefined' != typeof VueCustomRoutes.vues[routePath]){
        if(!initError) componentData.posts=[];//reset;
        restRequest = VueCustomRoutes.vues[routePath];
        restpath = restRequest.rest;
        if(is404 && InitialPage.page404>0) restpath = WPrestPath.current;
        componentData.type = restRequest.post;
        componentData.archive = false;
        componentData.single = false;
        switch(restRequest.type){
          case 'archive':
            componentData.archive = true;
            break;
          case 'single':
            componentData.single = true;
            break;
        }
        componentData.istax = false;
        componentData.taxonomy = '';
        if('undefined' != typeof restRequest.taxonomy) {
          componentData.istax = true;
          componentData.taxonomy = restRequest.taxonomy;
        }
        componentData.term = '';
        if('undefined' != typeof restRequest.term) componentData.term = restRequest.term;

        if(wpGurusVueJSlocal.debug) console.log('Vue rest request: '+restpath);
        //set the current page request rest path to the first index of an array of Promises.
        let arrPromises = [this.$http.get(restpath)];
        let rIdx =0;
        let pageSlug='';
        //if a menu is requested, set its request rest path to the next index in the array.
        if('undefined' != typeof InitialMenu['languages'] && 'undefined' != typeof WPrestPath['languages'] ){
          rIdx++;
          let getpath = WPrestPath.languages;
          if(path !== home){ //inner page request/
            let slugs = routePath.split('/');
            pageSlug = slugs[slugs.length-1];
            if(0==pageSlug.length) pageSlug = slugs[slugs.length-2];
            getpath = WPrestPath.languages+pageSlug;
          }
          switch(true){
            case componentData.istax:
              getpath = WPrestPath.languages+componentData.term+'?tax='+componentData.taxonomy;
              break;
            case componentData.archive:
              getpath += '?archive='+componentData.type;
              break;
            case componentData.single:
              getpath += '?ptype='+componentData.type;
              break;
          }
          if(wpGurusVueJSlocal.debug) console.log('translate page: '+getpath);
          arrPromises[rIdx]=this.$http.get(getpath);
        }
        //extra custom request: if any set each extra request path to subsequent indexes in teh array.
        if('undefined' != typeof VueCustomRoutes.routes[routePath]){
          if(wpGurusVueJSlocal.debug) console.log('found extra rest resquest:');
          for(let key in VueCustomRoutes.routes[routePath]){
            rIdx++;
            let path = VueCustomRoutes.routes[routePath][key];
            arrPromises[rIdx] = this.$http.get(path)
            if(wpGurusVueJSlocal.debug) console.log(VueCustomRoutes.routes[routePath][key]);
          }
        }
        /** @since 2.1.0 enable asynchronous  scripts/css stylesheets*/
        if('undefined' != typeof VueCustomRoutes.vues[routePath].script
           && VueCustomRoutes.vues[routePath].script.length>0){
          for(let key in VueCustomRoutes.vues[routePath].script){
            rIdx++;
            let path = VueCustomRoutes.vues[routePath].script[key];
            arrPromises[rIdx] = this.$http.get(path);
          }
        }
        if(initError) return;
        //now we wait until all request rest paths have been returned through out Proise object.
        Promise.all(arrPromises).then( (data) => {
          rIdx = 0;
          if(data[rIdx].body instanceof Array){
            componentData.posts = data[rIdx].body;
          }else{
            componentData.posts = [data[rIdx].body];
          }
          componentData.homepage = (path === home);
          //language menus for new page.
          if('undefined' != typeof InitialMenu['languages'] && 'undefined' != typeof WPrestPath['languages'] ){
            rIdx++;
            let translations = data[rIdx].body;
            if(home == data[rIdx].body[componentData.lang].url && pageSlug.length>0
              && componentData.archive && !componentData.istax){
              //this is an archive page for which polylang does not have a translation.
              if(wpGurusVueJSlocal.debug) console.log('translated pages: ');
              for(let lng in translations ){
                translations[lng].url += pageSlug+'/';
                if(wpGurusVueJSlocal.debug) console.log(translations[lng].url);
              }
            }
            componentData.menus.languages = translations;
          }
          //extra custom request.
          componentData.custom={};
          if('undefined' != typeof VueCustomRoutes.routes[this.$route.path]){
            for(let key in VueCustomRoutes.routes[this.$route.path]){
              rIdx++;
              componentData.custom[key] = data[rIdx].body;
              if(wpGurusVueJSlocal.debug) console.log('added custom data: '+key);
              if(wpGurusVueJSlocal.debug) console.log(componentData.custom[key]);
            }
          }
          /** @since 2.1.0 enable asynchronous  scripts/css stylesheets*/
          //remove prev asynchronous elements.
          let head = document.getElementsByTagName('head')[0];
          let asyncElms = head.getElementsByClassName('asynchronous-script');
          for(let aIdx=0; aIdx < asyncElms.length;aIdx++) head.removeChild(asyncElms[aIdx]);

          if('undefined' != typeof VueCustomRoutes.vues[routePath].script
             && VueCustomRoutes.vues[routePath].script.length>0){
            for(let key in VueCustomRoutes.vues[routePath].script){
              rIdx++;
              let script;
              switch(key){
                case 'css':
                  script = document.createElement('style');
                  script.type = 'text/css';
                  script.appendChild(document.createTextNode(data[rIdx].body));
                  break;
                default:
                  script = document.createElement('script');
                  script.type = 'text/'+key;
                  break;
              }
              script.class = 'asynchronous-script';
              head.appendChild(script);
            }
          }
          this.data = componentData; /*setup the data*/
        }, (data) => {
          if(wpGurusVueJSlocal.debug) console.log('ERROR,failed to get api data');
          if(wpGurusVueJSlocal.debug) console.log(data);
          this.status = { error: "failed to load the page"};
          //if('404'==data.status) componentData.posts[0] = InitialPage.content404;
        });
      }
    },
    updated: function(){
      //remove any inner styles.
      Array.from(document.getElementsByClassName("wpgurus-inner-styles")).forEach(element => element.remove());
      //inline styles if any.
      if(this.data.posts.length > 0 && 'undefined' != typeof this.data.posts[0].wg_inline_style && this.data.single){
        var elId, style;
        for(let sid in this.data.posts[0].wg_inline_style){
          elId = 'wpgurus-inline-style-' + sid;
          if(sid.length>1) elId = sid;
          style = document.getElementById(elId);
          if(wpGurusVueJSlocal.debug) console.log('creating <style> element: '+elId);
          style = document.createElement('style')
          style.type = "text/css"
          style.id = elId;
          style.class="wpgurus-inner-styles";
          document.head.appendChild(style)
          style.innerText = this.data.posts[0].wg_inline_style[sid];
        }
      }

      //trigger an update on the page body.
      let event = new CustomEvent("wpgurus-vuejs-updated", {
      		detail: {
      			message: "Mounted VueJs",
      			time: new Date(),
      		},
      		bubbles: true,
      		cancelable: true
      	}
      );
      document.body.dispatchEvent(event);
      if(wpGurusVueJSlocal.debug) console.log('vuejs updated');
    }
  })
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
if(wpGurusVueJSlocal.debug) console.log('Custom routes:');
if(wpGurusVueJSlocal.debug) console.log(VueCustomRoutes.routes);
//setup routes and components.
const bodyComponent = pageComponent(); //VueCustomRoutes.routes
if('undefined' == typeof VueCustomRoutes.vues[rootPath].async){
  routes[routes.length]={
    path:rootPath,
    component: bodyComponent
  }
}
//setup pages/posts.
const asyncVues = [];

for(let key in VueCustomRoutes.vues){
  if('undefined' != typeof VueCustomRoutes.vues[key].async && VueCustomRoutes.vues[key].async){
    asyncVues[key] = VueCustomRoutes.vues[key];
  }else{
    routes[routes.length]={
      path:key,
      component:bodyComponent
    }
  }
}

if(wpGurusVueJSlocal.debug) console.log('page routes');
if(wpGurusVueJSlocal.debug) console.log(routes);
const router  = new VueRouter({
  routes: routes,
  mode:'history'
});

router.beforeEach(function(to,from,next){
  if(to.path in asyncVues){
    next(false);
    if(wpGurusVueJSlocal.debug) console.log('Async route:'+to.path);
    let restPath = asyncVues[to.path].rest;
    delete asyncVues[to.path]; //remove since now in router.
    //get page template.
    new Promise(function(resolve, reject) {
      const req = new XMLHttpRequest();
      req.open('GET', restPath);
      req.onload = () => req.status === 200 ? resolve(req.response) : reject(Error(req.statusText));
      req.onerror = (e) => reject(Error(`Network Error: ${e}`));
      req.send();
    }).then(function(data){
      let obj = JSON.parse(data);
      let tpl = obj.content.rendered;
      if(tpl.length==0) tpl='<em>Empty page</em>';
      router.addRoutes([{
        path: to.path,
        component: pageComponent(tpl)
      }]);
    }).catch(function(err){
      console.log('Async route error:');
      console.log(err);
    });
  }
  next();
});

//mount vue.
const mv = new Vue({
  router: router
}).$mount('#main-vue');
