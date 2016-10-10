<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../php-lib/dbconnect.php';

$query = "SELECT * FROM radio_stations";

$results = $mysqli->query($query);
?>

<div class="modalHeader">Radio Stations</div>


<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
    <div class="aRadio plus cover-container" onclick="openModalPage('/assets/modals/radio/add_radio/index.php')">
        <div>+</div>
        <span>Add new radio</span>
    </div>

    <?php while ($result = $results->fetch_object()) {

        $parsed_address = parse_url($result->url);

        if (!isset($parsed_address['port'])) {
            $parsed_address['port'] = 80;
        }


        ?>
        <div class="aRadio cover-container" data-id="<?php echo $result->id ?>"
             data-url='<?php echo json_encode($parsed_address) ?>'
             data-name="<?php echo $result->name ?>">
            <div class="badge"><i class="fa fa-trash"></i></div>
            <img src="/assets/img/album-placeholder.png" class="covers"/>
            <span><?php echo $result->name ?></span>
        </div>
    <?php } ?>

</div>

<link rel="stylesheet" type="text/css" href="/assets/modals/radio/style.css">

<script>
    $(".aRadio:not(.plus)").click(function () {
        playRadio($(this).attr('data-url'), $(this).attr('data-name'));
    });

    $(".aRadio:not(.plus) .badge").click(function (e) {
        var id = $(this).parent().attr('data-id');
        deleteRadio(id);
        e.stopPropagation();
    });

</script>
