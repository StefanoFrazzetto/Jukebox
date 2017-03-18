<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 18/03/2017
 * Time: 15:01
 */

include_once '../../../vendor/autoload.php';

use Lib\ICanHaz;

?>
<div class="modalHeader">Wifi</div>
<div class="modalBody">
    <table class="songsTable" id="wifiTable" style="width: 100%; padding-top: 0; margin-top: 0;">
        <thead>
        <tr class="th">
            <th class="wifiESSID">ESSID</th>
            <th class="wifiEncryption">Encryption</th>
            <th class="wifiQuality">Quality</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="3" style="text-align: center;">Loading...</td>
        </tr>
        </tbody>
    </table>
    <div id="wifiPagination"></div>
</div>
<div class="modalFooter">
    <button onclick="modal.openPage('/assets/modals/network_settings/')">Cancel</button>
</div>
<?php
ICanHaz::js('js/wifi_scan.js', false, true);
ICanHaz::css('css/style.css');
?>
<script>
    modal.onModalClosed = stopScan;
    startScan();
</script>