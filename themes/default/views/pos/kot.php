<html>
    <head>
        <meta charset="utf-8">
        <title><?= lang('pos_module') . " | " . $Settings->site_name; ?></title>
        <base href="<?= base_url() ?>"/>   
        <meta http-equiv="cache-control" content="no-cache"  />
        <meta http-equiv="expires" content="0"/>
        <meta http-equiv="pragma" content="no-cache"/>
        <meta content='12' http-equiv='refresh'/>
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
        <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
        <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css"/>
        <link rel="stylesheet" href="<?= $assets ?>styles/style.css" type="text/css"/>
        <link rel="stylesheet" href="<?= $assets ?>pos/css/posajax.css" type="text/css"/>

        <link rel="stylesheet" href="<?= $assets ?>pos/css/default-inline.css" type="text/css"/> 

        <link rel="stylesheet" href="<?= $assets ?>pos/css/print.css" type="text/css" media="print"/>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-migrate-1.2.1.min.js"></script>    
        <script src="<?= $assets ?>pos/js/jquery.validate.min.js"></script>
      
        <?php $logopath = base_url("assets/icons/") ?>
        <link rel="apple-touch-icon" sizes="57x57" href="<?= $logopath ?>apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="<?= $logopath ?>apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?= $logopath ?>apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?= $logopath ?>apple-icon-76x76.png">        
        <link rel="icon" type="image/png" sizes="192x192"  href="<?= $logopath ?>android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= $logopath ?>favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="<?= $logopath ?>favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?= $logopath ?>favicon-16x16.png">
        <link rel="manifest" href="<?= $logopath ?>manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="<?= $logopath ?>ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <style>
            .title{
                background: #373B44;  /* fallback for old browsers */
                background: -webkit-linear-gradient(to bottom, #4286f4, #373B44);  /* Chrome 10-25, Safari 5.1-6 */
                background: linear-gradient(to bottom, #4286f4, #373B44); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */

                padding: 10px;
                margin: 0;
                color: #FFF;
            }
            
            .tab-content{
                border: 2px solid #ccc;
                background: #ECE9E6;  /* fallback for old browsers */
                background: -webkit-linear-gradient(to top, #FFFFFF, #ECE9E6);  /* Chrome 10-25, Safari 5.1-6 */
                background: linear-gradient(to top, #FFFFFF, #ECE9E6); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */

            }
        </style>
    </head>
    <body>
        <div id="wrapper">
            <div class="row title">
                <div class="col-sm-5">
                    <strong> <?= date('d/m/Y h:i') ?> </strong><br/>
                    <strong id="countdown"></strong>
                    
                </div>
                <div class="col-sm-6">
                    <h1 style="padding:0; margin:0px"> Table View </h1>
                </div>
               <div class="col-sm-1">
                    <button type="button" onclick="removeOrderTypeAndRedirect()" class="btn btn-warning"> POS </button>
                </div>
            </div>
            <div class="container">
                <!--<p><?= lang('type_reference_note'); ?></p>-->
                <div class="form-group">

                    <?php
                    $options[""] = "";
                    foreach ($tables as $key => $table) {
                        $options[$table->id] = $table->name;
                    }
                    echo form_dropdown('table_id', $options, '', ' id="kot_restaurant_tables" style="display:none;" class="form-control restaurant_tables" style="width:100%; "');
                    ?>
                </div>
<!--                <div class="form-group">
                    <?= lang("reference_note", "reference_note"); ?>
                    <?php echo form_input('reference_note', $reference_note, 'readonly="true" class="form-control kb-text" id="reference_note"'); ?>
                </div>-->
                <?php
                if (isset($Settings->pos_type) && $Settings->pos_type == 'restaurant') {
                    ?>

                    <div class="row">
<!--                        <div class="col-xs-6">
                            <div class="chk-btn">
                                <input class="carry_out" type="radio" name="carry_out" value="carry_out" onchange="valueChanged()"/>
                                <label>Carry Out</label>
                            </div>
                            <div class="information-field" style="display:none">
                                <div class="row">
                                    <div class="col-md-12">
                                        <input name="carry_out_customer_info" type="text" class="form-control" placeholder="Customer name / Mobile no">

                                    </div>
                                </div>
                            </div>
                        </div>-->
<!--                        <div class="col-xs-6">
                            <div class="chk-btn">
                                <input class="table-num" type="radio" name="carry_out" value="carry_out" checked="checked"  onchange="valueChanged()"/>
                                <label>Enter table number</label>
                            </div>
                        </div>-->
                    </div>
                    <div class="radio-btn-section">
                        <ul class="nav nav-tabs">
                            <?php foreach ($tables_groups as $key => $grp_table) { ?>      
                                <li class="<?= ($key == 0 ? 'active' : '') ?> table-tab" data-value="<?= str_replace(" ","",$grp_table['table_group']) ?>"><a data-toggle="tab" href="#<?= ($grp_table['table_group'] ? str_replace(" ", "", $grp_table['table_group']) : 'default' ) ?>"><?= ($grp_table['table_group'] ? $grp_table['table_group'] : 'Default' ) ?></a></li>
                            <?php } ?>

                        </ul>

                        <div class="tab-content container">
                           
                            <?php foreach ($tables_groups as $key => $items_tableG) { ?>
                                <div id="<?= ($items_tableG['table_group'] ? str_replace(" ", "", $items_tableG['table_group']) : 'default' ) ?>" class="tab-pane fade <?= ($key == 0 ? 'in active' : '') ?>">
                                    <strong style="display: block;clear: both; color: #FF0000; padding:10px"><?= ($items_tableG['table_group'] ? $items_tableG['table_group'] : 'Default' ) ?></strong>
                                    <div class="row">
                                        <?php
                                        foreach ($tables as $rtkey => $rtval) {
                                           $getSubtable = getSubTables($rtval->id);
                                            if ($user->table_assign) {

                                                $tableselected = explode(",", $user->table_assign);
                                                if (in_array($rtval->id, $tableselected)) {
                                                    $billPrint = '';
                                                    if ($items_tableG['table_group'] == $rtval->table_group) {
                                                        $checked = (($table_id == $rtval->id) && ($rtval->name == $rtval->suspended_note)) ? "checked" : "";
                                                        $style_lable = '';
                                                        $call_id = '';
                                                        if (($table_id != $rtval->id) && ($rtval->name == $rtval->suspended_note)) {
                                                            //var_dump($rtval->status);
                                                            $style_lable = ($rtval->status == 'Booked') ? "background:red" : "";
                                                            $call_id = $rtval->suspended_id;
                                                            //$style_desable = ($rtval->status=='Booked')? "disabled" :"";
                                                        }
                                                        if ($rtval->bill_printed) {
                                                            $billPrint = 'style="background-color:#ff8400eb;border:1px solid #ff8400eb;"';
                                                        }
                                                        echo '<div class="col-sm-2 table-block" id="table_block-' . $rtval->id . '">';
                                                        if ($Owner || $Admin || $GP['pos_clear_table']) { 
                                                         if($call_id){
                                                             echo '<button delete-id="'.$call_id.'" type="button" class="btn btn-danger pull-right delete_suspend"> <i class="fa fa-trash"></i> </button>';
                                                          }
                                                        }

                                                        echo '<div class="rd-btn resturent_table_group" ' . (($call_id) ? 'onclick=callTable("' . $call_id . '")' : 'onclick=selecttable("'.$rtval->id.'")') . ' >';
                                                        echo '<input ' . $checked . ' type="radio"  table_id="' . $rtval->id . '"  name="gender" id="' . str_replace(" ", "_", $rtval->name) . '" value="' . $rtval->name . '" ' . $style_desable . '>';
                                                        echo '<lable style="' . $style_lable . '">' . $rtval->name . '</lable>';

                                                        echo '</div>';
                                                        echo '<div style="display:block; padding: 8px;">';
                                                        echo '<button type="button" onclick="update_seats(\'' . $rtval->id . '\')" id="table-' . $rtval->id . '" class=" btn btn-xs btn-warning">Guests : ' . $rtval->seats . '</button> ';

                                                        if ($Owner || $Admin || $GP['checkout']) { 
                                                            if($call_id){
                                                               echo '<button type="button" onclick="checkout(\'' . $call_id. '\')"  class=" btn btn-xs btn-success">Checkout </button> ';
                                                            }
                                                        }

                                                         if ($Owner || $Admin || $GP['bill_print']) { 
                                                           echo '<button type="button" onclick="bill_print(\'' . $rtval->id . '\')"  id="billprint-' . $rtval->id . '" class=" btn btn-xs btn-info" ' . $billPrint . ($call_id ? '' :'disabled="true"') . '>Bill Print</button> ';
                                                        }
                                                        if($getSubtable){
                                                            
                                                           echo '<div class="btn-group">';
                                                                echo '<button class="btn btn-primary  btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                                                     echo 'Sub Table';
                                                                echo '</button>';
                                                                echo '<div class="dropdown-menu" style="padding: 10px 20px; color:#000">';
                                                                   echo '<ul class="sub_table">';
//                                                                        echo '<li onclick="add_subtable(\'A\','.$rtval->id.')" class="dropdown-item" >A</</li>';
                                                                    foreach($getSubtable as $sub_items){
                                                                        echo '<li onclick="add_subtable('.$sub_items->id.')" class="dropdown-item" >'.$sub_items->name.'</li>';
                                                                    }
                                                                   echo '</ul>';
                                                              echo ' </div>';
                                                           echo '</div>'; 
                                                        }
  
                                                        
                                                        echo '</div>';
                                                        echo '</div>';
                                                        
                                                        
                                                         if($getSubtable){
                                                            foreach($getSubtable as $sub_items){
                                                                $billPrintSub = '';
                                                                $style_lablesub = '';
                                                                     $call_idsub = '';
                                                                     if (($table_id != $sub_items->id) && ($sub_items->name == $sub_items->suspended_note)) {
                                                                         $style_lablesub = ($sub_items->status == 'Booked') ? "background:red" : "";
                                                                         $call_idsub = $sub_items->suspended_id;
                                                                     }
                                                                if($sub_items->bill_printed){
                                                                    $billPrintSub= 'style="background-color:#ff8400eb;border:1px solid #ff8400eb;"';
                                                                }
                                                               echo '<div class="col-sm-2 table-block" '.($call_idsub?'style="display:block;': 'style="display:none;').' " id="table_block-'.$sub_items->id.'">';
                                                                
                                                            if ($Owner || $Admin || $GP['pos_clear_table']) { 
                                                                if($call_idsub){
                                                                        echo '<button delete-id="'.$call_idsub.'" type="button" class="btn btn-danger pull-right delete_suspend"> <i class="fa fa-trash"></i> </button>';
                                                                     }
                                                               }

                                                                   echo '<div class="rd-btn resturent_table_group"' .($call_idsub?'onclick=callTable("' . $call_idsub . '")' : 'onclick=selecttable("'.$sub_items->id.'")').'  >';
                                                                        echo '<input ' . $checked . ' type="radio"  table_id="' . $sub_items->id . '"  name="gender" id="' .  $sub_items->name.'" value="' . $sub_items->name. '" ' . $style_desable . '>';
                                                                        echo '<lable style="' . $style_lablesub . '">' . $sub_items->name . '</lable>';
                                                                   echo '</div>';
                                                                   echo '<div style="display:block; padding: 8px;">';
                                                                        echo '<button type="button" onclick="delete_subtable('.$sub_items->id.')" class=" btn btn-xs btn-danger">Delete</button> ';
                                                                        echo '<button type="button" onclick="update_seats(\''.$sub_items->id.'\')" id="table-'.$sub_items->id.'" class=" btn btn-xs btn-warning">Guests : '.$sub_items->seats.'</button> ';

                                                                       if ($Owner || $Admin || $GP['checkout']) { 
                                                                           if($call_idsub){
                                                                              echo '<button type="button" onclick="checkout(\'' . $call_idsub. '\')"  class=" btn btn-xs btn-success">Checkout </button> ';
                                                                            }
                                                                         }


                                                                       if ($Owner || $Admin || $GP['bill_print']) { 
                                                                        echo '<button type="button" onclick="bill_print(\''.$sub_items->id.'\')"  id="billprint-'.$sub_items->id.'" class=" btn btn-xs btn-info" '.$billPrintSub.  ($call_idsub? '' :'disabled="true"') .'>Bill Print</button> ';

                                                                       }
                                                                  echo '</div>';
                                                                echo '</div>';
                                                            }
                                                        }  
                                                    
                                                    }
                                                }
                                            } else {
                                                $billPrint = '';
                                                if ($items_tableG['table_group'] == $rtval->table_group) {
                                                    $checked = (($table_id == $rtval->id) && ($rtval->name == $rtval->suspended_note)) ? "checked" : "";
                                                    $style_lable = '';
                                                     $call_id  ='';
                                                    if (($table_id != $rtval->id) && ($rtval->name == $rtval->suspended_note)) {
                                                        $style_lable = ($rtval->status == 'Booked') ? "background:red" : "";
                                                        $call_id = $rtval->suspended_id;
                                                    }
                                                    if ($rtval->bill_printed) {
                                                        $billPrint = 'style="background-color:#ff8400eb;border:1px solid #ff8400eb;"';
                                                    }
                                                    echo '<div class="col-sm-2 table-block" id="table_block-' . $rtval->id . '">';
                                                  if ($Owner || $Admin || $GP['pos_clear_table']) { 
                                                   if($call_id){
                                                        echo '<button delete-id="'.$call_id.'" type="button" class="btn btn-danger pull-right delete_suspend"> <i class="fa fa-trash"></i> </button>';
                                                    }
                                                   }

                                                   echo '<div class="rd-btn resturent_table_group" ' . (($call_id) ? 'onclick=callTable("' . $call_id . '")' : 'onclick=selecttable("'.$rtval->id.'")') . ' >';
                                                    echo '<input ' . $checked . ' type="radio"  table_id="' . $rtval->id . '"  name="gender" id="' . str_replace(" ", "_", $rtval->name) . '" value="' . $rtval->name . '" ' . $style_desable . '>';
                                                    echo '<lable style="' . $style_lable . '">' . $rtval->name . '</lable>';

                                                    echo '</div>';
                                                    echo '<div style="display:block; padding: 8px;">';
                                                    echo '<button type="button" onclick="update_seats(\'' . $rtval->id . '\')" id="table-' . $rtval->id . '" class=" btn btn-xs btn-warning">Guests : ' . $rtval->seats . '</button> ';

                                                    if ($Owner || $Admin || $GP['checkout']) { 
                                                        if($call_id){
                                                           echo '<button type="button" onclick="checkout(\'' . $call_id. '\')"  class=" btn btn-xs btn-success">Checkout </button> ';
                                                        }
                                                    }

                                                   if ($Owner || $Admin || $GP['bill_print']) { 
                                                    echo '<button type="button" onclick="bill_print(\'' . $rtval->id . '\')"  id="billprint-' . $rtval->id . '" class=" btn btn-xs btn-info" ' . $billPrint .($call_id? '' :'disabled="true"') . '>Bill Print</button> ';
                                                    }
                                                        if($getSubtable){
                                                           echo '<div class="btn-group">';
                                                                echo '<button class="btn btn-primary  btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                                                     echo 'Sub Table';
                                                                echo '</button>';
                                                                echo '<div class="dropdown-menu" style="padding: 10px 20px; color:#000">';
                                                                  echo '<ul class="sub_table">';
                                                                    foreach($getSubtable as $sub_items){
                                                                        echo '<li onclick="add_subtable('.$sub_items->id.')" class="dropdown-item" >'.$sub_items->name.'</li>';
                                                                    }
                                                                  echo '</ul>';
                                                              echo ' </div>';
                                                           echo '</div>'; 
                                                        }
                                                       echo '</div>';
                                                    echo '</div>';
                                                  
                                                   if($getSubtable){
                                                       foreach($getSubtable as $sub_items){
                                                           $billPrintSub = '';
                                                           $style_lablesub = '';
                                                                $call_idsub = '';
                                                                if (($table_id != $sub_items->id) && ($sub_items->name == $sub_items->suspended_note)) {
                                                                    $style_lablesub = ($sub_items->status == 'Booked') ? "background:red" : "";
                                                                    $call_idsub = $sub_items->suspended_id;
                                                                }


                                                               
                                                           if($sub_items->bill_printed){
                                                               $billPrintSub= 'style="background-color:#ff8400eb;border:1px solid #ff8400eb;"';
                                                           }
                                                          echo '<div class="col-sm-2 table-block" '.($call_idsub?'style="display:block;': 'style="display:none;').' " id="table_block-'.$sub_items->id.'">';

                                                         if ($Owner || $Admin || $GP['pos_clear_table']) { 
                                                           if($call_idsub){
                                                                echo '<button delete-id="'.$call_idsub.'" type="button" class="btn btn-danger pull-right delete_suspend"> <i class="fa fa-trash"></i> </button>';
                                                             }
                                                          }  
                                                              echo '<div class="rd-btn resturent_table_group"' .($call_idsub?'onclick=callTable("' . $call_idsub . '")' : 'onclick=selecttable("'.$sub_items->id.'")').'  >';
                                                                   echo '<input ' . $checked . ' type="radio"  table_id="' . $sub_items->id . '"  name="gender" id="' .  $sub_items->name.'" value="' . $sub_items->name. '" ' . $style_desable . '>';
                                                                   echo '<lable style="' . $style_lablesub . '">' . $sub_items->name . '</lable>';
                                                              echo '</div>';
                                                              echo '<div style="display:block; padding: 8px;">';
                                                                   echo '<button type="button" onclick="delete_subtable('.$sub_items->id.')" class=" btn btn-xs btn-danger">Delete</button> ';
                                                                   echo '<button type="button" onclick="update_seats(\''.$sub_items->id.'\')" id="table-'.$sub_items->id.'" class=" btn btn-xs btn-warning">Guests : '.$sub_items->seats.'</button> ';

                                                                   if ($Owner || $Admin || $GP['checkout']) { 
                                                                       if($call_idsub){
                                                                            echo '<button type="button" onclick="checkout(\'' . $call_idsub. '\')"  class=" btn btn-xs btn-success">Checkout </button> ';
                                                                        }
                                                                   }                                                              
     
                                                                  if ($Owner || $Admin || $GP['bill_print']) { 
                                                                   echo '<button type="button" onclick="bill_print(\''.$sub_items->id.'\')"  id="billprint-'.$sub_items->id.'" class=" btn btn-xs btn-info" '.$billPrintSub. ($call_idsub? '' :'disabled="true"') .'>Bill Print</button> ';
                                                                  }   
                                                             echo '</div>';
                                                           echo '</div>';
                                                       }
                                                   }  
                                                    
                                                }
                                            }
                                        }
                                        ?>

                                    </div>
                                </div>
                                    <?php } ?>

                        </div>
                        <script type="text/javascript">
                            function valueChanged()
                            {
                                if ($('.carry_out').is(":checked")) {
                                    $("#reference_note").val('');
                                    $(".information-field").show();
                                } else {
                                    $("#reference_note").val('');
                                    $(".information-field").hide();
                                }
                                if ($('.table-num').is(":checked")) {
                                    $("#reference_note").val('');
                                    $(".radio-btn-section").show();
                                } else {
                                    $("#reference_note").val('');
                                    $(".radio-btn-section").hide();
                                }


                            }
                        </script>


                        <div style="clear:both"></div>
                    </div>
<?php }?>

            </div>

        </div> 

        <script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/select2.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?= $assets ?>js/custom.js"></script>

        <script>
                                    /**
                                     * Print
                                     **/
                                    function openWin(div)
                                    {
                                        var winPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
                                        winPrint.document.write(div);
                                        winPrint.document.close();
                                        winPrint.focus();
                                        winPrint.print();
                                        setTimeout(function () {
                                            winPrint.close();
                                        }, 100)
                                    }


                                    /**
                                     * Bill Print
                                     * @param {type} table_id
                                     * @returns {undefined}
                                     */
                                    function bill_print(table_id) {
                                        $('#billprint-' + table_id).css("background-color", "#ff8400eb");
                                        $('#billprint-' + table_id).css("border", "1px solid #ff8400eb");

                                        /*if (table_id == localStorage.getItem('table_id')) {
                                            $('#print_bill').trigger('click');
                                        } else {*/
                                            $.ajax({
                                                type: 'ajax',
                                                dataType: 'html',
                                                method: 'get',
                                                url: '<?= base_url("pos/billPrint") ?>/' + table_id,
                                                async: false,
                                                success: function (result) {
                                                    openWin(result);
                                                }, error: function (erorr) {
                                                    console.log('erorr');
                                                }
                                            });
//                                        }

                                        $.ajax({
                                            type: 'ajax',
                                            dataType: 'json',
                                            method: 'get',
                                            url: "<?= base_url('pos/tableBillPrint') ?>/" + table_id,
                                            data: {'billPrint': '1'},
                                            async: false,
                                            success: function (result) {
                                                console.log(result);
                                            }, error: function (error) {
                                                console.log('erorr');
                                            }
                                        });
                                    }

                                    /**
                                     *  Update Seats
                                     * @param {type} tableId
                                     * @returns {undefined}            */
                                    function update_seats(tableId) {
                                        bootbox.prompt({
                                            title: "Enter Table Guest",
                                            inputType: 'number',
                                            callback: function (result) {
                                                if (result) {
                                                    $.ajax({
                                                        type: 'ajax',
                                                        dataType: 'json',
                                                        method: 'get',
                                                        data: {
                                                            'table_id': tableId,
                                                            'seats': result
                                                        },
                                                        url:'<?= base_url("pos/table_seats")?>',
                                                        async: false,
                                                        success: function (response) {
                                                            if (response.status) {
                                                                $('#table-' + tableId).html("Guests : " + response.seats);
                                                            }
                                                            console.log(response);
                                                        }, error: function (error) {
                                                            console.log(error);
                                                        }
                                                    });
                                                }
                                                console.log(result);
                                            }
                                        });

                                    }


                                    function callTable(passid) {
                               
                                        window.location.href = '<?= base_url('pos/index') ?>/' + passid;

                                    }
                                    
                                    
                                    function selecttable(passid){
                                        if (localStorage.getItem('positems')) {
                                            localStorage.removeItem('positems');
                                        }
                                        if (localStorage.getItem('active_offers')) {
                                            localStorage.removeItem('active_offers');
                                        }
                                        if (localStorage.getItem('applyOffers')) {
                                            localStorage.removeItem('applyOffers');
                                        }
                                        if (localStorage.getItem('posdiscount')) {
                                            localStorage.removeItem('posdiscount');
                                        }
                                        if (localStorage.getItem('postax2')) {
                                            localStorage.removeItem('postax2');
                                        }
                                        if (localStorage.getItem('posshipping')) {
                                            localStorage.removeItem('posshipping');
                                        }
                                        if (localStorage.getItem('poswarehouse')) {
                                            localStorage.removeItem('poswarehouse');
                                        }
                                        if (localStorage.getItem('posnote')) {
                                            localStorage.removeItem('posnote');
                                        }
                                        if (localStorage.getItem('poscustomer')) {
                                            localStorage.removeItem('poscustomer');
                                        }
                                        if (localStorage.getItem('posbiller')) {
                                            localStorage.removeItem('posbiller');
                                        }
                                        if (localStorage.getItem('poscurrency')) {
                                            localStorage.removeItem('poscurrency');
                                        }
                                        if (localStorage.getItem('posnote')) {
                                            localStorage.removeItem('posnote');
                                        }
                                        if (localStorage.getItem('staffnote')) {
                                            localStorage.removeItem('staffnote');
                                        }

                                        if (localStorage.getItem('table_id')) {
                                            localStorage.removeItem('table_id');
                                        }

                                       if (localStorage.getItem('olditems')) {
                                            localStorage.removeItem('olditems');
                                        }

                                         if (localStorage.getItem('table_name')) {
                                           
                                            localStorage.removeItem('table_name');
                                            
                                        } 
                                       window.location.href = '<?= base_url('pos') ?>?select_table=' + passid;
 
                                    }
                                    
            <?php if ($this->session->userdata('remove_posls')) { ?>
                    if (localStorage.getItem('positems')) {
                        localStorage.removeItem('positems');
                    }
                    if (localStorage.getItem('active_offers')) {
                        localStorage.removeItem('active_offers');
                    }
                    if (localStorage.getItem('applyOffers')) {
                        localStorage.removeItem('applyOffers');
                    }
                    if (localStorage.getItem('posdiscount')) {
                        localStorage.removeItem('posdiscount');
                    }
                    if (localStorage.getItem('postax2')) {
                        localStorage.removeItem('postax2');
                    }
                    if (localStorage.getItem('posshipping')) {
                        localStorage.removeItem('posshipping');
                    }
                    if (localStorage.getItem('poswarehouse')) {
                        localStorage.removeItem('poswarehouse');
                    }
                    if (localStorage.getItem('posnote')) {
                        localStorage.removeItem('posnote');
                    }
                    if (localStorage.getItem('poscustomer')) {
                        localStorage.removeItem('poscustomer');
                    }
                    if (localStorage.getItem('posbiller')) {
                        localStorage.removeItem('posbiller');
                    }
                    if (localStorage.getItem('poscurrency')) {
                        localStorage.removeItem('poscurrency');
                    }
                    if (localStorage.getItem('posnote')) {
                        localStorage.removeItem('posnote');
                    }
                    if (localStorage.getItem('staffnote')) {
                        localStorage.removeItem('staffnote');
                    }

                    if (localStorage.getItem('table_id')) {
                        localStorage.removeItem('table_id');
                    }
                     
                   if (localStorage.getItem('olditems')) {
                        localStorage.removeItem('olditems');
                    }
                    
                     if (localStorage.getItem('table_name')) {
                        $('#reference_note').val('');
                        $('#active_table').html('--');
                        localStorage.removeItem('table_name');
                         $('#suspend').show();  
                         $('#suspend_sale1').hide();
                    }
    <?php  $this->sma->unset_data('remove_posls');}?>    
        </script>
        <script>
            var timeleft = 12;
            var downloadTimer = setInterval(function(){
              if(timeleft <= 0){
                clearInterval(downloadTimer);
                document.getElementById("countdown").innerHTML = "Refresh";
              } else {
                document.getElementById("countdown").innerHTML = timeleft + " seconds remaining";
              }
              timeleft -= 1;
            }, 1000);
            
         function add_subtable(table_id){
             $('#table_block-'+table_id).show();
         }       
             
         function delete_subtable(table_id){
              $('#table_block-'+table_id).hide();
         }    
             
        </script>   

       <script>
            $('.table-tab').click(function(){
               var tagname = $(this).attr('data-value');
               localStorage.setItem('tabactive',tagname);
               
            });
           $( document ).ready(function() {
                $(".table-tab").removeClass("active");
                if(localStorage.getItem('tabactive')){
                    var tag = localStorage.getItem('tabactive');
                    $('a[href="#'+tag+'"]').tab('show')
                }
            });


             $(document).on("click",".delete_suspend",function() {
                var result = confirm("Want to delete?");
                if (result) {
                    //Logic to delete the item

                    var delete_id = $(this).attr("delete-id");
                    deleteSuspend(delete_id);
                    $(this).parent().remove();
                    window.location.reload();
                }
              });
              
              function deleteSuspend (did) {
                if (did > 0) {
                    return $.ajax({
                      type: 'get',
                      url: 'pos/deleteSuspend/' + did,
                      data: {},
                      dataType: 'json',
                      success: function (data) {
                        console.log('------deleteSuspend log------')
                        console.log(data)
                        }
                    })
                }
              }



               /**
                * Checkout
                **/ 
               function checkout(passid){
                  if (localStorage.getItem('positems')) {
                      localStorage.removeItem('positems');
                  }
                  if (localStorage.getItem('active_offers')) {
                      localStorage.removeItem('active_offers');
                  }
                  if (localStorage.getItem('applyOffers')) {
                      localStorage.removeItem('applyOffers');
                  }
                  if (localStorage.getItem('posdiscount')) {
                      localStorage.removeItem('posdiscount');
                  }
                  if (localStorage.getItem('postax2')) {
                      localStorage.removeItem('postax2');
                  }
                  if (localStorage.getItem('posshipping')) {
                      localStorage.removeItem('posshipping');
                  }
                  if (localStorage.getItem('poswarehouse')) {
                      localStorage.removeItem('poswarehouse');
                  }
                  if (localStorage.getItem('posnote')) {
                      localStorage.removeItem('posnote');
                  }
                  if (localStorage.getItem('poscustomer')) {
                      localStorage.removeItem('poscustomer');
                  }
                  if (localStorage.getItem('posbiller')) {
                      localStorage.removeItem('posbiller');
                  }
                  if (localStorage.getItem('poscurrency')) {
                      localStorage.removeItem('poscurrency');
                  }
                  if (localStorage.getItem('posnote')) {
                      localStorage.removeItem('posnote');
                  }
                  if (localStorage.getItem('staffnote')) {
                      localStorage.removeItem('staffnote');
                  }

                  if (localStorage.getItem('table_id')) {
                      localStorage.removeItem('table_id');
                  }

                  if (localStorage.getItem('olditems')) {
                      localStorage.removeItem('olditems');
                  }

                  if (localStorage.getItem('table_name')) {
                      localStorage.removeItem('table_name');
                  } 
                  window.location.href = '<?= base_url('pos/index') ?>/' + passid+'?checkout=1';
 
              }
              
              function removeOrderTypeAndRedirect() {
                    // Remove "ordertype" from localStorage
                    localStorage.removeItem("ordertype");
                    
                    // Redirect to POS page
                    window.location.href = '<?= base_url('pos') ?>';
                }
              /**
               * End Checkout 
               **/
              
        </script>  
    </body>        
</html>
