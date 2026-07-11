<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?> 
<?php 
$pages =  unserialize($Settings->barcode_a4_page_dynamic);
                
?>
<style>
    .modal.fade {
        -webkit-transition: opacity .3s linear, top .3s ease-out;
        -moz-transition: opacity .3s linear, top .3s ease-out;
        -ms-transition: opacity .3s linear, top .3s ease-out;
        -o-transition: opacity .3s linear, top .3s ease-out;
        transition: opacity .3s linear, top .3s ease-out;
        top: -3%;
    }

    .modal-header .btnGrp{
        position: absolute;
        top:18px;
        right: 10px;
    } 

</style>
<div class="container" >				
    <!--<div class="mymodal" id="modal-1" role="dailog">-->
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo lang('Barcode on A4 Size Paper '); ?></h4>
            </div>
            <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add-customer-form');
                echo form_open_multipart("system_settings/barcode_stiker_size", $attrib);
            ?>
            <div class="modal-body">
                <p><?= lang('enter_info'); ?></p>
                <div class="form-group">
                   <label class="control-label" for="paper_size"><?php echo $this->lang->line("Paper Size"); ?> </label>
                   <?php
                      $paper_size = ['A4'=> 'A4 Paper'];
                      echo form_dropdown('paper_size', $paper_size,'', 'class="form-control select" id="paper_size" style="width:100%;" required="required"');
                   ?>
               </div>
                   
              <div >
                   <label class="control-label" ><?php echo $this->lang->line("Paper Padding"); ?> (mm)</label>
                   <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="margin_top"><?php echo $this->lang->line("Top"); ?> </label>
                                <input type="number" step="0.1" value="<?= ($pages['margin_top']? $pages['margin_top']: '0') ?>" placeholder="Top Padding 9mm" name="margin_top" required="required" class="form-control" id="margin_top">
                            </div>    
                       </div>
                       <div class="col-sm-3">
                           <div class="form-group">
                                <label class="control-label" for="margin_bottom"><?php echo $this->lang->line("Bottom"); ?> </label>
                                <input type="number" step="0.1"  value="<?= ($pages['margin_bottom']? $pages['margin_bottom']: '0') ?>" placeholder="Bottom Padding 9mm" name="margin_bottom" required="required" class="form-control" id="margin_bottom">
                           </div>     
                       </div>
                       <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="margin_left"><?php echo $this->lang->line("Left"); ?> </label>
                                <input type="number" step="0.1"  value="<?= ($pages['margin_left']? $pages['margin_left']: '0') ?>" placeholder="Left Padding 9mm" name="margin_left" required="required" class="form-control" id="margin_left">
                            </div> 
                       </div>
                       <div class="col-sm-3">
                             <div class="form-group">
                                <label class="control-label" for="margin_right"><?php echo $this->lang->line("Right"); ?> </label>
                                <input type="number" step="0.1"   value="<?= ($pages['margin_right']? $pages['margin_right']: '0') ?>" placeholder="Right Padding 9mm" name="margin_right" required="required"  class="form-control" id="margin_right">
                             </div>    
                       </div>
                   </div>
              </div>
            
              <div >
                 <label class="control-label" ><?php echo $this->lang->line("Label/Sticker Size"); ?> (mm)</label>
                 <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="label_width"><?php echo $this->lang->line("Label Width"); ?> </label> 
                            <input type="number" step="0.1"  value="<?= ($pages['label_width']? $pages['label_width']: '0') ?>" class="form-control" id="label_width" required="required" placeholder="Label Width" name="label_width">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="label_height"><?php echo $this->lang->line("Label Height"); ?></label> 
                            <input type="number" step="0.1"  value="<?= ($pages['label_height']? $pages['label_height']: '0') ?>"  class="form-control" id="label_height" required="required" placeholder="Label Height" name="label_height">    
                        </div>         
                   </div>
                </div>
              </div>  
                
             
              <div >
                 <label class="control-label" ><?php echo $this->lang->line("Gap Between Label"); ?> (mm)</label>
                 <div class="row">
                     <div class="col-sm-3">
                           <div class="form-group">
                              <label class="control-label" for="gap_label_top"><?php echo $this->lang->line("Top"); ?> </label>
                              <input type="number" step="0.1"  value="<?= ($pages['gap_label_top']? $pages['gap_label_top']: '0') ?>" placeholder="Gap Label Top 9mm" name="gap_label_top" class="form-control" required="required" id="gap_label_top" >
                           </div>
                            
                       </div>
                     <div class="col-sm-3">
                           <div class="form-group">
                                <label class="control-label" for="gap_label_bottom"><?php echo $this->lang->line("Bottom"); ?> </label>
                                <input type="number" step="0.1"  value="<?= ($pages['gap_label_bottom']? $pages['gap_label_bottom']: '0') ?>" placeholder="Gap Label Bottom 9mm" name="gap_label_bottom" class="form-control" required="required" id="gap_label_bottom" >
                           </div>
                       </div>
                     <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="gap_label_left"><?php echo $this->lang->line("Left"); ?> </label>
                                <input type="number" step="0.1"  value="<?= ($pages['gap_label_left']? $pages['gap_label_left']: '0') ?>" class="form-control" id="gap_label_left" required="required" placeholder="Gap Label Left 9mm" name="gap_label_left">
                            </div>
                       </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="control-label" for="gap_label_right"><?php echo $this->lang->line("Right"); ?> </label> 
                            <input type="number" step="0.1"  value="<?= ($pages['gap_label_right']? $pages['gap_label_right']: '0') ?>" class="form-control" id="gap_label_right" required="required" placeholder="Gap Label Right 9mm" name="gap_label_right">
                        </div>
                    </div>

                </div>
              </div>  
           
<!--              <div >
                 <label class="control-label" ><?php echo $this->lang->line("No of Rows and No of Columns"); ?></label>
                 <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="no_of_row"><?php echo $this->lang->line("No of Rows"); ?> </label> 
                            <input type="number" value="<?= ($pages['no_of_row']? $pages['no_of_row']: '0') ?>" class="form-control" id="no_of_row" required="required" placeholder="No of Rows" name="no_of_row">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label" for="no_of_columns"><?php echo $this->lang->line("No of Columns"); ?></label> 
                            <input type="number" value="<?= ($pages['no_of_columns']? $pages['no_of_columns']: '0') ?>"  class="form-control" id="no_of_columns" required="required" placeholder="No of Columns" name="no_of_columns">    
                        </div>         
                   </div>
                </div>-->

                <div class="form-group">
                    <label class="control-label" for="sticker_per_page" ><?php echo $this->lang->line("No of Sticker per page"); ?></label>
                    <input type="number" step="0.1"  value="<?= ($pages['sticker_per_page']? $pages['sticker_per_page']: '0') ?>"  class="form-control" id="sticker_per_page" required="required" placeholder="No of Sticker per page" name="sticker_per_page">    
                </div>    
                  
              </div>  
                
        <div class="modal-footer">
        <?php echo form_submit('add_paper', lang('submit'), 'class="btn btn-primary" id="add_paper"'); ?>
        </div>
    </div>
<?php echo form_close(); ?>
</div>
<!--</div>-->
</div>

<?= $modal_js ?>

<script type="text/javascript">
    $(document).ready(function (e) {
        $('#add-customer-form').bootstrapValidator({
            feedbackIcons: {
                valid: 'fa fa-check',
                invalid: 'fa fa-times',
                validating: 'fa fa-refresh'
            }, excluded: [':disabled']
        });
        $('select.select').select2({minimumResultsForSearch: 7});
        fields = $('.modal-content').find('.form-control');
        $.each(fields, function () {
            var id = $(this).attr('id');
            var iname = $(this).attr('name');
            var iid = '#' + id;
            if (!!$(this).attr('data-bv-notempty') || !!$(this).attr('required')) {
                $("label[for='" + id + "']").append(' *');
                $(document).on('change', iid, function () {
                    $('form[data-toggle="validator"]').bootstrapValidator('revalidateField', iname);
                });
            }
        });

        $('.form-control').attr('autocomplete', 'off');
    });

   
</script> 

