/**
 * Created by Vittorio on 16/02/2017.
 */
var metaDataSongsTableBody = $('#metaDataSongsTableBody');
var metaDataTitlesList = $('#metaDataTitlesList');
var metaDataAlbumTitle = $('#metaDataAlbumTitle');

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

uploader.createSongsTable(metaDataSongsTableBody, true);

metaDataAlbumTitle.change(function () {
    uploader.title = $(this).val();
});

metaDataTitlesList.html('');
uploader.titles.forEach(function (title) {
    var button = $("<button>" + title + "</button>");
    button.click(function () {
        metaDataAlbumTitle.val(title);
    });
    metaDataTitlesList.append(button);
});

if (uploader.title !== null) {
    metaDataAlbumTitle.val(uploader.title);
}