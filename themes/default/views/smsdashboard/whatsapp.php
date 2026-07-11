<div class="section-heading">
    <i class="fa fa-whatsapp" aria-hidden="true"></i> <?= lang('WhatsApp'); ?>
    <ul id="myTab2" class="nav nav-tabs whatsapp_tab">
        <li class="active" id="single-whatsapp-4"><a href="#single-whatsapp" class="tab-grey" data-toggle="tab">Send Single WhatsApp</a></li>
        <li class="" id="group-whatsapp-4"><a href="#group-whatsapp" class="tab-grey" data-toggle="tab">Send Group WhatsApp</a></li>
    </ul>
</div>
<div class="row"><div class="col-md-12" id="whatsapp-loader"></div></div>
<div class="row">
    <div class="tab-content col-sm-12">
        <div id="single-whatsapp" class="tab-pane fade in active">
            <?php
            $attrib = array('role' => 'form', 'name' => "whatsapp-single", 'id' => "whatsapp-single");
            echo form_open_multipart("", $attrib);
            ?>
            <input type="hidden" name="hiddencust_whatsapp" id="hiddencust_whatsapp">
            <div class="row">
                <div class="col-lg-7">
                    <div class="form-group all">
                        <div class="col-lg-12">
                            <?= lang("List *", "product_details") ?>
                            <select id="customers_whatsapp" multiple="multiple"></select>
                        </div>
                        <div class="col-lg-12">
                            <?= lang("Message *", "product_details") ?>
                            <textarea name="wa_body" cols="40" rows="7" class="form-control skip wa_body" id="wa_body_single"></textarea>
                        </div>
                        <div class="col-lg-12">
                            <br>
                            <button type="submit" class="btn btn-primary"><?= lang('Send WhatsApp Message'); ?></button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <label for="product_details">Available Template (Application Message)</label>
                    <div class="whatsapp_template message-template well">
                        <?php echo $this->sma->TemplateList($templateList, 3); ?>
                    </div>
                </div>
            </div>
            <?= form_close(); ?>
        </div>

        <div id="group-whatsapp" class="tab-pane fade in">
            <?php
            $attrib = array('role' => 'form', 'name' => "whatsapp-group", 'id' => "whatsapp-group");
            echo form_open_multipart("", $attrib);
            ?>
            <input type="hidden" name="group_id" id="whatsapp_group_id" class="group_id">
            <input type="hidden" name="group_count" class="group_count" value="<?php echo $GroupCount ?>">

            <div class="row">
                <div class="col-lg-7">
                    <div class="form-group all">
                        <div class="col-lg-12">
                            <label for="product_details">Group</label>
                            <ul class="contact-group">
                                <div class="row">
                                    <?php echo $GroupGrid ?>
                                </div>
                            </ul>
                        </div>

                        <div class="col-lg-12">
                            <?= lang("Message *", "product_details") ?>
                            <textarea name="wa_body" cols="40" rows="7" class="form-control skip wa_body" id="wa_body_group"></textarea>
                        </div>

                        <div class="col-lg-12">
                            <br>
                            <button type="submit" class="btn btn-primary group_submit_button"><?= lang('Send WhatsApp Message'); ?></button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <label for="product_details">Available Template (Application Message)</label>
                    <div class="whatsapp_template message-template well">
                        <?php echo $this->sma->TemplateList($templateList, 3); ?>
                    </div>
                </div>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>

