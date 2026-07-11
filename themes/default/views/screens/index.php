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
                    <table class="table table-responsive">
                        <thead>
                        <tr>
                            <th class="no-print">Ready</th>
                            <?php if(!empty($pos_settingss->show_current_stock) && (int)$pos_settingss->show_current_stock === 1){ ?>
                                <th>S.no</th>
                                <th>Name</th>
                                <th class="numeric">QTY</th>
                                <th class="numeric">Current stock</th>
                            <?php } else { ?>
                                <th>KOT</th>
                                <th>Product Name</th>
                                <th class="numeric">Qty</th>
                            <?php } ?>
                        </tr>
                        </thead>

                        <tbody>
                        <?php $sr_no = 1; foreach ($list as $key=>$val){ ?>
                            <tr>
                                <td class="no-print">
                                    <input type="checkbox" name="isdelivered" class="checkbox isdelivered"
                                        item_quantity="<?php echo $val->quantity; ?>"
                                        item_id="<?php echo $val->id; ?>">
                                </td>

                                <?php if(!empty($pos_settingss->show_current_stock) && (int)$pos_settingss->show_current_stock === 1){ ?>
                                    <td class="center"><?php echo $sr_no++; ?></td>
                                    <td class="center"><?php echo $val->product_name; ?></td>
                                    <td class="center numeric"><?php echo ($val->quantity-$val->isdelivered); ?></td>
                                    <td class="center numeric"><?php echo isset($val->current_stock) ? (float)$val->current_stock : 0; ?></td>
                                <?php } else { ?>
                                    <td class="center"><?php echo $val->suspend_note." : ".$val->customer; ?></td>
                                    <td class="center"><?php echo $val->product_name; ?></td>
                                    <td class="center numeric"><?php echo ($val->quantity-$val->isdelivered); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>

                    <?php } ?>
<input type="hidden" name="hid_display_token" id="hid_display_token" value="<?= $pos_settingss->display_token; ?>">
<input type="hidden" id="hid_print_all_category" value="<?= $pos_settingss->print_all_category ?>">
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

         window.setTimeout(function(){ window.location.href=window.location.href },(10000));

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

function get_print_html(detail_obect){
        var print_style = "<style>.btn_back{display:inline-block;padding:6px 12px;margin:15px;font-size:14px;font-weight:400;line-height:1.42857143;text-align:center;white-space:nowrap;vertical-align:middle;cursor:pointer;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;background-image:none;border:1px solid #357ebd;border-radius:4px;color:#fff;background-color:#428bca}</style>"
            + "<style>"
            + "table { border-collapse: collapse; width: 100%; }"
            + "th, td { border: 1px solid #333; padding: 4px 6px; }"
            + "#order-table th:first-child, #order-table td:first-child { text-align: center; padding-left: 0; }" /* Sr.no */
            + "#order-table th:nth-child(3), #order-table td:nth-child(3) { text-align: center; }" /* Qty */
            + "#order-table th:nth-child(4), #order-table td:nth-child(4) { text-align: center; }" /* Current stock */
            + ".no-border { border: 0; } .bold { font-weight: bold; }"
            + "</style>";
        var print_html = '<span id="order_span"><span style="text-align:center; display:flex; flex-direction:column; align-items: center; justify-content: center;">';
        

        $.each( detail_obect.group_items, function( key, items ) {
            
            var customer_name = (items[0].customer)? items[0].customer : 'Customer Name';
            var kot_date = printing_pos.get_date();
                <?php if($default_printer->kot_print_site_name){  ?>
                    print_html +='<center><h3 class="text-center">'+detail_obect.settings.site_name+'</h3>';
                <?php } ?>
               if($('#hid_display_token').val()==1){
    if($('#hid_print_all_category').val() == 1){
        print_html +='<strong class="text-center" style="display: block;">Token No.: '+items[0].kot_tokan+'</strong><br/>';
    }
}

                <?php if (in_array($Settings->pos_type , ['restaurant'])) { ?>
                print_html +='<strong class="text-center">'+key+'</strong><br/>';
                <?php } ?>
                <?php if($default_printer->kot_print_customer_name){ ?>
                 print_html +='<strong class="text-center" style="display: block;">'+customer_name+'<br><?= date('d/m/Y g:i A') ?></strong></span></span></center>';
                <?php } else {?>
                 print_html +='<strong class="text-center" style="display: block;">'+customer_name+'<br><?= date('d/m/Y g:i A') ?></strong></span></span></center>';
                <?php } ?>
                <?php if(!empty($pos_settingss->show_current_stock) && (int)$pos_settingss->show_current_stock === 1){ ?>
                print_html +='<table id="order-table" class="prT" style="margin-top:0.3em; margin-bottom:0; margin-left: 0;" width="100%"><thead><tr><th>S.no</th><th>Name</th><th>QTY</th><th>Current stock</th></tr></thead><tbody>';
                <?php } else { ?>
                print_html +='<table id="order-table" class="prT" style="margin-top:0.3em; margin-bottom:0; margin-left: 0;" width="100%"><thead><tr><th>S.no</th><th>Items</th><th>Qty</th></tr></thead><tbody>';
                <?php } ?>
                
                var Oldcat = '';
                let note ;
                var sr_no = 1;
                $.each( items, function( key, p ) {
                    note = p.suspend_note;
                    if(Oldcat!=p.category_id) {
                        <?php if($default_printer->kot_printing_category_name){ ?>
                            <?php if(!empty($pos_settingss->show_current_stock) && (int)$pos_settingss->show_current_stock === 1){ ?>
                                print_html +='<tr><td colspan="4" style="font-weight:bold; font-size:<?= $default_printer->kot_category_font_size ?>px;">'+((p.category_name)?p.category_name :'')+'</td></tr>';
                            <?php } else { ?>
                                print_html +='<tr><td colspan="2" style="font-weight:bold; font-size:<?= $default_printer->kot_category_font_size ?>px;">'+((p.category_name)?p.category_name :'')+'</td></tr>';
                            <?php } ?>
                        <?php } ?>   
                    }
                    print_html +='<tr>';
                        print_html += '<td>'+ (sr_no++) +'</td>';
                    <?php if(!empty($pos_settingss->show_current_stock) && (int)$pos_settingss->show_current_stock === 1){ ?>
                        
                        print_html += '<td><span style="font-size:<?= $default_printer->kot_product_name ?>px;"> • '+p.product_name+'</span>';
                        <?php if($default_printer->kot_printing_combo_product){ ?>
                            print_html +='<br><span style="font-size:<?= $default_printer->kot_sub_product_name ?>px;">'+((p.items)? p.items:'')+'</span>';
                        <?php } ?>
                        print_html +='</td>';
                        print_html += '<td>'+(parseInt(p.quantity)-parseInt(p.isdelivered))+'</td>';
                        print_html += '<td>'+(p.current_stock ? parseFloat(p.current_stock) : 0)+'</td>';
                    <?php } else { ?>
                        print_html +='<td><span style="font-size:<?= $default_printer->kot_product_name ?>px;"> • '+p.product_name+'</span>';
                        <?php if($default_printer->kot_printing_combo_product){ ?>
                            print_html +='<br><span style="font-size:<?= $default_printer->kot_sub_product_name ?>px;">'+((p.items)? p.items:'')+'</span>';
                        <?php } ?>
                        print_html +='</td>';
                        print_html += '<td><span style="font-size:<?= $default_printer->kot_product_name ?>">' + ((parseFloat(p.quantity) - parseFloat(p.isdelivered)).toFixed(2)) + '</span></td>';
                    <?php } ?>
                    print_html +='</tr>';

                    Oldcat = p.category_id;
                });
                
                print_html +='</tbody></table>';
                 print_html += '<div class="ttt" style="text-align: center;">' + (note ? note : "") + '</div>';
                print_html +='<div class="page-break" style="page-break-after: always;"></div>';
                
        });
        return print_style+print_html;
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

            var print_content = get_print_html(data_json);
            console.log('Generated Print HTML:', print_content);
            
            $('body').html(print_content);
           
            window.print();
            
            
            
            $.each( data_json.group_items, function( key, items ) {
                $.each( items, function( key, p ) {
                        var suspend_id = p.id;
                        var item_quantity = p.quantity;
                        var suspend_item_id = p.id;

                        $.get( "<?php echo base_url('screens/delivered/');?>"+suspend_id+"/"+item_quantity, function( data ) {
                        });
                        // delete suspend after print on kitchen printer screen
                        setTimeout(function() {
                            deleteSuspend(suspend_item_id); 
                        }, 5000); //2 min
                });
            });
        });
        function deleteSuspend(suspend_item_id) {

            $.get("<?php echo base_url('screens/deleteSuspendAfterPrint/'); ?>" + suspend_item_id, function(data) {
            }).fail(function(xhr, status, error) {
                console.error("Error deleting suspend:", suspend_item_id, error);
            });
        }
    </script>
<?php } ?>
