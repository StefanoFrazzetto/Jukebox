/**
 * Created by Vittorio on 18/02/2017.
 */

$('#btnBack').click(function () {
    uploader.previousPage();
});

$('#btnConfirm').click(function () {
    var btn = $(this);

    uploader.onDone = function () {
        btn.removeClass('disabled');
    };

    btn.addClass('disabled');

    uploader.done();
});

$('#btnCancel').click(function () {
    Uploader.abort();
});

$(function () {
    // This should set the right stage in the uploader
    // when jumping to this modal from the cover picker.
    uploader.stage = 4;

    var confirmTitleHeader = $('#confirmTitleHeader');
    var confirmArtistsHeader = $('#confirmArtistsHeader');
    var confirmCoverImg = $('#confirmCoverImg');
    var confirmSongsTableBody = $('#confirmSongsTableBody');

    confirmTitleHeader.html(uploader.title);
    confirmArtistsHeader.html(uploader.getAllArtists().join(', '));

    if (imageSelector.imageUrl !== null) {
        uploader.cover = imageSelector.imageUrl;
    }

    if (uploader.cover !== null) {
        confirmCoverImg.attr('src', uploader.cover);
    }

    uploader.createSongsTable(confirmSongsTableBody, false);
});




