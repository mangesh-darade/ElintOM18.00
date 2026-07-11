/*Shopping Cart List*/
new Vue({
	el:'#shopping-cart-list-content',
	data:{
		cart_lists:[],
		total_amount:0,
		msg:'',
	},
	mounted() {
		loader('show');
		this.getList();
	},
	methods:{
		getList:function(){
		var slitems_str = localStorage.getItem('slitems');
		var self = this;
		//console.log(slitems_str);
		if(slitems_str==null){
			self.msg='No Item present in your cart';
		}else{
			self.msg='';
			$.each( JSON.parse(slitems_str), function( key, value ) {
				//console.log(value);
				self.cart_lists.push(value);
				self.total_amount = parseFloat(self.total_amount) + parseFloat(value.product_price*value.CartItemQty);
				//console.log(self.total_amount +' '+ value.product_price+' '+value.CartItemQty);
			});
		}
		
		loader();
		},
                removeAllItem:function(event, product_item_id){
			if(confirm('Are you sure, You want to remove item?')){
				loader('show');
				localStorage.removeItem('slitems_cart');
				localStorage.removeItem('slitems');
				window.location.href = baseurl+"shop/shopping_cart";
			}
		},
		removeItem:function(event, product_item_id){
			if(confirm('Are you sure, You want to remove item?')){
				loader('show');
				var slitems = {};
				var slitems_str = JSON.parse(localStorage.getItem('slitems'));
				this.$delete(slitems_str, product_item_id)
				if(Object.keys( slitems_str ).length != 0){
					$.each( slitems_str, function( key, value ) {
						slitems[key] = value;
						localStorage.setItem('slitems', JSON.stringify(slitems));
					});
					getCartCount();
				}else{
					
					localStorage.removeItem('slitems_cart');
					localStorage.removeItem('slitems');
					window.location.href = baseurl+"shop/shopping_cart";
				}
				
				$('#ItemRow_'+product_item_id).remove();
				loader();
			}
			//console.log(localStorage.getItem('slitems'));
		},
		onchangeCartQty:function(event, Stock, product_item_id){
                        loader('show');
			var QtyInput = event.target.value;
			if(parseFloat(event.target.value)>parseFloat(Stock)){
				QtyInput = Stock;
				$('#StockMsg_'+product_item_id).fadeIn('slow');
				$('#StockMsg_'+product_item_id).delay(5000).fadeOut('slow');
			}
			$('#QtyInput-'+product_item_id).val(QtyInput);
			updateCartQty(event.target.value, event.target.id);
                        loader();
			//console.log(event.target.id+' '+event.target.value+' '+ProductId+' '+OptionId);
		},
	}
});
/*END Shopping Cart List*/
 