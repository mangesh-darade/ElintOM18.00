<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $warehouses_id;

$permisions_werehouse = explode(",", $warehouses_id);
// $warehouse_id = $warehouses_id;

$v = "";
/* if($this->input->post('name')){
  $v .= "&product=".$this->input->post('product');
  } */
if ($this->input->post('product')) {
    $v .= "&product=" . $this->input->post('product');
}

if($this->input->post('category'))
{
    $v .= "&category=" . $this->input->post('category');
}

if($this->input->post('subcategory'))
{
    $v .= "&subcategory=" . $this->input->post('subcategory');
}
if($this->input->post('brand'))
{
    $v .= "&brand=" . $this->input->post('brand');
}

if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}else{
    $v .=($user_warehouse=='0' ||$user_warehouse==NULL)?'':"&warehouse=" . str_replace(",", "_",$user_warehouse);
}
if ($this->input->post('user')) {
    $v .= "&user=" . $this->input->post('user');
}
if($this->input->post('start_date'))
{
    $v .= "&start_date=" . $this->input->post('start_date');
}
if($this->input->post('end_date'))
{
    $v .= "&end_date=" . $this->input->post('end_date');
}

?>
<style>
    thead th:nth-child(2) .yadcf-filter-wrapper input {
    text-align: LEFT;
}
</style>
<script>
    $(document).ready(function () {
        var oTable = $('#SlRData').dataTable({
            "aaSorting": [[6, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/gettransfer_request/?v=1'. $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({
                    'dataType': 'json', 
                    'type': 'POST', 
                    'url': sSource, 
                    'data': aoData, 
                    'success': fnCallback,
                    'error': function(xhr, error, thrown) {
                        console.log('DataTables error:', xhr.responseText);
                        console.log('Thrown:', thrown);
                        alert('Error loading data. Check console for details.');
                    }
                });
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
             $( nRow ).find('td:eq(2)').addClass('noprint');
                $( nRow ).find('td:eq(3)').addClass('noprint');
                // Center align quantity and warehouse quantity columns
                $(nRow).find('td:gt(4)').addClass('text-center');
            },
            "aoColumns": [
                {"bSortable": true, "bSearchable": true},   // Code
                {"bSortable": true, "bSearchable": true},   // Product Name
                {"bSortable": true, "bSearchable": true},   // Variant
                {"bSortable": true, "bSearchable": true},   // Category
                {"bSortable": true, "bSearchable": true},   // Sub Category
                {"bSortable": true, "bSearchable": true},   // Brand

                {   // Total Request Qty
                    "bSortable": true,
                    "bSearchable": false,
                    "mRender": function (data) {
                        return formatQuantity(data);
                    }
                }
                <?php foreach($warehouses as $itemwarehouse) { ?>
                ,{
                    "bSortable": false,
                    "bSearchable": false,
                    "mRender": function (data) {
                        return formatQuantity(data);
                    }
                }
                <?php } ?>
            ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {

                var totalCols = aaData[0] ? aaData[0].length : 0;

                // start from Total Request Qty column index = 6
                for (var col = 6; col < totalCols; col++) {

                    var colTotal = 0;

                    for (var i = 0; i < aiDisplay.length; i++) {
                        var val = aaData[aiDisplay[i]][col];

                        // remove commas if any
                        val = (val + '').replace(/,/g, '');
                        colTotal += parseFloat(val) || 0;
                    }

                    var footerCell = nRow.getElementsByTagName('th')[col];
                    if (footerCell) {
                        footerCell.innerHTML =
                            '<strong>' + formatQuantity(colTotal) + '</strong>';
                    }
                }
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 0, filter_default_label:"Code",filter_type: "text", data: []},  // Code
            {column_number: 1, filter_default_label:"Product Name",filter_type: "text", data: []},  // Product Name
            {column_number: 2, filter_default_label:"Variant",filter_type: "text", data: []},  // Variant
            {column_number: 3, filter_default_label:"Category",filter_type: "text", data: []},  // Category
            {column_number: 4, filter_default_label:"Sub Category",filter_type: "text", data: []},  // Sub Category
            {column_number: 5, filter_default_label:"Brand",filter_type: "text", data: []}   // Brand
        ], "footer");
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#form').hide();
                $('.toggle_down').click(function () {
            $("#form").slideDown();
            return false;
        });
        $('.toggle_up').click(function () {
            $("#form").slideUp();
            return false;
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        // $('#category').select2({allowClear: true, placeholder: "<?= lang('select'); ?>", minimumResultsForSearch: 7}).select2('destroy');
        $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            allowClear: true,
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
        $('#category').change(function () {
            var v = $(this).val();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        if (scdata != null) {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                allowClear: true,
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        } else {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                                allowClear: true,
                                placeholder: "<?= lang('no_subcategory') ?>",
                                data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                    }
                });
            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    allowClear: true,
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
        });
        <?php if (isset($_POST['category']) && ! empty($_POST['category'])) { ?>
        $.ajax({
            type: "get", async: false,
            url: "<?= site_url('products/getSubCategories') ?>/" + <?= $_POST['category'] ?>,
            dataType: "json",
            success: function (scdata) {
                if (scdata != null) {
                    $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                        allowClear: true,
                        placeholder: "<?= lang('no_subcategory') ?>",
                        data: scdata
                    });
                }
            }
        });
        <?php } ?>
    });
</script>


<style>
    
    @media print {
         .noprint {
display:none !important;
               }
         .printdata{display: block !important;} 
        
    }
     
</style>    
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('Transfer Request Report'); ?> <?php
            if ($this->input->post('start_date')) {
                echo "From " . $this->input->post('start_date') . " to " . $this->input->post('end_date');
            }
            ?>
        </h2>
        
        <div class="box-icon">
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
        </div>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="#" id="pdf" class="tip" title="<?= lang('download_pdf') ?>">
                        <i class="icon fa fa-file-pdf-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <span class="tip" style="color: #5993cb;"  onclick="myprint()" /><i class=" icon fa fa-print"></i> </span>
<!--                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>-->
                </li>
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('customize_report'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                

                <div id="form">

                    <?php echo form_open("reports/transfer_request"); ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("product", "suggest_product"); ?>
                                <?php echo form_input('sproduct', (isset($_POST['sproduct']) ? $_POST['sproduct'] : ""), 'class="form-control" id="suggest_product"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : "" ?>" id="report_product_id"/>
                            </div>
                        </div>
                        

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = lang('select') . ' ' . lang('category');
                                foreach($categories as $category)
                                {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang("subcategory", "subcategory") ?>
                                <div class="controls" id="subcat_data"> <?php
                                    echo form_input('subcategory', (isset($_POST['subcategory']) ? $_POST['subcategory'] : ''), 'class="form-control" id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("brand", "brand") ?>
                                <?php
                                $bt[''] = lang('select') . ' ' . lang('brand');
                                foreach($brands as $brand)
                                {
                                    $bt[$brand->id] = $brand->name;
                                }
                                echo form_dropdown('brand', $bt, (isset($_POST['brand']) ? $_POST['brand'] : ''), 'class="form-control select" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $permisions_werehouse = explode(",", $user_warehouse);
                                $wh[""] = lang('select') . ' ' . lang('warehouse');
                                foreach($all_warehouses as $warehouse)
                                {
                                	if($Owner || $Admin ){
                                            $wh[$warehouse->id] = $warehouse->name;
                                        }else if(in_array($warehouse->id,$permisions_werehouse)){
                                           $wh[$warehouse->id] = $warehouse->name;
                                        }    
                                }
                                echo form_dropdown('warehouse', $wh, (isset($_POST['warehouse']) ? $_POST['warehouse'] : ""), 'class="form-control" id="warehouse" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("warehouse") . '"');
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group choose-date hidden-xs">
                                <div class="controls">
                                    <?= lang("date_range", "date_range"); ?>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text"
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] . '-' . $_POST['end_date'] : ""; ?>"
                                               id="daterange_new" class="form-control">
                                        <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
                                        <input type="hidden" name="start_date" id="start_date"
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ""; ?>">
                                        <input type="hidden" name="end_date" id="end_date"
                                               value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ""; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="user"><?= lang("created_by"); ?></label>
                                <?php
                                $us[""] = lang('select').' '.lang('user');
                                foreach ($users as $user) {
                                    $us[$user->id] = $user->first_name . " " . $user->last_name;
                                }
                                echo form_dropdown('user', $us, (isset($_POST['user']) ? $_POST['user'] : ""), 'class="form-control" id="user" data-placeholder="' . $this->lang->line("select") . " " . $this->lang->line("user") . '"');
                                ?>
                            </div>
                        </div>
                        
                    </div>
                    
                    
                    <div class="form-group">
                        <div class="controls"> 
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            
                            <input type="hidden" id="report_reset" data-value="<?=base_url('reports/transfer_request');?>" name="submit_report" value="Reset" >
                            <a href="reports/restbutton" class="btn btn-success"  onClick="resetFunction();">Reset</a> 
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>
                <div class="clearfix"></div>

                <div class="table-responsive ">
                    <h2 class="printdata text-center" style="display:none"> Transfer Request Report </h2>
                    <table id="SlRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("Code"); ?></th>
                            <th><?= lang("Product Name"); ?></th>
                            <th><?= lang("Variant"); ?></th>
                            <th class="noprint"><?= lang("Category"); ?></th>
                            <th class="noprint"><?= lang("Sub Category"); ?></th>
                            <th class="noprint"><?= lang("Brand"); ?></th>
                            <th><?= lang("Total Request Qty"); ?></th>
                            <?php
                             $col =7;
                            foreach($warehouses as $itemwarehouse){ ?>
                              <th><?= $itemwarehouse->name ?></th>  
                            <?php $col++; } ?>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="<?= $col ?>" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="noprint"></th>
                            <th class="noprint"></th>
                            <th class="noprint"></th>
                            <th></th>
                             <?php
                            
                            foreach($warehouses as $itemwarehouse){ ?>
                              <th><?= $itemwarehouse->name ?></th>  
                            <?php  } ?>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/gettransfer_request/pdf/?v=1'.$v)?>";
            return false;
         });

        $('#xls').click(function (event) {         
            event.preventDefault();          
            window.location.href = "<?=site_url('reports/gettransfer_request/0/xls/?v=1'.$v)?>";
            return false;
        });

        $('#image').click(function (event) {
            event.preventDefault();
			  window.location.href = "<?=site_url('reports/gettransfer_request/0/0/img/?v=1'.$v)?>";
			
            html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    var myImage = canvas.toDataURL("image/png");
				
                }
            });
            return false;
        });
    });

    function resetFunction(){
       $('form#search-form input[type=hidden].search-value').val('');
     // location.reload(true);
    }
    
    
  function myprint() {
    window.print();
//    exit;

  }

  
</script>
