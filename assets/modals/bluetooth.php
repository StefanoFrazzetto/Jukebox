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

    $('#btn_unpair').click(function () {
        bluetooth({action: 'unpair'});
    });

    $('#btn_scan').click(function () {
        console.log("FIRE!");
        if ($('#bluetooth').prop('checked')) {
            $('#devices').find('tbody').html('<tr><td colspan="3">Loading...</td></tr>');

            bluetooth({action: 'scan'});
        } else {
            alert('Turn on the bluetooth');
        }
    });

    function bluetooth(data) {
        $.ajax({
            url: "assets/php/Bluetooth.php",
            method: "POST",
            data: data,
            cache: false
        }).done(function (res) {
            var output = JSON.parse(res);

            switch (data.action){

                case 'scan':
                    if(output == null) {
                        $('#devices').find('tbody').html('<tr><td colspan="3">NO DEVICES FOUND</td></td>');
                    } else {
                        $('#devices').find('tbody').html('');

                        $.each(output, function (index, value) {
                            $('#devices').append('<tr><td>' + index + '</td><td>' + value.device + '</td><td class="mac">' + value.mac + '</td></tr>');
                        });

                        $('#devices').find('tbody tr').click(function () {
                            var mac_address = $(this).find('.mac').html();
                            console.log(mac_address);
                            bluetooth({action: 'pair', mac: mac_address});
                        });
                    }
                    break;

                case 'pair':
                    console.log("Pairing done. MAC: " + data.mac);
                    break;

                case 'unpair':
                    console.log('Unpair done');
                    break;

                default:
                    break;
            } // SWITCH

            console.log("OUTPUT: " + output);
        });
    }

    function toggleBluetooth() {
        var bluetooth_on = $('#bluetooth').prop('checked');
        if (bluetooth_on) {
            var mode = 'turn_on';
        } else {
            mode = 'turn_off';
        }

        bluetooth({action: mode});
    }

    $('#bluetooth').change(function () {
        toggleBluetooth();
    });
</script>