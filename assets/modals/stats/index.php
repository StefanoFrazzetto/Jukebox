<?php

require_once '../../../vendor/autoload.php';

use Lib\System;

function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);

    if ($ini == 0) {
        return '';
    }
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;

    return substr($string, $ini, $len);
}

function factory($raw_materials)
{
    $components = explode(' ', $raw_materials); //Let's hope it's not kerosene;
    $goods = [];

    foreach ($components as $component) {
        if (trim($component != '')) {
            $goods[] = $component;
        }
    }

    return $goods;
}

?>

<div class="modalHeader">Stats</div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">

    <div class="col-left">
        <?php
        $raw_materials = exec('df -hT /home');

        $refined = factory($raw_materials);

        ?>
        <p>Firmware Storage</p>
        <div class="progressBar" style="width: 100%;">
            <div class="progress" style="width: <?php echo $refined[5] ?>">
                <?php echo $refined[5]; ?>
            </div>
        </div>

        <?php

        $raw_materials = exec('df -hT /var/www/html/jukebox');
        $refined = factory($raw_materials);

        ?>

        <p>Music Storage</p>
        <div class="progressBar" style="width: 100%;">
            <div class="progress" style="width: <?php echo $refined[5] ?>">
                <?php echo $refined[5]; ?>
            </div>
        </div>
    </div>

    <div class="col-right">
        <?php
        //use -bn2 for more accurate resuls, it will take a long time, tho
        $percentage = exec('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1"%"}\'');
        ?>

        <p>CPU Usage</p>
        <div class="progressBar" style="width: 100%;">
            <div class="progress" style="width: <?php echo $percentage ?>">
                <?php echo $percentage; ?>
            </div>
        </div>

        <?php
        $temp = System::getSoctemp();
        ?>

        <p>CPU Temp</p>
        <div class="progressBar full-wide">
            <div class="progress" style="width: <?php echo $temp ?>%">
                <?php echo $temp; ?> &deg;C
            </div>
        </div>
    </div>


</div>
