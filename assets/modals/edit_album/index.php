<?php
include '../../php/Database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!isset($id))
    $id = 2;

$database = new Database();

$album_details = $database->select('*', 'albums', 'WHERE `id` = ' . $id)[0]; // colonna tabella query

$tracks = json_decode($album_details->tracks);
?>
<div class="modalHeader">Edit Album - <?php echo $album_details->title ?></div>
<div class="modalBody" data-mcs-theme="dark" style="position: relative">
    <div style="position: absolute; width: 300px; height: 300px; display: inline-block; overflow: hidden">
        <img id="album_cover_img" class="cover" src="jukebox/<?php echo $id; ?>/cover.jpg" style="width: 100%;">
        <div class="badge badge-bottom badge-bigger" onclick="openPickCoverModal()"><i class="fa fa-pencil fa-2x"></i></div>
    </div>

    <div class="mCustomScrollbar" id="edit-album-column" style="position: relative; margin-left: 350px; width: 480px; height: 300px;">
        <form id="edit-album-form" >
            <label for="album-artist">Artist</label>
            <input type="text" name="album-artist" id="album-artist" class="right"
                   value="<?php echo $album_details->artist ?>"/>

            <br/>
            <!-- GOD FORGIVE ME FOR MY HTML SINS -->
            <br/>

            <label for="album-title">Title</label>
            <input type="text" name="album-title" id="album-title" class="right"
                   value="<?php echo $album_details->title ?>"/>


            <input type="hidden" name="album-id" id="album-id" value="<?php echo $id ?>"/>
            <input type="hidden" name="album-tracks" id="album-tracks"
                   value="<?php echo base64_encode($album_details->tracks) ?>"/>


            <input type="submit" name="submit" value="Save" class="invisible"/>
        </form>

        <hr/>

        <ul class="multiselect" id="edit-tracks" style="padding-bottom: 100px">
            <?php
            $cd_no = 0;

            function print_cd_header($cd_no)
            {
                echo "<li class=\"cd header\" data-cd=\"$cd_no\">CD $cd_no</li>";
            }

            // var_dump($tracks);
            foreach ($tracks as $key => $track) {
                if ($track->cd != $cd_no) {
                    $cd_no = $track->cd;

                    print_cd_header($cd_no);
                }

                echo "<li data-id='$key' class='track'><span class='title'><i class=\"fa fa-bars handle\"></i> $track->title</span> <span class='right'><i class='fa fa-pencil edit'></i> <i class='fa fa-trash delete'></i></span></li>";
            }

            ?>
        </ul>
    </div>

</div>
<div class="modalFooter">
    <button onclick="openModalPage('assets/modals/edit_album/?id=<?php echo $id ?>')">Back to Album</button>
    <button onclick="appendCd()">Add CD</button>
    <button class="right" id="edit-album-save">Save</button>
</div>

<style>
    #edit-tracks .handle {
        cursor: move;
        padding: 10px;
        margin: -8px;

        user-select: none;
    }

    #edit-tracks .edit, #edit-tracks .delete {
        cursor: pointer;
    }
</style>

<script type="text/javascript" src="/assets/js/Sortable.min.js"></script>
<script type="text/javascript" src="/assets/modals/edit_album/js/scripts.js"></script>
