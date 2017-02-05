<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">

    <div class="col-row">
        <div class="col-left">
            <input type="text" placeholder="Album Title" title="Album Title" class="full-wide" id="metaDataAlbumTitle"/>
        </div>
        <div class="col-right">
            <div id="metaDataTitlesList">
            </div>
        </div>
    </div>

    <div>
        <hr/>
        <table class="cooltable">
            <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Artist</th>
                <th>File Name</th>
            </tr>
            </thead>
            <tbody id="metaDataSongsTableBody"></tbody>
        </table>
    </div>

</div>
<div class="modalFooter">
    <button id="btnBack">Back</button>
    <button class="right" id="btnNext">Next</button>
</div>

<script src="/assets/modals/album_upload/Uploader.js"></script>

<script>
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
            var td2 = $("<td><input type='text' class='full-wide' value='" + track.title + "'/></td>");
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
</script>