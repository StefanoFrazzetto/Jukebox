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

        <?php while($result = $results->fetch_object()) { 

            $parsed_address = parse_url($result->url);

            if(!isset($parsed_address['port'])){
                $parsed_address['port'] = 80;
            }
            

            ?>
            <div class="aRadio" data-id="<?php echo $result->id ?>" data-url='<?php echo json_encode($parsed_address) ?>' data-name="<?php echo $result->name ?>">
                <div class="badge"><i class="fa fa-trash"></i></div>
                <img src="assets/img/album-placeholder.png" />
                <span><?php echo $result->name ?></span>
            </div>
            <?php } ?>


                <div class="aRadio plus" onclick="openModalPage('assets/modals/radio/upload_1.php')">
                    <div>+</div>
                    <span>Add new radio</span>
                </div>
    </div>

    <style>
        .aRadio {
            width: 128px;
            height: 128px;
            display: inline-block;
            position: relative;
            cursor: pointer;
            overflow: hidden;
        }
        
        .aRadio img {
            width: 100%;
            height: 100%;
        }
        
        .aRadio span {
            padding: 2px;
            text-align: center;
            width: 100%;
            position: absolute;
            bottom: 0;
            right: 0;
        }
        
        .plus div {
            font-size: 120px;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            font-style: normal;
            text-align: center;
            vertical-align: middle;
            line-height: 105px;
        }

        .icon-bubble {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 30px;
            height: 30px;
            transition: 200ms all;
            text-align: center;
            line-height: 31px;
            text-shadow: 0px 0px 8px rgba(50, 50, 50, 0.9);
            background-color: rgba(50, 50, 50, 0.5);
            opacity: 0.8;
            border-radius: 30px;
        }

    </style>
    <script>
        $(".aRadio:not(.plus)").click(function() {
            playRadio($(this).attr('data-url'), $(this).attr('data-name'));
        });

        $(".aRadio:not(.plus) .badge").click(function(e) {
            var id = $(this).parent().attr('data-id');
            deleteRadio(id);
            e.stopPropagation();
        });

    </script>
