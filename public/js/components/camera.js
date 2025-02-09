import * as posenet from "@tensorflow-models/posenet";
import {
    drawKeypoints,
    drawSkeleton,
    isMobile,
    toggleLoadingUI,
} from "./index";

const videoWidth = 600;
const videoHeight = 500;

const poseNetConfig = {
    architecture: "MobileNetV1",
    outputStride: 16,
    inputResolution: isMobile() ? 257 : 513,
    multiplier: isMobile() ? 0.75 : 1.0,
    quantBytes: 2,
    minPoseConfidence: isMobile() ? 0.2 : 0.15,
    minPartConfidence: isMobile() ? 0.6 : 0.5,
    maxPoseDetections: 1,
    nmsRadius: 20,
};

export let formData = {
    data: {},
};

let detectionLoop = true;
let capturedImage = null;

async function setupCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        throw new Error(
            "Browser API navigator.mediaDevices.getUserMedia not available"
        );
    }

    const video = document.getElementById("video");
    video.width = videoWidth;
    video.height = videoHeight;

    const stream = await navigator.mediaDevices.getUserMedia({
        audio: false,
        video: {
            facingMode: "user",
            width: isMobile() ? { ideal: 360 } : videoWidth,
            height: isMobile() ? { ideal: 270 } : videoHeight,
        },
    });
    video.srcObject = stream;

    return new Promise((resolve) => {
        video.onloadedmetadata = () => {
            resolve(video);
        };
    });
}

async function loadVideo() {
    const video = await setupCamera();
    video.play();
    return video;
}

function detectPoseInRealTime(video, net) {
    const canvas = document.getElementById("output");
    const ctx = canvas.getContext("2d");
    const flipPoseHorizontal = true;

    canvas.width = videoWidth;
    canvas.height = videoHeight;

    async function poseDetectionFrame() {
        if (!detectionLoop) return;

        const pose = await net.estimateSinglePose(video, {
            flipHorizontal: flipPoseHorizontal,
        });

        ctx.clearRect(0, 0, videoWidth, videoHeight);

        if (pose.score >= poseNetConfig.minPoseConfidence) {
            ctx.save();
            ctx.scale(-1, 1);
            ctx.translate(-videoWidth, 0);
            ctx.drawImage(video, 0, 0, videoWidth, videoHeight);
            ctx.restore();

            drawKeypoints(pose.keypoints, poseNetConfig.minPartConfidence, ctx);
            drawSkeleton(pose.keypoints, poseNetConfig.minPartConfidence, ctx);

            const requiredKeypoints = pose.keypoints.filter((point) =>
                [
                    "nose",
                    "leftEye",
                    "rightEye",
                    "leftShoulder",
                    "rightShoulder",
                    "leftElbow",
                    "rightElbow",
                    "leftWrist",
                    "rightWrist",
                    "leftHip",
                    "rightHip",
                    "leftKnee",
                    "rightKnee",
                    "leftAnkle",
                    "rightAnkle",
                ].includes(point.part)
            );

            const allKeypointsValid = requiredKeypoints.every((point) => {
                if (point.part === "rightAnkle" || point.part === "leftAnkle") {
                    return point.score > 0.7;
                }
                return point.score >= 0.9;
            });

            if (allKeypointsValid) {
                detectionLoop = false;
                video.pause();
                await handleImageCapture(pose.keypoints);
                return;
            }
        }

        if (detectionLoop) {
            requestAnimationFrame(poseDetectionFrame);
        }
    }

    async function handleImageCapture(keypoints) {
        detectionLoop = false;
        // const videoElement = video;
        // videoElement.pause();
        const stream = video.srcObject;
        const tempCanvas = document.createElement("canvas");
        tempCanvas.width = video.videoWidth;
        tempCanvas.height = video.videoHeight;
        const tempCtx = tempCanvas.getContext("2d");

        tempCtx.save();

        tempCtx.scale(-1, 1);
        tempCtx.translate(-tempCanvas.width, 0);
        tempCtx.drawImage(video, 0, 0);

        tempCtx.restore();

        const imageData = tempCanvas.toDataURL("image/jpeg", 0.95);
        formData.data.image = imageData;

        capturedImage = new Image();
        console.log("captured image", capturedImage);

        await new Promise((resolve, reject) => {
            capturedImage.onload = resolve;
            capturedImage.onerror = reject;
            capturedImage.src = imageData;
        });
        await processCapturedImage(imageData, keypoints);
    }

    function showCapturedImage(imageData, keypoints) {
        ctx.resetTransform();
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.drawImage(capturedImage, 0, 0, canvas.width, canvas.height);

        addRetakeAndContinueButtons(imageData, keypoints);
    }

    function addRetakeAndContinueButtons(imageData, keypoints) {
        $(".button-container").remove();

        const buttonContainer = $("<div>", {
            class: "button-container",
            css: {
                textAlign: "center",
                marginTop: "20px",
            },
        });

        const retakeButton = $("<button>", {
            text: "Retake",
            class: "btn btn-secondary",
            click: () => retake(),
        });

        const continueButton = $("<button>", {
            text: "Continue",
            class: "btn btn-primary",
            css: { marginLeft: "10px" },
            click: () => continueCapt(imageData, keypoints),
        });

        buttonContainer.append(retakeButton, continueButton);

        $("#step2").append(buttonContainer);
    }

    function retake() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        detectionLoop = false;
        capturedImage = null;

        if (video.srcObject) {
            const tracks = video.srcObject.getTracks();
            tracks.forEach((track) => track.stop());
            video.srcObject = null;
        }

        if (guiState.net) {
            guiState.net.dispose();
        }

        bindPage()
            .then(() => {
                console.log("Camera and pose detection restarted successfully");
            })
            .catch((error) => {
                console.error("Error restarting camera:", error);
                showAlert(
                    "Error restarting camera. Please refresh the page.",
                    "error"
                );
            });
    }

    async function processCapturedImage(imageData, keypoints) {
        try {
            handleValidPose(
                imageData,
                keypoints
            );
        } catch (error) {
            showAlert("Error processing image", "error");
            console.error("Error processing image:", error);
            retake();
        }
    }

    function handleValidPose(
        imageData,
        keypoints
    ) {
    
        const rightEye = keypoints.find((point) => point.part === "rightEye");
        const leftEye = keypoints.find((point) => point.part === "leftEye");

        const leftShoulder = keypoints.find(
            (point) => point.part === "leftShoulder"
        );
        const rightShoulder = keypoints.find(
            (point) => point.part === "rightShoulder"
        );
        const nose = keypoints.find((point) => point.part === "nose");
        const leftElbow = keypoints.find((point) => point.part === "leftElbow");
        const rightElbow = keypoints.find(
            (point) => point.part === "rightElbow"
        );
        const leftWrist = keypoints.find((point) => point.part === "leftWrist");
        const rightWrist = keypoints.find(
            (point) => point.part === "rightWrist"
        );
        const leftHip = keypoints.find((point) => point.part === "leftHip");
        const rightHip = keypoints.find((point) => point.part === "rightHip");
        const leftKnee = keypoints.find((point) => point.part === "leftKnee");
        const rightKnee = keypoints.find((point) => point.part === "rightKnee");
        const leftAnkle = keypoints.find((point) => point.part === "leftAnkle");
        const rightAnkle = keypoints.find(
            (point) => point.part === "rightAnkle"
        );

        formData.data = {
            rightEye: {
                x: rightEye.position.x,
                y: rightEye.position.y,
                score: rightEye.score,
            },
            leftEye: {
                x: leftEye.position.x,
                y: leftEye.position.y,
                score: leftEye.score,
            },
            nose: {
                x: nose.position.x,
                y: nose.position.y,
                score: nose.score,
            },
            rightShoulder: {
                x: rightShoulder.position.x,
                y: rightShoulder.position.y,
                score: rightShoulder.score,
            },
            leftShoulder: {
                x: leftShoulder.position.x,
                y: leftShoulder.position.y,
                score: leftShoulder.score,
            },
            leftElbow: {
                x: leftElbow.position.x,
                y: leftElbow.position.y,
                score: leftElbow.score,
            },
            rightElbow: {
                x: rightElbow.position.x,
                y: rightElbow.position.y,
                score: rightElbow.score,
            },
            leftWrist: {
                x: leftWrist.position.x,
                y: leftWrist.position.y,
                score: leftWrist.score,
            },
            rightWrist: {
                x: rightWrist.position.x,
                y: rightWrist.position.y,
                score: rightWrist.score,
            },
            leftHip: {
                x: leftHip.position.x,
                y: leftHip.position.y,
                score: leftHip.score,
            },
            rightHip: {
                x: rightHip.position.x,
                y: rightHip.position.y,
                score: rightHip.score,
            },
            leftKnee: {
                x: leftKnee.position.x,
                y: leftKnee.position.y,
                score: leftKnee.score,
            },
            rightKnee: {
                x: rightKnee.position.x,
                y: rightKnee.position.y,
                score: rightKnee.score,
            },
            leftAnkle: {
                x: leftAnkle.position.x,
                y: leftAnkle.position.y,
                score: leftAnkle.score,
            },
            rightAnkle: {
                x: rightAnkle.position.x,
                y: rightAnkle.position.y,
                score: rightAnkle.score,
            },
            image: imageData,
        };

        submitData();
    }

    function submitData() {
        const trialName = $("#trial_name").val();
        const height = $("#height").val();
        const weight = $("#weight").val();
        const gender = $('input[name="gender"]:checked').val();

        if (!trialName) {
            showAlert(messages.trial_name_required, "warning");
            detectionLoop = true;
            return;
        }

        if (!gender || gender === "undefined") {
            showAlert(messages.select_gender, "warning");
            detectionLoop = true;
            return;
        }

        if (
            !height ||
            height === "undefined" ||
            height === "0" ||
            height === ""
        ) {
            showAlert(messages.enter_height, "warning");
            detectionLoop = true;
            return;
        }
        if (
            !weight ||
            weight === "undefined" ||
            weight === "0" ||
            weight === ""
        ) {
            showAlert(messages.enter_weight, "warning");
            detectionLoop = true;
            return;
        }

        formData.trial_name = trialName;
        formData.height = height;
        formData.weight = weight;
        formData.gender = gender;

        console.log("formData", formData);

        $.ajax({
            url: "/trial",
            method: "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: (response) => {
                const trialId = response.trial.id;

                $(".success-message").fadeIn();
                setTimeout(() => {
                    window.location.href = `/trial/${trialId}`;
                }, 1500);
            },
            error: (error) => {
                showAlert(messages.error_saving, "error");
                console.log(error);
                detectionLoop = true;
            },
        });
    }

    function showAlert(message, type = "error") {
        $(".alert").remove();

        const $alert = $("<div>", {
            class: `alert alert-${type}`,
        });

        let icon = "";
        switch (type) {
            case "success":
                icon = '<i class="fas fa-check-circle alert-icon"></i>';
                break;
            case "error":
                icon = '<i class="fas fa-exclamation-circle alert-icon"></i>';
                break;
            case "warning":
                icon = '<i class="fas fa-exclamation-triangle alert-icon"></i>';
                break;
        }

        $alert.html(`${icon}<span>${message}</span>`);
        $("body").append($alert);

        setTimeout(() => {
            $alert.css("animation", "fadeOut 0.3s ease-out forwards");
            setTimeout(() => $alert.remove(), 300);
        }, 10000);
    }

    poseDetectionFrame();
}

export async function bindPage() {
    toggleLoadingUI(true);

    const net = await posenet.load({
        architecture: poseNetConfig.architecture,
        outputStride: poseNetConfig.outputStride,
        inputResolution: poseNetConfig.inputResolution,
        multiplier: poseNetConfig.multiplier,
        quantBytes: poseNetConfig.quantBytes,
    });

    toggleLoadingUI(false);

    try {
        const video = await loadVideo();
        detectionLoop = true;
        detectPoseInRealTime(video, net);
    } catch (e) {
        console.error("Error initializing camera:", e);
    }
}

navigator.getUserMedia =
    navigator.getUserMedia ||
    navigator.webkitGetUserMedia ||
    navigator.mozGetUserMedia;
