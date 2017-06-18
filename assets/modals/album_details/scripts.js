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

    player.callback(player.onTrackChange);
});