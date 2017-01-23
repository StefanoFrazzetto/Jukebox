<?php
session_start();
?>

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

    #covers .covers {
        padding: 15px;
        width: 28%;
    }

    #covers .active {
        border: 1px solid #03A9F4;
    }

    #covers .covers {
        width: 168px;
        height: 168px;
        margin: 10px;
        display: inline-block;
        position: relative;
        text-align: center;
    }

    #covers img {
        max-height: 100%;
        max-width: 100%;
        padding: 0 !important;
        vertical-align: middle;
        box-shadow: 0px 0px 6px 2px rgba(0, 0, 0, 0.40);
        transition: box-shadow 400ms ease;
    }

    #covers img:hover {
        box-shadow: 0px 0px 6px 2px rgba(3, 169, 244, 0.8);
    }

    #covers img.active {
        box-shadow: 0px 0px 7px 3px rgba(3, 169, 244, 0.95);
    }
</style>

<div class="modalHeader">Cover Picker</div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 350px;">
    <form id="coverPickerForm" action="assets/php/album_creation/add_cover.php">
        <input type="hidden" name="coverFrom" id="coverFrom" />
        <input type="hidden" name="uploadedCover" id="uploadedCover" />
        <input type="hidden" name="coverURL" id="coverURL" />
    </form>
    <div id="fromTheWeb">
        <p>Search and select a cover.</p>
        <center>

        	<div class="row" id="cover_info">
                <input type="text" id="artist" name="artist" placeholder="Artist" value="<?php echo @$_SESSION['albumArtist'] ?>" required />
                <input type="text" id="album" name="album" placeholder="Album"
                       value="<?php echo @$_SESSION['albumTitle'] ?>" required/>
                <div class="box-btn" id="search">SEARCH</div>
                <!-- <img id="loading-img" src="#" style="display: none;" /> -->
            </div>
            <div class="row" id="covers">
            	 <?php
                 if (isset($_SESSION['covers'])) {
		                foreach ($_SESSION['covers'] as $key => $cover) {
		                    echo "<img src=\"jukebox/tmp_uploads/$cover?". time() ."\" data-type='local' data-id='$key' class='covers local'>";
		                }

		                /*foreach ($_SESSION['thumbnails']['large'] as $thumbnail) {
		                    echo "<div class='remote_img_div' ><img src=\"$thumbnail\" data-type='local' class='all_image_style'></div>";
		                }*/
		            } else {
		                //echo "<label>Cover URL: &nbsp;<input type='url' id='coverImageURL' name='coverImageURL' style='width: 50%;'></label>";
		            }
            	?>
                <p style="display: none;">Loading...</p>
            </div>
        </center>
    </div>


</div>
<div class="modalFooter">
    <div class="box-btn pull-right" id="submit">Next</div>
    <div class="box-btn" onclick="openModalPage('assets/modals/add_album/3.add_album_details.php');">Previous</div>
</div>

<script>
    var addAlbumForm = $('#coverPickerForm');
    var submit_btn = $('#submit');

    var uploadedCover;

    function setImageURL(){
        imageURL = $('#coverImage').val();
        //console.log(imageURL);
    }

    $('#coverImageURL').bind('input propertychange', function() {
        imageURL = $('#coverImageURL').val();
    });

    function getCoverFromURL(){
        $.ajax({
            url: 'assets/php/album_creation/add_cover.php',
            type: 'POST',
            data: {coverFrom: 1, coverURL: imageURL},
            cache: false,
            success: function () {
                console.log("Remote cover saved.");
                $.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function(data) {
                    if (data === '0') {
                        openModalPage('assets/modals/add_album/5.add_album_review.php');
                    } else {
                        alert('error code: ' + data);
                    }
                });
            }
        });
    }

    function getCoverFromLocalStorage(){
        $.ajax({
            url: 'assets/php/album_creation/add_cover.php',
            type: 'POST',
            data: {coverFrom: 0, uploadedCover: uploadedCover},
            cache: false,
            success: function () {
                console.log("Local cover saved.");
                $.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function(data) {
                    if (data === '0') {
                        openModalPage('assets/modals/add_album/5.add_album_review.php');
                    } else {
                        alert('error code: ' + data);
                    }
                });
            }
        });
    }

    // Search the covers
    $('#search').on('click', function(){

        var artist = $('#artist').val();
        var album = $('#album').val();
        if(artist != '' && album != '') {
            $('#loading-img').css('display', 'block');
            $('#covers > p').css('display', 'block');

            $.ajax({
                url: 'assets/php-lib/image_sources/fetch.php',
                method: 'POST',
                data: { artist: artist, album: album },
                dataType: 'json',
                success: function(data) {
                    $('img.covers:not(.local)').remove();
                    $('#covers > p').css('display', 'none');

                    $('#loading-img').css('display', 'none');

                    // If the array is not empty
                    if(data.length === 0) {
                        $('#covers').html("No images found. Check your connection.");
                    } else {
                        // Append the cover to #covers
                        $.each(data, function(key, value){
                            $('#covers').append("<img class='covers' id='cover-"+key+"' src='"+value+"'>");
                        });
                    }

                    bind_clicks();
                    

                }
            });

        }
    });

    // Bind the onclick event to each cover
    function bind_clicks(){
        $('img.covers').on('click', function(){
            $('#covers').find('.active').removeClass("active");
            $(this).addClass("active");
            if($(this).hasClass("local")){
                uploadedCover = $(this).attr('data-id');
                coverFrom = 0;
            } else {
                coverFrom = 1;
                imageURL = $(this).attr('src');    
            }
            
        });
    }

    submit_btn.click(function(event) {
        if(coverFrom == 1)
            getCoverFromURL();
        else 
            getCoverFromLocalStorage();
    });

    $('#coverPickerForm').submit(function (e){
        e.preventDefault();
        submit_btn.click();
    });

    bind_clicks();

    try {
        $("[data-type='local']").get(0).click();
    } catch(ex) {
        console.log('There no local cover! Yeah!');
    }

    console.log();

</script>   