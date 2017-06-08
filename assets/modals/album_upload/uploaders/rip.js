/**
 * Created by Vittorio on 08/04/2017.
 */
$(function () {
    var btnNext = $('#btnNext');
    var btnBack = $('#btnBack');
    var btnNextCD = $('#btnNextCD');

    var ripperCheckStatus = $('#ripperCheckStatus');
    var ripperStart = $('#ripperStart');
    var ripperStop = $('#ripperStop');
    var ripperSuccess = $('#ripperSuccess');
    var ripperReset = $('#ripperReset');

    var ripperProgress = $('#ripperProgress');

    function setProgressPercentage(p) {
        ripperProgress.css('width', p + '%');
        ripperProgress.text(p + '%');
    }

    function updateStatus() {
        getStatus(function (status, percentage) {
            if (status === "ripping" || status === "encoding")
                setProgressPercentage(percentage);
            else if (status === "complete") {
                alert("Process complete!");
                success();
            } else if (status === "idle") {
                alert("The ripper is in idle...");
            } else {
                error("Unknown status returned by the ripper");
            }
        });
    }

    var handler;

    function startProgress() {
        ripperStart.hide();
        ripperStop.show();
        ripperSuccess.hide();

        updateStatus();
        handler = setInterval(function () {
            updateStatus();
        }, 3000);
    }

    function stopProgress() {
        clearInterval(handler);
    }

    function success() {
        ripperStart.hide();
        ripperStop.hide();
        ripperSuccess.show();
        btnNext.removeClass("disabled");
        setProgressPercentage(100);
        stopProgress();
    }

    function getStatus(callback) {
        $.getJSON('/assets/API/uploader.php?action=get_ripper_status')
            .done(function (data) {
                //noinspection JSUnresolvedVariable
                callback(data.status, data.percentage);
            })
            .fail(function () {
                error("Failed to get ripper status.");
                stopProgress();
            });
    }

    btnBack.click(function () {
        uploader.previousPage();
    });

    btnNext.click(function () {
        uploader.nextPage();
    });

    // CHECK
    ripperCheckStatus.click(function () {
        $.getJSON('/assets/API/uploader.php?action=get_ripper_status')
            .done(function (data) {
                alert(JSON.stringify(data));
            })
            .fail(function () {
                error("Failed to check ripper status.");
            });

    });

    // START
    ripperStart.click(function () {
        $.getJSON('/assets/API/uploader.php?action=start_ripping&uploader_id=' + uploader.uploaderID + '&cd=' + uploader.uploadingCD)
            .done(function (data) {
                if (data.status === "success") {
                    startProgress();
                } else {
                    error("Failed to start the ripper. " + data.message);
                    console.error(data);
                }

            })
            .fail(function () {
                error("Failed to start ripping.");
            });

    });

    // STOP
    ripperStop.click(function () {
        $.getJSON('/assets/API/uploader.php?action=stop_ripping')
            .done(function (data) {
                console.log(data);
                if (data.status !== "error") {
                    alert(data.status);
                    stopProgress()
                } else {
                    error("Failed to abort the process.");
                    //reset();
                }
            })
            .fail(function () {
                error("Failed to stop ripping.");
            });
    });

    function reset() {
        $.getJSON('/assets/API/uploader.php?action=reset_ripping')
            .done(function () {
                ripperStart.show();
                ripperStop.hide();
                ripperSuccess.hide();
                setProgressPercentage(0);
            });
    }

    // RESET
    ripperReset.click(function () {
        reset();
    });

    // NEW CD
    btnNextCD.click(function () {
        uploader.incrementCD();

        $(this).text("Add CD" + (uploader.uploadingCD + 1));

        reset();
    });

    modal.onModalClosed = function () {
        stopProgress();
    };

    getStatus(function (status) {
        if (status === "complete") {
            success();
        } else if (status === "ripping" || status === "encoding") {
            startProgress();
        }
    });
});