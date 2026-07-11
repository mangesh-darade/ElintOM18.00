// Quantity decimal formatter using global qty_decimals setting (similar to BOM screens)
function quantityDecimal(x) {
  if (x == null || x === "") return "";

  // Normalize to string and remove thousand separators (comma/space) before parsing
  var s = String(x).trim();
  s = s.replace(/,/g, "").replace(/\s/g, "");

  var n = parseFloat(s);
  if (isNaN(n)) return "0";

  var decimals = 2;
  if (typeof site !== "undefined" && site && site.settings) {
    if (
      site.settings.qty_decimals !== undefined &&
      site.settings.qty_decimals !== null
    ) {
      decimals = parseInt(site.settings.qty_decimals);
    } else if (
      site.settings.decimals !== undefined &&
      site.settings.decimals !== null
    ) {
      decimals = parseInt(site.settings.decimals);
    }
  }
  return n.toFixed(decimals);
}

// Step attribute for number inputs based on qty_decimals (e.g. 3 -> "0.001", 0 -> "1")
function quantityInputStep() {
  var d = 2;
  if (
    typeof site !== "undefined" &&
    site &&
    site.settings &&
    site.settings.qty_decimals != null
  ) {
    d = parseInt(site.settings.qty_decimals);
  }
  if (d === 0) return "1";
  return "0." + "0".repeat(d - 1) + "1";
}

$(document).ready(function () {
  //////////////////////////////////////////////////Left Menu////////////////////////////////////////////////////////////////////////////////////////////
  // search products
  $(".search-box").on("keyup", function () {
    var searchText = $(this).val().toLowerCase();
    $(".mainmenu li").each(function () {
      var productText = $(this).text().toLowerCase();
      var isVisible = productText.indexOf(searchText) > -1;
      $(this).toggle(isVisible);
    });
  });

  // Function to populate the product list dynamically
  // function productList(products) {

  //     $('.mainmenu').empty();
  //     $.each(products, function (index, product) {
  //         var activeClass = index === 0 ? ' active' : '';
  //         var productText = product.name + ' (' + product.order_quantity + '/' + product.stock_quantity + ')';
  //         var productItem = '<li class=" btn-sty product" data-product-id="' + product.id + '">' + productText + '</li>';
  //         $('.mainmenu').append(productItem);
  //     });
  //     setTimeout(function() {
  //         $('.mainmenu .product:first').trigger('click'); // Trigger click event for the first product to load its details immediately
  //     }, 100);
  // }
  // productList(products);
  // $(document).on('click', '.product', function () {
  //     $('.product').removeClass('active');
  //     $(this).addClass('active');
  //     var productId = $(this).data('product-id');
  //     localStorage.setItem('productId', productId);
  //     // var productDetails = JSON.parse(localStorage.getItem('productDetails'));
  //     // $('#productName').text(productDetails.name);
  //     var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time
  //     orderDetails(productId, manufacturingDate);

  // });

  // Function to log out
  function logout() {
    // Clear the stored product ID on logout
    localStorage.removeItem("productId");
    // Redirect or refresh page as needed
    location.reload(); // Reload the page to apply changes
  }

  // Add click handler for the logout button
  $("#logoutButton").click(function () {
    logout(); // Call the logout function when the logout button is clicked
  });

  // Function to display the product list
  function productList(products) {
    var storedProductId = localStorage.getItem("productId"); // Retrieve stored product ID

    $(".mainmenu").empty();
    $.each(products, function (index, product) {
      var activeClass = product.id == storedProductId ? " active" : ""; // Check if it matches stored product ID
      var orderQty = product.order_quantity ?? 0;
      var stockQty = product.stock_quantity ?? 0;
      // Show quantities using global qty_decimals setting
      var productText =
        product.name +
        " (" +
        quantityDecimal(orderQty) +
        "/" +
        quantityDecimal(stockQty) +
        ")";
      var productItem =
        '<li class="btn-sty product' +
        activeClass +
        '" data-product-id="' +
        product.id +
        '">' +
        productText +
        "</li>";
      $(".mainmenu").append(productItem);
    });

    setTimeout(function () {
      if (storedProductId) {
        // Trigger click event for the stored product to load its details
        $(
          '.mainmenu .product[data-product-id="' + storedProductId + '"]',
        ).trigger("click");
      } else {
        // No stored product, activate the first product
        $(".mainmenu .product:first").addClass("active").trigger("click");
      }
    }, 100);
  }

  // Call productList function to initialize when the document is ready
  $(document).ready(function () {
    productList(products);
  });
  function clearIngredientStorage() {
    localStorage.removeItem("selectedIngredientIds");
    localStorage.removeItem("selectedBatchIds");
    localStorage.removeItem("allIngredients");
    localStorage.removeItem("Ingredients_details");
    console.log(
      "Cleared: selectedIngredientIds, allIngredients, Ingredients_details",
    );
  }

  // clear on page load
  $(document).ready(function () {
    clearIngredientStorage();
  });

  // clear on .btn-sty click
  $(document).on("click", ".btn-sty", function () {
    clearIngredientStorage();
  });

  // Click event for product items
  $(document).on("click", ".product", function () {
    $(".product").removeClass("active");
    $(this).addClass("active");
    var productId = $(this).data("product-id");
    localStorage.setItem("productId", productId); // Store selected product ID
    // Reset variant state until orderDetails returns (keeps non-variant Add Batch/Ingredients safe)
    window.managerProductVariants = [];
    localStorage.removeItem("batchOptionId");
    var manufacturingDate = getCurrentDateTime(); // Get the formatted current date and time
    orderDetails(productId, manufacturingDate);
  });

  // --- Variant products: shared state (set when orderDetails loads product variants) ---
  window.managerProductVariants = [];
  window.variantRmMultiMode = false;
  window.multiVariantRmEntries = [];
  window.variantBatchOpenSeq = 0;

  /** Fills #batchProductVariant + batchOptionId for Ingredients button; multi Add Batch uses managerProductVariants. */
  function populateBatchVariants(variants) {
    window.managerProductVariants =
      variants && variants.length > 0 ? variants : [];
    var $row = $("#batchVariantRow");
    var $select = $("#batchProductVariant");
    $select.empty();

    if (variants && variants.length > 0) {
      $.each(variants, function (index, variant) {
        $select.append(
          $("<option>", {
            value: variant.id,
            text: variant.name,
            "data-price": variant.price != null ? variant.price : "",
          }),
        );
      });
      $select.prop("selectedIndex", 0);
      localStorage.setItem("batchOptionId", variants[0].id);
      if (variants[0].price != null && variants[0].price !== "") {
        $("#price").text(variants[0].price);
      }
      $row.css("display", "flex");
    } else {
      localStorage.removeItem("batchOptionId");
      $row.css("display", "none");
    }
  }

  $(document).on("change", "#batchProductVariant", function () {
    var optionId = $(this).val();
    if (optionId) {
      localStorage.setItem("batchOptionId", optionId);
    } else {
      localStorage.removeItem("batchOptionId");
    }
    var variantPrice = $(this).find("option:selected").data("price");
    if (variantPrice !== undefined && variantPrice !== "") {
      $("#price").text(variantPrice);
    }
  });

  //////////////////////////////////////////////////show product wise orders in grid////////////////////////////////////////////////////////////////////////////////////////////

  function orderDetails(productId, manufacturingDate = null) {
    var productionUnitName = $("#productionUnitName").val() || "";
    $.ajax({
      url: site.base_url + "Production_Unit/getProductWiseAllDetails",
      method: "GET",
      data: {
        productId: productId,
        manufacturingDate: manufacturingDate,
        productionUnitName: productionUnitName,
      },
      dataType: "json",
      success: function (response) {
        // Debug: Show the first item in response array
        if (response && response.length > 0) {
          if (response[0].OrderDetails) {
          }
        }

        var yield_unit = null;
        var yieldData = null;

        // First, get yieldData from response root level
        if (response && response.length > 0 && response[0].yieldData) {
          yieldData = response[0].yieldData;
          localStorage.setItem("yieldData", JSON.stringify(yieldData));
        }

        $.each(response, function (index, OrderDetails) {
          if (
            OrderDetails.productDetails &&
            OrderDetails.productDetails.yield_unit !== undefined
          ) {
            yield_unit = OrderDetails.productDetails.yield_unit;
            console.log("Found yield_unit in response:", yield_unit);
            return false; // Exit loop after finding yield_unit
          }
        });
        localStorage.setItem("yield_unit", yield_unit); // Store for popup use

        $.each(response, function (index, OrderDetails) {
          var productStock = OrderDetails.productStock;
          var productBatches = OrderDetails.productBatches;
          // ✅ Show minimum batch quantity below Build Quantity
          if (yieldData && yieldData.min_batch_qty) {
            var minBatchQty = yieldData.min_batch_qty || 0;
            var minBatchUnit = yieldData.batch_unit_name || "";

            $("#minBatchQtyText").html(
              "Min batch qty: " +
                formatQuantity(minBatchQty) +
                " " +
                minBatchUnit,
            );
          } else {
            $("#minBatchQtyText").text("");
          }
          // ===============================
          // Yield Qty & Sales Qty setup
          // ===============================

          if (yieldData && yieldData.min_batch_qty) {
            // Only set values if popup not initializing
            if (!window.popupInitializing) {
              var minBatchQty = parseFloat(yieldData.min_batch_qty) || 0;

              var salesUnits = 0;
              var salesUnit = "";

              if (yieldData.sales_units) {
                var parts = yieldData.sales_units.split(" ");
                salesUnits = parseFloat(parts[0]) || 0;
                salesUnit = parts.slice(1).join(" ") || "";
              }

              var yieldUnit = yieldData.batch_unit_name || "";

              // Set values
              $("#yieldQty").val(minBatchQty);
              $("#salesQty").val(salesUnits);

              // Set units - Build qty, yield qty, and wastage all use the same unit (batch unit)
              $("#yieldUnit").text(yieldUnit);
              $("#salesUnit").text(salesUnit);
              // Ensure wastage unit is same as build/yield unit
              $("#rmBatchUnit").text(yieldUnit);
            } else {
              // Still set units (important)
              var yieldUnit = yieldData.batch_unit_name || "";
              var salesUnit = "";

              if (yieldData.sales_units) {
                var parts = yieldData.sales_units.split(" ");
                salesUnit = parts.slice(1).join(" ") || "";
              }

              // Set units - Build qty, yield qty, and wastage all use the same unit (batch unit)
              $("#yieldUnit").text(yieldUnit);
              $("#salesUnit").text(salesUnit);
              // Ensure wastage unit is same as build/yield unit
              $("#rmBatchUnit").text(yieldUnit);
            }
          } else {
            if (!window.popupInitializing) {
              console.log("orderDetails: Clearing yield/sales (no yieldData)");

              $("#yieldQty").val("");
              $("#salesQty").val("");
              $("#yieldUnit").text("");
              $("#salesUnit").text("");
            }
          }
          var latestProductBatches = OrderDetails.latestProductBatches;
          var productDetails = OrderDetails.productDetails;
          var productVariants = OrderDetails.productVariants;
          var lastBatch = OrderDetails.lastBatch;
          var locationCode = OrderDetails.locationCode;
          var Ingredients = OrderDetails.Ingredients;

          populateBatchVariants(productVariants);

          if (
            OrderDetails.OrderDetails &&
            OrderDetails.OrderDetails.length > 0
          ) {
            loadOrderItems(OrderDetails.OrderDetails);
          } else {
            resetQuantities(productStock.stock_quantity); // If no order details found, clear and reset quantities
            $("#tablebody").empty();
          }

          createBatchNymber(lastBatch, locationCode); // create new batch number against product
          if (productDetails) {
            $("#unit").text(productDetails.unit_name);
            $("#price").text(productDetails.price);
            $("#productName").text(productDetails.name);
            $("#ingredientsModalLabel").text(
              "Bill Of Materials : " + productDetails.name,
            );
            $("#rmConsumptionModalLabel").text(
              "Ingrediants Consumption: " + productDetails.name,
            );
          }
          if (Ingredients) {
            localStorage.setItem(
              "Ingredients_details",
              JSON.stringify(Ingredients),
            );
          } else {
            localStorage.removeItem("Ingredients_details");
            $("#ingredientsTableBody").empty();
          }
          if (productBatches) {
            productBatches.forEach(function (batch) {
              $("#expiryDate").text(batch.expiry_date);
            });
          } else {
            $("#expiryDate").text(productDetails.expiryDate);
          }
          if (productBatches && latestProductBatches) {
            localStorage.setItem(
              "productBatches",
              JSON.stringify(productBatches),
            );
            localStorage.setItem(
              "latestProductBatches",
              JSON.stringify(latestProductBatches),
            );
          } else {
            localStorage.removeItem("productBatches");
            localStorage.removeItem("latestProductBatches");
            $("#batchtablebody").empty();
            $("#latestbatchestablebody").empty();
          }
        });
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", status, error);
      },
    });
  }

  //create new batch number against location and product
  function createBatchNymber(lastBatch, locationCode) {
    var productId = localStorage.getItem("productId", productId);

    if (lastBatch) {
      var batch_no = lastBatch.batch_no;
      var parts = batch_no.split("/"); // Split the batch number by '/'
      var lastBatchNumber = parts[parts.length - 1]; // Get the last part which contains the last three digits
      var newBatchNumber = (parseInt(lastBatchNumber, 10) + 1)
        .toString()
        .padStart(3, "0"); // Increment and pad the batch number
    } else {
      var newBatchNumber = "001";
    }
    var newBatchNo = locationCode + "/" + productId + "/" + newBatchNumber; // Display the new batch number
    $("#batchId").text(newBatchNo);
  }

  // load Orders in grid
  function loadOrderItems(OrderDetails) {
    $("#tablebody").empty();
    let totalOrderQuantity = 0; // Initialize total order quantity
    let totalAllottedQuantity = 0; // Initialize total allotted quantity

    OrderDetails.forEach(function (orders) {
      var stockQuantity = safeParseFloat(orders.stock_quantity);
      var orderQuantity = safeParseFloat(orders.order_quantity); // Update to get the current order's quantity
      totalOrderQuantity += orderQuantity; // Add the current order's quantity to the total
      var note = orders.note;
      var infoicon = "";
      var procurementOrderRefNo = orders.procurement_order_ref_no;
      var orderStatus = orders.order_status;

      // Check if the note is not blank, then display the info icon
      if (note && note.trim() !== "") {
        infoicon =
          '<i class="fa fa-info-circle info-note ml-auto mr-2 cursor" data-toggle="modal" data-id="' +
          orders.procurement_orders_id +
          '" data-note="' +
          note +
          '" data-ref="' +
          procurementOrderRefNo +
          '"></i>';
      }
      // var calculatedQty = calculateQuantity(orders, totalOrderQuantity);
      // var calculatedQty = calculateQuantity(orders, totalOrderQuantity,totalAllottedQuantity);

      // var buildQuantity = calculatedQty.build_quantity;
      var isChecked = localStorage.getItem("checkbox_" + orders.itemId);
      var allotQuantityInput = localStorage.getItem(
        "allotQuantity_" + orders.itemId,
      ); // get allot quantity
      var allotQuantity = safeParseFloat(allotQuantityInput);
      totalAllottedQuantity += allotQuantity;
      var calculatedQty = calculateQuantity(
        orders,
        totalOrderQuantity,
        totalAllottedQuantity,
      );
      var buildQuantity = calculatedQty.build_quantity;

      var orderCreationDate = new Date(orders.order_creation_date);

      if (orders.item_status == "Open") {
        var allotQuantity = "";
      } else {
        var allotQuantity = allotQuantityInput
          ? allotQuantityInput
          : calculatedQty.allot_quantity;
      }
      // Display allot value with quantity decimals when it's a number
      var allotDisplay =
        allotQuantity === "" ||
        allotQuantity === null ||
        allotQuantity === undefined
          ? ""
          : quantityDecimal(allotQuantity);
      var requestedQtyDisplay = quantityDecimal(orders.order_quantity);
      var allotStep = quantityInputStep();

      var row = $(
        "<tr class='text-center bg-light-orange'>" +
          "<td class='cen-set' style='width:28%'><p class='circle-set'>" +
          orders.procurement_order_ref_no +
          infoicon +
          "</p></td>" +
          //"<td style='width:25%'>" + orders.order_creation_date + "</td>" +
          "<td style='width:25%' id='orderAge_" +
          orders.itemId +
          "'>" +
          "<span id='orderAgeValue_" +
          orders.itemId +
          "'></span><br>" +
          "<span id='orderAgeFormat_" +
          orders.itemId +
          "'></span>" +
          "</td>" +
          "<td style='width:20%'>" +
          requestedQtyDisplay +
          "</td>" +
          //"<td class='d-flx11'>" + "<input type='number' class='allot-set' name='quantity[]' style='width:20%; text-align:right;' value='" + "' " + ">" +
          "<td class='d-flx11'>" +
          "<input type='number' class='allot-set' name='allotQuantity[]' step='" +
          allotStep +
          "' style='width:35%; text-align:right; color: grey;' value='" +
          allotDisplay +
          "' >" +
          "<span class='ml-3'><input type='checkbox' class='large-checkbox' name='allot[]' id='checkbox_" +
          orders.itemId +
          "' value='" +
          orders.itemId +
          "' " +
          (isChecked ? "checked" : "") +
          "></span></td>" +
          "</tr>",
      );

      if (orderStatus == "Open" || orderStatus == "Completed") {
        row.find(".allot-set").prop("disabled", true);
        row.find(".large-checkbox").prop("disabled", true);
      }
      if (isChecked) {
        row.find(".allot-set").prop("disabled", true);
      }

      $("#tablebody").append(row);
      // Apply global quantity-decimal formatting for headline KPIs
      $("#stockQuantity").text(quantityDecimal(stockQuantity));
      // $('#orderQuantity').text(orderQuantity);
      $("#buildQuantity").text(quantityDecimal(buildQuantity));
      // Update order age and format text every second
      setInterval(function () {
        var formattedOrderAge = calculateOrderAge(orderCreationDate);
        $("#orderAgeValue_" + orders.itemId).text(formattedOrderAge);

        // Update format text
        var formatText =
          productionOrderAgeFormat == 1 ? "DD:HH:MM:SS" : "HH:MM:SS";
        $("#orderAgeFormat_" + orders.itemId).text(formatText);
      }, 1000);

      // validation for input allot qty
      var inputField = row.find("input.allot-set");
      inputField.on("input", function () {
        var inputValue = safeParseFloat($(this).val());
        var stockQuantity = safeParseFloat(orders.stock_quantity);
        var orderQuantity = safeParseFloat(orders.order_quantity);

        // Check if the input value is not empty and is a valid integer
        // if (inputValue !== '' && !Number.isInteger(Number(inputValue))) {
        //     alert('Please enter an integer value.');
        //     var intValue = parseInt(inputValue, 10);
        //     $(this).val(intValue);
        //     return;
        // }
        if (inputValue > stockQuantity) {
          $(this).val(quantityDecimal(stockQuantity));
          alert("Allocation quantity cannot exceed stock quantity.");
          return;
        }
        if (inputValue > orderQuantity) {
          $(this).val(quantityDecimal(orderQuantity));
          alert("Input value cannot exceed order quantity.");
          return;
        }
        if (inputValue < 0) {
          $(this).val(quantityDecimal(0));
          alert("Input value cannot be negative.");
          return;
        }
      });
      // Initial check for the checkbox state and set colour
      if (isChecked) {
        var inputValue = safeParseFloat(row.find("input.allot-set").val());
        var stockQuantity = safeParseFloat(orders.stock_quantity);
        var orderQuantity = safeParseFloat(orders.order_quantity);

        if (inputValue === orderQuantity) {
          row.css(
            "background",
            "linear-gradient(90deg, #00C314, rgba(0, 195, 20, 0))",
          ); // Green

          // } else if (inputValue > orderQuantity || stockQuantity !== 0) {
        } else if (inputValue > orderQuantity) {
          row.css(
            "background",
            "linear-gradient(90deg, #FF0000, rgba(255, 0, 0, 0))",
          ); // Red
        } else if (orderQuantity > inputValue && inputValue !== 0) {
          row.css(
            "background",
            "linear-gradient(90deg, #FFFF00, rgba(255, 255, 0, 0))",
          ); // Yellow
        }
      }
    });
    localStorage.setItem("totalOrderQuantity", totalOrderQuantity);
    setupNotePopups(); // Call function to setup note popups after loading items
    $("#orderQuantity").text(quantityDecimal(totalOrderQuantity));
  }
  // Function to calculate the order age
  function calculateOrderAge(orderCreationDate) {
    var now = new Date(); // Current time
    var differenceMs = now.getTime() - orderCreationDate.getTime();

    // Calculate the difference in hours, minutes, and seconds
    var seconds = Math.floor(differenceMs / 1000);
    var hours = Math.floor(seconds / 3600);
    seconds %= 3600;
    var minutes = Math.floor(seconds / 60);
    seconds %= 60;

    var formattedDifference = "";
    if (productionOrderAgeFormat == 1) {
      // If "shows in days" is selected
      var days = Math.floor(differenceMs / (1000 * 60 * 60 * 24));
      hours = Math.floor(
        (differenceMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60),
      );
      minutes = Math.floor((differenceMs % (1000 * 60 * 60)) / (1000 * 60));
      seconds = Math.floor((differenceMs % (1000 * 60)) / 1000);
      formattedDifference =
        String(days).padStart(2, "0") +
        ":" +
        String(hours).padStart(2, "0") +
        ":" +
        String(minutes).padStart(2, "0") +
        ":" +
        String(seconds).padStart(2, "0");
    } else {
      formattedDifference =
        String(hours).padStart(2, "0") +
        ":" +
        String(minutes).padStart(2, "0") +
        ":" +
        String(seconds).padStart(2, "0");
    }

    return formattedDifference;
  }
  // orderDetails();

  // Helper function to safely parse numbers - returns 0 for NaN, null, or undefined
  function safeParseFloat(value) {
    if (value === null || value === undefined || value === "") {
      return 0;
    }
    var parsed = parseFloat(value);
    return isNaN(parsed) ? 0 : parsed;
  }

  // calculate build quantity, allot quantity
  function calculateQuantity(
    orders,
    totalOrderQuantity,
    totalAllottedQuantity,
  ) {
    // Ensure all values are safe numbers
    var stockQty = safeParseFloat(orders.stock_quantity);
    totalOrderQuantity = safeParseFloat(totalOrderQuantity);
    totalAllottedQuantity = safeParseFloat(totalAllottedQuantity);

    if (stockQty > totalOrderQuantity) {
      var build_quantity = 0;
    } else {
      // var build_quantity = parseFloat(totalOrderQuantity - orders.stock_quantity);
      var stockQuantity = stockQty;
      var remainingOrderQuantity = totalOrderQuantity - totalAllottedQuantity;
      var build_quantity = Math.max(remainingOrderQuantity - stockQuantity, 0);
    }

    if ((orders.order_status = "Locked")) {
      var allot_quantity = Math.min(
        safeParseFloat(orders.order_quantity),
        stockQty,
      );
    } else if (
      orders.item_status == "Completed" ||
      orders.item_status == "partially_completed"
    ) {
      var allot_quantity = safeParseFloat(orders.allot_quantity); // For complete order items
    } else {
      var allot_quantity = "";
    }
    return {
      build_quantity: safeParseFloat(Math.abs(build_quantity)),
      allot_quantity: allot_quantity,
    };
  }

  // If no order details found, clear and reset quantities
  function resetQuantities(productStock) {
    if (productStock === null || productStock === undefined) {
      $("#stockQuantity").text(quantityDecimal(0));
    } else {
      // Handle both object with stock_quantity property and direct number
      var stockQty =
        typeof productStock === "object" && productStock !== null
          ? productStock.stock_quantity
          : productStock;
      $("#stockQuantity").text(quantityDecimal(safeParseFloat(stockQty)));
    }
    $("#orderQuantity").text(quantityDecimal(0));
    $("#buildQuantity").text(quantityDecimal(0));
  }
  // show Note popup
  function setupNotePopups() {
    var infoIcons = document.querySelectorAll(".info-note");

    infoIcons.forEach(function (icon) {
      icon.addEventListener("click", function () {
        var orderId = this.getAttribute("data-id");
        var note = this.getAttribute("data-note");
        var procurementOrderRefNo = this.getAttribute("data-ref");

        // Update modal content with the note data
        var modalTitle = document.querySelector("#exampleModalLongTitle");
        var modalBodyContent = document.querySelector("#modal-body-content");

        modalTitle.innerHTML = `<i class="fa fa-info-circle"></i> Note for Order ID: ${procurementOrderRefNo}`;
        modalBodyContent.innerHTML = `
                    <div class="padding-left-2">
                        <p>Note:</p>
                        <p>${note}</p>
                    </div>
                `;

        $("#note").modal("show"); // Show the modal
      });
    });
  }

  //////////////////////////////////////////////////Batches////////////////////////////////////////////////////////////////////////////////////////////

  // View All Batches
  function loadBatches(productBatches) {
    $("#batchtablebody").empty();
    productBatches.forEach(function (batch) {
      var row = $(
        "<tr class='text-center'>" +
          "<td>" +
          batch.batch_no +
          "</td>" +
          "<td>" +
          batch.product_id +
          "</td>" +
          "<td>" +
          parseFloat(batch.quantity) +
          "</td>" +
          "<td>" +
          batch.unit_name +
          "</td>" +
          "<td>" +
          batch.created_at +
          "</td>" +
          "<td>" +
          batch.expiry_date +
          "</td>" +
          "</tr>",
      );
      $("#batchtablebody").append(row);
    });
  }
  // View Latest Batches
  function loadLatestBatches(latestProductBatches) {
    $("#latestbatchestablebody").empty();
    latestProductBatches.forEach(function (batch) {
      var row = $(
        "<tr class='text-center'>" +
          "<td>" +
          batch.batch_no +
          "</td>" +
          "<td>" +
          batch.product_id +
          "</td>" +
          "<td>" +
          parseFloat(batch.quantity) +
          "</td>" +
          "<td>" +
          batch.unit_name +
          "</td>" +
          "<td>" +
          batch.created_at +
          "</td>" +
          "<td>" +
          batch.expiry_date +
          "</td>" +
          "</tr>",
      );
      $("#latestbatchestablebody").append(row);
    });
  }

  // show batches table
  $("#showTable").on("click", function () {
    var productId = localStorage.getItem("productId", productId);
    var div = document.getElementById("mytable");
    div.style.display = "block";
    var productBatches = JSON.parse(localStorage.getItem("productBatches"));
    var latestProductBatches = JSON.parse(
      localStorage.getItem("latestProductBatches"),
    );

    if (latestProductBatches) {
      latestProductBatches.forEach(function (batch) {
        if (batch.product_id === productId) {
          loadLatestBatches(latestProductBatches); // view latest batches
        } else {
          loadLatestBatches(); // view latest batches
        }
      });
    }
    if (productBatches) {
      productBatches.forEach(function (batch) {
        if (batch.product_id === productId) {
          loadBatches(productBatches); // view all batches
        } else {
          loadBatches(); // view all batches
        }
      });
    }
  });
  // Hide batches table
  $("#hideTable").on("click", function () {
    var div = document.getElementById("mytable");
    div.style.display = "none";
    localStorage.removeItem("productBatches");
    localStorage.removeItem("latestProductBatches");
  });

  // remove leading zero when user types batch quantity and apply decimal formatting
  $(document).on("input", "#allotbatchqty", function () {
    let val = $(this).val();
    let cursorPos = this.selectionStart;
    let originalLength = val.length;

    // Check if decimals are allowed (default to 2 decimals if setting not available)
    let maxDecimals =
      site.settings && site.settings.qty_decimals
        ? site.settings.qty_decimals
        : 2;
    let allowDecimals = maxDecimals > 0;

    console.log(
      "Decimals allowed:",
      allowDecimals,
      "Max decimals:",
      maxDecimals,
      "Current value:",
      val,
    );

    // First, allow all numbers and decimal points
    val = val.replace(/[^0-9.]/g, "");

    // Handle multiple decimal points - keep only the first one
    let parts = val.split(".");
    if (parts.length > 2) {
      val = parts[0] + "." + parts.slice(1).join("");
    }

    // Limit decimal places if we have a decimal point
    if (allowDecimals && parts.length > 1 && parts[1].length > maxDecimals) {
      val = parts[0] + "." + parts[1].substring(0, maxDecimals);
    }

    // Remove leading zeros but allow 0 and 0.x
    if (val.length > 1 && val.startsWith("0") && !val.startsWith("0.")) {
      val = val.replace(/^0+/, "");
    }

    // Ensure we don't end up with empty string
    if (val === "") {
      val = "0";
    }

    console.log("Final value:", val);
    $(this).val(val);

    // Restore cursor position
    let newLength = val.length;
    let newCursorPos = cursorPos + (newLength - originalLength);
    this.setSelectionRange(
      Math.max(0, newCursorPos),
      Math.max(0, newCursorPos),
    );
  });

  // NEW: if the current value is "0", select it so first keystroke replaces it
  $(document).on("focus click", "#allotbatchqty", function () {
    if (this.value === "0") {
      this.select();
    }
  });

  // NEW: if user leaves it empty, put 0 back with proper formatting
  $(document).on("blur", "#allotbatchqty", function () {
    let val = $.trim(this.value);

    if (val === "" || val === ".") {
      // If empty or just decimal point, set to 0 with proper formatting
      if (site.settings.qty_decimals > 0) {
        this.value = "0." + "0".repeat(site.settings.qty_decimals);
      } else {
        this.value = "0";
      }
    } else {
      // Format the value according to decimal settings
      let parsedValue = parseFloat(val);
      if (!isNaN(parsedValue)) {
        if (site.settings.qty_decimals > 0) {
          this.value = parsedValue.toFixed(site.settings.qty_decimals);
        } else {
          this.value = parsedValue.toString();
        }
      } else {
        // Fallback to 0 if parsing fails
        if (site.settings.qty_decimals > 0) {
          this.value = "0." + "0".repeat(site.settings.qty_decimals);
        } else {
          this.value = "0";
        }
      }
    }
  });

  $("#rmConsumptionSubmit").click(function () {
    var actual_yield_quantity = $("#yieldQty").val().trim();
    var actual_sale_quantity = $("#salesQty").val().trim();
    if (actual_yield_quantity === "" && actual_sale_quantity === "") {
      bootbox.alert({
        message: "Please enter Yield Quantity and Sales Quantity",
        size: "small",
      });

      return; // stop submission
    }
    //  Validation for empty fields
    if (actual_yield_quantity === "" || actual_sale_quantity === "") {
      let missingFields = [];

      if (actual_yield_quantity === "") {
        missingFields.push("Yield Quantity");
      }
      if (actual_sale_quantity === "") {
        missingFields.push("Sales Quantity");
      }

      bootbox.alert({
        message: "Please enter: " + missingFields.join(", "),
        size: "small",
      });

      return;
    }
    var $button = $(this);

    $("#rmConsumptionTableBody tr").removeClass("missing-highlight");

    function highlightMissingRows(altGroups, materialIds) {
      if ($("#missingHighlightStyle").length === 0) {
        $("head").append(
          '<style id="missingHighlightStyle"> .missing-highlight { background-color: #ffcccc !important; } </style>',
        );
      }
      (altGroups || []).forEach(function (grp) {
        $("#rmConsumptionTableBody tr[data-alt-group='" + grp + "']").addClass(
          "missing-highlight",
        );
      });
      (materialIds || []).forEach(function (mid) {
        $(
          "#rmConsumptionTableBody tr[data-material-id='" + mid + "']",
        ).addClass("missing-highlight");
      });
    }

    // Prevent multiple clicks
    if ($button.prop("disabled")) {
      return;
    }

    var batchQuantity = $("#allotbatchqty").val();

    // Format the batch quantity according to decimal settings
    var parsedQuantity = parseFloat(batchQuantity);
    if (!isNaN(parsedQuantity)) {
      if (site.settings.qty_decimals > 0) {
        batchQuantity = parsedQuantity.toFixed(site.settings.qty_decimals);
      } else {
        batchQuantity = parsedQuantity.toString();
      }
    } else {
      batchQuantity = "0";
    }
    var productId = localStorage.getItem("productId");
    var manufacturingDate = getCurrentDateTime();

    if (batchQuantity === "" || batchQuantity == 0) {
      alert("Please enter batch quantity.");
      return;
    }

    const selected = JSON.parse(
      localStorage.getItem("selectedIngredientIds") || "[]",
    );
    const ingredientsDetails = JSON.parse(
      localStorage.getItem("Ingredients_details") || "[]",
    );
    const selectedBatchIdsForValidation = JSON.parse(
      localStorage.getItem("selectedBatchIds") || "[]",
    );

    // Build wastage map first
    let wastageMap = {};
    ingredientsDetails.forEach((ing) => {
      const materialId = ing.material_id;
      if (!materialId) return;
      const batchNo = ing.batch_no || "";
      const key = batchNo ? `${materialId}|${batchNo}` : `${materialId}`;
      wastageMap[key] = 0; // default 0 if no input yet
    });

    // Collect wastage values from inputs (if exist)
    var wastageData = [];
    // Build wastage map first
    ingredientsDetails.forEach((ing) => {
      const materialId = ing.material_id;
      if (!materialId) return;
      const batchNo = ing.batch_no || "";
      const key = batchNo ? `${materialId}|${batchNo}` : `${materialId}`;
      wastageMap[key] = 0; // default 0 if no input yet
    });

    // Collect wastage values from inputs
    var wastageData = [];

    $(".wastage-input").each(function () {
      const $input = $(this);
      const row = $input.closest("tr");
      const materialId = row.data("material-id");
      const batchNo = row.data("batch-no") || "";
      const key = batchNo ? `${materialId}|${batchNo}` : `${materialId}`;
      const val = $input.val().trim();
      const floatVal = val === "" || isNaN(val) ? 0 : parseFloat(val);

      wastageData.push({ key, wastage: floatVal });
    });

    // 🔁 Update wastageMap with actual input values
    wastageData.forEach((item) => {
      if (wastageMap.hasOwnProperty(item.key)) {
        wastageMap[item.key] = item.wastage;
      }
    });

    const altGroupsMap = {};
    ingredientsDetails.forEach((ing) => {
      if (String(ing.is_alternative) === "1") {
        const altKey = String(
          ing.primary_product_id ||
            ing.alternative_for ||
            ing.alt_group ||
            ing.material_id,
        );
        if (!altGroupsMap[altKey]) altGroupsMap[altKey] = [];
        altGroupsMap[altKey].push(ing);
      }
    });
    let selectedModified = [];
    selected.map((ele) => selectedModified.push(...ele.split("|")));
    const selectedIdsSet = new Set((selectedModified || []).map(String));
    let missingAltGroup = false;
    let missingAltGroups = [];
    Object.keys(altGroupsMap).forEach((k) => {
      const items = altGroupsMap[k];
      if (items.length > 1) {
        const anySelected = items.some((ing) => {
          const midStr = String(ing.material_id);
          if (selectedIdsSet.has(midStr)) return true;
          return selectedBatchIdsForValidation.some(
            (bk) => bk.startsWith(midStr + "|") || bk === midStr,
          );
        });
        if (!anySelected) {
          missingAltGroup = true;
          missingAltGroups.push(k);
        }
      }
    });
    if (missingAltGroup) {
      highlightMissingRows(missingAltGroups, []);
      // bootbox.alert({
      //     message: 'Ingredient selection missing. Please choose an option to continue.',
      //     size: 'small'
      // });
      // return;
    }

    const materialIdCountsForValidation = ingredientsDetails.reduce(
      (acc, ing) => {
        acc[ing.material_id] = (acc[ing.material_id] || 0) + 1;
        return acc;
      },
      {},
    );
    let missingMandatoryNonAlt = false;
    let missingMandatoryIds = [];
    ingredientsDetails.forEach((ing) => {
      const mid = String(ing.material_id);
      if (
        String(ing.is_alternative) === "0" &&
        (materialIdCountsForValidation[ing.material_id] || 0) > 1
      ) {
        const anyBatchSelected = selectedBatchIdsForValidation.some((bk) =>
          bk.startsWith(mid + "|"),
        );
        if (!anyBatchSelected) {
          missingMandatoryNonAlt = true;
          missingMandatoryIds.push(mid);
        }
      }
    });
    if (missingMandatoryNonAlt) {
      highlightMissingRows([], missingMandatoryIds);
    }

    if (missingAltGroup || missingMandatoryNonAlt) {
      bootbox.alert({
        message:
          "Ingredient selection missing. Please choose an option to continue.",
        size: "small",
      });
      return;
    }

    // Prepare other ingredient lists
    const materialIdCounts = ingredientsDetails.reduce((counts, ing) => {
      counts[ing.material_id] = (counts[ing.material_id] || 0) + 1;
      return counts;
    }, {});

    const compulsoryIds = ingredientsDetails
      .filter(
        (ing) =>
          ing.is_alternative == 0 && materialIdCounts[ing.material_id] === 1,
      )
      .map((ing) => {
        const batch = ing.batch_no || "";
        const expiry = ing.expiry_date || "";
        // return (batch && expiry)
        //     ? `${ing.material_id}|${batch}|${expiry}`
        //     : `${ing.material_id}`;
        // console.log(document.querySelector(`#rmConsumptionTableBody[data-material-id="${ing.material_id}"] .required-qty-input`));
        let compQty = document.querySelector(
          `[data-material-id="${ing.material_id}"] .required-qty-input`,
        ).value;
        return `${ing.material_id}|${batch ? batch : ""}|${expiry ? expiry : ""}|${compQty ? compQty : ""}`;
      });

    let batchSelections = JSON.parse(
      localStorage.getItem("selectedBatchIds") || "[]",
    );
    if (batchSelections.length) {
      let updated = batchSelections.map((batch, idx) => {
        let materialid = batch.split("|")[0];
        console.log("materialid", materialid);
        console.log(
          document.querySelector(
            `[data-material-id="${materialid}"] .required-qty-input`,
          ),
        );
        console.log(
          document.querySelector(
            `[data-material-id="${materialid}"] .required-qty-input`,
          ).value,
        );
        let compQty = document.querySelector(
          `[data-material-id="${materialid}"] .required-qty-input`,
        ).value;
        return batch + "|" + compQty;
      });
      batchSelections = [...updated];
    }

    let selectedWithQty = selected.map((sel) => {
      let parts = sel.split("|");
      let materialid = parts[0];
      let batch = parts[1] || "";
      let expiry = parts[2] || "";
      let compQtyEle = document.querySelector(`[data-material-id="${materialid}"] .required-qty-input`);
      let compQty = compQtyEle ? compQtyEle.value : "";
      return `${materialid}|${batch}|${expiry}|${compQty}`;
    });

    console.log("Compulsory IDs:", compulsoryIds);
    console.log("Selected IDs:", selectedWithQty);
    console.log("Batch Selections:", batchSelections);
    const allIngredients = [...compulsoryIds, ...selectedWithQty, ...batchSelections];
    console.log("All Ingredients to submit:", allIngredients);

    let qtyAlertIngre = [];
    let qtyTextEle = document.querySelectorAll(
      "#rmConsumptionTableBody .qty-text",
    );
    let qtyBadgeEle = document.querySelectorAll(
      "#rmConsumptionTableBody .qty-badge",
    );

    Array.from(qtyTextEle).forEach((ele) => {
      // console.log('ele', ele.textContent.split(' ')[0]);
      let avlQty = Number(ele.textContent.split(" ")[0]);
      let avlQtyUnit = ele.textContent.split(" ")[1];

      let parentTREle = ele.closest("tr");
      // console.log('parentTREle', parentTREle);

      if (!parentTREle.classList.contains("alt-disabled-row")) {
        // check for checkbox
        let inputCheckEle = ele
          .closest(".available-qty-td")
          .querySelector("input");
        if ((inputCheckEle && inputCheckEle.checked) || !inputCheckEle) {
          let resIngreMatId = parentTREle.dataset.materialId;
          let materialReqQty = document.querySelector(
            `[data-material-id="${resIngreMatId}"] .required-qty-input`,
          ).value;

          if (avlQty < materialReqQty) {
            // console.log('ingreNameEle', document.querySelector(`[data-material-id="${resIngreMatId}"] .ingredient_name div`).textContent);
            let ingreNameEle = document.querySelector(
              `[data-material-id="${resIngreMatId}"] .ingredient_name div`,
            ).textContent;

            qtyAlertIngre.push({
              "Ingredient name": ingreNameEle,
              "Available qty": `${String(avlQty) + " " + avlQtyUnit}`,
              "Required qty": `${String(materialReqQty) + " " + avlQtyUnit}`,
            });

            // console.log('qtyAlertIngre', qtyAlertIngre);
          }
        }
      }
    });

    Array.from(qtyBadgeEle).forEach((ele) => {
      // console.log('ele', ele.textContent.split(' ')[0]);
      let avlQty = Number(ele.textContent.split(" ")[0]);
      let avlQtyUnit = ele.textContent.split(" ")[1];

      let parentTREle = ele.closest("tr");
      // console.log('parentTREle', parentTREle);

      if (!parentTREle.classList.contains("alt-disabled-row")) {
        // check for checkbox
        let inputCheckEle = ele
          .closest(".available-qty-td")
          .querySelector("input");
        if ((inputCheckEle && inputCheckEle.checked) || !inputCheckEle) {
          let resIngreMatId = parentTREle.dataset.materialId;
          let materialReqQty = document.querySelector(
            `[data-material-id="${resIngreMatId}"] .required-qty-input`,
          ).value;

          if (avlQty < materialReqQty) {
            // console.log('ingreNameEle', document.querySelector(`[data-material-id="${resIngreMatId}"] .ingredient_name div`).textContent);
            let ingreNameEle = document.querySelector(
              `[data-material-id="${resIngreMatId}"] .ingredient_name div`,
            ).textContent;

            qtyAlertIngre.push({
              "Ingredient name": ingreNameEle,
              "Available qty": `${String(avlQty) + " " + avlQtyUnit}`,
              "Required qty": `${String(materialReqQty) + " " + avlQtyUnit}`,
            });
          }
        }
      }
    });

    if (qtyAlertIngre.length) {
      alert(
        `${qtyAlertIngre.map((ingre) => `Ingredient: ${ingre["Ingredient name"]}, Available: ${ingre["Available qty"]}, Required: ${ingre["Required qty"]}`).join("\n")}\n\nPlease adjust the required quantities or select alternative ingredients.`,
      );
    }
    console.log("qtyAlertIngre", qtyAlertIngre);

    // ✅ Validation: at least one ingredient must be selected
    if (!allIngredients.length) {
      bootbox.alert({
        message:
          "Ingredient selection missing. Please choose an option to continue.",
        size: "small",
      });
      return; // ⛔ stop submit
    }

    // Show confirmation popup
    bootbox.confirm({
      message: "Are you sure you want to submit this data?",
      buttons: {
        confirm: {
          label: "Yes",
          className: "btn-success",
        },
        cancel: {
          label: "No",
          className: "btn-danger",
        },
      },
      callback: function (result) {
        if (result) {
          // User clicked Yes - proceed with submission
          // Disable button and show loading state
          $button.prop("disabled", true);
          var originalText = $button.text();
          $button.text("Processing...");

          // Get the latest wastage calculation data
          var wastageData = window.wastageCalculationData || {};

          // Now send everything in one AJAX GET
          $.ajax({
            url: site.base_url + "Production_Unit/addProductWiseBatches",
            method: "GET",
            data: {
              batchQuantity: batchQuantity,
              productId: productId,
              manufacturingDate: manufacturingDate,
              ingredients: allIngredients.join(","),
              wastageMap: JSON.stringify(wastageMap), // encoded automatically by jQuery
              actual_yield_quantity: $("#yieldQty").val(), // Add actual yield quantity (G)
              actual_sale_quantity: $("#salesQty").val(), // Add actual sale quantity (H)
              total_wastage: $("#wastageW3").val(), // Add total wastage (W) quantity
              production_loss: wastageData.production_loss || 0, // Add W1 (production_loss)
              rounding_loss: wastageData.rounding_loss || 0, // Add W2 (rounding_loss)
              packaging_loss: wastageData.packaging_loss || 0, // Add W3 (packaging_loss)
              wastage_unit: $("#rmBatchUnit").text(), // Add wastage unit
            },
            dataType: "json",
            success: function (response) {
              alert("Batch added successfully.");
              var productBatches = response.productBatches;
              var latestProductBatches = response.latestProductBatches;
              var productStock = response.productStock;

              $("#stockQuantity").text(
                quantityDecimal(
                  safeParseFloat(
                    productStock ? productStock.stock_quantity : 0,
                  ),
                ),
              );
              loadLatestBatches(latestProductBatches);
              loadBatches(productBatches);
              location.reload();
            },
            error: function (xhr, status, error) {
              console.error("AJAX error:", status, error);
              alert(
                "An error occurred while processing the batch. Please try again.",
              );
              // Re-enable button on error
              $button.prop("disabled", false);
              $button.text(originalText);
            },
            complete: function () {
              // This will run after both success and error
              // Note: location.reload() in success will prevent this from being visible
              // but it's good practice to have cleanup
            },
          });
        } else {
          // User clicked No - stay on the same page, do nothing
          return;
        }
      },
    });
  });
  $("#addBatch").on("click", function () {
    var productId = localStorage.getItem("productId");
    var batchQty = $("#allotbatchqty").val(); // current batch quantity
    var optionId = $("#batchVariantRow").is(":visible") && $("#batchProductVariant").val()? $("#batchProductVariant").val(): null;

    if (batchQty === "" || batchQty == 0) {
      bootbox.alert("Please enter batch quantity.");
      return;
    }
    // Single-variant RM from legacy #batchModal (when variant dropdown row is visible)
    if (optionId) {
      openVariantRmConsumption(productId, optionId, batchQty);
      return;
    }

    // Set flag to prevent orderDetails from overriding popup values
    window.popupInitializing = true;

    // Format the batch quantity according to decimal settings
    var parsedQuantity = parseFloat(batchQty);
    if (!isNaN(parsedQuantity)) {
      if (site.settings.qty_decimals > 0) {
        batchQty = parsedQuantity.toFixed(site.settings.qty_decimals);
      } else {
        batchQty = parsedQuantity.toString();
      }
    } else {
      batchQty = "0";
    }

    var Ingredients =
      JSON.parse(localStorage.getItem("Ingredients_details")) || [];
    $("#rmBatchQuantity").text(batchQty); // display batch quantity

    // Get batch unit name from productBatches
    var productBatches = JSON.parse(localStorage.getItem("productBatches"));
    var batchUnitName = "";
    if (productBatches && productBatches.length > 0) {
      batchUnitName = productBatches[0].batch_unit_name || "";
    } else {
      console.log("No productBatches data found!");
    }
    $("#rmBatchUnit").text(batchUnitName); // display batch unit name
    var yield_unit = localStorage.getItem("yield_unit");
    console.log("yield_unit from localStorage:", yield_unit);
    yield_unit = parseInt(yield_unit) || 0;
    console.log("Parsed yield_unit:", yield_unit);

    // Get the batch quantity entered by user to set as build quantity
    var userEnteredBatchQty = parseFloat(batchQty) || 0;

    // Set build quantity to match the user-entered batch quantity
    $("#builds_Quantity").val(userEnteredBatchQty);

    // Calculate and set yield and sales quantities based on yield calculation data
    var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};

    if (yieldData && yieldData.min_batch_qty && yieldData.sales_units) {
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      var salesUnitsStr = yieldData.sales_units || "1";
      var salesUnits = parseFloat(salesUnitsStr.split(" ")[0]) || 1;
      var batchUnitName = yieldData.batch_unit_name || "";

      // Initialize values
      var yieldQty = 0;
      var salesQty = 0;

      if (minBatchQty > 0 && salesUnits > 0) {
        // ON POPUP LOAD: Yield Qty always equals Build Qty
        yieldQty = userEnteredBatchQty;

        // Calculate Sales Qty using cross-multiplication ratio method
        // If minBatchQty produces salesUnits, then yieldQty produces ?
        // salesQty = (yieldQty * salesUnits) / minBatchQty
        salesQty = (yieldQty * salesUnits) / minBatchQty;

        // Round sales quantity to integer as per requirement
        salesQty = Math.floor(salesQty);
      } else {
        yieldQty = userEnteredBatchQty;
        salesQty = userEnteredBatchQty;
      }

      // Set the field values

      $("#yieldQty").val(quantityDecimal(yieldQty));
      $("#salesQty").val(quantityDecimal(salesQty));
    } else {
      // Fallback: set both fields to batch quantity if no yield calculation data

      $("#yieldQty").val(quantityDecimal(userEnteredBatchQty));
      $("#salesQty").val(quantityDecimal(userEnteredBatchQty));
    }
    // ✅ Apply edit control

    if (yield_unit == 1) {
      console.log("Making Yield editable, Sales readonly");
      // Yield editable
      $("#yieldQty")
        .prop("readonly", false)
        .css("background-color", "#fff")
        .css("cursor", "text")
        .removeClass("readonly-input");

      // Sales readonly
      $("#salesQty")
        .prop("readonly", true)
        .css("background-color", "#f5f5f5")
        .css("cursor", "not-allowed")
        .css("color", "#666")
        .addClass("readonly-input");
    } else if (yield_unit == 0) {
      // Both fields editable
      $("#yieldQty")
        .prop("readonly", false)
        .css("background-color", "#fff")
        .css("cursor", "text")
        .removeClass("readonly-input");

      $("#salesQty")
        .prop("readonly", false)
        .css("background-color", "#fff")
        .css("cursor", "text")
        .removeClass("readonly-input");
    } else {
      // Sales editable
      $("#salesQty")
        .prop("readonly", false)
        .css("background-color", "#fff")
        .css("cursor", "text")
        .removeClass("readonly-input");

      // Yield readonly
      $("#yieldQty")
        .prop("readonly", true)
        .css("background-color", "#f5f5f5")
        .css("cursor", "not-allowed")
        .css("color", "#666")
        .addClass("readonly-input");
    }
    if (Ingredients && Ingredients.length) {
      Ingredients.forEach(function (rawMaterial) {
        // only show ingredients for current product
        if (rawMaterial.product_id === productId) {
          // update required_qty based on build quantity and min batch qty
          var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
          var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
          var bQty = parseFloat($("#yieldQty").val()) || parseFloat(batchQty || 1);
          rawMaterial.required_qty = (parseFloat(rawMaterial.quantity_required) * bQty) / minBatchQty;
        }
      });
      loadRMConsumptionTable(Ingredients); // populate modal table
    }

    $("#rmConsumptionModal").modal("show");

    // Calculate initial wastage values when modal loads
    setTimeout(function () {
      calculateWastage();
    }, 500);

    // Clear the flag after modal is shown
    setTimeout(function () {
      window.popupInitializing = false;
      console.log("Popup initialization complete, flag cleared");
    }, 1000); // Extended from 100ms to 1000ms (1 second)
  });

  // Dynamic conversion event handlers
  let isUpdating = false;

  // Function to get current date and time in 'YYYY-MM-DD HH:MM:SS' format

  // When Yield changes
  $("#yieldQty").on("input", function () {
    if (isUpdating) return;
    isUpdating = true;

    // Remove any non-numeric characters (allow only numbers and decimal point)
    var inputValue = $(this).val();
    var numericValue = inputValue.replace(/[^0-9.]/g, "");

    // Prevent multiple decimal points
    var decimalCount = (numericValue.match(/\./g) || []).length;
    if (decimalCount > 1) {
      // Keep only the first decimal point
      var parts = numericValue.split(".");
      numericValue = parts[0] + "." + parts.slice(1).join("");
    }

    // Update the field with cleaned value only if changed
    if (inputValue !== numericValue) {
      var start = this.selectionStart;
      var end = this.selectionEnd;
      $(this).val(numericValue);
      if (this.setSelectionRange) this.setSelectionRange(start, end);
    }

    let yieldVal = parseFloat(numericValue) || 0;

    // Don't allow values <= 0
    if (yieldVal <= 0) {
      $(this).val(0); // Set to 0 instead of clearing
      isUpdating = false;
      return;
    }

    // Reset original_calculated_sales when yield changes
    window.original_calculated_sales = null;

    // Get yield calculation data for conversion
    var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};

    if (yieldData && yieldData.min_batch_qty && yieldData.sales_units) {
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      var salesUnitsStr = yieldData.sales_units || "1";
      var salesUnits = parseFloat(salesUnitsStr.split(" ")[0]) || 1;

      if (minBatchQty > 0 && salesUnits > 0) {
        // Calculate Sales using cross-multiplication ratio method
        // If minBatchQty produces salesUnits, then yieldVal produces ?
        // salesQty = (yieldVal * salesUnits) / minBatchQty
        var calculatedSales = (yieldVal * salesUnits) / minBatchQty;

        // Round sales quantity to integer as per requirement
        calculatedSales = Math.floor(calculatedSales);

        $("#salesQty").val(quantityDecimal(calculatedSales));
      } else {
        $("#salesQty").val(quantityDecimal(yieldVal));
      }
    } else {
      $("#salesQty").val(quantityDecimal(yieldVal));
    }

    isUpdating = false;
  });

  // Sales qty change logic removed as requested

  // Function to calculate wastage value W3 (Main Wastage) with new calculation logic
  function calculateWastage() {
    // Get recipe data from yieldData
    var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};

    // Recipe variables (A, C, Z)
    var min_build_quantity = parseFloat(yieldData.min_batch_qty) || 0; // A
    var expected_sale_count =
      parseFloat(
        yieldData.sales_units ? yieldData.sales_units.split(" ")[0] : 0,
      ) || 1; // C

    // Calculate exact unit volume for internal calculations
    var exact_unit_volume_per_piece =
      min_build_quantity > 0 && expected_sale_count > 0
        ? min_build_quantity / expected_sale_count
        : 0; // Z = A/C (exact value)

    // Calculate rounded unit volume for display and W3 calculation
    var unit_volume_per_piece =
      min_build_quantity > 0 && expected_sale_count > 0
        ? parseFloat((min_build_quantity / expected_sale_count).toFixed(2))
        : 0; // Z = A/C rounded to 2 decimal places

    // Batch variables (D, E, F)
    var rmBatchQuantityText = $("#rmBatchQuantity").text();
    var actual_build_quantity =
      parseFloat(rmBatchQuantityText) || min_build_quantity; // D - actual build quantity from UI
    
    var expected_yield_quantity = actual_build_quantity; // E (same as D)
    var expected_sale_quantity =
      expected_yield_quantity > 0 && exact_unit_volume_per_piece > 0
        ? expected_yield_quantity / exact_unit_volume_per_piece
        : 0; // F = E/Z (using exact unit volume)

    // Screen changes (G, H)
    var actual_yield_quantity = parseFloat($("#yieldQty").val()) || 0; // G
    var actual_sale_quantity = parseFloat($("#salesQty").val()) || 0; // H

    // Calculate expected_sale_from_actual_yield (I)
    var expected_sale_from_actual_yield =
      actual_yield_quantity > 0 && unit_volume_per_piece > 0
        ? actual_yield_quantity / unit_volume_per_piece
        : 0; // I = G/Z (using rounded unit volume)

    // Calculate production_loss (W1)
    var production_loss = actual_build_quantity - actual_yield_quantity; // W1 = E - G
    // Ensure W1 cannot be negative
    if (production_loss <= 0) {
      production_loss = 0;
    }
    
    // Calculate rounding_loss (W2)
    var rounding_loss =
      expected_sale_from_actual_yield -
      Math.floor(expected_sale_from_actual_yield); // W2 = I - INT(I)
    // Round W2 to 2 decimal places
    rounding_loss = parseFloat(rounding_loss.toFixed(2));
    // Ensure W2 cannot be negative
    if (rounding_loss <= 0) {
      rounding_loss = 0;
    }

    // Calculate packaging_loss (W3) - This tracks changes from original calculated sales
    // We need to store the original calculated sales when it changes
    if (!window.original_calculated_sales) {
      window.original_calculated_sales = Math.floor(
        expected_sale_from_actual_yield,
      );
    }

    var packaging_loss =
      (window.original_calculated_sales - actual_sale_quantity) *
      unit_volume_per_piece; // W3 = (H_original - H_changed) * Z
    // Ensure W3 cannot be negative
    if (packaging_loss <= 0) {
      packaging_loss = 0;
    }

    // Calculate total_wastage (W)
    var total_wastage = production_loss + rounding_loss + packaging_loss; // W = W1 + W2 + W3

    // Update the wastage field with calculated value
    var formattedWastage = quantityDecimal(total_wastage);

    $("#wastageW3").val(formattedWastage);

    // Verify the value was set
    var actualValue = $("#wastageW3").val();

    // Set unit for wastage field (always same as Build Qty unit)
    var batchUnit = $("#rmBatchUnit").text() || "";
    if (!batchUnit) {
      // Fallback: get unit from yieldData if rmBatchUnit is empty
      var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
      batchUnit = yieldData.batch_unit_name || "";
      $("#rmBatchUnit").text(batchUnit); // Also set the build unit
    }
    $("#wastageW3Unit").text(batchUnit);

    // Store calculation data for potential database insertion
    window.wastageCalculationData = {
      min_build_quantity: min_build_quantity,
      expected_sale_count: expected_sale_count,
      exact_unit_volume_per_piece: exact_unit_volume_per_piece,
      rounded_unit_volume_per_piece: unit_volume_per_piece,
      actual_build_quantity: actual_build_quantity,
      expected_yield_quantity: expected_yield_quantity,
      expected_sale_quantity: expected_sale_quantity,
      actual_yield_quantity: actual_yield_quantity,
      actual_sale_quantity: actual_sale_quantity,
      expected_sale_from_actual_yield: expected_sale_from_actual_yield,
      production_loss: production_loss, // W1
      rounding_loss: rounding_loss, // W2
      packaging_loss: packaging_loss, // W3
      total_wastage: total_wastage,
    };

    return {
      total_wastage: total_wastage,
      production_loss: production_loss,
      packaging_loss: packaging_loss,
    };
  }

  // Add event listeners for yield and sales quantity changes to recalculate wastage
  $("#yieldQty").on("input", function () {
    setTimeout(function () {
      calculateWastage();
      
      // Dynamic Required Qty calculation for RM Consumption table
      var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      var currentBuildQty = parseFloat($("#yieldQty").val()) || 0;
      
      $(".required-qty-input").each(function() {
        var baseQty = parseFloat($(this).attr("data-base-qty")) || 0;
        var newReqQty = (baseQty * currentBuildQty) / minBatchQty;
        $(this).val(quantityDecimal(newReqQty));
      });
    }, 100); // Small delay to ensure the field value is updated
  });

  $("#salesQty").on("input", function () {
    // Remove any non-numeric characters (allow only numbers and decimal point)
    var inputValue = $(this).val();
    var numericValue = inputValue.replace(/[^0-9.]/g, "");

    // Prevent multiple decimal points
    var decimalCount = (numericValue.match(/\./g) || []).length;
    if (decimalCount > 1) {
      // Keep only the first decimal point
      var parts = numericValue.split(".");
      numericValue = parts[0] + "." + parts.slice(1).join("");
    }

    // Update the field with cleaned value only if changed
    if (inputValue !== numericValue) {
      var start = this.selectionStart;
      var end = this.selectionEnd;
      $(this).val(numericValue);
      if (this.setSelectionRange) this.setSelectionRange(start, end);
    }

    // Prevent negative values
    var currentValue = parseFloat(numericValue) || 0;
    if (currentValue < 0) {
      $(this).val(0); // Set to 0 if negative
      return;
    }

    setTimeout(function () {
      calculateWastage();
    }, 100); // Small delay to ensure the field value is updated
  });

  // Also trigger on change event for manual entries
  $("#salesQty").on("change", function () {
    // Remove any non-numeric characters (allow only numbers and decimal point)
    var inputValue = $(this).val();
    var numericValue = inputValue.replace(/[^0-9.]/g, "");

    // Prevent multiple decimal points
    var decimalCount = (numericValue.match(/\./g) || []).length;
    if (decimalCount > 1) {
      // Keep only the first decimal point
      var parts = numericValue.split(".");
      numericValue = parts[0] + "." + parts.slice(1).join("");
    }

    // Update the field with cleaned value only if changed
    if (inputValue !== numericValue) {
      var start = this.selectionStart;
      var end = this.selectionEnd;
      $(this).val(numericValue);
      if (this.setSelectionRange) this.setSelectionRange(start, end);
    }

    // Prevent negative values
    var currentValue = parseFloat(numericValue) || 0;
    if (currentValue < 0) {
      $(this).val(0); // Set to 0 if negative
      return;
    }

    calculateWastage();
  });

  // Manual trigger function for testing (can be called from browser console)
  window.testWastage = function () {
    console.log("Manual wastage calculation triggered");
    calculateWastage();
  };

  // Also add a function to force set wastage value
  window.setWastage = function (value) {
    console.log("Force setting wastage to:", value);
    var formattedValue = quantityDecimal(value);
    $("#wastageW3").val(formattedValue);
    console.log("Wastage field now shows:", $("#wastageW3").val());
  };

  // Run test function (can be called from browser console)
  // testWastageCalculation();

  // Function to get current date and time in 'YYYY-MM-DD HH:MM:SS' format
  function getCurrentDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
  }

  //////////////////////////////////////////////////current time in Header////////////////////////////////////////////////////////////////////////////////////////////

  // Function to update current time
  function updateTime() {
    var currentTimeElement = document.getElementById("currentTime1");
    var currentTime = new Date();

    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();
    var seconds = currentTime.getSeconds();

    // Formatting time to ensure two digits
    var formattedHours = hours < 10 ? "0" + hours : hours;
    var formattedMinutes = minutes < 10 ? "0" + minutes : minutes;
    var formattedSeconds = seconds < 10 ? "0" + seconds : seconds;

    // Update the #currentTime1 element with the current time
    currentTimeElement.textContent =
      formattedHours + ":" + formattedMinutes + ":" + formattedSeconds;
  }

  // Update time every second
  setInterval(updateTime, 1000);
  updateTime();

  //////////////////////////////////////////////////Update stock and allot algorithm////////////////////////////////////////////////////////////////////////////////////////////

  $("#confirmResetStock").click(function () {
    var productId = localStorage.getItem("productId", productId);
    updateProductionDashboardData(productId);
    $("#reset").modal("hide");
    location.reload(); // Reload the page after click on confirm button
  });

  //update stock
  function updateProductionDashboardData(productId = Null) {
    var selectedProductionUnit = $("#productionUnitName").val(); // Get the selected production unit name
    $.ajax({
      url: "Production_Unit/manager_dashboard",
      type: "GET",
      data: {
        productId: productId,
        productionUnitName: selectedProductionUnit,
      },
      dataType: "json",
      success: function (response) {
        if (response) {
          var products = response;
          // $.each(response, function(index, products) {
          localStorage.setItem("products", JSON.stringify(products));
          productList(products); // Update productList with filtered(location wise) products
          // });
        } else {
          localStorage.removeItem("products");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error updating data:", error);
      },
    });
  }

  //////////////////////////////////////////////////Currently viewing orders for kitchen filter////////////////////////////////////////////////////////////////////////////////////////////

  // Currently viewing orders for kitchen filter
  $(".production-unit-select").on("change", function () {
    updateProductionDashboardData((productId = null));
  });

  ////////////////////////////////////////////////// checkbox check uncheck functionality ////////////////////////////////////////////////////////////////////////////////////////////

  // allot quantity for specific order item
  $("#tablebody").on("click", 'input[type="checkbox"]', function () {
    var isChecked = $(this).is(":checked"); // check checkbox select or not
    orderItemId = $(this).val(); // order item id
    allotQuantityInput = $(this)
      .closest("tr")
      .find('input[name="allotQuantity[]"]')
      .val(); // allot quantity
    // Keep display as per quantity decimals setting (avoid stripping decimals on allot/unallot)
    var allotFormatted =
      allotQuantityInput === "" || allotQuantityInput == null
        ? ""
        : quantityDecimal(allotQuantityInput);
    $(this)
      .closest("tr")
      .find('input[name="allotQuantity[]"]')
      .val(allotFormatted);
    var valueToStore = allotFormatted || allotQuantityInput;
    var productId = localStorage.getItem("productId", productId);

    if (isChecked === true) {
      localStorage.setItem("checkbox_" + orderItemId, isChecked);
      localStorage.setItem("allotQuantity_" + orderItemId, valueToStore); // store allot quantity (formatted)
      var isChecked = "Checked"; // set flag for click on checkbox
      updateOrderItem(orderItemId, isChecked, valueToStore, productId);
    } else {
      localStorage.removeItem("checkbox_" + orderItemId);
      localStorage.removeItem("allotQuantity_" + orderItemId); // remove allot quantity
      var isChecked = "Unchecked"; // set flag for click on checkbox
      updateOrderItem(orderItemId, isChecked, valueToStore, productId);
    }
  });
  // update stock qty and status of order item
  // function updateOrderItem(orderItemId, isChecked, allotQuantityInput, productId) {

  //     $.ajax({
  //         url: site.base_url + "Production_Unit/updateDashboardOrderItemDetails",
  //         method: 'GET',
  //         data: {
  //             orderItemId: orderItemId,
  //             isChecked : isChecked,
  //             allotQuantityInput :allotQuantityInput,
  //             productId : productId
  //         },
  //         dataType: 'json',
  //         success: function (response) {
  //             var OrderDetails = response[0];
  //             if (OrderDetails.OrderDetails && OrderDetails.OrderDetails.length > 0) {
  //                 loadOrderItems(OrderDetails.OrderDetails);
  //             }
  //             var Data = OrderDetails.OrderDetails;
  //             var totalOrderQuantity = localStorage.getItem('totalOrderQuantity'); // total order qty against products
  //             var CommittedOrderItem = false;  //flag for check committed order item

  //             Data.forEach(function (orders) {
  //                 var stockQuantity   = parseFloat(orders.stock_quantity);
  //                 $('#stockQuantity').text(stockQuantity); // show updated stock if stock is reset

  //                 if (orders.item_status === 'Committed') {
  //                     CommittedOrderItem = true;
  //                     AllotQuantity = parseFloat(orders.allot_quantity);
  //                 }

  //             });

  //             // Only show the build quantity if there's at least one committed order
  //             if (CommittedOrderItem) {
  //                 var buildQuantity = totalOrderQuantity - AllotQuantity;
  //                 $('#buildQuantity').text(buildQuantity);
  //             }
  //         },
  //         error: function (xhr, status, error) {
  //             console.error("AJAX error:", status, error);
  //         }
  //     });
  // }
  function updateOrderItem(
    orderItemId,
    isChecked,
    allotQuantityInput,
    productId,
  ) {
    $.ajax({
      url: site.base_url + "Production_Unit/updateDashboardOrderItemDetails",
      method: "GET",
      data: {
        orderItemId: orderItemId,
        isChecked: isChecked,
        allotQuantityInput: allotQuantityInput,
        productId: productId,
      },
      dataType: "json",
      success: function (response) {
        var OrderDetails = response[0];
        if (OrderDetails.OrderDetails && OrderDetails.OrderDetails.length > 0) {
          loadOrderItems(OrderDetails.OrderDetails);
        }
        var Data = OrderDetails.OrderDetails;
        var totalOrderQuantity = safeParseFloat(
          localStorage.getItem("totalOrderQuantity"),
        ); // total order qty against products
        var stockQuantity = 0;
        var totalAllottedQuantity = 0; // Total quantity allotted to all orders
        var buildQuantity = 0;
        var committedOrderFound = false;

        Data.forEach(function (orders) {
          stockQuantity = safeParseFloat(orders.stock_quantity);
          $("#stockQuantity").text(quantityDecimal(stockQuantity)); // Show updated stock (per qty decimals setting)

          if (orders.item_status === "Committed") {
            committedOrderFound = true;
            var allotQuantity = safeParseFloat(orders.allot_quantity);
            totalAllottedQuantity += allotQuantity;
          }
        });

        // Calculate build quantity if there are committed orders
        if (committedOrderFound) {
          // // buildQuantity = stockQuantity - (totalOrderQuantity - totalAllottedQuantity);
          // buildQuantity = (totalOrderQuantity - totalAllottedQuantity) - stockQuantity; //akshu

          // $('#buildQuantity').text(buildQuantity);
          if (stockQuantity > totalOrderQuantity) {
            var remainingOrderQuantity =
              totalOrderQuantity - totalAllottedQuantity;
            var remainingStock = stockQuantity - totalAllottedQuantity;
            buildQuantity = Math.max(
              remainingOrderQuantity - remainingStock,
              0,
            );
            $("#buildQuantity").text(quantityDecimal(buildQuantity));
          } else {
            // var stockQuantity1 = stockQuantity + totalAllottedQuantity;

            // buildQuantity = (totalOrderQuantity - totalAllottedQuantity) - stockQuantity;
            buildQuantity = totalOrderQuantity - stockQuantity;

            $("#buildQuantity").text(quantityDecimal(buildQuantity));
          }
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", status, error);
      },
    });
  }
  function preserveDecimal(x) {
    if (x == null || x === "") return "";
    var s = String(x).trim();
    if (s === "") return "";
    if (s.indexOf(".") === -1) return s;
    s = s.replace(/0+$/, "");
    s = s.replace(/\.$/, "");
    return s;
  }
  /////////////////////////////////// Ingredients //////////////////////////////////////////////////////////////////////

  $("#ingredients").on("click", function (e) {
    var productId = localStorage.getItem("productId", productId);
    var optionId = localStorage.getItem("batchOptionId");
    var buildQty = $("#buildQuantity").text();

    // Variant product: all variants in #variantIngredientsModal only
    if (
      window.managerProductVariants &&
      window.managerProductVariants.length > 0
    ) {
      e.preventDefault();
      e.stopPropagation();
      openVariantIngredientsMulti(productId, buildQty);
      return false;
    }

    orderDetails(productId);

    var div = document.getElementById("ingredientsModal");
    div.style.display = "block";
    var Ingredients = JSON.parse(localStorage.getItem("Ingredients_details"));

    var buildQty = $("#buildQuantity").text();
    $("#builds_Quantity").val(buildQty);

    if (Ingredients) {
      console.log(Ingredients);
      Ingredients.forEach(function (rawMaterial) {
        console.log(rawMaterial.sales_units);
        const minBatchQty = parseFloat(rawMaterial.min_batch_qty);
        const salesUnits = parseFloat(rawMaterial.sales_units);
        const requiredQty =
          parseFloat(rawMaterial.quantity_required) * buildQty;
        const [numberPart, unitPart] = rawMaterial.sales_units.split(" ");
        // calculate selling_unit
        const sellingUnit = (minBatchQty * salesUnits) / buildQty;
        $("#Min_batch_quantity").text(
          preserveDecimal(rawMaterial.min_batch_qty || 0) +
            (rawMaterial.batch_unit_name
              ? " " + rawMaterial.batch_unit_name
              : ""),
        );

        //  const displayValue = isNaN(sellingUnit) ? 0 : sellingUnit.toFixed(2);
        // $('#selling_unit').text(`${displayValue} ${unitPart || ''}`);

        $("#selling_unit").text(rawMaterial.sales_units || "");

        // If product matches, load with updated required qty
        if (rawMaterial.product_id === productId) {
          var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
          var minBatchQtyLocal = parseFloat(yieldData.min_batch_qty) || 1;
          rawMaterial.required_qty =
            (parseFloat(rawMaterial.quantity_required) * parseFloat(buildQty || 0)) / minBatchQtyLocal;
          loadIngredients(Ingredients);
        } else {
          loadIngredients();
        }
      });
    }
  });

  // Handle the modal shown and hidden events to style the input field
  $("#ingredientsModal").on("shown.bs.modal", function () {
    $("#builds_Quantity")
      .css({
        "font-weight": "bold",
        color: "black",
      })
      .focus(); // focus on the input field
  });

  $("#ingredientsModal").on("hidden.bs.modal", function () {
    $("#builds_Quantity").css({
      "font-weight": "normal",
      color: "",
    });
  });

  $(document).on("change", "#rmConsumptionModal .rm-check", function () {
    const batchKey = $(this).val();
    const materialId = $(this).data("material-id");
    const altGroup = $(this).data("alt-group") || materialId;
    let selected =
      JSON.parse(localStorage.getItem("selectedIngredientIds")) || [];

    if (this.checked) {
      // disable other alts and batches (existing code)
      $("#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']")
        .not(this)
        .prop("checked", false)
        .prop("disabled", true);

      $(
        "#rmConsumptionModal .batch-check[data-alt-group='" +
          altGroup +
          "'][data-material-id!='" +
          materialId +
          "']",
      )
        .prop("checked", false)
        .prop("disabled", true);

      $(
        "#rmConsumptionModal .batch-check[data-material-id='" +
          altGroup +
          "']:not([data-alt-group])",
      )
        .prop("checked", false)
        .prop("disabled", true);

      // if (!selected.includes(String(materialId))) selected.push(String(materialId));
      if (!selected.includes(String(batchKey))) selected.push(String(batchKey));
      // 1️ Grey ALL rows in this alternative group
      $("#rmConsumptionModal tr[data-alt-group='" + altGroup + "']").addClass(
        "alt-disabled-row",
      );

      // 2 Remove grey ONLY from selected material's rows
      $(
        "#rmConsumptionModal tr[data-alt-group='" +
          altGroup +
          "'][data-material-id='" +
          materialId +
          "']",
      ).removeClass("alt-disabled-row");
      document.querySelectorAll("#rmConsumptionModal tr").forEach((row) => {
        const alt = row.dataset.altGroup;
        if (alt === String(altGroup)) {
          row.classList.remove("missing-highlight");
        }
      });
    } else {
      // re-enable others (existing code)
      $(
        "#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']",
      ).prop("disabled", false);

      $(
        "#rmConsumptionModal .batch-check[data-alt-group='" +
          altGroup +
          "'][data-material-id!='" +
          materialId +
          "']",
      ).prop("disabled", false);

      $(
        "#rmConsumptionModal .batch-check[data-material-id='" +
          altGroup +
          "']:not([data-alt-group])",
      ).prop("disabled", false);

      // selected = selected.filter(id => id !== String(materialId));
      selected = selected.filter((id) => id !== String(batchKey));

      //  Remove greying when unselected
      $(
        "#rmConsumptionModal tr[data-alt-group='" + altGroup + "']",
      ).removeClass("alt-disabled-row");
      document.querySelectorAll("#rmConsumptionModal tr").forEach((row) => {
        const alt = row.dataset.altGroup;
        if (alt === String(altGroup)) {
          row.classList.remove("missing-highlight");
        }
      });
    }

    localStorage.setItem("selectedIngredientIds", JSON.stringify(selected));
  });
  $(document).on("change", "#rmConsumptionModal .batch-check", function () {
    const batchKey = $(this).val();
    const materialId = $(this).data("material-id");
    const altGroup = $(this).data("alt-group") || materialId;
    let selectedBatchIds =
      JSON.parse(localStorage.getItem("selectedBatchIds")) || [];

    // Allow multiple batch choices for the same material; only toggle storage
    if (this.checked) {
      if (!selectedBatchIds.includes(batchKey)) selectedBatchIds.push(batchKey);
    } else {
      selectedBatchIds = selectedBatchIds.filter((x) => x !== batchKey);
    }

    localStorage.setItem("selectedBatchIds", JSON.stringify(selectedBatchIds));

    if ($(this).data("alt-group")) {
      const anyAltChecked =
        $(
          "#rmConsumptionModal .batch-check[data-alt-group='" +
            altGroup +
            "']:checked",
        ).length > 0;
      if (anyAltChecked) {
        // existing disable code ...
        $("#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']")
          .prop("checked", false)
          .prop("disabled", true);

        $(
          "#rmConsumptionModal .batch-check[data-alt-group='" +
            altGroup +
            "'][data-material-id!='" +
            materialId +
            "']",
        )
          .prop("checked", false)
          .prop("disabled", true);

        $(
          "#rmConsumptionModal .batch-check[data-material-id='" +
            altGroup +
            "']:not([data-alt-group])",
        )
          .prop("checked", false)
          .prop("disabled", true);

        // 1️ Grey ALL rows in this alternative group
        $("#rmConsumptionModal tr[data-alt-group='" + altGroup + "']").addClass(
          "alt-disabled-row",
        );

        // 2️ Remove grey ONLY from selected material's rows
        $(
          "#rmConsumptionModal tr[data-alt-group='" +
            altGroup +
            "'][data-material-id='" +
            materialId +
            "']",
        ).removeClass("alt-disabled-row");
        document.querySelectorAll("#rmConsumptionModal tr").forEach((row) => {
          const alt = row.dataset.altGroup;
          if (alt === String(altGroup)) {
            row.classList.remove("missing-highlight");
          }
        });
      } else {
        // re-enable (existing code)
        $(
          "#rmConsumptionModal .rm-check[data-alt-group='" + altGroup + "']",
        ).prop("disabled", false);
        $(
          "#rmConsumptionModal .batch-check[data-alt-group='" +
            altGroup +
            "'][data-material-id!='" +
            materialId +
            "']",
        ).prop("disabled", false);
        $(
          "#rmConsumptionModal .batch-check[data-material-id='" +
            altGroup +
            "']:not([data-alt-group])",
        ).prop("disabled", false);

        let selected =
          JSON.parse(localStorage.getItem("selectedIngredientIds")) || [];
        selected = selected.filter((id) => id !== String(materialId));
        localStorage.setItem("selectedIngredientIds", JSON.stringify(selected));
        // 👉 Remove grey
        $(
          "#rmConsumptionModal tr[data-alt-group='" + altGroup + "']",
        ).removeClass("alt-disabled-row");
        document.querySelectorAll("#rmConsumptionModal tr").forEach((row) => {
          const alt = row.dataset.altGroup;
          if (alt === String(altGroup)) {
            row.classList.remove("missing-highlight");
          }
        });
      }
    }
  });

  let Ingredients = []; // declare Ingredients globally

  function loadIngredientsTable() {
    $("#ingredientsTableBody").empty();

    const selectedBatchIds =
      JSON.parse(localStorage.getItem("selectedBatchIds")) || [];
    const selectedIds =
      JSON.parse(localStorage.getItem("selectedIngredientIds")) || [];
    const buildQty = parseFloat($("#builds_Quantity").val()) || 1;

    // Build alternative index per primary_product_id
    const altIndexMap = {};
    Ingredients.forEach((item) => {
      if (item.is_alternative == 1 && item.primary_product_id) {
        const key = item.primary_product_id;
        if (!altIndexMap[key]) altIndexMap[key] = [];
        if (!altIndexMap[key].includes(item.material_id))
          altIndexMap[key].push(item.material_id);
      }
    });

    // Precompute required qty and alt group id for each item
    const items = Ingredients.map((it) => {
      const clone = { ...it };
      var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      clone.required_qty = (parseFloat(clone.quantity_required) * buildQty) / minBatchQty;
      clone.__altGroup =
        clone.primary_product_id ||
        clone.alternative_for ||
        clone.alt_group ||
        clone.material_id;
      return clone;
    });

    // Group by alternative group id
    const altGroups = items.reduce((acc, it) => {
      const k = String(it.__altGroup);
      if (!acc[k]) acc[k] = [];
      acc[k].push(it);
      return acc;
    }, {});

    let serial = 1;
    Object.keys(altGroups).forEach((altKey) => {
      const groupItems = altGroups[altKey];
      // Group within altGroup by material_id
      const byMaterial = groupItems.reduce((acc, it) => {
        const m = String(it.material_id);
        if (!acc[m]) acc[m] = [];
        acc[m].push(it);
        return acc;
      }, {});

      const totalRows = Object.values(byMaterial).reduce(
        (sum, arr) => sum + arr.length,
        0,
      );
      let isFirstRowOfAlt = true;
      const sortedPrimAltArr = [];
      Object.keys(byMaterial).forEach((altKey) => {
        let arr = byMaterial[altKey];
        if (arr[0].primary_product_id) {
          sortedPrimAltArr.push(altKey);
        } else {
          sortedPrimAltArr.unshift(altKey);
        }
      });

      // Object.keys(byMaterial).forEach(materialId => {
      sortedPrimAltArr.forEach((materialId) => {
        const rows = byMaterial[materialId];
        const matRowspan = rows.length;

        rows.forEach((rawMaterial, idx) => {
          const batchKey =
            rawMaterial.material_id +
            "|" +
            (rawMaterial.batch_no || "") +
            "|" +
            (rawMaterial.expiry_date || "");
          const isBatchChecked = selectedBatchIds.includes(batchKey)
            ? "checked"
            : "";
          const availableQty = parseFloat(rawMaterial.available_qty) || 0;
          const requiredQty = parseFloat(rawMaterial.required_qty) || 0;

          const displayValue = availableQty + " " + rawMaterial.unit_name;

          // Build checkbox HTML (if needed) - REMOVED since ingredients popup is display-only
          let availableCellCheckboxes = "";
          const altGroup = rawMaterial.__altGroup;
          // No checkboxes for ingredients popup - display only

          // Qty HTML - simplified since no checkboxes
          const qtyHtml =
            availableQty <= 0 || availableQty < requiredQty
              ? "<span class='qty-badge'><span>" +
                availableQty +
                "</span> <span>" +
                rawMaterial.unit_name +
                "</span></span>"
              : "<span class='qty-text'>" + displayValue + "</span>";

          // Row Start
          let tr =
            "<tr class='text-left' data-material-id='" +
            rawMaterial.material_id +
            "'" +
            " data-batch-no='" +
            (rawMaterial.batch_no || "") +
            "'" +
            " data-alt-group='" +
            altGroup +
            "'>";

          // Sr. No. (rowspan for entire alt group)
          if (isFirstRowOfAlt) {
            const srClass = totalRows > 1 ? " merge-border" : "";
            tr +=
              "<td class='" +
              srClass +
              "' rowspan='" +
              totalRows +
              "' style='border-right: 1px solid #ddd;'>" +
              serial +
              "</td>";
            isFirstRowOfAlt = false;
          }

          // Ingredient + Required Qty (merged per material group)
          if (idx === 0) {
            const mergeCls = matRowspan > 1 ? " merge-border" : "";
            let altBadge = "";

            if (
              rawMaterial.is_alternative == 1 &&
              rawMaterial.primary_product_id != null &&
              altIndexMap[rawMaterial.primary_product_id]
            ) {
              const altList = altIndexMap[rawMaterial.primary_product_id];
              const index = altList.indexOf(rawMaterial.material_id) + 1;
              altBadge =
                "<div class='alt-badge'>Alternative " + index + "</div>";
            }

            tr +=
              "<td class='" +
              mergeCls +
              " ingredient_name' rowspan='" +
              matRowspan +
              "'>" +
              altBadge +
              "<div>" +
              rawMaterial.raw_material +
              "</div>" +
              "</td>";

            tr +=
              "<td class='" +
              mergeCls +
              " req_qty_td' rowspan='" +
              matRowspan +
              "'>" +
              "<span class='req-badge'><span>" +
              rawMaterial.required_qty +
              "</span> <span>" +
              rawMaterial.unit_name +
              "</span></span>" +
              "</td>";
          }

          // Available Column - simplified without checkboxes
          tr += "<td class='available-qty-td'>" + qtyHtml + "</td>";

          // Batch / Expiry
          tr +=
            "<td class='batch-qty-td'>" +
            (rawMaterial.batch_no || "") +
            "</td>";

          // Check if expiry date is within 24 hours and add warning icon
          let expiryCell = rawMaterial.expiry_date || "";
          let expiryWarningIcon = "";
          if (rawMaterial.expiry_date) {
            const expiryDate = new Date(rawMaterial.expiry_date);
            const now = new Date();
            const hoursUntilExpiry = (expiryDate - now) / (1000 * 60 * 60);

            if (hoursUntilExpiry <= 24 && hoursUntilExpiry > 0) {
              expiryWarningIcon =
                ' <i class="fa fa-exclamation-triangle" style="color: #ff9800; margin-left: 5px;" title="Expiring within 24 hours"></i>';
            }
          }

          tr +=
            "<td class='expiry_date'>" +
            expiryCell +
            expiryWarningIcon +
            "</td>";

          tr += "</tr>";
          $("#ingredientsTableBody").append($(tr));
        });
      });

      serial += 1;
    });
  }

  function loadRMConsumptionTable(Ingredients) {
    var tableBody = "#rmConsumptionTableBody";
    var batchQtyEl = "#rmBatchQuantity";
    var keyBatch = "selectedBatchIds";
    var keyIngredient = "selectedIngredientIds";
    var clsBatchCheck = "batch-check";
    var clsRmCheck = "rm-check";

    $(tableBody).empty();

    var selectedBatchIds = JSON.parse(localStorage.getItem(keyBatch) || "[]");
    var selectedIds = JSON.parse(localStorage.getItem(keyIngredient) || "[]");
    var batchQty = parseFloat($(batchQtyEl).text()) || 1;
    //  Build alternative index per primary_product_id
    const altIndexMap = {};
    // console.log("ingreeeee", Ingredients);
    Ingredients.forEach((item) => {
      if (item.is_alternative == 1 && item.primary_product_id) {
        const key = item.primary_product_id;
        if (!altIndexMap[key]) altIndexMap[key] = [];
        if (!altIndexMap[key].includes(item.material_id))
          altIndexMap[key].push(item.material_id);
      }
    });
    // console.log('altIndexMap', altIndexMap);
    // Precompute required qty and alt group id for each item
    const items = Ingredients.map((it) => {
      const clone = { ...it };
      var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      var currentBuildQty = parseFloat($("#yieldQty").val()) || batchQty;
      clone.required_qty = (parseFloat(clone.quantity_required) * currentBuildQty) / minBatchQty;
      clone.__altGroup =
        clone.primary_product_id ||
        clone.alternative_for ||
        clone.alt_group ||
        clone.material_id;
      return clone;
    });

    // Group by alternative group id
    const altGroups = items.reduce((acc, it) => {
      const k = String(it.__altGroup);
      if (!acc[k]) acc[k] = [];
      acc[k].push(it);
      return acc;
    }, {});

    // console.log("altGroupsaltGroups", altGroups);
    let serial = 1;
    Object.keys(altGroups).forEach((altKey) => {
      const groupItems = altGroups[altKey];
      // within alt group, further group by material_id to merge ingredient/required cells
      const byMaterial = groupItems.reduce((acc, it) => {
        const m = String(it.material_id);
        if (!acc[m]) acc[m] = [];
        acc[m].push(it);
        return acc;
      }, {});
      // console.log("byMaterial", byMaterial);
      const totalRows = Object.values(byMaterial).reduce(
        (sum, arr) => sum + arr.length,
        0,
      );

      const sortedPrimAltArr = [];
      Object.keys(byMaterial).forEach((altKey) => {
        let arr = byMaterial[altKey];
        if (arr[0].primary_product_id) {
          sortedPrimAltArr.push(altKey);
        } else {
          sortedPrimAltArr.unshift(altKey);
        }
      });
      // console.log("sortedPrimAltArr", sortedPrimAltArr);
      // console.log('tototal rows', totalRows);
      let isFirstRowOfAlt = true;
      // console.log("byMaterial", byMaterial);
      // Object.keys(byMaterial).forEach(materialId => {
      sortedPrimAltArr.forEach((materialId) => {
        const rows = byMaterial[materialId];
        // console.log("rowsrows", rows);
        const matRowspan = rows.length;

        rows.forEach((rawMaterial, idx) => {
          const batchKey =
            rawMaterial.material_id +
            "|" +
            (rawMaterial.batch_no || "") +
            "|" +
            (rawMaterial.expiry_date || "");
          const isBatchChecked = selectedBatchIds.includes(batchKey)
            ? "checked"
            : "";
          const availableQty = parseFloat(rawMaterial.available_qty) || 0;
          const requiredQty = parseFloat(rawMaterial.required_qty) || 0;

          const displayValue = availableQty + " " + rawMaterial.unit_name;
          const qtyHtml =
            availableQty <= 0 || availableQty < requiredQty
              ? "<span class='qty-badge'><span>" +
                availableQty +
                "</span> <span>" +
                rawMaterial.unit_name +
                "</span></span>"
              : "<span class='qty-text'>" + displayValue + "</span>";

          // Available column content
          let availableCellCheckboxes = "";
          const altGroup = rawMaterial.__altGroup;
          if (rows.length > 1) {
            availableCellCheckboxes +=
              "<input type='checkbox' class='" +
              clsBatchCheck +
              " large-checkbox' data-material-id='" +
              rawMaterial.material_id +
              "' data-alt-group='" +
              altGroup +
              "' value='" +
              batchKey +
              "' " +
              isBatchChecked +
              ">";
          }
          if (rawMaterial.is_alternative == 1 && rows.length === 1) {
            const isAltChecked = selectedIds.includes(
              String(rawMaterial.material_id),
            )
              ? "checked"
              : "";
            availableCellCheckboxes +=
              "<input type='checkbox' class='" +
              clsRmCheck +
              " large-checkbox' data-material-id='" +
              rawMaterial.material_id +
              "' data-alt-group='" +
              altGroup +
              "' value='" +
              batchKey +
              "' " +
              isAltChecked +
              ">";
          }

          const disabledAttr = availableQty <= 0 ? " disabled" : "";
          const wastageInputHtml =
            "<div class='wastage-container'>" +
            "<input type='text' class='wastage-input' inputmode='decimal' step='any' min='0' value='0'" +
            disabledAttr +
            " onkeydown='event.stopPropagation();' onfocus='this.select();'>" +
            "<span class='wastage-unit'>" +
            rawMaterial.unit_name +
            "</span>" +
            "</div>";

          let tr =
            "<tr class='text-left' data-material-id='" +
            rawMaterial.material_id +
            "'" +
            " data-batch-no='" +
            (rawMaterial.batch_no || "") +
            "'" +
            " data-alt-group='" +
            altGroup +
            "'>";

          // Sr. No. merged across the entire alt group
          if (isFirstRowOfAlt) {
            const srClass = totalRows > 1 ? " merge-border" : "";
            tr +=
              "<td class='" +
              srClass.trim() +
              "' rowspan='" +
              totalRows +
              "'>" +
              serial +
              "</td>";
            isFirstRowOfAlt = false;
          }

          // Ingredient and Required merged per material group
          if (idx === 0) {
            const mergeCls = matRowspan > 1 ? " merge-border" : "";
            // tr += "<td class='" + mergeCls.trim() + "' rowspan='" + matRowspan + "' class='ingredient_name'>" + rawMaterial.raw_material + "</td>";
            // tr += "<td class='" + mergeCls.trim() + " ingredient_name' rowspan='" + matRowspan + "'>" + rawMaterial.raw_material + "</td>";
            let altBadge = "";

            if (
              rawMaterial.is_alternative == 1 &&
              rawMaterial.primary_product_id != null &&
              altIndexMap[rawMaterial.primary_product_id]
            ) {
              const altList = altIndexMap[rawMaterial.primary_product_id];
              // console.log("altList: ",altList);
              const index = altList.indexOf(rawMaterial.material_id) + 1;
              altBadge =
                "<div class='alt-badge'>Alternative " + index + "</div>";
            }
            tr +=
              "<td class='" +
              mergeCls.trim() +
              " ingredient_name' rowspan='" +
              matRowspan +
              "'>" +
              altBadge +
              "<div>" +
              rawMaterial.raw_material +
              "</div>" +
              "</td>";
            // tr += "<td class='" + mergeCls.trim() + " req_qty_td' rowspan='" + matRowspan + "'><span class='req-badge'><span>" + rawMaterial.required_qty + "</span> <span>" + rawMaterial.unit_name + "</span></span></td>";
            tr +=
              "<td class='" +
              mergeCls.trim() +
              " req_qty_td' rowspan='" +
              matRowspan +
              "'>" +
              "<span class='req-badge'>" +
              "<input type='text' inputmode='decimal' name='required-qty-input' class='required-qty-input' min='0' step='any' onfocus='this.select();' onclick='this.select();' data-base-qty='" + rawMaterial.quantity_required + "' " +
              "value='" +
              rawMaterial.required_qty +
              "' /> " +
              "<span>" +
              rawMaterial.unit_name +
              "</span>" +
              "</span>" +
              "</td>";
          }

          // Available column per row (left-aligned): checkbox first, then quantity (reverted as requested)
          tr +=
            "<td class='available-qty-td'><div class='td-left-flex'>" +
            availableCellCheckboxes +
            qtyHtml +
            "</div></td>";

          tr +=
            "<td class = 'batch-qty-td'>" +
            (rawMaterial.batch_no || "") +
            "</td>";

          // Check if expiry date is within 24 hours and add warning icon
          let expiryCell = rawMaterial.expiry_date || "";
          let expiryWarningIcon = "";
          if (rawMaterial.expiry_date) {
            const expiryDate = new Date(rawMaterial.expiry_date);
            const now = new Date();
            const hoursUntilExpiry = (expiryDate - now) / (1000 * 60 * 60);

            if (hoursUntilExpiry <= 24 && hoursUntilExpiry > 0) {
              expiryWarningIcon =
                ' <i class="fa fa-exclamation-triangle" style="color: #ff9800; margin-left: 5px;" title="Expiring within 24 hours"></i>';
            }
          }

          tr +=
            "<td class='expiry_date'>" +
            expiryCell +
            expiryWarningIcon +
            "</td>";
          tr += "<td class='wastage_td'>" + wastageInputHtml + "</td>";

          tr += "</tr>";
          $(tableBody).append($(tr));
        });
      });

      serial += 1;
    });
  }

  // =============================================================================
  // VARIANT PRODUCTS — Add Batch, Ingredients popup, RM consumption
  // (Non-variant #batchModal / #rmConsumptionModal flows are unchanged above.)
  // =============================================================================
  // 1) Add Batch: #addBatchModal → #variantMultiBatchModal → #variantRmConsumptionModal (multi)
  // 2) Ingredients: #ingredients when batchOptionId set → #variantIngredientsModal
  // 3) Legacy: #batchModal #addBatch + visible #batchVariantRow → single-variant RM popup
  // =============================================================================

  // --- Multi-variant Add Batch (#variantMultiBatchModal) ---
  function syncVariantMultiBatchMetaFields() {
    $("#vmb_batchId").text($("#batchId").text());
    $("#vmb_manufacturingDate").text($("#manufacturingDate").text());
    $("#vmb_productsId").text($("#productsId").text());
    $("#vmb_expiryDate").text($("#expiryDate").text());
    $("#vmb_unit").text($("#unit").text());
    $("#vmb_price").text($("#price").text());
    var minBatchTxt = $("#minBatchQtyText").text().trim();
    if (minBatchTxt) {
      $("#vmb_minBatchQtyText").html(
        "<b>Min batch qty:</b> " +
          minBatchTxt.replace(/^Min batch qty:\s*/i, ""),
      );
    } else {
      $("#vmb_minBatchQtyText").empty();
    }
  }

  function populateVariantMultiBatchGrid() {
    var $tbody = $("#vmb_variantGridBody");
    $tbody.empty();
    var zeroVal = site.settings.qty_decimals > 0 ? (0).toFixed(site.settings.qty_decimals): "0";
    (window.managerProductVariants || []).forEach(function (variant) {
      $tbody.append(
        "<tr class='vmb-variant-row' data-option-id='" +
          variant.id +
          "'>" +
          "<td class='vmb-variant-name'>" +
          (variant.name || "") +
          "</td>" +
          "<td class='text-center'>" +
          "<input type='text' class='form-control vmb-build-qty-input' value='" +
          zeroVal +
          "' min='0' inputmode='decimal' autocomplete='off' />" +
          "</td></tr>",
      );
    });
  }

  function openVariantMultiBatchModal(prodName) {
    syncVariantMultiBatchMetaFields();
    populateVariantMultiBatchGrid();
    $("#variantMultiBatchProductName").text(
      "PRODUCT: " + (prodName || "").toUpperCase(),
    );
    $("#variantMultiBatchModal").modal("show");
  }

  /** Yield/sales from build qty and product yield settings (multi + single variant RM). */
  function calcVariantYieldSales(buildQty, yieldData) {
    var yieldQty = parseFloat(buildQty) || 0;
    var salesQty = yieldQty;
    var salesUnitLabel = "";
    if (yieldData && yieldData.min_batch_qty && yieldData.sales_units) {
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      var parts = (yieldData.sales_units || "1").split(" ");
      var salesUnits = parseFloat(parts[0]) || 1;
      salesUnitLabel = parts.slice(1).join(" ") || "";
      if (minBatchQty > 0 && salesUnits > 0) {
        salesQty = Math.floor((yieldQty * salesUnits) / minBatchQty);
      }
    }
    return {
      yieldQty: yieldQty,
      salesQty: salesQty,
      salesUnitLabel: salesUnitLabel,
    };
  }

  $("#addBatchModal").on("click", function (e) {
    var productId = localStorage.getItem("productId");
    if (!productId) {
      e.preventDefault();
      bootbox.alert("Please select a product from the list first.");
      return;
    }

    // Only variant products: non-variant keeps Bootstrap + #batchModal flow below
    if (window.managerProductVariants.length > 0) {
      e.preventDefault();
      e.stopImmediatePropagation();
      $("#batchModal").modal("hide");

      $("#productsId").text(productId);
      var initialQty = 0;
      if (site.settings.qty_decimals > 0) {
        $("#allotbatchqty").val(
          initialQty.toFixed(site.settings.qty_decimals),
        );
      } else {
        $("#allotbatchqty").val(initialQty);
      }
      $("#manufacturingDate").text(getCurrentDateTime());
      orderDetails(productId);

      window.variantBatchOpenSeq += 1;
      var openSeq = window.variantBatchOpenSeq;
      setTimeout(function () {
        if (openSeq !== window.variantBatchOpenSeq) {
          return;
        }
        if (
          !window.managerProductVariants ||
          !window.managerProductVariants.length
        ) {
          return;
        }
        openVariantMultiBatchModal($("#productName").text());
      }, 350);
      return false;
    }

    $("#productsId").text(productId);
    var initialQty = 0;
    if (site.settings.qty_decimals > 0) {
      $("#allotbatchqty").val(initialQty.toFixed(site.settings.qty_decimals));
    } else {
      $("#allotbatchqty").val(initialQty);
    }
    var currentDateTime = getCurrentDateTime();
    $("#manufacturingDate").text(currentDateTime);

    orderDetails(productId);

    setTimeout(function () {
      var prodName = $("#productName").text();
      $("#batchModalProductName").text("Product: " + prodName);
    }, 200);

    $("#batchModal").modal("show");
  });

  // --- Variant Ingredients popup (#variantIngredientsModal; standard #ingredientsModal unchanged) ---
  var variantPopupIngredients = [];
  window.variantIngredientsMultiMode = false;
  window.variantIngredientsMultiEntries = [];

  $("#variantIngredientsModal").on("shown.bs.modal", function () {
    $("#variant_builds_Quantity")
      .css({ "font-weight": "bold", color: "black" })
      .focus();
  });

  $("#variantIngredientsModal").on("hidden.bs.modal", function () {
    $("#variant_builds_Quantity").css({ "font-weight": "normal", color: "" });
    window.variantIngredientsMultiMode = false;
    window.variantIngredientsMultiEntries = [];
    variantPopupIngredients = [];
  });

  $("#variant_builds_Quantity").on("input change", function () {
    renderVariantIngredientsTable();
  });

  function setupVariantIngredientsModalHeader(productLabel, buildQty, meta) {
    meta = meta || {};
    var first = meta.firstIngredient || {};
    var yieldData = meta.yieldData || {};
    $("#variant_builds_Quantity").val(buildQty);
    $("#variant_Min_batch_quantity").text(
      preserveDecimal(yieldData.min_batch_qty || first.min_batch_qty || 0) +
        (first.batch_unit_name ? " " + first.batch_unit_name : ""),
    );
    $("#variant_selling_unit").text(
      yieldData.sales_units || first.sales_units || "",
    );
    var modalTitle = "INGREDIENTS";
    if (productLabel) {
      modalTitle += " : " + productLabel;
    }
    if (!window.variantIngredientsMultiMode && meta.variantName) {
      modalTitle += " (" + meta.variantName + ")";
    }
    $("#variantIngredientsModalLabel").text(modalTitle);
  }

  /** Render one variant's ingredient rows; serial restarts per variant section in multi mode. */
  function appendVariantIngredientRows(ingredients, buildQty, startSerial) {
    var altIndexMap = {};
    (ingredients || []).forEach(function (item) {
      if (item.is_alternative == 1 && item.primary_product_id) {
        var key = item.primary_product_id;
        if (!altIndexMap[key]) {
          altIndexMap[key] = [];
        }
        if (altIndexMap[key].indexOf(item.material_id) === -1) {
          altIndexMap[key].push(item.material_id);
        }
      }
    });

    var items = (ingredients || []).map(function (it) {
      var clone = $.extend({}, it);
      var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      clone.required_qty = (parseFloat(clone.quantity_required) * buildQty) / minBatchQty;
      clone.__altGroup =
        clone.primary_product_id ||
        clone.alternative_for ||
        clone.alt_group ||
        clone.material_id;
      return clone;
    });

    var altGroups = {};
    items.forEach(function (it) {
      var k = String(it.__altGroup);
      if (!altGroups[k]) {
        altGroups[k] = [];
      }
      altGroups[k].push(it);
    });

    var serial = startSerial || 1;
    Object.keys(altGroups).forEach(function (altKey) {
      var groupItems = altGroups[altKey];
      var byMaterial = {};
      groupItems.forEach(function (it) {
        var m = String(it.material_id);
        if (!byMaterial[m]) {
          byMaterial[m] = [];
        }
        byMaterial[m].push(it);
      });

      var totalRows = 0;
      Object.keys(byMaterial).forEach(function (m) {
        totalRows += byMaterial[m].length;
      });

      var isFirstRowOfAlt = true;
      var sortedPrimAltArr = [];
      Object.keys(byMaterial).forEach(function (matKey) {
        var arr = byMaterial[matKey];
        if (arr[0].primary_product_id) {
          sortedPrimAltArr.push(matKey);
        } else {
          sortedPrimAltArr.unshift(matKey);
        }
      });

      sortedPrimAltArr.forEach(function (materialId) {
        var rows = byMaterial[materialId];
        var matRowspan = rows.length;

        rows.forEach(function (rawMaterial, idx) {
          var availableQty = parseFloat(rawMaterial.available_qty) || 0;
          var requiredQty = parseFloat(rawMaterial.required_qty) || 0;
          var displayValue = availableQty + " " + rawMaterial.unit_name;
          var qtyHtml =
            availableQty <= 0 || availableQty < requiredQty
              ? "<span class='qty-badge'><span>" +
                availableQty +
                "</span> <span>" +
                rawMaterial.unit_name +
                "</span></span>"
              : "<span class='qty-text'>" + displayValue + "</span>";
          var altGroup = rawMaterial.__altGroup;
          var tr =
            "<tr class='text-left' data-material-id='" +
            rawMaterial.material_id +
            "' data-batch-no='" +
            (rawMaterial.batch_no || "") +
            "' data-alt-group='" +
            altGroup +
            "'>";

          if (isFirstRowOfAlt) {
            var srClass = totalRows > 1 ? " merge-border" : "";
            tr +=
              "<td class='" +
              srClass +
              "' rowspan='" +
              totalRows +
              "' style='border-right: 1px solid #ddd;'>" +
              serial +
              "</td>";
            isFirstRowOfAlt = false;
          }

          if (idx === 0) {
            var mergeCls = matRowspan > 1 ? " merge-border" : "";
            var altBadge = "";
            if (
              rawMaterial.is_alternative == 1 &&
              rawMaterial.primary_product_id != null &&
              altIndexMap[rawMaterial.primary_product_id]
            ) {
              var altList = altIndexMap[rawMaterial.primary_product_id];
              var index = altList.indexOf(rawMaterial.material_id) + 1;
              altBadge =
                "<div class='alt-badge'>Alternative " + index + "</div>";
            }
            tr +=
              "<td class='" +
              mergeCls +
              " ingredient_name' rowspan='" +
              matRowspan +
              "'>" +
              altBadge +
              "<div>" +
              rawMaterial.raw_material +
              "</div></td>";
            tr +=
              "<td class='" +
              mergeCls +
              " req_qty_td' rowspan='" +
              matRowspan +
              "'><span class='req-badge'><span>" +
              rawMaterial.required_qty +
              "</span> <span>" +
              rawMaterial.unit_name +
              "</span></span></td>";
          }

          tr += "<td class='available-qty-td'>" + qtyHtml + "</td>";
          tr +=
            "<td class='batch-qty-td'>" + (rawMaterial.batch_no || "") + "</td>";

          var expiryCell = rawMaterial.expiry_date || "";
          var expiryWarningIcon = "";
          if (rawMaterial.expiry_date) {
            var expiryDate = new Date(rawMaterial.expiry_date);
            var now = new Date();
            var hoursUntilExpiry = (expiryDate - now) / (1000 * 60 * 60);
            if (hoursUntilExpiry <= 24 && hoursUntilExpiry > 0) {
              expiryWarningIcon =
                ' <i class="fa fa-exclamation-triangle" style="color: #ff9800; margin-left: 5px;" title="Expiring within 24 hours"></i>';
            }
          }
          tr +=
            "<td class='expiry_date'>" + expiryCell + expiryWarningIcon + "</td>";
          tr += "</tr>";
          $("#variantIngredientsTableBody").append($(tr));
        });
      });
      serial += 1;
    });
  }

  function renderVariantIngredientsTable() {
    $("#variantIngredientsTableBody").empty();
    var buildQty = parseFloat($("#variant_builds_Quantity").val()) || 1;

    if (window.variantIngredientsMultiMode) {
      (window.variantIngredientsMultiEntries || []).forEach(function (entry) {
        $("#variantIngredientsTableBody").append(
          "<tr class='variant-ingredients-section-header'><td colspan='6'>Variant: <strong>" +
            (entry.variantName || "") +
            "</strong></td></tr>",
        );
        appendVariantIngredientRows(entry.ingredients || [], buildQty, 1);
      });
      return;
    }

    appendVariantIngredientRows(variantPopupIngredients, buildQty, 1);
  }

  /** All variants' ingredients in #variantIngredientsModal (read-only; build qty updates req qty). */
  function openVariantIngredientsMulti(productId, buildQty) {
    var variants = window.managerProductVariants || [];
    if (!variants.length) {
      return;
    }

    window.variantIngredientsMultiMode = true;
    window.variantIngredientsMultiEntries = [];
    var productionUnitName = $("#productionUnitName").val() || "";
    var pending = variants.length;

    variants.forEach(function (variant) {
      $.ajax({
        url: site.base_url + "Production_Unit/getVariantBatchRmConsumptionData",
        method: "GET",
        data: {
          productId: productId,
          optionId: variant.id,
          productionUnitName: productionUnitName,
        },
        dataType: "json",
        success: function (response) {
          if (response && response.success) {
            var ingredients = filterVariantIngredientsForOption(
              response.Ingredients || [],
              variant.id,
            );
            if (ingredients.length) {
              window.variantIngredientsMultiEntries.push({
                optionId: variant.id,
                variantName: variant.name || response.variant_name || "",
                ingredients: ingredients,
                yieldData: response.yieldData || {},
                product_name: response.product_name || "",
              });
            }
          }
        },
        complete: function () {
          pending -= 1;
          if (pending > 0) {
            return;
          }
          if (!window.variantIngredientsMultiEntries.length) {
            bootbox.alert("No ingredients found for product variants.");
            window.variantIngredientsMultiMode = false;
            return;
          }

          window.variantIngredientsMultiEntries.sort(function (a, b) {
            return String(a.variantName).localeCompare(String(b.variantName));
          });

          var firstEntry = window.variantIngredientsMultiEntries[0];
          var productLabel =
            firstEntry.product_name || $("#productName").text() || "";
          setupVariantIngredientsModalHeader(productLabel, buildQty, {
            firstIngredient: firstEntry.ingredients[0],
            yieldData: firstEntry.yieldData,
          });
          renderVariantIngredientsTable();
          $("#variantIngredientsModal").modal("show");
        },
      });
    });
  }

  function variantIngredient(productId, optionId, buildQty) {
    var productionUnitName = $("#productionUnitName").val() || "";
    $.ajax({
      url: site.base_url + "Production_Unit/getVariantBatchRmConsumptionData",
      method: "GET",
      data: {
        productId: productId,
        optionId: optionId,
        productionUnitName: productionUnitName,
      },
      dataType: "json",
      success: function (response) {
        if (!response || !response.success) {
          bootbox.alert(
            response && response.message
              ? response.message
              : "Could not load variant ingredients.",
          );
          return;
        }

        var Ingredients = filterVariantIngredientsForOption(
          response.Ingredients || [],
          optionId,
        );
        if (!Ingredients.length) {
          bootbox.alert("No ingredients found for selected variant.");
          return;
        }

        var userBuildQty = parseFloat(buildQty) || 0;
        $.each(Ingredients, function (i, row) {
          if (String(row.product_id) === String(productId)) {
            row.required_qty =
              parseFloat(row.quantity_required) * userBuildQty;
          }
        });

        window.variantIngredientsMultiMode = false;
        variantPopupIngredients = Ingredients;

        setupVariantIngredientsModalHeader(
          response.product_name || $("#productName").text() || "",
          buildQty,
          {
            firstIngredient: Ingredients[0],
            yieldData: response.yieldData || {},
            variantName:
              ($("#batchProductVariant option:selected").text() || "").trim() ||
              response.variant_name ||
              "",
          },
        );
        renderVariantIngredientsTable();
        $("#variantIngredientsModal").modal("show");
      },
      error: function () {
        bootbox.alert("Failed to load variant ingredients.");
      },
    });
  }

  // --- Variant RM consumption (#variantRmConsumptionModal) ---
  // Hide BOM lines where recipe required qty is 0 for the selected variant (e.g. Package 2 for 1kg)
  function filterVariantIngredientsForOption(ingredients, optionId) {
    optionId = parseInt(optionId, 10) || 0;
    var recipeQtyByMaterial = {};

    ingredients.forEach(function (row) {
      var materialId = row.material_id;
      var variantOpt = parseInt(row.variant_option_id, 10) || 0;
      var qty = parseFloat(row.quantity_required) || 0;
      if (variantOpt === optionId) {
        recipeQtyByMaterial[materialId] = qty;
      }
    });
    ingredients.forEach(function (row) {
      var materialId = row.material_id;
      if (recipeQtyByMaterial[materialId] !== undefined) {
        return;
      }
      var variantOpt = parseInt(row.variant_option_id, 10) || 0;
      if (variantOpt === 0) {
        recipeQtyByMaterial[materialId] = parseFloat(row.quantity_required) || 0;
      }
    });

    return ingredients.filter(function (row) {
      return (recipeQtyByMaterial[row.material_id] || 0) > 0;
    });
  }

  function loadVariantRMConsumptionTable(Ingredients, options) {
    options = options || {};
    var tableBody = options.tableBody || "#variantRmConsumptionTableBody";
    var batchQtyEl = "#variantRmBatchQuantity";
    var keyBatch = options.storageKeyBatch || "variantSelectedBatchIds";
    var keyIngredient = options.storageKeyIngredient || "variantSelectedIngredientIds";
    var clsBatchCheck = "variant-batch-check";
    var clsRmCheck = "variant-rm-check";
    var optionIdAttr = options.optionId ? String(options.optionId) : "";
    var batchKeyPrefix = optionIdAttr ? optionIdAttr + "|" : "";
    if (!options.append) {
      $(tableBody).empty();
    }

    var selectedBatchIds = JSON.parse(localStorage.getItem(keyBatch) || "[]");
    var selectedIds = JSON.parse(localStorage.getItem(keyIngredient) || "[]");
    var batchQty =
      options.batchQty !== undefined
        ? parseFloat(options.batchQty) || 0
        : parseFloat($(batchQtyEl).text()) || 1;
    const altIndexMap = {};
    Ingredients.forEach((item) => {
      if (item.is_alternative == 1 && item.primary_product_id) {
        const key = item.primary_product_id;
        if (!altIndexMap[key]) altIndexMap[key] = [];
        if (!altIndexMap[key].includes(item.material_id))
          altIndexMap[key].push(item.material_id);
      }
    });
    const items = Ingredients.map((it) => {
      const clone = { ...it };
      var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      var currentBuildQty = parseFloat($("#yieldQty").val()) || batchQty;
      clone.required_qty = (parseFloat(clone.quantity_required) * currentBuildQty) / minBatchQty;
      clone.__altGroup =
        clone.primary_product_id ||
        clone.alternative_for ||
        clone.alt_group ||
        clone.material_id;
      return clone;
    });

    const altGroups = items.reduce((acc, it) => {
      const k = String(it.__altGroup);
      if (!acc[k]) acc[k] = [];
      acc[k].push(it);
      return acc;
    }, {});

    let serial = options.startSerial || 1;
    Object.keys(altGroups).forEach((altKey) => {
      const groupItems = altGroups[altKey];
      const byMaterial = groupItems.reduce((acc, it) => {
        const m = String(it.material_id);
        if (!acc[m]) acc[m] = [];
        acc[m].push(it);
        return acc;
      }, {});
      const totalRows = Object.values(byMaterial).reduce(
        (sum, arr) => sum + arr.length,
        0,
      );

      const sortedPrimAltArr = [];
      Object.keys(byMaterial).forEach((altKey) => {
        let arr = byMaterial[altKey];
        if (arr[0].primary_product_id) {
          sortedPrimAltArr.push(altKey);
        } else {
          sortedPrimAltArr.unshift(altKey);
        }
      });
      let isFirstRowOfAlt = true;
      sortedPrimAltArr.forEach((materialId) => {
        const rows = byMaterial[materialId];
        const matRowspan = rows.length;

        rows.forEach((rawMaterial, idx) => {
          const batchKey =
            batchKeyPrefix +
            rawMaterial.material_id +
            "|" +
            (rawMaterial.batch_no || "") +
            "|" +
            (rawMaterial.expiry_date || "");
          const isBatchChecked = selectedBatchIds.includes(batchKey)
            ? "checked"
            : "";
          const availableQty = parseFloat(rawMaterial.available_qty) || 0;
          const requiredQty = parseFloat(rawMaterial.required_qty) || 0;

          const displayValue = availableQty + " " + rawMaterial.unit_name;
          const qtyHtml =
            availableQty <= 0 || availableQty < requiredQty
              ? "<span class='qty-badge'><span>" +
                availableQty +
                "</span> <span>" +
                rawMaterial.unit_name +
                "</span></span>"
              : "<span class='qty-text'>" + displayValue + "</span>";

          let availableCellCheckboxes = "";
          const altGroup = rawMaterial.__altGroup;
          if (rows.length > 1) {
            availableCellCheckboxes +=
              "<input type='checkbox' class='" +
              clsBatchCheck +
              " large-checkbox' data-material-id='" +
              rawMaterial.material_id +
              "' data-alt-group='" +
              altGroup +
              "' value='" +
              batchKey +
              "' " +
              isBatchChecked +
              ">";
          }
          if (rawMaterial.is_alternative == 1 && rows.length === 1) {
            const isAltChecked = selectedIds.includes(
              String(rawMaterial.material_id),
            )
              ? "checked"
              : "";
            availableCellCheckboxes +=
              "<input type='checkbox' class='" +
              clsRmCheck +
              " large-checkbox' data-material-id='" +
              rawMaterial.material_id +
              "' data-alt-group='" +
              altGroup +
              "' value='" +
              batchKey +
              "' " +
              isAltChecked +
              ">";
          }

          const disabledAttr = availableQty <= 0 ? " disabled" : "";
          const wastageInputHtml =
            "<div class='wastage-container'>" +
            "<input type='number' class='wastage-input' inputmode='decimal' step='any' min='0' value='0'" +
            disabledAttr +
            " onkeydown='event.stopPropagation();' onfocus='this.select();'>" +
            "<span class='wastage-unit'>" +
            rawMaterial.unit_name +
            "</span>" +
            "</div>";

          let tr =
            "<tr class='text-left' data-material-id='" +
            rawMaterial.material_id +
            "'" +
            " data-batch-no='" +
            (rawMaterial.batch_no || "") +
            "'" +
            " data-alt-group='" +
            altGroup +
            "'" +
            (optionIdAttr
              ? " data-variant-option-id='" + optionIdAttr + "'"
              : "") +
            ">";

          if (isFirstRowOfAlt) {
            const srClass = totalRows > 1 ? " merge-border" : "";
            tr +=
              "<td class='" +
              srClass.trim() +
              "' rowspan='" +
              totalRows +
              "'>" +
              serial +
              "</td>";
            isFirstRowOfAlt = false;
          }

          if (idx === 0) {
            const mergeCls = matRowspan > 1 ? " merge-border" : "";
            let altBadge = "";
            if (
              rawMaterial.is_alternative == 1 &&
              rawMaterial.primary_product_id != null &&
              altIndexMap[rawMaterial.primary_product_id]
            ) {
              const altList = altIndexMap[rawMaterial.primary_product_id];
              const index = altList.indexOf(rawMaterial.material_id) + 1;
              altBadge =
                "<div class='alt-badge'>Alternative " + index + "</div>";
            }
            tr +=
              "<td class='" +
              mergeCls.trim() +
              " ingredient_name' rowspan='" +
              matRowspan +
              "'>" +
              altBadge +
              "<div>" +
              rawMaterial.raw_material +
              "</div>" +
              "</td>";
            tr +=
              "<td class='" +
              mergeCls.trim() +
              " req_qty_td' rowspan='" +
              matRowspan +
              "'>" +
              "<span class='req-badge'>" +
              "<input type='number' name='required-qty-input' class='required-qty-input' min='0' onfocus='this.select();' onclick='this.select();' data-base-qty='" + rawMaterial.quantity_required + "' " +
              (optionIdAttr
                ? " data-original-req='" + rawMaterial.required_qty + "'"
                : "") +
              " value='" +
              rawMaterial.required_qty +
              "' /> " +
              "<span>" +
              rawMaterial.unit_name +
              "</span>" +
              "</span>" +
              "</td>";
          }

          tr +=
            "<td class='available-qty-td'><div class='td-left-flex'>" +
            availableCellCheckboxes +
            qtyHtml +
            "</div></td>";

          tr +=
            "<td class = 'batch-qty-td'>" +
            (rawMaterial.batch_no || "") +
            "</td>";

          let expiryCell = rawMaterial.expiry_date || "";
          let expiryWarningIcon = "";
          if (rawMaterial.expiry_date) {
            const expiryDate = new Date(rawMaterial.expiry_date);
            const now = new Date();
            const hoursUntilExpiry = (expiryDate - now) / (1000 * 60 * 60);

            if (hoursUntilExpiry <= 24 && hoursUntilExpiry > 0) {
              expiryWarningIcon =
                ' <i class="fa fa-exclamation-triangle" style="color: #ff9800; margin-left: 5px;" title="Expiring within 24 hours"></i>';
            }
          }

          tr +=
            "<td class='expiry_date'>" +
            expiryCell +
            expiryWarningIcon +
            "</td>";
          tr += "<td class='wastage_td'>" + wastageInputHtml + "</td>";

          tr += "</tr>";
          $(tableBody).append($(tr));
        });
      });

      serial += 1;
    });
    return serial;
  }

  /** Multi + single variant RM modal title. */
  function setVariantRmConsumptionModalTitle(productName) {
    $("#variantRmConsumptionModalLabel").text(
      "Ingrediants Consumption: " + (productName || ""),
    );
  }

  /** Toggles single-variant qty row vs multi-variant summary grid in #variantRmConsumptionModal. */
  function setVariantRmMultiModeUi(isMulti) {
    window.variantRmMultiMode = !!isMulti;
    if (isMulti) {
      $("#variantRmSingleQtyRow").hide();
      $("#variantRmMultiQtyWrap").show();
      $("#variantRmGridSeparator, #variantRmIngredientsHeading").show();
    } else {
      $("#variantRmSingleQtyRow").show();
      $("#variantRmMultiQtyWrap").hide();
      $("#variantRmGridSeparator, #variantRmIngredientsHeading").hide();
    }
  }

  function renderVariantRmMultiQtyGrid() {
    var $body = $("#variantRmMultiQtyGridBody");
    $body.empty();
    var yield_unit = parseInt(localStorage.getItem("yield_unit"), 10) || 0;
    var unitName = $("#variantRmBatchUnit").text() || $("#unit").text() || "";

    (window.multiVariantRmEntries || []).forEach(function (entry, idx) {
      var yReadonly = yield_unit === 2 ? "readonly" : "";
      var sReadonly = yield_unit === 1 ? "readonly" : "";
      $body.append(
        "<tr data-entry-index='" +
          idx +
          "' data-option-id='" +
          entry.optionId +
          "'>" +
          "<td class='text-left'><strong>" +
          (entry.variantName || "") +
          "</strong></td>" +
          "<td class='text-center'>" +
          quantityDecimal(entry.buildQty) +
          " <span class='unit-text'>" +
          unitName +
          "</span></td>" +
          "<td class='text-center'>" +
          "<input type='number' class='vrm-variant-qty-input vrm-yield-input' data-entry-index='" +
          idx +
          "' value='" +
          quantityDecimal(entry.yieldQty) +
          "' " +
          yReadonly +
          " />" +
          "</td>" +
          "<td class='text-center'>" +
          "<input type='number' class='vrm-variant-qty-input vrm-sales-input' data-entry-index='" +
          idx +
          "' value='" +
          quantityDecimal(entry.salesQty) +
          "' " +
          sReadonly +
          " /> <span class='unit-text'>" +
          (entry.salesUnitLabel || "") +
          "</span></td>" +
          "<td class='text-center'>" +
          "<input type='number' class='vrm-variant-qty-input vrm-wastage-input' data-entry-index='" +
          idx +
          "' value='" +
          quantityDecimal(entry.wastage || 0) +
          "' readonly style='background:#f5f5f5;' />" +
          "</td></tr>",
      );
    });
  }

  function findMultiVariantEntryIndexByOptionId(optionId) {
    var found = -1;
    (window.multiVariantRmEntries || []).forEach(function (entry, i) {
      if (String(entry.optionId) === String(optionId)) {
        found = i;
      }
    });
    return found;
  }

  /**
   * Req qty kam (ingredient) → us variant ki yield/sales qty badh (multi-variant RM popup).
   */
  function syncMultiVariantSalesFromReqQty($input) {
    if (!window.variantRmMultiMode || !$input || !$input.length) {
      return;
    }
    var $tr = $input.closest("tr");
    var optionId = $tr.data("variant-option-id");
    if (!optionId) {
      return;
    }
    var entryIndex = findMultiVariantEntryIndexByOptionId(optionId);
    if (entryIndex < 0) {
      return;
    }
    var entry = window.multiVariantRmEntries[entryIndex];
    if (!entry) {
      return;
    }

    var newReq = parseFloat($input.val()) || 0;
    var originalReq = parseFloat($input.attr("data-original-req"));
    if (isNaN(originalReq) || originalReq <= 0) {
      originalReq = newReq;
      $input.attr("data-original-req", originalReq);
    }
    if (newReq <= 0) {
      return;
    }

    var buildQty = parseFloat(entry.buildQty) || 0;
    var ratio = originalReq / newReq;
    entry.yieldQty = buildQty * ratio;

    var calc = calcVariantYieldSales(entry.yieldQty, entry.yieldData || {});
    entry.salesQty = calc.salesQty;
    entry.original_calculated_sales = null;

    $(".vrm-yield-input[data-entry-index='" + entryIndex + "']").val(
      quantityDecimal(entry.yieldQty),
    );
    $(".vrm-sales-input[data-entry-index='" + entryIndex + "']").val(
      quantityDecimal(entry.salesQty),
    );
    calculateMultiVariantRmWastage(entryIndex);
  }

  function calculateMultiVariantRmWastage(entryIndex) {
    var entry = window.multiVariantRmEntries[entryIndex];
    if (!entry) {
      return;
    }
    var yieldData =
      entry.yieldData ||
      JSON.parse(localStorage.getItem("variantYieldData") || "{}");
    var min_build_quantity = parseFloat(yieldData.min_batch_qty) || 0;
    var expected_sale_count =
      parseFloat(
        yieldData.sales_units ? yieldData.sales_units.split(" ")[0] : 0,
      ) || 1;
    var unit_volume_per_piece =
      min_build_quantity > 0 && expected_sale_count > 0
        ? parseFloat((min_build_quantity / expected_sale_count).toFixed(2))
        : 0;

    entry.yieldQty = parseFloat(entry.yieldQty) || 0;
    entry.salesQty = parseFloat(entry.salesQty) || 0;
    entry.buildQty = parseFloat(entry.buildQty) || 0;

    var production_loss = entry.buildQty - entry.yieldQty;
    if (production_loss < 0) {
      production_loss = 0;
    }
    var expected_sale_from_yield =
      entry.yieldQty > 0 && unit_volume_per_piece > 0
        ? entry.yieldQty / unit_volume_per_piece
        : 0;
    var rounding_loss =
      expected_sale_from_yield - Math.floor(expected_sale_from_yield);
    rounding_loss = parseFloat(rounding_loss.toFixed(2));
    if (rounding_loss < 0) {
      rounding_loss = 0;
    }
    if (!entry.original_calculated_sales) {
      entry.original_calculated_sales = Math.floor(expected_sale_from_yield);
    }
    var packaging_loss =
      (entry.original_calculated_sales - entry.salesQty) * unit_volume_per_piece;
    if (packaging_loss < 0) {
      packaging_loss = 0;
    }
    entry.wastage = production_loss + rounding_loss + packaging_loss;
    entry.wastageData = {
      production_loss: production_loss,
      rounding_loss: rounding_loss,
      packaging_loss: packaging_loss,
      total_wastage: entry.wastage,
    };
    $(".vrm-wastage-input[data-entry-index='" + entryIndex + "']").val(
      quantityDecimal(entry.wastage),
    );
  }

  function loadMultiVariantRMConsumptionTable() {
    clearVariantIngredientStorage();
    localStorage.setItem("variantSelectedBatchIds", "[]");
    localStorage.setItem("variantSelectedIngredientIds", "[]");
    $("#variantRmConsumptionTableBody").empty();
    (window.multiVariantRmEntries || []).forEach(function (entry) {
      if (!entry.ingredients || !entry.ingredients.length) {
        return;
      }
      $("#variantRmConsumptionTableBody").append(
        "<tr class='variant-rm-section-header'><td colspan='7'>Variant: <strong>" +
          (entry.variantName || "") +
          "</strong> &nbsp;|&nbsp; Build Qty: " +
          quantityDecimal(entry.buildQty) +
          "</td></tr>",
      );
      loadVariantRMConsumptionTable(entry.ingredients, {
        append: true,
        batchQty: entry.buildQty,
        optionId: entry.optionId,
        startSerial: 1,
      });
    });
  }

  /** After multi Add Batch: load RM data per variant with qty > 0, then open RM modal. */
  function openMultiVariantRmConsumption(productId, variantBatchRows) {
    window.variantRmMultiMode = true;
    window.multiVariantRmEntries = [];
    clearVariantIngredientStorage();

    var productionUnitName = $("#productionUnitName").val() || "";
    var pending = variantBatchRows.length;
    var hadError = false;

    variantBatchRows.forEach(function (row) {
      $.ajax({
        url: site.base_url + "Production_Unit/getVariantBatchRmConsumptionData",
        method: "GET",
        data: {
          productId: productId,
          optionId: row.optionId,
          productionUnitName: productionUnitName,
        },
        dataType: "json",
        success: function (response) {
          if (response && response.success) {
            var ingredients = filterVariantIngredientsForOption(
              response.Ingredients || [],
              row.optionId,
            );
            if (ingredients.length) {
              var yieldData = response.yieldData || {};
              var calc = calcVariantYieldSales(row.buildQty, yieldData);
              window.multiVariantRmEntries.push({
                optionId: row.optionId,
                variantName: row.variantName,
                buildQty: row.buildQty,
                yieldQty: calc.yieldQty,
                salesQty: calc.salesQty,
                salesUnitLabel: calc.salesUnitLabel,
                yieldData: yieldData,
                ingredients: ingredients,
                product_unit_name: response.product_unit_name || "",
                product_name: response.product_name || "",
                wastage: 0,
                wastageData: {},
                original_calculated_sales: null,
              });
            }
          } else {
            hadError = true;
          }
        },
        error: function () {
          hadError = true;
        },
        complete: function () {
          pending -= 1;
          if (pending > 0) {
            return;
          }
          if (hadError && !window.multiVariantRmEntries.length) {
            bootbox.alert("Could not load variant consumption data.");
            return;
          }
          if (!window.multiVariantRmEntries.length) {
            bootbox.alert(
              "No raw materials with required quantity for entered variant batch quantities.",
            );
            return;
          }

          window.multiVariantRmEntries.sort(function (a, b) {
            return String(a.variantName).localeCompare(String(b.variantName));
          });

          localStorage.setItem(
            "variantYieldData",
            JSON.stringify(window.multiVariantRmEntries[0].yieldData || {}),
          );
          var unitName =
            window.multiVariantRmEntries[0].product_unit_name ||
            $("#unit").text().trim() ||
            "";
          $("#variantRmBatchUnit").text(unitName);
          $("#variantYieldUnit").text(unitName);
          $("#variantWastageW3Unit").text(unitName);

          var productLabel =
            window.multiVariantRmEntries[0].product_name ||
            $("#productName").text() ||
            "";
          setVariantRmConsumptionModalTitle(productLabel);

          setVariantRmMultiModeUi(true);
          renderVariantRmMultiQtyGrid();
          window.multiVariantRmEntries.forEach(function (e, i) {
            calculateMultiVariantRmWastage(i);
          });
          loadMultiVariantRMConsumptionTable();

          $("#variantMultiBatchModal").modal("hide");
          $("#batchModal").modal("hide");
          $("#variantRmConsumptionModal").modal("show");
        },
      });
    });
  }

  $("#variantMultiAddBatch").on("click", function () {
    var productId = localStorage.getItem("productId");
    var variantRows = [];
    $("#vmb_variantGridBody tr").each(function () {
      var optionId = $(this).data("option-id");
      var variantName = $(this).find("td:first").text().trim();
      var buildQty =
        parseFloat($(this).find(".vmb-build-qty-input").val()) || 0;
      if (buildQty > 0) {
        variantRows.push({
          optionId: optionId,
          variantName: variantName,
          buildQty: buildQty,
        });
      }
    });
    if (!variantRows.length) {
      bootbox.alert("Enter batch quantity for at least one variant.");
      return;
    }
    openMultiVariantRmConsumption(productId, variantRows);
  });

  $(document).on(
    "input change",
    "#variantRmConsumptionModal .required-qty-input",
    function () {
      if (!window.variantRmMultiMode) {
        return;
      }
      syncMultiVariantSalesFromReqQty($(this));
    },
  );

  $(document).on(
    "input",
    "#variantRmMultiQtyGridBody .vrm-yield-input, #variantRmMultiQtyGridBody .vrm-sales-input",
    function () {
      var idx = parseInt($(this).data("entry-index"), 10);
      if (isNaN(idx)) {
        return;
      }
      var entry = window.multiVariantRmEntries[idx];
      if (!entry) {
        return;
      }
      if ($(this).hasClass("vrm-yield-input")) {
        entry.yieldQty = parseFloat($(this).val()) || 0;
      } else {
        entry.salesQty = parseFloat($(this).val()) || 0;
      }
      calculateMultiVariantRmWastage(idx);
    },
  );

  /** Clears variant RM checkbox / ingredient localStorage before (re)loading tables. */
  function clearVariantIngredientStorage() {
    localStorage.removeItem("variantSelectedIngredientIds");
    localStorage.removeItem("variantSelectedBatchIds");
    localStorage.removeItem("variantIngredients_details");
  }

  /** On RM modal close without submit: return to multi-batch or standard batch modal. */
  function restoreAddBatchAfterVariantClose() {
    if (window.variantRmSubmitting || window.variantRmRestoringBatch) {
      return;
    }
    window.variantRmRestoringBatch = true;
    window.variant_original_calculated_sales = null;

    $("#variantRmConsumptionModal")
      .removeClass("in")
      .css("display", "none")
      .attr("aria-hidden", "true");
    $("body").removeClass("modal-open").css("padding-right", "");
    $(".modal-backdrop").remove();

    setTimeout(function () {
      var $targetModal = window.variantRmMultiMode
        ? $("#variantMultiBatchModal")
        : $("#batchModal");
      $targetModal
        .one("shown.bs.modal", function () {
          window.variantRmRestoringBatch = false;
        })
        .modal("show");
    }, 150);
  }

  /**
   * Build save payload for one variant: req qty + wastage from that variant's table rows only.
   * Batch rows need a checked box; single-row materials (e.g. packaging) use DOM req qty.
   */
  function buildVariantIngredientSubmitPayload(optionId, entry) {
    var optionIdStr = String(optionId);
    var wastageMap = {};
    var seen = {};
    var ingredientLines = [];
    var selector =
      "#variantRmConsumptionTableBody tr[data-variant-option-id='" +
      optionIdStr +
      "']";

    $(selector).each(function () {
      var $row = $(this);
      if ($row.hasClass("variant-rm-section-header")) {
        return;
      }
      var materialId = $row.data("material-id");
      var batchNo = $row.data("batch-no") || "";
      var key = batchNo ? materialId + "|" + batchNo : String(materialId);
      wastageMap[key] = parseFloat($row.find(".wastage-input").val()) || 0;
    });

    var materialsWithBatchChoice = {};
    $(selector).each(function () {
      var $row = $(this);
      if (
        $row.find(".variant-batch-check, .variant-rm-check").length
      ) {
        materialsWithBatchChoice[$row.data("material-id")] = true;
      }
    });

    function pushIngredientLine($row) {
      var materialId = $row.data("material-id");
      var batchNo = $row.data("batch-no") || "";
      var lineKey =
        materialId + "|" + batchNo + "|" + optionIdStr;
      if (seen[lineKey]) {
        return;
      }
      seen[lineKey] = true;
      var expiry = $row.find(".expiry_date").text().trim();
      var reqQty = $row.find(".required-qty-input").first().val();
      if (reqQty === undefined || reqQty === null || reqQty === "") {
        reqQty = "";
      }
      ingredientLines.push(
        materialId + "|" + batchNo + "|" + expiry + "|" + reqQty,
      );
    }

    $("#variantRmConsumptionModal .variant-batch-check:checked").each(
      function () {
        var $row = $(this).closest("tr");
        if (String($row.data("variant-option-id")) !== optionIdStr) {
          return;
        }
        pushIngredientLine($row);
      },
    );

    $("#variantRmConsumptionModal .variant-rm-check:checked").each(function () {
      var $row = $(this).closest("tr");
      if (String($row.data("variant-option-id")) !== optionIdStr) {
        return;
      }
      pushIngredientLine($row);
    });

    $(selector).each(function () {
      var $row = $(this);
      if ($row.hasClass("variant-rm-section-header")) {
        return;
      }
      var materialId = $row.data("material-id");
      if (materialsWithBatchChoice[materialId]) {
        return;
      }
      if (!$row.find(".required-qty-input").length) {
        return;
      }
      pushIngredientLine($row);
    });

    return {
      ingredients: ingredientLines.join(","),
      wastageMap: wastageMap,
    };
  }

  /** Read latest yield/sales from multi-variant grid before AJAX save. */
  function syncMultiVariantEntryQtyFromGrid() {
    (window.multiVariantRmEntries || []).forEach(function (entry, idx) {
      var $yield = $(".vrm-yield-input[data-entry-index='" + idx + "']");
      var $sales = $(".vrm-sales-input[data-entry-index='" + idx + "']");
      if ($yield.length) {
        entry.yieldQty = parseFloat($yield.val()) || 0;
      }
      if ($sales.length) {
        entry.salesQty = parseFloat($sales.val()) || 0;
      }
    });
  }

  function submitMultiVariantRmBatches($button) {
    syncMultiVariantEntryQtyFromGrid();
    var productId = localStorage.getItem("productId");
    var manufacturingDate = getCurrentDateTime();
    var entries = (window.multiVariantRmEntries || []).filter(function (e) {
      return parseFloat(e.buildQty) > 0;
    });
    var index = 0;
    var unitName = $("#variantRmBatchUnit").text();

    function submitNext() {
      if (index >= entries.length) {
        window.variantRmSubmitting = true;
        alert("All variant batches saved successfully.");
        location.reload();
        return;
      }

      var entry = entries[index];
      var payload = buildVariantIngredientSubmitPayload(entry.optionId, entry);
      if (!payload.ingredients) {
        bootbox.alert(
          "Please select at least one ingredient batch for variant: " +
            entry.variantName,
        );
        $button.prop("disabled", false);
        return;
      }

      var batchQuantity = String(entry.buildQty);
      var parsedQuantity = parseFloat(batchQuantity);
      if (!isNaN(parsedQuantity) && site.settings.qty_decimals > 0) {
        batchQuantity = parsedQuantity.toFixed(site.settings.qty_decimals);
      }

      var wastageData = entry.wastageData || {};

      $.ajax({
        url: site.base_url + "Production_Unit/saveVariantBatchRmConsumption",
        method: "GET",
        data: {
          batchQuantity: batchQuantity,
          productId: productId,
          optionId: entry.optionId,
          manufacturingDate: manufacturingDate,
          ingredients: payload.ingredients,
          wastageMap: JSON.stringify(payload.wastageMap),
          actual_yield_quantity: entry.yieldQty,
          actual_sale_quantity: entry.salesQty,
          total_wastage: entry.wastage || 0,
          production_loss: wastageData.production_loss || 0,
          rounding_loss: wastageData.rounding_loss || 0,
          packaging_loss: wastageData.packaging_loss || 0,
          wastage_unit: unitName,
        },
        dataType: "json",
        success: function (response) {
          if (response && response.success) {
            index += 1;
            submitNext();
          } else {
            bootbox.alert(
              (response && response.message) ||
                "Failed to save batch for " + entry.variantName,
            );
            $button.prop("disabled", false);
          }
        },
        error: function () {
          bootbox.alert("Error saving batch for " + entry.variantName);
          $button.prop("disabled", false);
        },
      });
    }

    submitNext();
  }

  /** Single-variant RM from legacy #batchModal (one optionId, one batch qty). */
  function openVariantRmConsumption(productId, optionId, batchQty) {
    window.variantRmMultiMode = false;
    setVariantRmMultiModeUi(false);
    clearVariantIngredientStorage();
    window.variant_original_calculated_sales = null;
    localStorage.setItem("variantBatchOptionId", optionId);

    var productionUnitName = $("#productionUnitName").val() || "";
    $.ajax({
      url: site.base_url + "Production_Unit/getVariantBatchRmConsumptionData",
      method: "GET",
      data: {
        productId: productId,
        optionId: optionId,
        productionUnitName: productionUnitName,
      },
      dataType: "json",
      success: function (response) {
        if (!response || !response.success) {
          bootbox.alert(
            response && response.message
              ? response.message
              : "Could not load variant raw materials.",
            function () {
              restoreAddBatchAfterVariantClose();
            },
          );
          return;
        }

        var Ingredients = filterVariantIngredientsForOption(
          response.Ingredients || [],
          optionId,
        );
        if (!Ingredients.length) {
          bootbox.alert(
            "No raw materials with required quantity for this variant.",
            function () {
              restoreAddBatchAfterVariantClose();
            },
          );
          return;
        }

        localStorage.setItem(
          "variantIngredients_details",
          JSON.stringify(Ingredients),
        );
        if (response.yieldData) {
          localStorage.setItem(
            "variantYieldData",
            JSON.stringify(response.yieldData),
          );
        }

        // Build / yield / sales (same logic as standard RM popup)
        var userEnteredBatchQty = parseFloat(batchQty) || 0;
        $("#variantRmBatchQuantity").text(quantityDecimal(userEnteredBatchQty));

        var productUnitName =
          response.product_unit_name ||
          $("#unit").text().trim() ||
          "";
        $("#variantRmBatchUnit").text(productUnitName);
        $("#variantYieldUnit").text(productUnitName);
        $("#variantWastageW3Unit").text(productUnitName);

        var yieldData = response.yieldData || {};
        if (yieldData.min_batch_qty && yieldData.sales_units) {
          var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
          var parts = (yieldData.sales_units || "1").split(" ");
          var salesUnits = parseFloat(parts[0]) || 1;
          var yieldQty = userEnteredBatchQty;
          var salesQty =
            minBatchQty > 0 && salesUnits > 0
              ? Math.floor((yieldQty * salesUnits) / minBatchQty)
              : userEnteredBatchQty;
          $("#variantYieldQty").val(quantityDecimal(yieldQty));
          $("#variantSalesQty").val(quantityDecimal(salesQty));
          $("#variantSalesUnit").text(parts.slice(1).join(" ") || "");
        } else {
          $("#variantYieldQty").val(quantityDecimal(userEnteredBatchQty));
          $("#variantSalesQty").val(quantityDecimal(userEnteredBatchQty));
        }

        var yield_unit = parseInt(localStorage.getItem("yield_unit"), 10) || 0;
        if (yield_unit == 1) {
          $("#variantYieldQty").prop("readonly", false);
          $("#variantSalesQty").prop("readonly", true);
        } else if (yield_unit == 0) {
          $("#variantYieldQty,#variantSalesQty").prop("readonly", false);
        } else {
          $("#variantSalesQty").prop("readonly", false);
          $("#variantYieldQty").prop("readonly", true);
        }

        $.each(Ingredients, function (i, row) {
          if (String(row.product_id) === String(productId)) {
            var minBatchQtyLocal = yieldData && yieldData.min_batch_qty ? parseFloat(yieldData.min_batch_qty) : 1;
            row.required_qty =
              (parseFloat(row.quantity_required) * userEnteredBatchQty) / minBatchQtyLocal;
          }
        });

        var productLabel =
          response.product_name || $("#productName").text() || "";
        setVariantRmConsumptionModalTitle(productLabel);

        loadVariantRMConsumptionTable(Ingredients);
        $("#batchModal").modal("hide");
        $("#variantRmConsumptionModal").modal("show");
        setTimeout(calculateVariantRmWastage, 400);
      },
      error: function () {
        bootbox.alert("Failed to load variant consumption data.", function () {
          restoreAddBatchAfterVariantClose();
        });
      },
    });
  }

  function calculateVariantRmWastage() {
    var yieldData = JSON.parse(localStorage.getItem("variantYieldData") || "{}");
    var min_build_quantity = parseFloat(yieldData.min_batch_qty) || 0;
    var expected_sale_count =
      parseFloat(
        yieldData.sales_units ? yieldData.sales_units.split(" ")[0] : 0,
      ) || 1;
    var unit_volume_per_piece =
      min_build_quantity > 0 && expected_sale_count > 0
        ? parseFloat((min_build_quantity / expected_sale_count).toFixed(2))
        : 0;

    var actual_build_quantity =
      parseFloat($("#variantRmBatchQuantity").text()) || min_build_quantity;
    var actual_yield_quantity = parseFloat($("#variantYieldQty").val()) || 0;
    var actual_sale_quantity = parseFloat($("#variantSalesQty").val()) || 0;
    var expected_sale_from_actual_yield =
      actual_yield_quantity > 0 && unit_volume_per_piece > 0
        ? actual_yield_quantity / unit_volume_per_piece
        : 0;

    var production_loss = actual_build_quantity - actual_yield_quantity;
    if (production_loss <= 0) {
      production_loss = 0;
    }
    var rounding_loss =
      expected_sale_from_actual_yield -
      Math.floor(expected_sale_from_actual_yield);
    rounding_loss = parseFloat(rounding_loss.toFixed(2));
    if (rounding_loss <= 0) {
      rounding_loss = 0;
    }
    if (!window.variant_original_calculated_sales) {
      window.variant_original_calculated_sales = Math.floor(
        expected_sale_from_actual_yield,
      );
    }
    var packaging_loss =
      (window.variant_original_calculated_sales - actual_sale_quantity) *
      unit_volume_per_piece;
    if (packaging_loss <= 0) {
      packaging_loss = 0;
    }
    var total_wastage = production_loss + rounding_loss + packaging_loss;
    $("#variantWastageW3").val(quantityDecimal(total_wastage));

    window.variantWastageCalculationData = {
      production_loss: production_loss,
      rounding_loss: rounding_loss,
      packaging_loss: packaging_loss,
      total_wastage: total_wastage,
    };
  }

  $("#variantRmConsumptionModal").on("hidden.bs.modal", function () {
    restoreAddBatchAfterVariantClose();
  });

  $("#variantYieldQty, #variantSalesQty").on("input", function () {
    calculateVariantRmWastage();
    if ($(this).attr("id") === "variantYieldQty") {
      var yieldData = JSON.parse(localStorage.getItem("variantYieldData")) || {};
      var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
      var currentBuildQty = parseFloat($("#variantYieldQty").val()) || 0;
      $("#variantRmConsumptionModal .required-qty-input").each(function() {
        var baseQty = parseFloat($(this).attr("data-base-qty")) || 0;
        var newReqQty = (baseQty * currentBuildQty) / minBatchQty;
        $(this).val(quantityDecimal(newReqQty));
      });
    }
  });

  $(document).on("change","#variantRmConsumptionModal .variant-rm-check",function () {
      var batchKey = $(this).val();
      var materialId = $(this).data("material-id");
      var altGroup = $(this).data("alt-group") || materialId;
      var selected = JSON.parse(
        localStorage.getItem("variantSelectedIngredientIds") || "[]",
      );

      if (this.checked) {
        $(
          "#variantRmConsumptionModal .variant-rm-check[data-alt-group='" +
            altGroup +
            "']",
        )
          .not(this)
          .prop("checked", false)
          .prop("disabled", true);
        if (selected.indexOf(String(batchKey)) === -1) {
          selected.push(String(batchKey));
        }
        $("#variantRmConsumptionModal tr[data-alt-group='" + altGroup + "']")
          .addClass("alt-disabled-row")
          .filter("[data-material-id='" + materialId + "']")
          .removeClass("alt-disabled-row");
      } else {
        $(
          "#variantRmConsumptionModal .variant-rm-check[data-alt-group='" +
            altGroup +
            "']",
        ).prop("disabled", false);
        selected = selected.filter(function (id) {
          return id !== String(batchKey);
        });
        $("#variantRmConsumptionModal tr[data-alt-group='" + altGroup + "']")
          .removeClass("alt-disabled-row");
      }
      localStorage.setItem(
        "variantSelectedIngredientIds",
        JSON.stringify(selected),
      );
    },
  );

  $(document).on("change","#variantRmConsumptionModal .variant-batch-check",function () {
      var batchKey = $(this).val();
      var selected = JSON.parse(
        localStorage.getItem("variantSelectedBatchIds") || "[]",
      );
      if (this.checked) {
        if (selected.indexOf(batchKey) === -1) {
          selected.push(batchKey);
        }
      } else {
        selected = selected.filter(function (k) {
          return k !== batchKey;
        });
      }
      localStorage.setItem("variantSelectedBatchIds", JSON.stringify(selected));
    },
  );

  $("#variantRmConsumptionSubmit").on("click", function () {
    var $button = $(this);
    if ($button.prop("disabled")) {
      return;
    }

    if (window.variantRmMultiMode) {
      var invalidEntry = (window.multiVariantRmEntries || []).find(function (e) {
        return (
          parseFloat(e.buildQty) > 0 &&
          (e.yieldQty === "" ||
            e.yieldQty === null ||
            isNaN(parseFloat(e.yieldQty)) ||
            e.salesQty === "" ||
            e.salesQty === null ||
            isNaN(parseFloat(e.salesQty)))
        );
      });
      if (invalidEntry) {
        bootbox.alert(
          "Please enter Yield and Sales quantity for variant: " +
            invalidEntry.variantName,
        );
        return;
      }
      bootbox.confirm({
        message: "Save batches for all variants with entered quantities?",
        buttons: {
          confirm: { label: "Yes", className: "btn-success" },
          cancel: { label: "No", className: "btn-danger" },
        },
        callback: function (result) {
          if (!result) {
            return;
          }
          $button.prop("disabled", true);
          submitMultiVariantRmBatches($button);
        },
      });
      return;
    }

    var yieldVal = $("#variantYieldQty").val().trim();
    var salesVal = $("#variantSalesQty").val().trim();
    if (yieldVal === "" || salesVal === "") {
      bootbox.alert("Please enter Yield Quantity and Sales Quantity.");
      return;
    }

    var batchQuantity = $("#allotbatchqty").val();
    var parsedQuantity = parseFloat(batchQuantity);
    if (!isNaN(parsedQuantity)) {
      if (site.settings.qty_decimals > 0) {
        batchQuantity = parsedQuantity.toFixed(site.settings.qty_decimals);
      } else {
        batchQuantity = parsedQuantity.toString();
      }
    }

    var productId = localStorage.getItem("productId");
    var optionId = localStorage.getItem("variantBatchOptionId");
    var manufacturingDate = getCurrentDateTime();
    var Ingredients_details = JSON.parse(
      localStorage.getItem("variantIngredients_details") || "[]",
    );

    var wastageMap = {};
    Ingredients_details.forEach(function (ing) {
      if (!ing.material_id) {
        return;
      }
      var key = ing.batch_no
        ? ing.material_id + "|" + ing.batch_no
        : String(ing.material_id);
      wastageMap[key] = 0;
    });

    $("#variantRmConsumptionModal .wastage-input").each(function () {
      var row = $(this).closest("tr");
      var materialId = row.data("material-id");
      var batchNo = row.data("batch-no") || "";
      var key = batchNo ? materialId + "|" + batchNo : String(materialId);
      wastageMap[key] = parseFloat($(this).val()) || 0;
    });

    var selected = JSON.parse(
      localStorage.getItem("variantSelectedIngredientIds") || "[]",
    );
    var compulsoryIds = [];
    var batchSelections = [];

    $("#variantRmConsumptionModal .variant-batch-check:checked").each(
      function () {
        var row = $(this).closest("tr");
        var materialId = $(this).data("material-id");
        var batchNo = row.data("batch-no") || "";
        var expiry = row.find(".expiry_date").text().trim();
        var reqQty = row.find(".required-qty-input").first().val() || "";
        batchSelections.push(
          materialId + "|" + batchNo + "|" + expiry + "|" + reqQty,
        );
      },
    );

    $("#variantRmConsumptionModal .variant-rm-check:checked").each(function () {
      var row = $(this).closest("tr");
      var materialId = $(this).data("material-id");
      var batchNo = row.data("batch-no") || "";
      var expiry = row.find(".expiry_date").text().trim();
      var reqQty = row.find(".required-qty-input").first().val() || "";
      batchSelections.push(
        materialId + "|" + batchNo + "|" + expiry + "|" + reqQty,
      );
    });

    Ingredients_details.forEach(function (ing) {
      if (ing.is_alternative != 1 && ing.available_qty > 0) {
        compulsoryIds.push(
          ing.material_id +
            "|" +
            (ing.batch_no || "") +
            "|" +
            (ing.expiry_date || "") +
            "|" +
            (ing.required_qty || ""),
        );
      }
    });

    var allIngredients = compulsoryIds.concat(selected).concat(batchSelections);
    if (!allIngredients.length) {
      bootbox.alert("Please select at least one ingredient batch.");
      return;
    }

    var wastageData = window.variantWastageCalculationData || {};

    bootbox.confirm({
      message: "Are you sure you want to submit this data?",
      buttons: {
        confirm: { label: "Yes", className: "btn-success" },
        cancel: { label: "No", className: "btn-danger" },
      },
      callback: function (result) {
        if (!result) {
          return;
        }
        $button.prop("disabled", true);
        $.ajax({
          url: site.base_url + "Production_Unit/saveVariantBatchRmConsumption",
          method: "GET",
          data: {
            batchQuantity: batchQuantity,
            productId: productId,
            optionId: optionId,
            manufacturingDate: manufacturingDate,
            ingredients: allIngredients.join(","),
            wastageMap: JSON.stringify(wastageMap),
            actual_yield_quantity: $("#variantYieldQty").val(),
            actual_sale_quantity: $("#variantSalesQty").val(),
            total_wastage: $("#variantWastageW3").val(),
            production_loss: wastageData.production_loss || 0,
            rounding_loss: wastageData.rounding_loss || 0,
            packaging_loss: wastageData.packaging_loss || 0,
            wastage_unit: $("#variantRmBatchUnit").text(),
          },
          dataType: "json",
          success: function (response) {
            if (response && response.success) {
              window.variantRmSubmitting = true;
              alert("Batch added successfully.");
              location.reload();
            } else {
              alert(
                response && response.message
                  ? response.message
                  : "Failed to save batch.",
              );
              $button.prop("disabled", false);
            }
          },
          error: function () {
            alert("An error occurred while processing the batch.");
            $button.prop("disabled", false);
          },
        });
      },
    });
  });

  // ✅ Ensure typing always works
  $(document).on("focus click keydown", ".wastage-input", function (e) {
    e.stopPropagation();
    e.stopImmediatePropagation();
  });

  // initial load — pass your Ingredients here
  function loadIngredients(initialIngredients) {
    Ingredients = initialIngredients; // store globally
    loadIngredientsTable(); // render first time
  }

  // listen for buildQty change
  $("#builds_Quantity").on("input", function () {
    loadIngredientsTable(); // just reload with updated buildQty
  });

  $(document).on("input", ".required-qty-input", function () {
    var value = $(this).val();
    // Allow digits and decimal point
    var cleanValue = value.replace(/[^0-9.]/g, "");
    // Prevent multiple decimal points
    var parts = cleanValue.split(".");
    if (parts.length > 2) {
      cleanValue = parts[0] + "." + parts.slice(1).join("");
    }
    // Remove leading zeros but allow 0 and 0.x
    if (cleanValue.length > 1 && cleanValue.startsWith("0") && !cleanValue.startsWith("0.")) {
      cleanValue = cleanValue.replace(/^0+/, "");
    }
    // Only update if value has changed to prevent cursor jump
    if (value !== cleanValue) {
      // Save cursor position
      var start = this.selectionStart;
      var end = this.selectionEnd;
      
      $(this).val(cleanValue);
      
      // Restore cursor position
      if (this.setSelectionRange) {
        this.setSelectionRange(start, end);
      }
    }
  });

  $(document).on("blur", ".required-qty-input", function () {
    var value = $(this).val();
    if (value === "" || value === ".") {
      $(this).val("0");
    }
  });

  ///////////////////////////////////////////////// Ingredients pop build Quantity//////////////////////////////////////
  $("#builds_Quantity").on("input change", function () {
    $("#ingredientsTableBody").empty();
    var productId = localStorage.getItem("productId", productId);
    orderDetails(productId);
    var div = document.getElementById("ingredientsModal");
    div.style.display = "block";
    var Ingredients = JSON.parse(localStorage.getItem("Ingredients_details"));
    var buildQty = $("#builds_Quantity").val();
    $("#builds_Quantity").val(buildQty);
    if (Ingredients) {
      Ingredients.forEach(function (rawMaterial) {
        if (rawMaterial.product_id === productId) {
          var yieldData = JSON.parse(localStorage.getItem("yieldData")) || {};
          var minBatchQty = parseFloat(yieldData.min_batch_qty) || 1;
          rawMaterial.required_qty =
            (parseFloat(rawMaterial.quantity_required) * parseFloat(buildQty || 0)) / minBatchQty;
          loadIngredients(Ingredients);
        } else {
          loadIngredients();
        }
      });
    }
  });
  $("#print_raw_materials").on("click", function () {
    // var productId = localStorage.getItem('productId', productId);
    // orderDetails(productId)
    // var Ingredients = JSON.parse(localStorage.getItem('Ingredients_details'));
    // print_raw_materials(Ingredients);
  });
});
