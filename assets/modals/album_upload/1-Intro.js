/**
 * Created by Vittorio on 16/02/2017.
 */
var selector = $('#uploaderSelector');
var uploaderSelectorNext = $('#uploaderSelectorNext');

if (typeof uploader === "undefined")
    uploader = new Uploader();

uploader.uploadMethods.forEach(function (method, key) {
    if (isJukebox && method.codeName === 'files')
        return;

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


uploaderSelectorNext.click(function () {
    uploader.nextPage();
});

$('#btnCancel').click(function () {
    Uploader.abort();
});