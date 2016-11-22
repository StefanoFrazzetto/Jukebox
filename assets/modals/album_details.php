<?php
require '../php-lib/dbconnect.php';
require '../php-lib/getMp3Length.php';

function addZeroes($digit)
{
    if ($digit < 10) {
        $digit = '0' . $digit;
    }
    return $digit;
}

function makeTimeNice($time)
{
    $minutes = floor($time / 60);
    $seconds = $time - $minutes * 60;

    return addZeroes($minutes) . ':' . addZeroes($seconds);
}

function folderSize($dir)
{
    $size = 0;
    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
    }
    return $size;
}

require '../php/get_cover.php';

$albumID = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$results = $mysqli->query("SELECT * FROM $albums WHERE id = $albumID LIMIT 1");
$result = $results->fetch_object();

$title = $result->title;
$artist = $result->artist;
$tracks = json_decode($result->tracks);
$cover_url = get_cover($albumID);

$dir = '/var/www/html/jukebox/' . $albumID;
$albumSize = number_format(folderSize($dir) / 1048576, 2);

$outputFileName = preg_replace('/[^A-Za-z0-9\-]/', '', $title) . ' - ' . preg_replace('/[^A-Za-z0-9\-]/', '', $artist) . '.zip';
$outputFile = '/var/www/html/downloads/' . $outputFileName;

$album['title'] = $title;
$album['artist'] = $artist;
$album['size'] = $albumSize;
$album['cds'] = 1;
$album['tracks'] = $tracks;


if (file_exists($outputFile)) {
    $albumSize = 1;
    $fileExists = true;
} else {
    $fileExists = false;
}
?>
<style>
    .modalBody img {
        float: right;
        height: 300px;
        width: 300px;
        box-shadow: 0px 0px 6px 2px rgba(0, 0, 0, 0.40);
        border-radius: 5px;
    }

    .addTrackToPlaylist {
        color: white;
        cursor: pointer;
        font-weight: 100;
        float: right;
        background-color: rgba(90, 90, 90, 0.5);
        border-radius: 100%;
        width: 21px;
        height: 21px;
        text-align: center;
    }

    th .addTrackToPlaylist {
        float: none;
    }

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
<script src="/assets/js/progressbar.min.js" type="text/javascript"></script>

<div class="modalHeader">
    <div class="center">
        <?php echo $artist ?> -
        <?php echo $title ?>
    </div>
</div>

<div class="modalBody" id="details" data-mcs-theme="dark">
    <img id="album_cover_img" src="<?php echo $cover_url ?>"/>

    <table class="mCustomScrollbar songsTable" id="albumDetails" data-album="<?php echo $albumID ?>">
        <tr class="th">
            <th>#</th>
            <th class="playlist_title">Title</th>
            <th>Length</th>
        </tr>
        <?php

        if (is_array($tracks) && count($tracks) > 0) {
            if (end($tracks)->cd != $tracks[0]->cd) {
                $differentCDs = true;
                $CD = -1;
                $album['cds'] = 0;
            }

            foreach ($tracks as $key => $track) {
                if (@$differentCDs) {
                    if ($track->cd != $CD) {
                        $CD = $track->cd;
                        $album['cds'] += 1;
                        echo "<tr class='th'><th colspan='3' data-cd='", $CD, "' class='CDth'>CD ", $CD, "<span class='addTrackToPlaylist'>+</span></th></tr>";
                    }

                    $CDMap[$CD][] = $key;

                }
                ?>
                <tr class="trackRow" data-track-no="<?php echo $key ?>" data-album="<?php echo $albumID ?>">
                    <td class="num">
                        <?php echo $key + 1; ?>
                    </td>
                    <td class="playlist_title">
                        <?php echo $track->title; ?>
                        <span class="addTrackToPlaylist">+</span>
                    </td>
                    <td class="duration">
                        <?php echo @makeTimeNice($track->length); ?>
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
    if (@$differentCDs) { // This will inject the CD/tracks map  as a javascript variable. I don't know whether it's nasty or cool.
        echo '<script>',
        'var CDMap = ', json_encode($CDMap), ';',
        '</script>';
    }
    ?>

</div>


<div class="modalFooter" id="details-footer">
    <div class="box-btn" onclick="changeAlbum(<?php echo $albumID ?>)">Play</div>
    <div class="box-btn" onclick="openModalPage('assets/modals/edit_album/?id=<?php echo $albumID ?>')">Edit</div>
    <div class="box-btn" onclick="deleteAlbum(<?php echo $albumID ?>)">Delete</div>
    <div class="box-btn" id="download-btn">Download</div>
    <div class="box-btn" id="burner_single_album">Burn Album</div>
    <!-- <div class="box-btn" id="burner_addto_compilation">Add to burning compilation</div> -->
    <div class="box-btn" onclick="addAlbumToPlaylist(<?php echo $albumID ?>)">Add to Playlist</div>
</div>

<!-- PRE-DOWNLOAD -->
<div class="modalBody download-body" id="pre-download">
    <img style="float: right;" src="<?php echo $cover_url ?>"/>
    <div class="download-box">
        <div class="text" id="album-size">Album size: <?php echo $album['size'] . " MB"; ?></div>
        <div class="text" id="album-tracks">Tracks: <?php echo count($album['tracks']); ?></div>
        <div class="text" id="album-cds">CDs: <?php echo $album['cds']; ?></div>
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


<script type="text/javascript">
    $('#burner_single_album').click(function () {
        burner_show_compilation_btn = true;
        var album_id = <?php echo $albumID; ?>;
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

        openModalPage('assets/modals/burner.php');
    });

    var album_details = $('#albumDetails');

    album_details.find('.trackRow').click(function () {
        var _albumID = $(this).closest('table').attr('data-album');
        var _trackNo = $(this).attr('data-track-no');
        changeAlbum(_albumID, _trackNo);
    });

    album_details.find('.trackRow .addTrackToPlaylist').click(function (e) {
        var _albumID = $(this).closest('table').attr('data-album');
        var _trackNo = $(this).parent().parent().attr('data-track-no');
        addAlbumSongToPlaylist(_albumID, _trackNo);

        e.stopPropagation();
    });

    album_details.find('.CDth .addTrackToPlaylist').click(function (e) {
        var _albumID = $(this).closest('table').attr('data-album');
        var _CD = $(this).parent().attr('data-cd');

        addAlbumCDToPlaylist(_albumID, _CD);

        e.stopPropagation();
    });

    $(document).ready(function () {
        $('#pre-download').hide();
        $('#download').hide();

    });

    function updateProgress(timeRequired, nowTimestamp, folderSize) {
        var actualTime = new Date();
        folderSize = folderSize / 1.83;
        timeRequired = timeRequired + folderSize;
        var perc = Math.round((100 / timeRequired) * Math.round((actualTime.getTime() / 1000) - nowTimestamp));
        console.log(perc);
        if ($('#download').is(":hidden")) {
            clearProgress();
        }


        $('.progress').width(perc + '%');
        $('.progress').html(perc + '%');

        if (perc >= 100) {
            $('.progress').width('100%');
            $('.progress').html('100%');
            console.log("Process completed");
            clearProgress();
        }
    }

    var downloadProgress;

    $('#download').on('remove', function () {
        clearProgress();
    });

    function clearProgress() {
        clearInterval(downloadProgress);
    }

    $('#download-btn').click(function () {
        $('#details').hide();
        $('#details-footer').hide();
        $('#pre-download').show();
    });

    $('#download-btn-2').click(function () {
        $('#pre-download').hide();

        $('#download').show();

        var folderSize = 0;
        folderSize = <?php echo $albumSize; ?>;
        console.log("Album size: " + folderSize);
        var timeRequired = (folderSize * 60) / 100;
        var nowTimestamp = new Date().getTime() / 1000;


        $.ajax({
            url: "assets/php/album_download.php?id=" + <?php echo $albumID; ?>
        }).done(function (data) {
            $('#download').html("<div class='download-link'>" + data + "</div>");
            $('#progressContainer').remove();
            clearProgress();
        }).fail(function () {
            alert("Something went wrong. Please try again.");
        });

        if (folderSize !== 1) {
            downloadProgress = window.setInterval(updateProgress.bind(null, timeRequired, nowTimestamp, folderSize), 1000);
        } else {
            clearProgress();
        }

    });
</script>
