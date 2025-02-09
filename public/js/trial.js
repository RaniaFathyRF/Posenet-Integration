import { bindPage, formData } from "./components/camera.js";

$(document).ready(function () {
    let faceDetection = null;
    let poseDetection = null;

    const style = $("<style>").text(`
        .countdown {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 72px;
            color: white;
            background: rgba(0,0,0,0.5);
            padding: 20px;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
    `);
    $("head").append(style);

    $("#nextStep").on("click", async function () {
        const trialName = $("#trial_name").val();
        const height = $("#height").val();
        const weight = $("#weight").val();
        const gender = $('input[name="gender"]:checked').val();

        if (!trialName) {
            showAlert(messages.enter_trial_name, "warning");
            return;
        }

        if (!gender || gender === "undefined") {
            showAlert(messages.select_gender, "warning");
            return;
        }

        if (
            !height ||
            height === "undefined" ||
            height === "0" ||
            height === ""
        ) {
            showAlert(messages.enter_height, "warning");
            return;
        }
        if (
            !weight ||
            weight === "undefined" ||
            weight === "0" ||
            weight === ""
        ) {
            showAlert(messages.enter_weight, "warning");
            return;
        }

        $("#step1").hide();
        $("#step2").show();

        try {
            bindPage();
        } catch (error) {
            console.error("Error initializing PoseDetection:", error);
        }
    });

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
        }, 3000);
    }
});
