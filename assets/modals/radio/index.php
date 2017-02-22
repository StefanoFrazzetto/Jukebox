<?php

require_once '../../../vendor/autoload.php';

use Lib\Radio;

$radios = Radio::getAllRadios();
?>

<div class="modalHeader">Radio Stations</div>

<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
    <div class="aRadio plus cover-container" onclick="modal.openPage('/assets/modals/radio/add_radio/index.php')">
        <div>+</div>
        <span>Add new radio</span>
    </div>

    <?php foreach ($radios as $radio) {
    ?>
        <div class="aRadio cover-container" data-id="<?php echo $radio->getId() ?>"
             data-url='<?php echo json_encode($radio->getParsedAddressed()) ?>'
             data-name="<?php echo $radio->getName() ?>">
            <img src="<?php echo $radio->getCoverThumb() ?>" class="covers"/>
            <div class="badge delete"><i class="fa fa-trash"></i></div>
            <div class="badge badge-left edit"><i class="fa fa-pencil"></i></div>
            <span><?php echo $radio->getName() ?></span>
        </div>
    <?php 
} ?>

</div>

<link rel="stylesheet" type="text/css" href="/assets/modals/radio/style.css">

<script>
    $(".aRadio:not(.plus)").click(function () {
        var id = parseInt($(this).attr('data-id'));
        if (storage.getRadio(id)) {
            player.playRadio(storage.getRadio(id));
        } else {
            error("Radio not found in local database");
        }
    });

    $(".aRadio:not(.plus) .delete").click(function (e) {
        var id = $(this).parent().attr('data-id');
        storage.deleteRadio(id);
        e.stopPropagation();
    });

    $(".aRadio:not(.plus) .edit").click(function (e) {
        var id = $(this).parent().attr('data-id');
        modal.openPage("/assets/modals/radio/edit_radio?id=" + id);
        e.stopPropagation();
    });

</script>
