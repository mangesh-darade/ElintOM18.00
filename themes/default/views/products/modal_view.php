<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.bcimg {
    height: 50px;
}

.color-black {
    color: black;
}

.text-center {
    text-align: center;
}

.img-fld {
    width: 100px;
    margin-right: 1rem;
    height: 50px;
}

.product-details {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: auto;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    margin-top: 2rem;
}
@media screen and (max-width: 1024px) {
    .product-details {
    display: flex;
    flex-direction: column;
  }
  .modal-content{
    width: 95%;
    margin-left: 9px;
  }
  .d-flx{
    display: flex;
    flex-wrap: wrap;
  }
}

.product-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: calc(25% - 10px);
    background-color: #fff;
    padding: 5px 1.2rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 0.5rem;
    min-height: 2rem;
}

/* .product-label {
    font-weight: bold;
    color: #333;
    flex-basis: 80%;
    padding: 0rem;
    font-size:1.3rem;
} */

.product-label {
    font-weight: bold;
    color: #333;
    flex-basis: 80%;
    padding: 0rem;
    font-size: 1.3rem;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex-basis: 25%;
}

.product-content {
    color: #666;
    flex-basis: 100%;
    text-align: start;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 150px;
}

.product-content1 {
    color: #666;
    flex-basis: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: flex;
    flex-wrap: wrap;
    margin: 0rem;
}

.product-content span.label-primary {
    background-color: #428bca;
    color: #fff;
    padding: 5px 10px;
    border-radius: 5px;
}

@media (max-width: 768px) {
    .product-row {
        width: 100%;
    }
}

.display-flx {
    display: flex;
    align-items: center;
}

.display-flx1 {
    display: flex;
    align-items: center;
    justify-content: center;
}


.custom-wid {
    width: 1015px;
}

.p0 {
    padding: 0rem;
}

.product-row1 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: calc(80% - 20px);
    background-color: #fff;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 0.5rem;
}

.m1 {
    margin: 0.5rem;
}

.qrimg {
    width: 50px !important;
}
.barcode-qrcode-item.content{
    width: 10%!important;
}
</style>
<div class="modal-dialog modal-lg custom-wid">
    <div class="modal-content">
        <div class="modal-header display-flx">
            <?php if (!$Supplier || !$Customer) { ?>
            <div class="buttons">
                <div class="btn-group btn-group-justified">
                    <div class="btn-group">
                        <a href="<?= site_url('products/print_barcodes/' . $product->id) ?>" class="tip btn btn-primary"
                            title="<?= lang('print_barcode_label') ?>">
                            <i class="fa fa-print"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('print_barcode_label') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('products/pdf/' . $product->id) ?>" class="tip btn btn-primary"
                            title="<?= lang('pdf') ?>">
                            <i class="fa fa-download"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= site_url('products/edit/' . $product->id) ?>" class="tip btn btn-warning"
                            title="<?= lang('edit_product') ?>">
                            <i class="fa fa-edit"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="#" class="tip btn btn-danger bpo" title="<b><?= lang("delete_product") ?></b>"
                            data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= site_url('products/delete/' . $product->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                            data-html="true" data-placement="top">
                            <i class="fa fa-trash-o"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                        </a>
                    </div>

                    <!-- Print Button Added -->
                    <div class="btn-group">
                        <a href="javascript:void(0);" onclick="window.print();" class="tip btn btn-info"
                            title="<?= lang('print_page') ?>">
                            <i class="fa fa-print"></i>
                            <span class="hidden-sm hidden-xs"><?= lang('print_page') ?></span>
                        </a>
                    </div>

                </div>
            </div>

            <script type="text/javascript">
            $(document).ready(function() {
                $('.tip').tooltip();
            });
            </script>
            <?php } ?>

            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="disp">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-xs-4 display-flx">
                                <img id="pr-image" src="<?= base_url('assets/mdata/' . $this->Customer_assets . '/uploads/' . $product->image) ?>"
                                    alt="<?= $product->name ?>" class="img-responsive img-thumbnail img-fld" />

                                <div id="multiimages" class="padding10">
                                    <?php if (!empty($images)) {
                                // echo '<a class="img-thumbnail change_img" href="' . base_url() . 'assets/uploads/' . $product->image . '" style="margin-right:5px;">
                                //         <img class="img-responsive" src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->image . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" />
                                //       </a>';
                                foreach ($images as $ph) {
                                echo '<div class="gallery-image"><a class="img-thumbnail change_img" href="' . base_url() . 'assets/uploads/' . $ph->photo . '" style="margin-right:5px;"><img class="img-responsive" src="' . base_url('assets/mdata/' . $this->Customer_assets . '/uploads/' . $ph->photo) . '" alt="' . $ph->photo . '" style="width:' . $Settings->twidth . 'px; height:' . $Settings->theight . 'px;" /></a>';
                                if ($Owner || $Admin || $GP['products-edit']) {
                                    echo '<a href="#" class="delimg" data-item-id="'.$ph->id.'"><i class="fa fa-times"></i></a>';
                                }
                                echo '</div>';
                                }
                                    }
                                    ?>
                                    <h4 class="modal-title" id="myModalLabel"><?= $product->name; ?></h4>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center barcodealign">
                                <div class="display-flx1">
                                    <?= $this->sma->save_barcode($product->code, $product->barcode_symbology, 66, false); ?>
                                    <h3 class="color-black"></h3>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="barcode-qrcode-item content">
                                    <?= $this->sma->qrcode('link', urlencode(site_url('products/view/' . $product->id)), 2); ?>
                                </div>
                            <!-- <?= $this->sma->save_barcode($product->code, $product->barcode_symbology, 66, false); ?> -->
                                <h3></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12">
                        <div class="table-responsive">
                            <div class="product-details">
                                <div class="d-flx">
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("type"); ?></div>
                                        <div class="product-content col-md-6"><?= lang($product->type); ?></div>
                                    </div>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("code"); ?></div>
                                        <div class="product-content col-md-6"><?= $product->code; ?></div>
                                    </div>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("brand"); ?></div>
                                        <div class="product-content col-md-6"><?= $brand ? $brand->name : ''; ?></div>
                                    </div>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("category"); ?></div>
                                        <div class="product-content col-md-6"><?= $category->name; ?></div>
                                    </div>
                                    <?php if ($product->subcategory_id) { ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("subcategory"); ?></div>
                                        <div class="product-content col-md-6"><?= $subcategory->name; ?></div>
                                    </div>
                                    <?php } ?>
                                     <?php if ($Settings->other_category_for_product): ?>
                                    <?php
                                    // Safety fallback
                                     $other_category = trim($other_category);
                                     if (empty($other_category)) {
                                     $other_category = 'No categories';
                                    }

                                     $max_length = 40; // max visible length
                                     $display_text = $other_category;
                                     if (strlen($other_category) > $max_length) {
                                     $display_text = substr($other_category, 0, $max_length) . '...';
                                    }
                                    ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("Other Category"); ?></div>
                                        <div class="product-content col-md-6">
                                            <span title="<?= htmlspecialchars($other_category, ENT_QUOTES, 'UTF-8') ?>"
                                                style="cursor:Pointer;">
                                                <?= htmlspecialchars($display_text, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                    </div>

                                    <?php endif; ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("unit"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $unit->name . ' (' . $unit->code . ')'; ?></div>
                                    </div>
                                    <?php if ($Owner || $Admin) { ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("cost"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $this->sma->formatMoney($product->cost); ?></div>
                                    </div>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("price"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $this->sma->formatMoney($product->price); ?></div>
                                    </div>
                                    <?php if ($product->promotion) { ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("promotion"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $this->sma->formatMoney($product->promo_price); ?>
                                            (<?= $this->sma->hrsd($product->start_date); ?> -
                                            <?= $this->sma->hrsd($product->end_date); ?>)
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <?php } else {
                                    if ($this->session->userdata('show_cost')) { ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("cost"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $this->sma->formatMoney($product->cost); ?></div>
                                    </div>
                                    <?php } if ($this->session->userdata('show_price')) { ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("price"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $this->sma->formatMoney($product->price); ?></div>
                                    </div>
                                    <?php if ($product->promotion) { ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("promotion"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $this->sma->formatMoney($product->promo_price); ?>
                                            (<?= $this->sma->hrsd($product->start_date); ?> -
                                            <?= $this->sma->hrsd($product->end_date); ?>)
                                        </div>
                                    </div>
                                    <?php }
                                    }
                                    } ?>
                                     <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("mrp"); ?></div>
                                        <div class="product-content col-md-6">
                                        <?= $this->sma->formatMoney($product->mrp); ?></div>
                                    </div>

                                    <?php if ($product->tax_rate) { ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("tax_rate"); ?></div>
                                        <div class="product-content col-md-6"><?= $tax_rate->name; ?></div>
                                    </div>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("tax_method"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $product->tax_method == 0 ? lang('inclusive') : lang('exclusive'); ?>
                                        </div>
                                    </div>
                                    <?php } ?>

                                    <?php if ($product->alert_quantity != 0) { ?>
                                    <div class="product-row col-md-4">
                                        <div class="product-label col-md-6"><?= lang("alert_quantity"); ?></div>
                                        <div class="product-content col-md-6">
                                            <?= $this->sma->formatQuantity($product->alert_quantity); ?></div>
                                    </div>
                                    <?php } ?>

                                    <div class="product-row col-md-6">
                                        <div class="product-label col-md-6"><?= lang("Article No"); ?>
                                        </div>
                                        <div class="product-content col-md-6"><?= $product->article_code; ?></div>
                                    </div>
                                    <div class="product-row col-md-6">
                                        <div class="product-label col-md-6"><?= lang("Quantity"); ?>
                                        </div>
                                        <div class="product-content col-md-6"><?= $product->quantity; ?></div>
                                    </div>

                                    <div class="col-md-12 p0">


                                        <?php if ($variants) { ?>
                                        <div class="product-row1 col-md-12">
                                            <div class="product-label col-md-6"><?= lang("product_variants"); ?></div>
                                            <div class="product-content1 col-md-6 p0">
                                                <?php foreach ($variants as $variant) {
                                            //$options_color = $this->sma->getProductOptionsByGroupId($product->id, COLOR);
                                            echo '<span class="label label-primary m1">' . $variant->name .'</span> ';
                                        } ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <?php if ($colors) { ?>
                                        <div class="product-row col-md-12">
                                            <div class="product-label col-md-6"><?= lang("Product_Color"); ?></div>
                                            <div class="product-content2 col-md-6">
                                                <?php foreach ($colors as $color) {
                                                    echo '<span class="label label-primary m1">' . $color->name .'</span> ';
                                                } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-xs-12">
                    <div class="row">
                        <div class="col-xs-5">
                            <?php if ($product->cf1 || $product->cf2 || $product->cf3 || $product->cf4 || $product->cf5 || $product->cf6) { ?>
                            <h3 class="bold"><?= lang('custom_fields') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                    <thead>
                                        <tr>
                                            <th><?= lang('value') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
										// $product_row = $this->products_model->getProduct_typeByID($product->cf2);
										
                                        if ($product->cf1) {
                                            echo '<tr><td>' . $product->cf1 . '</td></tr>';
                                        }
                                        if ($product->cf2) {
                                            echo '<tr><td>' . $product_row->cf2 . '</td></tr>';
                                        }
                                        if ($product->cf3) {
                                            echo '<tr><td>' . $product->cf3 . '</td></tr>';
                                        }
                                        if ($product->cf4) {
                                            echo '<tr><td>' . $product->cf4 . '</td></tr>';
                                        }
                                        if ($product->cf5) {
                                            echo '<tr><td>' . $product->cf5 . '</td></tr>';
                                        }
                                        if ($product->cf6) {
                                            echo '<tr><td>' . $product->cf6 . '</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } ?>

                            <?php if ((!$Supplier || !$Customer) && !empty($warehouses) && $product->type == 'standard') { ?>
                            <h3 class="bold"><?= lang('warehouse_quantity') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                    <thead>
                                        <tr>
                                            <th><?= lang('warehouse_name') ?></th>
                                            <th><?= lang('quantity') . ' (' . lang('rack') . ')'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($warehouses as $warehouse) {
                                        if ($warehouse->quantity != 0) {
                                            echo '<tr><td>' . $warehouse->name . ' (' . $warehouse->code . ')</td><td><strong>' . $this->sma->formatQuantity($warehouse->quantity) . '</strong>' . ($warehouse->rack ? ' (' . $warehouse->rack . ')' : '') . '</td></tr>';
                                        }
                                    } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="col-xs-7">
                            <?php if ($product->type == 'combo') { ?>
                            <h3 class="bold"><?= lang('combo_items') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                    <thead>
                                        <tr>
                                            <th><?= lang('product_name') ?></th>
                                            <th><?= lang('quantity') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($combo_items as $combo_item) {
                                    echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->sma->formatQuantity($combo_item->qty) . '</td></tr>';
                                } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } ?>
                            <?php if ($product->type == 'Bundle') { ?>
                            <h3 class="bold"><?= lang('combo_items') ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable two-columns">
                                    <thead>
                                        <tr>
                                            <th><?= lang('product_name') ?></th>
                                            <th><?= lang('quantity') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($combo_items as $combo_item) {
                                    echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->sma->formatQuantity($combo_item->qty) . '</td></tr>';
                                } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } ?>
                            <?php if (!empty($options)) { ?>
                            <h3 class="bold"><?= lang('product_variants_quantity'); ?></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-condensed dfTable">
                                    <thead>
                                        <tr>
                                        <th><?= lang('warehouse_name') ?></th>
                                                <th><?= lang('product_variant'); ?></th>
                                                <th><?= lang('quantity') . ' (' . lang('rack') . ')'; ?></th>
                                                <th><?= lang('Price'); ?></th>
                                                <th><?= lang('MRP'); ?></th>
                                                <th><?= lang('Avg_Cost'); ?></th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                foreach ($options as $option) {
                                    // if ($option->wh_qty != 0) {
                                        echo '<tr><td>' . $option->wh_name . '</td><td>' . $option->name . '</td><td class="text-center">' . $this->sma->formatQuantity($option->wh_qty) . '</td><td class="text-center">' . $this->sma->formatQuantity($option->price) . '</td><td class="text-center">' . $this->sma->formatQuantity($option->mrp) . '</td><td class="text-center">' . $this->sma->formatQuantity($option->avg_cost) . '</td>';
                                        echo '</tr>';
                                    // }
                                }
                                ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <!-- <h2>Product Variant Barcode</h2> -->
                    <?php
                if(!empty($variants_barcode)){
                    foreach($variants_barcode as $key => $val){
                        $VariantBarcode = $val->Variants_stock;
                    }
                    if($VariantBarcode!=''){
                    $ExploadeBarcode = explode(',',$VariantBarcode);
                    foreach($ExploadeBarcode as $kbarcode => $valbarcode){
                                        $str = trim(preg_replace('/\s*\([^)]*\)/', '', $valbarcode));
                        echo $str .'<br>';
                    }
                    }
                } 
                ?>
                </div>
                <div class="col-xs-12">
                    <?= $product->details ? '<div class="panel panel-success"><div class="panel-heading">' . lang('product_details_for_invoice') . '</div><div class="panel-body">' . $product->details . '</div></div>' : ''; ?>
                    <?= $product->product_details ? '<div class="panel panel-primary"><div class="panel-heading">' . lang('product_details') . '</div><div class="panel-body">' . $product->product_details . '</div></div>' : ''; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $('.change_img').click(function(event) {
        event.preventDefault();
        var img_src = $(this).find("img").attr('src');
        $('#pr-image').attr('src', img_src);
        return false;
    });
});
</script>