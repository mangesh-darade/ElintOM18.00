(function ($) {
    "use strict";

    var cfg = window.ServiceSiteReportConfig || {};
    var csrfName = cfg.csrfName || "";
    var csrfHash = cfg.csrfHash || "";
    var customerAddresses = cfg.customerAddresses || {};

    $(function () {
        var customerLocationsMap = {};

        // Auto-hide success/error flash messages after 2 seconds
        setTimeout(function() {
            $('.alert-dismissible-mobile').fadeOut(600);
        }, 2000);

        function updateServiceReportNo(force) {
            var custId = $("#customer_id").val() || "";
            var locId = $("#customer_location_id").val() || "0";
            if (!custId || custId === "0") {
                $("#service_report_no").val("");
                return;
            }

            $("#service_report_no").val("Generating...");
            $.post(cfg.nextReportNumberUrl, {company_id: custId, location_id: locId, [csrfName]: csrfHash}, function(res) {
                if (res.csrfHash) csrfHash = res.csrfHash;
                if (res.status === 'success' && res.next_number) {
                    $("#service_report_no").val(res.next_number);
                } else {
                    $("#service_report_no").val(custId + "/LOC/??");
                }
            }, 'json');
        }

        $("#customer_id, #customer_location_id").on("change", function() {
            updateServiceReportNo(true); // Force reset on cust/loc change
        });
        
        $("input[name='service_date']").on("change", function() {
            updateServiceReportNo(false);
        });

        // Initialize
        updateServiceReportNo(false);

        // Clear session storage on actual form submit success (handled by reload usually)
        // But we add it here just in case
        $("#ssr_mobile_form").on("submit", function () {
            serializePmGridMobile();
            // We don't clear here yet, only after successful redirect
        });
        // Intercept Tab Clicks to Save Data
        $(".nav-tabs-mobile a").on("click", function (e) {
            e.preventDefault();
            serializePmGridMobile();
            var targetUrl = $(this).attr("href");
            if (!targetUrl) {
                return;
            }
            var formData = $("#ssr_mobile_form").serializeArray();
            var payload = {};
            formData.forEach(function(item) { payload[item.name] = item.value; });
            payload[csrfName] = csrfHash;

            $.ajax({
                url: cfg.saveMobileTabDataUrl,
                type: "POST",
                dataType: "json",
                data: payload,
                success: function (res) {
                    if (res && res.csrfHash) {
                        csrfHash = res.csrfHash;
                    }
                    if (res && res.status === "success") {
                        window.location.href = targetUrl;
                        return;
                    }
                    alert((res && res.message) ? res.message : "Unable to save current tab data. Please try again.");
                },
                error: function() {
                    alert("Unable to save current tab data due to network/server issue. Please try again.");
                }
            });
        });

        // Intercept form submit (Next / Submit buttons) — same as desktop
        // Must call serializePmGridMobile() BEFORE the form is serialized
        $("#ssr_mobile_form").on("submit", function () {
            serializePmGridMobile();
        });

        function buildAddressText(item) {
            var parts = [];
            var seen = {};
            function addPart(value) {
                var v = $.trim(value || "");
                if (!v) return;
                var key = v.toLowerCase();
                if (seen[key]) return;
                seen[key] = true;
                parts.push(v);
            }
            addPart(item.line1); addPart(item.line2); addPart(item.city);
            addPart(item.state); addPart(item.postal_code); addPart(item.country);
            addPart(item.floor_details);
            return $.trim(parts.join(", "));
        }

        function renderCustomerLocations(locations) {
            customerLocationsMap = {};
            var $location = $("#customer_location_id");
            $location.html('<option value="">Select job site name</option>');
            if (!$.isArray(locations) || !locations.length) return;

            $.each(locations, function (idx, item) {
                if (!item || !item.id) return;
                customerLocationsMap[item.id] = item;
                var label = $.trim(item.location_name || item.warehouse_name || item.address_name || ("Location " + item.id));
                var $opt = $("<option/>", { value: item.id, text: label });
                if (item.id) $opt.attr("data-location-id", item.id);
                if (item.code) $opt.attr("data-code", item.code);
                $location.append($opt);
            });
            
            // Auto-select first location — exactly as desktop does (service_site_report.js line 75-78)
            var firstId = (locations[0] && locations[0].id) ? locations[0].id : "";
            if (firstId) {
                $location.val(firstId).trigger("change");
            }
        }

        function fetchCustomerLocations(customerId) {
            if (!customerId) {
                renderCustomerLocations([]);
                return;
            }
            $.ajax({
                url: cfg.customerLocationsUrl,
                type: "POST",
                dataType: "json",
                data: { company_id: customerId, [csrfName]: csrfHash },
                success: function (res) {
                    if (res && res.csrfHash) csrfHash = res.csrfHash;
                    if (res && res.status === "success" && $.isArray(res.locations)) {
                        renderCustomerLocations(res.locations);
                    }
                }
            });
        }

        function fetchEquipmentDetails() {
            var companyId = $.trim($("#customer_id").val() || "");
            var locationId = $.trim($("#actual_location_id").val() || $("#customer_location_id").val() || "");
            var equipmentTag = $.trim($("#eqpt_tag_no").val() || "");
            var existingEqId = $.trim($("#equipment_id").val() || "");

            if (!companyId || !locationId) {
                if (!companyId) $("#eqpt_tag_no").val("");
                return;
            }

            if (!equipmentTag) {
                $("#model_no, #serial_no, #equipment_id").val("");
                $("#multi_equipment_wrapper").hide();
                $("#multi_equipment_grid").empty();
                return;
            }

            $.ajax({
                url: cfg.equipmentDetailsUrl,
                type: "POST",
                dataType: "json",
                data: {
                    company_id: companyId,
                    location_id: locationId,
                    equipment_tag: equipmentTag,
                    tag_no: equipmentTag,
                    [csrfName]: csrfHash
                },
                success: function (res) {
                    if (res && res.csrfHash) csrfHash = res.csrfHash;
                    if (!res || res.status !== "success" || !res.details) return;
                    
                    $("#multi_equipment_wrapper").hide();
                    $("#multi_equipment_grid").empty();
                    
                    var details = $.isArray(res.details) ? res.details : [res.details];

                    if (details.length >= 1) {
                        $("#multi_equipment_wrapper").show();
                        var html = "";
                        details.forEach(function(item) {
                            var isChecked = "";
                            if (existingEqId) {
                                if (String(item.id) === String(existingEqId)) isChecked = 'checked';
                            } else if (details.length === 1) {
                                isChecked = 'checked';
                            }
                            
                            html += '<label class="multi-equipment-row" style="display:flex; align-items:center; padding:12px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; cursor:pointer;">';
                            html += '  <input type="radio" name="select_multi_eq" class="select-multi-eq" value="' + (item.id||'') + '" data-id="' + (item.id||'') + '" data-model="' + (item.model_no||'') + '" data-serial="' + (item.serial_no||'') + '" style="margin:0 12px 0 0;" ' + isChecked + '>';
                            html += '  <div style="flex:1;">';
                            html += '    <div style="font-size:13px; color:#475569; margin-bottom:4px;"><strong>Model:</strong> ' + (item.model_no || '-') + '</div>';
                            html += '    <div style="font-size:13px; color:#475569;"><strong>Serial:</strong> ' + (item.serial_no || '-') + '</div>';
                            html += '  </div>';
                            html += '</label>';
                        });
                        $("#multi_equipment_grid").html(html);

                        var $title = $("#multi_equipment_wrapper").find("div").first();
                        if (details.length === 1) {
                            var item = details[0];
                            $("#equipment_id").val(item.id || "");
                            $("#model_no").val(item.model_no || "");
                            $("#serial_no").val(item.serial_no || "");
                            $title.html('<i class="fa fa-info-circle text-info"></i> Equipment Details:');
                        } else {
                            // If we have multiple but one was already selected (isChecked), don't clear inputs
                            if (!existingEqId) {
                                $("#equipment_id").val("");
                                $("#model_no").val("");
                                $("#serial_no").val("");
                            }
                            $title.html('<i class="fa fa-info-circle text-info"></i> Multiple records found:');
                        }
                        
                        var d = details[0];
                        if (d.floor_details && !$("#job_site_address").val()) {
                            var addr = d.floor_details;
                            $("#job_site_address").val(addr);
                            var $addr = $("#job_site_address");
                            if ($addr.hasClass('redactor') || typeof $.fn.redactor !== 'undefined') {
                                try { $addr.redactor('code.set', addr); } catch(e) {}
                            }
                        }
                    }
                }
            });
        }

        $(document).on("change", ".select-multi-eq", function() {
            var $r = $(this);
            $("#equipment_id").val($r.data("id"));
            $("#model_no").val($r.data("model"));
            $("#serial_no").val($r.data("serial"));
        });

        $("#customer_id").on("change", function () {
            var id = $(this).val();
            $("#job_site_name").val("");
            $("#job_site_address").val("");
            
            fetchCustomerLocations(id);
        });

        $(document).on("change", "#customer_location_id", function () {
            var locationId = $(this).val();
            var item = (locationId && customerLocationsMap[locationId]) ? customerLocationsMap[locationId] : null;
            if (!item) {
                $("#job_site_name").val("");
                $("#job_site_address").val("");
                return;
            }

            var siteName = $.trim(item.location_name || item.warehouse_name || item.address_name || "");
            var addressText = buildAddressText(item);

            $("#job_site_name").val(siteName);
            // Direct .val() works now — Redactor is blocked by the 'skip' class
            $("#job_site_address").val(addressText);
        });

        $("#customer_id, #customer_location_id, #eqpt_tag_no").on("change", fetchEquipmentDetails);

        // Initialization for draft load
        if ($("#customer_id").val()) {
            var initId = $("#customer_id").val();
            $.ajax({
                url: cfg.customerLocationsUrl,
                type: "POST",
                dataType: "json",
                data: { company_id: initId, [csrfName]: csrfHash },
                success: function (res) {
                    if (res && res.csrfHash) csrfHash = res.csrfHash;
                    if (res && res.status === "success" && $.isArray(res.locations)) {
                        $.each(res.locations, function (idx, item) {
                            if (item && item.id) customerLocationsMap[item.id] = item;
                        });
                        // Trigger locations change if we have a location but no site info yet
                        if ($("#customer_location_id").val() && !$("#job_site_address").val()) {
                             $("#customer_location_id").trigger("change");
                        }
                    }
                }
            });

            // Trigger equipment details fetch if we already have a tag selected in draft
            if ($("#eqpt_tag_no").val()) {
                fetchEquipmentDetails();
            }
        }

        // --- Quick Actions ---
        function getOtpDestination(channel) {
            var customerId = $("#customer_id").val();
            if (!customerId || !customerAddresses[customerId]) return "";
            return channel === "email" ? $.trim(customerAddresses[customerId].email || "") : $.trim(customerAddresses[customerId].phone || "");
        }

        $(document).on("click", "#btn_send_report_customer", function () {
            var customerId = $("#customer_id").val();
            if (!customerId) { alert("Please select customer first"); return; }
            serializePmGridMobile();
            $("#report_send_feedback").removeClass("text-danger text-success").text("Generating...");
            var payload = $("#ssr_mobile_form").serializeArray();
            payload.push({ name: csrfName, value: csrfHash });
            $.ajax({
                url: cfg.sendServiceReportToCustomerUrl, type: "POST", dataType: "json", data: payload,
                success: function (res) {
                    if (res && res.csrfHash) csrfHash = res.csrfHash;
                    if (res && res.status === "success") {
                        $("#report_sent_confirmed").val("1");
                        $("#report_send_feedback").removeClass("text-danger").addClass("text-success").text(res.message || "Report sent");
                    } else {
                        $("#report_send_feedback").removeClass("text-success").addClass("text-danger").text(res.message || "Failed");
                    }
                }
            });
        });

        $(document).on("click", "#btn_send_otp_email, #btn_resend_otp", function () {
            if (!$("#report_sent_confirmed").val()) { alert("Please send report to customer first"); return; }
            $("#otp_feedback").removeClass("text-danger text-success").text("Sending...");
            $.ajax({
                url: cfg.sendServiceReportOtpUrl, type: "POST", dataType: "json",
                data: { channel: "email", destination: getOtpDestination("email"), customer_id: $("#customer_id").val(), context_ref: $("#otp_context_ref").val(), [csrfName]: csrfHash },
                success: function (res) {
                    if (res && res.csrfHash) csrfHash = res.csrfHash;
                    if (res && res.status === "success") {
                        $("#otp_challenge_id").val(res.challenge_id || "");
                        if (res.context_ref) $("#otp_context_ref").val(res.context_ref);
                        $("#otp_feedback").removeClass("text-danger").addClass("text-success").text("OTP sent to client email.");
                        $("#resend_otp_wrapper").fadeIn();
                        $("#verify_sign_section").slideDown();
                    } else { $("#otp_feedback").removeClass("text-success").addClass("text-danger").text(res.message || "Failed"); }
                }
            });
        });

        $(document).on("input", ".otp-digit-input", function (e) {
            var $inputs = $(".otp-digit-input"), idx = $inputs.index(this), val = $(this).val();
            if (val.length > 1) $(this).val(val.slice(0, 1));
            if (val.length === 1 && idx < $inputs.length - 1) $inputs.eq(idx + 1).focus();
            var code = ""; $inputs.each(function() { code += $(this).val(); });
            $("#otp_code").val(code);
            $("#btn_verify_otp_big").toggleClass("enabled", code.length === 6);
        });

        $(document).on("keydown", ".otp-digit-input", function (e) {
            if (e.key === "Backspace" && !$(this).val()) {
                var $inputs = $(".otp-digit-input"), idx = $inputs.index(this);
                if (idx > 0) $inputs.eq(idx - 1).focus();
            }
        });

        $(document).on("click", "#btn_verify_otp_big", function () {
            $("#otp_feedback").removeClass("text-danger text-success").text("Verifying...");
            $.ajax({
                url: cfg.verifyServiceReportOtpUrl, type: "POST", dataType: "json",
                data: { challenge_id: $("#otp_challenge_id").val(), otp_code: $("#otp_code").val(), context_ref: $("#otp_context_ref").val(), channel: "email", is_mobile: "1", [csrfName]: csrfHash },
                success: function (res) {
                    if (res && res.csrfHash) csrfHash = res.csrfHash;
                    if (res && res.status === "success") {
                        $("#otp_verification_token").val(res.verification_token || "");
                        $("#otp_feedback").removeClass("text-danger").addClass("text-success").text("OTP Verified!");
                        alert("OTP Verified. Proceed to Signatures tab.");
                    } else { $("#otp_feedback").removeClass("text-success").addClass("text-danger").text(res.message || "Invalid OTP"); }
                }
            });
        });

        function initSignaturePad(canvasId, hiddenId, msgId) {
            var canvas = document.getElementById(canvasId);
            if (!canvas) return;
            
            // Set dimensions only if not already set or changed
            if (canvas.width !== canvas.offsetWidth || canvas.height !== canvas.offsetHeight) {
                canvas.width = canvas.offsetWidth; 
                canvas.height = canvas.offsetHeight;
            }
            
            var ctx = canvas.getContext("2d");
            ctx.strokeStyle = "#3b82f6"; ctx.lineWidth = 3; ctx.lineJoin = "round"; ctx.lineCap = "round";
            
            // Redraw existing signature if data exists
            var existingData = $("#" + hiddenId).val();
            if (existingData && existingData.indexOf('data:image') === 0) {
                var img = new Image();
                img.onload = function() {
                    ctx.drawImage(img, 0, 0);
                };
                img.src = existingData;
                $(canvas).siblings(".sig-hint").hide();
            }

            var drawing = false;
            function getPos(e) {
                var rect = canvas.getBoundingClientRect();
                var ev = e.originalEvent || e;
                var touch = ev.touches && ev.touches[0] ? ev.touches[0] : (ev.changedTouches && ev.changedTouches[0] ? ev.changedTouches[0] : null);
                if (touch) {
                    return { x: touch.clientX - rect.left, y: touch.clientY - rect.top };
                }
                return { x: e.clientX - rect.left, y: e.clientY - rect.top };
            }
            function handleStart(e) {
                drawing = true;
                ctx.beginPath();
                var pos = getPos(e);
                ctx.moveTo(pos.x, pos.y);
                $(canvas).siblings(".sig-hint").hide();
                if (e.type === "touchstart") e.preventDefault();
            }
            function handleMove(e) {
                if (!drawing) return;
                e.preventDefault();
                var pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            }
            function handleEnd() {
                if (!drawing) return;
                drawing = false;

                // Cache the drawing locally so tab switches don't erase it before AJAX completes
                canvas.dataset.localB64 = canvas.toDataURL();

                canvas.toBlob(function(blob) {
                    if (!blob) return;
                    var formData = new FormData();
                    formData.append('signature_file', blob, hiddenId + '.jpg');
                    formData.append(csrfName, csrfHash);
                    $.ajax({
                        url: cfg.uploadSignatureUrl,
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(res) {
                            if (res && res.csrfHash) csrfHash = res.csrfHash;
                            if (res && res.status === 'success' && res.filename) {
                                // Store only the filename — exactly like the logo
                                $('#' + hiddenId).val(res.filename).trigger('change');
                                if (msgId) $('#' + msgId).fadeIn();
                            }
                        }
                    });
                }, 'image/jpeg', 0.9);
            }

            // Using native listeners to avoid jQuery passive-event issues on mobile
            canvas.addEventListener("mousedown", handleStart, false);
            canvas.addEventListener("touchstart", handleStart, { passive: false });
            
            window.addEventListener("mousemove", handleMove, false);
            canvas.addEventListener("touchmove", handleMove, { passive: false });
            
            window.addEventListener("mouseup", handleEnd, false);
            canvas.addEventListener("touchend", handleEnd, false);
        }
        initSignaturePad("engineer_signature_pad", "engineer_signature_data", "sig_timestamp_msg_eng");
        initSignaturePad("customer_signature_pad", "customer_signature_data", "sig_timestamp_msg");

        // Disable Submit until both signatures are present
        function checkSubmitBtn() {
            var engSig = $.trim($("#engineer_signature_data").val());
            var custSig = $.trim($("#customer_signature_data").val());
            // Engineer = Base64, Customer = filename from server
            var engSigned = engSig.indexOf('data:image') === 0 || engSig.length > 5;
            var custSigned = custSig.length > 5; // filename like "abc123.jpg"
            $('button[name="action"][value="submit"]').prop('disabled', !(engSigned && custSigned));
        }
        checkSubmitBtn(); // evaluate on page load
        $(document).on("change", "#engineer_signature_data, #customer_signature_data", function () {
            checkSubmitBtn();
        });

        var activeTab = $(".nav-tabs-mobile li.active");
        if (activeTab.length > 0) {
            var container = $(".tabs-scroll-container");
            var scrollLeft = activeTab.offset().left + container.scrollLeft() - (container.width() / 2) + (activeTab.width() / 2);
            container.animate({ scrollLeft: scrollLeft }, 300);
        }

        $("#ssr_mobile_form").on("submit", function (e) {
            serializePmGridMobile();
            if ($(document.activeElement).val() === "submit") {
                if (!$("#report_sent_confirmed").val()) { alert("Please send report first"); return false; }
                if (!$("#otp_verification_token").val()) { alert("Please verify OTP first"); return false; }
            }
        });
        // Keep hidden PM JSON always up-to-date while typing.
        $(document).on("input change", ".pm-cell-mobile", function () {
            serializePmGridMobile();
        });
        
        function serializePmGridMobile() {
            // Only run when PM grid is actually in the DOM (i.e. user is on pm_log tab).
            // If we're on any other tab, the rows don't exist — do NOT overwrite the
            // hidden field; the server-side draft already has the correct value.
            var $rows = $(".pm-data-row-mobile");
            if ($rows.length === 0) {
                return;
            }
            var data = [];
            $rows.each(function () {
                var $row = $(this);
                // Push ALL rows unconditionally — same as desktop serializePmLogGrid()
                // This ensures the full parameter list always shows in the PDF report
                data.push({
                    section: $.trim($row.attr("data-section") || ""),
                    parameter: $.trim($row.attr("data-parameter") || ""),
                    ckt_01: $.trim($row.find('.pm-cell-mobile[data-ckt="ckt_01"]').val() || ""),
                    ckt_02: $.trim($row.find('.pm-cell-mobile[data-ckt="ckt_02"]').val() || ""),
                    ckt_03: $.trim($row.find('.pm-cell-mobile[data-ckt="ckt_03"]').val() || ""),
                    ckt_04: $.trim($row.find('.pm-cell-mobile[data-ckt="ckt_04"]').val() || "")
                });
            });
            $("#pm_log_grid_json").val(JSON.stringify(data));
        }

        // --- Global Equipment Selection & Creation ---
        var $modal = $("#global_equipment_modal");
        var $searchInput = $("#equipment_search_input");
        var $listContainer = $("#global_equipment_list_container");
        var $selectionView = $("#equipment_selection_view");
        var $formView = $("#add_equipment_form_view");
        var $modalTitle = $("#equipment_modal_title");
        
        var unmappedProducts = [];

        function fetchUnmappedProducts() {
            var customerId = $("#customer_id").val();
            if (!customerId) return;
            
            $listContainer.html('<div style="padding:20px; text-align:center;"><i class="fa fa-spinner fa-spin"></i> Fetching products...</div>');
            
            $.ajax({
                url: ServiceSiteReportConfig.getUnmappedProductsUrl,
                type: 'GET',
                data: { customer_id: customerId },
                success: function(res) {
                    if (res.status === 'success') {
                        unmappedProducts = res.data || [];
                        unmappedProducts.sort(function(a, b) {
                            var nA = (a.name || "").toLowerCase();
                            var nB = (b.name || "").toLowerCase();
                            if (nA < nB) return -1;
                            if (nA > nB) return 1;
                            return 0;
                        });
                        renderUnmappedProducts("");
                    } else {
                        $listContainer.html('<div class="no-results" style="padding:20px; text-align:center;">' + (res.message || 'Error fetching products') + '</div>');
                    }
                }
            });
        }

        function renderUnmappedProducts(filter) {
            var html = '';
            var query = $.trim(filter || "").toLowerCase();
            var results = unmappedProducts;
            
            if (query) {
                results = unmappedProducts.filter(function(p) {
                    return (p.name || "").toLowerCase().indexOf(query) !== -1 || (p.code || "").toLowerCase().indexOf(query) !== -1;
                });
            }
            
            if (results.length === 0) {
                html = '<div class="no-results" style="padding:20px; text-align:center; color:#666;">' + (query ? 'No matching products found.' : 'No unmapped products found.') + '</div>';
            } else {
                results.forEach(function(p) {
                    html += '<div class="equipment-list-item-container" style="padding:16px; border:1px solid #f3f4f6; border-radius:16px; margin-bottom:12px; background:#fff; box-shadow:0 2px 4px rgba(0,0,0,0.02);">';
                    html += '  <div style="margin-bottom:12px;">';
                    html += '    <div style="font-weight:700; font-size:15px; color:#111827; line-height:1.4;">' + p.name + '</div>';
                    html += '    <div style="font-size:12px; color:#9ca3af; margin-top:2px; font-weight:500;">Code: ' + (p.code || 'N/A') + '</div>';
                    html += '  </div>';
                    html += '  <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:8px; align-items:center;">';
                    html += '    <input type="text" class="inline-model" placeholder="Model No" style="width:100%; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:13px; background:#f9fafb; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor=\'#3b82f6\'" onblur="this.style.borderColor=\'#e5e7eb\'">';
                    html += '    <input type="text" class="inline-serial" placeholder="Serial No" style="width:100%; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:10px; font-size:13px; background:#f9fafb; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor=\'#3b82f6\'" onblur="this.style.borderColor=\'#e5e7eb\'">';
                    html += '    <button type="button" class="btn-map-product-inline" data-id="' + p.id + '" data-name="' + p.name + '" style="padding:10px 16px; background:#2563eb; color:#fff; border:none; border-radius:10px; font-size:13px; font-weight:700; box-shadow:0 4px 6px -1px rgba(37,99,235,0.2);">Select</button>';
                    html += '  </div>';
                    html += '</div>';
                });
            }
            $listContainer.html(html);
        }

        $("#btn_open_global_equipment").on("click", function() {
            var customerId = $("#customer_id").val();
            if (!customerId) {
                alert("Please select a customer first.");
                return;
            }
            $modal.css('display', 'flex').hide().fadeIn(200);
            $selectionView.show();
            $formView.hide();
            $modalTitle.text("Equipment Selection");
            $searchInput.val('').focus();
            fetchUnmappedProducts();
        });

        $("#btn_close_global_equipment, .modal-mobile-overlay").on("click", function(e) {
            if (e.target === this || this.id === 'btn_close_global_equipment') $modal.fadeOut(200);
        });

        $searchInput.on("input", function() {
            renderUnmappedProducts($(this).val());
        });

        // Toggle to Add Form
        $("#btn_show_add_equipment_form").on("click", function() {
            $selectionView.hide();
            $formView.show();
            $modalTitle.text("Add New Equipment");
            $("#new_eq_product_name").val('').focus();
            $("#new_eq_brand_input").val('');
            $("#new_eq_brand_id").val('');
            $("#new_eq_model_no").val('');
            $("#new_eq_serial_no").val('');
        });

        $("#btn_cancel_add_equipment").on("click", function() {
            $formView.hide();
            $selectionView.fadeIn(200);
            $modalTitle.text("Equipment Selection");
        });

        // Map product with inline details
        $(document).on("click", ".btn-map-product-inline", function() {
            var $btn = $(this);
            var $container = $btn.closest(".equipment-list-item-container");
            var pId = $btn.data('id');
            var pName = $btn.data('name');
            var modelNo = $.trim($container.find(".inline-model").val());
            var serialNo = $.trim($container.find(".inline-serial").val());

            if (!modelNo || !serialNo) {
                alert("Please enter both Model No and Serial No.");
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            
            saveEquipment({
                product_id: pId,
                product_name: pName,
                model_no: modelNo,
                serial_no: serialNo,
                customer_id: $("#customer_id").val(),
                location_id: $("#customer_location_id").val()
            });
        });

        // Save New Equipment Form
        var isSavingEquipment = false;
        $("#btn_save_new_equipment").on("click", function() {
            if (isSavingEquipment) return;
            var $btn = $(this);
            var data = {
                customer_id: $("#customer_id").val(),
                location_id: $("#customer_location_id").val(),
                product_id: $("#selected_existing_product_id").val(), // For existing products
                product_name: $.trim($("#new_eq_product_name").val()),
                brand: $.trim($("#new_eq_brand_input").val()),
                model_no: $.trim($("#new_eq_model_no").val()),
                serial_no: $.trim($("#new_eq_serial_no").val())
            };
            
            if (!data.model_no || !data.serial_no) {
                alert("Please enter both Model No and Serial No.");
                return;
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            isSavingEquipment = true;
            saveEquipment(data);
        });

        function saveEquipment(data) {
            var csrfName = ServiceSiteReportConfig.csrfName;
            var csrfHash = ServiceSiteReportConfig.csrfHash;
            data[csrfName] = csrfHash;

            $.ajax({
                url: ServiceSiteReportConfig.addNewEquipmentUrl,
                type: 'POST',
                data: data,
                success: function(res) {
                    if (res.csrfHash) ServiceSiteReportConfig.csrfHash = res.csrfHash;
                    if (res.status === 'success') {
                        var eq = res.equipment;
                        var $dropdown = $("#eqpt_tag_no");
                        
                        // Add to dropdown if not exists
                        if ($dropdown.find('option[value="' + eq.tag_no + '"]').length === 0) {
                            $dropdown.append($('<option/>', { value: eq.tag_no, text: eq.tag_no }));
                        }
                        
                        // Set values directly to the UI
                        $dropdown.val(eq.tag_no);
                        $("#equipment_id").val(eq.id || "");
                        $("#model_no").val(eq.model_no || "");
                        $("#serial_no").val(eq.serial_no || "");
                        
                        $("#multi_equipment_wrapper").show();
                        var html = '<label class="multi-equipment-row" style="display:flex; align-items:center; padding:12px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; cursor:pointer;">';
                        html += '  <input type="radio" name="select_multi_eq" class="select-multi-eq" value="' + (eq.id||'') + '" data-model="' + (eq.model_no||'') + '" data-serial="' + (eq.serial_no||'') + '" style="margin:0 12px 0 0;" checked>';
                        html += '  <div style="flex:1;">';
                        html += '    <div style="font-size:13px; color:#475569; margin-bottom:4px;"><strong>Model:</strong> ' + (eq.model_no || '-') + '</div>';
                        html += '    <div style="font-size:13px; color:#475569;"><strong>Serial:</strong> ' + (eq.serial_no || '-') + '</div>';
                        html += '  </div>';
                        html += '</label>';
                        $("#multi_equipment_grid").html(html);
                        $("#multi_equipment_wrapper").find("div").first().html('<i class="fa fa-info-circle text-info"></i> Equipment Details:');
                        
                        // Trigger change but tell our listener to skip the AJAX fetch
                        window.isManuallySettingEquipment = true;
                        $dropdown.trigger('change');
                        
                        // Also trigger a silent draft save to update the session on server
                        setTimeout(function() {
                            var formData = $("#ssr_mobile_form").serializeArray();
                            var payload = { active_tab: 'equipment' };
                            formData.forEach(function(item) { payload[item.name] = item.value; });
                            payload[csrfName] = csrfHash;
                            $.post(cfg.saveMobileTabDataUrl, payload);
                        }, 500);

                        $modal.fadeOut(200);
                        $("#btn_save_new_equipment").prop('disabled', false).text('Save');
                        isSavingEquipment = false;

                    } else {
                        alert(res.message || "Failed to save equipment");
                        $("#btn_save_new_equipment").prop('disabled', false).text('Save');
                    }
                },
                error: function() {
                    alert("A server error occurred.");
                    $("#btn_save_new_equipment").prop('disabled', false).text('Save');
                }
            });
        }

        // Brand Search
        var allBrands = [];
        $("#new_eq_brand_input").on("focus input", function() {
            var val = $(this).val().toLowerCase();
            if (allBrands.length === 0) {
                $.get(ServiceSiteReportConfig.getBrandsUrl, function(res) {
                    if (res.status === 'success') allBrands = res.data;
                    showBrandResults(val);
                });
            } else {
                showBrandResults(val);
            }
        });

        function showBrandResults(query) {
            var $results = $(".brand-results-overlay");
            var filtered = allBrands.filter(function(b) { return b.name.toLowerCase().indexOf(query) !== -1; });
            if (filtered.length > 0) {
                var html = '';
                filtered.forEach(function(b) {
                    html += '<div class="brand-item" data-id="' + b.id + '" data-name="' + b.name + '" style="padding:10px; cursor:pointer; border-bottom:1px solid #eee; color: #111;">' + b.name + '</div>';
                });
                $results.html(html).show();
            } else {
                $results.hide();
            }
        }

        $(document).on("click", ".brand-item", function() {
            $("#new_eq_brand_input").val($(this).data('name'));
            $("#new_eq_brand_id").val($(this).data('id'));
            $("#brand_search_results").hide();
        });

        $(document).on("click", function(e) {
            if (!$(e.target).closest('.form-group').length) $("#brand_search_results").hide();
        });
    });

    window.togglePMCategory = function(catId) { $('#' + catId).toggleClass('expanded').find('.pm-category-body-mobile').slideToggle(300); };
    window.switchSigTab = function(type) {
        $('.sig-pill').removeClass('active').each(function() { if ($(this).text().toLowerCase() === type) $(this).addClass('active'); });
        $('[id^="sig_"]').removeClass('active');
        var $pane = $('#sig_' + type + '_section').addClass('active');
        var canvas = $pane.find('canvas')[0];
        if (canvas) {
            // Only re-init if really necessary (setting width/height clears the canvas)
            if (canvas.width === 0 || canvas.width !== canvas.offsetWidth) {
                canvas.width = canvas.offsetWidth; 
                canvas.height = canvas.offsetHeight;
                var ctx = canvas.getContext('2d'); 
                ctx.strokeStyle = "#3b82f6"; ctx.lineWidth = 3; ctx.lineJoin = "round"; ctx.lineCap = "round";
                
                // Redraw if we have data after a resize/init
                var hid = type === 'engineer' ? 'engineer_signature_data' : 'customer_signature_data';
                var data = $('#' + hid).val();
                
                var imgSrc = canvas.dataset.localB64 || null;
                if (!imgSrc && data) {
                    imgSrc = data.indexOf('data:image') === 0 ? data : (cfg.assetsUploadUrl + data);
                }
                
                if (imgSrc) {
                    var img = new Image();
                    img.onload = function() { ctx.drawImage(img, 0, 0); };
                    img.src = imgSrc;
                    $(canvas).siblings(".sig-hint").hide();
                }
            }
        }
    };
    window.clearSig = function(canvasId) {
        var canvas = document.getElementById(canvasId);
        if (canvas) {
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            canvas.dataset.localB64 = ''; // clear local cache
            var hid = canvasId === 'engineer_signature_pad' ? 'engineer_signature_data' : 'customer_signature_data';
            $('#' + hid).val('').trigger('change');
            var msgId = canvasId === 'engineer_signature_pad' ? 'sig_timestamp_msg_eng' : 'sig_timestamp_msg';
            $('#' + msgId).fadeOut();
        }
    };
    window.filterCktGlobal = function(cktNum, el) {
        if ($(el).hasClass('active')) {
            return; // Do nothing if already active
        }
        var $grid = $('.pm-grid-mobile');
        var $pills = $('.pm-global-filters .ckt-pill');
        $pills.removeClass('active');
        $(el).addClass('active');
        $grid.removeClass('filter-ckt1 filter-ckt2 filter-ckt3 filter-ckt4');
        $grid.addClass('filter-ckt' + cktNum);
    };
})(jQuery);
