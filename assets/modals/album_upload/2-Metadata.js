/**
 * Created by Vittorio on 16/02/2017.
 */

$(function () {
    // This should set the right stage in the uploader
    // when jumping to this modal from the cover picker.
    uploader.stage = 2;

    var metaDataSongsTableBody = $('#metaDataSongsTableBody');
    var metaDataTitlesList = $('#metaDataTitlesList');
    var metaDataAlbumTitle = $('#metaDataAlbumTitle');
    var metaDataAlbumArtist = $('#metaDataAlbumArtist');

    function drawTable() {
        metaDataSongsTableBody.html('');
        uploader.createSongsTable(metaDataSongsTableBody, true);
    }

    $('#btnBack').click(function () {
        uploader.previousPage();
    });

    $('#btnNext').click(function () {
        if (metaDataAlbumTitle.val() === "") {
            error("Album title is required.");
        } else {
            uploader.nextPage();
        }

    });

    $('#btnCancel').click(function () {
        Uploader.abort();
    });


    metaDataAlbumTitle.change(function () {
        uploader.title = $(this).val();
    });

    if (typeof uploader.getAllArtists()[0] !== "undefined")
        metaDataAlbumArtist.val(uploader.getAllArtists()[0]);


    metaDataAlbumArtist.change(function () {
        var artist = $(this).val();

        uploader.tracks.forEach(function (cd) {
            cd.forEach(function (track) {
                    track.artists = [artist];
                }
            )
        });

        drawTable();
    });

// metaDataTitlesList.html('');
// uploader.titles.forEach(function (title) {
//     var button = $("<button>" + title + "</button>");
//     button.click(function () {
//         metaDataAlbumTitle.val(title);
//     });
//     metaDataTitlesList.append(button);
// });

    if (uploader.title !== null) {
        metaDataAlbumTitle.val(uploader.title);
    }

    drawTable();
});
