<style>
    .remove-btn{
        position: absolute;
        right: 8px;
        top: -12px;
        border-radius: 50%;
        width: 22px;
        height: 23px;
    }
    .producttable th{text-align: left !important;}

    @media print {
        #printbtn{display:none;}
    }

</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue">
            Stock Check
        </h2>

    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <fieldset>
                    <div class="row">

                        <div class="col-md-3">
                            <label> Scan Barcode <span id="scanpoductcount" class="btn btn-xs btn-success"></span></label>
                            <input type="text" id="scanbarcode"  name="barcode" class="form-control" />
                        </div>
                        <div class="col-md-1 text-center">
                            <label><br/>OR</label>
                        </div>
                        <div class="col-md-4">
                            <label> Import File <a href="<?= base_url('assets/csv/sample_barcode.xlsx') ?>" ><i class="fa fa-download"></i> Download Sample File</a> </label>
                            <input type="file" accept=".xls, .xlsx" id="barcodefile"  name="barcodefile" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label> Category  <strong>*</strong> <input type="checkbox"   id="allcategory" name="all_cateory"> All</label>
                            <select name="category[]" id="category" class="form-control" style="height: auto;" multiple="true" >
                                
                                <?php foreach ($category as $category_val) { ?>
                                    <option value="<?= $category_val->id ?>"><?= $category_val->name ?></option>
                                <?php } ?>
                            </select>   
                        </div>
                        </div>
                        <div class="row"> 
                        <div class="col-md-3">
                            <label> Warehouse <strong>*</strong></label>
                            <select name="warehouse" id="warehouse" class="form-control" >
                                <option value="0">Select Warehouse</option>
                                <?php foreach ($warehouse as $wh) { ?>
                                    <option value="<?= $wh->id ?>"><?= $wh->name ?></option>
                                <?php } ?>
                            </select>   


                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label><br>
                            <button class="btn btn-success" id="btnsubmit"  type="submit">Submit</button>
                        </div>
                        <div class="col-md-1">
                            <label>&nbsp;</label><br>
                            <button type="button" onclick="reset()" class="btn btn-danger"><i class="fa fa-refresh"></i> Reset</button>
                        </div>

                    </div>

                </fieldset>
                <hr/>
                <div id="showscanbarcode">
                   
                </div>


            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.14.3/xlsx.full.min.js"></script>
<script type="text/javascript">
    var scanbarcode = JSON.parse(localStorage.getItem('scanbarcode')) || [];
    $(document).ready(function () {

        $('#scanbarcode').focus();
        $('#scanbarcode').blur(function () {
            if ($('#scanbarcode').val() != '') {
                collectionBarcode($('#scanbarcode').val());
                $('#scanbarcode').val('');
                $('#scanbarcode').focus();
            }
        });

        $('#scanbarcode').change(function () {
            if ($('#scanbarcode').val() != '') {
                collectionBarcode($('#scanbarcode').val());
                $('#scanbarcode').val('');
                $('#scanbarcode').focus();
            }
        });

        loadBarcode();
    });


    function collectionBarcode(passvalue) {
        scanbarcode.push(passvalue);
        localStorage.setItem("scanbarcode", JSON.stringify(scanbarcode));
        loadBarcode();

    }

    function reset() {
        localStorage.removeItem("scanbarcode");
        window.location.reload();

    }


    function loadBarcode() {
        if (localStorage.getItem('scanbarcode')) {
            productBarcode = scanbarcode;
            var productcount = productBarcode.length;
            var barcodevalue = '<table class="table table-barcode">'; 
                       barcodevalue +='<thead><tr>';
                                barcodevalue +='<th style="text-align: left;"> Sr. No</th>';
                                barcodevalue +='<th style="text-align: left;">Product Barcode</th>';
                                barcodevalue +='<th><i class="fa fa-times"></i></th></tr>';   
                        barcodevalue +='</thead> <tbody> <tr>';
                            
            $.each(productBarcode, function (index) {

                var item = this;
                barcodevalue += '<tr><td>'+(index + 1)+'</td><td><strong class=" " style="width:100%">' + item + '</strong></td><td class="text-center"><button class="btn btn-danger btn-xs " onclick="removebarcode(' + index + ')"><i class="fa fa-times"></i></button></td></tr>';
            });
            barcodevalue +='</tr></tbody></table>';
            $('#showscanbarcode').html(barcodevalue);
            $('#scanpoductcount').html(productcount);
        }
    }

    function removebarcode(passindex) {
        productBarcode = scanbarcode;
        productBarcode.splice(passindex, 1);
        localStorage.setItem("scanbarcode", JSON.stringify(productBarcode));
        loadBarcode();
    }


    $('#btnsubmit').click(function () {
        var category = $('#category').val();
        var warehouse = $('#warehouse').val();
        const cb = document.getElementById('allcategory');
        if (scanbarcode === undefined || scanbarcode.length == 0) {
            bootbox.alert('Please scan product barcode');

        } else if(warehouse=='0'){
           bootbox.alert('Please select warehouse');
        }  else if(cb.checked || category){
            getReports();
        }else {
            bootbox.alert('Please select category or all category any one');
        }
    });

    function getReports() {
     var x = document.getElementById("allcategory").checked;
        $.ajax({
            type: 'ajax',
            dataType: 'json',
            method: 'POST',
            url: '<?= base_url('CheckStock/getStockReport') ?>',
            data: {
                warehouse: $('#warehouse').val(),
                scanproduct: scanbarcode,
                allcategory: x,
                category: $('#category').val(),
                token: '<?= $this->security->get_csrf_hash() ?>',
            },
            success: function (result) {
                
                $('#showscanbarcode').html(result);
                $('#note').html($('#notData').html());
                $('#notData').html('');

            }, error: function () {
                console.log('error');
            }
        });
    }

    function print_list() {
        var printContents = document.getElementById('showproductlist').innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
    }

</script> 
   <script>
    var file = document.getElementById('barcodefile')
var viewer = document.getElementById('dataviewer')
file.addEventListener('change', importFile);

function importFile(evt) {
  var f = evt.target.files[0];

  if (f) {
    var r = new FileReader();
    r.onload = e => {
      var contents = processExcel(e.target.result);
      console.log(contents)
    }
    r.readAsBinaryString(f);
  } else {
    console.log("Failed to load file");
  }
}

function processExcel(data) {
  var workbook = XLSX.read(data, {
    type: 'binary'
  });

  var firstSheet = workbook.SheetNames[0];
  var data = to_json(workbook);
  return data
};

function to_json(workbook) {
  var result = {};
  workbook.SheetNames.forEach(function(sheetName) {
    var roa = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {
      header: 1
    });
    if (roa.length) result[sheetName] = roa;
    roa.splice('0', 1);
    scanbarcode = [];
    $.each(roa, function (index, value) {
        scanbarcode.push(value[0]);
    });
        localStorage.setItem("scanbarcode", JSON.stringify(scanbarcode));
        loadBarcode();
    
  });
  return JSON.stringify(result, 2, 2);
};
</script>


<script>
    function add_adjustment(product_id, type_t, qty_q, variant_v, warehouse) {
        if (warehouse > 0) {
            var product = [];
            var type = [];
            var qty = [];
            var variant = [];
            product.push(product_id);
            type.push(type_t);
            qty.push(qty_q);
            variant.push(variant_v);
            $.ajax({
                type: 'ajax',
                dataType: 'json',
                data: {
                    product_id: product,
                    type: type,
                    quantity: qty,
                    variant: variant,
                    warehouse: $('#warehouse').val(),
                    token: '<?= $this->security->get_csrf_hash() ?>',
                },
                url: 'CheckStock/add_adjustment',
                method: 'POST',
                success: function (result) {
                    console.log(result.messages);
                    if (result.status) {
                        bootbox.alert(result.messages);
                        getReports();
                    } else {
                        bootbox.alert(result.messages);
                    }

                }, error: function () {
                    console.log('error');
                }
            });
        } else {
            bootbox.alert('Please select warehouse');
        }
    }
</script>    
   
<!-- Export Reports-->
<script>
function exportTableToExcel() {
    $('.hidecheckbox').remove();
    var tableId = 'product_table', filename = 'stock_chack_<?= date("d-m-Y")  ?>';
    let dataType = 'application/vnd.ms-excel';
    let extension = '.xls';

    let base64 = function(s) {
        return window.btoa(unescape(encodeURIComponent(s)))
    };

    let template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>';
    let render = function(template, content) {
        return template.replace(/{(\w+)}/g, function(m, p) { return content[p]; });
    };

    let tableElement = document.getElementById(tableId);

    let tableExcel = render(template, {
        worksheet: filename,
        table: tableElement.innerHTML
    });

    filename = filename + extension;

    if (navigator.msSaveOrOpenBlob)
    {
        let blob = new Blob(
            [ '\ufeff', tableExcel ],
            { type: dataType }
        );

        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        let downloadLink = document.createElement("a");

        document.body.appendChild(downloadLink);

        downloadLink.href = 'data:' + dataType + ';base64,' + base64(tableExcel);

        downloadLink.download = filename;

        downloadLink.click();
    }
   getReports();
}
</script> 


<script type="text/javascript">
        function bulk_adjustment(){
            var product = [];
            var type = [];
            var qty = [];
            var variant = [];
            var warehouse = $('#warehouse').val();
            if(warehouse > 0){
                $('input:checkbox[name=addAdjustment]:checked').each(function(){
                    var data = $(this).val();
                    var exp = data.split("~");
                     
                    var product_id = exp[0];
                    var type_t = exp[1];
                    var qty_q = exp[2];
                    var variant_v = exp[3];
                    
                    product.push(product_id);
                    type.push(type_t);
                    qty.push(qty_q);
                    variant.push(variant_v);
                });
                if(product.length > 0){
                $.ajax({
                type: 'ajax',
                dataType: 'json',
                data: {
                    product_id: product,
                    type: type,
                    quantity: qty,
                    variant: variant,
                    warehouse: warehouse,
                    token: '<?= $this->security->get_csrf_hash() ?>',
                },
                url: 'CheckStock/add_adjustment',
                method: 'POST',
                success: function (result) {
                    console.log(result.messages);
                    if (result.status) {
                        bootbox.alert(result.messages, function(){ 
                           reset();
                        });
                        getReports();
                       
                    } else {
                        bootbox.alert(result.messages);
                    }

                }, error: function () {
                    console.log('error');
                }
            });
            }else{
                     bootbox.alert('Please select products');
                }    
          }
        }
        
        function multiplecheck(){
            var checkBox = document.getElementById("selectall");
            if (checkBox.checked == true){
                $(".check_add_adjustment").attr("checked", "true");
            }else{
                $(".check_add_adjustment").removeAttr('checked');
            }    
        }

        function removerow(passid){
            bootbox.confirm({
                message: "Are you sure you want to delete?",
                buttons: {
                    confirm: {
                        label: 'Yes',
                        className: 'btn-success'
                    },
                    cancel: {
                        label: 'No',
                        className: 'btn-danger'
                    }
                },
                callback: function (result) {
                    if(result==true){
                        $('#id_'+passid).remove();
                    }
                }
            }); 

        }
        
</script>   
  
