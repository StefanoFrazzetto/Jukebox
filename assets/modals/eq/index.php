<?php
include_once '../../../vendor/autoload.php';

use Lib\ICanHaz;

ICanHaz::css('style.css');
?>
    <div class="modalHeader">Equaliser</div>

    <div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
        <div id="eq-holder"></div>
        <div id="eq-presets" class="mCustomScrollbar">
            <h3>Presets</h3>
            <ul class="multiselect" id="presets" data-mcs-theme="dark">

            </ul>
        </div>
    </div>
    <div class="modalFooter">

        <div class="onoffswitch" id="eq-switch-div">
            <input type="checkbox" name="dhcp" class="onoffswitch-checkbox" id="eq-switch">
            <label class="onoffswitch-label" for="eq-switch">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>
        Equaliser
    </div>

<?php ICanHaz::js('script.js', false, true); ?>