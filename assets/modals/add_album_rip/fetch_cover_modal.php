<style>
	.center {
		text-align: center;
		margin: auto;
		padding: 10px;
	}

	.row {
		margin: 30px auto;
	}

	.row > input {
		width: 40%;
	}

	.row > .bot-btx {
		padding: 5px;
	}

	.covers {
		padding: 15px;
		width: 28%;
		height: 28%;
	}

	.active {
		border: 1px solid #03A9F4;
	}

</style>

<div class="modalHeader center">Fetch ALBUM COVER</div>
<div id="fetchingModal" class="modalBody mCustomScrollbar center">
	<div class="row" id="cover_info">
		<input type="text" id="artist" name="artist" placeholder="Artist" required="required" />
		<input type="text" id="album" name="album" placeholder="Album" required="required" />
		<div class="box-btn" id="search">SEARCH</div>
		<!-- <img id="loading-img" src="#" style="display: none;" /> -->
	</div> 
	<div class="row" id="covers">
		<p style="display: none;">Loading...</p>
	</div>
</div>
<script type="text/javascript">

	$('#search').on('click', function(){

		var artist = $('#artist').val();
		var album = $('#album').val();
		if(artist != '' && album != '') {
			$('#search').attr('disabled', 'true');
			$('#loading-img').css('display', 'block');
			$('#covers > p').css('display', 'block');

			$.ajax({
				url: 'assets/php-lib/image_sources/fetch.php',
				method: 'POST',
				data: { artist: artist, album: album },
				dataType: 'json',
				success: function(data) {
					$('img.covers').remove();
					$('#covers > p').css('display', 'none');

					$('#loading-img').css('display', 'none');
					$.each(data, function(key, value){
						$('#covers').append($("<img class='covers' id='cover-"+key+"' src='"+value+"'>"));
					});

					$('img.covers').on('click', function(){
						$('#covers').find('.active').removeClass("active");
						$(this).addClass("active");
					});

				}
			});

		}
	});
</script>