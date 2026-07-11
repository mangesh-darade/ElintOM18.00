<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
    @media screen {
        .KOT_TITLE {
            display: none;
        }
    }
    @media print
    {
        .KOT_TITLE{
            display: block;
        }
    
    .page-break {
        page-break-after: always;
        }

    }
    
        @media screen {
            .print_area{
                display: none;
                width: 100%;
            }
            
        }
        @media print
        {
            .print_area{
                display: block;
                width: 100%;
            }

            .window-size{
                height:400px !important;
                width:400px !important;
            }
        }
    
</style>

<div class="box">
    <div class="box-header no-print">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?=lang('Division')." : ".$division->name; ?></h2>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#"  id="print-icon" class="tip print_btn" title="<?= lang('print') ?>">
                        <i class="icon fa fa-print"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12 window-size">
                <!--p class="introtext"><?= lang('list_results'); ?></p-->


                    <?php if(strtolower($division->name) == '5 ptint'){ ?>
                        <div class="no-print">
                            This screen Will be refresh after every 0.30 seconds for print or reload this page to take instent print.
                        </div><br>
                        <div class="print_area">
                        <h2 class='KOT_TITLE text-center'>KOT ITEMS</h2>
                        <table  class="table" width="100%" >
                            <thead>
                            <tr>
                                <th>KOT</th>
                                <th>Product Name</th>
                                <th class="numeric">Qty</th>
                            </tr>
                            </thead>
                            <tbody>

                                <?php foreach ($list as $key=>$val){ ?>
                                <tr>
                                    <td class="center" data-title="Code"><?php echo $val->suspend_note." : ".$val->customer; ?></td>
                                    <td class="center" data-title="Company"><?php echo $val->product_name; ?></td>
                                    <td class="center" data-title="Price" class="numeric"><?php echo ($val->quantity-$val->isdelivered); ?></td>
                                </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                        </div>

                   <?php }else{ ?>
                        <h2 class='KOT_TITLE text-center'>KOT ITEMS</h2>
                    <table  class="table table-responsive" >
                        <thead>
                        <tr>
                            <th class="no-print">Ready</th>
                            <th>KOT</th>
                            <th>Product Name</th>
                            <th class="numeric">Qty</th>

                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($list as $key=>$val){ ?>
                            <tr>
                                <td class="no-print"><input type="checkbox" name="isdelivered"  class="checkbox isdelivered" item_quantity="<?php echo $val->quantity; ?>" item_id="<?php echo $val->id; ?>"></td>
                                <td class="center" data-title="Code"><?php echo $val->suspend_note." : ".$val->customer; ?></td>
                                <td class="center" data-title="Company"><?php echo $val->product_name; ?></td>
                                <td class="center" data-title="Price" class="numeric"><?php echo ($val->quantity-$val->isdelivered); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?php } ?>
<input type="hidden" name="hid_display_token" id="hid_display_token" value="<?= $pos_settingss->display_token; ?>">
            </div>
        </div>
    </div>
</div>


<script>
    $(function(){
        $(document).on('ifUnchecked ifChecked', '.checkbox', function(this_element) {
            
            var this_element = this;
            if(this_element.checked){
               
                var suspend_id = this_element.attributes.item_id.value;
                var item_quantity = this_element.attributes.item_quantity.value;

                $.get( "<?php echo base_url('screens/delivered/');?>"+suspend_id+"/"+item_quantity, function( data ) {
                    this_element.closest('tr').remove();
                });
            }
        });

        // Hook up the print link.
        $(".print_btn" ).attr( "href", "javascript:void( 0 )" ).click(
                function(){
                     location.reload();
                });

         window.setTimeout(function(){ window.location.href=window.location.href },(5000));

    });

</script>

<script>
var printing_pos = {
        print:function(data,action){
            var mywindow = window.open('', 'sma_pos_print', 'height=500,width=300');
            mywindow.document.write('<html><head><title>KOT</title><style>@media screen,print {    .btn { display:none }  }</style>');
            mywindow.document.write('<link rel="stylesheet" href="<?=$assets?>styles/helpers/bootstrap.min.css" type="text/css" />');
            mywindow.document.write('</head><body >');
            mywindow.document.write(data);
            //mywindow.document.write('<script>'+'  setTimeout(function(){ window.print(); window.close();this.checkChild();  }, 100); </'+'script>');
            mywindow.document.write('</body></html>');

             
                mywindow.print(); 

        },
        checkChild:function(){
            $("#suspend_sale").trigger('click');
            $('.kot_tbl').empty();
        },
        get_date:function(){
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();

            var th = today.getHours();
            var tm = today.getMinutes();
            var ts = today.getSeconds();
            
            if(dd<10){
                dd='0'+dd;
            } 
            if(mm<10){
                mm='0'+mm;
            } 
            var today = dd+'/'+mm+'/'+yyyy+' '+th+':'+tm+':'+ts;
            return  today;
        }
        

    };

function get_print_html(detail_object) {
    var print_style = "<style>.btn_back{display:inline-block;padding:6px 12px;margin:15px;font-size:14px;font-weight:400;line-height:1.42857143;text-align:center;white-space:nowrap;vertical-align:middle;cursor:pointer;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background-image:none;border:1px solid #357ebd;border-radius:4px;color:#fff;background-color:#428bca}</style><style>table, th, td { border-collapse:collapse; border-bottom: 1px solid #CCC; } .no-border { border: 0; } .bold { font-weight: bold; }</style>";
    var print_html = '<span id="order_span"><span style="text-align:center; display:flex; flex-direction:column; align-items: center; justify-content: center;">';

    $.each(detail_object.group_items, function(key, items) {
        var item = items[0];
        var company_name = item.company_company ? item.company_company : item.company_name;
        var company_phone = item.company_phone || '';
        var order_type = item.order_type || 'Order';
        var customer_name = item.customer ? item.customer : 'Customer Name';
        var current_date = new Date();
        var formatted_date = (current_date.getMonth() + 1) + '/' + current_date.getDate() + '/' + current_date.getFullYear().toString().substr(-2) + ', ' + current_date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

        <?php if($default_printer->kot_print_site_name){ ?>
            print_html += '<center><h3 class="text-center">' + detail_object.settings.site_name + '</h3>';
        <?php } ?>

        
        if ($('#hid_display_token').val() == 1) {
            print_html += '<strong class="text-center">KOT No.: ' + item.kot_tokan + '</strong><br/>';
        }
        <?php if($default_printer->kot_print_customer_name){ ?>
            print_html += '<strong class="text-center">' + customer_name + '<br>' + formatted_date + '</strong></span></span></center>';
        <?php } else { ?>
            print_html += '<strong class="text-center">' + customer_name + '<br>' + formatted_date + '</strong></span></span></center>';
        <?php } ?>
        
        if (company_name.toLowerCase() !== 'walk-in customer') {
            let full_address = '';
            if (item.company_address) full_address += item.company_address + '';
            if (item.company_city) full_address += item.company_city + ' ';
            full_address = full_address.trim();
            print_html += '<div style="text-align:center;">';
            if (full_address) print_html += full_address + '.<br/>';
            if (company_phone) print_html += 'Tel: ' + company_phone + '<br/>';
            print_html += '</div>';
        }

        // Show order type
        print_html += '<div style="margin-top:5px;text-align:center;">';
        print_html += '<strong>Order Type:</strong> ' + order_type + '<br/>';
        print_html += '</div>';

        

        if (key && key.toLowerCase() !== '') {
            print_html += '<strong class="text-center">' + key + '</strong><br/>';
        }

        print_html += '<table id="order-table" class="prT" style="margin-top:0.3em; margin-bottom:0;" width="100%"><thead><tr><th>Items</th><th>Qty</th></tr></thead><tbody>';

        var Oldcat = '';
        let note ;
        $.each(items, function(key, p) {
            note = p.suspend_note;

            if (Oldcat != p.category_id) {
                <?php if($default_printer->kot_printing_category_name){ ?>
                    print_html += '<tr><td colspan="2" style="font-weight:bold; font-size:<?= $default_printer->kot_category_font_size ?>">' + (p.category_name ? p.category_name : '') + '</td></tr>';
                <?php } ?>
            }

            print_html += '<tr class="row_264" data-item-id="264">';
            print_html += '<td><span style="font-size:<?= $default_printer->kot_product_name ?>"> • ' + p.product_name + '</span><br>';

            <?php if($default_printer->kot_printing_combo_product){ ?>
                print_html += '<span style="font-size:<?= $default_printer->kot_sub_product_name ?>">' + (p.items ? p.items : '') + '</span>';
            <?php } ?>

            print_html += '</td>';
            print_html += '<td><span style="font-size:<?= $default_printer->kot_product_name ?>">' + ((parseFloat(p.quantity) - parseFloat(p.isdelivered)).toFixed(2)) + '</span></td>';
            print_html += '</tr>';

            Oldcat = p.category_id;
        });

        print_html += '</tbody></table>';
        print_html += '<div class="ttt" style="text-align: center;">' + (note ? note : "") + '</div>';
        print_html += '<div class="page-break" style="page-break-after: always;"></div>';


    });
    
    return print_style + print_html;
}



    var data_json =  <?php echo $data_json; ?>;
    
console.log(data_json);
    
</script>



<?php if(count($list) > 0){ ?>
    <script>
        $(function(){
            
            var old_html = $('.col-lg-12').html();

      old_html = '' +
        '<style type="text/css">' +
        'body {' +
        'border:1px solid #000;' +
        'margin:0em;' +
        'padding:0.5em;' +
        '}' +
        '</style>'+old_html ;


            $('body').html(get_print_html(data_json));
           
            window.print();
            
            
            
                $.each( data_json.group_items, function( key, items ) {
                    $.each( items, function( key, p ) {
                            var suspend_id = p.id;
                            var item_quantity = p.quantity;

                            $.get( "<?php echo base_url('screens/delivered/');?>"+suspend_id+"/"+item_quantity, function( data ) {
                                
                            });
                    });
                });
            
            
     
            
        });
    </script>
<?php } ?>
