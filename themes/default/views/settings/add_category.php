<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_category'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("system_settings/add_category", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>

            <div class="form-group">
                <?= lang('category_code *', 'code'); ?>
                <?= form_input('code', set_value('code'), 'class="form-control" id="code" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang('category_name *', 'name'); ?>
                <?= form_input('name', set_value('name'), 'class="form-control" id="name" required="required"'); ?>
            </div>

            <div class="form-group">
                <?= lang("category_image", "image") ?>
                <input id="image" type="file" data-browse-label="<?= lang('browse'); ?>" name="userfile" data-show-upload="false" data-show-preview="false"
                       class="form-control file">
            </div>
            <div class="form-group">
                <?= lang("parent_category ", "parent") ?>
                <?php
                $cat[''] = lang('select').' '.lang('parent_category');
                foreach ($categories as $pcat) {
                    $cat[$pcat->id] = $pcat->name;
                }
                echo form_dropdown('parent', $cat, (isset($_POST['parent']) ? $_POST['parent'] : ''), 'class="form-control select" id="parent" style="width:100%"')
                ?>
            </div>
            
            <div class="form-group">
                <?= lang('Tax_Type', 'fix_tax_rate'); ?> <strong>*</strong>
                <?php
                $oopts = array('fix tax' => lang('Fix_Tax'), 'variable tax' => lang('Variable_Tax'),);
                ?>
                <?= form_dropdown('fix_tax_rate', $oopts, (isset($_POST['fix_tax_rate']) ? $_POST['fix_tax_rate '] : 'variable tax'), 'class="form-control tip" required="required" id="fix_tax_rate" style="width:100%;"'); ?>
            </div>
            <div class="form-group" id="taxrate">
                <?= lang("Tax_Rate ", "tax") ?>
                <?php
                $tax = [];
                foreach ($tax_rates as $ctax) {
                    $tax[$ctax->id] = $ctax->name;
                }
                echo form_dropdown('tax_rate', $tax, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ''), 'class="form-control select" id="tax_rate" style="width:100%"')
                ?>
            </div>
            <div class="form-group">
                <?= lang("flag_visible", "flag_visible") ?>
                <?php
                $vopts = array('1' => lang('yes'), '0' => lang('no'));
                echo form_dropdown('flag_visible', $vopts, (isset($_POST['flag_visible']) ? $_POST['flag_visible'] : 1), 'class="form-control select" id="flag_visible" style="width:100%"');
                ?>
            </div>
            <div class="form-group">
                <?= lang("Available_At_Locations", "Available_At_Locations"); ?><img src="<?= $assets ?>images/new.gif"
                height="30px" alt="new" />
                <?php
                $wh['*All'] = '*All';
                $wh['*None'] = '*None'; 
                foreach ($warehouses as $warehouse) {
                    $wh[$warehouse->id] = $warehouse->name;
                }
                echo form_dropdown('warehouse[]',$wh,(isset($_POST['warehouse']) ? $_POST['warehouse'] : []),'id="warehouse" multiple class="form-control select" style="width:100%;" ' );
                ?>
                <i>Note : This feature needs to be activated in the POS settings.</i>
            </div>
            <div class="form-group" id="taxslabs" style="display: none;">
                <?= lang("Tax Slabs ", "Tax_Slabs") ?> 

                <table class="table">
                    <thead>
                        <tr>
                            <th>Condition</th>
                            <th>Price</th> 
                            <th>Upto</th> 
                           <th>Tax Rate</th>
                            <th><i class="fa fa-plus"></i></th>
                        </tr>
                    </thead>
                    <tbody id="taxslabsrow">
                        <tr>
                            <td>
                                <select class="form-control" name="condition[]" >
                                    <option value="less_than">Less than or equal to</option> 
                                    <option value="greater_than">Onward</option>  
                                </select>
                            </td>
                            <td><input type="number" name="price[]" value="999" class="form-control"/></td> 
                            <td><input type="number" name="upto[]" value="" class="form-control"/></td> 
                            
                            <td>
                                <select class="form-control" name="taxratevalue[]" >
                                    <option value=""> -- Select --</option> 
                                    <?php foreach ($tax_rates as $taxrate) { ?>
                                        <option value="<?= $taxrate->id . '~' . $taxrate->rate.'~'.$taxrate->name ?>" <?= ($taxrate->id == "15")?'Selected' :'' ?> ><?= $taxrate->name ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn " onclick="addmore()" ><i class="fa fa-plus"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <select class="form-control" name="condition[]" >
                                    <option value="less_than">Less than or equal to</option>  
                                    <option value="greater_than" selected="true">Onward</option>  
                                </select>
                            </td>
                            <td><input type="number" name="price[]" value ="1000" class="form-control"/></td> 
                            <td><input type="number" name="upto[]"  class="form-control"/></td> 
                            
                            <td>
                                <select class="form-control" name="taxratevalue[]" >
                                    <option value=""> -- Select --</option> 
                                    <?php foreach ($tax_rates as $taxrate) { ?>
                                        <option value="<?= $taxrate->id . '~' . $taxrate->rate.'~'.$taxrate->name ?>" <?= ( $taxrate->id  == "16")?'Selected' :'' ?>><?= $taxrate->name ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn " onclick="addmore()" ><i class="fa fa-plus"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_category', lang('add_category'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>
<script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>
<script>
    $(document).ready(function() {
        $('#warehouse').select2({
            placeholder: "Select warehouses",
            allowClear: true
        });
    });
</script>
<script>
   
    $(document).ready(function(){
        var type = $('#fix_tax_rate').val();
        taxtype(type);
        
    });
    var count = 0;
    $('#fix_tax_rate').change(function (e) {
        type = $(this).val();
       taxtype(type);
    });
    
    function taxtype(type){
         if (type == 'variable tax') {
            $('#taxrate').hide();
            $('#taxslabs').show();
        } else {
            $('#taxrate').show();
            $('#taxslabs').hide();
        }
    }
    
    
    function addmore() {
        
        if(count > 2){
            return false;
        }else{
        var html = '';
            html += '<tr id="row_'+count+'">';
             html += '<td>';
                    html += '<select class="form-control" name="condition[]" >';
                        html += '<option value="less_than">Less than or equal to</option> '; 
                        html += '<option value="greater_than">Onward</option>';
                    html += '</select>';
                html += '</td>';
                html += '<td><input type="number" name="price[]" class="form-control"/></td>';
                html += '<td><input type="number" name="upto[]" class="form-control"/></td>'; 
               
                html += '<td>';
                    html += '<select class="form-control" name="taxratevalue[]" >';
                        html += '<option value=""> -- Select --</option> ';
                        <?php foreach ($tax_rates as $taxrate) { ?>
                                 html += '<option value="<?= $taxrate->id . '~' . $taxrate->rate.'~'.$taxrate->name ?>"><?= $taxrate->name ?></option>';
                        <?php } ?>
                    html += '</select>';
                html += '</td>';
                html += '<td>';
                    html += ' <span type="button" class=" " onclick="addmore()" ><i class="fa fa-plus"></i></span> | ';
                    html += ' <span type="button" class="text-danger" onclick="remove('+count+')" ><i class="fa fa-trash"></i></span>';
                html += '</td>';
            html += '</tr>';
            $('#taxslabsrow').append(html);
            count++;
        }    
   }
  
  function remove(passid){
    $('#row_'+passid).remove();
    count--;
  }
</script>
<?= $modal_js ?>