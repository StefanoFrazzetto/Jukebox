<?php

require_once '../../vendor/autoload.php';

use Lib\Speakers;

?>

<div class="modalHeader">Settings</div>

<div class="modalBody mCustomScrollbar text-center settings-home" data-mcs-theme="dark">
    <div class="selector eject">
        <i class="fa fa-5x fa-eject"></i>
        <p>Eject Disc</p>
    </div>

    <div class="selector speakers">
        <i class="fa fa-5x fa-volume-up"></i>
        <p></p>
        <div class="onoffswitch inline" id="speakers_div">
            <input type="checkbox" name="dhcp" class="onoffswitch-checkbox"
                   id="speakers" <?php if (Speakers::getStatus()) {
    echo 'checked';
} ?> />
            <label class="onoffswitch-label" for="speakers">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>

    </div>

    <div class="selector calibrate">
        <i class="fa fa-5x fa-crosshairs"></i>
        <p>Calibrate Screen</p>
    </div>

    <br/>

    <div class="selector restore">
        <i class="fa fa-5x fa-history"></i>
        <p>Restore Albums</p>
    </div>

    <div class="selector refresh">
        <i class="fa fa-5x fa-refresh"></i>
        <p>Refresh</p>
    </div>

    <div class="selector reset">
        <i class="fa fa-5x fa-undo"></i>
        <p>Factory Reset</p>
    </div>
</div>

<script>
    $('#speakers').on("change", function () {
        if (!this.checked) {
            $.ajax('assets/API/system.php?action=speakers_off');
        } else {
            $.ajax('assets/API/system.php?action=speakers_on');
        }
    });

    $('.selector.eject').click(function () {
        $.ajax('assets/API/system.php?action=eject');
    });

    $('.selector.calibrate').click(function () {
        $.ajax('assets/API/system.php?action=calibrate');
    });

    $('.selector.restore').click(function () {
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
    });

    $('.selector.reset').click(function () {
        modal.openSettings('format.php');
    });

    $('.selector.refresh').click(function () {
        location.reload();
    });
</script>

<style>
    .settings-home .selector {
        width: 150px;
        height: 180px;
        line-height: 30px;
        vertical-align: bottom;
    }
</style>
