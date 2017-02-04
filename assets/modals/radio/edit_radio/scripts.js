/**
 * Created by Vittorio on 15-Oct-16.
 */
var radioNameField = $('#radioName');
var radioUrlField = $('#radioUrl');

var editRadioSaveBtn = $('#editRadioSave');
var editRadioCancelBtn = $('#editRadioCancel');

var editRadioCoverImg = $('#editRadioCover');
var editRadioId = parseInt($('#editRadioId').val());

var editRadioForm = $('#editRadioForm');

var editRadioImg = null;

function editRadioSubmit(goback) {
    if (typeof goback == "undefined")
        goback = true;

    var name = radioNameField.val();
    var url = radioUrlField.val();

    if (name.length == 0) {
        error("Radio name must not be empty.");
        return;
    }

    if (url.length == 0) {
        error("Radio name must not be empty.");
        return;
    }

    if (!validateURL(url)) {
        error("Please, provide a valid url.");
        return;
    }

    $.getJSON("/assets/modals/radio/edit_radio/edit_radio.php",
        {id: editRadioId, name: name, url: url, cover: editRadioImg})
        .done(function (response) {
            if (response.status == "success") {
                alert("Radio edited successfully!");


                if (goback)
                    backToRadios();
                else {
                    var src = editRadioCoverImg.attr("src");
                    editRadioCoverImg.attr("src", response.cover);
                }
            }
            else
                error("Radio not edited successfully. " + response.message + ".");
        })
        .fail(function (a, b, c) {
            error("Radio not edited successfully. " + c);
        });
}

function backToRadios() {
    modal.openPage("/assets/modals/radio");
}

editRadioSaveBtn.click(function () {
    editRadioSubmit();
});

editRadioForm.submit(function (e) {
    editRadioSubmit();
    e.preventDefault();
});

editRadioCancelBtn.click(function () {
    backToRadios();
});

editRadioCoverImg.click(function () {
    initImageSelectorObject();

    imageSelector.isRadio = true;
    imageSelector.isRadioUploaded = true;
    imageSelector.from = "/assets/modals/radio/edit_radio?id=" + editRadioId;
    imageSelector.to = "/assets/modals/radio/edit_radio?id=" + editRadioId;
    imageSelector.albumArtist = false;
    imageSelector.defaultArtist = radioNameField.val();
    imageSelector.open();
});

if (imageSelector.imageUrl != null) {

    editRadioImg = imageSelector.imageUrl;

    editRadioSubmit(false);

    initImageSelectorObject();
    editRadioImg = null;
}
