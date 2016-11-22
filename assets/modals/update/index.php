<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 22-Nov-16
 * Time: 18:29
 */

?>

<div class="modalHeader">Update</div>
<div class="modalBody">
    <div class="center">
        <div id="loader">
            <i class="fa fa-spinner fa-5x fa-spin"></i>
            <p>Checking for updates</p>
        </div>
        <div id="up-to-date" class="hidden">
            <h2>Up to date</h2>
            <p>Congrats, your jukebox is running the latest version.</p>
        </div>
        <div id="not-up-to-date" class="hidden">
            <h2>NOT up to date</h2>
            <p>The jukebox needs an update.</p>
            <button id="update-btn">UPDATE</button>
        </div>
    </div>
</div>
<script>
    function checkForUpdates() {
        $('#up-to-date, #not-up-to-date').hide();
        $('#loading').show();

        $.ajax('/assets/cmd/exec.php?cmd=needs_update')
            .done(function (data) {
                console.log("-" + data + "-");
                if (data == "up to date\n") {
                    $('#up-to-date').show();
                } else if (data == "not up to date\n") {
                    $('#not-up-to-date').show();
                } else {
                    alert("Oh, snap");
                }
            })
            .fail(function () {
                $('#not-up-to-date').show();
            })
            .always(function () {
                $('#loader').hide();
            });
    }

    checkForUpdates();

    $('#update-btn').click(function () {
        $.ajax('/assets/cmd/exec.php?cmd=git_force_pull')
            .done(function () {
                checkForUpdates();
            });
    });
</script>