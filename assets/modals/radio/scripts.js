function addNewRadio() {
    var name = $('#radioname').val();
    var url = $('#radiourl').val();

    var request = 'assets/modals/radio/add_radio.php?name=' + encodeURIComponent(name) + '&url=' + encodeURIComponent(url);

    console.log(name, url, request);

    $.getJSON(request, function(response) {
        if (response.status == 'success') {
            alert('Radio successfully added!');
        }
    });

}

function makeRadioObjectFromUrl(url){
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

    console.log(radio);

    playRadio(radio);
}

function firstRadioPage() {
    $('#loading').hide();
    $('#secondStep').hide(function() {
        $('#firstStep').show();
    });
}


function secondRadioPage() {
    var url = $('#radiourl').val();

    $('#firstStep').hide(function() {
        $('#loading').show(function() {

        });


        var request = 'assets/modals/radio/read_meta.php?url=' + encodeURIComponent(url);

        console.log(request);

        $.getJSON(request)
        .done(function(response) {
            console.log(response);

            $('#loading').hide();



            $('#secondStep').show();
        })
        .fail(function(z, zz, zzz) {
            alert('something went wrong \n' + z + zz + zzz);
        });

    });
}