/*Product List*/
new Vue({
  el:'#product-detail-content',
  data() {
		return {
			baseurl: baseurl,
			ProductItemId: ProductItemId,
			product_detail: [],
			product_variants: [],
			product_images: [],
			releted_products: [],
			title:'Product Details Content',
			msg:'',
			CartItemQty:1,
			stocks:stocks,
			LoadActive:false,
		}
    },
   mounted() {
	   //console.log(localStorage.getItem('slitems'));
		this.getProductDetails();
		this.showQty();
		//this.getCartItems();
		
  },
  updated () {
		$("#populer-product").slick({
			infinite: true,
			autoplay:true,
			slidesToShow: 2,
			slidesToScroll: 2
		});
	},
  methods:{
	  getProductDetails:function(){  
		loader('show');
		var detail = '';
		axios
		  .get(baseurl+"eshop_api/product_details/"+ProductItemId)
		  .then(res => {
				this.product_detail=res.data.product;
				this.product_variants=res.data.variants;
				this.product_images=res.data.product_images;
				this.releted_products=res.data.releted_products;
				//console.log(this.product_images);
				this.LoadActive=true;
		  });
		  loader();
		},
		onchangeCartQty:function(event, Stock, product_item_id){
			
			var QtyInput = event.target.value;
			if(parseFloat(event.target.value)>parseFloat(Stock)){
                                loader('show');
				QtyInput = Stock;
				$('#StockMsg_'+product_item_id).fadeIn('slow');
				$('#StockMsg_'+product_item_id).delay(5000).fadeOut('slow');
                                loader();
			}
			$('#QtyInput-'+product_item_id).val(QtyInput);
			/*updateCartQty(event.target.value, event.target.id);
			*/
			//console.log(event.target.id+' '+event.target.value+' '+product_item_id);
		},
		addToCart:function(ProductId){
			loader('show');
			var ItemId = $('.ProductItemId_'+ProductId).val();
			addToCart_(ProductId, ItemId, 'ManualUpdate');
		 
		},
		showQty:function(ProductId){
			var Qty = 1;
			var slitems_str = localStorage.getItem('slitems');
			$.each( JSON.parse(slitems_str), function( key, value ) {
				if(key==ProductItemId){
					Qty = value.CartItemQty;
				}
				
			});
			this.CartItemQty = Qty;
		},
		getCartItems:function(){
			getCartItems_();
		},
         addtowishlist:function(productid){
           loader('show');
         var varId = $('#prodoption_'+productid).val();
         var postData = 'product_id=' + productid;
         if(varId){
            postData = postData + '&option=' + varId;
          }
          var returndata =  addToWishlist(postData);
          if(returndata){
           document.getElementById("prdaddwishbtn_"+productid).style.background = '#26a69a';
          }
         loader();

      },
  }
});
/*END Product List*/ 