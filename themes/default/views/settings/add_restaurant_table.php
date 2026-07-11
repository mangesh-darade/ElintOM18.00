<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_restaurant_table'); ?></h4>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open("system_settings/add_restaurant_table", $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
            <div class="form-group">
                <label for="name"><?php echo $this->lang->line("Name"); ?></label>
                <?php echo form_input('name', '', 'class="form-control" id="name" required="required"'); ?>
            </div>
            
            <div class="form-group">
                <label for="parent_id"> Parent Table</label>
                 <?php 
                $ptables[''] = lang('select').' '.lang('Table');
                foreach ($tables as $tables_items){
                    $ptables[$tables_items->id] = $tables_items->name .' ('.$tables_items->type.')'; 
                }
                echo form_dropdown('parent_id', $ptables, (isset($_POST['parent_id']) ? $_POST['parent_id'] : ''), 'class="form-control select" id="parent_id" style="width:100%"')
               ?>
            </div>    
            
            <div class="form-group">
                <label for="type"><?php echo $this->lang->line("Type "); ?></label>
                <?php 
                $type[''] = lang('select').' '.lang('type');
                $type['AC'] = 'AC';
                $type['Non-AC'] = 'Non-AC';
                echo form_dropdown('type', $type, (isset($_POST['type']) ? $_POST['type'] : ''), 'class="form-control select" id="type" style="width:100%"')
               ?>
            </div>
            
            <div class="form-group">
                <label for="table_group"><?php echo $this->lang->line("Table Group "); ?></label>
                <?php echo form_input('table_group', '', 'class="form-control" id="table_group" '); ?>
            </div>
            
            <div class="form-group">
                <label for="price_group"><?php echo $this->lang->line("Price_Group"); ?></label>
               <?php 
                $priceGroup[''] = lang('select').' '.lang('price_group');
                foreach ($price_group as $price_items){
                    $priceGroup[$price_items->id.'~'.$price_items->name] = $price_items->name; 
                }
                echo form_dropdown('price_group', $priceGroup, (isset($_POST['price_group']) ? $_POST['price_group'] : ''), 'class="form-control select" id="price_group" style="width:100%"')
               ?>
            </div>
            
            
            
            

        </div>
        <div class="modal-footer">
            <?php echo form_submit('add_restaurant_table', lang('Add_Restaurant_Table'), 'class="btn btn-primary"'); ?>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<?= $modal_js ?>