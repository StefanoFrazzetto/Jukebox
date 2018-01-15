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

	<div class="choice" id="format_intro">
		<p>Please click on the action that you want to perform:</p> <br>
		<div class="box-btn" id="intro_reset_default_settings">Reset default settings</div>
		<div class="box-btn" id="intro_hdd_database">Format HDD & Database</div>
		<div class="box-btn" id="intro_factory">Factory Reset</div>
	</div>

    <div class="choice" id="reset_default_settings">
        <p>Do you really want to reset all the settings to their default values?</p>
        <p>This operation WILL NOT remove your albums or radio stations.</p><br>
        <div class="box-btn" id="confirm_reset_default_settings">Confirm</div>
        <div class="box-btn cancel">Cancel</div>
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
        $('#reset_default_settings').hide();
		$('#format_hdd_database').hide();
		$('#format_factory').hide();
	}

	init();

    $('#intro_reset_default_settings').click(function() {
        $('#format_intro').hide();
        $('#reset_factory_setting').show();
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
		$('#format_intro').show();
	});

	$('#exit_reset').click(function(){
        modal.close();
	});

    $('#confirm_reset_default_settings').click(function() {
        $.ajax({
            url: "assets/API/format.php?operation=reset_default_settings"
        }).done(function(data){
            init();
            $('#format_end').html("<p>" + data + "</p>");
            reload();
        });
    });

	$('#confirm_hdd_database').click(function() {
		$.ajax({
            url: "assets/API/format.php?operation=format_hdd_database"
		}).done(function(data){
			init();
			$('#format_end').html("<p>" + data + "</p>");
			reload();
		});
	});

	$('#format_factory').click(function() {
		$.ajax({
            url: "assets/API/format.php?operation=factory_reset"
		}).done(function(data){
			init();
			$('#format_end').html("<p>" + data + "</p>");
            reload();
		});
	});

</script>