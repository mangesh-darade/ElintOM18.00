<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$user_warehouse = $this->session->userdata('warehouse_id');
$v = "";

if ($this->input->post('category')) {
    $v .= "&category=" . $this->input->post('category');
}
if ($this->input->post('warehouse')) {
    $v .= "&warehouse=" . $this->input->post('warehouse');
}else{
    $v .=($user_warehouse=='0' ||$user_warehouse==NULL)?'': "&warehouse=" . str_replace(",", "_",$user_warehouse);
}
if ($this->input->post('start_date')) {
    $v .= "&start_date=" . $this->input->post('start_date');
}
if ($this->input->post('end_date')) {
    $v .= "&end_date=" . $this->input->post('end_date');
}
?>
<style>
    thead th:nth-child(8) .yadcf-filter-wrapper input{
        text-align: right;
    }

    tbody td:nth-child(8){
        text-align:right;
    }
</style>
<script>
    $(document).ready(function () {
		/*var pQty = 0, sQty = 0, pAmt = 0, sAmt = 0, pl = 0;
        var oTable = $('#PrRData').dataTable({
            "aaSorting": [[0, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/getCategoriesDetailReport/?v=1'.$v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
				var pQty = 0, sQty = 0, pAmt = 0, sAmt = 0, pl = 0;
            },
            "aoColumns": [{"bVisible": false}, null, null, null,{"mRender": decimalFormat, "bSearchable": false}, {"mRender": decimalFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}, {"mRender": currencyFormat, "bSearchable": false}],
			'fnRowCallback': function (nRow, aData, iDisplayIndex) {
				//console.log(aData);
				
                nRow.id = aData[1];
               nRow.className = " text-right text-capitalize";
			   var nCells = nRow.getElementsByTagName('td');
			   nCells[3].className = "sum";
			   //console.log(aData[0]);
                 var url = "<?= site_url('reports/getCategoriesWiseDataReport/?category="+aData[0]+"'.$v) ?>";
				 //console.log(url+' '+aData[0]);
                   $.ajax({
                    type:'ajax',
                    dataType:'json',
                    url:url,
                    async:true,
                    success:function(result){
                        //console.log(result)
						//var Response = jQuery.parseJSON(result);  
						$.each(result, function(key, val){
						});
						$.each(result, function(key, val){
							
							sQty += parseFloat(val.SoldQty);
							pAmt += parseFloat(val.TotalPurchase);
							sAmt += parseFloat(val.TotalSales);
							pl += parseFloat(val.Profit);
							nCells[3].innerHTML = decimalFormat(val.PurchasedQty);
							nCells[4].innerHTML = decimalFormat(val.SoldQty);
							nCells[5].innerHTML = currencyFormat(val.TotalPurchase);
							nCells[6].innerHTML = currencyFormat(val.TotalSales);
							nCells[7].innerHTML = currencyFormat(val.Profit);
							
							$('#PrRData tr').find('td:eq(3)').each(function() {
								pQty += parseFloat($(this).text());
								$('.pqty').text(pQty);
							});
						});
						
                    }, error:function(){
                        //nCells['<?= $keydata ?>'].innerHTML = currencyFormat(0);
                    }
                
                });
                console.log(pQty);
             // nCells['<?= $keydata ?>'].innerHTML = currencyFormat(aData[1]);
            },
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {
				var nCells = nRow.getElementsByTagName('th');
                
				
                nCells[3].innerHTML = decimalFormat(parseFloat(pQty));
                nCells[4].innerHTML = decimalFormat(parseFloat(sQty));
                nCells[5].innerHTML = currencyFormat(parseFloat(pAmt));
                nCells[6].innerHTML = currencyFormat(parseFloat(sAmt));
                nCells[7].innerHTML = currencyFormat(parseFloat(pl));
            }
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 1, filter_default_label: "[<?=lang('category_code');?>]", filter_type: "text", data: []},
            {column_number: 2, filter_default_label: "[<?=lang('category_name');?>]", filter_type: "text", data: []},
			{column_number: 3, filter_default_label: "[<?=lang('Subcategory');?>]", filter_type: "text", data: []},
        ], "footer");*/
		//alert('<?= site_url('reports/getCategoriesDetailReport/?v=1'.$v) ?>');
		$('#PrRData').DataTable({
			    "destroy": true,
				"processing": true,
				"serverSide": true,
				"ordering": true,
				"lengthMenu": [ [20, 40, 60, 80, 100, -1], [20, 40, 60, 80, 100, "All"] ],
				"pageLength": 20,
				"ajax": {
					'type': 'GET',
					"url": '<?= site_url('reports/getCategoriesDetailReport/?v=1'.$v) ?>',
					//"dataSrc": "",
					"async": false,
					'data': {
					   action: 'SaleList',
					   
					},
					error: function (xhr, error, code)
					{
						console.log(xhr);
						console.log(code);
						console.log(error);
					},
				},
				"columns": [
					{ "data": "category_code" },
					{ "data": "category_name" },
					{ "data": "sub_category_name" },
					{ "data": "purchased" , "class":"sum" , "orderable":false, "mRender": formatQuantity},
					{ "data": "SoldQty" , "class":"sum" , "orderable":false, "mRender": formatQuantity},
					{ "data": "TotalPurchase" , "class":"sum" , "orderable":false, "mRender": currencyFormat},
					{ "data": "TotalSales" , "class":"sum" , "orderable":false, "mRender": currencyFormat},
					{ "data": "Profit", "class": "sum", "orderable": false, "mRender": currencyFormat },
				],
				"fnCreatedRow": function( nRow, aData, iDataIndex ) {
					console.log(aData);
				},
				"footerCallback": function ( row, data, start, end, display ) {
    var api = this.api();
    api.columns('.sum', { page: 'current' }).every(function() {
        var sum = this
            .data()
            .reduce(function(a, b) {
                var b_value = b.split(',').join('');
                var x = parseFloat(a) || 0;
                var y = parseFloat(b_value) || 0;
                return x + y;
            }, 0);

        // Format quantity or currency based on column index
        var colIndex = this.index();
        var isQuantityCol = colIndex === 3 || colIndex === 4;

        var formattedSum = isQuantityCol ? formatQuantity(sum) : currencyFormat(sum);

        $(this.footer()).html(formattedSum);
    });
}
,fnDrawCallback: function () {
					
				},
			});
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
		$('#PrRData_wrapper select').change(function(){
			
		});
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
<style>
  .search_right{text-align:right}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-folder-open"></i><?= lang('categories_report'); ?> <?php
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
                    <a href="#" id="image" class="tip" title="<?= lang('save_image') ?>">
                        <i class="icon fa fa-file-picture-o"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
<p class="introtext"><?= lang('customize_report'); ?></p>

    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                
                <div id="form">

                    <?php echo form_open("reports/categories_report"); ?>
                    <div class="row">

                        <div class="col-sm-4">
                            <div class="form-group">
                                <?= lang("category", "category") ?>
                                <?php
                                $cat[''] = "";
                                foreach ($categories as $category) {
                                    $cat[$category->id] = $category->name;
                                }
                                echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ''), 'class="form-control select" id="category" placeholder="' . lang("select") . " " . lang("category") . '" style="width:100%"')
                                ?>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="control-label" for="warehouse"><?= lang("warehouse"); ?></label>
                                <?php
                                $permisions_werehouse = explode(",",$user_warehouse);
                                $wh[""] = lang('select').' '.lang('warehouse');
                                foreach ($warehouses as $warehouse) {
                                    if($Owner || $Admin  ){
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
		                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'].'-'.$_POST['end_date'] : "";?>"
		                               id="daterange_new" class="form-control">
		                        <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
		                         <input type="hidden" name="start_date"  id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : "";?>">
		                         <input type="hidden" name="end_date"  id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : "";?>" >
                                    </div>
		                </div>
		            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div  class="controls">
                            <?php echo form_submit('submit_report', $this->lang->line("submit"), 'class="btn btn-primary"'); ?>
                            <!--<input type="button" id="report_reset" data-value="<?=base_url('reports/categories');?>" name="submit_report" value="Reset" class="btn btn-warning input-xs">-->
                             <a href="reports/restbutton" class="btn btn-success">Reset</a>
                        </div>
                    </div>
                    <?php echo form_close(); ?>

                </div>

                <div class="clearfix"></div>

                <div class="table-responsive">
                    <table id="PrRData"
                           class="table table-striped table-bordered table-condensed table-hover dfTable reports-table"
                           style="margin-bottom:5px;">
                        <thead>
                        <tr class="active">
                            
                            <th><?= lang("category_code"); ?></th>
                            <th><?= lang("category_name"); ?></th>
                            <th><?= lang("Subcategory"); ?></th>
                            <th><?= lang("purchased"); ?></th>
                            <th><?= lang("sold"); ?></th>
                            <th><?= lang("purchased_amount"); ?></th>
                            <th><?= lang("sold_amount"); ?></th>
                            <th><?= lang("profit_loss"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="8" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="pqty"><?= lang("purchased"); ?></th>
                            <th><?= lang("sold"); ?></th>
                            <th><?= lang("purchased_amount"); ?></th>
                            <th><?= lang("sold_amount"); ?></th>
                            <th><?= lang("profit_loss"); ?></th>
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
            window.location.href = "<?=site_url('reports/getCategoriesDetailReport/pdf/?v=1'.$v)?>";
            return false;
        });
        $('#xls').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/getCategoriesDetailReport/0/xls/?v=1'.$v)?>";
            return false;
        });
        $('#image').click(function (event) {
            event.preventDefault();
			window.location.href = "<?=site_url('reports/getCategoriesDetailReport/0/0/img/?v=1'.$v)?>";
            /*html2canvas($('.box'), {
                onrendered: function (canvas) {
                    var img = canvas.toDataURL()
                    window.open(img);
                }
            });*/
            return false;
        });
    });
</script>