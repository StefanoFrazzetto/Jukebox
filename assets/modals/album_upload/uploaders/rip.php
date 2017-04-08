<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">

    <p>You can import an audio CD to the Jukebox using this feature.</p>

    <div id="ripperMain" style="margin: 50px">
        <button id="ripperStart">Start</button>
        <button id="ripperStop" class="hidden">Abort</button>

        <div id="ripperSuccess" class="hidden">
            <p>CD imported successfully!</p>

            <p>
                <button id="ripperReset">Reset</button>
            </p>

            <!--<button id="ripperCheckStatus">Check</button>-->
        </div>
    </div>

    <hr/>
    <div class="progressBar full-wide" style="margin: 0">
        <div class="progress" id="ripperProgress"></div>
    </div>
</div>

<div class="modalFooter">
    <button id="btnBack">Back</button>
    <button id="btnCancel">Cancel</button>
    <button class="right disabled" id="btnNext">Next</button>
</div>

<script>
    $(function () {
        var btnNext = $('#btnNext');
        var btnBack = $('#btnBack');

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
            $.getJSON('/assets/API/uploader.php?action=start_ripping&uploader_id=' + uploader.uploaderID)
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
</script>