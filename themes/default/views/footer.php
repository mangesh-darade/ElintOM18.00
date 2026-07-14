<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<input type="hidden" name="sent_edit_transfer" id="sent_edit_transfer" value="<?= isset($sent_edit_transfer) ? $sent_edit_transfer : ''; ?>">
<input type="hidden" name="permission_owner" id="permission_owner" value="<?php echo $Owner; ?>">
<input type="hidden" name="permission_admin" id="permission_admin" value="<?php echo $Admin; ?>">
<input type="hidden" name="page_mode" id="page_mode" value="<?= (isset($page_mode) && $page_mode) ? $page_mode : ''; ?>">
<div class="clearfix"></div>
<?= '</div></div></div></td></tr></table></div></div>'; ?>
<div class="clearfix"></div>
<footer>
    <a href="#" id="toTop" class="blue" style="position: fixed; bottom: 30px; right: 30px; font-size: 30px; display: none;">
        <i class="fa fa-chevron-circle-up"></i>
    </a>

    <!-- <p style="text-align:center;">&copy; <?= date('Y') . " " . $Settings->site_name; ?> ( V 15.01 )  </p> -->
    <p style="text-align:center;">&copy; <?= date('Y') . " " . 'ElintOM' ?> ( v 17.01 )  </p>
</footer>
<?= '</div>'; ?>
<div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
<div class="modal fade in" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true"></div>
<div id="modal-loading" style="display:none;">
    <div class="blackbg"></div>
    <div class="loader"></div>
</div>
<div id="ajaxCall"><i class="fa fa-spinner fa-pulse"></i></div>

<audio id="myAudio">
    <source src="alertsound.ogg" type="audio/ogg">
    <source src="<?= $assets ?>sounds/alertsound.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>

<?php unset($Settings->setting_id, $Settings->smtp_user, $Settings->smtp_pass, $Settings->smtp_port, $Settings->update, $Settings->reg_ver, $Settings->allow_reg, $Settings->default_email, $Settings->mmode, $Settings->timezone, $Settings->restrict_calendar, $Settings->restrict_user, $Settings->auto_reg, $Settings->reg_notification, $Settings->protocol, $Settings->mailpath, $Settings->smtp_crypto, $Settings->corn, $Settings->customer_group, $Settings->envato_username, $Settings->purchase_code); ?>
<script type="text/javascript">
    var dt_lang = <?= $dt_lang ?>, dp_lang = <?= $dp_lang ?>, site = <?= json_encode(array('base_url' => base_url(), 'settings' => $Settings, 'dateFormats' => $dateFormats)) ?>;
    var lang = {paid: '<?= lang('paid'); ?>', pending: '<?= lang('pending'); ?>', completed: '<?= lang('completed'); ?>', ordered: '<?= lang('ordered'); ?>', received: '<?= lang('received'); ?>', partial: '<?= lang('partial'); ?>', sent: '<?= lang('sent'); ?>', r_u_sure: '<?= lang('r_u_sure'); ?>', due: '<?= lang('due'); ?>', returned: '<?= lang('returned'); ?>', transferring: '<?= lang('transferring'); ?>', active: '<?= lang('active'); ?>', inactive: '<?= lang('inactive'); ?>', unexpected_value: '<?= lang('unexpected_value'); ?>', select_above: '<?= lang('select_above'); ?>', download: '<?= lang('download'); ?>'};
</script>
<?php
$s2_lang_file = read_file('./assets/config_dumps/s2_lang.js');
foreach (lang('select2_lang') as $s2_key => $s2_line) {
    $s2_data[$s2_key] = str_replace(array('{', '}'), array('"+', '+"'), $s2_line);
}
$s2_file_date = $this->parser->parse_string($s2_lang_file, $s2_data, true);
?>
<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
<?php if (isset($simple_datatable)) { ?>
    <script type="text/javascript" src="<?= $assets ?>js/datatable_column/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/datatable_column/dataTables.bootstrap.min.js"></script>
<?php } else { ?>
    <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.dtFilter.min.js"></script>
<?php } ?>
<script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrapValidator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery.calculator.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/core.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/customers_add_modal.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrap-tagsinput.min.js"></script>

<?= ($m == 'purchases' && ($v == 'add' || $v == 'edit' || $v == 'purchase_by_csv' || $v == 'inward_po')) ? '<script type="text/javascript" src="' . $assets . 'js/purchases.js"></script>' : ''; ?>
<?= ($m == 'purchases' && ($v == 'generate_variant_po' )) ? '<script type="text/javascript" src="' . $assets . 'js/generate_variant_po.js"></script>' : ''; ?>
<?= ($m == 'transfers' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/transfers.js"></script>' : ''; ?>
<?= ($m == 'transfersnew' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/transfersnew.js"></script>' : ''; ?>
<?= ($m == 'sales' && ($v == 'add' || $v == 'edit' || $v == 'edit_challan' || $v == 'sale_by_csv')) ? '<script type="text/javascript" src="' . $assets . 'js/sales.js"></script>' : ''; ?>
<?= ($m == 'orders' && ( $v == 'edit_eshop_order' )) ? '<script type="text/javascript" src="' . $assets . 'js/order_sales.js"></script>' : ''; ?>
<?= ($m == 'quotes' && ($v == 'add' || $v == 'edit')) ? '<script type="text/javascript" src="' . $assets . 'js/quotes.js"></script>' : ''; ?>
<?= ($m == 'products' && ($v == 'add_adjustment' || $v == 'edit_adjustment')) ? '<script type="text/javascript" src="' . $assets . 'js/adjustments.js"></script>' : ''; ?>
<?= ($m == 'transfers' && ($v == 'add_request' || $v == 'edit_request' )) ? '<script type="text/javascript" src="' . $assets . 'js/transfers_request.js"></script>' : ''; ?>
<?= ($m == 'transfers' && ($v == 'jw_edit_transfer' || $v == 'jw_edit_request')) ? '<script type="text/javascript" src="' . $assets . 'js/jw_edit_transfer.js"></script>' : ''; ?>
<?= ($m == 'transfersnew' && ($v == 'request' )) ? '<script type="text/javascript" src="' . $assets . 'js/transfersnew_request.js"></script>' : ''; ?>

<script type="text/javascript">
    
    $(document).ready(function () {
        
        var QtyTab = $('#QtyTab').val();
        if (QtyTab == 1) {
            $('.tab-pane').removeClass('in');
            $('.tab-pane').removeClass('active');
            $('#damages').removeClass('fade');
            $('#damages').addClass('active');
            $('#myTab li').removeClass('active');
            $('.Qty_Adjustment_Class').addClass('active');
        }


        var r_u_sure = "<?= lang('r_u_sure') ?>";
        <?= $s2_file_date ?>
        $.extend(true, $.fn.dataTable.defaults, {"oLanguage":<?= $dt_lang ?>});
        $.fn.datetimepicker.dates['sma'] = <?= $dp_lang ?>;
        $(window).load(function () {
            $('.mm_<?= $m ?>').addClass('active');
            $('.mm_<?= $m ?>').find("ul").first().slideToggle();
            $('#<?= $m ?>_<?= $v ?>').addClass('active');
            $('.mm_<?= $m ?> a .chevron').removeClass("closed").addClass("opened");
        });

        setTimeout(function () {
            $('#ajaxCall').hide();
        }, 10000);

    });

    var alertOn = false;

    function changeText() {
       
        $.ajax({
            type: "get",
            async: false,
            url: '<?= base_url("eshop/new_eshop_orders") ?>',
            dataType: "json",
            success: function (data) {
               
                if (data.num) {
                    $('#eshop_new_orders').html(data.num);
                    if (data.new_order > 0) {
                        playSound(1);
                        var href_eshop = "<?= base_url('orders/eshop_order') ?>";
                        $('#eshop-order-alert').html('<div class="alert alert_notify alert-success"><button type="button" class="close fa-2x" onclick="notify_close()" >&times;</button> <a href="' + href_eshop + '" style="color:green;">' + data.new_order + ' new orders received from E-shop.</a></div>');
                        if (alertOn == false) {
                            $('.alert_notify').show();
                            alertOn = true;
                        }
                    }

                    if (alertOn == true) {
                        setTimeout(function () {
                            $('.alert_notify').hide();
                            alertOn = false;
                        }, 19000);
                    }
                }
            }
        });
    }

    function playSound(Play)
    {
        var x = document.getElementById("myAudio");
        if (Play == 1) {
            x.play();
            setTimeout(function () {
                x.pause();
            }, 4000);
        }
    }


<?php if ($Settings->pos_type == 'restaurant') { ?>

        function changeText1() {
            $.ajax({
                type: "get",
                async: false,
                url: '<?= base_url("urban_piper/new_orders") ?>',
                dataType: "json",
                success: function (data) {

                    if (data.num) {
                        $('#urbanpipersorder').html(data.num);

                        if (data.new_order > 0) {
                            playSound(1);
                            $('#urbanpiper-order-alert').html('<div class="alert alert_notify alert-success"><button type="button" class="close fa-2x" onclick="upnotify_close()" >&times;</button> <a href="<?= base_url('urban_piper') ?>" onclick="upnotify_close()" target="_bank"> ' + data.new_order + ' new orders received from Urbanpiper.</a></div>');
                            if (alertOn == false) {
                                $('.alert_notify').show();
                                alertOn = true;
                            }
                        }

                        if (alertOn == true) {
                            setTimeout(function () {
                                $('.alert_notify').hide();
                                alertOn = false;
                            }, 19000);
                        }
                    }
                }
            });
        }

        function upnotify_close() {
            $.ajax({
                type: "get",
                async: false,
                url: '<?= base_url("urban_piper/new_orders_alert/2") ?>',
                dataType: "json",
                success: function (data) {
                    $('.alert_notify').hide();
                }
            });
        }

        setInterval(changeText1,5000); //30000
        changeText1();


         function  upstocknotify_close(){
           $('.urbanpiper-stock_notify').hide();
        }

        <?php if($UPSettings->auto_store_status_manage){ ?>
         function storeManage(status){
            $.ajax({
                type:'ajax',
                dataType:'json',
                method:'get',
                url:'<?= base_url('urban_piper/manageStoreStatus') ?>/'+status,
                async:false,
                success:function(result){
                     console.log(result);
                },error:function(){
                    console.log('error');
                }
                
            });         
            
        }

        function sendStoreStatuUP(){
          $.ajax({
               type:'ajax',
               dataType:'json',
               method:'get',
               url:'<?= base_url('urban_piper/checkduration') ?>',
               async:false,
               success:function(result){
                   if(result.status){
                        storeManage('Disable');
                        setTimeout(function(){  storeManage('Enable'); }, 1000);
                   }
               },error:function(){
                   console.log('error');
               }
          });

        }
        setInterval(sendStoreStatuUP, 300000);  //5 min

    <?php } ?>



<?php }//end if  ?>

    function notify_close() {
        $.ajax({
            type: "get",
            async: false,
            url: '<?= base_url("eshop/new_eshop_orders_alert") ?>',
            dataType: "json",
            success: function (data) {
                $('.alert_notify').hide();
            }
        });
    }

    setInterval(changeText, 30000);
    changeText();


    /**
     * Check Mobile no register or not 
     * @param {type} groupname
     * @param {type} mobileno
     * @param {type} errorshow
     * @param {type} thisid
     * @returns {undefined}
     */
    function checkmobileno(groupname, mobileno, errorshow, thisid) {

        if (mobileno.toString().length == 10) {

            $.ajax({
                type: 'ajax',
                dataType: 'json',
                method: 'get',
                url: '<?= base_url('customers/checkMobileno') ?>',
                data: {'groupname': groupname, 'mobileno': mobileno},
                success: function (response) {
                    if (response.status == 'success') {

                        $('#' + thisid).val('');
                        $('#' + thisid).focus();
                        alert('Phone no already exists');
                    }

                }
            });
        }

    }

    function getstate(country) {
       // var statedata = '';
        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'get',
            url: '<?= base_url('customers/getstates') ?>',
            data: {'country': country},
            success: function (response) {
              //  console.log(response.status);
                if (response.status == 'success') {
                    $('#state').html(response.data);
//                    $('#statecode').hide();
                    $('#statename').hide();
                } else {
                    $('#state').html(response['data']);
//                    $('#statecode').show();
                    $('#statename').show();
                }
            }

        });

    }

     function purchasesNotification() {
            $.ajax({
                type: "get",
                async: false,
                url: '<?= base_url("purchases/new_purchase") ?>',
                dataType: "json",
                success: function (data) {

                    if (data.num) {
//                        $('#urbanpipersorder').html(data.num);

                        if (data.num > 0) {
                            playSound(1);
                            $('#purcahse-order-alert').html('<div class="alert alert_notify alert-success"><button type="button" class="close fa-2x" onclick="upnotify_close()" >&times;</button> <a href="<?= base_url('purchases/purchase_notification') ?>" onclick="purchasesnotify_close()" target="_bank"> ' + data.num + ' new purchase received.</a></div>');
                            if (alertOn == false) {
                                $('.alert_notify').show();
                                alertOn = true;
                            }
                        }

                        if (alertOn == true) {
                            setTimeout(function () {
                                $('.alert_notify').hide();
                                alertOn = false;
                            }, 19000);
                        }
                    }
                }
            });
        }

        function purchasesnotify_close() {
            $.ajax({
                type: "get",
                async: false,
                url: '<?= base_url("purchases/new_purchase_alert") ?>',
                dataType: "json",
                success: function (data) {
                    $('.alert_notify').hide();
                }
            });
        }

        setInterval(purchasesNotification,50000); //30000
        purchasesNotification();

      <?php 
        if($Settings->synced_data_sales){
           if(isset($_SESSION['Send_customer']) && is_array($_SESSION['Send_customer']) && isset($_SESSION['Send_customer']['status']) && $_SESSION['Send_customer']['status']==1){
    ?>    
            $.ajax({
                type:'ajax',
                dataType:'json',
                method:'POST',
                data:{
                     'suppliername':'<?= $_SESSION['Send_customer']['suppliername'] ?>',
                     'supplierKey': '<?= $_SESSION['Send_customer']['supplierKey'] ?>',
                     'supplierURL': '<?= $_SESSION['Send_customer']['supplierURL']?>',    
                     'privatekey': '<?= $_SESSION['Send_customer']['pivatekey'] ?>'
                    },
                url: '<?= $_SESSION['Send_customer']['send_customer_url'] ?>', //
                async:false,
                success:function(result){
                    if(result.status =='SUCCESS'){
                        <?php  
                            unset($_SESSION['Send_customer']);
                        ?>
                    }
                    console.log(result);
                },error:function(){
                    console.log('error');
                }
            });
    <?php 
            }            
        }        
    ?>     
        
        
        function suplierNotification() {
            $.ajax({
                type: "get",
                async: false,
                url: '<?= base_url("customers/supplier_key") ?>',
                dataType: "json",
                success: function (data) {

                    if (data.num) {
//                        $('#urbanpipersorder').html(data.num);

                        if (data.num > 0) {
                            playSound(1);
                            $('#suplier-order-alert').html('<div class="alert alert_notify alert-success"><button type="button" class="close fa-2x" onclick="upnotify_close()" >&times;</button> <button class="btn btn-sucess"  onclick="acceptcsuplierkey('+data.suplier_id+')" >  Accept Suplier Key.</button></div>');
                            if (alertOn == false) {
                                $('.alert_notify').show();
                                alertOn = true;
                            }
                        }

                        if (alertOn == true) {
                            setTimeout(function () {
                                $('.alert_notify').hide();
                                alertOn = false;
                            }, 19000);
                        }
                    }
                }
            });
        }

        function acceptcsuplierkey(passid) {
            $.ajax({
                type: "get",
                async: false,
                url: '<?= base_url("customers/supplier_key_accept") ?>',
                data:{'id':passid},
                dataType: "json",
                success: function (data) {
                    $('.alert_notify').hide();
                }
            });
        }

        suplierNotification();
      
        function restBill(){
            bootbox.confirm("Are you sure?", function (res) {
               if (res == true) {
                    window.location.href = '<?= base_url('restandlogout') ?>';
        
               }
           });
                        
        }
      
</script>
</body>
</html>
