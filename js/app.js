  /*
    The following constants are used in this script and are loaded from the php files include/load_menu.php or include/load_page.php in the theme folder,
     - InitialPage : this is the page json object on the initial request to the server.
     - InitialMenu : this is the menus regsitered by the theme.
     - WPrestPath : an array of paths for the various end-points available.  NB: the home page (front page) request path is included as WPrestPath.home.
     - SitePaths : some site url paths, used to distinguish between localhost and domain based isntallations.
  */

  var currentPage = InitialPage;
  let isHomepage = InitialPage.data[0].homepage;

  const exText = Vue.component('content-example',{
    template: '#comp-example',
    data: function(){
      return {
        text:'<!-- this is an example of inserting a vueJs component -->'
      }
    }
  });
  //menu.
  const vjsMenu = function(type){
    if('undefined' == typeof type) type = 'primary';
    return Vue.component(type+'-menu',{
      template: '#vue-menu',
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
        }
      }
    }); //end Vue.component.
  }
  const stdMenu = function(type){
    if('undefined' == typeof type) type = 'primary';
    return Vue.component(type+'-menu',{
      template: '#std-menu',
      data: function(){
        return {
          menu:InitialMenu[type]
        }
      },
      methods:{
        menuClass: function(){
          return 'large-text-right';
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
  //const primaryMenu = vjsMenu('primary');
  const footerMenu = vjsMenu('footer');
  const primaryMenu = stdMenu('primary');

  const wpPage = {
    template: '#content-page',
    data: function(){
      return {
        page: currentPage.data[0],
        homepage: isHomepage,
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
          currentPage = { error: "failed to load the page"};
        });
        //end get.
      }
    }
  }
  //}
  //Vue.use(VueResource);
  //Vue.use(VueRouter);
  const routes = [{
    path:SitePaths.home.replace(SitePaths.root,'/'),
    component: wpPage
  }];
  //setup primary menu routes.
  if(InitialMenu.enabled && InitialMenu.primary.length > 0){
    for(let idx = 0; idx< InitialMenu.primary.length; idx++){
      routes[routes.length] = {
        path: InitialMenu.primary[idx].url.replace(SitePaths.root, '/'),
        component: wpPage
      };
    }
  }
  if(InitialMenu.enabled && InitialMenu.footer.length > 0){
    for(let idx = 0; idx< InitialMenu.footer.length; idx++){
      routes[routes.length] = {
        path: InitialMenu.footer[idx].url.replace(SitePaths.root, '/'),
        component: wpPage
      };
    }
  }
  //console.log(routes);
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
