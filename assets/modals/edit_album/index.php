<?php
include '../../php-lib/MusicClasses/Album.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$album = Album::getAlbum($id);

$tracks = $album->getTracks();

?>
<div class="modalHeader">Edit Album - <?php echo $album->getTitle() ?></div>
<div class="modalBody" data-mcs-theme="dark" style="position: relative">
    <div style="position: absolute; width: 300px; height: 300px; display: inline-block; overflow: hidden">
        <img id="album_cover_img" class="cover" src="jukebox/<?php echo $id; ?>/cover.jpg" style="width: 100%;">
        <div class="badge badge-bottom badge-bigger" onclick="openPickCoverModal()"><i class="fa fa-pencil fa-2x"></i>
        </div>
    </div>

    <div class="mCustomScrollbar" id="edit-album-column"
         style="position: relative; margin-left: 350px; width: 480px; height: 300px;">
        <form id="edit-album-form">
            <label for="album-artist">Artist</label>
            <input type="text" name="album-artist" readonly="readonly" id="album-artist" class="right large"
                   value="<?php echo implode($album->getArtistsName()) ?>"/>

            <br/>
            <!-- GOD FORGIVE ME FOR MY HTML SINS -->
            <br/>

            <label for="album-title">Title</label>
            <input type="text" name="album-title" id="album-title" class="right large"
                   value="<?php echo $album->getTitle() ?>"/>


            <input type="hidden" name="album-id" id="album-id" value="<?php echo $id ?>"/>
            <input type="hidden" name="album-tracks" id="album-tracks"
                   value="<?php echo base64_encode(json_encode($tracks)) ?>"/>

            <input type="submit" name="submit" value="Save" class="invisible"/>
        </form>

        <hr/>

        <ul class="multiselect" id="edit-tracks" style="padding-bottom: 100px">
            <?php
            $cd_no = 0;

            function print_cd_header($cd_no)
            {
                $delete = $cd_no > 1 ? " <i class='fa fa-trash right delete'></i>" : '';

                echo "<li class=\"cd handle header\" data-cd=\"$cd_no\">CD $cd_no$delete</li>";
            }

            // var_dump($tracks);
            foreach ($tracks as $key => $track) {
                if ($track->getCd() != $cd_no) {
                    $cd_no = $track->getCd();

                    print_cd_header($cd_no);
                }

                $title = $track->getTitle();

                echo "<li data-id='$key' class='track'><span class='title'><i class=\"fa fa-bars handle\"></i> $title</span> <span class='right'><i class='fa fa-pencil edit'></i> <i class='fa fa-trash delete'></i></span></li>";
            }

            ?>
        </ul>
    </div>

</div>
<div class="modalFooter">
    <button onclick="modal.openPage('assets/modals/album_details?id=<?php echo $id ?>')">Back to Album</button>
    <button onclick="appendCd()">Add CD</button>
    <button class="right" id="edit-album-save">Save</button>
</div>

<!--suppress CssUnusedSymbol -->
<style>
    #edit-tracks .handle {
        cursor: move;
        padding: 10px;
        margin: -8px;

        pointer-events: all;

        user-select: none;
    }

    #edit-tracks .cd.handle {
        margin: 0;
    }

    #edit-tracks .cd.handle.nodrag {
        pointer-events: none;
    }

    #edit-tracks .edit, #edit-tracks .delete {
        cursor: pointer;
    }
</style>

<script type="text/javascript" src="/assets/js/Sortable.min.js"></script>
<script type="text/javascript" src="/assets/modals/edit_album/js/scripts.js"></script>
