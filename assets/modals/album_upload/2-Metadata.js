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

uploader.tracks.forEach(function (cd) {
    cd.forEach(function (track, no) {
        var tr = $("<tr>");

        var td1 = $("<td>" + (no + 1) + "</td>");
        var input = $("<input type='text' class='full-wide'/>");
        input.val(track.title);
        var td2 = $("<td></td>");
        td2.append(input);
        var td3 = $("<td>" + track.main_artist + "</td>");
        var td4 = $("<td>" + track.url + "</td>");

        td2.find('input').change(function () {
            track.title = $(this).val();
        });

        tr.append(td1);
        tr.append(td2);
        tr.append(td3);
        tr.append(td4);

        metaDataSongsTableBody.append(tr);
    })
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