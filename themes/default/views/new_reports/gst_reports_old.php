<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i>GST Report</h2>
         
<!--        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" class="toggle_up tip" title="<?= lang('hide_form') ?>">
                        <i class="icon fa fa-toggle-up"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="toggle_down tip" title="<?= lang('show_form') ?>">
                        <i class="icon fa fa-toggle-down"></i>
                    </a>
                </li>
            </ul>
        </div>-->
        <div class="box-icon">
            <ul class="btn-tasks">                 
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>                
            </ul>
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">                 
                <li class="dropdown" onclick="print_report('report_print_div')">
                        <i class="icon fa fa-print"></i>                     
                </li>                
            </ul>
        </div>
    </div>
    <!--<p class="introtext"><?= lang('customize_report'); ?></p>-->
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div id="filter_form">                     
                    <div class="row">
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label" for="report_type"><?= lang("Repoty Type"); ?></label>
                                <?php
                                 if(!$this->Owner && !$this->Admin){
                                    $type = [];
                                    if($GP['sales_gst_report']){
                                        $type['sale_gst_report']= "Sale GST Report";
                                    }
                                   if($GP['purchase_gst_report']){
                                        $type['purchase_gst_report'] = "Purchase GST Report";
                                   }
                                }else{

                                    $type = ['sale_gst_report'=>"Sale GST Report", 'purchase_gst_report'=>"Purchase GST Report"];                                 
                                 }                         
                                echo form_dropdown('report_type', $type, (isset($_GET['report_type']) ? $_GET['report_type'] : ""), 'class="form-control load_report" id="report_type" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("report_type") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label" for="sale_items"><?= lang("Sale Items"); ?></label>
                                <?php
                                $sale_items = [ "Hide", "Display"];                                 
                                                             
                                echo form_dropdown('sale_items', $sale_items, (isset($_GET['sale_items']) ? $_GET['sale_items'] : ""), 'class="form-control load_report" id="sale_items" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("sale_items") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("warehouse"); ?></label>
                                <?php
                                $wh[0] = "All Warehouse"; 
                                foreach ($warehouses as $warehous) {
                                    $wh[$warehous->id] = $warehous->name; 
                                }                                
                                echo form_dropdown('warehouse', $wh, (isset($_GET['warehouse']) ? $_GET['warehouse'] : ""), 'class="form-control load_report" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2" id="biller_div">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("biller"); ?></label>
                                <?php                                                            
                                $bi[0] = "All Biller"; 
                                if(!empty($billers)){
                                    foreach ($billers as $biller) {
                                        $bi[$biller->id] = $biller->company; 
                                    }                                
                                }                                
                                echo form_dropdown('biller', $bi, (isset($_GET['biller']) ? $_GET['biller'] : ""), 'class="form-control load_report" id="biller" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("biller") . '"');
                                ?>
                            </div>
                        </div>
                       
                          <div class="col-sm-2" id="customer_div">
                            <div class="form-group">
                                <label class="control-label" for="customer"><?= lang("customer"); ?></label>
                                <?php                                                            
                                $cust[0] = "All Customer"; 
                                foreach ($customers as $customer) {
                                    $cust[$customer->id] = $customer->company; 
                                }                                
                                echo form_dropdown('customer', $cust, (isset($_GET['customer']) ? $_GET['customer'] : ""), 'class="form-control load_report" id="customer" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("customer") . '"');
                                ?>
                            </div>
                        </div>
                       
                        <div class="col-sm-2" id="supplier_div">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("supplier"); ?></label>
                                <?php                                                            
                                $si[0] = "All Supplier";
                                 
                                if(is_array($suppliers)){
                                    foreach ($suppliers as $supplier) {
                                         
                                        $si[$supplier->id] = $supplier->name . '('.$supplier->company.')'; 
                                    }                                
                                }
                                echo form_dropdown('supplier', $si, (isset($_GET['supplier']) ? $_GET['supplier'] : ""), 'class="form-control load_report" id="supplier" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("supplier") . '"');
                                ?>
                            </div>
                        </div>
                         
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("Year"); ?></label>
                                <?php
                                for($y=date('Y'); $y >= (date('Y')-3); $y--){
                                    $year[$y] = $y;   
                                }                                                              
                                echo form_dropdown('report_year', $year, (isset($_GET['report_year']) ? $_GET['report_year'] : ""), 'class="form-control load_report" id="report_year" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("report_year") . '"');
                                ?>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("Month"); ?></label>
                                <?php
                                for($m=1; $m<= 12; $m++){
                                    $mt = $m < 10 ? '0'.$m : $m;
                                    $month[$mt] = date("F", strtotime("$y-$mt-01"));
                                    $last_month = date('m') - 1;
                                }                              
                                echo form_dropdown('report_month', $month, (isset($_GET['report_month']) ? $_GET['report_month'] : $last_month), 'class="form-control load_report" id="report_month" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("report_month") . '"');
                                ?>
                            </div>
                        </div>                        
                       <div class="col-sm-2">
                            <div class="form-group">
                                <label class="control-label" for="user">&nbsp;</label>
                                <input type="button" name="search" value="Search" class="form-control btn btn-primary" id="search_gst_data" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                
                <div id="report_print_div">
                    <table class="table table-bordered " style="margin-bottom: 1px;">
                        <thead><tr>                                                      
                            <th class="bind_type"></th>                            
                            <th class="bind_warehouse"></th>
                            <th class="bind_people"></th>
                            <th class="bind_year"></th>
                            <th class="bind_month"></th>
                            </tr>
                        </thead>
                    </table>
                
                <div class="table-responsive" id="div_report_display">
                    <table id="SlRData" class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th rowspan="2">Sr. No.</th>
                            <th rowspan="2"><?= lang("Invoice_no"); ?></th>
                            <th rowspan="2"><?= lang("date"); ?></th>
                            <th rowspan="2"><?= lang("customer"); ?></th>
                            <th rowspan="2"><?= lang("gstn_number"); ?></th>
                            <th rowspan="2"><?= lang("Taxable Amount"); ?></th>                            
                            <th colspan="2"><?= lang("CGST"); ?></th>
                            <th colspan="2"><?= lang("SGST"); ?></th>
                            <th colspan="2"><?= lang("IGST"); ?></th>
                            <th rowspan="2"><?= lang("Total GST"); ?></th>
                            <th rowspan="2"><?= lang("Invoice Amount"); ?></th>
                        </tr>
                        <tr>                                                      
                            <th>%</th>
                            <th>Amt.</th>
                            <th>%</th>
                            <th>Amt.</th>
                            <th>%</th>
                            <th>Amt.</th>                             
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="14" class="empty_table_body"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th>&nbsp;</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>                             
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        
        $('#supplier_div').hide();
        
        //load_gst_report();
        
        $('#report_type').change(function(){
            
            var type = $('#report_type').val();
            
            if(type == 'sale_gst_report'){
                $('#supplier_div').hide();
                $('#biller_div').show();
                $('#customer_div').show();
            } else {
                $('#biller_div').hide();
                $('#customer_div').hide();
                $('#supplier_div').show();
            }
            
        });
        
        
       /* $('.load_report').change(function(){
            
          load_gst_report();           
            
        });*/
        
        $('#search_gst_data').click(function(){
            
           load_gst_report();
            bind_report_header()
       });
        

        $('#xls').click(function (event) {
          
           var type         = $('#report_type').val();
           var sale_items   = $('#sale_items').val();
           var warehouse    = $('#warehouse').val();
           var biller       = $('#biller').val();
           var customer     = $('#customer').val();
           var supplier     = $('#supplier').val();
           var year         = $('#report_year').val();
           var month        = $('#report_month').val();
            
            event.preventDefault();                           
            window.location.href = "<?=site_url('reports_new/request_gst_report/')?>"+type+'/'+year+'/'+month+'/xls/'+warehouse+'/'+biller+'/'+supplier+'/'+sale_items+'/'+customer;
           
            return false;
            
        });
         
    });

function load_gst_report() {    
    
    var type        = $('#report_type').val();
    var sale_items  = $('#sale_items').val();
    var warehouse   = $('#warehouse').val();   
    var biller      = $('#biller').val();
    var customer       = $('#customer').val();
    var supplier    = $('#supplier').val();
    var year        = $('#report_year').val();
    var month       = $('#report_month').val();
   
    bind_report_header();
    
   var postData = "type="+type+"&year="+year+"&month="+month+'&warehouse='+warehouse+'&biller='+biller+'&supplier='+supplier+'&sale_items='+sale_items+'&customer='+customer;
   
   var postUrl = '<?=site_url('reports_new/request_gst_report')?>';
   
   $.ajax({
        type: "GET",
        url: postUrl,
        data: postData,
        beforeSend: function(){
            $("#empty_table_body").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i></div>");
        },
        success: function(data){			 
            $("#div_report_display").html(data);			 
        }
    }); 
    
}

function bind_report_header(){

    $('.bind_type').html($("#report_type option:selected").html());
    $('.bind_warehouse').html('Warehouse : '+$("#warehouse option:selected").html());
    var people = ($("#report_type").val() == 'sale_gst_report') ? 'Biller: '+ $("#biller option:selected").html() : 'Supplier : '+ $("#supplier option:selected").html() ;
    if($("#report_type").val() == 'sale_gst_report') { $("#sale_items").attr('disabled', false); } else { $("#sale_items").attr('disabled', true); }
    $('.bind_people').html(people);
    $('.bind_year').html('Year: ' + $("#report_year option:selected").html());
    $('.bind_month').html('Month: ' +$("#report_month option:selected").html());
}

function print_report(){    
     
    $('#filter_form').hide();     

    window.print();
     
    $('#filter_form').show();
    
}
    
</script>