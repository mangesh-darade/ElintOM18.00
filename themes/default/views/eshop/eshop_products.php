<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Manage Products For E-Shop & E-commerce'); ?></h2>
    </div>
    <div class="box-content">
        <?php
        $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'name' => "manage_products", 'id' => "manage_products");
        echo form_open_multipart("eshop_admin/manage_products/$category_id", $attrib, ['action' => 'save_changes']);
        
        ?>
        <div class="row">
            <div class="col-sm-4">
                <table class="table table-bordered">
                    <thead>
                        <th>#</th>
                        <th>Categories</th>                         
                        <th>Status</th>                         
                    </thead>
                <?php
                if(is_array($categories)){
                    echo '<tbody> ';
                    foreach ($categories as $category) {
                        $checked = ($category['in_eshop'] || $category['id'] == $category_id) ? ' checked="checked" ' : '';
                        $active = $category['id'] == $category_id ? ' bg-success ' : '';
                        $in_eshop = $category['in_eshop'] ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-ban text-danger"></i>';
                        
                        /*
                         * This view load into two different controller eshop & webshop
                         * To manage current controller path using server veriable.
                         */
                        $mvc = explode('/', $_SERVER['REDIRECT_QUERY_STRING']);                        
                        $cm = $mvc[1].'/'.$mvc[2]; //Get Current Controler & Method function from url
                        
                        echo  '<tr><td class="'.$active.'"><input type="checkbox" name="categories[]" value="'.$category['id'].'" '.$checked.' class="checkbox" /></td>                           
                                <td class="'.$active.'"><a href="'.base_url($cm.'/'.$category['id']).'">'.$category['name'].'</a></td>'
                                . '<td class="text-center '.$active.'">'.$in_eshop.'</td>'
                                . '<tr>';
                    }
                    echo '</tbody> ';
                }
                ?>                     
                 </table>
            </div>
            <div class="col-sm-8">
                <table class="table table-bordered">
                    <thead>
                    <th><input type="checkbox" id="all_products" value="1" class=""   /></th>
                        <th>Image</th>
                        <th>Products Code</th>
                        <th>Products Name</th>
                        <th>Storage Type</th>
                    </thead>
                    <?php 
                    
                if(is_array($products)){
                    
                    echo '<tbody>';                    
                    
                    foreach ($products as $product) {
                        
                        $pchecked =  $product->in_eshop == 1 ?  ' checked="checked" ' : '';                       
                        $product_image = ($product->image == '') ? 'no_image.png' : $product->image;
                      
                        echo '<tr><td><input type="checkbox" name="products[]" value="'.$product->id.'" '.$pchecked.' class="checkbox prd_chk" /></td>
                             <td><img src="' . site_url("assets/mdata/$Customer_assets/uploads/$product_image") . '" height="40" alt="'.$product->code.'" class="img" /></td>
                             <td>'.$product->code.'</td>
                             <td>'.$product->name.'</td>                        
                             <td>'.$product->storage_type.'</td></tr>';                       
                    }
                    echo ' </tbody>';
                }
            ?>          
                </table>            
            </div>
        </div>
        <div class="row">
            <div class="form-group text-center">
            <?php echo form_submit('send', $this->lang->line("Submit"), 'id="send" class="btn btn-primary"  style="margin-top:20px;"'); ?> 
            </div>
            <?= form_close(); ?>
        </div>
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
</script>    