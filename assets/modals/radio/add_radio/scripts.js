var radio_name_field = $('#radioname');
var radio_url_field = $('#radiourl');
var radio_cover = $('#addRadioCover');

function addNewRadio() {
    var name = radio_name_field.val();
    var url = radio_url_field.val();
    var cover = radio_cover.attr('src');

    var request = '/assets/modals/radio/add_radio/add_radio.php?name='
        + encodeURIComponent(name)
        + '&url=' + encodeURIComponent(url)
        + '&cover=' + encodeURIComponent(cover);

    $.getJSON(request)
        .done(function (response) {
            if (response.status === 'success') {
                alert('Radio successfully added!');
                const radio = Radio.read(response.radio);

                storage.radios[radio.id] = radio;
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
            modal.openPage('/assets/modals/radio/');
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
    var radioUrl = radio_url_field.val();

    if (!validateURL(radioUrl)) {
        error("The provided URL is not valid");
        return;
    }

    var radio = makeRadioObjectFromUrl(radioUrl);


    // Put port 80 if it is not assigned from URL
    if (radio.port === "") {
        radio.port = 80;
    }


    radio = JSON.stringify(radio);

    console.log("Testing radio: ", radio);

    error("Not supported yet!");
}

function firstRadioPage() {
    $('#loading').hide();
    $('#secondStep').hide(function () {
        $('#firstStep').show();
        radio_url_field.focus();
    });
}

function secondRadioPage() {
    var url = radio_url_field.val();

    if (!validateURL(url)) {
        error("The provided URL is not valid");
        return;
    }

    $('#firstStep').hide(function () {
        $('#loading').show(function () {

        });

        var request = '/assets/modals/radio/add_radio/read_meta.php?url=' + encodeURIComponent(url);

        radio_name_field.val("");

        $.getJSON(request)
            .done(function (response) {
                radio_name_field.val(response['icy-name']);
            })
            .fail(function (z, zz, zzz) {
                //alert('something went wrong \n' + z + zz + zzz);
                //modal.openPage('/assets/modals/radio/');
                console.log(z, zz, zzz);
                console.log(url)
            })
            .always(function () {
                $('#loading').hide();
                $('#secondStep').show();
                radio_name_field.focus();
            });

    });
}

function openChangeCover() {
    imageSelector = new ImageSelector();

    imageSelector.albumArtist = false;
    imageSelector.isRadio = true;
    imageSelector.to = '/assets/modals/radio/add_radio';
    imageSelector.from = '/assets/modals/radio/add_radio';
    imageSelector.defaultArtist = radio_name_field.val();
    imageSelector.defaultAlbum = radio_url_field.val();

    imageSelector.open();
}

if (imageSelector !== undefined && imageSelector.isRadio === true) {
    radio_name_field.val(imageSelector.defaultArtist);
    radio_url_field.val(imageSelector.defaultAlbum);

    if (typeof imageSelector.imageUrl !== "undefined" && imageSelector.imageUrl !== null && imageSelector.imageUrl !== "")
        $.getJSON("/assets/modals/radio/add_radio/uploadcover.php?url=" + encodeURI(imageSelector.imageUrl), function (response) {
            if (response.status !== "success") {
                error("Error! " + response.message + ".");
            }

            var urlsss = response.url;

            radio_cover.attr('src', urlsss);
        });

    secondRadioPage();
}