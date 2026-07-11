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
		<?php foreach($months as $km =>$kval){ if(!empty($monthly_arr[$kval])){ ?>
        $('#chart<?php echo $kval; ?>').highcharts({
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
                            return '<h3 style="margin:-15px 0 0 0;"><b>' + this.point.name + '</b>:<br><b> ' + currencyFormat(this.y) + '</b></h3>';
                        },
                        useHTML: true
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '<?php echo $this->lang->line("Total Sale"); ?>',
                data: [
                    <?php
					foreach ($monthly_arr[$kval] as $k => $v) {
						echo "['".$v->name."', ".$v->total_sales."],";
					}
					?>
					]
            }]
        });
		<?php } } ?>
		<?php foreach($daily as $km =>$kval){ if(!empty($daily_arr[$kval])){ ?>
        $('#chart<?php echo $kval; ?>').highcharts({
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
                            return '<h3 style="margin:-15px 0 0 0;"><b>' + this.point.name + '</b>:<br><b> ' + currencyFormat(this.y) + '</b></h3>';
                        },
                        useHTML: true
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '<?php echo $this->lang->line("Total Sale"); ?>',
                data: [
                    <?php
					foreach ($daily_arr[$kval] as $k => $v) {
						echo "['".$v->name."', ".$v->total_sales."],";
					}
					?>
					]
            }]
        });
		<?php } } ?>
    });
	/*function getDailyData($Type){
		$('.AllBox').hide();
		$('.'+$Type).show();
		
	}*/
</script>

<?php if ($Owner || $Admin) { ?>
    <div class="box" style="margin-top: 15px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-bar-chart-o"></i><?= lang('warehouse_stock') . ' (' . (!empty($warehouse_id) && is_numeric($warehouse_id) ? $warehouse[$warehouse_id]->name : lang('all_warehouses')) . ')'; ?>
                 </h2>

            <div class="box-icon">
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
					<h1 class="introtext" style="text-align:center;"><?php echo lang('Daily Categories Chart Details'); ?></h1>
			<?php foreach($daily as $km =>$kval){ if(!empty($daily_arr[$kval])){ ?>
			
					<div class="col-sm-6">
						<div class="box">
							<div class="box-header">
								<h2 class="blue"><?= $kval; ?>
								</h2>
							</div>
							<div class="box-content">
								<div class="row">
									<div class="col-md-12">
										<div id="chart<?= $kval; ?>" style="width:100%; height:450px;"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				
			<?php } }  ?>
			</div>
			</div>
        </div>
        <div class="box-content AllBox MonthlyBox" >
		<div class="row">
				<div class="col-lg-12">
					<h1 class="introtext" style="text-align:center;"><?php echo lang('Monthly Categories Chart Details'); ?></h1>
			<?php foreach($months as $km =>$kval){ if(!empty($monthly_arr[$kval])){ ?>
			
					<div class="col-sm-6">
						<div class="box">
							<div class="box-header">
								<h2 class="blue"><?= $kval.'('.date('F', strtotime($kval)).')'; ?>
								</h2>
							</div>
							<div class="box-content">
								<div class="row">
									<div class="col-md-12">
										<div id="chart<?= $kval; ?>" style="width:100%; height:450px;"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				
			<?php } }  ?>
			</div>
			</div>
        </div>
		
    </div>
<?php } ?>
