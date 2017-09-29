<?php
include_once '../../../vendor/autoload.php';

use Lib\Config;
use Lib\ICanHaz;

$ports = (new Config())->get('ports');
?>
    <div class="modalHeader">
        Ports
    </div>

    <div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
        <p>The following ports need to be forwarded to your jukebox, from your router,
            in order to allow the jukebox to be accessible from outside the local network.</p>
        <p><b>NOTE:</b> A restart is required for the changes to take place.</p>

        <form id="portsForm">
            <?php foreach ($ports as $key => $port): ?>
                <div class="col-row">
                    <div class="col-left right text-right">
                        <label for="port-<?php echo $port ?>"><?php echo $key ?></label>
                    </div>
                    <div class="col-right text-left">
                        <input type="number" id="port-<?php echo $port ?>" name="<?php echo $key ?>"
                               value="<?php echo $port ?>" min="1" max="65535" step="1">
                    </div>
                </div>
            <?php endforeach; ?>
        </form>
    </div>

    <div class="modalFooter">
        <button class="pull-right" id="portsFormSubmit">Save</button>
    </div>


<?php

ICanHaz::js('scripts.js', false, true);
