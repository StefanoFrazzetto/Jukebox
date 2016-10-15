<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 15-Oct-16
 * Time: 17:02
 */

require "../../../php-lib/Radio.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id == null) {
    echo "Invalid radio ID.";
    exit;
}

$radio = Radio::loadRadio($id);

if ($radio == null) {
    echo "Radio not found.";
    exit;
}
?>

<div class="modalHeader">Edit Radio - <?php echo $radio->getName() ?></div>

<div class="modalBody mCustomScrollbar center" data-mcs-theme="dark">
    <form id="editRadioForm">
        <input type="hidden" value="<?php echo $radio->getId() ?>" id="editRadioId" name="editRadioId">

        <div class="col-left">
            <label for="radioName">Tile</label>
            <p><input id="radioName" name="radioName" type="text" class="full-wide"
                      value="<?php echo $radio->getName() ?>"/></p>
            <hr/>
            <label for="radioUrl">URL</label>
            <p><input id="radioUrl" name="radioUrl" type="text" class="full-wide"
                      value="<?php echo $radio->getUrl() ?>"/>
            </p>
        </div>
        <div class="col-right">
            <label for="">Cover</label>
            <p>
                <img class="cover-picture" src="<?php echo $radio->getCover() ?>" width="150" id="editRadioCover">
            </p>
        </div>
        <input type="submit" class="invisible"/>
    </form>
</div>

<div class="modalFooter">
    <button id="editRadioCancel">Cancel</button>
    <button class="right" id="editRadioSave">Save</button>
</div>

<script src="/assets/js/validateURL.js"></script>

<script>
    var radioNameField = $('#radioName');
    var radioUrlField = $('#radioUrl');

    var editRadioSaveBtn = $('#editRadioSave');
    var editRadioCancelBtn = $('#editRadioCancel');

    var editRadioCoverImg = $('#editRadioCover');
    var editRadioId = parseInt($('#editRadioId').val());

    var editRadioForm = $('#editRadioForm');

    function editRadioSubmit() {
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
            {id: editRadioId, name: name, url: url})
            .done(function (response) {
                if (response.status == "success") {
                    alert("Radio edited successfully!");
                    backToRadios();
                }
                else
                    error("Radio not edited successfully. " + response.message + ".");
            })
            .fail(function (a, b, c) {
                error("Radio not edited successfully. " + c);
            });
    }

    function backToRadios() {
        openModalPage("/assets/modals/radio");
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
        alert("Take me to the edit cover modal, plz.");
        // TODO
    });

</script>