function normalizeDateToIso(rawDate) {
  if (rawDate === null || rawDate === undefined) return '';
  rawDate = String(rawDate).trim();
  if (!rawDate) return '';
  if (rawDate.indexOf('/') !== -1) {
    let parts = rawDate.split('/');
    if (parts.length === 3) {
      return parts[2] + '-' + parts[1] + '-' + parts[0];
    }
  }
  return rawDate;
}

function isAnniversaryOccasion($container) {
  const occasionText = ($container.find('.event_type option:selected').text() || '').toLowerCase().trim();
  return occasionText.includes('anniversary');
}

function isFutureIsoDate(isoDate) {
  if (!isoDate) return false;
  const today = new Date();
  const todayIso = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  const picked = new Date(isoDate + 'T00:00:00');
  return picked > todayIso;
}

function resolveSelectedEventId($container) {
  $container = $container && $container.length ? $container : $('.customer_family_relation').first();
  let cachedEventId = $.trim(String($container.data('selected-event-id') || ''));
  if (cachedEventId !== '') {
    return cachedEventId;
  }
  let $event = $container.find('.event_type');
  if (!$event.length) {
    return '';
  }
  let selectedVal = $.trim(String($event.find('option:selected').val() || ''));
  if (selectedVal !== '') {
    return selectedVal;
  }
  return $.trim(String($event.val() || ''));
}

function resolveSelectedRelationId($container) {
  $container = $container && $container.length ? $container : $('.customer_family_relation').first();
  let cachedRelationId = $.trim(String($container.data('selected-relation-id') || ''));
  if (cachedRelationId !== '') {
    return cachedRelationId;
  }
  let $relation = $container.find('.relation');
  if (!$relation.length) {
    return '';
  }
  let selectedVal = $.trim(String($relation.find('option:selected').val() || ''));
  if (selectedVal !== '') {
    return selectedVal;
  }
  return $.trim(String($relation.val() || ''));
}

$(document).ready(function () {

  let editIndex = null;
  $(".submitBtn").show();
  $(document).off('click', '.submitBtn');
  $(document).off('change', '.customer_family_relation .event_type');
  $(document).off('change', '.customer_family_relation .relation');
  $(document).on('change', '.customer_family_relation .relation', function () {
    const $container = $(this).closest('.customer_family_relation');
    const selectedId = $.trim(String($(this).find('option:selected').val() || $(this).val() || ''));
    $container.data('selected-relation-id', selectedId);
  });
  $(document).on('change', '.customer_family_relation .event_type', function () {
    const $container = $(this).closest('.customer_family_relation');
    const selectedId = $.trim(String($(this).find('option:selected').val() || $(this).val() || ''));
    $container.data('selected-event-id', selectedId);
  });
  $(document).on('click', '.submitBtn', function () {
    $('.error-message').hide();
    var $container = $(this).closest('.customer_family_relation');
    let relation = resolveSelectedRelationId($container);
    let name = $container.find('.personName').val().trim();
    let eventType = resolveSelectedEventId($container);
    // let date = $container.find('.event-date').val();
    let rawDate = $container.find('.event-date').val();
    let date = normalizeDateToIso(rawDate);

    let customer_id = $container.find('.customer_id').val();

    let isValid = true;

    if (!relation) { $container.find('#relation-error').show(); isValid = false; }
    if (!name) { $container.find('#name-error').show(); isValid = false; }
    if (!eventType) { $container.find('#event_type-error').show(); isValid = false; }
    if (!date) { $container.find('.date-error').show(); isValid = false; }
    if (date && !isAnniversaryOccasion($container) && isFutureIsoDate(date)) {
      $container.find('.date-error').text('Future date is not allowed for this occasion.').show();
      isValid = false;
    } else {
      $container.find('.date-error').text('This field is required.');
    }

    if (!isValid) return;

    let eventData = {
      relation_id: relation,
      event_id: eventType,
      name: name,
      companies_id: customer_id,
      date: date
    };

    CustomerFamilyRelationDataSave(eventData);
  });
  function CustomerFamilyRelationDataSave(eventData) {
    var submitBtn = $(".submitBtn");
    if (submitBtn.prop("disabled")) return;

    submitBtn.prop("disabled", true).hide();
    $.ajax({
      url: site.base_url + "pos/CustomerFamilyRelationDataSave",
      type: "POST",
      data: { eventData: eventData },
      dataType: "json",
      success: function (response) {
        // toastr.success('added Successfully');
        toastr.success('added Successfully', '', {
          timeOut: 3000 // The notification will disappear after 5 seconds (5000 milliseconds)
        });
        var customer_id = $(".customer_id").val();
        getCustomerRelationDetailById(customer_id);
        setTimeout(function () {
          submitBtn.prop("disabled", false).show();
        }, 0.001);
      },
      error: function () {
        toastr.success('error occured while fatching the data');
        setTimeout(function () {
          submitBtn.prop("disabled", false).show();
        }, 0.001);
      }
    });
  }

});
function getCustomerRelationDetailById() {
  var customer_id = $(".customer_id").val();
  if (!customer_id) {
    return;
  }
  $.ajax({
    url: site.base_url + "pos/getCustomerRelationDetailById",
    type: "POST",
    data: { customer_id: customer_id },
    dataType: "json",
    success: function (response) {
      populateTable(response);
    },
    error: function () {
      toastr.success('error occured');
    }
  });
  function populateTable(data) {
    $(".relation").val('').trigger("change");
    $(".personName").val('');
    $(".event_type").val('').trigger("change");
    $(".event-date").val('');
    let tableBody = $(".gridContent");
    tableBody.empty(); // Clear previous data

    data.forEach((item, index) => { // Added `index` as a parameter
      let row = `<tr>
                    <td style = "text-align : left;">${item.relation_name}</td>
                    <td style = "text-align : left;">${item.name}</td>
                    <td style = "text-align : left;">${item.event_name}</td>
                    <td  style = "text-align : center;">${item.date}</td>
                    <td class="grid-actions">
                        <button class="edit-btn edit_btn" id = "edit_btn" data-index="${item.id}">Edit</button>
                        <button class="delete-btn" data-index="${index}" id=${item.id}>Delete</button>
                    </td>
                   </tr>`;
      tableBody.append(row);
    });
  }
}
$(document).on("click", ".editButton", function () {
  var $con = $(this).closest('.customer_family_relation');
  let rawDate = $con.find('.event-date').val();
  let date = normalizeDateToIso(rawDate);
  let formData = {
    compaines_id: $con.find('.customer_id').val(),
    relation: resolveSelectedRelationId($con),
    personName: $con.find('.personName').val(),
    event_type: resolveSelectedEventId($con),
    date: date,
    id: $con.find('.customer_details_id').val(),
  };
  for (let key in formData) {
    if (formData[key] === "") {
      $("#myButton").prop("disabled", true);
      return; // Stop the function if any field is blank
    } else {
      $("#myButton").prop("disabled", false);
    }
  }
  if (date && !isAnniversaryOccasion($con) && isFutureIsoDate(date)) {
    $con.find('.date-error').text('Future date is not allowed for this occasion.').show();
    return;
  } else {
    $con.find('.date-error').text('This field is required.');
  }

  $.ajax({
    url: "pos/save_data",
    type: "POST",
    data: formData,
    success: function (response) {
      // Prevent multiple toastr calls
      if (!window.toastrCalled) {
        toastr.success('Saved Successfully', '', {
          timeOut: 400
        });
        window.toastrCalled = true;
        setTimeout(() => { window.toastrCalled = false; }, 3100);
      }
      // toastr.success('saved Successfully');
      $(".editButton").hide();
      $(".submitBtn").show();
      getCustomerRelationDetailById();
    },
    error: function () {
      toastr.success('error occured');

    }
  });
});
$(document).on("click", ".delete-btn", function (event) {
  if (confirm) {
    $.ajax({
      url: site.base_url + "pos/CustomerFamilyRelationDataDelete",
      type: "POST",
      data: { 'details_id': event.target.id },
      success: function (response) {
        if (!window.deleteToastrCalled) {
          toastr.success('Delete Successfully', '', {
            timeOut: 3000
          });
          window.deleteToastrCalled = true;
          setTimeout(() => { window.deleteToastrCalled = false; }, 3100);
        }

        getCustomerRelationDetailById();

      },
      error: function () {
        toastr.success('Error occured');
      }
    });
  }
});
$(document).on("click", ".edit_btn", function () {
  // Edit button is inside table-container; pick sibling form container explicitly.
  var $con = $(this).closest('.table-container').prevAll('.customer_family_relation').first();
  if (!$con.length) {
    $con = $('.customer_family_relation').first();
  }
  $con.find(".submitBtn").hide();
  $con.find(".editButton").show();
  $con.find(".relation").val('').trigger("change");
  $con.find(".personName").val('');
  $con.find(".event_type").val('').trigger("change");
  $con.find(".event-date").val('');


  let index = $(this).data("index");

  $.ajax({
    url: "pos/getDetailData",
    type: "POST",
    data: { 'detail_id': index },
    success: function (responce) {
      var parsedResponse = JSON.parse(responce);
      $con.find(".relation").val("").trigger("change");
      $con.find(".personName").val("");
      $con.find(".event_type").val("").trigger("change");
      let dbDate = (parsedResponse && parsedResponse.date) ? String(parsedResponse.date) : '';
      // If date is in DD/MM/YYYY → convert to YYYY-MM-DD
      if (dbDate.indexOf('/') !== -1) {
        let parts = dbDate.split('/');
        dbDate = parts[2] + '-' + parts[1] + '-' + parts[0];
      }

      $con.find(".customer_details_id").val("");
      // Populate fields with new data
      $con.find(".relation").val(parsedResponse.relation_id).trigger("change");
      $con.data('selected-relation-id', String(parsedResponse.relation_id || '').trim());
      $con.find(".personName").val(parsedResponse.name);
      $con.find(".event_type").val(parsedResponse.event_id).trigger("change");
      $con.data('selected-event-id', String(parsedResponse.event_id || '').trim());
      // Set date after event_type change, because event_type change handler clears date field.
      $con.find(".event-date").val(dbDate);
      $con.find(".customer_details_id").val(parsedResponse.id);
      // Show modal
      $("#editModal").data("index", index).modal("show");
    },
    error: function () {
      toastr.success('error occured');
    }
  });
  //   let item = data[index];


});
$(document).ready(function () {
  // Show div1 when button 1 is clicked
  $(".payments_mainsection").show();
  $(document).on("click", "#showDivButton1", function () {
    $(".payments_mainsection").show();
    $(".customerDetails").hide(); // Hide the other div
    $(".family_relation").hide(); // Hide the other div
    $(".profile").hide(); // Hide the other div
     $(".addressSection").hide();
  });

  // Show div2 when button 2 is clicked
  $(document).on("click", "#showDivButton2", function () {
    $(".customerDetails").show();
    $(".profile").show();
    $(".payments_mainsection").hide(); // Hide the other div
    $(".family_relation").hide(); // Hide the other div
    if (typeof customer_details === "function") {
      var phone = typeof extractPosCustomerPhone === "function" ? extractPosCustomerPhone() : "";
      if (phone) {
        customer_details(phone);
      }
    }
  });
  $(document).on("click", "#showDivButton3", function () {
    $(".customerDetails").show();
    $(".profile").show();
    $(".payments_mainsection").hide(); // Hide the other div
    $(".family_relation").hide(); // Hide the other div
    $(".addressSection").hide();
  });
  $(document).on("click", "#showDivButton5", function () {
    $(".customerDetails").show();
    $(".addressSection").show();
    $(".profile").hide();
    $(".payments_mainsection").hide();
    $(".family_relation").hide();
    if (typeof refreshCrmAddressesTab === "function") {
      refreshCrmAddressesTab();
    } else if (typeof initCrmAddressBook === "function") {
      initCrmAddressBook();
      renderAddressCards();
    }
  });
  $(document).on("click", "#showDivButton4", function () {
    getCustomerRelationDetailById();
    $(".family_relation").show();
    $(".customerDetails").show();
    $(".profile").hide(); // Hide the other div
    $(".addressSection").hide();
    $(".payments_mainsection").hide(); // Hide the other div
  });
});
$(document).ready(function () {
  // When the "Add" button is clicked, validate the form
  // $(document).on('click', '.submitBtn', function () {
  //   var isValid = true;

  //   // Clear previous error messages
  //   $('.error-message').hide();

  //   // Validate each field
  //   if ($('.relation').val() === '') {
  //     $('#relation-error').show();
  //     isValid = false;
  //   }

  //   if ($('.personName').val().trim() === '') {
  //     $('#name-error').show();
  //     isValid = false;
  //   }

  //   if ($('.event_type').val() === '') {
  //     $('#event_type-error').show();
  //     isValid = false;
  //   }

  //   // if ($('.date').val() === '') {
  //   //   $('.date-error').show();
  //   //   isValid = false;
  //   // }
  //   if (isValid) {
  //     console.log('Form is valid');
  //   } else {
  //     console.log('Form contains errors');
  //   }
  // });

  $('.relation').on('change', function () {
    if ($(this).val() !== '') {
      $('#relation-error').hide();
    }
  });

  $('.personName').on('keyup', function () {
    if ($(this).val().trim() !== '') {
      $('#name-error').hide();
    }
  });

  $('.event_type').on('change', function () {
    if ($(this).val() !== '') {
      $('.event_type-error').hide();
    }
  });

  $('.event-date').on('change', function () {
    if ($(this).val() !== '') {
      $('.date-error').hide();
    }
  });
});

$(document).ready(function () {
  // When the "save" button is clicked, validate the form
  $('.editButton').on('click', function () {
    var isValid = true;

    // Clear previous error messages
    $('.error-message').hide();

    // Validate each field
    if ($('.relation').val() === '') {
      $('#relation-error').show();
      isValid = false;
    }

    if ($('.personName').val().trim() === '') {
      $('#name-error').show();
      isValid = false;
    }

    if ($('.event_type').val() === '') {
      $('.event_type-error').show();
      isValid = false;
    }

    if ($('.event-date').val() === '') {
      $('.date-error').show();
      isValid = false;
    }
    if (isValid) {
      console.log('Form is valid');
    } else {
      console.log('Form contains errors');
      event.preventDefault();
    }
  });

  $('.relation').on('change', function () {
    if ($(this).val() !== '') {
      $('#relation-error').hide();
    }
  });

  $('.personName').on('keyup', function () {
    if ($(this).val().trim() !== '') {
      $('#name-error').hide();
    }
  });

  $('.event_type').on('change', function () {
    if ($(this).val() !== '') {
      $('.event_type-error').hide();
    }
  });

  $('.event-date').on('change', function () {
    if ($(this).val() !== '') {
      $('.date-error').hide();
    }
  });
});
