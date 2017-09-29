<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 04/04/2017
 * Time: 19:12.
 */
require_once '../../../vendor/autoload.php';

use Lib\ICanHaz;
use Lib\MusicClasses\Album;

function page()
{
    $albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    $album = Album::getAlbum($albumID);

    if ($album == null) {
        exit('No such album.');
    } ?>
    <style>
        #progressBar {
            text-align: center;
        }

        .text {
            margin-bottom: 15px;
        }

        .download-box {
            margin-top: 70px;
        }

        .download-link,
        .download-body {
            text-align: center;
        }
    </style>

    <div class="modalHeader">
        <div class="center">
            Download <?php echo $album->getTitle() ?>
        </div>
    </div>

    <!-- PRE-DOWNLOAD -->
    <div class="modalBody download-body" id="pre-download">
        <img style="float: right;" src="<?php echo $album->getCoverUrl() ?>"/>
        <div class="download-box">
            <div class="text" id="album-size">Album
                size: <?php echo round($album->getAlbumFolderSize()).' MB'; ?></div>
            <div class="text" id="album-tracks">Tracks: <?php echo $album->getSongsCount(); ?></div>
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
        var album_id =  <?php echo $albumID; ?>;
        var folderSize =  <?php echo $album->getAlbumFolderSize(); ?>;
    </script>
    <?php
}

page();

ICanHaz::js(['/assets/js/progressbar.min.js', 'scripts.js'], true, true);
ICanHaz::css('../album_details/style.css');
