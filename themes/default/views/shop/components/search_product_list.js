/*Product List*/
new Vue({
  el:'#page-list-content',
  data:{
	  buy_btn: true,
	  baseurl: baseurl,
	  product_lists: undefined,
	  product_lists_count: '',
	  product_lists_totalpages: 0,
	  product_lists_page_no: PageNo,
	  term: term,
	  page_link_left: '#',
	  page_link_left_active: 'disabled',
	  page_link_right: '#',
	  page_link_right_active: 'disabled',
	  title:'Product List Content',
	  msg:'',
	  CartItemQty:1
  },
   mounted() {
    this.getList();
	this.getCartItems();
  },
  methods:{
	  getList:function(){
		loader('show');
		axios
		  .get(baseurl+"eshop_api/search_product/"+term+'/'+PageNo)
		  .then(res => {
			this.product_lists = res.data.items;
			this.product_lists_count = res.data.count;
			this.product_lists_totalpages = res.data.totalPages; 
			this.msg = res.data.msg; 
			if(PageNo > 1){
				this.page_link_left = baseurl+'shop/product_list/'+term+'/'+(PageNo-1);
				this.page_link_left_active='waves-effect';
			}
			if(PageNo < this.product_lists_totalpages){
				this.page_link_right = baseurl+'shop/product_list/'+term+'/'+(parseInt(PageNo)+parseInt(1));
				this.page_link_right_active='waves-effect';
			}
			loader();
			console.log(this.product_lists);
		  });
		},
		onchangeVariant:function(event, ProductId){
			loader('show');
			var ProductItemQty = $('.ProductItemQty_'+ProductId).val();
			axios
		  .get(baseurl+"eshop_api/getProductVariantDetails/"+ProductId+'/'+event.target.value)
		  .then(res => {
			  console.log(res.data);
			  $('.Price_'+ProductId).text('Rs. '+res.data.product_price);
			  $('.ProductDetail_'+ProductId).attr('href', baseurl+'shop/product_details/'+res.data.product_item_id);
			  $('.ProductItemId_'+ProductId).val(res.data.product_item_id);
			  $('#AddToCart_'+ProductId).removeClass( "disabled" );
			  if(res.data.stocks<=0){
				  $('.Stock_'+ProductId).css('color', 'gray').text('Out Of Stock');
				  $('#AddToCart_'+ProductId).addClass( "disabled" );
			  }
			  else if(res.data.stocks>20)
				  $('.Stock_'+ProductId).css('color', 'green').text('Available');
			  else
				  $('.Stock_'+ProductId).removeAttr( "style" ).text('Only available '+res.data.stocks+' item');
			  loader();
			//console.log(res.data.product_price);
		  });
	 },
	 addToCart:function(ProductId){
		 loader('show');
		 console.log(ProductId);
		 addToCart_(ProductId);
		 
	 },
	 getCartItems:function(){
		 //localStorage.removeItem('slitems_cart');
		// localStorage.removeItem('slitems');
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
 