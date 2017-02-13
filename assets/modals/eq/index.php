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
            <li curve="53 45 41 39 42 42 47 52 51 43">Recommended</li>
            <li curve="50 50 50 50 50 50 50 50 50 50">Flat</li>
            <li curve="60 57 50 40 44 44 48 53 51 45">Boosted</li>
            <li curve="42 42 42 42 42 42 55 54 54 58">Classical</li>
            <li curve="54 54 50 46 46 46 50 54 54 54">Club</li>
            <li curve="37 41 49 51 51 61 63 63 51 51">Dance</li>
            <li curve="52 42 51 64 62 57 52 44 39 36">Headphones</li>
            <li curve="35 35 35 39 46 54 61 64 65 65">Treble</li>
            <li curve="71 71 71 62 52 39 31 31 31 29">Bass</li>
            <li curve="39 39 46 46 54 62 62 62 54 54">Large hall</li>
            <li curve="58 50 45 43 42 42 45 47 47 48">Live</li>
            <li curve="45 45 55 55 55 55 55 55 45 45">Party</li>
            <li curve="57 48 44 43 47 56 58 58 57 57">Pop</li>
            <li curve="50 50 51 60 50 41 41 50 50 50">Reggae</li>
            <li curve="39 44 61 65 58 47 39 36 36 36">Rock</li>
            <li curve="58 62 61 55 49 46 41 40 38 40">Ska</li>
            <li curve="47 47 50 53 59 61 58 53 49 39">Soft rock</li>
            <li curve="51 56 59 61 59 52 45 43 41 39">Soft</li>
            <li curve="42 45 53 62 61 53 42 39 39 40">Techno</li>
        </ul>
    </div>
</div>

<?php ICanHaz::js('script.js'); ?>
