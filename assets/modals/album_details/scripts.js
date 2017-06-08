/**
 * Created by Vittorio on 11/01/2017.
 */

$(document).ready(function () {
    var album_details = $('#albumDetails');
    var currentAlbumId = parseInt($('#currentAlbumId').val());

    album_details.find('.trackRow').click(function () {

        var _trackNo = parseInt($(this).attr('data-track-no')) - 1;
        player.playAlbum(currentAlbumId, _trackNo);
    });

    album_details.find('.trackRow .addTrackToPlaylist').click(function (e) {
        var _trackNo = parseInt($(this).closest('.trackRow').attr('data-track-no')) - 1;

        player.addAlbumSongToPlaylist(currentAlbumId, _trackNo);

        e.stopPropagation();
    });

    album_details.find('.CDth .addTrackToPlaylist').click(function (e) {
        var _CD = $(this).parent().attr('data-cd');
        player.addAlbumCdToPlaylist(currentAlbumId, _CD);

        e.stopPropagation();
    });

    //region Burner
    $('#burner_single_album').click(function () {
        burner_show_compilation_btn = true;

        try {
            if (burner_compilation === true) {
                try {
                    // If the albums IDs array exists
                    // put the album ID if not already there.
                    if ($.inArray(album_id, input_content_values) === -1) {
                        input_content_values.push(currentAlbumId);
                    }
                } catch (err) {
                    // Albums IDs array does not exist yet.
                    input_type_value = "albums";
                    input_content_values = [];
                    input_content_values.push(currentAlbumId);
                }
            } else {
                input_type_value = "albums";
                input_content_values = [currentAlbumId];
            }
        } catch (e) {
            input_type_value = "albums";
            input_content_values = [currentAlbumId];
        }

        modal.openPage('assets/modals/burner.php');
    });
    //endregion

    player.callback(player.onTrackChange);
});