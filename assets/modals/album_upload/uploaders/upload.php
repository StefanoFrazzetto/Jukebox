<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">
    <form action="/assets/php/upload.php" id="dropzone" class="dropzone">
        <div class="fallback">
            <input name="file" type="file" multiple/>
        </div>
    </form>
    <hr/>
    <div id="uploadProgressBar" class="progressBar">
        <div id="progress" class="progress"></div>
    </div>
</div>
<div class="modalFooter">
    <button id="btnBack">Back</button>

    <button class="right disabled" id="btnNext">Next</button>
</div>

<script src="/assets/js/dropzone.js"></script>
<script>
    $('#btnBack').click(function () {
        uploader.previousPage();
    });

    $('#btnNext').click(function () {
        uploader.nextPage();
    });

    Dropzone.autoDiscover = false;
    var uploadedBytes = 0;
    var dropzone = $("#dropzone");

    dropzone.on("remove", function () {
        //console.log('it has been destroyed');
        dropzone.dropzone.reset();
        dropzone.dropzone.destroy();
    });

    $(function () {
        var myDropzone = new Dropzone("#dropzone");

        myDropzone.on("totaluploadprogress", function (uploadprogress, asd, asdasd) {

            uploadprogress = (asdasd + uploadedBytes) / (asd + uploadedBytes) * 100;

            $('#progress').width(uploadprogress + "%");
        });

        myDropzone.on("success", function (file) {
            uploadedBytes += file.size;
        });

        myDropzone.on("queuecomplete", function () {
            $('#btnNext').removeClass('disabled');
        });

        myDropzone.on("sending", function () {
            $('#btnNext').addClass('disabled');
        });
    });
</script>