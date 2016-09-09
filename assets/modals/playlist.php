<style>
    .playlistModals img {
        float: right;
        height: 150px;
        width: 150px;
        box-shadow: 0px 0px 6px 2px rgba(0, 0, 0, 0.40);
        border-radius: 5px;
    }
    
    .removeTrackFromPlaylist {
        color: white;
        cursor: pointer;
        font-weight: 100;
        float: right;
        background-color: rgba(90, 90, 90, 0.5);
        border-radius: 100%;
        width: 21px;
        height: 21px;
        text-align: center;
        line-height: 21px;
    }
    
    th .addTrackToPlaylist {
        float: none;
    }

    .album_thumb {
        height: 35px !important;
        width: 35px !important;
    }

    table.songsTable {
        width: 100%;
        margin-right: 0;
        text-align: center;
    }

</style>

<div class="modalHeader">
    Playlist
</div>
<div class="modalBody mCustomScrollbar playlistModals" data-mcs-theme="dark">
    <table class=" songsTable" id="playlistDetails">
        <tr class="th">
            <th class="">Track</th>
            <th class="">Artist</th>
            <th class="">Album</th>
            <th></th>
            <th></th>
            <th>Length</th>
        </tr>
        <tr>
            <td colspan="6"> No songs in playlist. Add one!</td>
        </tr>
    </table>
</div>

<div class="modalFooter">
    <div class="box-btn" onclick="savePlaylist()">Save Playlist</div>
    <div class="box-btn" onclick="LoadPlaylist()">Load Playlist</div>
    <div class="box-btn" onclick="downloadPlaylist()">Download</div>
    <div class="box-btn" onclick="burnPlaylist()">Burn CD</div>
    <div class="box-btn" onclick="removeAllTracksFromPlaylist()">Remove all tracks</div>
</div>

<script>
    function toHHMMSS(num) {
        var sec_num = parseInt(num, 10); // don't forget the second param
        var hours = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);

        if (hours < 10) {
            hours = "0" + hours;
        }
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        if (seconds < 10) {
            seconds = "0" + seconds;
        }
        var time = minutes + ':' + seconds;

        if (hours != 0) {
            time = hours + ':' + time;
        }

        return time;
    }

    function updateBigPlaylist() {
        $('#playlistDetails .trackRow').remove();

        playlist.forEach(function(song, key) {
            if (typeof song.length != 'undefined') {
                var time = toHHMMSS(song.length);
            } else {
                var time = 'n/a';
            }

            getAlbumDetails(parseInt(song.album), function(data) {
                var tr = $(
                    '<tr data-no="' + key + '" data-album="' + song.album + '">' +
                    '<td>' + song.title + '</td> ' +
                    '<td>' + data.artist + '</td>' +
                    '<td>' + data.title + '</td>' +
                    '<td><img class="album_thumb" src="'+data.cover+'"/></td>' +
                    '<td><i class="fa fa-trash removeTrackFromPlaylist"></i></td>' +
                    '<td class="duration">' + time + '</td> </tr>'
                    )
                .addClass('trackRow')
                .attr('data-track-no', key);
                tr.appendTo('#playlistDetails');
            });


        });


        $('#playlistDetails .trackRow').click(function() {
            var _trackNo = $(this).attr('data-no');
            //changeAlbum(_albumID, _trackNo);

            getPlaylistSong(_trackNo);
        });

        $('#playlistDetails .trackRow .removeTrackFromPlaylist').click(function(e) {
            var _albumID = $(this).closest('table').attr('data-album');
            var _trackNo = $(this).parent().parent().attr('data-track-no');

            var _albumID = $(this).closest('table').attr('data-album');

            removeSongFromPlaylist(_trackNo);

            e.stopPropagation();
            e.preventDefault();
        });

        $('#playlistDetails .CDth .addTrackToPlaylist').click(function(e) {
            var _albumID = $(this).closest('table').attr('data-album');
            var _CD = $(this).parent().attr('data-cd');

            addAlbumCDToPlaylist(_albumID, _CD);

            e.stopPropagation();
        });
    }
    updateBigPlaylist();
</script>