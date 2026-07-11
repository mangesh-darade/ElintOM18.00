<?php defined('BASEPATH') OR exit('No direct script access allowed');
 
 ?>
<style>
    .select2-container .select2-choice, #goicon button{height:40px !important;}
</style>
    
<script src="<?= $assets; ?>js/hc/highcharts.js"></script>
<script type="text/javascript">
    $(function () {
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                stops: [[0, color], [1, Highcharts.Color(color).brighten(-0.3).get('rgb')]]
            };
        });
        <?php if ($daily_records) { ?>
        $('#m1bschart').highcharts({
            chart: {type: 'column'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: false},
            series: [{
                name: '<?=lang('sold');?>',
                data: [<?php
                foreach ($daily_records as $r) {
                    echo "['".$r->date."', ".$r->sales."],";
                    
                }
                ?>],
                dataLabels: {
                    enabled: true,
                    rotation: 0, //-90
                    overflow: 'none',
                    crop: false,
                    color: '#000',
                    align: 'right',
                    y: -5,//-25
                    style: {fontSize: '11px'}
                }
            }]
        });
        <?php } if ($daily_records) { ?>
        $('#m2bschart').highcharts({
            chart: {type: 'column'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: false},
            series: [{
                name: '<?=lang('Purchase');?>',
                data: [<?php
            foreach ($daily_records as $r) {
                 echo "['".$r->date."', ".$r->purchases."],";
            }
            ?>],
                dataLabels: {
                    enabled: true,
                    rotation: 0,
                    color: '#000',
                    align: 'right',
                     overflow: 'none',
                    crop: false,
                    y: -5,
                    style: {fontSize: '11px'}
                }
            }]
        });
        <?php } if ($monthly_records) { ?>
        $('#m3bschart').highcharts({
            chart: {type: 'column'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: false},
            series: [{
                name: '<?=lang('sold');?>',
                data: [<?php
                foreach ($monthly_records as $r) {
                    echo "['".$r->month_name."', ".$r->sales."],"; 
                }
                ?>],
                dataLabels: {
                    enabled: true,
                    rotation: 0,
                    overflow: 'none',
                    crop: false,
                    color: '#000',
                    align: 'right',
                    y: -5,
                    style: {fontSize: '11px'}
                }
            }]
        });
        <?php } if ($monthly_records) { ?>
        $('#m4bschart').highcharts({
            chart: {type: 'column'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: false},
            series: [{
                name: '<?=lang('Purchase');?>',
                data: [<?php
            foreach ($monthly_records as $r) {
                 echo "['".$r->month_name."', ".$r->purchases."],";
            }
            ?>],
                dataLabels: {
                    enabled: true,
                    rotation: 0,
                    color: '#000',
                    align: 'right',
                     overflow: 'none',
                    crop: false,
                    y: -5,
                    style: {fontSize: '11px'}
                }
            }]
        });
        <?php } ?>
        <?php if ($combine_daily_records) { ?>
		$('#combine_daily_records').highcharts({
            chart: {type: 'column'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: true},
            series: [
			{
                name: '<?=lang('Sale');?>',
                data: [<?php
            foreach ($combine_daily_records as $r) {
                 echo "['".$r->date."', ".$r->sales."],";
            }
            ?>],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#000',
                    align: 'right',
                     overflow: 'none',
                    crop: false,
                    y: -25,
                    style: {fontSize: '11px'}
                }
            },
			{
                name: '<?=lang('Purchase');?>',
                data: [<?php
            foreach ($combine_daily_records as $r) {
				echo "['".$r->date."', ".$r->purchases."],";
            }
            ?>],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#000',
                    align: 'right',
                     overflow: 'none',
                    crop: false,
                    y: -25,
                    style: {fontSize: '11px'}
                }
            },
			]
        });
        
        <?php } ?>
        <?php if ($combine_monthly_records) { ?>
		$('#combine_monthly_records').highcharts({
            chart: {type: 'column'},
            title: {text: ''},
            credits: {enabled: false},
            xAxis: {type: 'category', labels: {rotation: -60, style: {fontSize: '13px'}}},
            yAxis: {min: 0, title: {text: ''}},
            legend: {enabled: true},
            series: [
			{
                name: '<?=lang('Sale');?>',
                data: [<?php
            foreach ($combine_monthly_records as $r) {
                 echo "['".$r->month_name."', ".$r->sales."],";
            }
            ?>],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#000',
                    align: 'right',
                     overflow: 'none',
                    crop: false,
                    y: -25,
                    style: {fontSize: '11px'}
                }
            },
			{
                name: '<?=lang('Purchase');?>',
                data: [<?php
            foreach ($combine_monthly_records as $r) {
				echo "['".$r->month_name."', ".$r->purchases."],";
            }
            ?>],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#000',
                    align: 'right',
                     overflow: 'none',
                    crop: false,
                    y: -25,
                    style: {fontSize: '11px'}
                }
            },
			]
        });
        
        <?php } ?>
    });
 $(document).ready(function(){
     $("#go").click(function(){
         var year = $('#year').val();
         var month = $('#month').val();
         var wareId = $('#ware').val();
         var ViewType = $('#ViewType').val();
        window.location = "<?php echo site_url('reports/sale_purchase_chart_details/')?>"+wareId+'/'+ViewType ;
     })
 });

</script>
<style>

/* Style the tab */
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}
</style>
<div class="box">
    <div class="box-header">
        <?php $warehouse_id = $this->uri->segment(3); ?>
        <h2 class="blue">
            <i class="fa-fw fa fa-line-chart"></i>
            <?= lang('Sale Purchase Chart Details').' (' . ($warehouse ? $warehouse[$warehouse_id]->name : lang('all_warehouses')) . ')'; ?>
        </h2>
<?php// if (!empty($warehouses)) { ?>
<!--        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li><a href="<?php  site_url('reports/best_sellers') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                        <li class="divider"></li>
                        <?php
                        foreach ($warehouses as $warehouse) {
                            echo '<li><a class= "ware" href="' . site_url('reports/sale_purchase_chart_details/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                        }
                        ?>
                    </ul>
                </li>
            </ul>
        </div>-->
        <?php //} ?>
         <?php echo form_open("reports/sale_purchase_chart_details"); ?>
       
         <div class="box-icon" id="goicon">
         <button type="button" id="go" class="btn btn-info" name="go">Go !</button>
         </div>

         <?php if (!empty($warehouses)) { ?>
        <div class="box-icon">
               <select type="text" name="warehouse" id="ware" class="form-control">
                  
                        <?php
                        if($wareget==0){
                                     $selected = 'selected';
                                }
                        echo '<option value="0" '.$selected.'>' . lang('all_warehouses'). '</option>';
                        foreach ($warehouses as $warehouse) {
                           $selected = '';
                           if($wareget){
                               if($warehouse->id == $wareget){
                                     $selected = 'selected';
                                }
                              }
                        echo '<option value="'.$warehouse->id.'" '.$selected.'>' . $warehouse->name . '</option>';
                          }
                        ?>
                <select>
        </div>
        <?php } ?>
		<div class="box-icon">
		   <select type="text" name="ViewType" id="ViewType" class="form-control" style="width:120px;">
			  <option value="sale" <?php if($ViewType=='sale') echo 'selected'; ?>>Sale</option>
			  <option value="purchase" <?php if($ViewType=='purchase') echo 'selected'; ?> >Purchase</option>
			  <option value="sale_purchase" <?php if($ViewType=='sale_purchase') echo 'selected'; ?>>Sale/Purchase</option>
			<select>
        </div>
         <?php  echo form_close(); ?>
       
</div>      
    <div class="box-content">
	
	<div class="row" style="margin-bottom: 15px;">
	
        <div class="col-sm-12" style="<?php if($ViewType=='sale') echo 'display:block'; else echo 'display:none'; ?>">
            <div class="box">
                <div class="box-header">
                   <h2 class="blue">Daily Sale </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="m1bschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="col-sm-12" style="<?php if($ViewType=='purchase') echo 'display:block'; else echo 'display:none'; ?>">
            <div class="box">
                <div class="box-header">
                   <h2 class="blue">Daily Purchase </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="m2bschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    <div class="row" style="margin-bottom: 15px;">
        
        <div class="col-sm-12" style="<?php if($ViewType=='sale') echo 'display:block'; else echo 'display:none'; ?>">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue">Monthly Sale
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="m3bschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="col-sm-12" style="<?php if($ViewType=='purchase') echo 'display:block'; else echo 'display:none'; ?>">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue">Monthly Purchase
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="m4bschart" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div class="row" style="margin-bottom: 15px;">
		<div class="col-sm-12" style="<?php if($ViewType=='sale_purchase') echo 'display:block'; else echo 'display:none'; ?>">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue">Daily Records
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="combine_daily_records" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="col-sm-12" style="<?php if($ViewType=='sale_purchase') echo 'display:block'; else echo 'display:none'; ?>">
            <div class="box">
                <div class="box-header">
                    <h2 class="blue">Monthly Records
                    </h2>
                </div>
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="combine_monthly_records" style="width:100%; height:450px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>