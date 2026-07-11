<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php

$v = "";
if ($this->input->post('start_date')) {
    $startDate = explode('/', substr($this->input->post('start_date') , 0, 10));
    $start_date = $startDate[2] . "-" . $startDate[1] . "-" . $startDate[0] . "  00:00";
    $v .= "&start_date=" . $start_date;
}
if ($this->input->post('end_date')) {
    $endDate = explode('/', substr($this->input->post('end_date') , 0, 10));
    $end_date = $endDate[2] . "-" . $endDate[1] . "-" . $endDate[0]. "  23:59";
    $v .= "&end_date=" . $end_date;
}

?>

<script>
    $(document).ready(function () {
        var oTable = $('#SlRData').dataTable({
            "aaSorting": [[0, "desc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= site_url('reports/get_deposit_report/?v=1'. $v) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
//             $( nRow ).find('td:eq(2)').addClass('noprint');
//                $( nRow ).find('td:eq(3)').addClass('noprint');
            },
            "aoColumns": [ {"mRender": fld},null, null, null,null, null,  null,null,{
                     "bSearchable": false,
            }
          
    
    ],
            "fnFooterCallback": function (nRow, aaData, iStart, iEnd, aiDisplay) {

            }
        }).fnSetFilteringDelay().dtFilter([
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
        <h2 class="blue"><i class="fa-fw fa fa-heart"></i><?= lang('Deposit Report'); ?> <?php
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

                    <?php echo form_open("reports/deposit"); ?>
                    <div class="row">
                     

                     

                       
                        <div class="col-sm-4">                        
                            <div class="form-group choose-date hidden-xs">
		                <div class="controls">
		                    <?= lang("date_range", "date_range"); ?>
		                    <div class="input-group">
		                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
		                        <input type="text"
		                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'].'-'.$_POST['end_date'] : "";?>"
                                               id="daterange_new" class="form-control" autocomplete="off">
		                        <!--<span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>-->
		                         <input type="hidden" name="start_date"  id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : "";?>">
		                         <input type="hidden" name="end_date"  id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : "";?>" >
                                    </div>
		                </div>
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
                    <h2 class="printdata text-center" style="display:none"> Deposit Report </h2>
                    <table id="SlRData"
                           class="table table-bordered table-hover table-striped table-condensed reports-table">
                        <thead>
                        <tr>
                            <th><?= lang("Date"); ?></th>
                            <th><?= lang("Customer Name"); ?></th>
                            <th><?= lang("Phone No"); ?></th>
                            <th><?= lang("Card No"); ?></th>
                            <th><?= lang("Room No"); ?></th>
                            <th ><?= lang("Amount"); ?></th>
                            <th><?= lang("Paid By"); ?></th>                           
                            <th><?= lang("Note"); ?></th>
                             <th><?= lang("Created By"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="<?= $col ?>" class="dataTables_empty"><?= lang('loading_data_from_server') ?></td>
                        </tr>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th></th><th></th>
                            
                            <th></th>
                            <th ></th>
                            <th ></th>
                            <th ></th>
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
<script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#pdf').click(function (event) {
            event.preventDefault();
            window.location.href = "<?=site_url('reports/get_deposit_report/pdf/?v=1'.$v)?>";
            return false;
         });

        $('#xls').click(function (event) {         
            event.preventDefault();          
            window.location.href = "<?=site_url('reports/get_deposit_report/0/xls/?v=1'.$v)?>";
            return false;
        });

        $('#image').click(function (event) {
            event.preventDefault();
			  window.location.href = "<?=site_url('reports/get_deposit_report/0/0/img/?v=1'.$v)?>";
			
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
