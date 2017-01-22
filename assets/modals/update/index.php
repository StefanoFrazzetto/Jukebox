<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 22-Nov-16
 * Time: 18:29
 */

require_once '../../php-lib/Git.php';
?>

<div class="modalHeader">Update</div>
<div class="modalBody">
    <div class="center">
        <div class="col-left">
            <div id="loader">
                <p></p>
                <i class="fa fa-spinner fa-5x fa-spin"></i>
                <p>Checking for updates...</p>
            </div>

            <div id="updating" class="hidden">
                <p></p>
                <i class="fa fa-spinner fa-5x fa-spin"></i>
                <p>Updating...</p>
            </div>

            <div id="up-to-date" class="hidden">
                <h2><i class="fa fa-check"></i> Up to date</h2>
                <p>Congrats, your jukebox is running the latest version.</p>
                <button class="check-update-btn">
                    <i class="fa fa-refresh"></i> Check for update
                </button>
            </div>

            <div id="not-up-to-date" class="hidden">
                <h2><i class="fa fa-close"></i> NOT up to date</h2>
                <p>The jukebox needs an update.</p>
                <button class="update-btn"><i class="fa fa-arrow-circle-o-up"></i> UPDATE</button>
            </div>

            <div id="error" class="hidden">
                <h2><i class="fa fa-warning"></i> Error</h2>
                <p id="errorMessage">The jukebox needs an update.</p>
                <button class="update-btn"><i class="fa fa-arrow-circle-o-up"></i> UPDATE</button>
                <button class="check-update-btn"><i class="fa fa-refresh"></i> Check for update</button>
            </div>
        </div>
        <div class="col-right mCustomScrollbar" style="max-height: 155px">
            Latest changes:
            <ul id="changes" style="text-align: left"></ul>
        </div>
    </div>

</div>
<div class="modalFooter">
    <label for="branch">Branch: </label>
    <select id="branch">
        <?php
        $git = new Git();

        $branches = Git::branch();

        $current_branch = $git->getCurrentBranch();

        foreach ($branches as $branch) {
            $branch = trim($branch);

            if ($branch != '') {
                $selected = ($branch == "$current_branch") ? ' selected' : '';

                echo "<option$selected value='$branch'>$branch</option>";
            }
        }
        ?>
    </select>
    Current Branch: <?php echo "$current_branch"; ?>

    <button class="right" id="branch_button">Okay</button>
</div>
<script>
    var updateTried = false;

    function checkForUpdates() {
        $('#up-to-date, #not-up-to-date, #error').hide();
        $('#loader').show();

        $.ajax('/assets/cmd/exec.php?cmd=needs_update')
            .done(function (data) {
                if (data == "up to date\n") {
                    $('#up-to-date').show();
                    updateTried = false;
                } else if (data == "not up to date\n") {
                    if (updateTried) {
                        error("Failed to update");
                        return;
                    }
                    $('#not-up-to-date').show();
                } else {
                    error("Oh, snap! The update checker gave a bad output.");
                }
            })
            .fail(function () {
                error("Failed to contact the update checker.");
            })
            .always(function () {
                $('#loader').hide();
                loadChangeList();
            });
    }

    $('.update-btn').click(function () {
        $('#up-to-date, #not-up-to-date, #error').hide();
        var updating = $('#updating');
        updating.show();

        $.getJSON('/assets/API/git.php?git=pull')
            .done(function (done) {
                if (done.status == "succes") {
                    updateTried = true;
                    checkForUpdates();
                } else {
                    error("Failed to update");
                }

            })
            .fail(function () {
                error("Failed to contact the update server.")
            })
            .always(function () {
                updating.hide();
            });
    });

    function loadChangeList() {
        $.getJSON('/assets/modals/update/changelist.json.php')
            .done(function (data) {
                var cont = $('#changes').html('');
                data.forEach(function (entry) {
                    cont.append("<li>" + entry + "</li>");
                })
            })
            .fail(function () {
                error("Failed to load change list");
            });
    }

    function error(error) {
        $('#errorMessage').html(error);
        $('#error').show();
    }

    $('.check-update-btn').click(function () {
        checkForUpdates();
    });

    function changeBranch(branch_name) {
        $.getJSON('/assets/API/git.php?git=checkout&branch=' + branch_name)
            .done(function (data) {
                if (data.status === 'success') {
                    alert("Checked out to " + branch_name + " successfully!");
                } else {
                    error("Failed to checkout branch " + branch_name);
                }
            })
            .fail(function () {
                error("Failed to checkout branch " + branch_name);
            });
    }

    $('#branch').change(function () {
        var branch = $(this).val();
        changeBranch(branch);
    });

    $('#branch_button').click(function () {
        changeBranch($('#branch').val())
    });


    loadChangeList();
    checkForUpdates();
</script>