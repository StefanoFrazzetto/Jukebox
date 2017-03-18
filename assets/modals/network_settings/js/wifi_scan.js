var networks = {},
    handleScanner,
    xhr,
    networksPerPage = 5,
    wifiNetworkPage = 0;

var wifiPagination = $('#wifiPagination');


function writeErrorInTable(text) {
    $('#wifiTable').find('tbody').html("<tr><td colspan='3' class='text-center'>" + text + "</td></tr>");
}

function getWifiNetworks() {
    xhr = $.ajax({
        url: "assets/modals/network_settings/wifi/wifi_scan.php",
        type: 'POST',
        data: 'data',
        dataType: 'json',
        cache: false,
        success: function (json) {
            $.each(json, function (key, val) {
                networks[key] = val;
            });

            displayNetworks();
        },
        error: function () {
            writeErrorInTable("An error has occurred while fetching WiFi networks.");
        }
    });
}

function displayNetworks() {
    var r = [],
        j = 0,
        i = 0,
        numberOfNetworks = Object.keys(networks).length,
        numberOfPages = Math.floor((numberOfNetworks - 1) / networksPerPage) + 1;

    $.each(networks, function (key, val) {
        if (i < networksPerPage * wifiNetworkPage || i > networksPerPage * (wifiNetworkPage + 1) - 1) {
            i++;
            return;
        }

        var essid = val['ESSID'];
        var encryption = val['encryption'];
        var connected = val['connected'];
        var type = val['encryption_type'];
        var saved = val['saved'];

        var signal = val['signal'];

        var classes = "networkContainer";

        if (connected) {
            classes += " playing";
        }

        r[++j] = '<tr class="' + classes + '" data-essid="' + essid + '" data-encryption="' + encryption + '">';

        r[++j] = '<td class="wifiESSID">' + essid + '</td>';

        var icon = "";

        if (encryption == "on") {
            icon = '<i class="fa fa-lock" aria-hidden="true" title="' + type + '"></i>';
        } else if (encryption == "saved") {

        } else {
            icon = '<i class="fa fa-unlock" aria-hidden="true" title="open"></i>';
        }

        if (saved) {
            icon += ' <i class="fa fa-floppy-o" aria-hidden="true" title="saved"></i>';
        }

        if (connected) {
            icon += ' <i class="fa fa-link" aria-hidden="true" title="connected"></i>'
        }

        r[++j] = '<td class="wifiEncryption">' + icon + '</i></td>';

        r[++j] = '<td class="wifiQuality">';
        r[++j] = "<div class='progressBar' style='width: 98%; margin-bottom: 0;'>";
        r[++j] = "<div class='progress' title='" + signal + "%' style='width: " + signal + "%;' ></div></div>";
        r[++j] = '</td></tr>';
        i++;
    });

    if (numberOfNetworks) {
        $('#wifiTable').find('tbody').html(r.join('\n'));
    } else {
        $('#wifiTable').find('tbody').html("<tr><td colspan='3' class='text-center'>No WiFi networks found. :(</td></tr>");
    }

    bindWifiScannerClicks();

    wifiPagination.html("");
    for (i = 0; i < numberOfPages; i++) {
        var pageButton = $('<button>' + (i + 1) + "</button>");
        pageButton.page = i;

        pageButton.click(function () {
            wifiNetworkPage = parseInt($(this).text()) - 1;
            displayNetworks();
        });

        wifiPagination.append(pageButton);
    }
}

function stopScan() {
    console.log("Attempting to stop wifi scanner...");
    try {
        clearInterval(handleScanner);
        xhr.abort();
        console.log("scanner stopped.");
    } catch (e) {
        console.error("Error while aborting the wifi scan.");
    }
}

function startScan() {
    $(document).ready(function () {
        console.log("Starting wifi scan...");

        getWifiNetworks();
        // This will run the scanner every 1000 seconds. Doesn't look like it's getting more networks anyways
        handleScanner = setInterval(getWifiNetworks, 5000);
    });
}


//if($('#wifiTable').is(":hidden"))

$('#wifiTable').on('remove', function () {
    stopScan();
});

$('.connectbtn').click(function () {
    stopScan();
    modal.close();
});


function bindWifiScannerClicks() {
    $('.networkContainer').click(function () {
        var essid = $(this).attr('data-essid');

        new Alert({
            message: "Connect to the network?",
            title: essid,
            buttons: [
                {
                    text: "Connect!",
                    callback: function () {
                        alert("Not implemented yet!");
                    }
                },
                "Cancel"
            ]
        }).show();
    });
}

function forgetNetwork(essid, callback) {
    var network = networks[essid];

    //noinspection JSUnresolvedVariable
    if (network.saved) {
        $.ajax({
            url: "assets/modals/network_settings/wifi/wifi_forget.php?essid=" + essid
            //contentType: "application/json; charset=utf-8",
            //dataType: "json",
        }).done(function () {
            if (typeof callback !== 'undefined')
                callback();
        });
    }
}

