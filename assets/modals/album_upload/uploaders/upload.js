/**
 * Created by Vittorio on 08/04/2017.
 */
Dropzone.autoDiscover = false;

$(function () {
    var baseUrl = '/assets/API/uploader.php?uploader_id=' + uploader.uploaderID + '&action=upload_files';

    var btnNext = $('#btnNext');
    var btnBack = $('#btnBack');
    var btnNextCD = $('#btnNextCD');

    btnBack.click(function () {
        uploader.previousPage();
    });

    btnNext.click(function () {
        uploader.nextPage();
    });

    btnNextCD.click(function () {
        uploader.incrementCD();

        $(this).text("Add CD" + (uploader.uploadingCD + 1));

        myDropzone.options.url = baseUrl + '&cd=' + uploader.uploadingCD;
    });

    $('#btnCancel').click(function () {
        Uploader.abort();
    });

    if (uploader.tracks.length) {
        btnNext.removeClass('disabled');
        btnNextCD.removeClass('disabled');
    }

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
        url: baseUrl
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
        btnNextCD.removeClass('disabled');
    });

    myDropzone.on("sending", function () {
        btnNext.addClass('disabled');
        btnNextCD.addClass('disabled');
    });
});