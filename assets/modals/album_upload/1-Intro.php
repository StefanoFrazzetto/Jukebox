<div class="modalHeader">Album Uploader</div>
<div class="modalBody center">
    <p>Select how you want to upload songs to the Jukebox.</p>

    <div id="uploaderSelector"></div>

    <p id="uploaderSelectorDescription"></p>
</div>

<div class="modalFooter">
    <button>Cancel</button>
    <button class="right disabled" id="uploaderSelectorNext">Next</button>
</div>

<script src="/assets/modals/album_upload/Uploader.js"></script>

<script>
    var selector = $('#uploaderSelector');
    var uploaderSelectorNext = $('#uploaderSelectorNext');

    if (typeof uploader === "undefined")
        uploader = new Uploader();

    if (uploader.stage != 0) {
        uploader.openPage(uploader.stage);
    } else {
        uploader.uploadMethods.forEach(function (method, key) {
            var methodHtml = $("<div class='selector'></div>");

            methodHtml.append("<i class='fa fa-5x fa-" + method.icon + "'></i>");

            methodHtml.append("<p>" + method.name + "<p>");

            methodHtml.click(function () {
                $(this).siblings('.active').removeClass('active');
                $(this).addClass('active');
                uploaderSelectorNext.removeClass('disabled');

                uploader.uploadMethod = key;

                $('#uploaderSelectorDescription').html(method.description);
            });

            selector.append(methodHtml);
        });
    }

    uploaderSelectorNext.click(function (e) {
        uploader.nextPage();
    });
</script>