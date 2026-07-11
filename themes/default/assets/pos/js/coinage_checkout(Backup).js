$(document).ready(function () {
  $("#paymentModal").on("shown.bs.modal", function () {
    fetchDenomination();
  });
  // fetch denomination 
  function fetchDenomination() {
    $.ajax({
      type: "GET",
      url: site.base_url + "pos/get_denominations",
      dataType: "json",
      success: function (data) {
        Alldenominations = data;
        // Store a pristine copy for availability checks in Return mode
        localStorage.setItem('AvailableDenominations', JSON.stringify(Alldenominations));
        localStorage.setItem('Alldenominations', JSON.stringify(Alldenominations));  // working copy for Collected mode UI state

        renderDenominations(Alldenominations);
        loadData();
        initialTotalPayable();

      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
      },
    });
  }

  // Right side total payable section
  function initialTotalPayable() {

    var invoiceAmount = ($("#amount_1").val()) || 0;
    var Amount = "0";
    $("#totalPayableAmount").text(formatNumber(invoiceAmount));
    $("#returnAmount").text(formatNumber(Amount));
    $("#depositedAmount").text(formatNumber(Amount));
    $(".payment-box").hide();
    $("#yourDivId").hide();
  }

  // load selected items on left side
  function loadData() {

    var scanValue = $('#scan_item_qr').val();

    var per_cartunitview = ($('#per_cartunitview').val() == 1) ? true : false;
    var per_cartpriceedit = ($('#per_cartpriceedit').val() == 1) ? true : false;
    var permission_owner = ($('#permission_owner').val() == 1) ? true : false;
    var permission_admin = ($('#permission_admin').val() == 1) ? true : false;
    var add_tax_in_cart_unit_price = ($('#add_tax_in_cart_unit_price').val() == 1) ? true : false;
    var add_discount_in_cart_unit_price = ($('#add_discount_in_cart_unit_price').val() == 1) ? true : false;
    var changeQtyAsPerPrice = ($('#change_qty_as_per_user_price').val() == 1) ? true : false;

    if (localStorage.getItem('positems')) {
      total = 0;
      invoice_total_withtax = 0;      //For Apply Offers
      invoice_total_withouttax = 0;   //For Apply Offers 
      offerCartItems = {};        //For Apply Offers 
      count = 1;
      an = 1;
      product_tax = 0;
      invoice_tax = 0;
      product_discount = 0;
      order_discount = 0;
      total_discount = 0;
      poscartitems = null;
      item_cart_qty = [];


      $("#getsdta tbody").empty();

      if (java_applet == 1) {
        order_data = "";
        bill_data = "";
        bill_data += chr(27) + chr(69) + "\r" + chr(27) + "\x61" + "\x31\r";
        bill_data += site.settings.site_name + "\n\n";
        order_data = bill_data;
        bill_data += lang.bill + "\n";
        order_data += lang.order + "\n";
        bill_data += $('#select2-chosen-1').text() + "\n\n";
        bill_data += " \x1B\x45\x0A\r\n ";
        order_data += $('#select2-chosen-1').text() + "\n\n";
        order_data += " \x1B\x45\x0A\r\n ";
        bill_data += "\x1B\x61\x30";
        order_data += "\x1B\x61\x30";
      } else {
        $("#order_span").empty();
        $("#bill_span").empty();
        var styles = '<style>table, th, td { border-collapse:collapse; border-bottom: 1px solid #CCC; } .no-border { border: 0; } .bold { font-weight: bold; }</style>';
        // var pos_head1 = '<span style="text-align:center;"><h3>' + site.settings.site_name + '</h3><h4>';
        //var pos_head2 = '</h4><h5> Token No.: ' + tokan_no + ' </h5><h5>' + $('#select2-chosen-1').text() + '<br>' + hrld() + '</h5></span>';
        //$("#order_span").prepend(styles + pos_head1 + ' Order ' + pos_head2);

        var pos_head1 = '<div style="text-align:center;"><strong>' + site.settings.site_name + '</strong><br/>';
        if (site.settings.pos_type == 'restaurant') {
          var pos_head2 = ' Table No: ' + localStorage.getItem('table_name') + '</div>';
          $("#bill_span").prepend(styles + pos_head1 + pos_head2);

        } else {
          var pos_head2 = ' Token No.: ' + tokan_no + ' ' + ',' + hrld() + '</div>';
          $("#bill_span").prepend(styles + pos_head1 + ' Bill ' + pos_head2);

        }
        $("#order_span").prepend(styles + pos_head1 + pos_head2);

        // $("#bill_span").prepend(styles + pos_head1 + ' Bill ' + pos_head2);
        $("#order-table").empty();
        $("#bill-table").empty();
      }

      positems = JSON.parse(localStorage.getItem('positems'));

      console.log('=========positems=============');
      console.log(positems);

      var posItemsCount = Object.keys(positems).length;

      var poscartitems = {};
      /*********************Code For Offers Add Free Items*******************/
      //         console.log('Status addfreeitems: '+localStorage.getItem('addfreeitems'));


      if (localStorage.getItem('addfreeitems') == 'false') {
        var temp_item_id = '';
        //When do not have to add free items in cart but in localstorage have free items then remove from localstorage and cart

        $.each(positems, function () {

          if (this.note == 'Free Items' || this.is_free) {

            var objitemid = '';
            var objitemid2 = '';

            if (this.row.option) {
              objitemid = this.item_id + this.row.option;
              objitemid2 = this.item_id + '_' + this.row.option;
            } else if (this.category) {
              objitemid = this.item_id + this.category;
              objitemid2 = this.item_id + '_' + this.category;
            } else {
              objitemid = this.item_id;
              objitemid2 = this.item_id;
            }

            delete positems['free_item_' + objitemid2];
            localStorage.removeItem('free_item_' + objitemid2);

            delete positems[objitemid];
            localStorage.removeItem(objitemid);
          } else {

            temp_item_id = this.id;  //(this.row.option) ?  this.item_id + this.row.option :  this.item_id; // Add new Item to card Not Working
            poscartitems[temp_item_id] = this;
          }
        });
      } else {
        poscartitems = positems;

        if (localStorage.getItem('posfreeitems')) {
          var freepositems = JSON.parse(localStorage.getItem('posfreeitems'));
          jQuery.extend(poscartitems, freepositems); // Extend cart veriables with free items.
          localStorage.removeItem('posfreeitems');
        }
      }

      /**********************************************************************/

      if (pos_settings.item_order == 1) {
        sortedItems = _.sortBy(poscartitems, function (o) {
          return [parseInt(o.category), parseInt(o.order)];
        });
      } else if (site.settings.item_addition == 1) {
        sortedItems = _.sortBy(poscartitems, function (o) {
          return [parseInt(o.order)];
        })
      } else {
        sortedItems = poscartitems;
      }

      //        console.log('--------------sortedItems---------------------');
      //        console.log(sortedItems);

      //Get the total cart unit items
      var cart_item_unit_count = 0;

      $.each(sortedItems, function () {
        cart_item_unit_count += parseFloat(this.row.qty);
      });

      var category = 0, print_cate = false;
      // var itn = parseInt(Object.keys(sortedItems).length);
      $("#bill-table").append('<tr><th>  Item Code  </th><th>Item Name</th><th>Qty</th><th>Price</th><th style="text-align:right;">Total</th></tr>');
      var previous_row_no = '';

      $('#payment').attr('disabled', false);

      //        console.log('--------------sortedItems---------------------');
      //        console.log(sortedItems);

      $.each(sortedItems, function () {

        var item = this;
        var item_id = site.settings.item_addition == 1 ? item.item_id : item.id;
        division_array.push(item.row.divisionid);
        var hsn_code = '';
        if (item.row.hsn_code) {
          hsn_code = item.row.hsn_code;
        }
        // positems[item_id] = item;

        item.order = item.order ? item.order : new Date().getTime();
        var product_id = item.row.id, item_type = item.row.type, combo_items = item.combo_items, item_price = item.row.price, item_qty = item.row.qty, item_aqty = item.row.quantity, item_tax_method = item.row.tax_method, item_ds = item.row.discount, item_discount = 0, item_desc = item.row.description, item_option = item.row.option, item_code = item.row.code, item_article_code = item.row.article_code, item_serial = item.row.serial, item_name = item.row.name.replace(/"/g, "&#034;").replace(/'/g, "&#039;");
        var product_unit = item.row.unit;
        var item_weight = 0;
        if (item.row.storage_type == 'loose') {
          var base_quantity = formatDecimal((parseFloat(item.row.base_quantity) * parseFloat(item.row.qty)), 3);
        } else {
          var base_quantity = formatDecimal((parseFloat(item.row.qty)), 3);
        }
        var tax_rate = item.row.tax_rate;
        var mrp = item.row.mrp;
        var discount_on_mrp = item.row.discount_on_mrp;
        var pr_var_discount = item.row.pr_var_discount;
        var customer_group_discount = item.row.customer_group_discount; // flag for customer discount apply

        // Category Tax
        var category_tax = item.category_tax;
        var fixtax = item.fixtax;

        var warehouse_price_group_id = item.row.warehouse_price_group_id;
        if (!warehouse_price_group_id) {
          var unit_price = parseFloat(item.row.real_unit_price) > 0 ? item.row.real_unit_price : item.row.unit_price;
          if (scanValue) {
            var unit_price = item.row.mrp;
          }
        } else {
          var unit_price = item.row.unit_price;
          // var unit_price = item.row.mrp;
          // var unit_price = item_option == "0" ? item.row.mrp : item.row.unit_price;
        }
        // var customerName = $('#customer_name').val();
        // alert('customerName')
        // alert(customerName)
        // let inputString = "Swarup(8633683837)";
        // let parts = $('#customer_name').val().split('(');
        // let name = parts[0];

        //var base_quantity = (parseFloat(item.row.unit_quantity) * parseFloat(item.row.qty));
        // var unit_price = item.row.real_unit_price;
        var manualedit = (item.row.manualedit) ? item.row.manualedit : ''; // 05-09-19

        item_cart_qty[item.item_id] = parseFloat(item_cart_qty[item.item_id]) > 0 ? (item_cart_qty[item.item_id] + item.row.qty) : item.row.qty;

        var cf1 = item.row.cf1;
        var cf2 = item.row.cf2;
        var cf3 = item.row.cf3;
        var cf4 = item.row.cf4;
        var cf5 = item.row.cf5;
        var cf6 = item.row.cf6;

        var batchno = item.row.batch_number ? item.row.batch_number : '';

        if (item.row.fup != 1 && product_unit != item.row.base_unit) {
          $.each(item.units, function () {
            if (this.id == product_unit) {
              base_quantity = formatDecimal(unitToBaseQty(item.row.qty, this), 6);
              unit_price = formatDecimal((parseFloat(item.row.base_unit_price) * (unitToBaseQty(1, this))), 6);
            }
          });
        }
        var sel_opt = '';
        var option_input_hidden = '<input name="product_option[]" type="hidden" class="roption" value="' + item.row.option + '">';

        if (site.settings.attributes == 1) {
          if (item.options !== false) {
            $.each(item.options, function () {

              var this_options = this;

              //If Select multiple options
              if (jQuery.type(item.row.option) == 'string') {
                var optionArr = item.row.option.split(",");
                $.each(optionArr, function (k, opt) {

                  if (this_options.id == opt) {
                    if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                      if (manualedit == '') {
                        // item_price = formatDecimal(parseFloat(item.row.price) + parseFloat(this_options.price), 6);
                        item_price = formatDecimal(parseFloat(item.row.price) + parseFloat(this_options.mrp), 6);
                        unit_price = item_price;
                        item_aqty = this_options.quantity;
                      }
                    }
                    if (k) {
                      sel_opt = sel_opt + ',' + this_options.name;
                    } else {
                      sel_opt = this_options.name;
                    }
                  }
                });
              } else {
                if (this_options.id == item.row.option) {
                  if (this_options.price != 0 && this_options.price != '' && this_options.price != null) {
                    if (manualedit == '') {
                      // item_price = formatDecimal(parseFloat(item.row.price) + (parseFloat(this_options.price)), 6);
                      item_price = formatDecimal(parseFloat(item.row.price) + (parseFloat(this_options.mrp)), 6);
                      unit_price = item_price;
                      item_aqty = this_options.quantity;
                    }
                  }
                  sel_opt = this_options.name;
                }
              }
            });
          }
        }


        // Order level discount distributed in each items as item discount.
        var posdiscount = localStorage.getItem('posdiscount');

        if (posdiscount) {
          //Order Level Discount Calculations               
          var ods = posdiscount;
          var item_discount_on_mrp = 0;
          var item_order_discount = 0;
          // var mrp = unit_price;

          // calculating unit_price after apply discount on mrp 
          // start
          var ds = discount_on_mrp ? String(discount_on_mrp) : '0';
          if (ds.indexOf("%") !== -1) {
            var pds = ds.split("%");
            if (!isNaN(pds[0])) {
              item_discount_on_mrp = formatDecimal((parseFloat(((mrp) * parseFloat(pds[0])) / 100)), 6);
            } else {
              item_discount_on_mrp = formatDecimal(ds, 6);
            }
          } else {
            item_discount_on_mrp = formatDecimal(ds, 6);
          }
          unit_price = formatDecimal(mrp - item_discount_on_mrp, 6);
          // end


          if (ods.indexOf("%") !== -1) {
            var pds = ods.split("%");
            if (!isNaN(pds[0])) {
              item_order_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
              item_ds = ods;
            } else {
              item_order_discount = formatDecimal(parseFloat(ods), 6);
              item_ds = item_order_discount;
            }
          } else {
            //If Discount in amount then divided equal in each items unit equally.
            item_order_discount = formatDecimal((parseFloat(ods) / cart_item_unit_count), 6);
            item_ds = item_order_discount;
          }
          order_discount += formatDecimal((item_order_discount * item_qty), 6);
          // unit_price = formatDecimal(parseFloat(mrp) - parseFloat(item_discount_on_mrp), 6);
          unit_price = mrp;
          item_discount = item_discount_on_mrp;

          if (offer_categories = localStorage.getItem('offer_on_category')) {
            var offer_on_category = offer_categories.split(',');
            if (offer_on_category.indexOf(item.category) != -1) {
              //alert('found');
            } else {
              //alert('not found');
              if (offer_on_category.indexOf(item.sub_category) != -1) {  //alert('sub found');	
              } else {
                item_discount = 0;
                item_ds = 0;
                //alert('not sub found');
              }
            }
          }
          //Set Order Discount Value null.
          //$('#posdiscount').val('');
          $('#offer_on_category').val(localStorage.getItem('offer_on_category'));
          $('#offer_category').val(localStorage.getItem('offer_category'));
          $('#offer_description').val(localStorage.getItem('offer_description'));

          // alert('offer_category: '+localStorage.getItem('offer_category'));
          // alert('offer_description: '+localStorage.getItem('offer_description'));
          localStorage.setItem('applyOffers', true);
        } else {
          //Item Level Discount Calculations  
          // var ds = item_ds ? String(item_ds) : '0';

          if (manualedit == "1") {
            var flat_discount = mrp - unit_price;  // Calculate flat discount
            item_discount = flat_discount.toFixed(0);
            discount_on_mrp = item_discount;
          } else {
            if (customer_group_discount == '1') { // override customer discount to discount on mrp if customer discount applied
              discount_on_mrp = item_ds;
            }
            var ds = discount_on_mrp ? String(discount_on_mrp) : '0';
            if (ds.indexOf("%") !== -1) {
              var pds = ds.split("%");
              if (!isNaN(pds[0])) {
                item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
              } else {
                item_discount = formatDecimal(ds, 6);
              }
            } else {
              item_discount = formatDecimal(ds, 6);
            }
          }

        }

        if (item.row.editpopup == 'edititems') {

          unit_price = manualedit == "1" ? item.row.unit_price : mrp;

          if (manualedit == "1") {
            var flat_discount = mrp - unit_price;  // Calculate flat discount
            item_discount = flat_discount.toFixed(0);
          } else {
            if (customer_group_discount == '1') { // override customer discount to discount on mrp if customer discount applied
              discount_on_mrp = item.row.discount_on_mrp;
            }
            // Discount on mrp
            if (discount_on_mrp) {
              // item_ds = discount_on_mrp;
              //Item Level Discount Calculations  
              var ds = discount_on_mrp ? String(discount_on_mrp) : '0';

              if (ds.indexOf("%") !== -1) {
                var pds = ds.split("%");
                if (!isNaN(pds[0])) {
                  item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 6);
                } else {
                  item_discount = formatDecimal(ds, 6);
                }
              } else {
                item_discount = formatDecimal(ds, 6);
              }
            }
          }

        }

        if (posdiscount) {
          product_discount += formatDecimal((item_discount_on_mrp * item_qty), 6);
        } else {
          product_discount += formatDecimal((item_discount * item_qty), 6);
        }

        // item.row.discount = formatDecimal(item_discount, 4);
        if (changeQtyAsPerPrice) {
          var cart_user_price = parseFloat(item.row.user_price) > 0 ? parseFloat(item.row.user_price) : 0;
        }

        // unit_price = formatDecimal(unit_price - item_discount, 6);
        if (manualedit == '') {
          unit_price = formatDecimal(unit_price - item_discount, 6);
        }
        if (posdiscount) { // for calculate unit_price after apply order level discount
          unit_price = formatDecimal(unit_price - item_order_discount, 6);
        }

        // var pr_tax = item.tax_rate;
        // var pr_tax_val = 0;
        // if (site.settings.tax1 == 1) {
        //     if (pr_tax !== false) {
        //         if (pr_tax.type == 1) {
        //             if (item_tax_method == '0') {
        //                 pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 6);
        //                 pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
        //             } else {
        //                 pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / 100, 6);
        //                 pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
        //             }
        //         } else if (pr_tax.type == 2) {
        //             pr_tax_val = formatDecimal(pr_tax.rate);
        //             pr_tax_rate = pr_tax.rate;
        //         }
        //         product_tax += pr_tax_val * item_qty;
        //     }
        // }//end if.
        // if(item.row.editpopup == 'edititems') {
        //     unit_price = mrp;
        //     // var unit_price = $('#selling').val();
        // }
        var pr_tax = item.tax_rate;
        var pr_tax_val = 0, pr_tax_rate = 0;
        if (site.settings.tax1 == 1) {
          if (pr_tax !== false) {
            if (pr_tax.type == 1) {
              if (item_tax_method == '0') {
                if (fixtax) {
                  var exptax = fixtax.split("~");
                  pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1]))), 4);
                  pr_tax_rate = formatDecimal(exptax[1]) + '%';
                  tax_rate = exptax[0];
                } else {
                  if (category_tax) {
                    $.each(category_tax, function (k, categorytax) {
                      var uptocheck = categorytax.upto;
                      if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                        if (uptocheck) {
                          if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          } else {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          }
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                          pr_tax_rate = formatDecimal(exptax[1]) + '%';
                          tax_rate = exptax[0];
                        }
                      } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                        if (uptocheck) {
                          if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          } else {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          }
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / (100 + parseFloat(exptax[1])), 6);
                          pr_tax_rate = formatDecimal(exptax[1]) + '%';
                          tax_rate = exptax[0];
                        }

                      }
                    });
                  }

                }
              } else {
                if (fixtax) {
                  var exptax = fixtax.split("~");
                  pr_tax_val = formatDecimal((((unit_price) * parseFloat(exptax[1])) / 100), 4);
                  pr_tax_rate = formatDecimal(exptax[1]) + '%';
                  tax_rate = exptax[0];
                } else {
                  if (category_tax) {
                    $.each(category_tax, function (k, categorytax) {
                      var uptocheck = categorytax.upto;
                      if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                        if (uptocheck) {
                          if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          } else {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          }
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                          pr_tax_rate = formatDecimal(exptax[1]) + '%';
                          tax_rate = exptax[0];
                        }

                      } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {
                        if (uptocheck) {
                          if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          } else {
                            var taxvalue = categorytax.taxratevalue;
                            var exptax = taxvalue.split("~");
                            pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                            pr_tax_rate = formatDecimal(exptax[1]) + '%';
                            tax_rate = exptax[0];
                          }
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(((unit_price) * parseFloat(exptax[1])) / 100, 6);
                          pr_tax_rate = formatDecimal(exptax[1]) + '%';
                          tax_rate = exptax[0];
                        }

                      }
                    });
                  }

                }
              }

            } else if (pr_tax.type == 2) {
              if (fixtax) {
                var exptax = fixtax.split("~");
                pr_tax_val = parseFloat(exptax[1]);
                pr_tax_rate = exptax[1];
                tax_rate = exptax[0];
              } else {
                if (category_tax) {
                  $.each(category_tax, function (k, categorytax) {
                    var uptocheck = categorytax.upto;
                    if (categorytax.condition == "less_than" && unit_price <= categorytax.price) {
                      if (uptocheck) {
                        if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(exptax[1]);
                          pr_tax_rate = formatDecimal(exptax[1]);
                          tax_rate = exptax[0];
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(exptax[1]);
                          pr_tax_rate = formatDecimal(exptax[1]);
                          tax_rate = exptax[0];
                        }
                      } else {
                        var taxvalue = categorytax.taxratevalue;
                        var exptax = taxvalue.split("~");
                        pr_tax_val = formatDecimal(exptax[1]);
                        pr_tax_rate = formatDecimal(exptax[1]);
                        tax_rate = exptax[0];
                      }

                    } else if (categorytax.condition == "greater_than" && unit_price >= categorytax.price) {

                      if (uptocheck) {
                        if (categorytax.price >= unit_price && unit_price <= uptocheck) {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(exptax[1]);
                          pr_tax_rate = formatDecimal(exptax[1]);
                          tax_rate = exptax[0];
                        } else {
                          var taxvalue = categorytax.taxratevalue;
                          var exptax = taxvalue.split("~");
                          pr_tax_val = formatDecimal(exptax[1]);
                          pr_tax_rate = formatDecimal(exptax[1]);
                          tax_rate = exptax[0];
                        }
                      } else {
                        var taxvalue = categorytax.taxratevalue;
                        var exptax = taxvalue.split("~");
                        pr_tax_val = formatDecimal(exptax[1]);
                        pr_tax_rate = formatDecimal(exptax[1]);
                        tax_rate = exptax[0];
                      }

                    }
                  });
                }

              }
            }
            product_tax += pr_tax_val * item_qty;
          }
        }
        // if(item.row.editpopup == 'edititems') {
        //     unit_price = item_tax_method == 0 ? formatDecimal((parseFloat(unit_price)), 4) : formatDecimal((parseFloat(unit_price)), 4);
        // }
        if (posdiscount) { // for adding order level discount in price again
          unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_order_discount), 6);
        }
        item_price = item_tax_method == 0 ? formatDecimal((unit_price - pr_tax_val), 6) : formatDecimal(unit_price, 6);
        // unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_discount), 6);
        if (manualedit == '') {
          unit_price = formatDecimal((unit_price), 6) + formatDecimal((item_discount), 6);
        }
        /********************************************/
        if (item_tax_method == 0) {
          offerCartItems[item.row.id] = JSON.parse('{"item_id":"' + item.row.id + '", "price_with_tax":"' + unit_price + '", "price_without_tax":"' + (parseFloat(unit_price) - parseFloat(pr_tax_val)) + '", "qty":"' + item_qty + '", "category":"' + item.row.category_id + '", "discount":"' + item.row.discount + '"}');
        } else {
          offerCartItems[item.row.id] = JSON.parse('{"item_id":"' + item.row.id + '", "price_with_tax":"' + (parseFloat(unit_price) + parseFloat(pr_tax_val)) + '", "price_without_tax":"' + unit_price + '", "qty":"' + item_qty + '", "category":"' + item.row.category_id + '", "discount":"' + item.row.discount + '"}');
        }
        /************************************************/

        if (pos_settings.item_order == 1 && category != item.row.category_id) {
          category = item.row.category_id;
          print_cate = true;
          var newTh = $('<tr id="category_' + category + '"></tr>');
          newTh.html('<td colspan="100%"><strong>' + item.row.category_name + '</strong></td>');
          newTh.prependTo("#getsdta");
        } else {
          print_cate = false;
        }

        var row_no = (new Date).getTime();
        var newTr = $('<tr id="row_' + row_no + '" class="row_' + item_id + '" data-item-id="' + item_id + '"></tr>');

        item_weight = (item.row.unit_weight) ? (parseFloat(item_qty) * parseFloat(item.row.unit_weight)) : '';

        var tr_html = '<td><input name="row[]" type="hidden" id="item_' + item_id + '" class="roid" value="' + row_no + '">';
        tr_html += '<input name="product_id[]" type="hidden" class="rid" value="' + product_id + '">';
        tr_html += '<input name="hsn_code[]" type="hidden" class="rid hsn_code" value="' + hsn_code + '">';
        tr_html += '<input name="product_type[]" type="hidden" class="rtype product_type"  value="' + item_type + '">';
        tr_html += '<input name="product_code[]" type="hidden" class="rcode product_code" value="' + item_code + '">';
        tr_html += '<input name="article_code[]" type="hidden" class="rcode article_code" value="' + item_article_code + '">';
        tr_html += '<input name="product_name[]" type="hidden" class="rname product_name" value="' + item_name + '">';
        tr_html += '<input name="productids[]" type="hidden" class="productids" value="' + item.row.id + '">';
        tr_html += '<input name="manualedit[]"   type="hidden" class="rmanualedit" value="' + manualedit + '">';
        tr_html += '<input name="item_weight[]"  type="hidden" class="rweight" value="' + item_weight + '">';
        tr_html += '<input name="return_ref_no[]" type="hidden" class="return_ref_no" value="' + item.row.return_ref_no + '">';
        tr_html += '<input  name="customerRefNo" type="hidden" class="customerRefNo" value=" ' + scanValue + '">';

        // tr_html += '<input  name="customerRefNo" type="hidden" class="customerRefNo" value=" ' + scanValue +'">';

        //Options Input Hiddens 
        tr_html += option_input_hidden;

        tr_html += '<span class="sname" id="name_' + row_no + '">' + item_code + ' - ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ((item.note == '') ? item.note : ': ' + item.note) + ')' : '') + '</span>';

        //Hide Item Edit Options if Items is free
        if ((item.note == 'Free Items')) {
          var item_disabled = ' readonly="readonly" ';
          tr_html += '</td>';
        } else {
          var item_disabled = '';
          // tr_html += '<i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
        }

        //tr_html += '<i class="pull-right fa fa-edit tip pointer edit" id="' + row_no + '" data-item="' + item_id + '" title="Edit" style="cursor:pointer;"></i></td>';
        item.note = (item.note == undefined) ? '' : item.note;
        tr_html += '<input name="item_note[]" type="hidden" class="rid" value="' + item.note + '">';
        tr_html += '<input name="cf1[]" type="hidden" class="rid" value="' + cf1 + '">';
        tr_html += '<input name="cf2[]" type="hidden" class="rid" value="' + cf2 + '">';
        tr_html += '<input name="cf3[]" type="hidden" class="rid" value="' + cf3 + '">';
        tr_html += '<input name="cf4[]" type="hidden" class="rid" value="' + cf4 + '">';
        tr_html += '<input name="cf5[]" type="hidden" class="rid" value="' + cf5 + '">';
        tr_html += '<input name="cf6[]" type="hidden" class="rid" value="' + cf6 + '">';
        tr_html += '<input name="batch_number[]" type="hidden" class="rid" value="' + batchno + '">';

        tr_html += '<td class="text-right">';

        if (site.settings.product_serial == 1) {
          tr_html += '<input class="form-control input-sm rserial" name="serial[]" type="hidden" id="serial_' + row_no + '" value="' + item_serial + '">';
        }
        if (site.settings.product_discount == 1) {
          tr_html += '<input class="form-control input-sm rdiscount product_discount" name="product_discount[]" type="hidden" id="discount_' + row_no + '" value="' + item_ds + '">';
        }

        if (site.settings.tax1 == 1) {
          pr_tax.id = (tax_rate > 0) ? tax_rate : pr_tax.id;
          tr_html += '<input class="form-control input-sm text-right rproduct_tax product_tax" name="product_tax[]" type="hidden" id="product_tax_' + row_no + '" value="' + pr_tax.id + '"><input type="hidden" class="sproduct_tax" id="sproduct_tax_' + row_no + '" value="' + formatMoney(pr_tax_val * item_qty) + '">';
        }
        item_desc = item_desc == undefined ? '' : item_desc;
        tr_html += '<input class="rdescription" name="item_description[]" type="hidden" id="description_' + row_no + '" value="' + item_desc + '">';
        tr_html += '<input class="rprice" name="net_price[]" type="hidden" id="price_' + row_no + '" value="' + item_price + '">';
        tr_html += '<input class="ruprice unitprices" name="unit_price[]" type="hidden" value="' + unit_price + '">';
        tr_html += '<input class="realuprice" name="real_unit_price[]" type="hidden" value="' + item.row.real_unit_price + '">';
        tr_html += '<input class="rmrp mrp" name="mrp[]" type="hidden" value="' + mrp + '">';
        tr_html += '<input class="rmrpdiscount mrpdiscount" name="discount_on_mrp[]" type="hidden" value="' + discount_on_mrp + '">';
        tr_html += '<input class="rtaxrate rtaxrate" name="taxrate[]" type="hidden" value="' + tax_rate + '">';
        tr_html += '<input class="reditpopup reditpopup" name="editpopup[]" type="hidden" value="' + item.row.editpopup + '">';


        // var cart_item_price =  (add_tax_in_cart_unit_price == true) ? (parseFloat(item_price) + parseFloat(pr_tax_val)) : parseFloat(item_price);
        //alert(cart_item_price);

        var cart_item_price = 0;

        if (add_tax_in_cart_unit_price == true && add_discount_in_cart_unit_price == true) {

          cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val) + parseFloat(item_discount); //item_ds
        } else if (add_tax_in_cart_unit_price == true) {
          cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val);
        } else if (add_discount_in_cart_unit_price == true) {
          cart_item_price = parseFloat(item_price) + parseFloat(item_discount);
        } else {
          cart_item_price = parseFloat(item_price) + parseFloat(pr_tax_val);
        }

        if (permission_admin || permission_owner || per_cartpriceedit) {
          if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose') {
            tr_html += '<input type="text" maxlength="10" name="item_user_price[]" id="suserprice_' + row_no + '" value="' + ((cart_user_price > 0) ? parseInt(cart_user_price) : parseInt(cart_item_price)) + '"  class="form-control input-sm kb-pad text-center userprice" />';
            tr_html += (cart_user_price > 0) ? '<small class="text-left">' + parseInt(cart_item_price) + '/qty</small>' : '';
            tr_html += '<input type="hidden" name="item_price[]" id="sprice_' + row_no + '" value="' + (formatMoney(cart_item_price)) + '" />';
          } else {
            tr_html += '<span class="item_price userprice" data-id="' + row_no + '" id="sprice_' + row_no + '" ' + item_disabled + '>' + formatMoney(cart_item_price) + '</span>';
            // tr_html += '<input type="text" maxlength="10" name="item_price[]" id="sprice_' + row_no + '" value="' + (formatMoney(cart_item_price)) + '"  ' + item_disabled + '  class="form-control input-sm kb-pad text-center item_price userprice" />';
          }
        } else {
          tr_html += formatMoney(parseFloat(cart_item_price)) + '<input type="hidden"  maxlength="10" name="item_price[]" id="sprice_' + row_no + '" value="' + formatMoney(cart_item_price) + '" onchange="return false" class="form-control input-sm kb-pad text-center  item_price userprice" />';
        }
        tr_html += '</td>';

        // tr_html += '<td>';
        // tr_html += '<table style="border: none;"><tr ><td style="border-bottom: 0px !important;"> ';
        // if (oldProductSearch(item_id)) {
        //     // tr_html += '<button onclick="qtyMinus(\'' + item_id + '\')" type="button" style="border: 0; background: none;" ><i class="fa fa-minus"></i> </button>';
        // }
        // tr_html += ' &nbsp;  </td>';
        tr_html += '<td style="border-bottom: 0px !important; text-align: center;">';


        tr_html += '<input name="product_unit[]" type="hidden" class="runit" value="' + product_unit + '">';
        tr_html += '<input name="product_base_quantity[]" maxlength="6" type="hidden" class="rbase_quantity product_base_quantity" value="' + base_quantity + '">';


        if (permission_admin || permission_owner || per_cartpriceedit) {

          var qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(item_aqty, 0) : 1000;

          if (item.row.type == 'combo') {
            var cmax = 1000, cimax = '';
            $.each(combo_items, function () {
              cimax = (parseFloat(this.quantity) / parseFloat(this.qty));
              cmax = (cimax > cmax) ? cmax : cimax;
            });
            qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(cmax, 0) : 1000;
          }//end if.

          if (item.row.type == 'Bundle') {
            var cmax = 1000, cimax = '';
            $.each(combo_items, function () {
              cimax = (parseFloat(this.quantity) / parseFloat(this.qty));
              cmax = (cimax > cmax) ? cmax : cimax;
            });
            qmax = (parseInt(site.settings.overselling) == 0) ? formatDecimal(cmax, 0) : 1000;
          }//end if.

          if (item.row.storage_type == 'packed') {
            var qotp = '', selected = '';
            for (var q = 1; q <= (qmax ? qmax : 1); q++) {
              selected = '';
              if (formatDecimal(item_qty, 0) == q) {
                selected = ' selected="selected" ';
              }
              qotp += '<option ' + selected + '>' + q + '</option>';
            }//end for
            // tr_html += '<select class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" >' + qotp + '</select>';
            tr_html += '<span class="returnquantity rquantity" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '">' + item_qty + '</span>', style = "width: 43px";
          } else {
            if (changeQtyAsPerPrice == true && cart_user_price > 0) {
              tr_html += formatDecimal(item_qty, 3) + '<input style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + ' type="hidden" value="' + formatDecimal(item_qty, 3) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
            } else {
              tr_html += '<input style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + ' type="text"    value="' + formatDecimal(item_qty, 3) + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
            }
          }

        } else {
          tr_html += '<input readonly="readonly" style="width: 43px; float: right;" class="form-control input-sm kb-pad text-center rquantity" maxlength="6" tabindex="' + ((site.settings.set_focus == 1) ? an : (an + 1)) + '" name="quantity[]" ' + item_disabled + '  type="text" value="' + item_qty + '" data-id="' + row_no + '" data-item="' + item_id + '" id="quantity_' + row_no + '" onClick="this.select();">';
        }
        // tr_html += ' </td><td style="border-bottom: 0px !important;">';
        // // tr_html += '&nbsp;  <button type="button" onclick="qtyPlus(\'' + item_id + '\')"  style="border: 0; background: none; bottom:7px!important; padding:4px;"> <i class="fa fa-plus"></i> </button> ';
        // tr_html += '</td></table>';
        // tr_html += '</td>';

        var item_sale_unit = (item_name == 'Gift Card') ? 'pcs' : '';
        if (item.row.sale_unit) {
          $.each(item.units, function () {
            if (this.id == item.row.sale_unit) {
              item_sale_unit = this.code;
            }
          });
        }
        //Show/Hide Cart Unit
        // if(permission_admin || permission_owner || per_cartunitview){
        tr_html += '<td class="text-center"><small>' + item_sale_unit + '</small></td>';
        //}            
        //tr_html += '<td class="text-right"><span class="text-right ssubtotal" id="subtotal_' + row_no + '">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</span></td>';

        //Hide Item Edit Options if Items is free
        if ((item.note == 'Free Items')) {
          tr_html += '<td class="text-center" colspan="2" style="color:green;">Offer Free Item</td>';
        } else {
          if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose' && cart_user_price > 0) {
            tr_html += '<td class="text-right"><span class="text-right ssubtotal returntotal returnsubtotal" id="subtotal_' + row_no + '">' + formatMoney(cart_user_price) + '</span></td>';
            tr_html += '<input class="returntotal returnsubtotal"  type="hidden" value="' + formatMoney(cart_user_price) + '">';

          } else {
            tr_html += '<input class="returntotal returnsubtotal"  type="hidden" value="' + formatMoney(parseFloat(cart_item_price) * parseFloat(item_qty)) + '">';
            tr_html += '<td class="text-right" style="font-size: 13px;"><span class="text-right ssubtotal returntotal returnsubtotal" id="subtotal_' + row_no + '">' + formatMoney(parseFloat(cart_item_price) * parseFloat(item_qty)) + '</span></td>';
          }
          // tr_html += '<td class="text-center"><i class="fa fa-times tip pointer posdel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
        }

        newTr.html(tr_html);
        if (pos_settings.item_order == 1) {
          //newTr.prependTo("#getsdta");
          $('#getsdta').find('#category_' + category).after(newTr);
        } else if (pos_settings.item_order == 2) { // This is the new else if block for adding the "conduit"
          if (previous_row_no == '') {
            newTr.prependTo("#getsdta");
          } else {
            $('#getsdta').find('#row_' + previous_row_no).after(newTr);
          }
        } else {
          if (previous_row_no == '') {
            newTr.prependTo("#getsdta");
          } else {
            $('#getsdta').find('#row_' + previous_row_no).before(newTr);
          }
        }
        previous_row_no = row_no;

        invoice_total_withtax += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 6);
        invoice_total_withouttax += formatDecimal((parseFloat(item_price) * parseFloat(item_qty)), 6);

        if (changeQtyAsPerPrice == true && item.row.storage_type == 'loose' && cart_user_price > 0) {
          total += formatDecimal(cart_user_price, 6);
        } else {
          total += formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)), 6);
        }
        item_qtys = Math.abs(item_qty);
        count += parseFloat(item_qtys);
        an++;

        if (item_type == 'standard' && item.options !== false) {

          $.each(item.options, function () {
            if (this.id == item_option && (base_quantity > this.quantity || item_cart_qty[item.item_id] > this.quantity)) {
              $('#row_' + row_no).addClass('danger');
              if (site.settings.overselling != 1) {
                $('#payment').attr('disabled', true);
              }
            }
          });
        } else if (item_type == 'standard' && (base_quantity > item_aqty || item_cart_qty[item.item_id] > item_aqty)) {
          $('#row_' + row_no).addClass('danger');
          if (site.settings.overselling != 1) {
            $('#payment').attr('disabled', false);
          }
        } else if (item_type == 'combo') {
          if (combo_items === false) {
            $('#row_' + row_no).addClass('danger');
            if (site.settings.overselling != 1) {
              $('#payment').attr('disabled', true);
            }
          } else {
            $.each(combo_items, function () {
              if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                $('#row_' + row_no).addClass('danger');
                if (site.settings.overselling != 1) {
                  $('#payment').attr('disabled', true);
                }
              }
            });
          }
        } else if (item_type == 'Bundle') {
          if (combo_items === false) {
            $('#row_' + row_no).addClass('danger');
            if (site.settings.overselling != 1) {
              $('#payment').attr('disabled', true);
            }
          } else {
            $.each(combo_items, function () {
              if (parseFloat(this.quantity) < (parseFloat(this.qty) * base_quantity) && this.type == 'standard') {
                $('#row_' + row_no).addClass('danger');
                if (site.settings.overselling != 1) {
                  $('#payment').attr('disabled', true);
                }
              }
            });
          }
        }


        if (java_applet == 1) {
          bill_data += "#" + (an - 1) + " " + item_name + "\n";
          bill_data += printLine(item_qty + " x " + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + ": " + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)))) + "\n";
          order_data += printLine("#" + (an - 1) + " " + item_name + ":" + formatDecimal(item_qty)) + item.row.unit_lable + "\n";
        } else {
          if (pos_settings.item_order == 1 && print_cate) {
            var bprTh = $('<tr></tr>');
            bprTh.html('<td colspan="100%" class="no-border"><strong>' + item.row.category_name + '</strong></td>');
            var oprTh = $('<tr></tr>');
            oprTh.html('<td colspan="100%" class="no-border"><strong>' + item.row.category_name + '</strong></td>');
            $("#order-table").append(oprTh);
            //$("#bill-table").append(bprTh);
          }
          var bprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td> ' + item_code + ' </td><td class="no-border">  ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + (item.options ? '(' + item.row.option + ')' : '') + '</td><td>' + formatDecimal(item_qty) + ' ' + item.row.unit_lable + '</td> <td>' + (item_discount != 0 ? '<del>' + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val) + item_discount) + '</del>' : '') + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val)) + '</td><td style="text-align:right;">' + formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) + '</td></tr>';
          //var bprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td colspan="2" class="no-border">#'+(an-1)+' '+ item_name + ' (' + item_code + ')</td></tr>';
          //bprTr += '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td>(' + formatDecimal(item_qty) + ' x ' + (item_discount != 0 ? '<del>'+formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val) + item_discount)+'</del>' : '') + formatMoney(parseFloat(item_price) + parseFloat(pr_tax_val))+ ')</td><td style="text-align:right;">'+ formatMoney(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty))) +'</td></tr>';
          var oprTr = '<tr class="row_' + item_id + '" data-item-id="' + item_id + '"><td>#' + (an - 1) + ' ' + item_name + (sel_opt != '' ? ' (' + sel_opt + ')' : '') + (item.options ? '(' + item.row.option + ')' : '') + ')</td><td>' + formatDecimal(item_qty) + '</td></tr>';
          $("#order-table").append(oprTr);
          $("#bill-table").append(bprTr);
        }
      });

      // Order level discount calculations
      /* if (posdiscount = localStorage.getItem('posdiscount')) {
          var ds = posdiscount;
          if (ds.indexOf("%") !== -1) {
              var pds = ds.split("%");
              if (!isNaN(pds[0])) {
                  order_discount = formatDecimal((parseFloat(((total) * parseFloat(pds[0])) / 100)), 4);
              } else {
                  order_discount = parseFloat(ds);
              }
          } else {
              order_discount = parseFloat(ds);
          }
          total_discount += parseFloat(order_discount);
      }*/


      // Order level tax calculations
      if (site.settings.tax2 != 0) {
        if (postax2 = localStorage.getItem('postax2')) {
          $.each(tax_rates, function () {
            if (this.id == postax2) {
              if (this.type == 2) {
                invoice_tax = formatDecimal(this.rate);
              }
              if (this.type == 1) {
                invoice_tax = formatDecimal((((total - order_discount) * this.rate) / 100), 6);
              }
            }
          });
        }
      }

      total = formatDecimal(total, 2);
      product_tax = formatDecimal(product_tax, 2);
      total_discount = formatDecimal(order_discount + product_discount, 2);


      // Totals calculations after item addition
      gtotal = parseFloat(((total + invoice_tax) - order_discount) + shipping);
      $('#total1').text(formatMoney(total + product_discount));
      $('#titems1').text((an - 1) + ' (' + formatDecimal(parseFloat(count) - 1) + ')');
      $('#total_items').val((parseFloat(count) - 1));
      // $('#tds').text('(' + formatMoney(product_discount) + ') ' + formatMoney(order_discount));
      $('#tds1').text(formatMoney(product_discount + order_discount));
      if (site.settings.tax2 != 0) {
        $('#ttax21').text('(' + formatMoney(product_tax) + ') ' + formatMoney(invoice_tax))
      } else {
        $('#ttax21').text('(' + formatMoney(product_tax) + ') ')
      }
      $('#gtotal1').text(formatMoney(gtotal));
      if (java_applet == 1) {
        bill_data += "\n" + printLine(lang_total + ': ' + formatMoney(total)) + "\n";
        bill_data += printLine(lang_items + ': ' + (an - 1) + ' (' + (parseFloat(count) - 1) + ')') + "\n";
        if (total_discount > 0) {
          bill_data += printLine(lang_discount + ': (' + formatMoney(product_discount) + ') ' + formatMoney(order_discount)) + "\n";
        }
        if (site.settings.tax2 != 0 && invoice_tax != 0) {
          bill_data += printLine(lang_tax2 + ': ' + formatMoney(invoice_tax)) + "\n";
        }
        bill_data += printLine(lang_total_payable + ': ' + formatMoney(gtotal)) + "\n";
      } else {
        var bill_totals = '';
        bill_totals += '<tr class="bold"><td>' + lang_total + '</td><td></td><td style="text-align:right;">' + formatMoney(total) + '</td></tr>';
        bill_totals += '<tr class="bold"><td>' + lang_items + '</td><td></td><td style="text-align:right;">' + (an - 1) + ' (' + (parseFloat(count) - 1) + ')</td></tr>';
        if (order_discount > 0) {
          bill_totals += '<tr class="bold"><td>' + lang_discount + '</td><td></td><td style="text-align:right;">' + formatMoney(order_discount) + '</td></tr>';
        }
        if (site.settings.tax2 != 0 && invoice_tax != 0) {
          bill_totals += '<tr class="bold"><td>' + lang_tax2 + '</td><td></td><td style="text-align:right;">' + formatMoney(invoice_tax) + '</td></tr>';
        }
        bill_totals += '<tr class="bold"><td>' + lang_total_payable + '</td><td></td><td style="text-align:right;">' + formatMoney(gtotal) + '</td></tr>';

        if (site.settings.pos_type == 'restaurant') {
          bill_totals += '<tr><td>Waiter </td><td> ' + $('#sales_person').find('option:selected').text() + '</td><td></td>';
          bill_totals += '<tr><td>Date and Time</td><td> ' + hrld() + '</td><td></td>';
        }

        $('#bill-total-table').empty();
        $('#bill-total-table').append(bill_totals);
      }
      if (count > 1) {
        $('#poscustomer').select2("readonly", true);
        $('#poswarehouse').select2("readonly", true);
      } else {
        $('#poscustomer').select2("readonly", false);
        $('#poswarehouse').select2("readonly", false);
      }

      // Hide Keybord on mobile and Android device
      /* if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      $('input').attr("onfocus","blur()");
      KB = true;
      }
      if (KB) {
      display_keyboards();
      }
      if (site.settings.set_focus == 1) {
      $('#add_item').attr('tabindex', an);
      //  $('[tabindex='+(an-1)+']').focus().select();
      } else {
      $('#add_item').attr('tabindex', 1);
      // $('#add_item').focus();
      }*/
    }
    var customerName = document.getElementById('customer_name').value;
    // Loop through items
    var isExchange = localStorage.getItem("isExchange");
    if (isExchange === true) {
      exchangeOperation(isExchange);
    }
  }

  // shown denomination box with currency values
  function renderDenominations(Alldenominations) {

    const container = $("#denomination-container");
    container.empty();

    let currentType = "";
    let grid = $('<div class="grid-container"></div>');

    Alldenominations.forEach(function (item, index) {
      // If the denomination type changes, close current grid and start a new one
      if (item.type !== currentType) {
        if (grid.children().length > 0) {
          container.append(grid);
          grid = $('<div class="grid-container"></div>');
        }

        const title = item.type === "Bills" ? "Notes" : item.type;
        container.append(`<h3 class="denomination-title">${title}</h3>`);
        currentType = item.type;
      }

      const formattedCurrencyValue = formatMoneys(item.currency_value);
      const valFixed = parseFloat(item.currency_value).toFixed(2);
      // Normalize type to match internal logic and DOM selectors
      const normalizedType = (function (t) {
        const low = String(t || "").toLowerCase();
        if (low === "coins") return "coin";
        if (low === "bills") return "note";
        if (low === "coin" || low === "note") return low;
        return low; // fallback to lowercase
      })(item.type);

      const denominationBox = `
                <div class="denomination-box" data-type="${normalizedType}">
                    <span class="denom-label currency-value">${formattedCurrencyValue}</span>
                    <div class="counter">
                        <button class="decrease" data-value="${valFixed}" data-type="${normalizedType}">−</button>
                        <span class="count" data-value="${valFixed}">0</span>
                        <button class="increase" data-value="${valFixed}" data-type="${normalizedType}">+</button>
                    </div>
                    <button class="remove hidden" data-value="${valFixed}" id="cross">✖</button>
                </div>
            `;

      grid.append(denominationBox);


    });

    // Append the final grid after loop
    if (grid.children().length > 0) {
      container.append(grid);
    }
  }

  // Increase button click
  $(document).on('click', '.increase', function () {

    var currencyType = $(this).data("type");
    var currencyValue = parseFloat($(this).data('value'));
    var countElement = $(this).siblings('.count');
    var currencyCount = parseInt(countElement.text());
    var $btn = $(this);
    // reset any previous grey-out state before recalculating
    $btn.removeClass('gray-out');

    // In return mode, do not allow increasing beyond available count for this type/value
    var modeFlag = localStorage.getItem('modeFlag');
    if (modeFlag === 'return') {
      try {
        var availableList = JSON.parse(localStorage.getItem('AvailableDenominations')) || [];
        var key = parseFloat(currencyValue).toFixed(2);
        var t = String(currencyType).toLowerCase();
        var match = availableList.find(function (it) {
          var low = String(it.type || '').toLowerCase();
          if (low === 'coins') low = 'coin';
          if (low === 'bills') low = 'note';
          return String(it.currency_value) === key && low === t;
        });
        var avail = match ? (parseInt(match.count) || 0) : 0;
        // If already at or above availability, grey-out and block
        if (currencyCount >= avail) {
          $btn.addClass('gray-out');
          // Block increment if already at availability
          return;
        }
        // If the next increment will reach the limit, mark to grey-out after increment
        var shouldGreyAfter = (currencyCount + 1) >= avail;
      } catch (e) { /* ignore guard errors */ }
    }
    currencyCount += 1;
    countElement.text(currencyCount);
    var denominationBox = $(this).closest(".denomination-box");

    toggleCashButtons();// as per denomination selected enable/disable submit button
    updateDenominationBoxColorBasedOnCount(denominationBox); // Call the universal function to update color and visibility
    updateSelectedDenominationCount(currencyValue, currencyCount, currencyType);
    // Apply or clear grey-out state based on mode and availability
    if (modeFlag === 'return') {
      try {
        if (typeof shouldGreyAfter !== 'undefined' && shouldGreyAfter) {
          $btn.addClass('gray-out');
        } else {
          $btn.removeClass('gray-out');
        }
      } catch (e) { /* ignore */ }
    } else {
      $btn.removeClass('gray-out');
    }
  });

  // Increase button click
  $(document).on('click', '.decrease', function () {

    var currencyType = $(this).data("type");
    var currencyValue = parseFloat($(this).data('value'));
    var countElement = $(this).siblings('.count');
    var currencyCount = parseInt(countElement.text());
    // currencyCount -= 1;
    currencyCount = Math.max(0, currencyCount - 1);
    countElement.text(currencyCount);
    var denominationBox = $(this).closest(".denomination-box");
    // On decrease, if we were previously at the limit in return mode, allow increasing again (remove grey-out)
    try {
      var modeFlag = localStorage.getItem('modeFlag');
      if (modeFlag === 'return') {
        var availableList = JSON.parse(localStorage.getItem('AvailableDenominations')) || [];
        var key = parseFloat(currencyValue).toFixed(2);
        var t = String(currencyType).toLowerCase();
        var match = availableList.find(function (it) {
          var low = String(it.type || '').toLowerCase();
          if (low === 'coins') low = 'coin';
          if (low === 'bills') low = 'note';
          return String(it.currency_value) === key && low === t;
        });
        var avail = match ? (parseInt(match.count) || 0) : 0;
        // If below the availability, ensure increase is active (not greyed)
        if (currencyCount < avail) {
          $(this).siblings('.increase').removeClass('gray-out');
        }
      } else {
        $(this).siblings('.increase').removeClass('gray-out');
      }
    } catch (e) { /* ignore */ }

    toggleCashButtons();// as per denomination selected enable/disable submit button
    updateDenominationBoxColorBasedOnCount(denominationBox);  // Call the universal function to update color and visibility
    updateSelectedDenominationCount(currencyValue, currencyCount, currencyType);
  });

  //  update denomination count in localstoarge
  function updateSelectedDenominationCount(currencyValue, currencyCount, currencyType) {

    const modeFlag = localStorage.getItem("modeFlag");
    if (modeFlag === 'return') {
      var available = JSON.parse(localStorage.getItem("AvailableDenominations")) || JSON.parse(localStorage.getItem("Alldenominations")) || [];
      checkAvailableCurrency(available);
      return;
    }
    var key = parseFloat(currencyValue).toFixed(2);
    var denominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];

    var existing = denominations.find(item =>
      item.currency_value === key &&
      item.type === currencyType.toLowerCase() &&
      item.selected === "1"
    );

    if (existing) {
      existing.count = currencyCount.toString();
    } else {
      denominations.push({
        id: Date.now().toString(),
        currency_value: key,
        type: currencyType.toLowerCase(),
        is_active: "1",
        count: currencyCount.toString(),
        selected: "1"
      });
    }

    localStorage.setItem("Alldenominations", JSON.stringify(denominations));
    // console.log("Alldenominations:", denominations);

    const collected = { Bills: {}, Coins: {} };

    denominations.forEach(item => {
      const isSelected = item.selected === "1";
      const count = parseInt(item.count);
      const value = parseFloat(item.currency_value).toFixed(2);

      if (isSelected && count > 0) {
        const typeKey = item.type === "note" ? "Bills" : "Coins";
        collected[typeKey][value] = count;
      }
    });

    localStorage.setItem("CollectedDenominations", JSON.stringify(collected));
    // console.log("Collected Denominations:", collected);
    updateTotalPayableSection();
  }

  function updateTotalPayableSection() {

    var modeFlag = localStorage.getItem("modeFlag");
    var invoiceAmount = parseFloat($("#amount_1").val()) || 0;
    var returnAmt = parseFloat($("#returnAmount").text()) || 0;
    var denominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];
    var collectedAmount = 0;
    var selectedAmount = 0;


    // ---------- RETURN FLOW ----------
    if (modeFlag == 'return') {

      $(".denomination-box").each(function () {
        const count = parseInt($(this).find(".count").text()) || 0;
        const value = parseFloat($(this).find(".increase").data("value")) || 0;
        selectedAmount += count * value;
      });

      const pendingAmount = selectedAmount - returnAmt;

      if (pendingAmount < 0) {
        $("#returnToastMessage").text(`Currency of ${Math.abs(pendingAmount).toFixed(2)} is not available for issuing Return Amount.`);
        $("#returnOverlay").fadeIn();
        $("#returnToast").fadeIn();
      } else {
        $("#returnOverlay").hide();
        $("#returnToast").hide();
      }
      $("#amount-section-container").css({
        display: "flex",
        gap: "5px",
        "margin-top": "10px",
        "justify-content": "space-between",
        "align-items": "center",
        width: "100%",
      });

      $("#selectedAmount").html(`<strong>Selected Amount:</strong> ${formatNumber(selectedAmount)}`).css({
        "background-color": "#90EE90",
        "padding": "5px",
        "text-align": "center",
        "border-radius": "5px",
        "font-weight": "bold",
        "width": "fit-content",
        "margin": "5px auto"
      }).show();
      $("#pendingAmount").html(`<strong>Pending Amount:</strong> ${formatNumber(pendingAmount)}`).css({
        "background": "#FFC0CB",
        "padding": "5px",
        "text-align": "center",
        "border-radius": "5px",
        "font-weight": "bold",
        "width": "fit-content",
        "margin": "5px auto"
      }).show();

      $("#selectedAmount").css("background", "#90EE90");
      $("#pendingAmount").css("background", "#FFC0CB");
      $("#yourDivId").hide();
      $(".payment-box").show();
      $("#returnCompleteBtn").show();

      $(
        ".payment-box, #denomination-container, #clearAll, #amount-section-container"
      ).css({
        border: "9px solid #F9C4DA",
      });

      // Change background color and text color of the return amount row
      $(this).closest(".row").css({
        color: "#fff",
        padding: "2px",
        "border-radius": "5px",
        "margin-left": "-12px;",
      });

      // Style the button itself
      $(this).css({
        "background-color": "#E9136B",
        color: "#fff",
        border: "1px solid #E9136B",
      });
      $("#returnComplete").show();
      return;
    } else {
      // ---------- NORMAL AND COLLECTED FLOW ----------

      denominations.forEach(item => {
        if (item.selected === "1") {
          var count = parseInt(item.count) || 0;
          var value = parseFloat(item.currency_value) || 0;
          collectedAmount += count * value;
        }
      });

      // Prevent negative return amount in Collected/Normal flow
      var returnAmount = Math.max(0, collectedAmount - invoiceAmount);

      $("#totalPayableAmount").text(formatNumber(invoiceAmount));
      $("#depositedAmount").text(formatNumber(collectedAmount));
      $("#returnAmount").text(formatNumber(returnAmount));
      $("#balance").text(formatNumber(returnAmount));
      $("#pendingAmount").hide();

      $("#selectedAmount").html(`<strong>Selected Amount:</strong> ${formatNumber(collectedAmount)}`).css({
        "background-color": "#90EE90",
        "padding": "5px",
        "text-align": "center",
        "border-radius": "5px",
        "font-weight": "bold",
        "width": "fit-content",
        "margin": "5px auto"
      }).show();

      if (collectedAmount > 0) {
        $("#denomination-container").css("border", "9px solid #FFE0A7");
        $("#clearAll").css("border", "9px solid #FFE0A7");
        $(".payment-box").css("border", "9px solid #FFE0A7");
        $(".clear-btn").removeClass("hidden").show();
        $("#Collected").attr("style", "background-color: #FFE0A7 !important; color: #000 !important;");
        $("#amount-section-container").css({ border: "9px solid #FFE0A7" });
      }

      var $returnMatButton = $("#returnAmt");
      if (parseFloat(collectedAmount) <= invoiceAmount) {
        $returnMatButton.prop("disabled", true).css({
          "opacity": "0.6",
          "pointer-events": "none"
        });
      } else {
        $returnMatButton.prop("disabled", false).css({
          "opacity": "1",
          "pointer-events": "auto"
        });
      }
    }

  }

  // Remove button click and update denomination count
  $(document).on('click', '.remove', function () {
    var denominationBox = $(this).closest('.denomination-box');
    var currencyValue = parseFloat($(this).data("value")).toFixed(2);
    var currencyType = denominationBox.data("type").toLowerCase();

    // Set count to 0 in the UI
    denominationBox.find('.count').text('0');
    $(this).addClass('hidden');
    denominationBox.removeClass('highlight');
    // Reset the Increase button to initial state for this denomination
    denominationBox.find('.increase')
      .removeClass('gray-out')
      .prop('disabled', false)
      .css({ 'pointer-events': 'auto', 'opacity': '' });

    var denominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];  // Update localStorage count = 0

    denominations.forEach(item => {
      if (item.currency_value === currencyValue && item.type === currencyType && item.selected === "1") {
        item.count = "0";
      }
    });

    localStorage.setItem("Alldenominations", JSON.stringify(denominations));
    toggleCashButtons(); // as per denomination selected enable/disable submit button

    // Update the visual appearance after resetting count
    updateDenominationBoxColorBasedOnCount(denominationBox);
    updateTotalPayableSection();
    // Do not immediately re-enforce availability here; keep UI in initial state after removal
  });

  // clear all data from localStorage and also reset denomination box
  $("#clearAll").click(function () {

    localStorage.removeItem("modeFlag");
    // Clear computed stores to avoid stale state
    localStorage.removeItem("CollectedDenominations");
    localStorage.removeItem("ReturnDenominations");
    // Restore working copy from immutable availability snapshot (jQuery style)
    var availableStr = localStorage.getItem("AvailableDenominations");
    var available = availableStr ? JSON.parse(availableStr) : null;
    if ($.isArray(available)) {
      localStorage.setItem("Alldenominations", JSON.stringify(available));
    }
    $(".denomination-box").each(function () {
      const $box = $(this);
      const currencyValue = parseFloat($box.find(".increase").data("value"));
      const currencyType = $box.data("type");
      const $label = $box.find(".denom-label");
      const $countElem = $box.find(".count");
      const $increase = $box.find(".increase");
      const $decrease = $box.find(".decrease");
      const $remove = $box.find(".remove");

      $box.find(".count").text("0");
      $box.css({
        "background-color": "",
        "opacity": "",
        "pointer-events": "",
      });

      $label.add($countElem).css({
        "background-color": "",
        "opacity": "",
        "pointer-events": "",
        "color": ""
      });

      $increase.add($decrease).add($remove).css({
        "opacity": "",
        "pointer-events": "",
      }).prop("disabled", false); // ensure they are not disabled
      // $box.removeClass("highlight").css("background-color", "");
      // $box.find(".remove").addClass("hidden");
      updateDenominationBoxColorBasedOnCount($box);

      updateSelectedDenominationCount(currencyValue, 0, currencyType);
    });

    $("#returnAmount").text("0");
    $("#depositedAmount").text("0");
    $("#balance").text("0");

    $("#yourDivId").hide();
    $("#selectedAmount").hide();
    $("#pendingAmount").hide();
    $("#amount-section-container").hide();

    $("#Collected").css("background-color", "");
    $("#denomination-container").css("border", "9px solid #E6F5FF");
    $("#clearAll").css("border", "9px solid #E6F5FF");
    $(".payment-box").css("border", "9px solid rgb(230, 245, 255)").show();
    $("#amnt").show();
  });

  // collected click event
  $("#Collected").click(function () {
    localStorage.setItem("modeFlag", "collected");
    // render collected denomination when click on collected
    renderCollectedDenominations();
    // Update totals and return amount
    updateTotalPayableSection();
    $("#amount-section-container").show();
  });

  // render collected denomination when click on collected
  function renderCollectedDenominations() {

    $(".denomination-box").each(function () {
      const $box = $(this);
      // Reset highlight/background
      $box.removeClass("highlight").css("background-color", "#ffffff");
      // Reset counts
      $box.find(".count").text("0");
      // Ensure buttons and labels are fully enabled (undo Return mode greying)
      $box.find(".denom-label, .count").css({
        "background-color": "",
        "opacity": "1",
        "pointer-events": "auto",
      });
      $box.find(".increase, .decrease, .remove").css({
        "opacity": "1",
        "pointer-events": "auto",
      });
      // Clear any inline display:none from previous .hide()
      $box.find(".remove").show().addClass("hidden");
    });
    var Alldenominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];

    Alldenominations.forEach(item => {
      if (item.selected === "1" && parseInt(item.count) > 0) {
        const currencyValue = parseFloat(item.currency_value).toFixed(2);
        const type = item.type;

        const $box = $(`.denomination-box[data-type="${type}"]`).filter((_, el) => {
          const value = parseFloat($(el).find(".increase").data("value")).toFixed(2);
          return value === currencyValue;
        });

        // Apply updates to existing box
        $box.find(".count").text(item.count);
        // $box.addClass("highlight").css("background-color", "#90EE90");
        // $box.find(".remove").removeClass("hidden");
        // Call the universal function to update color and visibility
        updateDenominationBoxColorBasedOnCount($box);
      }
    });
  }

  // count wise set colour for denomination box
  function updateDenominationBoxColorBasedOnCount(denominationBox) {
    const count = parseInt(denominationBox.find('.count').text()) || 0;

    if (count === 0) {
      denominationBox.css("background-color", "#ffffff");
      denominationBox.removeClass("highlight");
      denominationBox.find('.remove').addClass('hidden').hide();
    } else if (count > 0) {
      denominationBox.css("background-color", "#90EE90");
      denominationBox.addClass("highlight");
      // Remove hidden class and ensure it becomes visible
      denominationBox.find('.remove').removeClass('hidden').show();
    }
  }
  // Return click event
  $("#returnAmt").click(function () {
    localStorage.setItem("modeFlag", "return");
    calculateReturnDenominationAmount();
    updateTotalPayableSection();
  });
  $(document).on("click", "#returnToastOkBtn", function () {
    $("#returnOverlay").fadeOut();
    $("#returnToast").fadeOut();
  });

  // calculate return amount when we click on return button
  function calculateReturnDenominationAmount() {
    // Always use immutable availability snapshot for return
    var Alldenominations = JSON.parse(localStorage.getItem("AvailableDenominations")) || JSON.parse(localStorage.getItem("Alldenominations")) || [];
    var returnAmount = parseFloat($("#returnAmount").text().replace(/,/g, '')) || 0;
    var returnDenominations = { coin: {}, note: {}, };

    // Reset UI highlights and counts before return rendering 
    $(".denomination-box").each(function () {
      $(this).removeClass("highlight").css("background-color", "#ffffff");
      $(this).find(".count").text("0");
      // In Return mode, rely on the 'hidden' class only; don't set inline display:none
      $(this).find(".remove").addClass("hidden");
    });

    // Sort denominations by currency value (highest to lowest)
    Alldenominations.sort((a, b) => b.currency_value - a.currency_value);
    // var originalAmount = returnAmount; // Save the original amount for final comparison

    Alldenominations.forEach(function (item) {
      var currency = item.currency_value;
      var availableCount = Number(item.count) || 0;
      var type = (function (t) {
        const low = String(t || "").toLowerCase();
        if (low === "coins") return "coin";
        if (low === "bills") return "note";
        if (low === "coin" || low === "note") return low;
        return low;
      })(item.type);
      var requiredCount = Math.floor(returnAmount / currency); // How many are count is needed

      disableDenominationBoxAsPerAvaCount(availableCount, requiredCount, currency, type); // grey out denomination box as per checking availability of currency

      // Calculate how much to use
      if (requiredCount > 0 && availableCount > 0) {
        var usedCount = Math.min(requiredCount, availableCount);
        if (type === "coin") {
          returnDenominations.coin[currency] = usedCount;
        } else if (type === "note") {
          returnDenominations.note[currency] = usedCount;
        } else {
          // In case an unexpected type sneaks in, place by matching DOM type
          returnDenominations[type] = returnDenominations[type] || {};
          returnDenominations[type][currency] = usedCount;
        }
        returnAmount -= usedCount * currency;
      }
    });

    localStorage.setItem("ReturnDenominations", JSON.stringify(returnDenominations));
    // console.log("ReturnDenominations :", returnDenominations);

    renderReturnDenomination(returnDenominations);
    // After rendering, enforce availability-based button states
    try {
      var _available = JSON.parse(localStorage.getItem("AvailableDenominations")) || Alldenominations;
      if (Array.isArray(_available)) {
        checkAvailableCurrency(_available);
      }
    } catch (e) { /* ignore */ }
    // return returnDenominations;
  }

  // Render return denominations
  function renderReturnDenomination(returnDenominations) {
    const allDenominations = [];

    // Merge coin denominations
    if (returnDenominations.coin) {
      Object.entries(returnDenominations.coin).forEach(([value, count]) => {
        allDenominations.push({
          value: parseFloat(value),
          count: parseInt(count),
          type: "coin",
        });
      });
    }

    // Merge note denominations
    if (returnDenominations.note) {
      Object.entries(returnDenominations.note).forEach(([value, count]) => {
        allDenominations.push({
          value: parseFloat(value),
          count: parseInt(count),
          type: "note",
        });
      });
    }

    // Sort all denominations in descending order by value
    const sortedDenoms = allDenominations.sort((a, b) => b.value - a.value);

    // Render denominations
    sortedDenoms.forEach(({ value, count, type }) => {
      if (count > 0) {
        // Find all denomination boxes with matching value
        const $boxes = $(".denomination-box").filter(function () {
          return $(this).find(".count").data("value") == value;
        });

        // Match box by type (coin/note)
        let $preferredBox = $boxes.filter(`[data-type='${type}']`).first();

        // If not found, fallback to any box with that value
        if ($preferredBox.length === 0) {
          $preferredBox = $boxes.first();
        }

        if ($preferredBox.length) {
          $preferredBox.addClass("highlight");
          $preferredBox.find(".count").text(count);
          $preferredBox.find(".remove").removeClass("hidden").show();
        }
      }
    });

    // Fallback rendering if nothing was rendered
    if (sortedDenoms.length === 0) {
      for (let key in returnDenominations) {
        let countElement = $('.count[data-value="' + key + '"]');

        if (countElement.length === 0) {
          console.error("No .count element found for denomination:", key);
        } else {
          const count = returnDenominations[key];
          countElement.text(count);
          if (count > 0) {
            countElement.closest(".denomination-box").addClass("highlight");
          } else {
            countElement.closest(".denomination-box").removeClass("highlight");
          }
        }
      }
    }
  }
  // grey out denomination box as per checking availability of currency
  function disableDenominationBoxAsPerAvaCount(availableCount, requiredCount, currency, type) {
    // console.log("availableCount:", availableCount, "requiredCount:", requiredCount, "currency:", currency, "type:", type);
    // Format currency to two decimal places
    const fixedCurrency = parseFloat(currency).toFixed(2);
    // const $countElem = $(`.denomination-box .count[data-value="${fixedCurrency}"]`);
    const $countElem = $(`.denomination-box[data-type="${type}"] .count[data-value="${fixedCurrency}"]`);

    if ($countElem.length) {
      const $box = $countElem.closest(".denomination-box");
      const $increase = $box.find(".increase");
      const $decrease = $box.find(".decrease");
      const $remove = $box.find(".remove");
      const $label = $box.find(".denom-label");

      // 1) No availability: gray out fully and disable increase
      if (availableCount === 0) {
        $label.css({ "background-color": "#eee", "opacity": "0.6", "pointer-events": "none" });
        $countElem.css({ "background-color": "#eee", "opacity": "0.6", "pointer-events": "none" });
        $increase.css({ "opacity": "0.4", "pointer-events": "none" });
        $decrease.css({ "opacity": "1", "pointer-events": "auto" });
        $remove.css({ "opacity": "1", "pointer-events": "auto" });
        $box.css({ "background-color": "#f5f5f5" });
        return;
      }

      // 2) Availability exists but selection reached the available count: disable only increase
      if (requiredCount >= availableCount && requiredCount > 0) {
        // Restore normal visuals, but lock the increase
        $label.add($countElem).css({ "background-color": "", "opacity": "1", "pointer-events": "auto" });
        $decrease.add($remove).css({ "opacity": "1", "pointer-events": "auto" });
        $increase.css({ "opacity": "0.4", "pointer-events": "none" });
        $box.css({ "background-color": "" });
        return;
      }

      // 3) Availability exists and selection is below available: fully interactive
      $label.add($countElem).css({ "background-color": "", "opacity": "1", "pointer-events": "auto" });
      $increase.add($decrease).add($remove).css({ "opacity": "1", "pointer-events": "auto" });
      $box.css({ "background-color": "" });
    }
  }

  // after return mode and increse event then check available count
  function checkAvailableCurrency(Alldenominations) {

    // Sort denominations by currency value (highest to lowest)
    Alldenominations.sort((a, b) => b.currency_value - a.currency_value);
    // var originalAmount = returnAmount; // Save the original amount for final comparison
    Alldenominations.forEach(function (item) {
      var currency = item.currency_value;
      var availableCount = Number(item.count) || 0;
      var type = (function (t) {
        const low = String(t || "").toLowerCase();
        if (low === "coins") return "coin";
        if (low === "bills") return "note";
        if (low === "coin" || low === "note") return low;
        return low;
      })(item.type);
      const selector = `.denomination-box[data-type="${type}"] .increase[data-value="${parseFloat(currency).toFixed(2)}"]`;
      const requiredCount = parseInt($(selector).siblings('.count').text(), 10) || 0;

      disableDenominationBoxAsPerAvaCount(availableCount, requiredCount, currency, type); // grey out denomination box as per checking availability of currency
    });
    updateTotalPayableSection();
  }

  //format money
  function formatMoneys(x, symbol) {

    if (!symbol) {
      symbol = "";
    }
    if (site.settings.sac == 1) {
      return (site.settings.display_symbol == 1 ? site.settings.symbol : '') +
        '' + (parseFloat(x).toFixed(site.settings.decimals)) +
        (site.settings.display_symbol == 2 ? site.settings.symbol : '');
    }
    var fmoney = accounting.formatMoney(x, symbol, site.settings.decimals, site.settings.thousands_sep == 0 ? ' ' : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
    fmoney = (fmoney == '-0.00') ? '0.00' : fmoney; //convert -0.00 to 0.00
    return (site.settings.display_symbol == 1 ? site.settings.symbol : '') + fmoney + (site.settings.display_symbol == 2 ? site.settings.symbol : '');
  }

  // Update Register Modal Trigger 
  $(document).on('click', '.UpdtRegister', function () {

    $('#paymentModal').modal('hide');
    // When the modal is fully hidden
    $('#paymentModal').one('hidden.bs.modal', function () {
      // Trigger the update_register_button
      $('#update_register_button').click();
    });
  });

  // TOGGLE of payment options
  const divElement = document.getElementById("toToggle");
  const togglElement = document.getElementById("arrow-toggle");
  // Toggle expand/collapse on arrow click
  togglElement.addEventListener("click", () => {
    if (divElement.style.height === "100%") {
      closeToggle();
      return;
    }

    divElement.style.height = "100%";
    divElement.style.zIndex = "1000";
    togglElement.style.transform = "rotate(180deg)";
    togglElement.style.transition = "transform 0.3s ease";
  });

  // Collapse function
  function closeToggle() {
    divElement.style.height = "13vh";
    togglElement.style.transform = "rotate(0deg)";
    togglElement.style.transition = "transform 0.3s ease";
  }

  // Collapse on payment method selection
  divElement.addEventListener("click", (event) => {
    const target = event.target.closest(".payment-method");
    if (target) {
      // console.log("Payment method clicked:", target.textContent);
      closeToggle();
    }
  });

  // Toggle button states based on payment type and denomination selection
  function toggleCashButtons() {
    const selectedPaymentRaw = $('input[name="colorRadio"]:checked').val();
    const selectedPayment = String(selectedPaymentRaw || '').toLowerCase().trim();
    const isCash = selectedPayment.indexOf('cash') !== -1; // handle values like 'Cash', 'CASH', 'cash_payment'

    // Check if any denomination count is > 0
    let anySelected = false;
    $('.denomination-box').each(function () {
      const count = parseInt($(this).find('.count').text()) || 0;
      if (count > 0) {
        anySelected = true;
        return false;
      }
    });

    var $targets = $('.disable-on-cash, .final-submit-btn');
    if (isCash) {
      $targets.prop('disabled', !anySelected);
    } else {
      $targets.prop('disabled', false);
    }
  }

  // detect cash selection
  function isCashSelected() {
    const raw = $('input[name="colorRadio"]:checked').val();
    return String(raw || '').toLowerCase().trim().indexOf('cash') !== -1;
  }

  // Helper: check if any denomination count > 0
  // function hasAnyDenominationSelected() {
  //   let anySelected = false;
  //   $('.denomination-box').each(function () {
  //     const count = parseInt($(this).find('.count').text()) || 0;
  //     if (count > 0) { anySelected = true; return false; }
  //   });
  //   return anySelected;
  // }

  //ensure buttons remain disabled when Cash selected with no denominations
  setInterval(function () {
    try {
      if (isCashSelected() && !hasAnyDenominationSelected()) {
        var $scope = $('#paymentModal');
        $scope.find('.disable-on-cash, .final-submit-btn, button[type="submit"], input[type="submit"]').prop('disabled', true);
      }
    } catch (e) {}
  }, 250);
  $('input[name="colorRadio"]').on('change', function () {
    toggleCashButtons();
  });
  $('#paymentModal').on('shown.bs.modal', function () {
    toggleCashButtons();
  });

  //Splitpay && SplitCheck Disable 
  setInterval(function () {
    $('#splitpay, #split-check').prop('disabled', true);
  }, 100);

  // right side corner cross button
  $(document).on("click", ".reset-denominations", function () {
    $('#clearAll').trigger('click');
    localStorage.clear();
  });

  // remove all data from localstorage
  $("#backToPOS").click(function () {
    localStorage.clear();
  });

  window.addEventListener("load", function () {
    localStorage.removeItem("Alldenominations");
    localStorage.removeItem("CollectedDenominations");
    localStorage.removeItem("ReturnDenominations");
    localStorage.removeItem("modeFlag");
  });

  // $(".final-submit-btn").click(function () {
  //   //  var formattedData =  saveDenominationDb();

  //   var ReturnDenominations = JSON.parse(localStorage.getItem("ReturnDenominations")) || {};
  //   var CollectedDenominations = JSON.parse(localStorage.getItem("CollectedDenominations")) || [];

  //   $.ajax({
  //     url: site.base_url + "pos/saveDenominations",
  //     type: "POST",
  //     data: {
  //       CollectedDenomination: JSON.stringify(CollectedDenominations),
  //       ReturnsDwnominations: JSON.stringify(ReturnDenominations),
  //     },
  //     success: function (response) {
  //       localStorage.removeItem("Alldenominations");
  //       localStorage.removeItem("CollectedDenominations");
  //       localStorage.removeItem("ReturnDenominations");
  //       localStorage.removeItem("modeFlag");
  //       console.log(response);
  //     },
  //     error: function (xhr, status, error) {
  //       alert("There was an error saving the data.");
  //       console.log(error);
  //     },
  //   });
  // });

  $(".final-submit-btn").click(function () {
    var modeFlag = localStorage.getItem("modeFlag");
    var invoiceAmount = parseFloat($("#amount_1").val()) || 0;
    var returnAmt = parseFloat($("#returnAmount").text()) || 0;
    var denominations = JSON.parse(localStorage.getItem("Alldenominations")) || [];

    let selectedAmount = 0;
    let pendingAmount = 0;
    let collectedAmount = 0;

    if (modeFlag === "return") {
      // Return mode: Calculate selected amount from DOM
      $(".denomination-box").each(function () {
        const count = parseInt($(this).find(".count").text()) || 0;
        const value = parseFloat($(this).find(".increase").data("value")) || 0;
        selectedAmount += count * value;
      });

      pendingAmount = selectedAmount - returnAmt;

    } else {
      // Collected mode: Calculate from localStorage
      denominations.forEach(item => {
        if (item.selected === "1") {
          const count = parseInt(item.count) || 0;
          const value = parseFloat(item.currency_value) || 0;
          collectedAmount += count * value;
        }
      });

      selectedAmount = collectedAmount;
      pendingAmount = selectedAmount - invoiceAmount;
      returnAmt = selectedAmount - invoiceAmount;

      // Ensure paid amount fields reflect what was actually collected (for Cash)
      try {
        if (typeof isCashSelected === 'function' && isCashSelected()) {
          if ($('#amount_1').length) {
            $('#amount_1').val(collectedAmount);
          }
          // if ($('#amount_val_1').length) {
          //   $('#amount_val_1').val(collectedAmount);
          // }
          if ($('#balance_amount_1').length) {
            $('#balance_amount_1').val(Math.max(0, invoiceAmount - collectedAmount));
          }
        }
      } catch (e) { /* noop */ }
    }

    // Set calculated values into hidden fields 
    $("#selected_amounts").val(selectedAmount);
    $("#pending_amount").val(pendingAmount);
    $("#deposited_amount").val(collectedAmount);
    $("#invoice_amounts").val(invoiceAmount);
    $("#return_Amount").val(returnAmt);

    // Fetch saved denomination data
    var ReturnDenominations = JSON.parse(localStorage.getItem("ReturnDenominations")) || {};
    var CollectedDenominations = JSON.parse(localStorage.getItem("CollectedDenominations")) || [];

    $.ajax({
      url: site.base_url + "pos/saveDenominations",
      type: "POST",
      data: {
        CollectedDenomination: JSON.stringify(CollectedDenominations),
        ReturnsDwnominations: JSON.stringify(ReturnDenominations),
      },
      success: function (response) {
        localStorage.removeItem("Alldenominations");
        localStorage.removeItem("CollectedDenominations");
        localStorage.removeItem("ReturnDenominations");
        localStorage.removeItem("modeFlag");
      },
      error: function (xhr, status, error) {
        alert("There was an error saving the data.");
        console.log("Error:", error);
      }
    });
  });

});

// exclude cash payment method : other payment method wise shown payment box and hide denomination box
document.addEventListener("DOMContentLoaded", function () {
  // Payment methods that should hide denomination box
  const excludedPayments = [
    "Cheque",
    "award_point",
    "deposit",
    "other",
    "gift_card",
    "NEFT",
    "DC",
    "CC",
    "payswiff",
    "PAYTM",
    "Googlepay",
    "magicpin",
    "complimentry",
    "UPI_QRCODE",
    "ubereats",
    "razorpay",
    "zomato",
    "paytm",
    "swiggy",
    "payumoney",
    "ccavenue",
    "authorize",
    "instamojo",
    "stripe",
    "ppp",
  ];

  // Normalize excluded payment names for case-insensitive comparison
  const excludedPaymentsLower = excludedPayments.map(function (p) { return String(p).toLowerCase(); });

  // Select DOM elements
  const paymentRadios = document.querySelectorAll('input[name="colorRadio"]');
  const denominationContainer = document.getElementById(
    "denomination-container"
  );
  const clearButton = document.getElementById("clearAll");
  const amountOuterDiv = document.getElementById("yourDivId");
  const paymentBox = document.getElementById("payment-box");

  if (!denominationContainer || !clearButton || !amountOuterDiv) {
    console.error(
      "Error: #denomination-container, #clearAll or #yourDivId not found in the DOM."
    );
    return;
  }

  function toggleDenominationBox(selectedValueRaw) {
    const selectedValue = String(selectedValueRaw || '').toLowerCase().trim();
    if (excludedPaymentsLower.includes(selectedValue)) {
      // Hide denomination container and clear button for excluded payments
      denominationContainer.style.display = "none";
      clearButton.style.display = "none";

      amountOuterDiv.style.display = "block";
      paymentBox.style.setProperty("display", "none", "important");

      const rect = denominationContainer.getBoundingClientRect();

      amountOuterDiv.style.position = "absolute";
      amountOuterDiv.style.top = "10px";
      amountOuterDiv.style.left = "-57rem";
      amountOuterDiv.style.width = "100%";

    } else {
      denominationContainer.style.display = "block";
      clearButton.style.display = "block";

      if (selectedValue.indexOf('cash') !== -1) {
        // Hide amountOuterDiv (#yourDivId) and show paymentBox
        amountOuterDiv.style.position = "relative";
        amountOuterDiv.style.top = "0px";
        amountOuterDiv.style.left = "0px";
        amountOuterDiv.style.width = "";

        amountOuterDiv.style.setProperty("display", "none", "important");
        paymentBox.style.setProperty("display", "block", "important");

      } else {
        // Apply custom positioning for non-cash payments
        const rect = denominationContainer.getBoundingClientRect();

        amountOuterDiv.style.position = "absolute";
        amountOuterDiv.style.top = `${rect.top + window.scrollY}px`;
        amountOuterDiv.style.left = "-38em"; // or adjust based on design
        amountOuterDiv.style.width = `${rect.width}px`;

        amountOuterDiv.style.setProperty("display", "block", "important");
        paymentBox.style.setProperty("display", "none", "important");
      }
    }
  }

  // Attach event listeners to payment radio buttons
  paymentRadios.forEach((radio) => {
    radio.addEventListener("change", function () {
      toggleDenominationBox(this.value);
      try { toggleCashButtons(); } catch (e) {}
    });
  });

  // Run on page load to check initial state
  const checkedRadio = document.querySelector(
    'input[name="colorRadio"]:checked'
  );
  if (checkedRadio) {
    toggleDenominationBox(checkedRadio.value);
    try { toggleCashButtons(); } catch (e) {}
  }
});
