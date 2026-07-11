function printBill(bill) {
usePrinter("<?= $pos_settings->receipt_printer; ?>");
        printData(bill);
        }

function printOrder(order) {
for (index = 0; index < printers.length; index++) {
usePrinter(printers[index]);
        printData(order);
        }
}

function paynear_mobile_app() {
$('#paynear_mobile_app').val(1);
        $('#paynear_btn_holder').css("display", 'none');
        $('#paynear_btn_app_holder').css("display", 'block');
        //alert('IN MOBILE APP');
        }

function cardDetails(cart_no, card_name, card_month, card_year, card_cvv, txt) {
txt = GetCardType(cart_no);
        //alert(txt);
        jQuery('#cardNo').html(cart_no);
        //1234-XXXX-XXXX-1234
        jQuery('#pcc_no_1').val(cart_no);
        jQuery('#pcc_no_1').hide();
        jQuery('#pcc_holder_1').val(card_name);
        jQuery('#pcc_holder_1').hide();
        jQuery('#pcc_month_1').val(card_month);
        jQuery('#pcc_month_1').hide();
        jQuery('#pcc_year_1').val(card_year);
        jQuery('#pcc_year_1').hide();
        jQuery('#swipe_1').hide();
        var str = jQuery('#cardNo').html();
        str1 = str.split("");
        var card_split = str1[0] + '' + str1[1] + '' + str1[2] + '' + str1[3] + '-XXXX-XXXX-' + str1[12] + '' + str1[13] + '' + str1[14] + '' + str1[15];
        jQuery('#cardNo').html(card_split);
        var ctype = jQuery('#cardty').html(txt);
        jQuery('#pcc_type_1 option[value=ctype]').attr('selected', 'selected');
        jQuery('#s2id_pcc_type_1').val(txt);
        jQuery('#s2id_pcc_type_1').hide();
        jQuery("#pcc_cvv2_1").css("margin-top", "-65px");
        }

function GetCardType(number) {
var re = new RegExp("^4");
        if (number.match(re) != null) {
return "Visa";
        }
re = new RegExp("^(34|37)");
        if (number.match(re) != null) {
return "American Express";
        }
re = new RegExp("^5[1-5]");
        if (number.match(re) != null) {
return "MasterCard";
        }
re = new RegExp("^6011");
        if (number.match(re) != null) {
return "Discover";
        }
return "unknown";
        }

function getQRCode(fullURL) {
param = fullURL.split('/');
        addItemTest(param[param.length - 1]);
        }

function actQRCam() {
window.MyHandler.activateQRCam(true);
        return false;
        }

function setCustomerGiftcard(bal, sel) {
var today = '<?= date('Y - m - d') ?>';
        var cus = $('#poscustomer').val();
        $.ajax({
        type: "get",
                url: "<?= site_url('pos/searchGiftcardByCustomer') ?>",
                data: {customer_id: cus, bill_amt: bal},
                dataType: "json",
                success: function (data) {

                if (data.card_no !== null && data.balance > 0) {
                if (today > data.expiry) {
                bootbox.alert('<?= lang('Gift card number is incorrect or expired.') ?>');
                } else {
                $('#gift_card_no_1').val(data.card_no);
                        $('#gc_details_1').html('<small>Card No: ' + data.card_no + '<br>Value: ' + data.value + ' - Balance: ' + data.balance + '</small>');
                        $('#gift_card_no_1').parent('.form-group').removeClass('has-error');
                        //calculateTotals();
                        //$('#amount_1').val(ba >= data.balance ? data.balance : ba).focus();
                        //$('#amount_1').val(ba).focus();
                        $('#paying_gift_card_no_val_1').val(data.card_no);
                        if (bal > parseFloat(data.balance)) {
                $('#errorgift_1').html('<small class="red">Amount Greater than Gift Card</small>');
                        $('.final-submit-btn').prop('disabled', true);
                        if (sel == 'gift_card') {
                bootbox.alert('Invoice amount is greater that available gift card balance please select other payment mode');
                }

                }
                }
                } else {
                $('#gift_card_no_1').val('');
                        $('#paying_gift_card_no_val_1').val('');
                        $('#amount_1').val('');
                        $('#gc_details_1').html('<small class="red">Giftcard not found for this customer</small>');
                        $('#gift_card_no_1').parent('.form-group').removeClass('has-error');
                        //bootbox.alert('<?= lang('gift_card_not_for_customer') ?>');
                }
                }
        });
        }

function setCustomerDeposit(ba, sel) {
var today = '<?= date('Y - m - d') ?>';
        var cu = $('#poscustomer').val();
        $.ajax({
        type: "get",
                url: "<?= site_url('pos/searchDepositByCustomer') ?>",
                data: {customer_id: cu, bill_amt: ba},
                dataType: "json",
                success: function (data) {

                if (data.balanceamt > 0) {
                //$('#amount_1').val(ba).focus();
                $('#depositdetails_1').html('<small>Value: ' + data.value + ' - Balance: ' + data.balanceamt + '</small>');
                        if (ba > parseFloat(data.balanceamt)) {
                $('#errordeposit_1').html('<small class="red">Amount Greater than Deposit</small>');
                        $('.final-submit-btn').prop('disabled', true);
                        if (sel == 'deposit') {
                bootbox.alert('Invoice amount is greater that available Deposit balance please select other payment mode');
                }

                }
                } else {
                $('#depositdetails_1').html('<small class="red">Deposit not found for this customer</small>');
                }
                }
        });
        }

function setCustomerName(valu) {
$('#custname').val(valu);
        $('#custname').prop('name', 'customer');
        }

function addItemTest(itemId) {
$('#modal-loading').show();
        var code;
        $.ajax({
        type: "get", //base_url("index.php/admin/do_search")
                url: "<?= site_url('pos/getProductByID') ?>",
                data: {id: itemId},
                dataType: "json",
                success: function (data) {
                code = data.code;
                        code = code,
                        wh = $('#poswarehouse').val(),
                        cu = $('#poscustomer').val();
                        $.ajax({
                        type: "get",
                                url: "<?= site_url('pos/getProductDataByCode') ?>",
                                data: {code: code, warehouse_id: wh, customer_id: cu},
                                dataType: "json",
                                success: function (data) {
                                if (data !== null) {
                                add_invoice_item(data);
                                        $('#modal-loading').hide();
                                } else {
                                bootbox.alert('<?= lang('no_match_found') ?>');
                                        $('#modal-loading').hide();
                                }
                                }
                        });
                }
        });
        }

function isNumberKey(evt) {
var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
return false;
        } else {
return true;
        }
}

function validCVV(cvv) {
var re = /^[0-9]{3,4}$/;
        return re.test(cvv);
        }

function validYear(year) {
var re = /^(19|20)\d{2}$/;
        return re.test(year);
        }

function Rfid() {

$.get('https://simplypos.in/api/rfid/?get=<?php echo site_url(); ?>', function (data) {
data3 = data.split(':');
        $.each(data3, function (index, value) {
        data4 = value.split('A');
                addItemByProductCode(data4[1]);
        });
        });
        }

function addItemByProductCode(code) {

code = code,
        wh = $('#poswarehouse').val(),
        cu = $('#poscustomer').val();
        $.ajax({
        type: "get",
                url: "<?= site_url('pos/getProductDataByCode') ?>",
                data: {code: code, warehouse_id: wh, customer_id: cu},
                dataType: "json",
                success: function (data) {
                if (data !== null) {
                add_invoice_item(data);
                        $('#modal-loading').hide();
                } else {
                bootbox.alert('<?= lang('no_match_found') ?>');
                        $('#modal-loading').hide();
                }
                }
        });
        }

var specialKeys = new Array();

function IsNumeric(e) {
        var keyCode = e.which ? e.which : e.keyCode
                var ret = ((keyCode >= 48 && keyCode <= 57) || specialKeys.indexOf(keyCode) != - 1);
                document.getElementById("error").style.display = ret ? "none" : "inline";
                return ret;
        }

function call_checkout() {

localStorage.setItem('staffnote', $("#reference_note").val());
        $("#payment").trigger('click');
        }

function enDis(idName) {
var txt = jQuery('#' + idName).attr('readonly');
        if (txt == 'readonly') {
jQuery('#' + idName).attr('readonly', false);
        } else {
jQuery('#' + idName).attr('readonly', 'readonly');
        }
}

function addProductToVarientProduct(option_id, option_name) {

var note = '';
        if (option_name.toLowerCase() == 'note') {

note = prompt("Please enter your note");
        if (note == null) {
return false;
        }
}

var itemId = $(".modalvarient").find('.product_item_id').attr("value")
        //var option_id = $(".modalvarient").find('.option_id').val();
        var term = $(".modalvarient").find('.product_term').val() + "<?php echo $this->Settings->barcode_separator; ?>" + option_id;
        wh = $('#poswarehouse').val(),
        cu = $('#poscustomer').val();
        $.ajax({
        type: "get",
                url: "<?= site_url('sales/suggestions') ?>",
                data: {term: term, option_id: option_id, warehouse_id: wh, customer_id: cu, option_note: note},
                dataType: "json",
                success: function (data) {
                if (data !== null) {
                add_invoice_item(data[0]);
                        $('.modalvarient').hide();
                } else {
                bootbox.alert('<?= lang('no_match_found') ?>');
                        $('.modalvarient').hide();
                }
                }
        });
        }

function product_option_model_call(product) {

var subdomain = new URL(window.location.href).hostname.split('.')[0];
var product_options = '';
        product_options = "" +
        "<div class='row'>" +
        "<div class='col-sm-12'>";
        $.each(product.options, function (index, element) {

        if (element.name.toLowerCase() == 'note') {
        product_options += '</div><div style="clear:both"></div></div><div class="note-btn"><button onclick="addProductToVarientProduct(\'' + element.id + '\',\'' + element.name + '\')"><i class="fa fa-pencil" id="addIcon" style="font-size: 1.2em;"></i>Note</button></div>';
        } else {
        product_options += '<button onclick="addProductToVarientProduct(\'' + element.id + '\',\'' + element.name + '\')" type="button"  title="' + element.name + '" class="btn-prni btn-info pos-tip" tabindex="-1"><img src="assets/mdata/' +subdomain+'/uploads/thumbs/no_image.png" alt="' + element.name + '" style="width:33px;height:33px;" class="img-rounded"><span>' + element.name + '</span></button>';
        }
        });
        product_options += "<input type='hidden' class='product_item_id' name='product_item_id' value='" + product.row.id + "' >";
        product_options += "<input type='hidden' class='product_term' name='product_term' value='" + product.row.code + "' >";
        $('.modalvarient').find('.modal-title').html(product.row.name);
        $('.modalvarient').find('.modal-body').empty();
        $('.modalvarient').find('.modal-body').append(product_options);
        $('.modalvarient').show();
        return true;
        }

function modalClose(modalClass) {
$('.' + modalClass).hide();
        }

function change_offerdetails(offer) {
$.ajax({
type: "ajax",
        dataType: 'json',
        method: 'get',
        url: "pos/change_offerdetails/" + offer,
        success: function (result) {
        if (result) {
        $('#offermsg').hide();
                document.getElementById('offer_id').value = result.id;
                document.getElementById('offer_name').value = result.offer_name;
                //alert( document.getElementById('offer_name').value);
                document.getElementById('offer_amount_including_tax').value = result.offer_amount_including_tax;
                document.getElementById('offer_discount_rate').value = result.offer_discount_rate;
                document.getElementById('offer_end_date').value = result.offer_end_date;
                document.getElementById('offer_end_time').value = result.offer_end_time;
                document.getElementById('offer_free_products').value = result.offer_free_products;
                document.getElementById('offer_free_products_quantity').value = result.offer_free_products_quantity;
                document.getElementById('offer_items_condition').value = result.offer_items_condition;
                document.getElementById('offer_on_brands').value = result.offer_on_brands;
                document.getElementById('offer_on_category_quantity').value = result.offer_on_category_quantity;
                document.getElementById('offer_on_days').value = result.offer_on_days;
                document.getElementById('offer_on_invoice_amount').value = result.offer_on_invoice_amount;
                document.getElementById('offer_on_products').value = result.offer_on_products;
                document.getElementById('offer_on_products_amount').value = result.offer_on_products_amount;
                document.getElementById('offer_on_products_quantity').value = result.offer_on_products_quantity;
                document.getElementById('offer_on_warehouses').value = result.offer_on_warehouses;
                document.getElementById('offer_start_date').value = result.offer_start_date;
                document.getElementById('offer_start_time').value = result.offer_start_time;
                console.log(result);
        }

        }, error: function () {
console.log('error');
        }
});
        }

function setPrintRequestData(print_data) {
//alert('--Store to handler---');
if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
//alert(print_data);
if (localStorage.getItem('positems')) {
var data = '{"table_number":"","customerName":"' + $.trim($("#s2id_poscustomer").text()) + '","total":"' + $.trim($("#total").text()) + '","tax":"' + $.trim($("#ttax2").text()) + '","discount":"' + localStorage.getItem('posdiscount') + '","items": [' + localStorage.getItem('positems') + ']  }';
        //alert(data);
        var pos_item_string = data;
        //alert(pos_item_string);
        window.MyHandler.setPrintRequestPos(pos_item_string);
        } else {
//var pos_item_string = JSON.stringify(localStorage.getItem('positems'));
//alert('---data not found---');
window.MyHandler.setPrintRequestPos('{status:"false"}');
        }
}
}

// get customer
function getCustomer(fieldkey, mobile_no) {
var pass_data = fieldkey + '=' + mobile_no;
        $.ajax({
        type: 'get',
                dataType: 'json',
                data: pass_data,
                url: site.base_url + 'pos/get_dependancy',
                async: false,
                success: function (data) {
                if (data != null) {
                //if(data.name =='Walk-in Customer name'){
                //  document.getElementById('customer_name').value= data.name; 
                //}else{
                document.getElementById('customer_name').value = data.name + '(' + data.phone + ')';
                        //}
                        document.getElementById('poscustomer').value = data.id;
                        document.getElementById('custname').value = data.id;
                        localStorage.setItem('poscustomer', data.id);
                        //                             console.log(localStorage);
                } else {

                bootbox.alert('Number not registered, to <a href="customers/add/quick?mobile_no=' + mobile_no + '" id="add-customer"  class="external" data-toggle="ajax"  tabindex="-1"> add new customer click here</a>');
                }
                }
        });
        }

//It should be number not registered , to add new customer click here
function searchCustomer() {
var mobile_no = $('#search_customer').val();
        if (mobile_no == '') {
bootbox.alert('Please Enter Mobile Number');
        $('#search_customer').focus();
        } else {
getCustomer('phone', mobile_no);
        }

}


function validation() {
var getkey = arguments[0]; // Get Key Typr
        var getvalue = arguments[1]; // get Value Type
        var getID = arguments[2];
        switch (getkey) {
case 'mobile':
        var filter = /^((\+[1-9]{1,4}[ \-]*)|(\([0-9]{2,3}\)[ \-]*)|([0-9]{2,4})[ \-]*)*?[0-9]{3,4}?[ \-]*[0-9]{3,4}?$/;
        if (!getvalue == ' ') {
if (filter.test(getvalue)) {
if (getvalue.length == 10) {
return true;
        } else {
showerrorMsg('Please put 10  digit mobile number');
        boxFocus(getID)
        return false;
        }
} else {
showerrorMsg('Not a valid number');
        boxFocus(getID)
        return false;
        }
} else {
showerrorMsg('Please enter mobile number');
        boxFocus(getID)
        return false;
        }
break;
        case 'name':
        var nameRegex = /^[a-zA-Z \-]+$/;
        if (!getvalue == ' ') {
if (nameRegex.test(getvalue)) {
if (getvalue.length >= 1) {
return true;
        } else {
showerrorMsg('Please put Min 4 character');
        boxFocus(getID)
        return false;
        }

} else {
showerrorMsg('Not a valid name');
        boxFocus(getID)
        return false;
        }
} else {
showerrorMsg('Please enter customer name');
        boxFocus(getID)
        return false;
        }
break;
        default:

        break;
        }
}

function boxFocus() {
document.getElementById(arguments[0]).focus();
        }

function showerrorMsg(msg) {
document.getElementById('errormsg').style.display = 'block';
        $('#error_msg').html(msg);
        setTimeout(function () {
        $('#errormsg').hide();
                $('#error_msg').html('');
        }, 3000)

        }

function change_theme(theme) {
$.ajax({
type: "ajax",
        dataType: 'json',
        method: 'get',
        url: "pos/change_theme/" + theme,
        success: function (result) {
        if (result == "TRUE") {
        location.reload();
        }
        // console.log(result);
        }, error: function () {
console.log('error');
        }
});
        }
