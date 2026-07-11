(function ($) {
    "use strict";

    var cfg = window.ServiceSiteReportConfig || {};
    var customerPmGridUrl = cfg.customerPmGridUrl || "";
    var customerLocationsUrl = cfg.customerLocationsUrl || "";
    var equipmentDetailsUrl = cfg.equipmentDetailsUrl || "";
    var sendServiceReportToCustomerUrl = cfg.sendServiceReportToCustomerUrl || "";
    var sendServiceReportOtpUrl = cfg.sendServiceReportOtpUrl || "";
    var verifyServiceReportOtpUrl = cfg.verifyServiceReportOtpUrl || "";
    var csrfName = cfg.csrfName || "";
    var csrfHash = cfg.csrfHash || "";

    function serializePmLogGrid() {
        var data = [];
        $("#pm_log_table tbody tr.pm-data-row").each(function () {
            var $row = $(this);
            data.push({
                section: $.trim($row.data("section") || ""),
                parameter: $.trim($row.data("parameter") || ""),
                ckt_01: $.trim($row.find('.pm-cell[data-ckt="ckt_01"]').val() || ""),
                ckt_02: $.trim($row.find('.pm-cell[data-ckt="ckt_02"]').val() || ""),
                ckt_03: $.trim($row.find('.pm-cell[data-ckt="ckt_03"]').val() || ""),
                ckt_04: $.trim($row.find('.pm-cell[data-ckt="ckt_04"]').val() || "")
            });
        });
        $("#pm_log_grid_json").val(JSON.stringify(data));
    }

    window.serializePmLogGrid = serializePmLogGrid;

    $(function () {
        var customerAddresses = cfg.customerAddresses || {};
        var customerLocationsMap = {};
        var previousCustomerId = $("#customer_id").val() || "";
        
        function updateServiceReportNo() {
            var $cust = $("#customer_id");
            var $reportNo = $("#service_report_no");
            
            var custId = $cust.val() || "";
            if (!custId || custId === "0") {
                $reportNo.val("");
                return;
            }

            var locCode = "LOC";
            var $loc = $("#customer_location_id option:selected");
            if ($loc.length && $loc.data("code")) {
                locCode = $loc.data("code");
            }

            var date = $("input[name='service_date']").val() || "";
            if (!date) {
                var d = new Date();
                date = d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
            }

            var now = new Date();
            var h = ("0" + now.getHours()).slice(-2);
            var m = ("0" + now.getMinutes()).slice(-2);
            
            var generated = custId + "/" + locCode + "/" + date + "/" + h + ":" + m;
            $reportNo.val(generated);
        }

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
            addPart(item.line1);
            addPart(item.line2);
            addPart(item.city);
            addPart(item.state);
            addPart(item.postal_code);
            addPart(item.country);
            addPart(item.floor_details);
            return $.trim(parts.join(", "));
        }

        function renderCustomerLocations(locations) {
            customerLocationsMap = {};
            var $location = $("#customer_location_id");
            $location.html('<option value="">Select location</option>');
            if (!$.isArray(locations) || !locations.length) {
                return;
            }
            $.each(locations, function (idx, item) {
                if (!item || !item.id) return;
                customerLocationsMap[item.id] = item;
                var label = $.trim(item.location_name || item.warehouse_name || item.address_name || ("Location " + item.id));
                var $opt = $("<option/>", { value: item.id, text: label });
                if (item.location_id) {
                    $opt.attr("data-location-id", item.location_id);
                }
                if (item.code) {
                    $opt.attr("data-code", item.code);
                }
                $location.append($opt);
            });
            var firstId = locations[0] && locations[0].id ? locations[0].id : "";
            if (firstId) {
                $location.val(firstId).trigger("change");
            }
        }

        function fetchCustomerLocations(customerId) {
            renderCustomerLocations([]);
            if (!customerId) return;
            var requestData = {};
            requestData.company_id = customerId;
            requestData[csrfName] = csrfHash;
            $.ajax({
                url: customerLocationsUrl,
                type: "POST",
                dataType: "json",
                data: requestData,
                success: function (res) {
                    if (res && res.csrfHash) {
                        csrfHash = res.csrfHash;
                    }
                    if (res && res.status === "success" && $.isArray(res.locations)) {
                        renderCustomerLocations(res.locations);
                    }
                }
            });
        }

        function setOtpStatus(verified, message) {
            if (verified) {
                $("#otp_status_badge").removeClass("label-default label-danger").addClass("label-success").text("Verified");
                $("#otp_feedback").removeClass("text-danger").addClass("text-success").text(message || "OTP verified");
            } else {
                $("#otp_status_badge").removeClass("label-success").addClass("label-default").text("Not verified");
                if (message) {
                    $("#otp_feedback").removeClass("text-success").addClass("text-danger").text(message);
                } else {
                    $("#otp_feedback").text("");
                }
            }
        }

        function setReportSendStatus(sent, message) {
            if (sent) {
                $("#report_send_status_badge").removeClass("label-default").addClass("label-success").text("Sent");
                $("#report_send_feedback").removeClass("text-danger").addClass("text-success").text(message || "Report sent");
                $("#otp_section_wrapper").stop(true, true).slideDown(150);
            } else {
                $("#report_send_status_badge").removeClass("label-success").addClass("label-default").text("Not sent");
                if (message) {
                    $("#report_send_feedback").removeClass("text-success").addClass("text-danger").text(message);
                } else {
                    $("#report_send_feedback").text("");
                }
                $("#otp_section_wrapper").hide();
            }
        }

        function resetOtpVerification() {
            $("#otp_challenge_id").val("");
            $("#otp_verification_token").val("");
            $("#otp_verified_channel").val("");
            setOtpStatus(false, "");
        }

        function resetReportSendState() {
            $("#report_sent_confirmed").val("");
            setReportSendStatus(false, "");
            resetOtpVerification();
        }

        function prefillOtpDestination() {
            var customerId = $("#customer_id").val();
            var channel = $("#otp_channel").val();
            if (!customerId || !customerAddresses[customerId]) {
                $("#otp_destination").val("");
                return;
            }
            if (channel === "email") {
                $("#otp_destination").val($.trim(customerAddresses[customerId].email || ""));
            } else {
                $("#otp_destination").val($.trim(customerAddresses[customerId].phone || ""));
            }
        }

        function clearPmGridValues() {
            $("#pm_log_table tbody tr.pm-data-row .pm-cell").val("");
            $("#pm_log_grid_json").val("");
        }

        function applyPmRowsToGrid(rows) {
            if (!rows || !rows.length) {
                return;
            }
            $("#pm_log_table tbody tr.pm-data-row").each(function () {
                var $row = $(this);
                var section = $.trim(($row.data("section") || "").toString()).toLowerCase();
                var parameter = $.trim(($row.data("parameter") || "").toString()).toLowerCase();

                for (var i = 0; i < rows.length; i++) {
                    var item = rows[i] || {};
                    var importedSection = $.trim((item.section || "").toString()).toLowerCase();
                    var importedParameter = $.trim((item.parameter || "").toString()).toLowerCase();

                    if (section === importedSection && parameter === importedParameter) {
                        $row.find('.pm-cell[data-ckt="ckt_01"]').val(item.ckt_01 || "");
                        $row.find('.pm-cell[data-ckt="ckt_02"]').val(item.ckt_02 || "");
                        $row.find('.pm-cell[data-ckt="ckt_03"]').val(item.ckt_03 || "");
                        $row.find('.pm-cell[data-ckt="ckt_04"]').val(item.ckt_04 || "");
                        break;
                    }
                }
            });
        }

        function loadCustomerPmGrid(customerId) {
            if (!customerId) return;
            var requestData = {};
            requestData.customer_id = customerId;
            requestData[csrfName] = csrfHash;

            $.ajax({
                url: customerPmGridUrl,
                type: "POST",
                dataType: "json",
                data: requestData,
                success: function (res) {
                    if (res && res.csrfHash) {
                        csrfHash = res.csrfHash;
                    }
                    clearPmGridValues();
                    if (res && res.status === "success" && $.isArray(res.grid_rows) && res.grid_rows.length) {
                        applyPmRowsToGrid(res.grid_rows);
                    }
                },
                error: function () {
                    clearPmGridValues();
                }
            });
        }

        function togglePmGridByCustomer() {
            var customerId = $("#customer_id").val();
            var hasCustomer = !!customerId;
            if (hasCustomer) {
                var customerName = (customerAddresses[customerId] && customerAddresses[customerId].name)
                    ? customerAddresses[customerId].name
                    : $("#customer_id option:selected").text();
                $("#pm_log_customer_name").text($.trim(customerName));
                $("#pm_log_customer_hint").hide();
                $("#pm_log_customer_title").show();
                $("#pm_log_grid_wrapper").show();
            } else {
                $("#pm_log_customer_name").text("");
                $("#pm_log_customer_title").hide();
                $("#pm_log_grid_wrapper").hide();
                $("#pm_log_customer_hint").show();
            }
        }

        $("#customer_id").on("change", function () {
            var id = $(this).val();
            $("#job_site_name").val("");
            $("#job_site_address").val("");
            if (id !== previousCustomerId) {
                clearPmGridValues();
                previousCustomerId = id;
            }
            togglePmGridByCustomer();
            loadCustomerPmGrid(id);
            fetchCustomerLocations(id);
            prefillOtpDestination();
            resetReportSendState();
            updateServiceReportNo(true);

        });

        $("#customer_location_id").on("change", function () {
            var locationId = $(this).val();
            var item = (locationId && customerLocationsMap[locationId]) ? customerLocationsMap[locationId] : null;
            if (!item) {
                $("#job_site_name").val("");
                $("#job_site_address").val("");
                return;
            }
            var addressText = buildAddressText(item);
            var siteName = $.trim(item.location_name || item.warehouse_name || item.address_name || "");
            if (siteName) {
                $("#job_site_name").val(siteName);
            }
            $("#job_site_address").val(addressText);
            updateServiceReportNo(true);
        });

        $("#otp_channel").on("change", function () {
            resetOtpVerification();
            prefillOtpDestination();
        });

        $("#service_type, input[name=\"service_report_no\"], input[name=\"service_date\"], #job_site_name, #job_site_address, #eqpt_tag_no, #model_no, #serial_no").on("change keyup", function () {
            if ($("#report_sent_confirmed").val()) {
                resetReportSendState();
            }
            if ($(this).attr('name') === 'service_date') {
                updateServiceReportNo();
            }
        });

        $("#btn_send_report_customer").on("click", function () {
            var customerId = $("#customer_id").val();
            if (!customerId) {
                setReportSendStatus(false, "Please select customer first");
                return;
            }
            serializePmLogGrid();
            $("#report_send_feedback").removeClass("text-danger text-success").text("Sending report...");
            var formData = $("form[role=\"form\"]").serializeArray();
            var payload = {};
            for (var i = 0; i < formData.length; i++) {
                payload[formData[i].name] = formData[i].value;
            }
            payload[csrfName] = csrfHash;
            $.ajax({
                url: sendServiceReportToCustomerUrl,
                type: "POST",
                dataType: "json",
                data: payload,
                success: function (res) {
                    if (res && res.csrfHash) {
                        csrfHash = res.csrfHash;
                    }
                    if (res && res.status === "success") {
                        $("#report_sent_confirmed").val("1");
                        setReportSendStatus(true, res.dispatch_message || res.message || "Report sent");
                    } else {
                        $("#report_sent_confirmed").val("");
                        setReportSendStatus(false, res && res.message ? res.message : "Failed to send report");
                    }
                },
                error: function () {
                    $("#report_sent_confirmed").val("");
                    setReportSendStatus(false, "Failed to send report");
                }
            });
        });

        $("#btn_send_otp").on("click", function () {
            var channel = $("#otp_channel").val();
            var destination = $.trim($("#otp_destination").val());
            var customerId = $("#customer_id").val();
            if (!$("#report_sent_confirmed").val()) {
                setOtpStatus(false, "Send report to customer first");
                return;
            }
            if (!customerId) {
                setOtpStatus(false, "Please select customer first");
                return;
            }
            if (!destination) {
                setOtpStatus(false, "Destination is required");
                return;
            }
            resetOtpVerification();
            $("#otp_feedback").removeClass("text-danger text-success").text("Sending OTP...");
            var data = {};
            data.channel = channel;
            data.destination = destination;
            data.customer_id = customerId;
            data.context_ref = $("#otp_context_ref").val();
            data[csrfName] = csrfHash;
            $.ajax({
                url: sendServiceReportOtpUrl,
                type: "POST",
                dataType: "json",
                data: data,
                success: function (res) {
                    if (res && res.csrfHash) {
                        csrfHash = res.csrfHash;
                    }
                    if (res && res.status === "success") {
                        $("#otp_challenge_id").val(res.challenge_id || "");
                        if (res.context_ref) {
                            $("#otp_context_ref").val(res.context_ref);
                        }
                        $("#otp_feedback").removeClass("text-danger").addClass("text-success").text(res.message || "OTP sent");
                    } else {
                        setOtpStatus(false, res && res.message ? res.message : "Failed to send OTP");
                    }
                },
                error: function () {
                    setOtpStatus(false, "Failed to send OTP");
                }
            });
        });

        $("#btn_verify_otp").on("click", function () {
            var challengeId = $("#otp_challenge_id").val();
            var otpCode = $.trim($("#otp_code").val());
            if (!challengeId) {
                setOtpStatus(false, "Send OTP first");
                return;
            }
            if (!otpCode || otpCode.length !== 6) {
                setOtpStatus(false, "Enter valid 6-digit OTP");
                return;
            }
            var data = {};
            data.challenge_id = challengeId;
            data.otp_code = otpCode;
            data.context_ref = $("#otp_context_ref").val();
            data.channel = $("#otp_channel").val();
            data[csrfName] = csrfHash;
            $.ajax({
                url: verifyServiceReportOtpUrl,
                type: "POST",
                dataType: "json",
                data: data,
                success: function (res) {
                    if (res && res.csrfHash) {
                        csrfHash = res.csrfHash;
                    }
                    if (res && res.status === "success") {
                        $("#otp_verification_token").val(res.verification_token || "");
                        $("#otp_verified_channel").val(res.channel || $("#otp_channel").val());
                        setOtpStatus(true, res.message || "OTP verified");
                    } else {
                        setOtpStatus(false, res && res.message ? res.message : "OTP verification failed");
                    }
                },
                error: function () {
                    setOtpStatus(false, "OTP verification failed");
                }
            });
        });

        togglePmGridByCustomer();
        if (previousCustomerId) {
            loadCustomerPmGrid(previousCustomerId);
            fetchCustomerLocations(previousCustomerId);
        }
        updateServiceReportNo(false);
        prefillOtpDestination();
    });

    $(function () {
        function initSignaturePad(canvasSelector, hiddenFieldSelector) {
            var canvas = document.querySelector(canvasSelector);
            if (!canvas) return;

            function resizeCanvas() {
                var rect = canvas.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
            }
            resizeCanvas();
            $(window).on("resize", resizeCanvas);

            var ctx = canvas.getContext("2d");
            ctx.strokeStyle = "#000";
            ctx.lineWidth = 2;
            ctx.lineCap = "round";

            var drawing = false;
            var lastPos = { x: 0, y: 0 };

            function getPos(e) {
                var rect = canvas.getBoundingClientRect();
                if (e.touches && e.touches.length) {
                    return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
                }
                return { x: e.clientX - rect.left, y: e.clientY - rect.top };
            }

            function startDraw(e) {
                e.preventDefault();
                drawing = true;
                lastPos = getPos(e);
            }

            function draw(e) {
                if (!drawing) return;
                e.preventDefault();
                var pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(lastPos.x, lastPos.y);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                lastPos = pos;
            }

            function endDraw() {
                if (!drawing) return;
                drawing = false;
                if (hiddenFieldSelector) {
                    $(hiddenFieldSelector).val(canvas.toDataURL("image/png"));
                }
            }

            canvas.addEventListener("mousedown", startDraw, false);
            canvas.addEventListener("mousemove", draw, false);
            canvas.addEventListener("mouseup", endDraw, false);
            canvas.addEventListener("mouseleave", endDraw, false);

            canvas.addEventListener("touchstart", startDraw, false);
            canvas.addEventListener("touchmove", draw, false);
            canvas.addEventListener("touchend", endDraw, false);
        }

        initSignaturePad("#engineer_signature_pad", "#engineer_signature_data");
        initSignaturePad("#customer_signature_pad", "#customer_signature_data");

        $(".signature-clear-btn").on("click", function () {
            var target = $(this).data("target");
            var canvas = document.querySelector(target);
            if (!canvas) return;
            var ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (target === "#engineer_signature_pad") {
                $("#engineer_signature_data").val("");
            } else if (target === "#customer_signature_pad") {
                $("#customer_signature_data").val("");
            }
        });
    });

    $(function () {
        function fetchEquipmentDetails() {
            var companyId = $.trim($("#customer_id").val() || "");
            var locationId = $.trim($("#customer_location_id option:selected").data("location-id") || "");
            var equipmentTag = $.trim($("#eqpt_tag_no").val() || "");

            if (!companyId || !locationId) {
                $("#model_no, #serial_no, #activity, #action_list").val("");
                if (!companyId) {
                    $("#eqpt_tag_no").val("");
                }
                return;
            }

            var requestData = {};
            requestData.equipment_tag = equipmentTag;
            requestData.company_id = companyId;
            requestData.location_id = locationId;
            requestData[csrfName] = csrfHash;

            $.ajax({
                url: equipmentDetailsUrl,
                type: "POST",
                dataType: "json",
                data: requestData,
                success: function (res) {
                    if (res && res.csrfHash) {
                        csrfHash = res.csrfHash;
                    }
                    if (!res || res.status !== "success" || !res.details) {
                        $("#model_no, #serial_no, #activity, #action_list").val("");
                        return;
                    }
                    var d = res.details;
                    if (d.eqpt_no) {
                        $("#eqpt_tag_no").val(d.eqpt_no);
                    }
                    $("#model_no").val(d.model_no || "");
                    $("#serial_no").val(d.serial_no || "");
                    $("#activity").val(d.activity || "");
                    $("#action_list").val(d.action_list || "");
                    if (d.floor_details && !$("#job_site_address").val()) {
                        $("#job_site_address").val(d.floor_details);
                    }
                }
            });
        }

        $("#customer_id, #customer_location_id").on("change", fetchEquipmentDetails);
        $("#eqpt_tag_no").on("change keyup", fetchEquipmentDetails);
        fetchEquipmentDetails();
    });

    $(function () {
        $("form[role=\"form\"]").on("submit", function () {
            serializePmLogGrid();
            if (!$("#report_sent_confirmed").val()) {
                $("#report_send_status_badge").removeClass("label-success").addClass("label-default").text("Not sent");
                $("#report_send_feedback").removeClass("text-success").addClass("text-danger").text("Please send report to customer before submit");
                return false;
            }
            if (!$("#otp_verification_token").val()) {
                $("#otp_feedback").removeClass("text-success").addClass("text-danger").text("Please verify OTP before saving report");
                return false;
            }
        });
    });
})(jQuery);
