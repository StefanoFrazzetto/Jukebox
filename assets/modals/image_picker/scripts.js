$(function () {
    var addAlbumForm = $('#coverPickerForm');
    var submit_btn = $('#imagePickerSubmit');
    var covers = $('#covers');
    var coverImageURL = $('#coverImageURL');
    var imageURL;

    if (typeof imageSelector === 'undefined') {
        alert('imageSelector variable not found');
    }

    coverImageURL.bind('input propertychange', function () {
        imageURL = coverImageURL.val();
    });

    function extract(obj) {
        return Object.keys(obj).map(function (k) {
            return obj[k]
        });
    }

    function createCovers(data) {
        // Convert objects to arrays
        data = extract(data);
        imageSelector.presetCovers = extract(imageSelector.presetCovers);

        // Adds the preset covers at the beginning of the results
        data = imageSelector.presetCovers.concat(data);

        // If the array is not empty
        if (data.length === 0) {
            covers.html("No images found. Check your connection.");
        } else {
            // Append the cover to #covers
            $.each(data, function (key, value) {
                var imageHtml = $("<img class='covers cover-picture' id='cover-" + key + "' src='" + value + "'>");
                var containerHtml = $("<div class='cover-container'></div>");

                imageHtml.on('error', function () {
                    $(this).parent().remove();
                });

                containerHtml.html(imageHtml);

                covers.append(containerHtml);

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

    // Search the covers
    $('#search').on('click', function () {
        var artist = $('#artist').val();
        var album = $('#album').val();

        if (artist !== '' && album !== '') {
            $('#loading-img').css('display', 'block');
            covers.find('> p').css('display', 'block');

            $.ajax({
                url: '/assets/API/image_fetch.php',
                method: 'GET',
                data: {artist: artist, album: album},
                dataType: 'json',
                success: function (data) {
                    $('.cover-container').remove();
                    covers.find('> p').css('display', 'none');

                    $('#loading-img').css('display', 'none');

                    createCovers(data);
                },
                fail: function () {
                    error("Failed to fetch covers.");
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

    var searchImage = $("#searchImage");

    searchImage.submit(function (e) {
        e.preventDefault();
        $("#search").click();
    });

    $('#artist').val(imageSelector.defaultArtist);
    $('#album').val(imageSelector.defaultAlbum);

    Dropzone.autoDiscover = false;

    if (imageSelector.albumArtist === false) {
        var cont = searchImage;
        cont.find('#album').hide().val(' ');
        cont.find('#artist').addClass("large");
    }

    var ImgPickerDropzone = new Dropzone("#image_picker_dropzone");

    ImgPickerDropzone.uploadMultiple = false;

    ImgPickerDropzone.acceptedFiles = "image/jpeg,image/png,image/gif";

    ImgPickerDropzone.on("success", function (file, response) {
        var resp = $.parseJSON(response);

        if (resp.status === "error") {
            alert(resp.message);
            return;
        }

        //noinspection JSUnresolvedVariable
        imageSelector.imageUrl = resp.cover_url;

        imageSelector.done();
    });

    if (imageSelector.presetCovers.length !== 0)
        createCovers([]);
});



