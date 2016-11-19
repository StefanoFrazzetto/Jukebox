<?php
session_start();
?>
<div class="modalHeader">Music Upload</div>

<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 350px;">
    <div style="float: left;">
        <img onerror="this.src= 'assets/img/album-placeholder.png';" class="cover-picture"
             src="/jukebox/tmp_uploads/cover.jpg?<?php echo time() ?>"
             style=" margin-left: 25px; margin-top: 25px; width: 250px; float: left;"/>
    </div>
    <div class="mCustomScrollbar" style="float: right; max-height: 300px; width: 520px;">
        <div class="text-center">
            <h2><?php echo $_SESSION['albumArtist']; ?></h2>
            <h3><?php echo $_SESSION['albumTitle']; ?></h3>
        </div>
        <table class="cooltable">
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Duration</th>
            </tr>

            <?php
            $tracks = $_SESSION['tracks'];

            session_write_close();

            foreach ($tracks as $key => $track) {
                if (!isset($track['track_no'])) {
                    $track['track_no'] = $key;
                }

                echo
                "<tr>",
                "<td>", $track['track_no'], "</td>",
                "<td>", $track['title'], "</td>",
                "<td>", gmdate("i:s", (int)$track['length']), "</td>",
                '</tr>';
            }

            ?>
        </table>
    </div>

</div>
<div class="modalFooter">
    <div class="box-btn pull-right" id="submit">Finish</div>
    <div class="box-btn pull-right hidden" id="openNewAlbum">Open Album</div>
    <span id="status" class=""></span>
    <div class="box-btn" id="last-modal-previous-btn"
         onclick="openModalPage('assets/modals/add_album/4.add_album_cover.php');">Previous
    </div>
</div>

<script>
    $('#submit').click(function () {
        $('#status').html('Please wait...');

        $.ajax('assets/php/add_album_finalize.php').done(function (data) {
            data = parseInt(data);

            if (data > -1 || isNumber(data)) {
                closeModal();

                new Alert({
                    message: "The new album has been uploaded successfully!",
                    title: "Upload success",
                    buttons: [
                        "Okay",
                        {
                            text: "Open Album",
                            callback: function () {
                                openModalPage('assets/modals/album_details.php?id=' + data);
                            }
                        }
                    ]
                }).show();

                reload();
            } else {
                error('error code: ' + data);
            }
        });
    });
</script>