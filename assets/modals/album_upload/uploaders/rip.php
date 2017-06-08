<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">

    <p>You can import an audio CD to the Jukebox using this feature.</p>

    <div id="ripperMain" style="margin: 50px">
        <button id="ripperStart">Start</button>
        <button id="ripperStop" class="hidden">Abort</button>

        <div id="ripperSuccess" class="hidden">
            <p>CD imported successfully!</p>

            <p>
                <button id="btnNextCD">Add CD2</button>
                <button id="ripperReset">Reset</button>
            </p>

            <!--<button id="ripperCheckStatus">Check</button>-->
        </div>
    </div>

    <hr/>
    <div class="progressBar full-wide" style="margin: 0">
        <div class="progress" id="ripperProgress"></div>
    </div>
</div>

<div class="modalFooter">
    <button id="btnBack">Back</button>
    <button id="btnCancel">Cancel</button>
    <button class="right disabled" id="btnNext">Next</button>
</div>

<?php
require_once '../../../../vendor/autoload.php';
use Lib\ICanHaz;

ICanHaz::js('rip.js', false, true);