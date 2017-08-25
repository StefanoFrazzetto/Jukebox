<?php

require_once '../../../vendor/autoload.php';

use Lib\ICanHaz;
use Lib\MusicClasses\Album;
use Lib\MusicClasses\Artist;

$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$album = Album::getAlbum($albumID);

if ($album == null) {
    exit('No such album.');
}

$artists_ids = $album->getArtists();

$artists = [];

foreach ($artists_ids as $artist_id) {
    $artists[] = Artist::getArtist($artist_id)->getName();
}
?>


    <div class="modalHeader">
        <div class="center">
            <?php echo $album->getTitle() ?> -
            <?php echo implode(', ', $artists) ?>
        </div>
    </div>

    <div class="modalBody" id="details" data-mcs-theme="dark">
        <input type="hidden" value="<?php echo $album->getId() ?>" id="currentAlbumId">
        <img id="album_cover_img" src="<?php echo $album->getCoverUrl() ?>"/>

        <div class="mCustomScrollbar tableContainer">
            <table class=" cooltable small" id="albumDetails" data-album="<?php echo $album->getId() ?>">
                <tr class="th">
                    <th>#</th>
                    <th class="playlist_title">Title</th>
                    <th>Length</th>
                </tr>
                <?php

                $tracks = $album->getSongs();

                $differentCDs = $album->getCdCount() != 1;
                $CD = -1;
                $CDMap = [];

                if (is_array($tracks) && count($tracks) > 0) {
                    foreach ($tracks as $key => $track) {
                        if ($differentCDs) {
                            if ($track->getCd() != $CD) {
                                $CD = $track->getCd();
                                echo "<tr class='th'><th colspan='3' data-cd='", $CD, "' class='CDth'>CD ", $CD, "<span class='addTrackToPlaylist'>+</span></th></tr>";
                            }

                            $CDMap[$CD][] = $key;
                        } ?>
                        <tr class="trackRow"
                            data-track-id="<?php echo $track->getId() ?>"
                            data-track-no="<?php echo $key + 1; ?>"
                            data-album="<?php echo $albumID ?>">
                            <td class="num">
                                <?php echo $key + 1; ?>
                            </td>
                            <td class="playlist_title">
                                <?php echo $track->getTitle(); ?>
                                <span class="addTrackToPlaylist">+</span>
                            </td>
                            <td class="duration">
                                <?php echo $track->getTimeString(); ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr class="th">
                        <th colspan="3">No tracks found</th>
                    </tr>
                    <?php
                } ?>
            </table>
        </div>
        <?php
        if ($differentCDs) { // This will inject the CD/tracks map  as a javascript variable. I don't know whether it's nasty or cool.
            echo '<script>',
            'var CDMap = ', json_encode($CDMap), ';',
            '</script>';
        }
        ?>

    </div>

    <div class="modalFooter" id="details-footer">
        <div class="box-btn" onclick="player.playAlbum(<?php echo $albumID ?>)">Play</div>
        <div class="box-btn" onclick="modal.openPage('assets/modals/edit_album/?id=<?php echo $albumID ?>')">Edit</div>
        <div class="box-btn" onclick="modal.openPage('assets/modals/download_album/?id=<?php echo $albumID ?>')">
            Download
        </div>
        <div class="box-btn" onclick="player.addAlbumToPlaylist(<?php echo $albumID ?>)">Add to Playlist</div>
    </div>
<?php


ICanHaz::css('/assets/modals/album_details/style.css');
ICanHaz::js('scripts.js', true);
?>