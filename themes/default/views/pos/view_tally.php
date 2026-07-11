<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= $page_title . " " . lang("no") . " " . $inv->id; ?></title>
        <base href="<?= base_url() ?>"/>
        <meta http-equiv="cache-control" content="max-age=0"/>
        <meta http-equiv="cache-control" content="no-cache"/>
        <meta http-equiv="expires" content="0"/>
        <meta http-equiv="pragma" content="no-cache"/>
        <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
        <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css"/>
        <style>
            #wrapper { max-width: <?php echo $default_printer->width ?>px; margin: 0 auto; padding-top: 20px; }
            #wrapper h4{text-align:center}
            .page_layout{boarder:1px solid #000}
            .table>thead>tr>th,.table>thead, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td { padding: 1px 1px;     border: 1px solid #000; }
            .table{margin-bottom:0px}
            .biller-info{
                padding:2px 2px 2px 5px;
                border-bottom: 2px solid #000;}
            .sales-info{padding:3px 2px 2px 5px;}
            .sales-info table{margin-top:2em; width: 100%;}
            .invoice-details{width: 50%; padding: 0px !important;}
            .invoice-details table{width: 100%}
            .invoice-details table tr td{padding:0px 3px; border:1px solid #000;vertical-align: top; font-size: 13px;}
            .td-border-left{border-top: 0px !important; border-left: 0px !important;}
            .td-border-right{border-top: 0px !important; border-right: 0px !important;}
            .items-details tbody tr td{padding:2px;border-top: none;border-bottom: none;}
            .items-details tfoot tr th{padding:5px;}
            .amount_words{border: 1px solid #000; border-top: 0;padding: 5px; }
            .tax_summary_head{border-left: 1px solid; border-right: 1px solid;padding: 5px;margin: 0 !important;;}
        </style>
    </head>

    <body>
        <div id="wrapper">
            <h4> Tax Invoice </h4>   
            <div class="page_layout" style="border: 1px solid #000;">
                <table class="table">
                    <tr>
                        <td style="width: 50%; padding: 0px">
                            <div class="biller-info">
                                <strong>
                                    <?= ( $biller->company != '-' ? $biller->company : $biller->name) ?>
                                </strong>
                                <p style='margin: 0 0 5px; '>
                                    <?= $biller->address . " " . $biller->city . " " . $biller->postal_code . " " . $biller->state . " " . $biller->country . '. ' ?><br/>
                                    <?= lang("Mob No") . ":&nbsp;" . $biller->phone ?><br/>
                                    <?php
                                    if ($inv->GstSale):
                                        echo lang("gstn_no") . ":&nbsp;" . $biller->gstn_no . '<br/>';
                                    endif;
                                    ?>
                                    State Name : <?= $biller->state ?> <br/>
                                    E-Mail : <?= $b_email ?> 
                                </p>

                            </div>
                            <div class="sales-info">
                                Buyer <br/>
                                <strong><?= $inv->customer ?></strong>

                                <table>
                                    <?php if ($inv->GstSale && !empty($customer->gstn_no)) { ?>
                                        <tr>
                                            <td> <?= lang("gstn_no") ?> </td>  
                                            <td> :<?= $customer->gstn_no ?></td>
                                        </tr>
                                    <?php } elseif (!empty($customer->vat_no)) { ?>
                                        <tr>
                                            <td> <?= lang('vat_no') ?> </td>  
                                            <td> : <?= $customer->vat_no ?></td>
                                        </tr>
                                    <?php } ?>

                                    <tr>
                                        <td>State Name</td>
                                        <td>: <?= $customer->state ?></td>
                                    </tr>   
                                </table>

                            </div>

                        </td>
                        <td class="invoice-details" >
                            <table>
                                <tr>
                                    <td style="width: 50%" class="td-border-left"> 
                                        <?= lang("Invoice No.") ?><br/>
                                        <b><?= $inv->invoice_no ?></b>
                                    </td>
                                    <td style="width: 50%" class="td-border-right"> 
                                        <?= lang("date") ?><br/>
                                        <b><?= date('d/m/Y g:i A', strtotime($inv->date)) ?></b>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50%" class="td-border-left"> 
                                        <?= lang("Delivery Note") ?><br/>

                                    </td>
                                    <td style="width: 50%" class="td-border-right"> 
                                        <?= lang("Mode/Terms of Payment") ?><br/>
                                        <b>&nbsp;</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50%" class="td-border-left"> 
                                        <?= lang("reference_no") ?><br/>
                                        <b><?= $inv->reference_no; ?></b>
                                    </td>
                                    <td style="width: 50%" class="td-border-right"> 
                                        <?= lang("Other Reference(s)") ?><br/>
                                        <b>&nbsp;</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50%" class="td-border-left"> 
                                        <?= lang("Buyer’s Order No.") ?><br/>
                                        <b>&nbsp;</b>
                                    </td>
                                    <td style="width: 50%" class="td-border-right"> 
                                        <?= lang("Dated") ?><br/>
                                        <b>&nbsp;</b>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="width: 50%" class="td-border-left"> 
                                        <?= lang("Despatch Document No") ?><br/>
                                        <b>&nbsp;</b>
                                    </td>
                                    <td style="width: 50%" class="td-border-right"> 
                                        <?= lang("Delivery Note Date") ?><br/>
                                        <b>&nbsp;</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50%" class="td-border-left"> 
                                        <?= lang("Despatched through") ?><br/>
                                        <b>&nbsp;</b>
                                    </td>
                                    <td style="width: 50%" class="td-border-right"> 
                                        <?= lang("Destination") ?><br/>
                                        <b>&nbsp;</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="height: 70px !important; border-bottom: 0px;" class=" td-border-left td-border-right">
                                        <?= lang("Terms of Delivery") ?><br/>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table> 

                <!--Items Details-->
                <table class="table items-details">
                    <thead>
                        <tr>
                            <td class="text-center"> Sr.No</td>
                            <td class="text-center"> Description of Goods </td>
                            <td class="text-center"> HSN/SAC </td>
                            <td class="text-center"> Quantity</td>
                            <td class="text-center"> Rate </td>
                            <td class="text-center"> per</td>
                            <td class="text-center"> Amount </td>
                        </tr>
                    </thead>
                    <tbody>
                      
                      <?php 
                        $total_qty = $total_amt = 0;
                       foreach($rows as $key => $row_items){ 
                           $total_qty += $row_items->quantity;
                           $total_amt += $row_items->subtotal;
                           
                            if (isset($tax_summary[$row->tax_code])) {
                            $tax_summary[$row->tax_code]['items'] += $row_items->quantity;
                            $tax_summary[$row->tax_code]['tax'] += $row_items->item_tax;
                            $tax_summary[$row->tax_code]['amt'] += ($row_items->quantity * $row_items->net_unit_price);
                        } else {
                            $tax_summary[$row->tax_code]['items'] = $row_items->quantity;
                            $tax_summary[$row->tax_code]['tax'] = $row_items->item_tax;
                            $tax_summary[$row->tax_code]['amt'] = ($row_items->quantity * $row_items->net_unit_price);
                            $tax_summary[$row->tax_code]['name'] = $row_items->tax_name;
                            $tax_summary[$row->tax_code]['code'] = $row_items->tax_code;
                            $tax_summary[$row->tax_code]['rate'] = $row_items->tax_rate;
                            $tax_summary[$row->tax_code]['tax_rate_id'] = $row_items->tax_rate_id;
                        }
                           ?>
                        <tr>
                            <td class="text-center"> <?= $key + 1 ?> </td>
                            <td style="text-transform: uppercase;width: 53%;"> <strong > <?= $row_items->product_name ?> </strong></td>
                            <td > <?= $row_items->hsn_code ?> </td>
                            <td class="text-right"> <?= number_format($row_items->quantity,2) ?> <?= $row_items->product_unit_code ?> </td>
                            <td class="text-right"> <?= number_format($row_items->unit_price,2) ?> </td>
                            <td> <?= $row_items->product_unit_code ?> </td>
                            <td class="text-right"> <?= number_format($row_items->subtotal,2) ?> </td>
                        </tr>    
                        
                        <?php if($row_items->gst_rate){
                            if($row_items->igst >=1 ){ ?>
                                <tr>
                                    <td> </td>
                                    <td class="text-right"><strong> IGST <?= number_format($row_items->gst_rate) ?>% </strong> 
                                        <br/>
                                        <?= number_format($row_items->igst,2) ?>
                                    </td>
                                    <td> </td>
                                    <td> </td>
                                    <td> </td>
                                    <td> </td>
                                    <td> </td>
                                </tr>
                           <?php } else { ?>
                                <tr>
                                    <td> </td>
                                    <td class="text-right"> <strong>CGST <?= number_format(($row_items->gst_rate/2),2) ?>%</strong>
                                        <br/> 
                                        <?= number_format($row_items->cgst,2) ?> 
                                    </td>
                                    <td> </td>
                                    <td> </td>
                                    <td>  </td>
                                    <td> </td>
                                    <td> </td>
                                </tr>
                                 <tr>
                                    <td> </td>
                                    <td class="text-right"><strong> SGST <?= number_format(($row_items->gst_rate/2),2) ?>% </strong>
                                        <br/> 
                                        <?= number_format($row_items->sgst,2) ?>
                                    </td>
                                    <td> </td>
                                    <td> </td>
                                    <td> </td>
                                    <td> </td>
                                    <td> </td>
                                </tr>
                                
                           <?php } 
                             } ?>
                     <?php } ?>  
                    </tbody>
                    <tfoot>
                        <tr>
                            <th> </th>
                            <th class="text-right"> Total  </th>
                            <th> </th>
                            <th class="text-right"> <?= number_format($total_qty,2) ?></th>
                            <th> </th>
                            <th> </th>
                            <th class="text-right"> <?= number_format($total_amt,2) ?></th>
                        </tr>
                    </tfoot>

                </table>
                <!--End Items Details-->
                
                <div class="amount_words"> 
                    <span> Amount Chargeable (in words) </span>
                    <span style="float:right;padding-right: 5px;"> E. & O.E </span>
                    <br/>
                    <strong>
                        <?=$this->sma->formatMoney(0);?>  <?= ucwords($this->sma->convert_number_to_words($total_amt)) ?>  Only
                    </strong>
                </div>
                <?php 
                if ($Settings->invoice_view == 1) {
                        $resTaxTbl = $this->sma->taxInvoiceTableCSI($tax_summary, $inv, $return_sale, $Settings, 1);
                        echo $resTaxTbl;
                    }
                 ?>
                
                <table class="table">
                    <tr>
                        <td style="width:50%;padding-left:10px;">
                               Company’s PAN  : <?= $biller->pan_card ?><br/>
                                <strong><u>Declaration</u></strong>
                                <br/>
                                 <?= $this->sma->decode_html($biller->invoice_footer); ?>

                        </td>
                        <td style="text-align: right;">
                            <strong style=" margin-right: 1em;">  For <?= $Settings->site_name?> </strong>
                                <br/><br/><br/><br/>
                            <span style=" margin-right: 1em;margin-top: 5em;">  Authorised Signatory </span>
                        </td>
                    </tr>
                </table>

            </div>
        </div>     
        
             <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">
                    <hr>
                    <?php if ($message) { ?>
                        <div class="alert alert-success">
                            <?= is_array($message) ? print_r($message, true) : $message; ?>
                        </div>
                    <?php }
                    ?>

                    <?php if ($pos_settings->java_applet) { ?>
                        <span class="col-xs-12"><a class="btn btn-block btn-primary" onClick="printReceipt()"><?= lang("print"); ?></a></span>
                        <span class="col-xs-12"><a class="btn btn-block btn-info" type="button" onClick="openCashDrawer()">Open Cash Drawer</a></span>
                        <div style="clear:both;"></div>
                    <?php } else { ?>
                        <span class="pull-right col-xs-12">
                            <a href="javascript:window.print()" id="web_print" class="btn btn-block btn-primary"
                               onClick="window.print();
                                               return false;"><?= lang("web_print"); ?></a>
                        </span>
                    <?php }
                    ?>
                    <span class="pull-left col-xs-12"><a class="btn btn-block btn-success" href="#" id="email"><?= lang("email"); ?></a></span>
                    <?php if (!empty($sms_url)) : ?>
                        <span class="pull-left col-xs-12">

                            <?php $DisSMSLink = (int) $sms_limit < 1 ? '<br><a href="http://simplypos.in/login.php" style="color:#000" target="_blank">Please Login on merchant panel & Rechagre Now</a>' : ''; ?>

                            <?php if ($sms_limit > 0): ?>
                                <a class="btn btn-block btn-info" href="javascript:void(0);" id="sms_bill">SMS</a>
                            <?php else: ?>
                                <a class="btn btn-block btn-info" style="cursor:no-drop" href="javascript:void(0);" id="sms_bill_not"> SMS </a>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                    <span class="col-xs-12">
                        <?php
                        $lasturl = explode("/", $_SERVER['HTTP_REFERER']);

                        $last_segment = sizeof($lasturl) - 1;

                        if ($lasturl[$last_segment - 1] . '/' . $lasturl[$last_segment] == 'pos/sales') {
                            ?>
                            <a class="btn btn-block btn-warning"  href="<?= site_url('pos/sales'); ?>">
                                Back To POS Sales
                            </a>
    <?php } elseif ($lasturl[$last_segment] == 'sales') { ?>
                            <a class="btn btn-block btn-warning"  href="<?= site_url('sales'); ?>">
                                Back To Sales
                            </a>
    <?php } elseif ($lasturl[$last_segment] == 'all_sale_lists') { ?>
                            <a class="btn btn-block btn-warning"  href="<?= site_url('sales/all_sale_lists'); ?>">
                                Back To All Sales
                            </a>
                        <?php } else { ?>
                            <a class="btn btn-block btn-warning"  href="<?= site_url(isset($_SESSION['Sales']) ? 'Sales' : 'pos'); ?>"><?= (isset($_SESSION['Sales']) ? "Back To Sales" : lang("back_to_pos")); ?></a>
    <?php } ?>

                    </span>
    <?php if (!$pos_settings->java_applet) { ?>
                        <div style="clear:both;"></div>
                        <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
                            <p style="font-weight:bold;"> Please don't forget to disable the header and footer in browser print settings.</p>
                            <p style="text-transform: capitalize;"><strong>FF:</strong> File &gt; Print Setup &gt; Margin &amp;
                                Header/Footer Make all --blank--</p>
                            <p style="text-transform: capitalize;"><strong>chrome:</strong> Menu &gt; Print &gt; Disable Header/Footer
                                in Option &amp; Set Margins to None</p>
                        </div>
                    <?php }
                    ?>
                    <div style="clear:both;"></div>
                    <div class="col-xs-12" style="background:#F5F5F5; padding:10px;">
                        <div class="sms_note blue">(Note : Available SMS limit <?php print((int) $sms_limit) ?> <?php echo $DisSMSLink; ?>)</div>
                    </div> 
                    <div style="clear:both;"></div>
                </div>
            </div>
    </body>
</html>