/*
main content component computed functions.
use the template below to add methods to your vuejs component.
*/
var vueJScomputedModule = (function (vcm) {
	// add capabilities...
  // vcm.isPage = function(){
  //   if('page'== this.data.type){
  //     return true;
  //   }else return false;
  // }
	return vcm;
}(vueJScomputedModule || {}));
/*
main content component vuejs methods functions.
*/
var vueJSmethodsModule = (function (vmm) {
	// add capabilities...
  // vmm.whichContent = function(){
  //   let component = '#single-page';
  //   switch(true){
  //     case vmm.isPage():
  //     case vmm.isSingle():
  //       component = '#single-page';
  //       break;
  //     case vmm.isArchive():
  //       component = '#archive-page';
  //       break;
  //   }
  //   return component;
  // }
  return vmm;
}(vueJSmethodsModule || {}));
/*
logo component vuejs methods functions.
*/
var vueJSlogoMethodsModule = (function (vlmm) {
	// add capabilities...
	return vlmm;
}(vueJSlogoMethodsModule || {}));
/*
logo component vuejs computed functions.
*/
var vueJSlogoComputedModule = (function (vlcm) {
	// add capabilities...
	return vlcm;
}(vueJSlogoComputedModule || {}));
/*
menu component vuejs methods functions.
*/
var vueJSmenuMethodsModule = (function (vmmm) {
	// add capabilities...
	return vmmm;
}(vueJSmenuMethodsModule || {}));
/*
menu component vuejs computed functions.
*/
var vueJSmenuComputedModule = (function (vmcm) {
	// add capabilities...
	return vmcm;
}(vueJSmenuComputedModule || {}));
/*
Computed methods for main vue component.
*/
var vueJScomputedModule = (function (vcm) {
	// add capabilities...
	// vcm.filteredSearch = function(){ //filter archive posts
	// 	if(this.data.form.header.search.length>0){
	// 		let filtered = [];
	// 		let regex = new RegExp(this.data.form.header.search,'gi');
	// 		for(let idx in this.data.posts){
	// 			let post = this.data.posts[idx];
	// 			if(post.title.rendered.match(regex)) filtered[filtered.length] = post;
	// 		}
	// 		return filtered;
	// 	}else return this.data.posts;
	// }
	return vcm;
}(vueJScomputedModule || {}));
/*
custom reactive data for forms
any data added here will be appended to the data.form element of the main vuejs component.
*/
//
var customReactiveData = (function (crd){
  //add something
	// crd.search_form = {
  //   'rooms':'1',
  //   'adults':'1',
  //   'children':'0',
  //   'amenities':[]
  // };
  return crd;
}(customReactiveData || {}));

/*
main content component vuejs methods functions.
*/
var vueJSmethodsModule = (function (vmm) {
	// add capabilities...
	// vmm.selectButton = function(index){ //dynamically show a button
  //   let buttons = document.getElementsByClassName('accomodation-button');
  //   for(let i = 0; i<buttons.length; i++){
  //     buttons[i].style.display = 'none';
  //   }
  //   document.getElementById('booking-button-'+index).style.display = 'block';
  // }

	return vmm;
}(vueJSmethodsModule || {}));
