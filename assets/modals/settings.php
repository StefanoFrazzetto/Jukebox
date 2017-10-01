<?php

require_once '../../vendor/autoload.php';

use Lib\Speakers;

?>

<div class="modalHeader">Settings</div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
    <div id="buttonSettings" style="text-align: center; line-height: 39px;">
        Speakers
        <div class="onoffswitch inline" id="speakers_div">
            <input type="checkbox" name="dhcp" class="onoffswitch-checkbox"
                   id="speakers" <?php if (Speakers::getStatus()) {
                echo 'checked';
            } ?>>
            <label class="onoffswitch-label" for="speakers">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>

        <button onclick="$.ajax('assets/API/device.php?action=eject');">Eject</button>
        <button onclick="$.ajax('assets/php/calibrate_screen.php');">Calibrate Screen</button>
        <button onclick="scanAlbums()">Restore Albums</button>
        <button class="nuclear" onclick="modal.openPage('assets/modals/format.php');">Factory Reset</button>
        <button onclick="location.reload();">Refresh</button>
    </div>

    <script>
        $('#speakers').on("change", function () {
            if (!this.checked) {
                $.ajax('assets/API/device.php?action=speakers_off');
            } else {
                $.ajax('assets/API/device.php?action=speakers_on');
            }
        });

        function scanAlbums() {
            $.getJSON('/assets/API/import_album.php')
                .done(function (data) {
                    if (data.scanned_albums > 0) {
                        alert(data.scanned_albums + " albums found. " + data.created_albums + " successfully imported.");
                        reload();
                    }
                    else
                        alert("No albums found.");
                })
                .fail(function (error) {
                    alert("Failed to scan for albums");
                    console.error(error);
                });
        }
    </script>
</div>
