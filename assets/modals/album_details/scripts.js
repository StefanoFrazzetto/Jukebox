/**
 * Created by Vittorio on 11/01/2017.
 */


$('#burner_single_album').click(function () {
    burner_show_compilation_btn = true;

    try {
        if (burner_compilation == true) {
            try {
                // If the albums IDs array exists
                // put the album ID if not already there.
                if ($.inArray(album_id, input_content_values) === -1) {
                    input_content_values.push(album_id);
                }
            } catch (err) {
                // Albums IDs array does not exist yet.
                input_type_value = "albums";
                input_content_values = [];
                input_content_values.push(album_id);
            }
        } else {
            input_type_value = "albums";
            input_content_values = [album_id];
        }
    } catch (e) {
        input_type_value = "albums";
        input_content_values = [album_id];
    }

    modal.openPage('assets/modals/burner.php');
});

var album_details = $('#albumDetails');

album_details.find('.trackRow').click(function () {
    var _albumID = parseInt($(this).closest('table').attr('data-album'));
    var _trackNo = parseInt($(this).attr('data-track-no')) - 1;
    player.playAlbum(_albumID, _trackNo);
});

album_details.find('.trackRow .addTrackToPlaylist').click(function (e) {
    var _albumID = parseInt($(this).closest('table').attr('data-album'));
    var _trackNo = parseInt($(this).closest('.trackRow').attr('data-track-no')) - 1;

    player.addAlbumSongToPlaylist(_albumID, _trackNo);

    e.stopPropagation();
});

album_details.find('.CDth .addTrackToPlaylist').click(function (e) {
    var _albumID = $(this).closest('table').attr('data-album');
    var _CD = $(this).parent().attr('data-cd');
    player.addAlbumCdToPlaylist(_albumID, _CD);

    e.stopPropagation();
});

$(document).ready(function () {
    $('#pre-download').hide();
    $('#download').hide();
    player.callback(player.onTrackChange);
});

function updateProgress(timeRequired, nowTimestamp, folderSize) {
    var actualTime = new Date();
    folderSize = folderSize / 1.83;
    timeRequired = timeRequired + folderSize;
    var perc = Math.round((100 / timeRequired) * Math.round((actualTime.getTime() / 1000) - nowTimestamp));
    console.log(perc);
    if ($('#download').is(":hidden")) {
        clearProgress();
    }


    $('.progress').width(perc + '%');
    $('.progress').html(perc + '%');

    if (perc >= 100) {
        $('.progress').width('100%');
        $('.progress').html('100%');
        console.log("Process completed");
        clearProgress();
    }
}

var downloadProgress;

$('#download').on('remove', function () {
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