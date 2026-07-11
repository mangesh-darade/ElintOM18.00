(function () {
    var FACE_MODEL_URL = "https://justadudewhohacks.github.io/face-api.js/models";
    var loaded = false;

    function loadFaceModels() {
        if (loaded) {
            return Promise.resolve();
        }
        return Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(FACE_MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(FACE_MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(FACE_MODEL_URL)
        ]).then(function () {
            loaded = true;
        });
    }

    function startCamera(videoEl) {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            return Promise.reject("Camera not supported");
        }
        return navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false
        }).then(function (stream) {
            videoEl.srcObject = stream;
        });
    }

    function captureFaceDescriptor(videoEl, canvasEl) {
        var ctx = canvasEl.getContext("2d");
        ctx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);

        return faceapi.detectAllFaces(canvasEl, new faceapi.TinyFaceDetectorOptions())
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

    function bindFaceRegister(containerId) {
        var container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        var startBtn = container.querySelector(".start-face-camera");
        var captureBtn = container.querySelector(".capture-face-descriptor");
        var videoEl = container.querySelector(".face-register-video");
        var canvasEl = container.querySelector(".face-register-canvas");
        var statusEl = container.querySelector(".face-register-status");
        var descriptorEl = container.querySelector(".face-register-descriptor");
        var imageDataEl = container.querySelector(".face-register-image-data");
        var previewEl = container.querySelector(".face-register-preview");

        if (!startBtn || !captureBtn || !videoEl || !canvasEl || !descriptorEl) {
            return;
        }

        loadFaceModels().then(function () {
            if (statusEl) {
                statusEl.innerHTML = "Face model loaded";
            }
        }).catch(function () {
            if (statusEl) {
                statusEl.innerHTML = "Face model load failed";
            }
        });

        startBtn.addEventListener("click", function () {
            if (descriptorEl && descriptorEl.value) {
                descriptorEl.value = "";
            }
            if (imageDataEl && imageDataEl.value) {
                imageDataEl.value = "";
            }
            if (previewEl) {
                previewEl.style.display = "none";
                previewEl.src = "";
            }
            startCamera(videoEl).then(function () {
                if (statusEl) {
                    statusEl.innerHTML = "Camera started. Capture face now.";
                }
            }).catch(function (err) {
                if (statusEl) {
                    statusEl.innerHTML = err;
                }
            });
        });

        captureBtn.addEventListener("click", function () {
            captureFaceDescriptor(videoEl, canvasEl).then(function (res) {
                if (res.error) {
                    descriptorEl.value = "";
                    if (previewEl) {
                        previewEl.style.display = "none";
                        previewEl.src = "";
                    }
                    if (statusEl) {
                        statusEl.innerHTML = res.error;
                    }
                    return;
                }
                descriptorEl.value = JSON.stringify(res.descriptor);
                if (imageDataEl) {
                    imageDataEl.value = canvasEl.toDataURL("image/png");
                }
                if (previewEl) {
                    previewEl.src = imageDataEl && imageDataEl.value ? imageDataEl.value : canvasEl.toDataURL("image/png");
                    previewEl.style.display = "inline-block";
                }
                if (startBtn) {
                    startBtn.innerHTML = "Retake";
                    startBtn.classList.remove("btn-info");
                    startBtn.classList.add("btn-warning");
                }
                if (statusEl) {
                    statusEl.innerHTML = "Face registered successfully";
                }
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function () {
            bindFaceRegister("create-user-face-register");
            bindFaceRegister("profile-face-register");
        });
    } else {
        bindFaceRegister("create-user-face-register");
        bindFaceRegister("profile-face-register");
    }
})();

