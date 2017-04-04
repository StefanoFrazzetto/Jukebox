/**
 * Created by Vittorio on 11/01/2017.
 */

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

//region Burner
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
//endregion

$(document).ready(function () {
    player.callback(player.onTrackChange);
});