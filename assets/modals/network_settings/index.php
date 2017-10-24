<?php

require_once '../../../vendor/autoload.php';

use Lib\ICanHaz;
use Lib\Wifi;

?>
<div class="modalHeader">Network Settings</div>
<form method="POST" id="network_settings_form">
    <div class="modalBody mCustomScrollbar" data-mcs-theme="dark">

        <div id="network_selectors">
            <select title="Network Type" id="network_type" name="network_type" style="display: none">
                <option value="0">NONE</option>
                <option value="1">LAN</option>
                <option value="2">WIFI</option>
                <option value="3">HOTSPOT</option>
            </select>

        </div>

        <div id="manual_settings">
            <div style="width: 45%; float: left;">
                <p><label for="ipaddress">IP Address</label><input type="text" class="ip right" value="192.168.0.2"
                                                                   id="ipaddress" name="ipaddress"/></p>
                <p><label for="gateway">Gateway</label><input type="text" class="ip right" value="192.168.0.1"
                                                              id="gateway" name="gateway"/></p>
            </div>
            <div style="width: 45%; float: right;">
                <!--<p><label for="network">Network</label><input type="text" class="ip right" value="192.168.0.0" id="network" name="network" /></p>
                <p><label for="dns2">Secondary DNS</label><input type="text" class="ip right" value="8.8.4.4" id="dns2" name="dns2" /></p>-->
                <p><label for="subnetmask">Subnet Mask</label><input type="text" class="ip right" value="255.255.255.0"
                                                                     id="subnetmask" name="subnetmask"/></p>
                <p><label for="dns1">Primary DNS</label><input type="text" class="ip right" value="8.8.8.8" id="dns1"
                                                               name="dns1"/></p>
            </div>
        </div>

        <input type="hidden" name="ssid" id="ssid" value=""/>

        <div id="hotspot_module" class="half-wide">
            <h4>Hotspot</h4>
            <p><label for="hotspot_ssid">Network Name</label><input type="text" class="right" value="Jukebox Wifi"
                                                                    id="hotspot_ssid" name="hotspot_ssid"/></p>
            <p><label for="hotspot_password">Passowrd</label><input type="password" class="right" value="password"
                                                                    id="hotspot_password" name="hotspot_password"/></p>

            <p>
                <label for="hotspot_channel">Channel</label>s
                <select class="right" id="hotspot_channel" name="hotspot_channel" class="box-btn">
                    <?php
                    for ($i = 1; $i <= 11; $i++) {
                        echo "<option value=\"$i\">$i</option>";
                    }
                    ?>
                </select>
            </p>
        </div>

        <div id="wifi_module">
            <hr/>

            <?php
            $wifi = new Wifi();

            $network = $wifi->getConnectedNetwork();
            $wifiPassword = '';
            $wifiEssid = '';

            if ($network !== null) {
                echo '<span>Connected to "'.$network['ESSID'].'"</span>';
                $wifiPassword = $wifi->getNetworkByEssid($network['ESSID']);

                $wifiPassword = Wifi::decodePassword($wifiPassword['password'], $wifiPassword['salt']);
                $wifiEssid = $network['ESSID'];
            }

            ?>
            <div class="box-btn" onclick="modal.openPage('/assets/modals/network_settings/wifi.php')">Connect to a WiFi
                network
            </div>
            <input type="hidden" id="wifi_essid" name="ssid" value="<?php echo $wifiEssid ?>"/>
            <input type="hidden" id="wifi_password" name="password" value="<?php echo $wifiPassword ?>"/>
            <input type="hidden" id="wifi_protocol" name="protocol"/>
            <input type="hidden" id="wifi_encryption" name="encryption"/>
            <input type="hidden" id="wifi_encryption_type" name="encryption_type"/>
        </div>
    </div>

    <div class="modalFooter">
    <span id="dhcp_module">
        DHCP
        <span class="onoffswitch" id="dhcp_div">
            <input type="checkbox" name="dhcp" class="onoffswitch-checkbox" id="dhcp">
            <label class="onoffswitch-label" for="dhcp">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </span>
    </span>
        <button onclick="modal.openSettings('network_settings/debug.php')">Network Diagnosis</button> <input type="submit" value="Save" class="right saveBtn" onclick="$('#network_settings_form').submit();"/>
    </div>
</form>
<?php
ICanHaz::css(['css/style.css', 'css/horizontal_selector.css']);
ICanHaz::js(['js/horizontal_selector.js', 'js/scripts.js'], false, true);
?>
