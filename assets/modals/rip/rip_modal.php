<?php
/**
 * Created by PhpStorm.
 * User: Stefano
 * Date: 07/02/2016
 * Time: 12:08
 */

?>

<style>
    .center {
        text-align: center;
        margin: auto;
        width: 80%;
        padding: 10px;
    }
</style>

<div class="modalHeader center">Ripping your disc</div>
<div id="rippingModal" class="modalBody center">
    <br>
    Status:
    <div id="ripper_status" style="display: inline;"></div>

    <div id="ripper_status_message">Loading...</div>
    <br>

    <div class='progressBar' style='width: 98%; margin-bottom: 0;'>
        <div id='percentage' class='progress' style='width: 0;'></div>
    </div>

    <br><br>
    <div class="box-btn" id="abort_process">ABORT PROCESS</div>
</div>

<script type="text/javascript">

    $('#abort_process').on('click', function(){
        $('#ripper_status_message').html('Aborting...');
        stopTracker();
        openModalPage('assets/modals/rip/abort.php');
    });


    function stopTracker(){
        clearInterval(handleTracker);
    }

    function tracker() {
        $.ajax({
            url: 'assets/modals/rip/ajax_handler.php',
            cache: false,
            success: function(json){
            	var json = $.parseJSON(json);
                var status = json['status'];
                var processed_tracks = json['processed_tracks'];
                var cd_title = json['cd_title'];
                var total_tracks = json['total_tracks'];
                var percentage = json['percentage'];

                if (cd_title !== '') {
                    $('.modalHeader').html('Ripping your disc: ' + cd_title);
                }

                if(status == 'completing the process'){
                	$('#percentage').width('100%');
                    $('#percentage').html('100%');
                    $('#ripper_status').html('Almost ready');
                    $('#ripper_status_message').hide();
                    $('.progressBar').hide();
                    openModalPage('assets/modals/add_album_rip/2.fix_titles.php');
                    stopTracker();

                }

                if(percentage == 100){
                    $('#ripper_status').html('finalizing ' + status);

                    $('#percentage').width('99%');
                    $('#percentage').html('99%');
                    $('#ripper_status_message').html('Tracks: ' + processed_tracks + ' of ' + total_tracks );
                } else {
                    $('#percentage').width(percentage+'%');
                    $('#percentage').html(percentage+'%');

                    $('#ripper_status').html('Now ' + status);
                    $('#ripper_status_message').html('Tracks: ' + processed_tracks + ' of ' + total_tracks );
                }
                // console.log(json);
            }
        })
    } 

    $(document).ready(function () {
        // Check the tracker every 10 seconds.
        handleTracker = setInterval(tracker, 10000);
    });
</script>