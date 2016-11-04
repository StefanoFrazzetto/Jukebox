<style>
    .bt-no {
        width: 10%;
    }

    .bt-name {
        width: 60%;
    }

    .bt-mac {
        width: 30%;
    }
</style>
<div class="modalHeader">Bluetooth</div>
<div id="bluetooth-modal" class="modalBody" data-mcs-theme="dark">
    <div class="mCustomScrollbar overflow-hidden">
        <table class="songsTable text-center" id="devices"
               style="min-width: 100%; padding-top: 0; margin-top: 0; margin-bottom: 30px">

            <thead>
            <tr class="th">
                <th class="bt-no">#</th>
                <th class="bt-name">Device name</th>
                <th class="bt-mac">MAC</th>
            </tr>

            </thead>

            <tr>
                <td></td>
                <td>Click scan devices to start</td>
                <td></td>
            </tr>

        </table>
    </div>
</div>
<div class="modalFooter">
    <div class="onoffswitch pull-left inline">
        <input type="checkbox" name="bluetooth" class="onoffswitch-checkbox" id="bluetooth">
        <label class="onoffswitch-label" for="bluetooth">
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
    </div>
    <div id="btn_scan" class="box-btn pull-right">SCAN DEVICES</div>
    <div id="btn_unpair" class="box-btn pull-right">UNPAIR ALL</div>
</div>
<script type="text/javascript">
    var bluetooth_switch_on;
    var keep_scanning;
    var devices;

    $(document).ready(function () {
        initBluetooth();
    });

    /**
     * Initialize the bluetooth modal.
     */
    function initBluetooth() {
        // Turn ON the bluetooth interface when this modal is opened.
        bluetooth_switch_on = true;
        devices = $('#devices');
        toggleBluetooth(bluetooth_switch_on);
        startScan();

        count = 0;
    }

    /**
     * Start the scan in continuous mode.
     */
    function startScan() {
        if ($('#bluetooth').prop('checked')) {
            $('#btn_scan').addClass("active");
            $('#btn_scan').text("SCANNING...");

            cleanDevicesList("SCANNING...");

            keep_scanning = true;
            bluetooth({action: "scan"});
        } else {
            alert('You need to turn ON the bluetooth.');
        }
    }

    function stopScan() {
        keep_scanning = false;
        $('#btn_scan').removeClass("active");
        $('#btn_scan').text("SCAN FOR DEVICES");
    }

    function bluetooth(data) {
        $.ajax({
            url: "assets/php/Bluetooth.php",
            method: "POST",
            data: data,
            cache: false
        }).done(function (res) {
            var output = JSON.parse(res);
            var devices = $('#devices');
            console.log(output);

            switch (data.action) {

                case 'scan':
                    // Clear the devices list
                    // Parse the devices found if the output contains any
                    // Bind every device found to the click event
                    if (output != null) {
//                        console.log("Found device: " + data.device_name);
                        cleanDevicesList();

                        $.each(output, function (index, value) {
                            devices.append('<tr><td>' + index + '</td><td class="device_name">' + value.device + '</td><td class="mac">' + value.mac + '</td></tr>');
                        });

                        devices.find('tbody tr').click(function () {
                            stopScan();
                            var mac_address = $(this).find('.mac').html();
                            var device_name = $(this).find('.device_name').html();
                            console.log("Trying to pair: " + mac_address);
                            bluetooth({action: 'pair', mac: mac_address, device_name: device_name});
                        });
                    } else if (keep_scanning) {
                        cleanDevicesList("SCANNING...");
                    }

                    // Stop scanning if the modal is not visible
                    if (!$('#bluetooth-modal').is(':visible')) {
                        stopScan();
                    }

                    // Keep scanning if the variable is set to true
                    if (keep_scanning) {
                        count++;
                        console.log(">>>> COUNT: " + count);
                        setTimeout(bluetooth.bind(null, {action: "scan"}), 3500);
                    }

                    output = null;

                    break;

                case 'pair':
                    if (output == "connected") {
                        console.log("Paired to: " + data.device_name);
                        alert("Successfully connected to " + data.device_name);
                    } else if (output == "failed") {
                        console.log("Pairing error: " + data.device_name + " - MAC: " + data.mac);
                        alert("Error, device not paired. Try again.");
                    }
                    break;

                case 'unpair':
                    console.log('Unpair done');
                    break;

                case 'turn_on':
                    console.log("Bluetooth " + data.action);
                    break;

                case 'turn_off':
                    stopScan();
                    console.log("Bluetooth: " + data.action);
                    break;

                default:
                    break;
            } // SWITCH
        });
    }

    $('#btn_unpair').click(function () {
        bluetooth({action: 'unpair'});
    });

    $('#btn_scan').click(function () {
        if (keep_scanning) {
            stopScan();
        } else {
            startScan();
        }
    });

    function cleanDevicesList(message) {
        if (message == undefined)
            devices.find('tbody').html('');
        else
            devices.find('tbody').html('<tr><td></td><td>' + message + '</td><td></td></tr>');
    }

    function toggleBluetooth() {
        if (bluetooth_switch_on) {
            $('#bluetooth').prop("checked", "true");
            var mode = 'turn_on';
        } else {
            $('#bluetooth').removeProp("checked");
            mode = 'turn_off';
        }

        bluetooth({action: mode});
    }

    $('#bluetooth').change(function () {
        cleanDevicesList();
        bluetooth_switch_on = !bluetooth_switch_on;
        toggleBluetooth();
    });
</script>