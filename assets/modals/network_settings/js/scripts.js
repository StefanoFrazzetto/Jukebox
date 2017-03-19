$.ajaxSetup({cache: false});

var network_type = $("#network_type");

var wifi_essid = $("#wifi_essid");
var wifi_password = $("#wifi_password");
var wifi_protocol = $("#wifi_protocol");
var wifi_encryption = $("#wifi_encryption");
var wifi_encryption_type = $("#wifi_encryption_type");

var modules_hash = [
    {dhcp_module: false, manual_settings: false, wifi_module: false, hotspot_module: false},
    {dhcp_module: true, manual_settings: true, wifi_module: false, hotspot_module: false},
    {dhcp_module: true, manual_settings: true, wifi_module: true, hotspot_module: false},
    {dhcp_module: false, manual_settings: false, wifi_module: false, hotspot_module: true}
];

network_type.horizontalSelector();

function toggleManualField() {
    var dhcpEnabled = $('#dhcp').prop('checked');
    if (dhcpEnabled) {
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

    for (var key in modules_hash[val]) {
        //noinspection JSUnfilteredForInLoop
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

function checkWifiNetwork() {
    $.getJSON('/assets/modals/network_settings/wifi/wifi_connected.php')
        .done(function (data) {
            console.log(data);
            if (data == null) {
                error("Failed to connect to '" + wifi_essid.val() + "'. Currently connected to no WiFi network.");
            } else if (data.ESSID == wifi_essid.val()) {
                alert("Connected successfully to '" + data.ESSID + "'.")
            } else {
                error("Failed to connect to the selected network. I am connected to '" + data.ESSID + "'.");
            }
        })
        .fail(function () {
            error("Failed to fetch the current WiFi network. You might need to change the network configuration locally.");
        })
        .always(function () {
            enableInteractions();
        });
}

function enableInteractions() {
    var btn = $('.saveBtn');
    btn.attr('disabled', false);
    btn.removeClass('disabled');
    btn.val('Save');
    modal.enable();
}

function disableInteractions() {
    modal.disable();
    var btn = $('.saveBtn');
    btn.attr('disabled', true);
    btn.addClass('disabled');
    btn.val('Wait...');
}

$('#network_settings_form').submit(function (e) {
    disableInteractions();

    e.preventDefault();
    var data = $(this).serialize();

    var requestStatus = 'fail';
    var requestError = 'Unknown error';

    $.ajax({
        url: "/assets/php/set_network_settings.php",
        method: "POST",
        timeout: 20000,
        data: data
    }).done(function (data) {
        if (data == "success")
            requestStatus = 'success';
        else {
            requestError = "An error occurred while saving. Check the logs for more.";
            console.log(data);
        }
    }).fail(function (a, b, c) {
        if (b === 'timeout') {
            requestStatus = 'timeout';
        } else {
            requestStatus = 'fail';
            requestError = "Something went wrong while contacting the saving network server. " + b;
        }

    }).always(function () {
        if (requestStatus == "fail") {
            error(requestError);
        }
        else if (network_type.val() == 2) {
            checkWifiNetwork();
        } else if (requestStatus == "timeout") {
            error("Request timed out.");
        } else {
            alert("Connected successfully.");
        }

        if (network_type.val() != 2) {
            enableInteractions();
        }
    });
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

    //noinspection JSUnresolvedVariable
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

        if (typeof selectedNetwork != "undefined") {//noinspection JSUnresolvedVariable
            network_type.val(2);
            network_type.triggerHandler('change');

            wifi_essid.val(selectedNetwork.ESSID);//noinspection JSUnresolvedVariable
            wifi_password.val(selectedNetwork.password);//noinspection JSUnresolvedVariable
            wifi_protocol.val(selectedNetwork.Protocol);//noinspection JSUnresolvedVariable
            wifi_encryption.val(selectedNetwork.encryption);//noinspection JSUnresolvedVariable
            wifi_encryption_type.val(selectedNetwork.encryption_type);

            $('#network_settings_form').submit();

            selectedNetwork = undefined;
        }
    })
    .fail(function () {
        loadDefaultConfiguration();
    });