/*Home Cateory*/
new Vue({
  el:'#home-category',
  data:{
	  home_categories: undefined,
	  title:'Home Cateory',
          baseurl:baseurl,
  },
   mounted() {
    this.getList();
	
  },
  methods:{
	  getList:function(){
		axios
		  .get("eshop_api/popular_categories")
		  .then(res => {
			this.home_categories = res.data
                        //$('.category_box').show();
			//console.log(this.home_categories)
		  });
	  },
	 getCategoryImage:function(image){
    var subdomain = new URL(window.location.href).hostname.split('.')[0];
		 if(image==null || image=='')
            var img =baseurl+'assets/mdata/'+ subdomain + '/uploads/no_image.png';
		else
			var img =baseurl+'assets/mdata/'+ subdomain + '/uploads/thumbs/'+image;
            return img;
    } 
  }
});
/*END Home Cateory*/

/* Slider Cateory*/
new Vue({
  el:'#slider-component',
  data:{
	   slider1 : hamepage_image_1,         
       slider2 : hamepage_image_2,
       slider3 : hamepage_image_3,
  },
   mounted() {
   
	
  },
  updated () {
		$('.carousel.carousel-slider').carousel({fullWidth: true},setTimeout(autoplay, 1500));
	},
  methods:{
	  getslideImage:function(image){
          return image;
      }
  }
});
 
/*END Slider Cateory*/
/* FEATURED PRODUCTS*/
new Vue({
  el:'#featured_product-component',
  data () {
      return {
        products:null,
        baseurl:baseurl,
       }
    },
    mounted () {
      axios
        .get('eshop_api/featured_product')
         .then(response => (this.products = response.data)
               
           
        );
    },
    updated () {
		$("#featured-product").slick({
			infinite: true,
			slidesToShow: 2,
			slidesToScroll: 2
		  });
    },
    methods: {
        formatPrice(value) {
            let val = (value/1).toFixed(2)
            return val.toString()
        },
        getImage:function(image){
          var subdomain = new URL(window.location.href).hostname.split('.')[0];
            var img =baseurl+'/assets/mdata/'+ subdomain + '/uploads/'+image;
            return img;
        },
        addtowishlist:function(productid){
        loader('show');
         var varId = $('#futprodoption_'+productid).val();
         var postData = 'product_id=' + productid;
         if(varId){
            postData = postData + '&option=' + varId;
          }
          var returndata =  addToWishlist(postData);
          if(returndata){
           document.getElementById("futprdaddwishbtn_"+productid).style.background = '#26a69a';
          }
         loader();

      },
      onchangeVariant:function(productid){
            loader('show');
            var option = $('#futprodoption_'+productid).val();
            var pprice  = $('#futproductprice_'+productid).val();
            if(option){
              var expvar = option.split('~');
              var vprice = expvar[1];
              pprice = parseFloat(pprice) + parseFloat(vprice);
            }
            $('#futproprice_'+productid).html(pprice.toFixed(2));
            loader();
      },
      variantprice:function(price, varaint){

        return parseFloat(price) + parseFloat(varaint.price);
      },
     addToCart:function(productid){
            var option = $('#futprodoption_'+productid).val();
			var ItemId=productid;
            if(option){
              var expvar = option.split('~');
              var variant = expvar[0];
               ItemId = productid+'_'+variant;
            }
            addToCart_(productid,ItemId);
        },
        show_product_detail:function(productid){
		  var option = $('#futprodoption_'+productid).val();
		  var ItemId=productid;
            if(option){
              var expvar = option.split('~');
              var variant = expvar[0];
               ItemId = productid+'_'+variant;
            }
		  window.location.href = baseurl+"shop/product_details/"+ItemId;
		}
    }
});
/*END FEATURED PRODUCTS*/

/** Populer Products ***/
var populer_product = new Vue({
  el:'#populer_product',
  data () {
      return {
        populers:null,
        baseurl:baseurl,
       }
    },
    mounted () {
      axios
        .get('eshop_api/populerproduct')
         .then(response => (this.populers = response.data)
                        
        );
    },
    updated () {
		$("#populer-product").slick({
			infinite: true,
			slidesToShow: 2,
			slidesToScroll: 2
		});
	},
    methods: {
     
       addtowishlist:function(productid){
        loader('show');
         var varId = $('#popprodoption_'+productid).val();
         var postData = 'product_id=' + productid;
         if(varId){
            postData = postData + '&option=' + varId;
          }
          var returndata =  addToWishlist(postData);
          if(returndata){
           document.getElementById("popprdaddwishbtn_"+productid).style.background = '#26a69a';
          }
         loader();

      },
      onchangeVariant:function(productid){
            loader('show');
            var option = $('#popprodoption_'+productid).val();
            var pprice  = $('#popproductprice_'+productid).val();
          
            if(option){
              var expvar = option.split('~');
              var vprice = expvar[1];
              pprice = parseFloat(pprice) + parseFloat(vprice);
            }
            $('#popproprice_'+productid).html(pprice.toFixed(2));
            loader();
      },
      variantprice:function(price, varaint){

        return parseFloat(price) + parseFloat(varaint.price);
      },
     addToCart:function(productid){
            var option = $('#popprodoption_'+productid).val();
			var ItemId=productid;
            if(option){
              var expvar = option.split('~');
              var variant = expvar[0];
               ItemId = productid+'_'+variant;
            }
            addToCart_(productid, ItemId);
        },
       show_product_detail:function(productid){
		  var option = $('#popprodoption_'+productid).val();
		  var ItemId=productid;
            if(option){
              var expvar = option.split('~');
              var variant = expvar[0];
               ItemId = productid+'_'+variant;
            }
		  window.location.href = baseurl+"shop/product_details/"+ItemId;
		}
    }
});


/** End Populer Products */

/**
 * Category Vise Product
 * @type Vue
 */

var category_products = new Vue({
  el:'#category_product-component',
  data () {
      return {
        catproducts:null,
        baseurl:baseurl,
       }
    },
    mounted () {
      loader('show');
      axios
        .get('eshop_api/categoryproducts')
         .then(response => (this.catproducts = response.data)
                        
        );
      loader();
    },
    updated () {
		$("#category-product").slick({
			infinite: true,
			autoplay:true,
			slidesToShow: 2,
			slidesToScroll: 2
		});
	},
    methods: {
     
       addtowishlist:function(productid){
        loader('show');
         var varId = $('#catproductoption_'+productid).val();
         var postData = 'product_id=' + productid;
         if(varId){
            postData = postData + '&option=' + varId;
          }
           var returndata =  addToWishlist(postData);
         if(returndata){
          document.getElementById("catprductaddwishbtn_"+productid).style.background = '#26a69a';
         }
         loader();

      },
      onchangeVariant:function(productid){
            loader('show');
            var option = $('#catproductoption_'+productid).val();
            var pprice  = $('#catproductprice_'+productid).val();
            if(option){
              var expvar = option.split('~');
              var vprice = expvar[1];
              pprice = parseFloat(pprice)+ parseFloat(vprice);
            }
            $('#catproprice_'+productid).html(pprice.toFixed(2));
            loader();
      },
      variantprice:function(price, varaint){

        return parseFloat(price) + parseFloat(varaint.price);
      },
      addToCart:function(productid){
            var option = $('#catproductoption_'+productid).val();
			var ItemId=productid;
            if(option){
              var expvar = option.split('~');
              var variant = expvar[0];
               ItemId = productid+'_'+variant;
            }
            addToCart_(productid,ItemId);
        },
        show_product_detail:function(productid){
		  var option = $('#catproductoption_'+productid).val();
		  var ItemId=productid;
            if(option){
              var expvar = option.split('~');
              var variant = expvar[0];
               ItemId = productid+'_'+variant;
            }
		  window.location.href = baseurl+"shop/product_details/"+ItemId;
		}

    }
});
/*
 * End Category Vise Product
 */


 