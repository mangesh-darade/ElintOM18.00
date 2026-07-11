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
			axios.post(baseurl+"eshop_api/checkout_details/", {
				slitems:JSON.parse(slitems_str),
			}).then((result) => {
				console.log(result.data);
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
				//console.log(this.eshop_settings);
				if(result.data.billing_shipping.billing_state==undefined)
					this.billing_shipping.billing_state=0;
				if(result.data.billing_shipping.shipping_state==undefined)
					this.billing_shipping.shipping_state=0;
				this.shipingAllTime = result.data.shipingAllTime;
				
			})
			
			loader();
		},
		
		
	}
});

/*END Shopping Cart List*/


function openPayment(evt, PaymentCode) {
	$('#payment_type').val(PaymentCode);
	  var i, tabcontent, tablinks;
	  tabcontent = document.getElementsByClassName("tabcontent");
	  for (i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	  }
	  tablinks = document.getElementsByClassName("tablinks");
	  for (i = 0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	  }
	  document.getElementById(PaymentCode).style.display = "block";
	  evt.currentTarget.className += " active";
}
function openOrder(evt, PaymentCode) {
	  var i, tabcontent, tablinks;
	  tabcontent = document.getElementsByClassName("tabordercontent");
	  for (i = 0; i < tabcontent.length; i++) {
		tabcontent[i].style.display = "none";
	  }
	  tablinks = document.getElementsByClassName("taborderlinks");
	  for (i = 0; i < tablinks.length; i++) {
		tablinks[i].className = tablinks[i].className.replace(" active", "");
	  }
	  document.getElementById(PaymentCode).style.display = "block";
	  evt.currentTarget.className += " active";
} 