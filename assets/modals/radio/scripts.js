function addNewRadio() {
    var name = $('#radioname').val();
    var url = $('#radiourl').val();

    var request = '/assets/modals/radio/add_radio/add_radio.php?name=' + encodeURIComponent(name) + '&url=' + encodeURIComponent(url);

    $.getJSON(request)
        .done(function (response) {
            if (response.status == 'success') {
                alert('Radio successfully added!');
            } else {
                console.log(response);
                alert("ERROR! Radio station not saved successfully!");
            }
        })
        .fail(function (x, xx, xxx) {
            alert("ERROR! Radio station not saved successfully! Network error!");
            console.error(x, xx, xxx);
        })
        .always(function () {
            openModalPage('/assets/modals/radio/');
        });

}

function makeRadioObjectFromUrl(url) {
    var uri = parseUri(url);

    return {
        scheme: uri.protocol,
        host: uri.host,
        port: uri.port,
        path: uri.path
    }
}

function testRadio() {
    var radioUrl = document.getElementById('radiourl').value;

    var radio = makeRadioObjectFromUrl(radioUrl);

    radio = JSON.stringify(radio);

    console.log("Testing radio: ", radio);

    playRadio(radio);
}

function firstRadioPage() {
    $('#loading').hide();
    $('#secondStep').hide(function () {
        $('#firstStep').show();
    });
}


function secondRadioPage() {
    var url = $('#radiourl').val();

    $('#firstStep').hide(function () {
        $('#loading').show(function () {

        });


        var request = '/assets/modals/radio/add_radio/read_meta.php?url=' + encodeURIComponent(url);

        $.getJSON(request)
            .done(function (response) {
                console.log(response);

                $('#radioname').val(response['icy-name']);
            })
            .fail(function (z, zz, zzz) {
                //alert('something went wrong \n' + z + zz + zzz);
                //openModalPage('/assets/modals/radio/');
                console.log(z, zz, zzz);
                console.log(url)
            })
            .always(function () {
                $('#loading').hide();
                $('#secondStep').show();
            });

    });
}