<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">
    <button id="ripperCheckStatus">Check</button>
    <button id="ripperStart">Start!</button>
    <button id="ripperStop">Stop.</button>
</div>
<div class="modalFooter">
    <button id="btnBack">Back</button>
    <button class="right disabled" id="btnNext">Next</button>
</div>

<script>
    $(function () {
        var btnNext = $('#btnNext');
        var btnBack = $('#btnBack');

        var ripperCheckStatus = $('#ripperCheckStatus');
        var ripperStart = $('#ripperStart');

        btnBack.click(function () {
            uploader.previousPage();
        });

        btnNext.click(function () {
            uploader.nextPage();
        });

        ripperCheckStatus.click(function () {
            $.getJSON('/assets/API/uploader.php?action=get_ripper_status')
                .done(function (data) {
                    alert(JSON.stringify(data));
                    console.log(data);
                })
                .fail(function () {
                    error("Failed to check ripper status.");
                });

        });

        ripperStart.click(function () {
            $.getJSON('/assets/API/uploader.php?action=start_ripping&uploader_id=' + uploader.uploaderID)
                .done(function (data) {
                    alert(data.status)
                })
                .fail(function () {
                    error("Failed to start ripping.");
                });

        });
    });
</script>