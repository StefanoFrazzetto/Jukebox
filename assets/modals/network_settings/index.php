<div class="modalHeader">Network Settings</div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
	<form method="POST" id="network_settings_form">
		<div id="network_selectors">
			<select id="network_type" name="network_type" style="display: none">
				<option value="0">NONE</option>
				<option value="1">LAN</option>
				<option value="2">WIFI</option>
				<option value="3">HOTSPOT</option>
			</select>

		</div>

		<hr/>

		<div id="dhcp_module">
			<h4 class="inline">DHCP</h4>
			<div class="onoffswitch" id="dhcp_div">
				<input type="checkbox" name="dhcp" class="onoffswitch-checkbox" id="dhcp">
				<label class="onoffswitch-label" for="dhcp">
					<span class="onoffswitch-inner"></span>
					<span class="onoffswitch-switch"></span>
				</label>
			</div>
		</div>

		<div id="manual_settings">
			<div style="width: 45%; float: left;">
				<p><label for="ipaddress">IP Address</label><input type="text" class="ip right" value="192.168.0.2" id="ipaddress" name="ipaddress" /></p>
				<p><label for="gateway">Gateway</label><input type="text" class="ip right" value="192.168.0.1" id="gateway" name="gateway" /></p>
			</div>
			<div style="width: 45%; float: right;">
				<!--<p><label for="network">Network</label><input type="text" class="ip right" value="192.168.0.0" id="network" name="network" /></p>
				<p><label for="dns2">Secondary DNS</label><input type="text" class="ip right" value="8.8.4.4" id="dns2" name="dns2" /></p>-->
				<p><label for="subnetmask">Subnet Mask</label><input type="text" class="ip right" value="255.255.255.0" id="subnetmask" name="subnetmask"/></p>
				<p><label for="dns1">Primary DNS</label><input type="text" class="ip right" value="8.8.8.8" id="dns1" name="dns1" /></p>
			</div>
		</div>

		<input type="hidden" name="ssid" id="ssid" value="" />

		<div id="hotspot_module" class="half-wide">
			<h4>Hotspot</h4>
			<p><label for="hotspot_ssid">Network Name</label><input type="text" class="right" value="Jukebox Wifi" id="hotspot_ssid" name="hotspot_ssid" /></p>
			<p><label for="hotspot_password">Passowrd</label><input type="password" class="right" value="password" id="hotspot_password" name="hotspot_password" /></p>

			<p>
				<label for="hotspot_channel">Channel</label>s
				<select class="right" id="hotspot_channel" name="hotspot_channel" class="box-btn">
					<?php 
					for ($i=1; $i <= 11; $i++) { 
						echo "<option value=\"$i\">$i</option>";
					}
					?>					
				</select>
			</p>
		</div>
	</form>

	<div id="wifi_module">
		<hr/>
		<h4>WiFi</h4>
		<table class="songsTable" id="wifiTable" style="width: 100%; padding-top: 0; margin-top: 0;">
			<thead>
				<tr class="th">
					<th class="wifiID">#</th>
					<th class="wifiESSID">ESSID</th>
					<th class="wifiEncryption">Encryption</th>
					<th class="wifiQuality">Quality</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="4" style="text-align: center;">Loading...</td>
				</tr>
			</tbody>
		</table>
		<div id="wifi_details" class="hidden">
			<div>ESSID: <span id="network_details_essid"></span></div>
			<div>Security: <span id="network_details_security"></span></div>

			<div id="network_details_password">Password: <input type="password" name="network_password" id="network_password" /></div>
			
			<button onclick="closeNetworkDetails()">Back</button>

			<button class="danger" id="network_details_forget">Forget</button>

            <button class="success saveBtn" onclick="setSelectedNetwork()">Save & Connect</button>

			
		</div>
	</div>
</div>

<div class="modalFooter">
    <input type="submit" value="Save" class="right saveBtn" onclick="$('#network_settings_form').submit();"/>
</div>


<link rel="stylesheet" type="text/css" href="/assets/modals/network_settings/css/style.css">
<link rel="stylesheet" type="text/css" href="/assets/modals/network_settings/css/horizontal_selector.css">
<script src="/assets/modals/network_settings/js/horizontal_selector.js"></script>
<script type="text/javascript" src="/assets/modals/network_settings/js/scripts.js"></script>
<script type="text/javascript" src="/assets/modals/network_settings/js/wifi_scan.js"></script>
