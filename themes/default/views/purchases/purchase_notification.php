<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-star"></i> <?=lang('Purchases Notification')?>
        </h2>

    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?=lang('list_results');?></p>

                <div class="table-responsive">
                    <table id="POData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped">
                        <thead>
                        <tr class="active">
                          
                            <th class="text-center"><?= lang("Sr.No"); ?></th>
                            <th class="text-center"><?= lang("Date"); ?></th>
                            <th class="text-center"><?= lang("Invoice No"); ?></th>
                            <th class="text-center"><?= lang("reference_no"); ?></th>
                            <th class="text-center"><?= lang("Supplier"); ?></th>                            
                            <th class="text-center"><?= lang("Items"); ?></th>
                            <th class="text-center"><?= lang("Accept"); ?></th>
                           
                        </tr>
                        </thead>
                        <tbody>
                           <?php if(!empty($notification_List)){
                               foreach($notification_List as  $key => $items){ ?>
                            <tr>
                                <td> <?= $key +1 ?></td>  
                                <td> <?= date('d-m-Y h:i:s',strtotime($items->created_at)) ?></td>
                                <td> <?= $items->invoice_no ?></td>  
                                <td> <?= $items->reference_no ?></td> 
                                <td> <?= $items->biller ?></td> 
                                <td>
                                    <a href="<?= base_url('purchases/purchase_notification_items/'.$items->id.'/'.$items->privatekey) ?>" data-toggle="modal" data-target="#myModal" class="btn btn-xs btn-primary">  Show Products</a>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-success" onclick="getpurchaseData('<?=$items->id ?>','<?=  $items->request_pos_url ?>','<?= $items->sales_id ?>','<?= $items->privatekey ?>')"> Accept </button>
                                </td>
                            </tr>
                         <?php  }
                           }else{
                               echo '<tr><td colspan="6" class="text-center"> Records not founds </td> </tr>';
                           } ?>
                        </tbody>
                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th class="text-center"><?= lang("Sr.No"); ?></th>
                            
                            <th class="text-center"> <?= lang("Date"); ?></th>
                            <th class="text-center"><?= lang("Invoice No"); ?></th>
                            <th class="text-center"><?= lang("reference_no"); ?></th>
                            <th class="text-center"><?= lang("Supplier"); ?></th>                            
                            <th class="text-center"><?= lang("Items"); ?></th>
                            <th class="text-center"><?= lang("Accept"); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" role="dialog" id="loadingmodel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Purchases</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
          <div  class="alert " role="alert">
          <h2 id="msg" >  </h2> 
          <p id="lodingP">Loading......</p>
          </div>
      </div>
      
    </div>
  </div>
</div>
<script>
    
    
    function getpurchaseData(notificationid, requesturl, salesid,privatekey){
        $('#loadingmodel').modal('show');
        $('#msg').html('Fetching Purchase data...');
        $.ajax({
            type:'ajax',
            dataType:'json',
            method:'post',
            url:requesturl+'/api4/getpurchases/',
            data:{'salesId':salesid,'privatekey':privatekey},
            success:function(result){
                if(result.status="SUCCESS"){
                    
                    storesyndata(notificationid,result.data,privatekey )
//                    console.log(result.data);
                }
            },error:function(){
                console.log('error');
                 $('#msg').html('Please try again');
            }
        });
    }
    
    function storesyndata(notificationId,syndata,privatekey  ){
         $('#msg').html('Checking products list');
        $.ajax({
            type:'ajax',
            dataType:'json',
            method:'post',
            url:'<?= base_url('purchases/storesyndata') ?>',
            data:{
                'notificationId':notificationId,
                'syndata' :syndata,
                '<?=$this->security->get_csrf_token_name()?>':'<?=$this->security->get_csrf_hash()?>'
            },
            async:false,
            success:function(result){
                if(result.status == 'SUCCESS'){
                    storePurchase(notificationId, privatekey);
                }else if(result.status == 'ERROR'){
                    getProducts(notificationId, result.requrestURL, result.data, privatekey  )
                }
                
//                console.log(result);
            },error:function(xhr, status, error){
                 $('#msg').html('Please try again');
                 console.log(xhr.responseText);
            }
        });
    }
    
    function getProducts(notificationId, requestURL, productscode, privatekey ){
        $('#msg').html('Fetching new products');
        $.ajax({
            type:'ajax',
            dataType:'json',
            method:'POST',
            data:{'barcodes':productscode, privatekey: privatekey},
            url:requestURL+'/api4/getProductDetails',
            success:function(result){
                if(result.status == 'SUCCESS'){
                    addnewProduts(notificationId,result.data);
                }

            },error:function(xhr, status, error){
                 console.log(xhr.responseText);
                  $('#msg').html('Please try again');
            }
        });
    }
    
    
    function addnewProduts(notificationId, productData){
         $('#msg').html('Create new products');
        $.ajax({
            type:'ajax',
            dataType:'json',
            method:'POST',
            data:{
                'productDetils' : productData,
                '<?=$this->security->get_csrf_token_name()?>':'<?=$this->security->get_csrf_hash()?>'
            },
            url:'<?= base_url('products/add_newproducts') ?>',
            async:false,
            success:function(result){
                if(result.status == 'SUCCESS'){
                    storePurchase(notificationId);
                }
//                console.log(result);
            },error:function(xhr, status, error){
                 $('#msg').html('Please try again');
                 console.log(xhr.responseText);
            }
        });
    }   
    
    
    function storePurchase(notificationId){
       $('#msg').html('Create new purchases');
        $.ajax({
            type:'ajax',
            dataType:'json',
            method:'get',
            url:'<?= base_url("purchases/storePurchase") ?>'+'/'+notificationId,
            async:false,
            success:function(result){
                if(result.status == 'SUCCESS'){
                    $('#msg').html(result.msg);
                    $('#lodingP').html('');
                    setTimeout(function(){
                        location.reload();
                    },2000)
                }
            },error:function(xhr, status, error){
                 $('#msg').html('Please try again');
                console.log(xhr.responseText); 
            }
        });
        
    }
    
</script>    