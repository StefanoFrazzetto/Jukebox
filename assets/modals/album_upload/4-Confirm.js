/**
 * Created by Vittorio on 18/02/2017.
 */

$('#btnBack').click(function () {
    uploader.previousPage();
});

$('#btnConfirm').click(function () {
    uploader.done();
});

$(function () {
    var confirmTitleHeader = $('#confirmTitleHeader');
    var confirmArtistsHeader = $('#confirmArtistsHeader');
    var confirmCoverImg = $('#confirmCoverImg');
    var confirmSongsTableBody = $('#confirmSongsTableBody');

    confirmTitleHeader.html(uploader.title);

    if (uploader.cover !== null) {
        confirmCoverImg.attr('src', uploader.cover);
    }

    uploader.createSongsTable(confirmSongsTableBody, false);
});




