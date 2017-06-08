<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">
    <div class="mCustomScrollbar" style="max-height: 200px; overflow: hidden">
        <form id="dropzone" class="dropzone">
            <div class="fallback">
                <input name="file" type="file" multiple/>
            </div>
        </form>
    </div>
    <hr/>
    <div id="uploadProgressBar" class="progressBar" style="margin: inherit">
        <div id="progress" class="progress"></div>
    </div>
</div>
<div class="modalFooter">
    <button id="btnBack">Back</button>
    <button id="btnCancel">Cancel</button>
    <button class="right disabled" id="btnNext">Next</button>
    <button class="right disabled" id="btnNextCD">Add CD2</button>
</div>

<?php
require_once '../../../../vendor/autoload.php';
use Lib\ICanHaz;

ICanHaz::js('/assets/js/dropzone.js');
ICanHaz::js('upload.js', false, true);