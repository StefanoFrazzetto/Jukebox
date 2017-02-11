<?php

require_once '../../../vendor/autoload.php';

use Lib\ICanHaz;
use Lib\MusicClasses\Album;
use Lib\MusicClasses\Artist;

$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$album = Album::getAlbum($albumID);

if ($album == null) {
    exit("No such album.");
}

$artists_ids = $album->getArtists();

$artists = [];

foreach ($artists_ids as $artist_id) {
    $artists[] = Artist::getArtist($artist_id)->getName();
}

//<editor-fold desc="Downloader Stuff">
// TODO @Stefano you should look into this
// P.S. It's possibly totally broken now. :)

$dir = '/var/www/html/jukebox/' . $albumID;
$outputFileName = preg_replace('/[^A-Za-z0-9\-]/', '', $album->getTitle()) . '.zip';
$outputFile = '/var/www/html/downloads/' . $outputFileName;


if (file_exists($outputFile)) {
    $fileExists = true;
} else {
    $fileExists = false;
}
//</editor-fold>
?>


    <div class="modalHeader">
        <div class="center">
            <?php echo $album->getTitle() ?> -
            <?php echo implode(', ', $artists) ?>
        </div>
    </div>

    <div class="modalBody" id="details" data-mcs-theme="dark">
        <img id="album_cover_img" src="<?php echo $album->getCoverUrl() ?>"/>

        <table class="mCustomScrollbar songsTable" id="albumDetails" data-album="<?php echo $album->getId() ?>">
            <tr class="th">
                <th>#</th>
                <th class="playlist_title">Title</th>
                <th>Length</th>
            </tr>
            <?php

            $tracks = $album->getTracks();

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
                    }
                    ?>
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
                <?php }
            } else { ?>
                <tr class="th">
                    <th colspan="3">No tracks found</th>
                </tr>
            <?php } ?>
        </table>
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
        <div class="box-btn" onclick="storage.deleteAlbum(<?php echo $albumID ?>)">Delete</div>
        <div class="box-btn" id="download-btn">Download</div>
        <div class="box-btn" id="burner_single_album">Burn Album</div>
        <!-- <div class="box-btn" id="burner_addto_compilation">Add to burning compilation</div> -->
        <div class="box-btn" onclick="player.addAlbumToPlaylist(<?php echo $albumID ?>)">Add to Playlist</div>
    </div>

    <!-- PRE-DOWNLOAD -->
    <div class="modalBody download-body" id="pre-download">
        <img style="float: right;" src="<?php echo $album->getCoverUrl() ?>"/>
        <div class="download-box">
            <div class="text" id="album-size">Album size: <?php echo $album->getAlbumFolderSize() . " MB"; ?></div>
            <div class="text" id="album-tracks">Tracks: <?php echo $album->getTracksCount(); ?></div>
            <div class="text" id="album-cds">CDs: <?php echo $album->getCdCount() ?></div>
            <br>
            <div class="box-btn" id="download-btn-2">Download</div>
        </div>
    </div>

    <!-- ALBUM DOWNLOAD -->
    <div class="modalBody" id="download" data-mcs-theme="dark">
        <div class="download-link">Please wait. Your download will be ready soon.</div>
        <div id="progressContainer" class="progressBar" style="width: 98%; margin-bottom: 0;">
            <div id="progressBar" class="progress" style="width: 0; padding-left: 5px;">
            </div>
        </div>
    </div>

    <!--suppress JSUnusedLocalSymbols -->
    <script type="text/javascript">
        var album_id =  <?php echo $albumID;?>;
        var folderSize =  <?php echo $album->getAlbumFolderSize();?>;
    </script>

<?php
ICanHaz::css('/assets/modals/album_details/style.css');
ICanHaz::js(['/assets/js/progressbar.min.js', 'scripts.js'], true);
?>