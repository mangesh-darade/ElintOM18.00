<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Manage Products Prices For E-commerce Platform'); ?></h2>
    </div>E-commerce
    <div class="box-content">
        <?php
        $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'name' => "frm_products", 'id' => "frm_products");
         echo form_open_multipart("products/eshop_price_update/", $attrib, ['action' => 'save_changes']);
        // echo '<pre>';
        // print_r($categories);
        // echo '</pre>';
        ?>
        <div class="row">
            <div class="col-sm-3">
                <table class="table table-bordered" style="width: 100%;">
                    <thead>                       
                        <th>Categories</th>                   
                    </thead>
                <?php
                if(is_array($categories)){
                    
                    foreach ($categories[0] as $category) {
                       
                        $active = $category['id'] == $category_id ? ' bg-success ' : '';
                        $view_list = '<i class="fa fa-list text-success" onclick="load_product('.$category['id'].', 0 )"></i>';
                ?>                                
                    <tr>
                        <th style="background-color: #eeeeee;">                             
                            <div class="col-sm-10"><i class="fa fa-check"></i><?=$category['name']?></div><div class="col-sm-2"><?=$view_list?></div>
                        </th>
                    </tr>
                       
                        <?php
                        if(is_array($categories[$category['id']])){
                    ?>
                        <tr>
                            <td>
                    <?php
                        foreach ($categories[$category['id']] as $subcategory) {
                            $view_sublist = '<i class="fa fa-list text-success" onclick="load_product('.$category['id'].', '.$subcategory['id'].' )"></i>';
                
                        ?>    
                            <div class="col-sm-10 "><?=$subcategory['name']?></div><div class="col-sm-2"><?=$view_sublist?></div>

                        <?php
                        }
                    ?>                                
                        </td>
                    </tr>
                    <?php
                        }                                
                        ?>      
                <?php            
                    }
                     
                }
                ?>                     
                 </table>
            </div>
            <div class="col-sm-9 form-group">
                <div id="div_product_list">
                    <h1>Products List Will Display Here ....</h1>
                </div>
                <?php echo form_submit('updateprice', $this->lang->line("Submit"), 'id="updateprice" class="btn btn-primary"  style="margin-top:20px;"'); ?> 
            
            </div>
        </div>
    <?= form_close(); ?>    
    </div>
</div>

<script>
    $(document).ready(function () {
                 
        $('input#all_products').on('ifToggled', function(event){
            var checked = $(this).is(":checked");
             
            if(checked) {
                $('input.prd_chk').iCheck('check');
            } else {
                $('input.prd_chk').iCheck('uncheck');
            }
        });
        
               
    });
    
    
        
    function load_product(category_id, subcategory_id ){        
                 
        var callurl = '<?= base_url('products/get_filter_products')?>';
        
        var postData = 'action=manage_eshop_product';
            postData = postData + '&category_id=' + category_id;
            postData = postData + '&subcategory_id=' + subcategory_id;
           // alert(postData);
        $.ajax({
            type: "GET",
            url: callurl ,
            data: postData,
            beforeSend: function () {
                $("#div_product_list").html("<div class='overlay'><i class='fa fa-refresh fa-spin'></i> Loading....</div>");
            },
            success: function (data) {
                $("#div_product_list").html(data);
//                var objData = JSON.parse(data);
//                if(objData.status == "SUCCESS") {
//                    
//                     
//                }
            }
        });
        
    
    }
    
</script>    