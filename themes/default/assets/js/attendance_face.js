(function () {
    var FACE_MODEL_URL = "https://justadudewhohacks.github.io/face-api.js/models";
    var modelsLoaded = false;

    function loadModels() {
        if (modelsLoaded) {
            return Promise.resolve();
        }
        return Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(FACE_MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(FACE_MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(FACE_MODEL_URL)
        ]).then(function () {
            modelsLoaded = true;
        });
    }

    function startCamera(videoEl) {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            return Promise.reject("Camera is not supported in this browser.");
        }
        return navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: "user",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        }).then(function (stream) {
            videoEl.srcObject = stream;
            return enableAutoFocus(stream).then(function () {
                return stream;
            });
        });
    }

    function enableAutoFocus(stream) {
        return new Promise(function (resolve) {
            try {
                var tracks = stream.getVideoTracks();
                if (!tracks || !tracks.length || !tracks[0].applyConstraints) {
                    return resolve();
                }

                var videoTrack = tracks[0];
                var capabilities = videoTrack.getCapabilities ? videoTrack.getCapabilities() : {};
                var advanced = [];

                if (capabilities.focusMode && capabilities.focusMode.length) {
                    advanced.push({ focusMode: "continuous" });
                }
                if (capabilities.exposureMode && capabilities.exposureMode.length) {
                    advanced.push({ exposureMode: "continuous" });
                }
                if (capabilities.whiteBalanceMode && capabilities.whiteBalanceMode.length) {
                    advanced.push({ whiteBalanceMode: "continuous" });
                }

                if (!advanced.length) {
                    return resolve();
                }

                videoTrack.applyConstraints({ advanced: advanced })
                    .then(function () { resolve(); })
                    .catch(function () { resolve(); });
            } catch (e) {
                resolve();
            }
        });
    }

    function captureDescriptor(videoEl, canvasEl) {
        var ctx = canvasEl.getContext("2d");
        ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);

        return faceapi
            .detectAllFaces(canvasEl, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptors()
            .then(function (detections) {
                if (!detections || detections.length === 0) {
                    return { error: "Face not detected" };
                }
                if (detections.length > 1) {
                    return { error: "Multiple faces detected" };
                }
                return { descriptor: Array.prototype.slice.call(detections[0].descriptor) };
            });
    }

    function bindEnrollment() {
        var startBtn = document.getElementById("startEnrollCamera");
        var captureBtn = document.getElementById("captureEnrollFace");
        var videoEl = document.getElementById("enrollVideo");
        var canvasEl = document.getElementById("enrollCanvas");
        var descriptorEl = document.getElementById("descriptor");
        var statusEl = document.getElementById("enrollStatus");

        if (!startBtn || !captureBtn || !videoEl || !canvasEl || !descriptorEl) {
            return;
        }

        loadModels().then(function () {
            statusEl.innerHTML = "Face model loaded.";
        }).catch(function () {
            statusEl.innerHTML = "Could not load face model.";
        });

        startBtn.addEventListener("click", function () {
            startCamera(videoEl).then(function () {
                statusEl.innerHTML = "Camera started.";
            }).catch(function (err) {
                statusEl.innerHTML = err;
            });
        });

        captureBtn.addEventListener("click", function () {
            captureDescriptor(videoEl, canvasEl).then(function (result) {
                if (result.error) {
                    statusEl.innerHTML = result.error;
                    descriptorEl.value = "";
                    return;
                }
                descriptorEl.value = JSON.stringify(result.descriptor);
                statusEl.innerHTML = "Face descriptor captured.";
            });
        });
    }

    function bindAttendanceCapture() {
        var allAttendanceTypeEls = document.querySelectorAll("#attendanceType");
        if (allAttendanceTypeEls.length > 1) {
            for (var k = 1; k < allAttendanceTypeEls.length; k++) {
                if (allAttendanceTypeEls[k] && allAttendanceTypeEls[k].parentNode) {
                    allAttendanceTypeEls[k].parentNode.removeChild(allAttendanceTypeEls[k]);
                }
            }
        }

        var captureBtn = document.getElementById("captureAttendanceBtn");
        var videoEl = document.getElementById("attendanceVideo");
        var canvasEl = document.getElementById("attendanceCanvas");
        var latEl = document.getElementById("latitude");
        var lngEl = document.getElementById("longitude");
        var landmarkEl = document.getElementById("landmark");
        var notesEl = document.getElementById("attendanceNotes");
        var derivedDisplayEl = document.getElementById("derivedLocationDisplay");
        var attendanceTypeEl = document.getElementById("attendanceType");
        var attendanceInBtnEl = document.getElementById("attendanceInBtn");
        var attendanceOutBtnEl = document.getElementById("attendanceOutBtn");
        var locationPickerWrapEl = document.getElementById("locationPickerWrap");
        var locationPickerEl = document.getElementById("locationPicker");
        var locationPickerHintEl = document.getElementById("locationPickerHint");
        var liveFaceStatusEl = document.getElementById("liveFaceStatus");
        var lastActivityTextEl = document.getElementById("lastActivityText");
        var liveDetectTimer = null;
        var liveFaceState = { status: "loading", descriptor: null };
        var descriptorBuffer = [];
        var maxDescriptorSamples = 3;
        var minDetectScore = 0.65;
        var kioskMode = !!(window.attendanceConfig && window.attendanceConfig.kioskMode);
        var postInFlight = false;
        var stableFaceTicks = 0;
        var kioskAutoCooldownUntil = 0;
        var KIOSK_STABLE_TICKS = 4;
        var autoSelectedFirstLocationOnce = false;
        var lastValidSelectedLocation = "";
        var attendanceButtonsLocked = true;
        var LOCATION_RESTORE_KEY = "attendance_restore_location_after_refresh";
        var pendingRestoredLocation = "";

        if (!captureBtn || !videoEl || !canvasEl || !window.attendanceConfig) {
            return;
        }

        function setCaptureButtonLabel(text) {
            if (captureBtn) {
                captureBtn.textContent = text;
            }
        }

        function getSelectedLocationLabel() {
            if (!locationPickerEl) {
                return "";
            }
            var selected = String(locationPickerEl.value || "").trim();
            return selected;
        }

        function rememberSelectedLocation() {
            var current = getSelectedLocationLabel();
            if (current) {
                lastValidSelectedLocation = current;
            }
        }

        function restorePreviousSelectedLocation() {
            if (!locationPickerEl || !lastValidSelectedLocation) {
                return "";
            }
            var targetKey = normalizeLocationKey(lastValidSelectedLocation);
            for (var i = 0; i < locationPickerEl.options.length; i++) {
                var optionValue = String(locationPickerEl.options[i].value || "");
                if (normalizeLocationKey(optionValue) === targetKey) {
                    locationPickerEl.value = optionValue;
                    return optionValue;
                }
            }
            return "";
        }

        function messageWithSelectedLocation(text) {
            var base = String(text || "");
            var selectedLocation = getSelectedLocationLabel();
            if (!selectedLocation) {
                return base;
            }
            return base + " (Selected location: " + selectedLocation + ")";
        }

        function setLastActivityText(lastType, lastAt, suggestedType, selectedOutCheckInAt, selectedInCheckOutAt) {
            if (!lastActivityTextEl) {
                return;
            }
            var suggested = String(suggestedType || "").toLowerCase();
            var outCheckInAt = String(selectedOutCheckInAt || "").trim();
            var inCheckOutAt = String(selectedInCheckOutAt || "").trim();
            if (suggested === "check_out" && outCheckInAt) {
                lastActivityTextEl.textContent = "Check In - " + outCheckInAt;
                return;
            }
            if (suggested === "check_in") {
                lastActivityTextEl.textContent = inCheckOutAt ? ("Check Out - " + inCheckOutAt) : "--";
                return;
            }
            var type = String(lastType || "").toLowerCase();
            var at = String(lastAt || "").trim();
            if (!at) {
                lastActivityTextEl.textContent = "--";
                return;
            }
            if (type === "check_out") {
                lastActivityTextEl.textContent = "Check In - " + at;
                return;
            }
            if (type === "check_in") {
                lastActivityTextEl.textContent = "Check Out - " + at;
                return;
            }
            lastActivityTextEl.textContent = "--";
        }

        function hardRefreshCurrentPage() {
            var current = window.location.pathname || "";
            var search = window.location.search || "";
            var hash = window.location.hash || "";
            var separator = search ? "&" : "?";
            window.location.replace(current + search + separator + "_r=" + Date.now() + hash);
        }

        function saveLocationForRefresh(locationName) {
            var value = String(locationName || "").trim();
            if (!value) {
                return;
            }
            try {
                window.sessionStorage.setItem(LOCATION_RESTORE_KEY, value);
            } catch (e) {
                // Ignore storage errors.
            }
        }

        function loadLocationAfterRefresh() {
            try {
                var saved = String(window.sessionStorage.getItem(LOCATION_RESTORE_KEY) || "").trim();
                if (saved) {
                    pendingRestoredLocation = saved;
                    window.sessionStorage.removeItem(LOCATION_RESTORE_KEY);
                }
            } catch (e) {
                pendingRestoredLocation = "";
            }
        }

        function syncAttendanceButtons() {
            if (!attendanceTypeEl || !attendanceInBtnEl || !attendanceOutBtnEl) {
                return;
            }
            var selected = String(attendanceTypeEl.value || "").toLowerCase();
            if (selected === "check_out") {
                attendanceOutBtnEl.classList.add("active");
                attendanceInBtnEl.classList.remove("active");
            } else {
                attendanceInBtnEl.classList.add("active");
                attendanceOutBtnEl.classList.remove("active");
            }
        }

        function setAttendanceButtonsDisabled(disabled) {
            var finalDisabled = attendanceButtonsLocked ? true : !!disabled;
            if (attendanceInBtnEl) {
                attendanceInBtnEl.disabled = finalDisabled;
            }
            if (attendanceOutBtnEl) {
                attendanceOutBtnEl.disabled = finalDisabled;
            }
        }

        if (!attendanceButtonsLocked && attendanceInBtnEl && attendanceTypeEl) {
            attendanceInBtnEl.addEventListener("click", function () {
                attendanceTypeEl.value = "auto";
                setAutoOptionLabel("check_in");
                syncAttendanceButtons();
            });
        }
        if (!attendanceButtonsLocked && attendanceOutBtnEl && attendanceTypeEl) {
            attendanceOutBtnEl.addEventListener("click", function () {
                attendanceTypeEl.value = "check_out";
                setAutoOptionLabel("check_out");
                syncAttendanceButtons();
            });
        }
        syncAttendanceButtons();
        setAttendanceButtonsDisabled(true);
        loadLocationAfterRefresh();

        function canKioskAutoSubmit() {
            if (!kioskMode || postInFlight) {
                return false;
            }
            if (Date.now() < kioskAutoCooldownUntil) {
                return false;
            }
            var la = latEl ? String(latEl.value || "").trim() : "";
            var lo = lngEl ? String(lngEl.value || "").trim() : "";
            if (!la || !lo) {
                return false;
            }
            if (locationPickerWrapEl && locationPickerWrapEl.style.display !== "none") {
                if (!locationPickerEl || !String(locationPickerEl.value || "").trim()) {
                    return false;
                }
            }
            return true;
        }

        function maybeKioskAutoSubmit() {
            if (!canKioskAutoSubmit()) {
                return;
            }
            if (liveFaceState.status !== "single" || !liveFaceState.descriptor) {
                stableFaceTicks = 0;
                return;
            }
            if (descriptorBuffer.length < maxDescriptorSamples) {
                return;
            }
            stableFaceTicks++;
            if (stableFaceTicks < KIOSK_STABLE_TICKS) {
                return;
            }
            stableFaceTicks = 0;
            kioskAutoCooldownUntil = Date.now() + 5000;
            postAttendance(liveFaceState.descriptor);
        }

        loadModels().then(function () {
            setLiveFaceStatus("Starting camera...");
            return startCamera(videoEl);
        }).then(function () {
            startLiveDetection();
            setLiveFaceStatus("Live face detection started...");
            setCaptureButtonLabel("Capture");
        }).catch(function (err) {
            setLiveFaceStatus("Camera failed to start. Tap Retake.");
            setCaptureButtonLabel("Retake");
            if (err) {
                showMessage("error", err);
            }
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                if (latEl) {
                    latEl.value = position.coords.latitude;
                }
                if (lngEl) {
                    lngEl.value = position.coords.longitude;
                }
                autoFillLandmark(position.coords.latitude, position.coords.longitude);
                refreshDerivedLocation();
            }, function () {
                showMessage("error", "Location permission denied.");
            });
        }

        captureBtn.addEventListener("click", function () {
            if (postInFlight) {
                return;
            }
            if (!videoEl.srcObject) {
                setCaptureButtonLabel("Retake");
                startCamera(videoEl).then(function () {
                    startLiveDetection();
                    setLiveFaceStatus("Live face detection started...");
                }).catch(function (err) {
                    showMessage("error", err);
                });
                return;
            }
            if (liveFaceState.status === "none") {
                restorePreviousSelectedLocation();
                showMessage("error", messageWithSelectedLocation("Face not detected"));
                setCaptureButtonLabel("Retake");
                return;
            }
            if (liveFaceState.status === "multiple") {
                restorePreviousSelectedLocation();
                showMessage("error", messageWithSelectedLocation("Multiple faces detected"));
                setCaptureButtonLabel("Retake");
                return;
            }
            if (liveFaceState.status === "single" && liveFaceState.descriptor) {
                return postAttendance(liveFaceState.descriptor);
            }

            captureDescriptor(videoEl, canvasEl).then(function (result) {
                if (result.error) {
                    restorePreviousSelectedLocation();
                    showMessage("error", messageWithSelectedLocation(result.error));
                    setCaptureButtonLabel("Retake");
                    return;
                }
                postAttendance(result.descriptor);
            });
        });

        function postAttendance(descriptor) {
            if (postInFlight) {
                return;
            }
            postInFlight = true;
            setCaptureButtonLabel("Submitting...");
            var payload = {};
            payload.descriptor = JSON.stringify(descriptor);
            payload.latitude = latEl ? latEl.value : "";
            payload.longitude = lngEl ? lngEl.value : "";
            payload.landmark = landmarkEl ? landmarkEl.value : "";
            payload.notes = notesEl ? notesEl.value : "";
            payload.attendance_type = attendanceTypeEl ? attendanceTypeEl.value : "auto";
            payload.selected_derived_location = locationPickerEl ? normalizeLocationLabel(locationPickerEl.value) : "";
            payload[window.attendanceConfig.csrfName] = window.attendanceConfig.csrfHash;

            $.ajax({
                url: window.attendanceConfig.postUrl,
                type: "POST",
                dataType: "json",
                data: payload,
                success: function (res) {
                    if (res && res.status === "success") {
                        applyLocationOptions(res.location_options, !!res.requires_location_selection);
                        showMessage("success", res.message || "Attendance marked");
                        setCaptureButtonLabel("Capture");
                        if (attendanceTypeEl && res.next_action) {
                            var na = String(res.next_action).toLowerCase();
                            if (na === "check_in") {
                                attendanceTypeEl.value = "auto";
                                setAutoOptionLabel("check_in");
                            } else if (na === "check_out") {
                                attendanceTypeEl.value = "check_out";
                                setAutoOptionLabel("check_out");
                            }
                        }
                        setTimeout(function () {
                            hardRefreshCurrentPage();
                        }, 700);
                    } else {
                        if (res) {
                            applyLocationOptions(res.location_options, !!res.requires_location_selection);
                        }
                        var errorText = (res && res.message) ? String(res.message) : "Face not recognized by system";
                        if (normalizeLocationHintMessage(errorText)) {
                            setCaptureButtonLabel("Retake");
                            return;
                        }
                        var restoredLocation = restorePreviousSelectedLocation();
                        if (String(errorText).toLowerCase().indexOf("face not recognized") !== -1 && restoredLocation) {
                            showMessage("info", "Face not recognized. Reloading with selected location: " + restoredLocation);
                            saveLocationForRefresh(restoredLocation);
                            setTimeout(function () {
                                hardRefreshCurrentPage();
                            }, 600);
                            return;
                        }
                        showMessage("error", messageWithSelectedLocation(errorText));
                        setCaptureButtonLabel("Retake");
                    }
                },
                error: function () {
                    restorePreviousSelectedLocation();
                    showMessage("error", messageWithSelectedLocation("Request failed, please try again."));
                    setCaptureButtonLabel("Retake");
                },
                complete: function () {
                    postInFlight = false;
                }
            });
        }

        var deriveTimer = null;
        function refreshDerivedLocation() {
            if (!derivedDisplayEl || !window.attendanceConfig || !window.attendanceConfig.deriveUrl) {
                return;
            }
            if (deriveTimer) {
                clearTimeout(deriveTimer);
            }
            deriveTimer = setTimeout(function () {
                var payload = {};
                payload.latitude = latEl ? latEl.value : "";
                payload.longitude = lngEl ? lngEl.value : "";
                payload.landmark = landmarkEl ? landmarkEl.value : "";
                payload.selected_derived_location = locationPickerEl ? normalizeLocationLabel(locationPickerEl.value) : "";
                payload[window.attendanceConfig.csrfName] = window.attendanceConfig.csrfHash;

                derivedDisplayEl.value = "Deriving...";
                $.ajax({
                    url: window.attendanceConfig.deriveUrl,
                    type: "POST",
                    dataType: "json",
                    data: payload,
                    success: function (res) {
                        if (res && res.status === "success") {
                            applyLocationOptions(res.location_options, !!res.requires_location_selection);
                            var derivedValue = (res.derived_location !== null && typeof res.derived_location !== "undefined")
                                ? String(res.derived_location)
                                : "";
                            derivedDisplayEl.value = derivedValue ? derivedValue : "None";
                            applySuggestedAttendanceType(res.suggested_attendance_type);
                            setLastActivityText(
                                res.last_activity_type,
                                res.last_activity_at,
                                res.suggested_attendance_type,
                                res.selected_out_checkin_at,
                                res.selected_in_checkout_at
                            );
                            setAttendanceButtonsDisabled(false);
                        } else {
                            if (res) {
                                applyLocationOptions(res.location_options, !!res.requires_location_selection);
                            } else {
                                applyLocationOptions([], false);
                            }
                            derivedDisplayEl.value = "None";
                            setLastActivityText("", "");
                            setAttendanceButtonsDisabled(false);
                        }
                    },
                    error: function () {
                        applyLocationOptions([], false);
                        derivedDisplayEl.value = "None";
                        setLastActivityText("", "");
                        setAttendanceButtonsDisabled(false);
                    }
                });
            }, 350);
        }

        function applyLocationOptions(options, requiresSelection) {
            if (!locationPickerWrapEl || !locationPickerEl) {
                return;
            }
            var list = Array.isArray(options) ? options : [];
            var uniqueByName = {};
            var uniqueList = [];
            for (var u = 0; u < list.length; u++) {
                var raw = list[u] || {};
                var rawName = raw.name ? normalizeLocationLabel(String(raw.name)) : "";
                if (!rawName) {
                    continue;
                }
                var key = normalizeLocationKey(rawName);
                if (!uniqueByName[key]) {
                    uniqueByName[key] = true;
                    uniqueList.push(raw);
                }
            }
            list = uniqueList;
            if (!requiresSelection || !list.length) {
                locationPickerWrapEl.style.display = "none";
                locationPickerEl.innerHTML = '<option value="">Select nearby location</option>';
                if (locationPickerHintEl) {
                    locationPickerHintEl.style.display = "none";
                }
                setAttendanceButtonsDisabled(false);
                return;
            }
            if (list.length <= 1) {
                locationPickerWrapEl.style.display = "none";
                var singleName = (list[0] && list[0].name) ? normalizeLocationLabel(String(list[0].name)) : "";
                locationPickerEl.innerHTML = '<option value="' + escapeHtml(singleName) + '">' + escapeHtml(singleName || "Select nearby location") + '</option>';
                if (list[0] && list[0].name) {
                    locationPickerEl.value = singleName;
                }
                if (locationPickerHintEl) {
                    locationPickerHintEl.style.display = "none";
                }
                return;
            }

            var existingValue = locationPickerEl.value || "";
            var html = ['<option value="">Select nearby location</option>'];
            for (var i = 0; i < list.length; i++) {
                var item = list[i] || {};
                var siteName = item.name ? normalizeLocationLabel(String(item.name)) : "";
                if (!siteName) {
                    continue;
                }
                var distanceText = "";
                if (typeof item.distance !== "undefined" && item.distance !== null && !isNaN(parseFloat(item.distance))) {
                    distanceText = " (" + parseFloat(item.distance).toFixed(1) + "m)";
                }
                html.push('<option value="' + escapeHtml(siteName) + '">' + escapeHtml(siteName + distanceText) + '</option>');
            }
            locationPickerEl.innerHTML = html.join("");
            locationPickerWrapEl.style.display = "block";
            if (locationPickerHintEl) {
                locationPickerHintEl.style.display = "block";
            }
            forceAutoAttendanceType();

            if (existingValue) {
                locationPickerEl.value = existingValue;
            }
            if (!locationPickerEl.value && list[0] && list[0].name) {
                locationPickerEl.value = String(list[0].name);
                if (!autoSelectedFirstLocationOnce) {
                    autoSelectedFirstLocationOnce = true;
                    setAttendanceButtonsDisabled(true);
                    refreshDerivedLocation();
                }
            }
            if (pendingRestoredLocation) {
                var pendingKey = normalizeLocationKey(pendingRestoredLocation);
                for (var p = 0; p < locationPickerEl.options.length; p++) {
                    var pendingOptionValue = String(locationPickerEl.options[p].value || "");
                    if (normalizeLocationKey(pendingOptionValue) === pendingKey) {
                        locationPickerEl.value = pendingOptionValue;
                        setAttendanceButtonsDisabled(true);
                        refreshDerivedLocation();
                        showMessage("info", "Auto-selected location: " + pendingOptionValue);
                        break;
                    }
                }
                pendingRestoredLocation = "";
            }
            rememberSelectedLocation();
            setAttendanceButtonsDisabled(!String(locationPickerEl.value || "").trim());
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#39;");
        }

        function normalizeLocationLabel(value) {
            var text = String(value || "").trim();
            text = text.replace(/\s*\([\d\.,]+\s*m\)\s*$/i, "");
            text = text.replace(/[\u00A0\u200B-\u200D\uFEFF]/g, " ");
            text = text.replace(/\s+/g, " ").trim();
            return text;
        }

        function normalizeLocationKey(value) {
            return normalizeLocationLabel(value).toLowerCase();
        }

        function normalizeLocationHintMessage(text) {
            var msg = String(text || "").toLowerCase();
            return msg.indexOf("multiple nearby locations found") !== -1
                && msg.indexOf("please select one location") !== -1;
        }

        function applySuggestedAttendanceType(suggestedType) {
            if (!attendanceTypeEl) {
                return;
            }
            var type = String(suggestedType || "").toLowerCase();
            if (type !== "check_in" && type !== "check_out") {
                return;
            }
            if (type === "check_in") {
                attendanceTypeEl.value = "auto";
                setAutoOptionLabel("check_in");
                return;
            }
            attendanceTypeEl.value = "check_out";
            setAutoOptionLabel("check_out");
        }

        function forceAutoAttendanceType() {
            if (!attendanceTypeEl) {
                return;
            }
            var hasAutoOption = false;
            for (var i = 0; i < attendanceTypeEl.options.length; i++) {
                if (String(attendanceTypeEl.options[i].value || "").toLowerCase() === "auto") {
                    hasAutoOption = true;
                    break;
                }
            }
            if (hasAutoOption) {
                attendanceTypeEl.value = "auto";
                syncAttendanceButtons();
            }
        }

        function getAutoOptionEl() {
            if (!attendanceTypeEl || !attendanceTypeEl.options) {
                return null;
            }
            for (var idx = 0; idx < attendanceTypeEl.options.length; idx++) {
                if (String(attendanceTypeEl.options[idx].value || "").toLowerCase() === "auto") {
                    return attendanceTypeEl.options[idx];
                }
            }
            return null;
        }

        function setAutoOptionLabel(type) {
            var autoOption = getAutoOptionEl();
            if (!autoOption) {
                return;
            }
            var t = String(type || "").toLowerCase();
            if (t === "check_out") {
                autoOption.text = "Out";
            } else {
                autoOption.text = "In";
            }
            syncAttendanceButtons();
        }

        function getToastContainer() {
            var el = document.getElementById("attendanceToastContainer");
            if (!el) {
                el = document.createElement("div");
                el.id = "attendanceToastContainer";
                el.className = "attendance-toast-container";
                el.setAttribute("aria-live", "polite");
                document.body.appendChild(el);
            }
            return el;
        }

        function showMessage(type, text) {
            var message = typeof text === "string" ? text : String(text || "");
            var container = getToastContainer();
            var toast = document.createElement("div");
            var variant = type === "success" ? "success" : (type === "info" ? "info" : "error");
            toast.className = "attendance-toast attendance-toast-" + variant;
            toast.textContent = message;
            container.appendChild(toast);
            requestAnimationFrame(function () {
                toast.classList.add("attendance-toast-show");
            });
            var dismissMs = type === "success" ? 4000 : 5500;
            setTimeout(function () {
                toast.classList.remove("attendance-toast-show");
                setTimeout(function () {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 280);
            }, dismissMs);
        }

        function setLiveFaceStatus(text) {
            if (liveFaceStatusEl) {
                liveFaceStatusEl.innerHTML = text;
            }
        }

        function startLiveDetection() {
            if (liveDetectTimer) {
                clearInterval(liveDetectTimer);
            }
            setLiveFaceStatus("Live face detection started...");

            liveDetectTimer = setInterval(function () {
                if (!videoEl || videoEl.readyState < 2) {
                    return;
                }

                faceapi.detectAllFaces(videoEl, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptors()
                    .then(function (detections) {
                        if (!detections || detections.length === 0) {
                            liveFaceState.status = "none";
                            liveFaceState.descriptor = null;
                            descriptorBuffer = [];
                            stableFaceTicks = 0;
                            setLiveFaceStatus("No face detected");
                            return;
                        }
                        if (detections.length > 1) {
                            liveFaceState.status = "multiple";
                            liveFaceState.descriptor = null;
                            descriptorBuffer = [];
                            stableFaceTicks = 0;
                            setLiveFaceStatus("Multiple faces detected");
                            return;
                        }
                        if (!detections[0].detection || detections[0].detection.score < minDetectScore) {
                            liveFaceState.status = "loading";
                            liveFaceState.descriptor = null;
                            descriptorBuffer = [];
                            stableFaceTicks = 0;
                            setLiveFaceStatus("Face detected, hold still for better accuracy");
                            return;
                        }
                        liveFaceState.status = "single";
                        pushDescriptorSample(Array.prototype.slice.call(detections[0].descriptor));
                        liveFaceState.descriptor = getAverageDescriptor();
                        setLiveFaceStatus("Face detected successfully");
                        maybeKioskAutoSubmit();
                    })
                    .catch(function () {
                        liveFaceState.status = "loading";
                        liveFaceState.descriptor = null;
                        descriptorBuffer = [];
                        stableFaceTicks = 0;
                        setLiveFaceStatus("Detecting face...");
                    });
            }, 900);
        }

        function pushDescriptorSample(descriptor) {
            if (!descriptor || !descriptor.length) {
                return;
            }
            descriptorBuffer.push(descriptor);
            if (descriptorBuffer.length > maxDescriptorSamples) {
                descriptorBuffer.shift();
            }
        }

        function getAverageDescriptor() {
            if (!descriptorBuffer.length) {
                return null;
            }
            var length = descriptorBuffer[0].length;
            var avg = [];
            for (var i = 0; i < length; i++) {
                var sum = 0;
                for (var j = 0; j < descriptorBuffer.length; j++) {
                    sum += parseFloat(descriptorBuffer[j][i] || 0);
                }
                avg.push(sum / descriptorBuffer.length);
            }
            return avg;
        }

        function autoFillLandmark(lat, lng) {
            if (!landmarkEl) {
                return;
            }

            var latNum = parseFloat(lat);
            var lngNum = parseFloat(lng);
            if (isNaN(latNum) || isNaN(lngNum)) {
                return;
            }

            if (!landmarkEl.value) {
                landmarkEl.value = "Fetching location...";
            }

            var url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=" +
                encodeURIComponent(latNum) + "&lon=" + encodeURIComponent(lngNum);

            fetch(url, {
                method: "GET",
                headers: {
                    "Accept": "application/json",
                    "Accept-Language": "en"
                }
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error("Reverse geocode failed");
                }
                return response.json();
            }).then(function (data) {
                var address = "";
                if (data && data.display_name) {
                    address = data.display_name;
                } else if (data && data.name) {
                    address = data.name;
                }

                if (address) {
                    landmarkEl.value = address;
                    refreshDerivedLocation();
                } else if (landmarkEl.value === "Fetching location...") {
                    landmarkEl.value = "";
                    refreshDerivedLocation();
                }
            }).catch(function () {
                if (landmarkEl.value === "Fetching location...") {
                    landmarkEl.value = "";
                }
                refreshDerivedLocation();
            });
        }

        // Keep preview updated if hidden landmark is changed elsewhere.
        if (landmarkEl) {
            landmarkEl.addEventListener("change", refreshDerivedLocation);
        }
        if (locationPickerEl) {
            locationPickerEl.addEventListener("change", function () {
                rememberSelectedLocation();
                setAttendanceButtonsDisabled(true);
                refreshDerivedLocation();
            });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            bindEnrollment();
            bindAttendanceCapture();
        });
    } else {
        bindEnrollment();
        bindAttendanceCapture();
    }
})();

