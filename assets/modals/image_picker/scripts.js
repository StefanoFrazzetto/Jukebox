var addAlbumForm = $('#coverPickerForm');
var submit_btn = $('#imagePickerSubmit');
var imageURL;

if (typeof imageSelector === 'undefined') {
    alert('imageSelector variable not found');
}

$('#coverImageURL').bind('input propertychange', function () {
    imageURL = $('#coverImageURL').val();
});

// Search the covers
$('#search').on('click', function () {

    var artist = $('#artist').val();
    var album = $('#album').val();

    var covers = $('#covers');
    if (artist != '' && album != '') {
        $('#loading-img').css('display', 'block');
        covers.find('> p').css('display', 'block');

        $.ajax({
            url: 'assets/php-lib/image_sources/fetch.php',
            method: 'POST',
            data: {artist: artist, album: album},
            dataType: 'json',
            success: function (data) {
                $('.cover-container').remove();
                covers.find('> p').css('display', 'none');

                $('#loading-img').css('display', 'none');

                // If the array is not empty
                if (data.length === 0) {
                    covers.html("No images found. Check your connection.");
                } else {
                    // Append the cover to #covers
                    $.each(data, function (key, value) {
                        var imagehtml = $("<img class='covers cover-picture' id='cover-" + key + "' src='" + value + "'>");
                        var cointainerhtml = $("<div class='cover-container'></div>");

                        imagehtml.on('error', function () {
                            $(this).parent().remove();
                        });

                        cointainerhtml.html(imagehtml);

                        covers.append(cointainerhtml);

                        submit_btn.addClass('disabled');
                    });
                }

                // Bind the onclick event to each cover
                $('img.covers').on('click', function () {
                    covers.find('.active').removeClass("active");
                    $(this).addClass("active");
                    imageURL = $(this).attr('src');
                    submit_btn.removeClass('disabled');
                });

            }
        });

    }
});

submit_btn.click(function () {
    imageSelector.imageUrl = imageURL;

    imageSelector.done();
});

addAlbumForm.submit(function (e) {
    e.preventDefault();
    submit_btn.click();
});

$("#searchImage").submit(function (e) {
    e.preventDefault();
    $("#search").click();
});

$('#artist').val(imageSelector.defaultArtist);
$('#album').val(imageSelector.defaultAlbum);

Dropzone.autoDiscover = false;

$(function () {
    var ImgPickerDropzone = new Dropzone("#image_picker_dropzone");

    ImgPickerDropzone.uploadMultiple = false;

    ImgPickerDropzone.acceptedFiles = "image/jpeg,image/png,image/gif";

    ImgPickerDropzone.on("success", function (file, response) {


        var resp = $.parseJSON(response);

        if (resp.status === "error") {
            alert(resp.message);
            return;
        }

        imageSelector.imageUrl = resp.cover_path;

        imageSelector.done();

    });
});



