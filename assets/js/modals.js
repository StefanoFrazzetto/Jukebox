var modal = $('#modal');
var modalLoader = $('#modalAjaxLoader');
var ajaxloadergif = $('#modal .ajaxloader');

var watchDogHandler;

var last_top = null;

var handleScanner = null; // Used to kill wifi scanner task

var jqxhr;

function openModal(callback) {
    modal.show(animation_medium, function () {

        // # Autopositioning

        // var top = ($(document).height() - modal.height()) / 2;

        // top = Math.floor(top);

        // last_top = top;

        // modal.css('top', top + "px");

        // # Watchdog

        // clearInterval(watchDogHandler);

        // watchDogHandler = setInterval(watchDog, 500);


        if (typeof callback !== 'undefined') {
            callback();
        }
    });
}

function abortRequest(){
    if (typeof jqxhr !== 'undefined')
        jqxhr.abort();
}

function closeModal() {
    abortRequest();

    modal.hide(animation_medium, function () {
        modalLoader.html('');
        ajaxloadergif.show();
    });
    //clearInterval(watchDogHandler);

    clearInterval(handleScanner);
}

function openModalPage(page) {
    clearInterval(handleScanner);
    ajaxloadergif.show();
    modalLoader.html('');

    openModal(function () {

        abortRequest();

        jqxhr = $.ajax(page)
            .done(function (done) {
                modalLoader.hide();
                modalLoader.fadeOut(0);
                modalLoader.html(done);
                modalLoader.show();

                modalLoader.dequeue().fadeIn(animation_medium, function () {

                    $('#modalBody').mCustomScrollbar({
                        theme: "dark"
                    });

                    try {
                        bindForms();
                    } catch (err) {

                    }
                });

                ajaxloadergif.fadeOut(animation_medium, function () {
                    ajaxloadergif.hide();
                });


            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                modalLoader.html('<div class="modalHeader">ERROR!</div><div class="modalBody"><strong>' + errorThrown + '<strong></div>');
                // openModal();
            });
    });

}

function watchDog() {
    var top = ($(document).height() - modal.height()) / 2;

    top = Math.floor(top);

    if (top != last_top) {
        last_top = top;

        //modal.css('top', top + "px");

        modal.dequeue().animate({
            top: top + "px"
        }, animation_medium);

    }

    console.log("WOOFF!");
}