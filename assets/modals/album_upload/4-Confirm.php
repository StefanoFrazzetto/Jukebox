<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">
    <div style="width: 30%; float: left; text-align: center">
        <h1>
            <img src="/assets/img/album-placeholder.png" style="width: 90%" id="confirmCoverImg" class="cover-picture">
        </h1>
    </div>
    <div style="width: 70%; float: right; max-height: 300px" class="mCustomScrollbar">
        <h2 id="confirmTitleHeader">[Title]</h2>
        <h3 id="confirmArtistsHeader">[Artists]</h3>
        <hr/>
        <div>
            <table class="cooltable">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>File Name</th>
                </tr>
                </thead>
                <tbody id="confirmSongsTableBody"></tbody>
            </table>
        </div>
    </div>

</div>

<div class="modalFooter">
    <button id="btnBack">Back</button>
    <button id="btnCancel">Cancel</button>
    <button class="right" id="btnConfirm">Confirm</button>
</div>

<?php
require_once '../../../vendor/autoload.php';
use Lib\ICanHaz;

ICanHaz::js('4-Confirm.js', false, true);
?>