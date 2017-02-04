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

    .covers {
        padding: 15px;
        width: 28%;
    }

    .active {
        border: 1px solid #03A9F4;
    }

    .covers {
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
        padding: 0;
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
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 500px;">
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
                <input type="text" id="album" name="album" placeholder="Album title" value="<?php echo @$_SESSION['albumTitle'] ?>" required />
                <div class="box-btn" id="search">SEARCH</div>
                <!-- <img id="loading-img" src="#" style="display: none;" /> -->
            </div>
            <div class="row" id="covers">
                <p style="display: none;">Loading...</p>
            </div>
        </center>
    </div>


</div>
<div class="modalFooter">
    <div class="box-btn pull-right" id="submit">Next</div>
    <div class="box-btn" onclick="modal.openPage('assets/modals/add_album_rip/3.add_album_details.php');">Previous</div>
</div>

<script>
    var addAlbumForm = $('#coverPickerForm');
    var submit_btn = $('#submit');
    var savePath = "<?php echo $_SESSION['tmp_folder']; ?>";

    function setImageURL(){
        imageURL = $('#coverImage').val();
        console.log(imageURL);
    }

    $('#coverImageURL').bind('input propertychange', function() {
        imageURL = $('#coverImageURL').val();
        console.log(imageURL);
    });

    function getCoverFromURL(){
        $.ajax({
            url: 'assets/php/album_creation/add_cover.php',
            type: 'POST',
            data: {coverFrom: 1, coverURL: imageURL, savePath: savePath},
            cache: false,
            success: function () {
                console.log("Cover saved.");
                $.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function(data) {
                    if (data === '0') {
                        modal.openPage('assets/modals/add_album_rip/5.add_album_review.php');
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
                url: 'assets/API/image_fetch.php',
                method: 'GET',
                data: { artist: artist, album: album },
                dataType: 'json',
                success: function(data) {
                    $('img.covers').remove();
                    $('#covers > p').css('display', 'none');

                    $('#loading-img').css('display', 'none');

                    // If the array is not empty
                    if (data == null || data.length === 0) {
                        $('#covers').html("No images found. Check your connection.");
                    } else {
                        // Append the cover to #covers
                        $.each(data, function(key, value){
                            $('#covers').append("<img class='covers' id='cover-"+key+"' src='"+value+"'>");
                        });
                    }

                    // Bind the onclick event to each cover
                    $('img.covers').on('click', function(){
                        $('#covers').find('.active').removeClass("active");
                        $(this).addClass("active");
                        imageURL = $(this).attr('src');
                        console.log(imageURL);
                    });

                }
            });

        }
    });

    submit_btn.click(function(event) {
        if(typeof imageURL != 'undefined'){
            getCoverFromURL();
        } else {

            $.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function(data) {
                if (data === '0') {
                    modal.openPage('assets/modals/add_album_rip/5.add_album_review.php');
                } else {
                    alert('error code: ' + data);
                }
            });
        }
    });

    $('#coverPickerForm').submit(function (e){
        e.preventDefault();
        submit_btn.click();
    });
</script>
