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
    </style>
    
    <div class="box">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-th-list"></i><?= lang('Manage Variants'); ?></h2>
            
        </div>
        <div class="box-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-sm-4">
                            <?php echo form_open("system_settings/variant_manage"); 
                            $manageV = array();
                            ?>
                            
                            <ul id="SortMe" class="Nodot">
                                <?php foreach($managevariant as $key=> $manage_variant){ 
                                    $manageV[] = $manage_variant->variant_id;
                                    ?>
                                    
                                    <li class="ListItem"><input type="checkbox" <?= ($manage_variant->status)?'checked' :'' ?> name="manage_variant[]" id="rowmanage_<?= $key ?>" value ='<?= $manage_variant->variant_id.'~'.$manage_variant->variant_name ?>' >
                                           <?= $manage_variant->variant_name ?>
                                    </li>
                                
                                <?php } ?>
                                <?php foreach($variant as $key => $variant_value){ 
                                    if(!in_array($variant_value->id,$manageV)){ ?>
                                    
                                    
                                     <li class="ListItem">
                                        <input type="checkbox" name="manage_variant[]" id="row_<?= $key ?>" value ='<?= $variant_value->id.'~'.$variant_value->name ?>' >
                                        
                                        <?= $variant_value->name ?>
                                    </li>
                                    <?php }} ?>

                            </ul>
                          
                            <button type="submit" class="btn btn-success" >Submit</button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>    
                </div>
            </div>    
    </div>    
        
      
    
     <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<script>
    $(document).ready(function () {
  
  var Items = $("#SortMe li");
  
  $('#SortMe').sortable({
    disabled: false,
    axis: 'y',
    forceHelperSize: true,
    update: function (event, ui) {
        var Newpos = ui.item.index();
//        console.log(ui.item.context);
//        alert("You moved item to position " + Newpos);
  
    }
  }).disableSelection();
  });
  </script>