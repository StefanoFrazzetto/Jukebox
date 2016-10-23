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
<div class="modalBody" data-mcs-theme="dark">
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
    var continue_scanning;

    $(document).ready(function () {
        initBluetooth();
    });

    /**
     * Initialize the bluetooth modal.
     */
    function initBluetooth() {
        // Turn ON the bluetooth interface when this modal is opened.
        bluetooth_switch_on = true;
        toggleBluetooth(bluetooth_switch_on);

        startScan();
    }

    /**
     * Start the scan in continuous mode.
     */
    function startScan() {
        if ($('#bluetooth').prop('checked')) {
            $('#btn_scan').addClass("active");
            $('#btn_scan').text("SCANNING...");

            $('#devices').find('tbody').html('<tr><td></td><td>SCANNING...</td><td></td></tr>');

            continue_scanning = true;
            bluetooth({action: "scan", continous: true});
        } else {
            alert('You need to turn ON the bluetooth.');
        }
    }

    function stopScan() {
        continue_scanning = false;
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

            switch (data.action) {

                case 'scan':
                    if (output == null) {
                        devices.find('tbody').html('<tr><td></td><td>NO DEVICES FOUND.</td><td></td></td>');
                    } else {
                        devices.find('tbody').html('');

                        $.each(output, function (index, value) {
                            devices.append('<tr><td>' + index + '</td><td>' + value.device + '</td><td class="mac">' + value.mac + '</td></tr>');
                        });

                        devices.find('tbody tr').click(function () {
                            stopScan();
                            var mac_address = $(this).find('.mac').html();
                            console.log(mac_address);
                            bluetooth({action: 'pair', mac: mac_address});
                        });
                    }

                    if (data.continous && continue_scanning) {
                        setTimeout(bluetooth({action: "scan", continous: true}), 500);
                    }

                    break;

                case 'pair':
                    if (output == "connected") {
                        console.log("Successfully connected!");
                    } else if (output == "failed") {
                        console.log("Unsuccessfully not connected!");
                    }
                    console.log("Pairing done. MAC: " + data.mac);
                    break;

                case 'unpair':
                    console.log('Unpair done');
                    break;

                case 'turn_on':
                case 'turn_off':
                    console.log("Toggle bluetooth: " + data.action);
                    break;

                default:
                    break;
            } // SWITCH

            console.log("OUTPUT: " + output);
        });
    }

    $('#btn_unpair').click(function () {
        bluetooth({action: 'unpair'});
    });

    $('#btn_scan').click(function () {
        if (continue_scanning) {
            stopScan();
        } else {
            startScan();
        }
    });

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
        bluetooth_switch_on = !bluetooth_switch_on;
        toggleBluetooth();
    });
</script>