<?php
session_start();
?>

<div class="modalHeader"><center id="headerTitle">Rip DISC</center></div>
<div class="modalBody">
	<center>
		<br>
		<div>Press BEGIN PROCESS to start.</div>
		<div id="message"></div>
		<br>
		<div class="box-btn" id="start_rip">BEGIN PROCESS</div>
	</center>
</div>

<script type="text/javascript">
	$('#start_rip').on('click', function(){
        $('#message').html('Please wait. The process will start soon.');;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
		$('#start_rip').remove();
        modal.openPage('assets/modals/rip/rip_modal.php');
	});

	$.ajax({
		url: "assets/modals/rip/gettitle.php",
		success: function (data) {
			if(data != "") {
				$('#headerTitle').html("Rip DISC" + ": " + data);
			}
		}
	});
</script>