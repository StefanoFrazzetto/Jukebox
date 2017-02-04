<?php
session_start();

function getConsecutiveCombinations($array)
{
    $length = count($array);
    $results = [];
    if (is_array($array))
        foreach ($array as $key => $item) {
            $this_train = '';
            for ($i = $key; $i < $length; $i++) {
                $this_train .= $array[$i] . ' ';
                $results[] = trim($this_train);
            }
        }
    return $results;
}

$album_count = isset($_SESSION['possible_albums']) ? count($_SESSION['possible_albums']) : 0;
$artist_count = isset($_SESSION['possible_artist']) ? count($_SESSION['possible_artist']) : 0;

if (!$album_count OR !$artist_count) { // if there are no album or artist get from id3 it will try to get some from the filenames
    require '../../php-lib/uploader_utilities/isThisPatternEverywhere.php';
    $tracks = $_SESSION['tracks'];
    $urls = array_column($tracks, 'url');
    $name_slices = explode('-', preg_replace('/(\.[^.]*$)/', '', $urls[0]));
    $found_slices = [];

    foreach ($name_slices as $name_slice) {
        if (isThisPatternEverywhere('/' . $name_slice . '/', $urls)) {
            $found_slices[] = $name_slice;
        } else {
            //Just nuffin' for now
        }
    }

    $found_slices = getConsecutiveCombinations($found_slices);


    foreach ($found_slices as $slice) {
        if (!$album_count) {
            $_SESSION['possible_albums'][] = $slice;
        }
        if (!$artist_count) {
            $_SESSION['possible_artist'][] = $slice;
        }
    }


}


?>
<div class="modalHeader">Album Details</div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 350px;">
    <?php
    $title_value = '';
    $artist_value = '';

    if (isset($_SESSION['albumTitle'])) {
        $title_value = $_SESSION['albumTitle'];
    } else if (isset($_SESSION['possible_albums']) && count($_SESSION['possible_albums']) > 1) {
        $title_value = $_SESSION['possible_albums'][0];
    } else {

    }

    if (isset($_SESSION['albumArtist'])) {
        $artist_value = $_SESSION['albumArtist'];
    } else if (isset($_SESSION['possible_artist']) && count($_SESSION['possible_artist']) > 1) {
        $artist_value = "Various Artists";
    } else if (isset($_SESSION['possible_artist']) && count($_SESSION['possible_artist']) == 1) {
        $artist_value = $_SESSION['possible_artist'][0];
    }

    if (isset($_SESSION['possible_artist']) && count($_SESSION['possible_artist']) > 1) {
        if (!in_array("Various Artists", $_SESSION['possible_artist'])) {
            $_SESSION['possible_artist'][] = "Various Artists";
        }
    }

    ?>
    <form id="addAlbumForm" action="/assets/php/album_creation/add_album_details.php" class="text-center">
        <h3>Artist</h3>
        <label>
            <input type="text" id="albumArtistField" name="albumArtist" placeholder="Artist" class="half-wide"
                   value="<?php echo $artist_value ?>" required/>
        </label>
        <br/>
        <br/>
        <div id="possible_artists">
            <?php
            if (isset($_SESSION['possible_artist']) && is_array($_SESSION['possible_artist']))
                foreach ($_SESSION['possible_artist'] as $possible_artist) {
                    echo '<div class="box-btn">', $possible_artist, '</div>';
                }
            ?>
        </div>
        <hr/>
        <h3>Title</h3>
        <label>
            <input type="text" id="albumTitleField" name="albumTitle" class="half-wide" placeholder="Album Title"
                   value="<?php echo $title_value ?>" required/>
        </label>
        <br/>
        <br/>
        <div id="titleWarning"></div>
        <div id="possible_albums">
            <?php
            if (isset($_SESSION['possible_albums']) && is_array($_SESSION['possible_albums']))
                foreach ($_SESSION['possible_albums'] as $possibile_album) {
                    echo '<div class="box-btn">', $possibile_album, '</div>';
                }
            ?>
        </div>
    </form>

</div>
<div class="modalFooter">
    <div class="box-btn pull-right" id="submit">Next</div>
    <div class="box-btn" onclick="modal.openPage('assets/modals/add_album/2.fix_titles.php');">Back</div>
</div>


<script>
    var addAlbumForm = $('#addAlbumForm');

    var submit_btn = $('#submit');

    var possible_albums = $('#possible_albums');
    var possible_artists = $('#possible_artists');

    var albumTitleField = $('#albumTitleField');
    var albumArtistField = $('#albumArtistField');

    $('#possible_albums *').click(function () {
        albumTitleField.val($(this).html());
        albumTitleField.change();
    });

    $('#possible_artists *').click(function () {
        albumArtistField.val($(this).html());
    });

    function checkIfAlbumExists() {
        var title = albumTitleField.val();

        $.getJSON('assets/API/check_album_exists.php?title=' + title).done(function (response) {
            if (Object.keys(response).length > 0) {
                $('#titleWarning').html('Warning, there is another album with a similar name: <br> "' + response[0].title + '" <br> <img height= "80" src="/jukebox/' + response[0].id + '/cover.jpg?' + response[0].cover + '"/>');
            } else {
                $('#titleWarning').text('');
            }
        });
    }

    albumTitleField.change(function () {
        checkIfAlbumExists();
    });

    albumTitleField.keyup(function () {
        checkIfAlbumExists();
    });

    submit_btn.click(function () {
        $.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function (data) {
            if (data === '0') {
                modal.openPage('assets/modals/add_album/4.add_album_cover.php');
            } else {
                alert('error code: ' + data);
            }
        });
    });

    addAlbumForm.submit(function (event) {
        event.preventDefault();
        submit_btn.click();
    });

    checkIfAlbumExists();

</script>