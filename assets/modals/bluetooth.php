<div class="modalHeader">Bluetooth</div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
    <div class="top" style="margin-bottom: 80px">
        <div class="onoffswitch pull-left">
            <input type="checkbox" name="bluetooth" class="onoffswitch-checkbox" id="bluetooth">
            <label class="onoffswitch-label" for="bluetooth">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>
        <div id="btn_scan" class="box-btn pull-right">SCAN DEVICES</div>
        <div id="btn_unpair" class="box-btn pull-right">UNPAIR ALL</div>
    </div>
    <table class="songsTable text-center" id="devices" style="width: 100%; padding-top: 0; margin-top: 0; margin-bottom: 30px">
        <thead>
            <tr class="th">
                <th>#</th>
                <th>Device name</th>
                <th>MAC</th>
            </tr>
        </thead>
        <tbody>
            <tr> <td>#</td> <td></td> <td></td> </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">

    function pair(mac_address) {
        $.ajax({
            url: "assets/php/Bluetooth.php",
            method: "POST",
            data: {action: 'pair', mac: mac_address},
            cache: false
        }).done(function () {
            console.log("Device paired");
        });
    }

    $('#btn_unpair').click(function () {
        $.ajax({
            url: "assets/php/Bluetooth.php",
            method: "POST",
            data: {action: 'unpair'},
            cache: false
        }).done(function (res) {
            console.log("Unpair done");
        });
    });

    $('#btn_scan').click(function () {
        console.log("FIRE!");
        if ($('#bluetooth').prop('checked')) {
            $('#devices').find('tbody').html('<tr><td colspan="3">Loading...</td></tr>');
            bluetooth('scan');
        } else {
            alert('Turn on the bluetooth');
        }
    });

    function bluetooth(b_action) {
        $.ajax({
            url: "assets/php/Bluetooth.php",
            method: "POST",
            data: {action: b_action},
            cache: false
        }).done(function (res) {
            var output = JSON.parse(res);
            console.log(output);
            if (b_action == 'scan') {
                if(output == null) {
                    $('#devices').find('tbody').html('<tr><td colspan="3">NO DEVICES FOUND</td></td>');
                } else {
                    $('#devices').find('tbody').html('');
                }

                $.each(output, function (index, value) {
                    $('#devices').append('<tr><td>' + index + '</td><td>' + value.device + '</td><td class="mac">' + value.mac + '</td></tr>');
                });

                $('#devices').find('tbody tr').click(function () {
                    var mac_address = $(this).find('.mac').html();
                    pair(mac_address);
                    console.log(mac_address);
                });
            }
        });
    }

    function toggleBluetooth() {
        var bluetooth_on = $('#bluetooth').prop('checked');
        if (bluetooth_on) {
            var mode = 'turn_on';
        } else {
            mode = 'turn_off';
        }

        bluetooth(mode);
    }

    $('#bluetooth').change(function () {
        toggleBluetooth();
    });
</script>