/**
 * Created by Vittorio on 04/04/2017.
 */

var progressBar = $('.progress');

function updateProgress(timeRequired, nowTimestamp, folderSize) {
    var actualTime = new Date();
    folderSize = folderSize / 1.83;
    timeRequired = timeRequired + folderSize;
    var perc = Math.round((100 / timeRequired) * Math.round((actualTime.getTime() / 1000) - nowTimestamp));

    if ($('#download').is(":hidden")) {
        clearProgress();
    }


    progressBar.width(perc + '%');
    progressBar.html(perc + '%');

    if (perc >= 100) {
        progressBar.width('100%');
        progressBar.html('100%');
        clearProgress();
    }
}

var downloadProgress;

$('#download').on('remove', function () {
    console.log("Canceling upload");
    clearProgress();
});

function clearProgress() {
    clearInterval(downloadProgress);
}

$('#download-btn').click(function () {
    $('#details').hide();
    $('#details-footer').hide();
    $('#pre-download').show();
});

$('#download-btn-2').click(function () {
    $('#pre-download').hide();

    $('#download').show();

    console.log("Album size: " + folderSize);
    var timeRequired = (folderSize * 60) / 100;
    var nowTimestamp = new Date().getTime() / 1000;

    $.ajax({
        url: "assets/php/album_download.php?id=" + album_id
    }).done(function (data) {
        $('#download').html("<div class='download-link'>" + data + "</div>");
        $('#progressContainer').remove();
        clearProgress();
    }).fail(function () {
        alert("Something went wrong. Please try again.");
    });

    if (folderSize !== 1) {
        downloadProgress = window.setInterval(updateProgress.bind(null, timeRequired, nowTimestamp, folderSize), 1000);
    } else {
        clearProgress();
    }

});


$(document).ready(function () {
    $('#download').hide();
});