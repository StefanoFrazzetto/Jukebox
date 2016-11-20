$.ajaxSetup({cache: false});

var selected_network = null;

var network_type = $("#network_type");

var modules_hash = [
    {dhcp_module: false, manual_settings: false, wifi_module: false, hotspot_module: false},
    {dhcp_module: true, manual_settings: true, wifi_module: false, hotspot_module: false},
    {dhcp_module: true, manual_settings: true, wifi_module: true, hotspot_module: false},
    {dhcp_module: false, manual_settings: false, wifi_module: false, hotspot_module: true}
];

network_type.horizontalSelector();

function toggleManualField() {
    var dhcpenabled = $('#dhcp').prop('checked');
    if (dhcpenabled) {
        //$('#manual_settings input').prop('disabled', true);
        $('#manual_settings').hide();
    } else {
        //$('#manual_settings input').prop('disabled', false);
        $('#manual_settings').show();
    }
}

$('#dhcp').change(function () {
    toggleManualField();
});

network_type.change(function () {
    var val = parseInt($(this).val());

    // Starts the wifiscan if the wifi tab is selected
    if (val === 2) {
        startScan();
    } else {
        stopScan();
    }

    for (var key in modules_hash[val]) {
        if (modules_hash[val][key]) {
            $('#' + key).show();
        } else {
            $('#' + key).hide();
        }
    }

    if (val === 2 || val === 1) {
        toggleManualField();
    }

    network_type.updateSelector();
});

$('#network_settings_form').submit(function (e) {
    e.preventDefault();
    var data = $(this).serialize();

    $.ajax({
        url: "assets/php/set_network_settings.php",
        method: "POST",
        data: data
    }).done(function () {
        alert("Network settings saved successfully!");
    }).fail(function (a, b, c) {
        error("Something went wrong while saving network settings. " + c);
    });
});

function openNetworkDetails(network) {
    selected_network = network;

    $('#wifi_details').show(animation_short);
    $('#wifitable').hide(animation_short);

    stopscan();

    $('#network_details_essid').text(selected_network['essid']);

    if (typeof selected_network['encryption_type'] === 'undefined') {
        selected_network['encryption_type'] = 'open';
    }

    $('#network_details_security').text(selected_network['encryption_type']);

    if (selected_network.saved) {
        $('#network_details_password').hide();

        $('#network_details_forget').show();
    } else {
        $('#network_details_forget').hide();

        $('#network_details_password').show();
    }
}

function closeNetworkDetails(network) {
    selected_network = null;

    $('#wifi_details').hide(animation_short);
    $('#wifiTable').show(animation_short);

    startScan();
}

$('#network_details_forget').click(function () {
    forgetNetwork(selected_network['ESSID']);
    closeNetworkDetails();
});


function loadConfigurationFromJson(data) {
    $.each(data, function (key, value) {
        var thing = $('#' + key);

        if (value == 'on') {
            thing.prop('checked', true);
        } else {
            thing.val(value);
        }

        thing.trigger('change');
        thing.triggerHandler('change');
    });

    var val = data.network_type;

    if (typeof val != "undefined" && (val == 2 || val == 1)) {
        toggleManualField();
    }
}

function loadDefaultConfiguration() {
    console.log("Loading the default network configuration file");
    $.getJSON('/assets/config/default_network_settings.json')
        .done(function (data) {
            loadConfigurationFromJson(data);
        }).fail(function () {
        error("Unable to load the default configuration file.");
    });
}


$.getJSON('/assets/config/network_settings.json')
    .done(function (data) {
        loadConfigurationFromJson(data);
    })
    .fail(function () {
        loadDefaultConfiguration();
    });