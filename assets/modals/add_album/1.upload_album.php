<div class="modalHeader">Music Upload</div>

<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 315px;">
    <form action="assets/php/upload.php" id="dropzone" class="dropzone">
        <div class="fallback">
            <input name="file" type="file" multiple/>
        </div>
    </form>
</div>

<div class="modalFooter">
    <div id="uploadProgressBar" class="progressBar">
        <div id="progress" class="progress"></div>
    </div>
    <?php
    session_start();

    if (isset($_SESSION['CD']) && $_SESSION['CD'] > 1) {
        ?>
        <div class="box-btn pull-right" onclick="modal.openPage('assets/modals/add_album/2.fix_titles.php');">Cancel
        </div>
    <?php
    } ?>

    <div class="box-btn pull-right" id="nextBtn" style="display: none"
         onclick="modal.openPage('assets/modals/add_album/2.fix_titles.php');">Next
    </div>
</div>

<script src="assets/js/dropzone.js"></script>

<script>
    Dropzone.autoDiscover = false;
    var uploadedBytes = 0;

    $("#dropzone").on("remove", function () {
        //console.log('it has been destroyed');
        $("#dropzone").dropzone.reset();
        $("#dropzone").dropzone.destroy();
    });

    $(function () {
        var myDropzone = new Dropzone("#dropzone");

        myDropzone.on("totaluploadprogress", function (uploadprogress, asd, asdasd) {

            uploadprogress = (asdasd + uploadedBytes) / (asd + uploadedBytes) * 100;

            $('#progress').width(uploadprogress + "%");
        });

        myDropzone.on("success", function (file, response) {
            uploadedBytes += file.size;
        });

        myDropzone.on("queuecomplete", function (file, response) {
            $('#nextBtn').show();
        });

        myDropzone.on("sending", function (file, response) {
            $('.modalFooter .box-btn').hide();
        });
    });
</script>