<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">
    <div class="mCustomScrollbar" style="max-height: 200px; overflow: hidden">
        <form action="/assets/API/upload_file.php" id="dropzone" class="dropzone">
            <div class="fallback">
                <input name="file" type="file" multiple/>
            </div>
        </form>
    </div>
    <hr/>
    <div id="uploadProgressBar" class="progressBar" style="margin: inherit">
        <div id="progress" class="progress"></div>
    </div>
</div>
<div class="modalFooter">
    <button id="btnBack">Back</button>
    <button class="right disabled" id="btnNext">Next</button>
</div>

<script src="/assets/js/dropzone.js"></script>
<script>
    Dropzone.autoDiscover = false;


    $(function () {

        var btnNext = $('#btnNext');
        var btnBack = $('#btnBack');

        btnBack.click(function () {
            uploader.previousPage();
        });

        btnNext.click(function () {
            uploader.nextPage();
        });

        if (uploader.tracks.length)
            btnNext.removeClass('disabled');

        var uploadedBytes = 0;
        var dropzone = $("#dropzone");

        dropzone.on("remove", function () {
            //console.log('it has been destroyed');
            dropzone.dropzone.reset();
            dropzone.dropzone.destroy();
        });

        Dropzone.options.dropzone = {
            acceptedFiles: '.mp3,.jpg,.jpeg,.png,.gif,.wav',
            parallelUploads: 6,
            url: '/assets/API/upload_file.php?uploader_id=' + uploader.uploaderID
        };

        var myDropzone = new Dropzone("#dropzone");

//        myDropzone.url = '/assets/API/upload_file.php?uploader_id=' + uploader.uploaderID;

        myDropzone.on("totaluploadprogress", function (uploadprogress, total, current) {

            var _uploadprogress = (current + uploadedBytes) / (total + uploadedBytes) * 100;

            $('#progress').width(_uploadprogress + "%");
        });

        myDropzone.on("success", function (file) {
            uploadedBytes += file.size;
        });

        myDropzone.on("queuecomplete", function () {
            btnNext.removeClass('disabled');
        });

        myDropzone.on("sending", function () {
            btnNext.addClass('disabled');
        });
    });
</script>