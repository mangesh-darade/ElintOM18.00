/*Shopping Cart List*/
new Vue({
	el:'#checkout-cart-list-content',
	data:{
		eshop_settings:[],
		state_lists:[],
		cart_lists:[],
		shipping_methods_lists:[],
		shipping_methods_list:{
			k:1,
			all_time:'',
			id:'',
			name:'',
			shipping_amount:0,
			display_shipping_amount:0,
		},
		billing_shipping:{
			billing_name:'',
			billing_phone:'',
			billing_email:'',
			billing_addr1:'',
			billing_city:'',
			billing_addr2:'',
			billing_state:0,
			billing_country:'',
			billing_zipcode:'',
			shipping_name:'',
			shipping_phone:'',
			shipping_email:'',
			shipping_addr1:'',
			shipping_city:'',
			shipping_addr2:'',
			shipping_state:0,
			shipping_country:'',
			shipping_zipcode:'',
		},
		total_amount:0,
		display_cart_sub_total:0,
		display_cart_tax_total:0,
		total_order_amount:0,
		total_payable_amount:0,
		cart_gross_rounding:0,
		order_tax_total:0,
		cart_gross_total:0,
		total_billing_amount:0,
		shipingAmt:0,
		billing_state:0,
		order_tax_id:0,
		shipping_address_same:false,
		msg:'',
		cart_serialize:'',
		shipping_name:'Delivery at home',
		shipingAllTime:1,
		thumbs:'',
		baseurl:baseurl,
	},
	mounted() {
		loader('show');
		this.getList();
		
	},
	methods:{
		getList:function(){
			var slitems_str = localStorage.getItem('slitems');
                        if(slitems_str==null){
				window.location = baseurl+"shop/shopping_cart";
			}
			axios.post(baseurl+"eshop_api/checkout_details/", {
				slitems:JSON.parse(slitems_str),
			}).then((result) => {
				this.state_lists=result.data.state;
				this.cart_lists=result.data.cart.items;
				this.display_cart_sub_total=result.data.cart.display_cart_sub_total;
				this.display_cart_tax_total=result.data.cart.display_cart_tax_total;
				this.total_order_amount=result.data.cart.total_order_amount;
				this.total_payable_amount=result.data.cart.total_payable_amount;
				this.cart_gross_rounding=result.data.cart.cart_gross_rounding;
				this.order_tax_total=result.data.cart.order_tax_total;
				this.cart_gross_total=result.data.cart.cart_gross_total;
				this.order_tax_id=result.data.cart.order_tax_id;
				this.shipingAmt=result.data.shipingAmt;
				this.total_billing_amount=result.data.total_billing_amount;
				this.shipping_methods_lists=result.data.ShippingMethods;
				this.billing_shipping=result.data.billing_shipping;
				this.cart_serialize=result.data.cart_serialize;
				this.thumbs=result.data.thumbs;
				this.eshop_settings=result.data.eshop_settings;
				if(this.billing_shipping.length!=0)
					this.shipping_address_same=true;
                                 if(this.billing_shipping.length==0){
					//console.log(result.data.customer);
					this.billing_shipping.billing_name=result.data.customer.name;
					this.billing_shipping.billing_phone=result.data.customer.phone;
					this.billing_shipping.billing_email=result.data.customer.email;
				}
				//console.log(this.eshop_settings);
				if(result.data.billing_shipping.billing_state==undefined)
					this.billing_shipping.billing_state=0;
				if(result.data.billing_shipping.shipping_state==undefined)
					this.billing_shipping.shipping_state=0;
				this.shipingAllTime = result.data.shipingAllTime;
				if(this.shipingAllTime!='1'){
					show_slote_time(1);
				}
					
			})
			
			loader();
		},
		shipping_input_same:function(event){
			if($('#shipping_billing_is_same').is(':checked')) {
				this.billing_shipping.shipping_name=this.billing_shipping.billing_name;
				this.billing_shipping.shipping_phone=this.billing_shipping.billing_phone;
				this.billing_shipping.shipping_email=this.billing_shipping.billing_email;
				this.billing_shipping.shipping_addr1=this.billing_shipping.billing_addr1;
				this.billing_shipping.shipping_city=this.billing_shipping.billing_city;
				this.billing_shipping.shipping_state=this.billing_shipping.billing_state;
				this.billing_shipping.shipping_country=this.billing_shipping.billing_country;
				this.billing_shipping.shipping_zipcode=this.billing_shipping.billing_zipcode;
			}
		},
		billing_input:function(event){
			if($('#shipping_billing_is_same').is(':checked')) {
				if(event.target.id=='billing_name')
					this.billing_shipping.shipping_name=event.target.value;
				if(event.target.id=='billing_phone')
					this.billing_shipping.shipping_phone=event.target.value;
				if(event.target.id=='billing_email')
					this.billing_shipping.shipping_email=event.target.value
				if(event.target.id=='billing_addr1')
					this.billing_shipping.shipping_addr1=event.target.value;
				if(event.target.id=='billing_city')
					this.billing_shipping.shipping_city=event.target.value
				if(event.target.id=='billing_state')
					this.billing_shipping.shipping_state=event.target.value;
				if(event.target.id=='billing_country')
					this.billing_shipping.shipping_country=event.target.value;
				if(event.target.id=='billing_zipcode')
					this.billing_shipping.shipping_zipcode=event.target.value;
			}
			
		},
		onchangeShipping:function(event){
			if($('#shippingType1').is(':checked'))
				this.shipping_name='Delivery at home';
			else
				this.shipping_name='Pickup from store';
			if($(event.target).is(':checked')) {
				var v = event.target.value;
				var shipping_price = $('#shipping_price_'+v).val(); 
				var order_total = $('#order_total').val();
				  //alert(order_total);
				  //alert(shipping_price);
			
				var orderwithtax = $('#withordertax_amount').val();
				//var total = parseInt(order_total) + parseInt(shipping_price);
				var total = (parseFloat(order_total) + parseFloat(shipping_price)).toFixed(2);
				// console.log(total);
				this.shipingAmt=shipping_price;
				this.total_billing_amount=total;
			}
			getshippingCharges(event.target);
		}
	}
});
function getshippingCharges(Obj){
	/*
	  Slot Box show
	  */
	  $('#dilivery_late_block').hide();
        $('#deliverydata').val('');
		$('input[type="date"]').removeAttr('required');
        
        var getid = Obj.value;
        var alltime = Obj.data_id;
        if(alltime !='1'){
            show_slote_time(getid);
        }

        if(getid == 3){
            $('#dilivery_late_block').show();
            $('input[type="date"]').attr("required", "true");
        }
}
/*END Shopping Cart List*/

function changevalue(){
   if($('#shippingType1').is(':checked'))
	  $('#shippingTypeName').val('Delivery at home');
   if($('#shippingType2').is(':checked'))
	  $('#shippingTypeName').val('Pickup from store');
}
function show_slote_time(getid){
      $('.show_slot').html('');
       $('#dilivery_late_block').hide();
       $('#deliverydata').val('');
     $.ajax({
           type:'ajax',
           dataType:'html',
           method:'get',
           url:baseurl+"eshop_api/getSloteTime/"+getid,
           success:function(response){
			   //console.log(response);
               $('#block_id_'+getid).html(response)
              
            }, error:function(){
                console.log('error');
            }
     });
 }
 