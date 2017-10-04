var playlistDetails = $('#playlistDetails');

function updateBigPlaylist() {
    if (player.tracks.length) {
        playlistDetails.html("");

        player.tracks.forEach(function (song, key) {
            var remove = $('<td class="text-center"><span class="removeTrackFromPlaylist mini-button" title="Remove track from playlist"><i class="material-icons">remove</i></span></td>').click(function (e) {
                player.removeSongFromPlaylistAtIndex(key);
                e.stopPropagation();
            });

            $("<tr></tr>")
                .append("<td>" + song.title + "</td>")
                .append("<td>" + song.getArtistsNames() + "</td>")
                .append("<td class='albumRow'>" + storage.getAlbum(song.album_id).title + "<img class='album_thumb right' src=" + storage.getAlbum(song.album_id).getCoverUrl() + "/></td>")
                .append("<td class='text-center'>" + song.getHHMMSS() + "</td>")
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