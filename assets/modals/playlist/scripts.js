var playlistDetails = $('#playlistDetails');

function updateBigPlaylist() {
    if (player.tracks.length) {
        playlistDetails.html("");

        player.tracks.forEach(function (song, key) {
            var remove = $('<td><i class="fa fa-trash removeTrackFromPlaylist"></i></td>').click(function (e) {
                player.removeSongFromPlaylistAtIndex(key);
                e.stopPropagation();
            });

            $("<tr></tr>")
                .append("<td>" + song.title + "</td>")
                .append("<td>" + song.getArtistsNames() + "</td>")
                .append("<td>" + storage.getAlbum(song.album_id).title + "</td>")
                .append("<td><img class='album_thumb' src='" + storage.getAlbum(song.album_id).getCoverUrl() + "'/></td>")
                .append("<td>" + song.getHHMMSS() + "</td>")
                .append(remove)
                .addClass('trackRow')
                .click(function () {
                    player.playSongAtIndex(key);
                })
                .appendTo(playlistDetails);

            //playlistDetails.append(row);
        });
    } else {
        playlistDetails.html('<tr><td colspan="6">No songs in playlist. Add one!</td></tr>');
    }
}

updateBigPlaylist();