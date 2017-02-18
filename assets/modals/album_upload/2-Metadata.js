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
    uploader.nextPage();
});

uploader.createSongsTable(metaDataSongsTableBody, true);

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