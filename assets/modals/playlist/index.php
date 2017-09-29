<?php
include_once '../../../vendor/autoload.php';

use Lib\ICanHaz;

ICanHaz::css('style.css');
?>

    <div class="modalHeader">
        Playlist
    </div>

    <div class="modalBody mCustomScrollbar playlistModals " data-mcs-theme="dark">
        <table class="cooltable small">
            <thead>
            <tr class="th">
                <th>Track</th>
                <th>Artist</th>
                <th>Album</th>
                <th>Length</th>
                <th></th>
            </tr>
            </thead>
            <tbody id="playlistDetails">
            <tr>
                <td colspan="6"> No songs in playlist. Add one!</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="modalFooter">
        <div class="box-btn disabled" onclick="">Save Playlist</div>
        <div class="box-btn disabled" onclick="">Load Playlist</div>
        <div class="box-btn disabled" onclick="">Download</div>
        <div class="box-btn" onclick="player.removeAllTracksFromPlaylist()">Remove all tracks</div>
    </div>

<?php ICanHaz::js('scripts.js', false, true);
