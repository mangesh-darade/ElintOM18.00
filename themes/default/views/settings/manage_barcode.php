<style>

    .Nodot {
      list-style: none;
    }
    
    .ListItem:Hover {
      cursor: move;
     
    }
    .ListItem{
        padding: 5px 10px;
        margin: 2px;
        font-size: 16px;
        background: #42a8df;
        color: #FFF;
        cursor:pointer ;
        
    }
    
    .sizeinput{    height: 36px;
    padding: 5px;
    color: #5c5555;}
    .styleselect{width:200px;}
    </style>
    <?php 
           $barcode_field = [
               'site'    => 'Site Name',
               'barcode'=> 'Barcode Image',
               'image'        => 'Product Image',
               'product_name' => 'Product Name',
               'brand'        => 'Brand',
               'category'     => 'Category',
               'Subcategory' => 'Sub Category',
               'variants'     => 'Variants',
               'unit'         => 'Unit',
               'price'        => 'Price',
               'mrp'          => 'MRP',
               'Date'         => 'Manufacture Date',
               'address'      => 'Address',
               
           ];
           
           $barcode_field2 = [
               
               'dynamic2_site'                   => 'Site Name',
               'dynamic2_barcode'                => 'Barcode Image',
               'dynamic2_image'                  => 'Product Image',
               'dynamic2_product_name'           => 'Product Name',
               'dynamic2_brand'                  => 'Brand',
               'dynamic2_category_subcategory'   => 'Category & Subcategory',
               'dynamic2_variants'               => 'Variants ',
               'dynamic2_unit'                   => 'Unit',
               'dynamic2_mrp'                    => 'MRP',
               'dynamic2_price'                  => 'Price',
               'dynamic2_Date'                   => 'Manufacture Date',
               'dynamic2_address'                => 'Address',
               
           ];
           
           
           
           $barcode_side = [
               'side_barcode'=> 'Barcode Image',
               'side_site'   => 'Site Name',
               'side_productname' => 'Product Name ',
               'side_brandname'   => 'Brand Name',
               'side_categorySubcategory' => 'Category & Subcategory',
               'side_variants' => 'Variants',
               'side_productprice' => 'Product Price',
               'side_mrp'  => 'MRP',
               'side_mfg_date' => 'Mfg Date',
               'side_address'  => 'Address',
           ];
    
    ?>
    
    
    <div class="box">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-th-list"></i><?= lang('Manage Barcode'); ?></h2>
            
        </div>
        <div class="box-content">
             <?php echo form_open("system_settings/manage_barcode"); $manageB = array(); ?>
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label> Barcode Type</label>
                        <select class="form-control" id="barcodetype" name="barcode_type">
                            <option value="static" <?= (($Settings->barcode_type=='static')?'Selected' :'') ?>>Static</option>
                            <option value="dynamic" <?= (($Settings->barcode_type=='dynamic')?'Selected' :'') ?>>Dynamic</option>
                            <option value="dynamic2" <?= (($Settings->barcode_type=='dynamic2')?'Selected' :'') ?>>Dynamic 2</option>
                            <option value="sidebyside" <?= (($Settings->barcode_type=='sidebyside')?'Selected' :'') ?>>Side By Side </option>
                            <option value="sillagefragrances" <?= (($Settings->barcode_type=='sillagefragrances')?'Selected' :'') ?>>Sillagefragrances</option>

                        </select>
                    </div>
                </div>
            </div>
            
             <!--  Dynamic Barcode -->
            <div class="row" id="dynamic_barcode" style="display:none;">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-sm-11">
                           
                            <div class="row">
                                <div class="col-sm-3">
                                    <strong>Title</strong>
                                </div>
                                <div class="col-sm-3">
                                    <strong>Font Style</strong>
                                </div>
                                <div class="col-sm-3 ">
                                    <strong>Font Size</strong>
                                </div>
                                <div class="col-sm-3 ">
                                    <strong>Show Label</strong>
                                </div>
                            </div>
                            <ul id="SortMe" class="Nodot">
                                <?php
                                    if($Settings->barcode_type=='dynamic'){
                                        foreach($managebarcode as $key=> $manage_barcode){ 
                                        $manageB[] = $manage_barcode->name_key;
                                        ?>

                                        <li class="ListItem">
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <input type="checkbox" <?= ($manage_barcode->status)?'checked' : ''?> name="manage_barcode[]" id="rowmanage_<?= $key ?>" value ='<?= $manage_barcode->name_key.'~'.$manage_barcode->name ?>' > 
                                                    <b> <?= $manage_barcode->name ?> </b>
                                                </div>
                                                <?php if(($manage_barcode->name_key!=='barcode') && ($manage_barcode->name_key!=='image')){ ?>
                                                <div class="col-sm-3">
                                                    <select name="<?=  $manage_barcode->name_key ?>_style" class="styleselect">
                                                        <option value='normal' <?= ($manage_barcode->font_weight=='normal')?'Selected' :'' ?>>Normal</option>
                                                        <option value='bold' <?= ($manage_barcode->font_weight=='bold')?'Selected' :'' ?>>Bold</option>
                                                    </select>

                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="text" class="sizeinput" value="<?= (($manage_barcode->font_size)?$manage_barcode->font_size:(($manage_barcode->name_key=='address')?'9':'13' )) ?>" placeholder="Font size ex.12" name='<?=  $manage_barcode->name_key ?>_size'>

                                                </div>

                                                
                                                <?php 
                                               if($manage_barcode->name_key=='price' || $manage_barcode->name_key=='mrp' ){ ?>
                                                <div class="col-sm-3">
                                                    <input type="text" class="form-control" value="<?= $manage_barcode->display ?>" name="<?= $manage_barcode->name_key?>_display" placeholder="<?= ($manage_barcode->name_key=='mrp'?'MRP':'Price')  ?> Label">
                                                </div>
                                            <?php } ?>
                                               <?php }?>
                                            </div>


                                        </li>

                                    <?php } 
                                    }    
                                ?>
                                <?php 
                               
                                foreach($barcode_field as $key => $barcodefield){ ++$keyval; 
                                   
                                   if(!in_array($key,$manageB)){ ?>
                                    
                                    <li class="ListItem">
                                        <div class="row">
                                            <div class="col-sm-3">
                                               <input type="checkbox" name="manage_barcode[]" id="row_<?= $keyval ?>" value ='<?= $key.'~'.$barcodefield ?>' > 
                                               <b><?= $barcodefield ?> </b>
                                            </div>
                                            <?php if(($key!=='barcode') && ($key!=='image')){ ?>
                                            <div class="col-sm-3">
                                                <select name="<?=  $key ?>_style" class="styleselect">
                                                    <option value='normal'>Normal</option>
                                                    <option value='bold'>Bold</option>
                                                </select>
                                        
                                            </div>
                                            <div class="col-sm-3">
                                                <input type="text" class="sizeinput" placeholder="Font size" value="<?= (($key=='address')?'9':'13') ?>" name='<?=  $key ?>_size'>
                                 
                                            </div>
                                                          
                                            <?php 
                                               if($key=='price' || $key=='mrp' ){ ?>
                                                <div class="col-sm-3">
                                                    <input type="text" class="form-control" name="<?= $key?>_display" placeholder="<?= ($key=='mrp'?'MRP':'Price')  ?> Label">
                                                </div>
                                            <?php } ?>
                                            <?php } ?>
                                        </div>
                                       
                                    </li>
                                   <?php } } ?>

                            </ul>
                          
                           
                            
                        </div>
                    </div>    
                </div>
            </div>  
            <!-- End Dynamic Barcode -->
            
            
            <!-- Dynamic Barcode 2 -->
             <div class="row" id="dynamic2_barcode" style="display:none;">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-sm-12">
                           <div class="row">
                                <div class="col-sm-3">
                                    <strong>Title</strong>
                                </div>
                                <div class="col-sm-3">
                                    <strong>Font Style</strong>
                                </div>
                                <div class="col-sm-3 ">
                                    <strong>Font Size</strong>
                                </div>
                               <div class="col-sm-3 ">
                                    <strong>Show Label</strong>
                                </div>
                            </div>
                            
                            <ul id="SortMe2" class="Nodot">
                                <?php
                                    if($Settings->barcode_type=='dynamic2'){
                                        foreach($managebarcode as $key=> $manage_barcode2){ 
                                        $manageB2[] = $manage_barcode2->name_key;
                                        ?>

                                        <li class="ListItem">
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <input type="checkbox" <?= ($manage_barcode2->status)?'checked' : ''?> name="manage_barcode_dynamic2[]" id="rowmanage_<?= $key ?>" value ='<?= $manage_barcode2->name_key.'~'.$manage_barcode2->name ?>' > 
                                                    <b> <?= $manage_barcode2->name ?> </b>
                                                </div>
                                                <?php if(($manage_barcode2->name_key!=='dynamic2_barcode') && ($manage_barcode2->name_key!=='dynamic2_image')){ ?>
                                                <div class="col-sm-3">
                                                    <select name="<?=  $manage_barcode2->name_key ?>_style" class="styleselect">
                                                        <option value='normal' <?= ($manage_barcode2->font_weight=='normal')?'Selected' :'' ?>>Normal</option>
                                                        <option value='bold' <?= ($manage_barcode2->font_weight=='bold')?'Selected' :'' ?>>Bold</option>
                                                    </select>

                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="text" class="sizeinput" value="<?= (($manage_barcode2->font_size)?$manage_barcode2->font_size:(($manage_barcode2->name_key=='dynamic2_address')?'9':'13' )) ?>" placeholder="Font size ex.12" name='<?=  $manage_barcode2->name_key ?>_size'>

                                                </div>

                                                
                                                <?php 
                                               if($manage_barcode2->name_key=='dynamic2_price' || $manage_barcode2->name_key=='dynamic2_mrp' ){ ?>
                                                <div class="col-sm-3">
                                                    <input type="text" class="form-control" value="<?= $manage_barcode2->display ?>" name="<?= $manage_barcode2->name_key?>_display" placeholder="<?= ($manage_barcode2->name_key=='dynamic2_mrp'?'MRP':'Price')  ?> Label">
                                                </div>
                                            <?php } ?>
                                               <?php }?>
                                            </div>


                                        </li>

                                    <?php } 
                                    }    
                                ?>
                                <?php 
                               
                                foreach($barcode_field2 as $key2 => $barcodefield2){ ++$keyval; 
                                   
                                   if(!in_array($key2,$manageB2)){ ?>
                                    
                                    <li class="ListItem">
                                        <div class="row">
                                            <div class="col-sm-3">
                                               <input type="checkbox" name="manage_barcode_dynamic2[]" id="row_<?= $keyval ?>" value ='<?= $key2.'~'.$barcodefield2 ?>' > 
                                               <b><?= $barcodefield2 ?> </b>
                                            </div>
                                            <?php if(($key2!=='dynamic2_barcode') && ($key2!=='dynamic2_image')){ ?>
                                            <div class="col-sm-3">
                                                <select name="<?=  $key2 ?>_style" class="styleselect">
                                                    <option value='normal'>Normal</option>
                                                    <option value='bold'>Bold</option>
                                                </select>
                                        
                                            </div>
                                            <div class="col-sm-3">
                                                <input type="text" class="sizeinput" placeholder="Font size" value="<?= (($key2=='dynamic2_address')?'9':'13') ?>" name='<?=  $barcodefield2 ?>_size'>
                                 
                                            </div>
                                                          
                                            <?php 
                                               if($key2=='dynamic2_price' || $key2=='dynamic2_mrp' ){ ?>
                                                <div class="col-sm-3">
                                                    <input type="text" class="form-control" name="<?= $key2?>_display" placeholder="<?= ($key2=='dynamic2_mrp'?'MRP':'Price')  ?> Label">
                                                </div>
                                            <?php } ?>
                                            <?php } ?>
                                        </div>
                                       
                                    </li>
                                   <?php } } ?>

                            </ul>
                          
                           
                            
                        </div>
                    </div>    
                </div>
            </div>  
            
            <!-- End Dynamic Barcode 2 -->
            
            <!-- Side By Side Barcode -->
            <div class="row" id="sidebyside_barcode">
                <div class=" col-sm-12" >
                            <div class="row">
                                <div class="col-sm-3">
                                    <strong>Title</strong>
                                </div>
                                <div class="col-sm-3">
                                    <strong>Font Style</strong>
                                </div>
                                <div class="col-sm-3 ">
                                    <strong>Font Size</strong>
                                </div>
                                <div class="col-sm-3 ">
                                    <strong>Show Label</strong>
                                </div>
                            </div>
                            
                    <ul id="Sortside" class="Nodot">
                        <?php
                            if($Settings->barcode_type=='sidebyside'){
                                foreach($managebarcode as $key=> $manage_barcode_side){ 
                                        $manageBside[] = $manage_barcode_side->name_key;
                                        ?>

                                        <li class="ListItem">
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <input type="checkbox" <?= ($manage_barcode_side->status)?'checked' : ''?> name="manage_side_barcode[]" id="rowmanage_<?= $key ?>" value ='<?= $manage_barcode_side->name_key.'~'.$manage_barcode_side->name ?>' > 
                                                    <b> <?= $manage_barcode_side->name ?> </b>
                                                </div>
                                                <?php if(($manage_barcode_side->name_key!=='side_barcode') ){ ?>
                                                <div class="col-sm-3">
                                                    <select name="<?=  $manage_barcode_side->name_key ?>_style" class="styleselect">
                                                        <option value='normal' <?= ($manage_barcode_side->font_weight=='normal')?'Selected' :'' ?>>Normal</option>
                                                        <option value='bold' <?= ($manage_barcode_side->font_weight=='bold')?'Selected' :'' ?>>Bold</option>
                                                    </select>

                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="text" class="sizeinput" value="<?= (($manage_barcode_side->font_size)?$manage_barcode_side->font_size:(($manage_barcode_side->name_key=='side_address')?'9' : '13')) ?>" placeholder="Font size ex.12" name='<?=  $manage_barcode_side->name_key ?>_size'>

                                                </div>

                                                <?php if($manage_barcode_side->name_key=='side_productprice' || $manage_barcode_side->name_key=='side_mrp' ){ ?>
                                                    <div class="col-sm-3">
                                                        <input type="text" class="form-control" name="<?= $manage_barcode_side->name_key ?>_display" value="<?= $manage_barcode_side->display ?>" placeholder="<?= ($manage_barcode_side->name_key=='side_mrp'?'MRP':'Price')  ?> Label">
                                                    </div>
                                                <?php } ?>
                                               <?php }?>
                                            </div>


                                        </li>

                            <?php } 
                            }
                        ?>
                        
                        <?php 
                            foreach($barcode_side as $keyside => $barcodefieldside){ ++$keyval; 
                               if(!in_array($keyside,$manageBside)){ ?>
                                <li class="ListItem">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <input type="checkbox" name="manage_side_barcode[]" id="row_side<?= $keyval ?>" value ='<?= $keyside.'~'.$barcodefieldside ?>' > 
                                            <b><?= $barcodefieldside ?> </b>
                                        </div>
                                        <?php if(($keyside!=='side_barcode') ){ ?>
                                            <div class="col-sm-3">
                                                <select name="<?=  $keyside ?>_style" class="styleselect">
                                                    <option value='normal'>Normal</option>
                                                    <option value='bold'>Bold</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-3">
                                                <input type="text" class="sizeinput" placeholder="Font size" value="<?= (($keyside=='side_address')?'9':'13') ?>" name='<?=  $keyside ?>_size'>
                                            </div>
                                            <?php 
                                            
;                                            if($keyside=='side_productprice' || $keyside=='side_mrp' ){ ?>
                                                <div class="col-sm-3">
                                                    <input type="text" class="form-control" name="<?= $keyside ?>_display" placeholder="<?= ($keyside=='side_mrp'?'MRP':'Price')  ?> Label">
                                                </div>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </li>
                                <?php } 
                            } ?>
                    </ul>
                </div>    
            </div>
            <!-- End Side by side Barcode -->
            <div class="row" id="align_block">
                <div class="col-sm-3">
                    <div class="form-group">
                        <label >Barcode Align </label>
                        <select class="form-control" name="barcode_align" >
                            <option value="left" <?= ($Settings->barcode_align=='left')?'Selected' :'' ?>>Left </option>
                            <option value="right" <?= ($Settings->barcode_align=='right')?'Selected' :'' ?>>Right </option>
                            <option value="center" <?= ($Settings->barcode_align=='center')?'Selected' :'' ?>>Center </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3" style="margin-top: 2em;">
                    <button type="submit" class="btn btn-success" >Submit</button>
                </div>
                <?php echo form_close(); ?>
            </div>    
        
        </div>
    
     <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<script>
    $(document).ready(function () {
 
 /**
  * Dynamin
  * @type @call;$
  */
  var Items = $("#SortMe li");
  $('#SortMe').sortable({
    disabled: false,
    axis: 'y',
    forceHelperSize: true,
    update: function (event, ui) {
        var Newpos = ui.item.index();
    }
  }).disableSelection();
 
 /**
  *  Dynamic 2
  * @type @call;
  */
  var Items = $("#SortMe2 li");
  $('#SortMe2').sortable({
    disabled: false,
    axis: 'y',
    forceHelperSize: true,
    update: function (event, ui) {
        var Newpos = ui.item.index();
    }
  }).disableSelection();
 
 
  /**
   *  Sort side by side
   * @type @call;$|@call;$
   */
  var Items = $("#Sortside li");
  
  $('#Sortside').sortable({
    disabled: false,
    axis: 'y',
    forceHelperSize: true,
    update: function (event, ui) {
        var Newpos = ui.item.index();

    }
  }).disableSelection();
  });
  
  
  
  
  </script>
  
  <script>
       $(document).ready(function(){
           section_show($('#barcodetype').val());

       });
       
       $('#barcodetype').change(function(){
            section_show($(this).val())
        });
       
       
       function section_show(keyvalue){
           
           switch(keyvalue){
               
            case 'dynamic':
                    $('#dynamic_barcode').show();
                    $('#align_block').show();
                    $('#sidebyside_barcode').hide();
                    $('#dynamic2_barcode').hide();
                break;
            
            case 'dynamic2':
                    $('#dynamic2_barcode').show();
                    $('#align_block').show();
                    $('#dynamic_barcode').hide();
                    $('#sidebyside_barcode').hide();
                break;
            
            case 'sidebyside':
                    $('#sidebyside_barcode').show();
                    $('#align_block').show();
                    $('#dynamic_barcode').hide();
                    $('#dynamic2_barcode').hide();
                break;
            default:
                    $('#dynamic_barcode').hide();
                    $('#sidebyside_barcode').hide();
                    $('#align_block').hide();
                    $('#dynamic2_barcode').hide();
                break;
           }
       }
  </script>    