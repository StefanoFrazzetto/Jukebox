<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../rip/rip_functions.php';
setSessionCoversURLS();
setSessionTracks();

?>
<div class="modalHeader">Titles Enhance<?php if(isset($_SESSION['cd_title'])) echo ": ". $_SESSION['cd_title']; ?></div>

<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 500px;">
	<form action="assets/php/fix_title.php" id="fixTitleForm">
		<table class="cooltable">
			<tr>
				<th>New Title</th>
				<th>Original Title</th>
				<th>File name</th>
				<th>NO</th>
			</tr>
			<?php

			$tracks = $_SESSION['tracks'];

			$previous_cd = 0;
			//$CD = $_SESSION['CD'];
			
            foreach ($tracks as $key => $track) {
            	if($previous_cd !== $track['cd']){
            		$previous_cd = $track['cd'];
            		echo '<th colspan="4">CD ', $track['cd'], '</th>';
            	}
            	
            	echo '<tr><td><input type="text" name="track', $key, '" value="', $track['title'], '"/></td><td>', $track['title'], '</td><td>', utf8_encode($track['url']), '</td><td>', utf8_encode($track['track_no']), '</td></tr>';
            }

            ?>
        </table>
    </form>
</div>
<div class="modalFooter">
	<div class="box-btn pull-right" id="submit">Next</div>
	<div class="box-btn" id="abort_process">ABORT</div>
	<div class="box-btn" onclick="openModalPage('assets/modals/rip/rip_pre.php');">Previous</div>
	<div class="box-btn center" id="nextCD">Add CD</div>
</div>
<script>
	var addAlbumForm = $('#fixTitleForm');
	var submit_btn = $('#submit');

	$('#abort_process').on('click', function(){
        openModalPage('assets/modals/rip/abort.php');
    });

	$('#nextCD').click(function() {
		$.ajax('assets/php/set_next_cd_upload.php');
		$.ajax('assets/php/empty_cdparanoia_folder.php');
		openModalPage("assets/modals/rip/rip_pre.php");
	});

	submit_btn.click(function(event) {
		$.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function(data) {
			if (data === '0') {
				openModalPage('assets/modals/add_album_rip/3.add_album_details.php');
			} else {
				alert('error code: ' + data);
			}
		});
	});

	$('#fixTitleForm').submit(function(e) {
		e.preventDefault();
		submit_btn.click();
	});

</script>
