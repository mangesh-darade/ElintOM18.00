<style>
.introtext {
    padding: 10px 0px 0;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    font-size: 14px;
}

.box {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.box-header h2 {
    margin: 0;
    font-size: 20px;
    color: #337ab7;
}

.btn-primary {
    background-color: #337ab7;
    border-color: #2e6da4;
    padding: 10px 20px;
    border-radius: 5px;
}

.btn-primary:hover {
    background-color: #286090;
}

label {
    font-weight: bold;
}
</style>
<div class="box">

    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?= lang('Add Lead'); ?></h2>
    </div>
    <p class="introtext"><?php echo lang('enter_info'); ?></p>
    <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'add_employee_form');
    echo form_open_multipart("Leads/add", $attrib);
    ?>
    <div class="row">
        <!-- First column for Name and Phone -->
        <div class="col-md-6">
            <div class="form-group person">
                <?= lang("name", "name"); ?>
                <?php echo form_input('name', $leads->full_name, 'class="form-control tip" id="name" required="required"'); ?>
                <span id="error_name" style="color:#a94442;font-size:10px; display: none">please enter alphabets
                    only</span>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <?= lang("phone", "phone"); ?>
                <input type="tel" name="phone" class="form-control" id="phone" required="required" minlength="10"
                    maxlength="10" pattern="\d{10}" value="<?= $leads->mobile ?>" />
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Second row for Email and Lead Type -->
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("email_address", "email_address"); ?>
                <input type="text" name="email" class="form-control" id="email_address" value="<?= $leads->email ?>" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Address_line_1", "Address_line_1"); ?>
                <?php echo form_input('address_line_1', $leads->address_line_1, 'class="form-control" id="address_line_1"'); ?>
            </div>
        </div>

    </div>

    <div class="row">
        <!-- Third row for Address, Address Line 1 -->
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Address_line_2", "Address_line_2"); ?>
                <?php echo form_input('address_line_2', $leads->address_line_2, 'class="form-control" id="address_line_2"'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("City", "City"); ?>
                <?php echo form_input('city', $leads->city, 'class="form-control" id="city"'); ?>
            </div>
        </div>

    </div>

    <div class="row">
        <!-- Fourth row for Address Line 2, City -->
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("District", "District"); ?>
                <?php echo form_input('district', $leads->district, 'class="form-control" id="district"'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("State", "State"); ?>
                <?php echo form_input('state', $leads->state, 'class="form-control" id="state"'); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Fifth row for State, Postal Code -->
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Country", "couCountryntry"); ?>
                <?php echo form_input('country', $leads->country, 'class="form-control" id="country"'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Postal_code", "Postal_code"); ?>
                <?php echo form_input('postal_code', $leads->postal_code, 'class="form-control" id="postal_code"'); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <!--sixth row for district, Product sel 1 -->
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Business", "Business"); ?>
                <?php echo form_input('business', $leads->business, 'class="form-control" id="business"'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Source", "Source"); ?>
                <?php echo form_input('source', $leads->source, 'class="form-control" id="source"'); ?>
            </div>
        </div>
    </div>
    <div class="row">

        <!-- seventh row for State, Postal Code -->
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Campaign", "Campaign"); ?>
                <?php echo form_input('campaign', $leads->campaign, 'class="form-control" id="campaign"'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Form", "Form"); ?>
                <?php echo form_input('form', $leads->form, 'class="form-control" id="form"'); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- sixth row for Business, Brands-->
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Brands", "Brands"); ?>
                <?php echo form_input('brands', $leads->brands, 'class="form-control" id="brands"'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group person">
                <?= lang("Leads_Type", "Leads_Type") ?>
                <select class="form-control" name="leads_type" required="true">
                    <?php foreach ($leads_type as $lead) { ?>
                    <option value="<?= $lead->id . '~' . $lead->type ?>"
                        <?= ($lead->type == $leads->type ? 'Selected' : '') ?>>
                        <?= $lead->type ?>
                    </option>
                    <?php } ?>
                </select>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Product_Sel_1", "Product_Sel_1"); ?>
                <?php echo form_input('product_sel_1', $leads->product_sel_1, 'class="form-control" id="product_sel_1"'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Product_Sel_2", "Product_Sel_2"); ?>
                <?php echo form_input('product_sel_1', $leads->product_sel_1, 'class="form-control" id="product_sel_1"'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Product_Sel_3", "Product_Sel_3"); ?>
                <?php echo form_input('product_sel_1', $leads->product_sel_1, 'class="form-control" id="product_sel_1"'); ?>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Form_Memo", "Form_Memo"); ?>
                <?php echo form_textarea('form_memo', $leads->form_memo, 'class="form-control" id="form_memo"'); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <?= lang("Comments", "Comments"); ?>
                <?php echo form_textarea('comments', $leads->comments, 'class="form-control" id="comments"'); ?>
            </div>
        </div>
    </div>
    <div class="form-group">
        <?php echo form_submit('Add_Leads', lang('Add'), 'class="btn btn-primary add_button"'); ?>
    </div>
</div>
<script>
$('#comments').redactor('destroy');
$('#comments').redactor({
    buttons: ['formatting', '|', 'alignleft', 'aligncenter', 'alignright', 'justify', '|', 'bold', 'italic',
        'underline', '|', 'unorderedlist', 'orderedlist', '|', 'link', '|', 'html'
    ],
    formattingTags: ['p', 'pre', 'h3', 'h4'],
    minHeight: 100,
    changeCallback: function(e) {
        var v = this.get();
        localStorage.setItem('comments', v);
    }
});
</script>
<?php echo form_close(); ?>