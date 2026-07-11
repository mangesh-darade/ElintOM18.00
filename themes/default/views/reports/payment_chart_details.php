<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src="<?= $assets; ?>js/hc/highcharts.js"></script>
<script type="text/javascript">


    $(function () {
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
            };
        });
		
		<?php if(!empty($payment_arr)){ ?>
        $('#CategoriesChartDaily').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {text: ''},
            credits: {enabled: false},
            tooltip: {
                formatter: function () {
                    return '<div class="tooltip-inner hc-tip" style="margin-bottom:0;">' + this.key + '<br><strong>' + currencyFormat(this.y) + '</strong> (' + formatNumber(this.percentage) + '%)';
                },
                followPointer: true,
                useHTML: true,
                borderWidth: 0,
                shadow: false,
                valueDecimals: site.settings.decimals,
                style: {fontSize: '14px', padding: '0', color: '#000000'}
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            return '<h3 style="margin:-12px 0 0 0;"><b>' + this.point.name + ': ' + currencyFormat(this.y, 'not_allow') + '</b></h3>';
                        },
                        useHTML: true
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '<?php echo $this->lang->line("Total Payment"); ?>',
                data: [
                    <?php
					foreach ($payment_arr as $k => $v) { if($v->Total!=0){
						echo "['".$v->paid_by."', ".$v->Total."],";
					} }
					?>
					]
            }]
        });
		<?php }  ?>
    });
	/*function getDailyData($Type){
		$('.AllBox').hide();
		$('.'+$Type).show();
		
	}*/
	function getData(){
		var WarehouseId = $('#WarehouseId').val();
		var start_date = $.trim($('#start_date').val());
		var end_date = $.trim($('#end_date').val());
		var Records = $('#Records').val();
		var Sale_Purchase = $('#Sale_Purchase').val();
		var res_start_date = start_date.replaceAll("/", "-");
		var res_end_date = end_date.replaceAll("/", "-");
		//alert(res_start_date+' '+res_end_date);
		window.location = 'reports/payment_chart_details/'+WarehouseId+'/'+res_start_date+'/'+res_end_date+'/'+Records+'/'+Sale_Purchase;
	}
</script>

    <div class="box" style="margin-top: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('Payment_Chart') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses')) . ')'; ?>
                 </h2>

            <div class="box-icon" style="display:none;">
                <ul class="btn-tasks">
                    <?php if (!empty($warehouses) && ($Owner || $Admin)) { ?>
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i
                                    class="icon fa fa-building-o tip" data-placement="left"
                                    title="<?= lang("warehouses") ?>"></i></a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu"
                                aria-labelledby="dLabel">
                                <li><a href="<?= site_url('reports/categories_chart_details') ?>"><i
                                            class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                                <li class="divider"></li>
                                <?php
                                foreach ($warehouses as $warehouse) {
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . site_url('reports/categories_chart_details/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }
                                ?>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
		<!--<div class="box-content">
			<div class="row">
				<div class="col-lg-12">
					<a href="javascript:void(0);" onclick="return getDailyData('DailyBox');">Daily</a>
					<a href="javascript:void(0);" onclick="return getDailyData('MonthlyBox');">Monthly</a>
				</div>
			</div>
		</div>-->

		<div class="box-content AllBox DailyBox">
		<div class="row">
			<div class="col-lg-12">
				<div class="row">
					<div class="col-sm-3">                        
                            <div class="form-group choose-date hidden-xs">
		                <div class="controls">
		                    <?= lang("date_range", "date_range"); ?>
		                    <div class="input-group">
		                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
		                        <input type="text"
		                               value="<?php echo isset($selected_start_date) ? date('d/m/Y',strtotime($selected_start_date)).'-'.date('d/m/Y',strtotime($selected_end_date)) : date('d/m/Y').'-'.date('d/m/Y'); ?>"
		                               id="daterange_new" class="form-control">
		                        <span class="input-group-addon" style="display:none;"><i class="fa fa-chevron-down"></i></span>
		                         <input type="hidden" name="start_date"  id="start_date" value="<?php echo isset($selected_start_date) ? date('d/m/Y',strtotime($selected_start_date)) : date('d/m/Y');?>">
		                         <input type="hidden" name="end_date"  id="end_date" value="<?php echo isset($selected_end_date) ? date('d/m/Y',strtotime($selected_end_date)) : date('d/m/Y');?>" >
                                    </div>
		                </div>
		            </div>
                        </div>
					<div class="col-md-2">
						<div class="form-group">
							<?= lang("Warehouse", "Warehouse"); ?>
							<select id="WarehouseId" class="form-control"  name="WarehouseId">
								<option value="0">All Warehouse</option>
								<?php                                 foreach ($warehouses as $warehouse) { ?>
								<option value="<?= $warehouse->id; ?>" <?php if(isset($warehouse_id) && $warehouse->id==$warehouse_id) echo 'selected'; ?>><?= $warehouse->name; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<?= lang("Sale/Purchase", "Sale/Purchase"); ?>
							<select id="Sale_Purchase" class="form-control"  name="Sale_Purchase">
								<option value="Sale" <?php if($Sale_Purchase=='Sale') echo 'selected'; ?>>Sale</option>
								<option value="Purchase" <?php if($Sale_Purchase=='Purchase') echo 'selected'; ?>>Purchase</option>
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<?= lang("Records", "Records"); ?>
							<select id="Records" class="form-control"  name="Records">
								<option value="All" <?php if($Records=='All') echo 'selected'; ?>>All</option>
								<option value="Top_10" <?php if($Records=='Top_10') echo 'selected'; ?>>Top 10</option>
								<option value="Bottom_10" <?php if($Records=='Bottom_10') echo 'selected'; ?>>Bottom 10</option>
							</select>
						</div>
					</div>
					
					<div class="col-md-1">
						<div class="form-group" style="margin-top: 28px;">
							<input type="button" name="SubmitDailyData" id="SubmitDailyData" class="btn btn-primary" onclick="return getData();" value="Submit">
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group" style="margin-top: 28px;">
							<a class="btn btn-primary" href="<?= base_url(); ?>reports/payment_chart_details">Cancel</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
				<div class="col-lg-12">
					<h1 class="introtext" style="text-align:center;"><?php echo lang('Payment Chart Details'); ?></h1>
					<div class="col-sm-12">
						<div class="box">
							<div class="box-header">
								<h2 class="blue"><?= $selected_date; ?>
								</h2>
							</div>
							<div class="box-content">
								<div class="row">
									<div class="col-md-12">
										<div id="CategoriesChartDaily" style="width:100%; height:450px;"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
				</div>
			</div>
        </div>
    </div>