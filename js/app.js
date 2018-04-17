  /*
    The following constants are used in this script and are loaded from the php files include/load_menu.php or include/load_page.php in the theme folder,
     - InitialPage : this is the page json object on the initial request to the server.
     - InitialMenu : this is the menus regsitered by the theme.
     - WPrestPath : an array of paths for the various end-points available.  NB: the home page (front page) request path is included as WPrestPath.home.
     - SitePaths : some site url paths, used to distinguish between localhost and domain based isntallations.
  */

  let isHomepage = InitialPage.data[0].homepage;
  // main content component.
  const wpPage = {
    template: '#content-page',
    data: function(){
      return {
        page: InitialPage.data[0],
        homepage: isHomepage,
        status:''
      }
    },
    methods: {
      articleId: function(){
        return 'post-'+this.page.id;
      }
    },
    created: function(){
      this.page = {title:{rendered:''},content:{rendered:''}};
      let path = SitePaths.root + this.$route.path.substr(1);
      if(InitialPage && InitialPage.data.length>0 && path === InitialPage.data[0].link){
        this.page = InitialPage.data[0];
        this.homepage = isHomepage;
      }else{
        let data ={};
        let restPath = WPrestPath.home;
        if(path !== SitePaths.home){ //inner page request/
          let slugs = this.$route.path.split('/');
          let pageSlug = slugs[slugs.length-1];
          if(0==pageSlug.length) pageSlug = slugs[slugs.length-2];
          restPath = WPrestPath.root+'pages/';
          data = {params:{slug: pageSlug}};
        }
        this.$http.get(restPath,data).then( (data) => {
          this.page = data.body[0];
          this.homepage = (path === SitePaths.home);
        }, (data) => {
          this.status = { error: "failed to load the page"};
        });
        //end get.
      }
    }
  }

  const routes = [{
    path:SitePaths.home.replace(SitePaths.root,'/'),
    component: wpPage
  }];
  //setup menu routes.
  const getRoutes = function(menu){
    if(InitialMenu.enabled && InitialMenu[menu].items.length > 0){
      for(let idx = 0; idx< InitialMenu[menu].items.length; idx++){
        if('undefined' !== typeof InitialMenu[menu].items[idx].isvjslink && InitialMenu[menu].items[idx].isvjslink){
          routes[routes.length] = {
            path: InitialMenu[menu].items[idx].url.replace(SitePaths.root, '/'),
            component: wpPage
          };
        }
      }
    }
  }
  //menu.
  const vjsMenu = function(type){
    if('undefined' == typeof type) type = 'primary';
    if('undefined' == typeof InitialMenu[type]){
      return null;
    }
    getRoutes(type); //load the menu links in the router.
    return Vue.component(type+'-menu',{
      template: '#'+type+'-menu',
      data: function(){
        return {
          menu:InitialMenu[type]
        }
      },
      methods:{
        menuClass: function(){
          return 'large-text-right';
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
    }); //end Vue.component.
  }

  const logo = Vue.component('logo-image',{
    template:'#content-logo',
    data: function(){
      return {
        src:SitePaths.logo,
        link:SitePaths.home.replace(SitePaths.root,'/')
      }
    }
  });


  //setup menu components and routes.
  const footerMenu = vjsMenu('footer');
  const primaryMenu = vjsMenu('primary');
  const networkMenu = vjsMenu('network');

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
