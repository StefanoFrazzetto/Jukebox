<form method="POST" id="network_settings_form">
	<div class="modalHeader">Network Settings</div>
	<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">

		<div style="width: 45%; float: left;">
			<h4>DHCP</h4>

			<label for="webserver">Enable</label>
			<div class="onoffswitch">
				<input type="checkbox" name="dhcp" class="onoffswitch-checkbox" id="dhcp">
				<label class="onoffswitch-label" for="dhcp">
					<span class="onoffswitch-inner"></span>
	        		<span class="onoffswitch-switch"></span>
				</label>
			</div>
			
			<hr/>
			<div id="manual_settings">
				<p><label>IP Address</label><input type="text" class="ip right" value="192.168.0.2" id="ipaddress" name="ipaddress" /></p>
				<p><label>Subnet Mask</label><input type="text" class="ip right" value="255.255.255.0" id="subnetmask" name="subnetmask" /></p>
				<p><label>Gateway</label><input type="text" class="ip right" value="192.168.0.1" id="gateway" name="gateway" /></p>
				<p><label>Primary DNS</label><input type="text" class="ip right" value="8.8.8.8" id="dns1" name="dns1" /></p>
				<p><label>Secondary DNS</label><input type="text" class="ip right" value="8.8.4.4" id="dns2" name="dns2" /></p>
			</div>
		</div>

		<div style="float: right; width: 45%;">
			<h4>Web Server</h4>
			<label for="webserver">Enable</label>
			<div class="onoffswitch">
				<input type="checkbox" name="webserver" class="onoffswitch-checkbox" id="webserver">
				<label class="onoffswitch-label" for="webserver">
        			<span class="onoffswitch-inner"></span>
        			<span class="onoffswitch-switch"></span>
    			</label>
			</div>

			<br/>
			<label for="webserverport">Port</label><input type="number" step="1" min="0" max="65535" value="80" id="webserverport" name="webserverport" />
			
			<hr/>
			<h4>WiFi</h4>
			<label for="wifitoggle">Enable</label>
			<div class="onoffswitch">
				<input type="checkbox" name="wifitoggle" class="onoffswitch-checkbox" id="wifitoggle">
				<label class="onoffswitch-label" for="wifitoggle">
					<span class="onoffswitch-inner"></span>
	        		<span class="onoffswitch-switch"></span>
				</label>
			</div></br>

			<label for="wifiautoconnect">Autoconnect</label>
			<div class="onoffswitch">
				<input type="checkbox" name="wifiautoconnect" class="onoffswitch-checkbox" id="wifiautoconnect">
				<label class="onoffswitch-label" for="wifiautoconnect">
					<span class="onoffswitch-inner"></span>
	        		<span class="onoffswitch-switch"></span>
				</label>
			</div></br>
		</div>

	</div>
	<div class="modalFooter"><input type="button" value="Reset Default" class="" /><input type="submit" value="Save" class="right" /></div>
</form>
<script>
	
	function toggleManualField(){
		var dhcpenabled = $('#dhcp').prop('checked');
		if(dhcpenabled){
			$('#manual_settings input').prop('disabled', true);
		} else {
			$('#manual_settings input').prop('disabled', false);
		}
	}

	$('#dhcp').change(function (){
		toggleManualField();
	});

	

	$.getJSON('assets/php/network_settings.json', function(data){
		$.each(data, function(key, value) {
			if(value == 'on'){
				$('#'+key).prop('checked', true);
			} else {
				$('#'+key).val(value);
			}
		});

		toggleManualField();
	});

	

	$('#network_settings_form').submit(function (e){
		e.preventDefault();
		var data = $(this).serialize();
		
		$.ajax({
			url: "/assets/php/set_network_settings.php",
			method: "POST",
			data: data
		}).done(function (a, s, d){
			//console.log(a);
		});
	});
</script>
