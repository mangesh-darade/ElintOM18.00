 <?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .btn, .form-control {border-radius: 4px !important;}
    .offeraction{text-decoration: none  !important;}
</style>
<?php $getdefaultoffet = $this->db->select('*')->get('sma_coupons')->row();?>
<div class="box">
    <div class="box-header">
        <div class="col-sm-6">
            <h2 class="blue"><i class="fa-fw fa fa-gift"></i><?= lang('Coupons'); ?></h2>
       </div>
        <div class="col-sm-6">
            
            <button onclick="window.location='system_settings/add_coupon'" class=" pull-right btn btn-primary " > <i class="fa fa-plus" aria-hidden="true"></i> Add Coupon</button>
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
        url:'<?= base_url() ?>system_settings/getcouponlist/',
        async:false,
        success:function(result){
			console.log(result);
            $('#databody').html(result);
        },error:function(){
            console.log('error');
        }
        
    });
     $('#offertable').DataTable({
         "destroy": true,
     });
}
</script>    