<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_SESSION['cd_title'])){
	$_SESSION['albumTitle'] = $_SESSION['cd_title'];
	$_SESSION['albumArtist'] = $_SESSION['cd_title'];
}

?>
<div class="modalHeader">Add new album<?php if(isset($_SESSION['cd_title'])) echo ": ". $_SESSION['cd_title']; ?></div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 350px;">
    <form id="addAlbumForm" class="text-center" action="/assets/php/album_creation/add_album_details.php">

		<h3>Artist</h3>
			<label>
				<input type="text" id="albumArtistField" name="albumArtist" class="half-wide" placeholder="Artist" value="<?php echo @$_SESSION['albumArtist'] ?>" required/>
			</label>
		<br/>

		<hr/>

		<h3>Album Title</h3>
			<label>
				<input type="text" id="albumTitleField" name="albumTitle" class="half-wide" placeholder="Album Title" value="<?php echo @$_SESSION['albumTitle'] ?>" required/>
			</label>
		<br/>
		<br/>
		<div id="titleWarning"></div>

	</form>

</div>
<div class="modalFooter">
	<div class="box-btn pull-right" id="submit">Next</div>
	<div class="box-btn" onclick="openModalPage('assets/modals/add_album_rip/2.fix_titles.php');">Back</div>
</div>


<script>
	var addAlbumForm = $('#addAlbumForm');

	var submit_btn = $('#submit');

	var possible_albums = $('#possible_albums');
	var possible_artists = $('#possible_artists');

	var albumTitleField = $('#albumTitleField');
	var albumArtistField = $('#albumArtistField');

	$('#possible_albums *').click(function() {
		albumTitleField.attr('value', $(this).html());
		albumTitleField.change();
	});

	$('#possible_artists *').click(function() {
		albumArtistField.attr('value', $(this).html());

	});

	albumTitleField.change(function() {
		var title = $(this).val();

        $.getJSON('assets/API/check_album_exists.php?title=' + title).done(function (response) {
			if (response[0] != 0) {
				$('#titleWarning').html('Warning, there is another album with a similar name: <br> "' + response.title + '" <br> <img height= "80" src="' + response.cover_url + '"/>');
			} else {
				$('#titleWarning').text('');
			}
		});
	});

	submit_btn.click(function() {
		$.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function(data) {
			if (data === '0') {
				openModalPage('assets/modals/add_album_rip/4.add_album_cover.php');
			} else {
				alert('error code: ' + data);
			}
		});
	});

	addAlbumForm.submit(function(event) {
		event.preventDefault();
		submit_btn.click();
	});

</script>

