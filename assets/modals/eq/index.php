<?php
include_once '../../../vendor/autoload.php';

use Lib\ICanHaz;

ICanHaz::css('style.css');
?>

<div class="modalHeader">Equaliser</div>

<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
    <div id="eq-holder">

    </div>
    <div id="eq-presets" class="mCustomScrollbar">
        <h3>Presets</h3>
        <ul class="multiselect" id="presets" data-mcs-theme="dark">

        </ul>
    </div>
</div>

<?php ICanHaz::js('script.js'); ?>
