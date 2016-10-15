<style type="text/css">
	.modalBody {
		text-align: center;
	}
</style>

<div class="modalHeader">
	<center>
		Format & Reset
	</center>
</div>
<div class="modalBody" data-mcs-theme="dark">

	<div id="confirm_action">
		<p><b>Do you really want to proceed?</b></p> 
		<p><b>This operation cannot be undone.<b></p><br>
		<div class="box-btn" id="confirm_action_button">Confirm</div>
		<div class="box-btn cancel" id="exit_reset">Cancel</div>
	</div>

	<div class="choice" id="format_intro">
		<p>Please click on the action that you want to perform:</p> <br>
		<div class="box-btn" id="intro_hdd_database">Format HDD & Database</div>
		<div class="box-btn" id="intro_factory">Factory Reset</div>
	</div>

	<div class="choice" id="format_hdd_database">
		<p>Click "Confirm" to remove all the albums stored in the HDD.</p> 
		<p>This operation will also remove the saved radio stations.</p><br>
		<div class="box-btn" id="confirm_hdd_database">Confirm</div>
		<div class="box-btn cancel">Cancel</div>
	</div>

	<div class="choice" id="format_factory">
		<p>Click "Confirm" to reset the Jukebox to default settings.</p> 
		<p><b>This operation cannot be undone.<b></p><br>
		<div class="box-btn" id="confirm_factory">Confirm</div>
		<div class="box-btn cancel">Cancel</div>
	</div>

	<div id="format_end">
		<p></p>
	</div>

</div>

<script type="text/javascript">
	function init() {
		$('#format_intro').hide();
		$('#format_hdd_database').hide();
		$('#format_factory').hide();
	}

	init();

	$('#confirm_action_button').click(function() {
		$('#confirm_action').hide();
		$('#format_intro').show();
	});

	$('#intro_hdd_database').click(function() {
		$('#format_intro').hide();
		$('#format_hdd_database').show();
	});

	$('#intro_factory').click(function() {
		$('#format_intro').hide();
		$('#format_factory').show();
	});

	$('.cancel').click(function() {
		$('.choice').hide();
		$('#confirm_action').show();
	});

	$('#exit_reset').click(function(){
		closeModal();
	});

	$('#confirm_hdd_database').click(function() {
		$.ajax({
			url: "assets/php/format.php?operation=format_hdd_database"
		}).done(function(data){
			init();
			$('#format_end').html("<p>" + data + "</p>");
			reload();
		});
	});

	$('#format_factory').click(function() {
		$.ajax({
			url: "assets/php/format.php?operation="
		}).done(function(data){
			init();
			$('#format_end').html("<p>" + data + "</p>");
            reload();
		});
	});

</script>