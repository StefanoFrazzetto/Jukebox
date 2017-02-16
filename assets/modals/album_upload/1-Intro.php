<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">
    <p>Select how you want to upload songs to the Jukebox.</p>

    <div id="uploaderSelector"></div>

    <p id="uploaderSelectorDescription"></p>
</div>

<div class="modalFooter">
    <button>Cancel</button>
    <button class="right disabled" id="uploaderSelectorNext">Next</button>
</div>

<?php
require_once '../../../vendor/autoload.php';
use Lib\ICanHaz;

ICanHaz::js('/assets/modals/album_upload/Uploader.js');
ICanHaz::js('1-Intro.js', false, true);
?>