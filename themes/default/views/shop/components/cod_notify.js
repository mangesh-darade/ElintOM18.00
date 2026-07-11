new Vue({
	el:'#cart_empty',
	data:{
		baseurl:baseurl,
	},
	mounted() {
		localStorage.removeItem('slitems_cart');
		localStorage.removeItem('slitems');
		getCartCount();
	},
	
}); 