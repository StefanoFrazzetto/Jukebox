<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 15-Oct-16
 * Time: 17:02.
 */
require_once '../../../../vendor/autoload.php';

use Lib\ICanHaz;
use Lib\Radio;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id == null) {
    echo 'Invalid radio ID.';
    exit;
}

$radio = Radio::loadRadio($id);

if ($radio == null) {
    echo 'Radio not found.';
    exit;
}
?>

<div class="modalHeader">Edit Radio - <?php echo $radio->getName() ?></div>

<div class="modalBody mCustomScrollbar center" data-mcs-theme="dark">
    <form id="editRadioForm">
        <input type="hidden" value="<?php echo $radio->getId() ?>" id="editRadioId" name="editRadioId">

        <div class="col-left">
            <label for="radioName">Tile</label>
            <p><input id="radioName" name="radioName" class="full-wide"
                      value="<?php echo $radio->getName() ?>"/></p>
            <hr/>
            <label for="radioUrl">URL</label>
            <p><input id="radioUrl" name="radioUrl" class="full-wide"
                      value="<?php echo $radio->getUrl() ?>"/>
            </p>
        </div>
        <div class="col-right">
            <label for="">Cover</label>
            <p>
                <img class="cover-picture" src="<?php echo $radio->getCover() ?>" width="150"
                     id="editRadioCover">
            </p>
        </div>
        <input type="submit" class="invisible"/>
    </form>
</div>

<div class="modalFooter">
    <button id="editRadioCancel">Cancel</button>
    <button class="right" id="editRadioSave">Save</button>
</div>

<?php ICanHaz::js(['$/validateURL.js', 'scripts.js'], true, true);