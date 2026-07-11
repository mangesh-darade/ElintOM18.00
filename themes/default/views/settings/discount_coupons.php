 <?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .btn, .form-control {border-radius: 4px !important;}
    .offeraction{text-decoration: none  !important;}
     .dropdown-item{
            display: block; padding: 2px 5px; color: #7e7676; font-size: 14px; cursor: pointer;
    }
</style>
<?php //$getdefaultoffet = $this->db->select('*')->get('sma_coupons')->row();?>
<div class="box">
    <div class="box-header">
        <div class="col-sm-6">
            <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?= lang('Coupons'); ?></h2>
       </div>
        <div class="col-sm-6">
            
            <button onclick="window.location='system_settings/add_discount_coupon'" class=" pull-right btn btn-primary " > <i class="fa fa-plus" aria-hidden="true"></i> Add Coupon</button>
        </div>  
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive" id="databody"> </div>   
            </div>
        </div>
    </div>    
</div>  


<script type="text/javascript">
$(document).ready(function() {
	getofferlist();
});
function getofferlist(){
    $.ajax({
        dataType:'json',
        type:'ajax',
        method:'get',
        url:'<?= base_url() ?>system_settings/getdiscountcouponlist/',
        async:false,
        success:function(result){			
            $('#databody').html(result);
        },error:function(){
            console.log('error');
        }
        
    });
     $('#offertable').DataTable({
         "destroy": true,
     });
}

function managestatus(couponid, status){
    $.ajax({
       type:'ajax',
       dataType:'json',
       method:'post',
       url:'<?= base_url('system_settings/discount_coupon_status') ?>',
       data:{'<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>',id: couponid, status: status},
       async:false,
       success:function(result){
           console.log(result.message);
          if(result.status){
                bootbox.alert(result.message);
                $('#status_'+couponid).html(result.changestatus);
                
                $('#status_'+couponid).removeClass('btn-success btn-warning btn-danger btn-secondary');
                $('#status_'+couponid).addClass(result.button);

//               ? getofferlist();
          }else{
              bootbox.alert(result.message);              
          }
       }, error:function(){
           console.log('error');
       }
    });
}
</script>    