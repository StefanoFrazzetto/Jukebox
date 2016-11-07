<style type="text/css">
	.modalBody {
		text-align: center;
	}

	.burner-icons {
		height: 100px;
	}

	.burner-icons > div {
		background-repeat: no-repeat;
		display: inline-block;
    	text-indent: -9999px;
    	height: 100px;
    	width: 100px;
	}

	.burner-icons-copying {
		background: url("/assets/img/burner/copying.svg");
	}

	.burner-icons-decoding {
		background: url("/assets/img/burner/encoding.svg");
	}

	.burner-icons-iso {
		background: url("/assets/img/burner/ripping.svg");
	}


	.burner-icons-ripping {
		background: url("/assets/img/burner/ripping.svg");
	}

	.burner-icons-normalizing {
		background: url("/assets/img/burner/normalizing.svg");
	}

	.burner-icons-burning {
		background: url("/assets/img/burner/burning.svg");
	}

	.burner-icons-complete {
		background: url("/assets/img/burner/finished.svg");
	}


</style>

<div class="modalHeader">
	<center>Burner</center>
</div>
<div id="burner_modal" class="modalBody" data-mcs-theme="dark">

	<!-- ICONS -->
	<div class="burner-icons">
		<div class="burner-icons-copying"></div>
		<div class="burner-icons-decoding"></div>
		<div class="burner-icons-normalizing"></div>
		<div class="burner-icons-iso"></div>
		<div class="burner-icons-burning"></div>
		<div class="burner-icons-complete"></div>
	</div>
	<!-- /ICONS -->

	<!-- BASE -->
	<div id="burner-error"></div>
	<div id="burner-status"></div>
	<div id="burner-message"></div>
	<!-- /BASE -->

	<!-- STEP 1 -->
	<div id="burner_step1">
		<p>Please choose the audio format output:</p>
		
		<button class="burner_step1-set" id="burner_step1-set-wav">AUDIO CD</button>
		<button class="burner_step1-set" id="burner_step1-set-mp3" >MP3 CD</button>
		<div class="box-btn burner_step1-set" id="burner_addto_compilation">ADD TO COMPILATION</div>
		<button class="burner_step1-set" id="burner_step1-reset" ><img style="height: 90%;" src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCA0NDMgNDQzIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0NDMgNDQzOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjMycHgiIGhlaWdodD0iMzJweCI+CjxnPgoJPHBhdGggZD0iTTMyMS43ODUsMzhoLTgzLjM4NFYwSDEyNS4xNjl2MzhINDEuNzg1djYwaDI4MFYzOHogTTE1NS4xNjksMzBoNTMuMjMydjhoLTUzLjIzMlYzMHoiIGZpbGw9IiNGRkZGRkYiLz4KCTxwYXRoIGQ9Ik0yOTUuMTQyLDIxNC4zMWw1LjY2LTg2LjMxSDYyLjc2OWwxOS4wMTYsMjkwaDExNC4xNzJjLTE0Ljg2MS0yMS4wNjctMjMuNjAyLTQ2Ljc0Ni0yMy42MDItNzQuNDMgICBDMTcyLjM1NSwyNzQuNDMsMjI2Ljg0OSwyMTcuNzc5LDI5NS4xNDIsMjE0LjMxeiIgZmlsbD0iI0ZGRkZGRiIvPgoJPHBhdGggZD0iTTMwMS43ODUsMjQ0LjE0MWMtNTQuODI2LDAtOTkuNDMsNDQuNjA0LTk5LjQzLDk5LjQyOVMyNDYuOTU5LDQ0MywzMDEuNzg1LDQ0M3M5OS40My00NC42MDQsOTkuNDMtOTkuNDMgICBTMzU2LjYxMSwyNDQuMTQxLDMwMS43ODUsMjQ0LjE0MXogTTM1NS45NjEsMzc2LjUzM2wtMjEuMjEzLDIxLjIxM2wtMzIuOTYzLTMyLjk2M2wtMzIuOTYzLDMyLjk2M2wtMjEuMjEzLTIxLjIxM2wzMi45NjMtMzIuOTYzICAgbC0zMi45NjMtMzIuOTYzbDIxLjIxMy0yMS4yMTNsMzIuOTYzLDMyLjk2M2wzMi45NjMtMzIuOTYzbDIxLjIxMywyMS4yMTNsLTMyLjk2MywzMi45NjNMMzU1Ljk2MSwzNzYuNTMzeiIgZmlsbD0iI0ZGRkZGRiIvPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+CjxnPgo8L2c+Cjwvc3ZnPgo=" /></button>

	</div>
	<!-- /STEP 1 -->


	<!-- STEP 3 -->
	<div id="burner_step3">
		<div id="burner_step3-progress_bar">
			<div class='progressBar' style='width: 98%; margin-bottom: 0;'>
            	<div id='burner_step3-progress_bar-progress' class='progress' style='width: 0;'></div>
            </div>
		</div>
	</div>
	<!-- /STEP 3 -->

	<!-- STEP 2 -->
	<div id="burner_step2">
		<p>Press BURN when you're ready to start the process:</p>
		<br>
		<button id="burner_step2-back" >BACK</button>
		<button id="burner_step2-burn" >BURN</button>
	</div>
	<!-- /STEP 2 -->
</div>

<script type="text/javascript">

	function setBurnerVariables(type, content, format) {
		if(type != "") {
			input_type_value = type;
		}
		
		if(content != "") {
			input_content_values = content;
		}
		
		if(format != "") {
			output_format_value = format;
		}
	}

	function checkPlaylist() {
		if(playlist.length > 0) {
			setBurnerVariables("playlist", playlist, null);
		} else {
			setBurnerVariables(null, null, null);
			$('#burner_step1').hide();
			burnerInfoSet = setInterval(burnerInfo, 5000);
		}

		burnerHandler("check");
	}

	function resetBurner() {
		setBurnerVariables(null, null, null);
		burner_show_compilation_btn = false;
	}

	function initBurner() {
		$('#burner_step2').hide();
		$('#burner_step3').show();
		$('#burner_step1-reset').hide();
		switchOffIcons();
		error_set = false;

		/**
		*	Show the "Add to compilation" button if the user clicks "Burn Album" in the album modal, 
		*	otherwise hide the button.
		*	If there's any data, show the recycle bin button to allow the user to clear the list.
		*/
		try {
			if(burner_show_compilation_btn == true) {
				$('#burner_addto_compilation').show();
				burner_show_compilation_btn = false;
			} else {
				$('#burner_addto_compilation').hide();
			}
		} catch (e) {
			$('#burner_addto_compilation').hide();
		} finally {
			try {
				if (input_content_values.length > 0 && burner_compilation == true) {
					$('#burner_step1-set-mp3').html("MP3 COMPILATION");
					$('#burner_step1-reset').show();
				}
			} catch(e) {
				$('#burner_step1-reset').hide();
			}
		}

		try {
			if(input_type_value == null) {
				checkPlaylist();
			} else {
				burnerHandler("check");
			}
		} catch(err) {
			checkPlaylist();
		}
	}

	$(document).ready(function() {
		initBurner();
	});

	$('#burner_step1-reset').click(function() {
		setBurnerVariables(null, null, null);
		closeModal();
	});

	$('#burner_addto_compilation').click(function() {
		burner_compilation = true;
		closeModal();
	});

	/* ***************************************************** */

	// Burn album wav
	$('#burner_step1-set-wav').click(function() {
		setWavIcons();
		if(error_set == false) {
			$('#burner_step1').hide();
			setBurnerVariables("", "", "wav");
			burnerHandler("info");
		}
	});

	// Burn album mp3
	$('#burner_step1-set-mp3').click(function() {
		setMp3Icons();
		if(error_set == false) {
			$('#burner_step1').hide();
			setBurnerVariables("", "", "mp3");
			burnerHandler("info");
		}
	});

	/* ***************************************************** */

	$('#burner_step2-back').click(function() {
		$('#burner_step1').show();
		$('#burner_step2').hide();
		burnerHandler("check");
	});

	$('#burner_step2-burn').click(function() {
		$('#burner_step2').hide();
		$('#burner_step3').show();
		$('#burner-status').html("Copying");
		$('#burner-message').html("Please wait...The process has been started.");
		resetProgressBar();
		$('.burner-icons-copying').css('opacity', '1');
		burnerHandler("burn");
		burnerInfoSet = setInterval(burnerInfo, 5000);
		burner_compilation = false;
	});

	
	/* ***************************************************** */

	$('#burner_step3-stop').click(function() {
		$('#burner_step3').hide();
		$('#burner_step2').show();
		clearInterval(burnerInfoSet);
	});

	/* ***************************************************** */

	function resetProgressBar() {
		$('#burner_step3-progress_bar-progress').css('background-color', "#03a9f4");
		setProgressBar(0);
	}

	function setProgressBar(bar_percentage) {
		$('#burner_step3-progress_bar-progress').css('width', bar_percentage + "%");
		$('#burner_step3-progress_bar-progress').html(bar_percentage + "%");
	}

	function setMp3Icons() {
		$('.burner-icons-copying').show();
		$('.burner-icons-decoding').hide();
		$('.burner-icons-normalizing').show();
		$('.burner-icons-iso').show();
		$('.burner-icons-burning').show();
		$('.burner-icons-complete').show();
	}

	function setWavIcons() {
		$('.burner-icons-copying').show();
		$('.burner-icons-decoding').show();
		$('.burner-icons-normalizing').show();
		$('.burner-icons-iso').hide();
		$('.burner-icons-burning').show();
		$('.burner-icons-complete').show();
	}

	function switchOffIcons() {
		$('.burner-icons').children().css('opacity', '0.3');
	}

    function progressHandler(burner_status, next_cd) {

		switchOffIcons();
		burner_status = burner_status.toLowerCase();

		switch(burner_status) {
			case "idle":
                $('#burner_step2').show();
                break;

			case "copying":
				$('.burner-icons-copying').css('opacity', '1');
				break;

			case "decoding":
				$('.burner-icons-decoding').css('opacity', '1');
				break;

			case "normalizing":
				$('.burner-icons-normalizing').css('opacity', '1');
				break;

			case "creating iso":
				$('.burner-icons-iso').css('opacity', '1');
				break;

			case "burning":
				$('.burner-icons-burning').css('opacity', '1');
				$('#burner_step3-progress_bar-progress').css('background-color', 'red');
				break;

			case "complete":
				$('.burner-icons-complete').css('opacity', '1');
				$('#burner_step3-progress_bar-progress').css('background-color', 'green');

                clearInterval(burnerInfoSet);
                // Check for next CD
                if (output['nextCD'] == true) {
                    $('#burner_step2').show();
                    setBurnerVariables("", "nextCD", "");
                }
				break;

			default:
		}
	}

	/* ***************************************************** */

	function burnerInfo() {
		// Interrupt the handler if the modal is closed.
		if(!($('#burner_modal').is(':visible'))) {
			clearInterval(burnerInfoSet);
			setBurnerVariables(null, null, "");
		}

		$.ajax({
			url: "assets/php/burner/BurnerInfo.php",
			type: "POST",
			cache: false
		}).done(function(data){
			var output = JSON.parse(data);
			console.log(output);

			// Progress bar and icons update
            progressHandler(output['status'], output['nextCD']);

			$.each(output, function( index, value ) {
				// console.log("INDEX: " + index);
				// console.log("VALUE: " + value);

				if(index == "error") {
					error_set = true;
				}

				if(value != null && value != "") {
					$('#burner-'+index).html(index.charAt(0).toUpperCase() + index.slice(1) + ': ' +value);
				} else {
					// Remove the element if the value is empty or null.
					$('#burner-'+index).remove()
				}

				setProgressBar(output['percentage']);
			});
		});
	}

	/* ***************************************************** */

	function burnerHandler(action_value) {
		if(action_value == "check") {
			setBurnerVariables("", "", null);
		}

		$.ajax({
			url: "assets/php/burner/BurnerHandler.php",
			type: "POST",
			data: { action: action_value,
				input_type: input_type_value, 
				input_content: input_content_values, 
				output_format: output_format_value 
			},
			cache: false
		}).done(function(data){
			if(action_value == "burn") {
				setBurnerVariables(null, null, "");
			}

			var output = JSON.parse(data);
			if(output['status'] == "Error") {
				$('#burner_step2').hide();
			}

			$.each(output, function( index, value ) {
				$('#burner-'+index).html(index.charAt(0).toUpperCase() + index.slice(1) + ': ' + value);

				if(index.toLowerCase() == "error") {
					$('#burner_step2-burn').off('click');
				}
			});
		});
	}
</script>