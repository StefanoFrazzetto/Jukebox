<?php
session_start();
?>
<div class="modalHeader">Music Upload</div>

<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 500px;">
	<div style="float: left;">
		<img onerror="this.src= 'assets/img/album-placeholder.png';" class="cover-picture" src="jukebox/ripper_encoded/cover.jpg?<?php echo time() ?>" style=" margin-left: 25px; margin-top: 25px; width: 250px; float: left;" />
	</div>

	<div class="mCustomScrollbar" style="float: right; max-height: 300px; width: 520px;">
		<div class="text-center">
			<h2><?php echo $_SESSION['albumArtist']; ?></h2>
			<h3><?php echo $_SESSION['albumTitle']; ?></h3>
		</div>
		<table class="cooltable">
			<tr>
				<th>#</th>
				<th>Title</th>
				<th>Duration</th>
			</tr>

			<?php
			$tracks = $_SESSION['tracks'];
			session_write_close();

			foreach ($tracks as $key => $track) {
				if(!isset($track['track_no'])){
					$track['track_no'] = $key; 
				}                    
				echo "
				<tr>",
					"<td>", $track['track_no'], "</td>",
					"<td>", $track['title'], "</td>",
					"<td>", gmdate("i:s", (int)$track['length']), "</td>",
					'</tr>';
				}

				?>
			</table>
		</div>

	</div>
	<div class="modalFooter">
		<div class="box-btn pull-right" id="submit">Done</div>
		<div class="box-btn pull-right hidden" id="openNewAlbum">Open Album</div>
		<span id="status"></span>
		<div class="box-btn" onclick="openModalPage('assets/modals/add_album_rip/4.add_album_cover.php');">Previous</div>
	</div>



	<script>
		var addAlbumForm = $('#finalizeAlbumForm');
		var tmp_folder = "<?php echo $_SESSION['tmp_folder']; ?>";
		var submit_btn = $('#submit');

		submit_btn.click(function(event) {
			$('#status').text('Please wait...');
			$.ajax({
                url: "assets/php/album_creation/add_album_finalize.php",
				type: "POST",
				data: {tmp_folder: tmp_folder},
				cache: false
			}).done(function (data){
				if (data !== '-1' && data % 1 === 0) {
					$('#status').text('The new album has been added successfully.');

					$('#openNewAlbum').removeClass('hidden').click(function(){
                        openModalPage('assets/modals/album_details?id=' + data);
					});

					submit_btn.addClass('hidden');
					reload();
				} else {
					alert('error code: ' + data);
				}		
			});
		});

	</script>
