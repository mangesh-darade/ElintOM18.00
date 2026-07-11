<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('cf_settings'); ?></h2>
    </div>
    <p class="introtext"><?= lang('update_info'); ?></p>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form');
                echo form_open_multipart("system_settings/custom_fields", $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12"> 
                    <?php                        
                        foreach ($custom_fields as $cf_type => $cfs) {
                    ?>
                        <fieldset class="scheduler-border" >
                            <legend class="scheduler-border"><?= lang($cf_type) ?></legend>
                            <?php                                  
                                $cf = $cfs[0];
                                
                                $cfi = $cf_type == 'employee' ? 2 : 1;
                                    
                                for($i=$cfi; $i<=6; $i++) {
                            ?>
                            <div class="col-md-4 col-sm-4">
                                <div class="form-group">
                                <?= lang($cf_type, $cf_type) .' '.lang('cf'.$i, 'cf'.$i).' '.lang('label', 'label'); ?>
                                <?= form_input( $cf_type."[cf$i]" , $cf->{"cf$i"}, 'class="form-control tip" id="'.$cf_type.'_cf'.$i.'"'); ?>
                                </div>
                            </div>
                            <?php } ?>
                             
                            <p class="text-danger col-lg-12" style="text-transform: capitalize;">Note: Add * With Label To Make Custom Field Mandatory. (Ex. <?=$cf_type?> name*) </p>
                        </fieldset>
                    <?php
                        }
                        ?>
           
                    </div>
                </div>
                <div style="clear: both; height: 10px;"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="controls">
                        <?= form_submit('update_settings', lang("update_settings"), 'class="btn btn-primary"'); ?>
                        </div>
                    </div>
                </div>
            <?= form_close(); ?>
            </div>
        </div>
         
    </div>
</div>
 