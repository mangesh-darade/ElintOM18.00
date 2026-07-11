<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            Add Combo Product
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
           
        </div>
        <div class="modal-body">
            <div>
                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form','id'=>'comboform');
                echo form_open_multipart("products/addcombo", $attrib);
                ?>
                  <input type="hidden" name="pos_type" id="pos_type" value="<?= $Settings->pos_type ?>" />
                  <div class="row">
                      <div class="col-sm-7">
                         <div class="row">
<!--                            <div class="col-md-6">
                                <div class="form-group">
                                    <?= lang("product_type", "type") ?>
                                    <?php
                                    $opts = array('combo' => lang('combo'));
                                    echo form_dropdown('type', $opts, (isset($_POST['type']) ? $_POST['type'] : ($product ? $product->type : '')), 'class="form-control select2" id="type" required="required"');
                                    ?>
                                </div>
                            </div>                    -->
                            <div class="col-md-6">
                                <div class="form-group all ">
                                    <?= lang("Storage Type", "storage_type") ?>
                                    <?php                        
                                    $ist = ['packed'=>'Packed Products','loose'=>'Loose Products'];
                                    echo form_dropdown('storage_type', $ist, (isset($_POST['storage_type']) ? $_POST['storage_type'] : ''), 'class="form-control select2" id="storage_type" placeholder="' . lang("select") . " " . lang("division") . '" style="width:100%"')
                                    ?>
                                </div>
                             </div>                     
                         </div>
                          
                          <div class="row">
<!--                             <div class="col-md-6">
                                    <div class="form-group all">
                                        <?= lang("category", "category") ?>
                                        <?php
                                        $cat[''] = "";
                                        foreach ($categories as $category) {
                                            $cat[$category->id] = $category->name;
                                        }
                                        echo form_dropdown('category', $cat, (isset($_POST['category']) ? $_POST['category'] : ($product ? $product->category_id : '')), 'class="form-control select2" id="category"  placeholder="' . lang("select") . " " . lang("category") . '" required="required" style="width:100%"')
                                        ?>
                                    </div>
                                </div>-->
<!--                                <div class="col-md-6">
                                    <div class="form-group all">
                                        <?= lang("subcategory", "subcategory") ?>
                                        <div class="controls" id="subcat_data"> <?php
                                            echo form_input('subcategory', ($product ? $product->subcategory_id : ''), 'class="form-control " id="subcategory"  placeholder="' . lang("select_category_to_load") . '"');
                                            ?>
                                        </div>
                                    </div>
                                </div>-->
                          </div> 
                          
                           
                           <div class="row">
                                <div class="col-md-8">
                                  <div class="form-group all">
                                    <?= lang("product_name", "name") ?>
                                    <?= form_input('name', (isset($_POST['name']) ? $_POST['name'] : ($product ? $product->name : '')), 'class="form-control" id="name" required="required" autocomplete="off" '); ?>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                      <div class="form-group all">
                                        <?= lang("product_code", "code") ?>
                                            <div class="input-group">
                                                <?= form_input('code', (isset($_POST['code']) ? $_POST['code'] : ($product ? $product->code : '')), 'class="form-control" id="code"  required="required"') ?>
                                                <span class="input-group-addon pointer" id="random_num" style="padding: 1px 10px;">
                                                    <i class="fa fa-random"></i>
                                                </span>
                                            </div>
                                       <!-- <span class="help-block"><?= lang('you_scan_your_barcode_too') ?></span>-->
                                       </div>
                                </div>

                          </div>
                          
                          <div class="row">
<!--                            <div class="col-md-4">
                                <div class="form-group all">
                                            <?= lang("Article Number", "Article Number") ?>
                                    <?= form_input('article_code', (isset($_POST['article_code']) ? $_POST['article_code'] : ($product ? $product->article_code : '')), 'class="form-control" id="article_no"   type="text"  ') ?>
                                    <span id="error" style="color:#a94442; display: none;font-size:11px;">please enter numbers only</span>
                                </div> 
                            </div>  -->
                            <!-- End 12-03-19 -->
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang("hsn_code", "hsn_code") ?>
                                    <?= form_input('hsn_code', (isset($_POST['hsn_code']) ? $_POST['hsn_code'] : ($product ? $product->hsn_code : '')), 'class="form-control" id="hsn_code"  '); ?>
                                </div>
                            </div>
<!--                            <div class="col-md-4">
                                <div class="form-group all">                         
                                    <?= lang("brand", "brand") ?>
                                    <?php
                                    if (!empty($brands) && is_array($brands)) {

                                        $brnd[''] = "";

                                        foreach ($brands as $brand) {
                                            $brnd[$brand->id] = $brand->name;
                                        }                                    
                                        echo form_dropdown('brand', $brnd, (isset($_POST['brand']) ? $_POST['brand'] : ($product ? $product->brand : '')), 'class="form-control select2" id="brand" placeholder="' . lang("select") . " " . lang("brand") . '" style="width:100%"');
                                    }   
                                    ?>
                                </div>
                            </div>
                            -->
                            
                            
                            
                          </div>
                      
<!--                          <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group standard">
                                        <?= lang('product_unit', 'unit'); ?>
                                        <?php
                                        $pu[''] = lang('select') . ' ' . lang('unit');
                                        foreach ($base_units as $bu) {
                                            $pu[$bu->id] = $bu->name . ' (' . $bu->code . ')';
                                        }
                                        ?>
                                        <?= form_dropdown('unit', $pu, set_value('unit', ($product ? $product->unit : '')), 'class="form-control select2 tip" id="unit" required="required" style="width:100%;"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group standard">
                                        <?= lang('default_sale_unit', 'default_sale_unit'); ?>
                                        <?php $uopts[''] = lang('select_unit_first'); ?>
                                        <?= form_dropdown('default_sale_unit', $uopts, ($product ? $product->sale_unit : ''), 'class="form-control select2" id="default_sale_unit" style="width:100%;"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group standard">
                                        <?= lang('default_purchase_unit', 'default_purchase_unit'); ?>
                                        <?= form_dropdown('default_purchase_unit', $uopts, ($product ? $product->purchase_unit : ''), 'class="form-control select2" id="default_purchase_unit" style="width:100%;"'); ?>
                                    </div>
                                </div>
                         </div>-->
                    
                         <div class="row">
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang("product_cost", "cost") ?> *
                                    <?= form_input('cost', (isset($_POST['cost']) ? $_POST['cost'] : ($product ? $this->sma->formatDecimal($product->cost) : '0')), 'class="form-control tip custom_price" id="cost" required="required"') ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang("product_price", "price") ?>
                                    <?= form_input('price', (isset($_POST['price']) ? $_POST['price'] : ($product ? $this->sma->formatDecimal($product->price) : '')), 'class="form-control tip custom_price" id="price" required="required"') ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang("product_mrp", "price") ?>
                                    <?= form_input('mrp', (isset($_POST['mrp']) ? $_POST['mrp'] : ($product ? $this->sma->formatDecimal($product->mrp) : '')), 'class="form-control tip custom_price" id="mrp" required="required"') ?>
                                </div>
                            </div>
                        </div> 

                        <?php if (in_array($Settings->pos_type , ['restaurant'])) { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group all">
                                     <?= lang("Division", "Division") ?>
                                    <?php
                                    $br[''] = "";
                                    foreach ($divisions as $division) {
                                        $br[$division->id] = $division->name;
                                    }
                                    echo form_dropdown('division', $br, (isset($_POST['division']) ? $_POST['division'] : ($product ? $product->division : 1)), 'class="form-control select2" id="division" placeholder="' . lang("select") . " " . lang("division") . '" style="width:100%"')
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if (in_array($Settings->pos_type , ['fruits_vegetables', 'fruits_vegetabl', 'grocerylite', 'grocery'])) { ?>
<!--                        <div class="form-group all">
                            <?= lang("Product_Weight", 'weight') ?>
                            <div class="input-group">
                                <?= form_input('weight', (isset($_POST['weight']) ? $_POST['weight'] : ($product ? $product->weight : '')), 'class="form-control" id="weight" size="6" ') ?>
                                <span class="input-group-addon" style="padding: 1px 10px;">
                                    / Kilogram (KG)
                                </span>
                            </div>  
                            <span class="help-block"><?= lang('Products weight should be in Kilogram (Ex. 1Kg = 1 | 500Gm = 0.500 | 250Gm = 0.250, etc.)') ?></span>
                        </div>-->
                        <?php } ?>
                        <!-- 12-03-19 --> 
                        
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group standard">
                                    <?= lang("alert_quantity", "alert_quantity") ?>
                                    <div class="input-group"> <?= form_input('alert_quantity', (isset($_POST['alert_quantity']) ? $_POST['alert_quantity'] : ($product ? $this->sma->formatQuantity($product->alert_quantity) : '')), 'class="form-control tip" id="alert_quantity"') ?>
                                        <span class="input-group-addon">
                                            <input type="checkbox" name="track_quantity" id="track_quantity"
                                                   value="1" <?= ($product ? (isset($product->track_quantity) ? 'checked="checked"' : '') : 'checked="checked"') ?>>
                                        </span>
                                    </div>
                                </div>
                            </div>
<!--                            <div class="col-md-4">
                                <div class="form-group all">
                                    <?= lang("barcode_symbology", "barcode_symbology") ?>
                                    <?php
                                    $bs = array('code25' => 'Code25', 'code39' => 'Code39', 'code128' => 'Code128', 'ean8' => 'EAN8', 'ean13' => 'EAN13', 'upca' => 'UPC-A', 'upce' => 'UPC-E');
                                    echo form_dropdown('barcode_symbology', $bs, (isset($_POST['barcode_symbology']) ? $_POST['barcode_symbology'] : ($product ? $product->barcode_symbology : 'code128')), 'class="form-control select2" id="barcode_symbology" required="required" style="width:100%;"');
                                    ?>
                                </div>
                            </div>-->
                        </div>
                        <?php if ($Settings->tax1) { ?>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group all" >
                                        <?= lang("product_tax", "tax_rate") ?>
                                        <?php
                                        $tx[""] = "";
                                        foreach ($tax_rates as $tax) {
                                            if($tax->is_substitutable == 0) {
                                                $tx[$tax->id] = $tax->name;
                                            }
                                        }
                                        echo form_dropdown('tax_rate', $tx, (isset($_POST['tax_rate']) ? $_POST['tax_rate'] : ($product ? $product->tax_rate : ($Settings->pos_type == 'restaurant'?15:$Settings->default_tax_rate))), 'class="form-control select2" id="tax_rate" placeholder="' . lang("select") . ' ' . lang("product_tax") . '" style="width:100%"')
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                   <div class="form-group all"  >
                                       <?= lang("tax_method", "tax_method") ?>
                                       <?php
                                       $tm = array('0' => lang('inclusive'), '1' => lang('exclusive'));
                                       echo form_dropdown('tax_method', $tm, (isset($_POST['tax_method']) ? $_POST['tax_method'] : ($product ? $product->tax_method : '')), 'class="form-control select2" id="tax_method" placeholder="' . lang("select") . ' ' . lang("tax_method") . '" style="width:100%"')
                                       ?>
                                   </div>
                                </div>
                            </div>
                        <?php } ?>
                   
                          
                      </div>
                      <div class="col-sm-5">
                            <div class="combo" >

                                <div class="form-group">
                                    <?= lang("add_product", "add_item2") . ' (' . lang('not_with_variants') . ')'; ?>
                                    <?php echo form_input('add_item2', '', 'class="form-control ttip" id="add_item2" data-placement="top" data-trigger="focus" data-bv-notEmpty-message="' . lang('please_add_item2s_below') . '" placeholder="' . $this->lang->line("add_item") . '"'); ?>
                                </div>
                                <div class="control-group table-group">
                                    <label class="table-label" for="combo"><?= lang("combo_products"); ?></label>
                                    <div class="controls table-controls">
                                        <table id="prTable"
                                               class="table items table-striped table-bordered table-condensed table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="col-md-5 col-sm-5 col-xs-5"><?= lang("product_name") . " (" . $this->lang->line("product_code") . ")"; ?></th>
                                                    <th class="col-md-2 col-sm-2 col-xs-2"><?= lang("quantity"); ?></th>
                                                    <th class="col-md-3 col-sm-3 col-xs-3"><?= lang("unit_price"); ?></th>
                                                    <th class="col-md-3 col-sm-3 col-xs-3"><?= lang("Price"); ?></th>
                                                    <th class="col-md-1 col-sm-1 col-xs-1 text-center">
                                                        <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>

                      </div>
                
                
            </div>  
                   <div class="form-group">
                        <?php echo form_submit('add_product', $this->lang->line("add_product"), 'class="btn btn-primary" id="submitbtn"'); ?>
                    </div>
                
                <?= form_close(); ?>
        </div>
        
    </div>
    <script type="text/javascript" src="http://localhost/simplysafe/newdevpos/themes/default/assets/js/jquery-ui.min.js"></script>
     <script>
           var items = {};
        $(document).ready(function () {
        
          $(".select2").select2();
          
           $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
          });
            $('#subcategory').change(function () {
                var id = $(this).val();
                if (id) {
                    setProductTaxRate(id);
                }
            });
        });
       
        $("document").ready(function () {
           setTimeout(function () {
              var url = "<?= site_url('products/add') ?>";
              $("[id*='random_num']").trigger('click');
              return false;
           }, 10);
        });
        
        $('#category').change(function () {
            var v = $(this).val();
            $('#modal-loading').show();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubCategories') ?>/" + v,
                    dataType: "json",
                    success: function (scdata) {
                        console.log(scdata);
                        if (scdata != null) {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                                placeholder: "<?= lang('select_category_to_load') ?>",
                                data: scdata
                            });
                        } else {
                            $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                                placeholder: "<?= lang('no_subcategory') ?>",
                                data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                            });
                        }
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                        $('#modal-loading').hide();
                    }
                });

                setProductTaxRate(v);

            } else {
                $("#subcategory").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
                    placeholder: "<?= lang('select_category_to_load') ?>",
                    data: [{id: '', text: '<?= lang('select_category_to_load') ?>'}]
                });
            }
            $('#modal-loading').hide();
        });
        
         function setProductTaxRate(id) {

            $.ajax({
                type: "get",
                async: false,
                url: "<?= site_url('products/getCategoryTaxrate') ?>/" + id,
                dataType: "json",
                success: function (taxrateid) {
                    if (taxrateid != null) {
                        $('#tax_rate').val(taxrateid);
                        $('#tax_rate').select2().trigger('change');
                    } else {
                        $('#tax_rate').val(1);
                        $('#tax_rate').select2().trigger('change');
                    }
                },
                error: function () {
                    bootbox.alert('<?= lang('ajax_error') ?>');
                    $('#modal-loading').hide();
                }
            });
        }
        
        $('#unit').change(function (e) {
            var v = $(this).val();
            if (v) {
                $.ajax({
                    type: "get",
                    async: false,
                    url: "<?= site_url('products/getSubUnits') ?>/" + v,
                    dataType: "json",
                    success: function (data) {
                        $('#default_sale_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $('#default_purchase_unit').select2("destroy").empty().select2({minimumResultsForSearch: 7});
                        $.each(data, function () {
                            $("<option />", {value: this.id, text: this.name + ' (' + this.code + ')'}).appendTo($('#default_sale_unit'));
                            $("<option />", {value: this.id, text: this.name + ' (' + this.code + ')'}).appendTo($('#default_purchase_unit'));
                        });
                        $('#default_sale_unit').select2('val', v);
                        $('#default_purchase_unit').select2('val', v);
                    },
                    error: function () {
                        bootbox.alert('<?= lang('ajax_error') ?>');
                    }
                });
            } else {
                $('#default_sale_unit').select2("destroy").empty();
                $('#default_purchase_unit').select2("destroy").empty();
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_sale_unit'));
                $("<option />", {value: '', text: '<?= lang('select_unit_first') ?>'}).appendTo($('#default_purchase_unit'));
                $('#default_sale_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
                $('#default_purchase_unit').select2({minimumResultsForSearch: 7}).select2('val', '');
            }
        });
    
         $("#add_item2").autocomplete({
            source: '<?= site_url('products/suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item2').focus();
                    });
                    $(this).val('');
                } else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                } else if (ui.content.length == 1 && ui.content[0].id == 0) {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>', function () {
                        $('#add_item2').focus();
                    });
                    $(this).val('');

                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_product_item(ui.item);
                    if (row) {
                        $(this).val('');
                    }
                } else {
                    //audio_error.play();
                    bootbox.alert('<?= lang('no_product_found') ?>');
                }
            }
        });

        function add_product_item(item) {
            if (item == null) {
                return false;
            }
            item_id = item.id;
            if (items[item_id]) {
                items[item_id].qty = (parseFloat(items[item_id].qty) + 1).toFixed(2);
            } else {
                items[item_id] = item;
            }
            var pp = 0;
            $("#prTable tbody").empty();
            $.each(items, function () {
                var row_no = this.id;
                var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.id + '" data-item-id="' + row_no + '"></tr>');
                tr_html = '<td><input name="combo_item_id[]" type="hidden" value="' + this.id + '"><input name="combo_item_name[]" type="hidden" value="' + this.name + '"><input name="combo_item_code[]" type="hidden" value="' + this.code + '"><span id="name_' + row_no + '">' + this.name + ' (' + this.code + ')</span></td>';
                tr_html += '<td><input class="form-control text-center rquantity" name="combo_item_quantity[]" type="text" value="' + formatDecimal(this.qty) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="quantity_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td><input class="form-control text-center rprice" name="combo_item_price[]" type="text" value="' + formatDecimal(this.price) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_price_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td><input class="form-control text-center rtprice" name="combo_item_total_price[]" type="text" value="' + (formatDecimal(this.price) * formatDecimal(this.qty)) + '" data-id="' + row_no + '" data-item="' + this.id + '" id="combo_item_total_price_' + row_no + '" onClick="this.select();"></td>';
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.prependTo("#prTable");
                pp += formatDecimal(parseFloat(this.price) * parseFloat(this.qty));
            });
            $('.item_' + item_id).addClass('warning');
            $('#price').val(pp);
            $('#cost').val(pp);
            return true;
        }

        function calculate_price() {

            var rows = $('#prTable').children('tbody').children('tr');
            var pp = 0;
            var row_total = 0
            $.each(rows, function () {
                row_total = formatDecimal(parseFloat($(this).find('.rprice').val()) * parseFloat($(this).find('.rquantity').val()));
                $(this).find('.rtprice').val(row_total);
                //console.log(this);
                //alert(row_total);
                pp += row_total;
            });
            //console.log(pp);
            $('#price').val(pp);
            return true;
        }

        $(document).on('change textchange', '.rquantity, .rprice', function () {
            calculate_price();
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            delete items[id];
            $(this).closest('#row_' + id).remove();
            calculate_price();
        });
     </script>
     
     
     <!-- Urbanpiper --->

<script type="text/javascript">
    $('#urbanpiperitem').click(function () {
        let values = $(this).val();
        if (values == '1') {
            $('#urbanpipercontain').show();
        } else {
            $('#urbanpipercontain').hide();
        }
    });
//    $(document.activeElement).filter(':select#category:focus').blur();

    function onlyAlphabets1(e, t) {
        var charCode = e.which ? e.which : e.keyCode
        var ret = (charCode == 32 || (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90));
        document.getElementById("error2").style.display = ret ? "none" : "inline";
        return ret;
    }

    /****/
    function isNumberKey(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            document.getElementById("error").style.display = "inline";
            return false;
        }
        document.getElementById("error").style.display = "none";
        return true;
    }

    function isNumberKeyQua(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            document.getElementById("errorq").style.display = "inline";
            return false;
        }
        document.getElementById("errorq").style.display = "none";
        return true;
    }

    function isNumberKeyPrice(evt)
    {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            document.getElementById("errorp").style.display = "inline";
            return false;
        }
        document.getElementById("errorp").style.display = "none";
        return true;
    }
</script> 


<!-- End Urbanpiper --->

<script>
    
    $('form').submit(function (event) {
       $('#submitbtn').attr('disabled','true');
        event.preventDefault();
       var formdata =  $('#comboform').serialize();
         $.ajax({
            type:'ajax',
            dataType:'json',
            method:'post',
            url:'<?= base_url('products/addcombo') ?>',
            data: formdata,
            success:function(result){
                if(result.status == 'success'){
                    $('#add_item').val(result.data);
                    $('#add_item').autocomplete('search', $('#add_item').val());
                    $('.close').trigger('click');
                }else{
                    bootbox.alert(result.message);
                }
                 $('#submitbtn').attr('disabled','false');
            },error:function(){
                console.log('error');
                $('#submitbtn').attr('disabled','false');
            }
         });
        return  false;
      });


    function generateCardNo(x) {
            if (!x) {
                x = 16;
            }
            chars = "1234567890";
            no = "";
            for (var i = 0; i < x; i++) {
                var rnum = Math.floor(Math.random() * chars.length);
                no += chars.substring(rnum, rnum + 1);
            }
            return no;
        }
        
        $('#random_num').click(function () {
           $(this).parent('.input-group').children('input').val(generateCardNo(8));
        });
    
 </script>   
    


    
</div>   