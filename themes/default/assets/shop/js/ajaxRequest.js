$(document).ready(function(){
	
     var baseUrl = $('#baseurl').val(); 
     
     $('#searchCategoryButton').click(function(){
       
        var key = $('#searchCategory').val();
        
        if(key.length <= 3) return false;
        
        var postData = 'keyword=' + key;
        var url = baseUrl + 'shop/searchCategories';
     
        $.ajax({
                type: 'get',
                url: url,
                data: postData,
                beforeSend: function(){ 
                    var alert = '<div class="overlay text-info"><i class="fa fa-refresh fa-spin"></i> Please Wait! Searching...</div>';
                    $("#searchCategoryList").html(alert);                    
                },
                success: function(dataList){
                    $("#searchCategoryList").html(dataList);
                },
                error: function(errormsg){
                    console.log(errormsg);
                }
            });  

    });
	
});

function searchProducts(){        
        
	var baseUrl = $('#baseurl').val(); 
	var catId   = $('#catId').val(); 
	var page    = $('#page').val(); 
	var limit   = $('#limit').val(); 
        var searchProducts   = $('#searchProducts').val();
        
        if(searchProducts.length < 4) {
            alert('Please search keyword should be minimum 4 charectors.');
            return false;
        }
        
	$('#catlog_products').html('<h4><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait ... </h4>');
        
	var postData = 'catId=' + catId;		 
            postData = postData + '&page=' + page;
            postData = postData + '&limit=' + limit;
            postData = postData + '&keyword=' + searchProducts;
           
	$.ajax({
                    type: "get",
                    url: baseUrl + 'shop/catlogProducts',
                    data: postData,	
                    beforeSend: function() {
                        
                        $('#catlog_products').html('<h4><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait ... </h4>');
                    },
                    success: function( Data){ 

                        $('#catlog_products').html(Data);      
                    }
            });    
}

function loadProducts(){        
        
	var baseUrl = $('#baseurl').val(); 
	var catId   = $('#catId').val(); 
	var page    = $('#page').val(); 
	var limit   = $('#limit').val(); 
         
        
	$('#catlog_products').html('<h4><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait ... </h4>');
	var postData = 'catId=' + catId;		 
            postData = postData + '&page=' + page;
            postData = postData + '&limit=' + limit;
           
	$.ajax({
                    type: "get",
                    url: baseUrl + 'shop/catlogProducts',
                    data: postData,	
                    beforeSend: function() {
                        
                        $('#catlog_products').html('<h4><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait ... </h4>');
                    },
                    success: function( Data){ 

                        $('#catlog_products').html(Data);      
                    }
            });    
}

function loadCategoryProducts(catId){
    
    $('#catId').val(catId);
    $('#page').val(1);
    
    loadProducts();
}

function loadPageProducts(page){
    
    $('#page').val(page);
    
    loadProducts();
}

function loadSubCategory(catId){
        
    var resultId = '#subcategory_list_' + catId;
    var storageId = 'cate_'+ catId;
    
    if($(resultId).html()=='' ) {
         
        var baseUrl = $('#baseurl').val(); 
        
        var postData = 'parent_id=' + catId;
              
        $(resultId).html('<h4><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait ... </h4>');
        
        $.ajax({
                    type: "get",
                    url: baseUrl + 'shop/loadSubcategory',
                    data: postData,	
                    beforeSend: function() {
                        
                        $(resultId).html('<h4><i class="fa fa-refresh fa-spin text-danger" ></i> Loading...</h4>');
                    },
                    success: function( Data ){ 

                        $(resultId).html(Data);      
                    }
            });
       
    }
}


function loadCategories(){
    
    var baseUrl = $('#baseurl').val();
    
    if (sessionStorage.category == '') {       
        getAllCategory(baseUrl);
    }
    
    if(sessionStorage.category) {

        var postData = 'categoryJson=' + sessionStorage.category;

        $.ajax({
                type: "post",
                url: baseUrl + 'shop/loadCategories',
                data: postData,	
                beforeSend: function() {

                    $('#myAccordion').html('<h4><i class="fa fa-refresh fa-spin text-danger" ></i> Loading...</h4>');
                },
                success: function( Data){ 

                    $('#myAccordion').html(Data);      
                }
        });
        
    } else {
        $('#myAccordion').html('<p class="text-red">Storage Data Not Found.</p>');
    }
}

function getAllCategory(baseUrl){ 

    $.ajax({
        type: "get",
        url: baseUrl + 'shop/allCategories',
        success: function( Data){
            // Storing Data
            sessionStorage.setItem('category', Data);     
        }
    });
    
}


function addToCart(prodId){
        
        var baseUrl = $('#baseurl').val(); 
        var postData = 'product_id=' + prodId;
        $('#cartNotify').modal('show');
        $('#bootstrapAlert').html('<div class="alert alert-info"><i class="fa fa-refresh fa-spin text-danger" ></i> Please Wait! Item is adding to cart</div>');
        $.ajax({
                type: "get",
                url: baseUrl + 'shop/addCartItems',
                data: postData,	
                success: function( Data){ 
                    $('#bootstrapAlert').html('<div class="alert alert-success"><i class="fa fa-check"></i> Item successfully added. Thank you.</div>');
                    
                    $('.cart-count').html(Data);
                    
                    setTimeout(function(){ $('#cartNotify').modal('hide'); }, 1000);
                    
                }
        });
    
}



function updateQtyCost(itemId){
    
    var qty = $('#qty_'+itemId).val();
    
    var tax = $('#item_tax_rate_'+itemId).val();
     
    var price = $('#item_price_'+itemId).val();
    
    var total = qty * price;
    var itemtax = ((total * tax) / 100); 
    
    $('#show_total_'+itemId).html(total.toFixed(2));
    $('#item_price_total_'+itemId).val(total.toFixed(2));
    
    $('#show_tax_total_'+itemId).html(itemtax.toFixed(2));
    $('#item_tax_total_'+itemId).val(itemtax.toFixed(2));
    
    calculateCart()
}


function calculateCart(){
    
    var cart_sub_total = 0;
    var cart_tax_total = 0;
    
    $('.item_tax_total').each(function(){
        
        cart_tax_total +=  parseFloat($(this).val());
   });
   
    $('.item_price_total').each(function(){
        
        cart_sub_total +=  parseFloat($(this).val());
   });
   
    
    var cart_gross_total = (cart_sub_total + cart_tax_total);
    
    $('#cart_sub_total_show').html(cart_sub_total.toFixed(2));
    $('#cart_tax_total_show').html(cart_tax_total.toFixed(2));
    $('#cart_gross_total_show').html(cart_gross_total.toFixed(2));
    
    $('#cart_sub_total').val(cart_sub_total.toFixed(2));
    $('#cart_tax_total').val(cart_tax_total.toFixed(2));
    $('#cart_gross_total').val(cart_gross_total.toFixed(2));
}

function order_details(transaction_key, user_id, baseurl){
            
    var postData = 'transaction_key=' + transaction_key  + '&user_id=' + user_id;
    var url = baseurl + '/shop/orderDetails';
     
    $.ajax({
            type: 'get',
            url: url,
            data: postData,
            headers : {'Content-Type': 'application/x-www-form-urlencoded'},
            beforeSend: function(){ 
                var alert = '<div class="modal-header alert alert-info"><button type="button" class="close" data-dismiss="modal">&times;</button><div class="overlay"><h1 class="modal-title"><i class="fa fa-refresh fa-spin"></i> Please Wait! Data is loading...</h1></div></div>';
                $("#model_order_details").html(alert);                    
            },
            success: function(data){
                $("#model_order_details").html(data);
            },
            error: function(errormsg){
                console.log(errormsg);
            }
	});
            
}

function clearSearchCategory()
{    
    
    $("#searchCategoryList").html('');
    $("#searchCategory").val('');
     
} 