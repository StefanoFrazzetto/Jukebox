<?php

require_once '../../../vendor/autoload.php';

use Lib\Database;

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!isset($id)) {
    $id = 2;
}

$database = new Database();

$album_details = $database->select('*', 'albums', 'WHERE `id` = '.$id)[0]; // colonna tabella query

$tracks = json_decode($album_details->tracks);
?>
<div class="modalHeader">Edit Album - <?php echo $album_details->title ?></div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
    <div style="position: relative; width: 250px; display: inline-block;">
        <img id="album_cover_img" src="jukebox/<?php echo $id; ?>/cover.jpg" style="width: 100%;">
        <div style="position: absolute; right: 10px; bottom: 10px; cursor: pointer;"
             onclick="modal.openPage('assets/modals/edit_album/pick_cover.php?id='+id)"><i
                    class="fa fa-pencil fa-2x"></i></div>
    </div>

    <form id="edit-album-form" class="half-wide right" style="position: relative;">
        <label for="album-title">Title</label>
        <input type="text" name="album-title" id="album-title" class="right"
               value="<?php echo $album_details->title ?>"/>
        <br/>
        <br/>
        <label for="album-artist">Artist</label>
        <input type="text" name="album-artist" id="album-artist" class="right"
               value="<?php echo $album_details->artist ?>"/>
        <input type="hidden" name="album-id" id="album-id" value="<?php echo $id ?>"/>
        <input type="hidden" name="album-tracks" id="album-tracks"
               value="<?php echo base64_encode($album_details->tracks) ?>"/>


        <input type="submit" name="submit" value="Save" class="right"
               style="position: absolute; bottom: -100px; right: 0;"/>
    </form>

    <hr/>

    <ul class="multiselect" id="edit-tracks">
        <?php

        // var_dump($tracks);
        foreach ($tracks as $key => $track) {
            echo "<li data-id='$key'><span class='title'>$track->title</span> <span class='right'><i class='fa fa-pencil edit'></i> <i class='fa fa-trash delete'></i></span></li>";
        }

        ?>
    </ul>
</div>

<script type="text/javascript" src="assets/modals/edit_album/js/scripts.js"></script>