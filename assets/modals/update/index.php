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
    <button class="right" id="rebase_button">Rebase</button>
    <button class="right" id="delete_button">Delete</button>
    <select id="branch" title="Select a branch and perform an action with the buttons" class="right">
        <?php
        $git = new Git();

        $branches = Git::branch("-a");

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

    Current Branch: <b><?php echo "$current_branch"; ?></b>
</div>
<script src="/assets/modals/update/scripts.js"></script>