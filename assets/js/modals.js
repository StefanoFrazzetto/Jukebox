var modalElement = $('#modal');

var modal = {
    modalElement: modalElement,
    modalLoaderElement: $('#modalAjaxLoader'),
    loaderGifElement: modalElement.find('.ajaxloader'),
    watchDogHandler: null,
    last_top: null,
    handleScanner: null, // Used to kill wifi scanner task
    jqxhr: null,
    autoPositioned: false,
    onModalClosed: null,

    abortRequest: function () {
        if (typeof jqxhr !== 'undefined')
            jqxhr.abort();
    },

    autoPosition: function () {
        var top = ($(document).height() - modal.height()) / 2;

        top = Math.floor(top);

        this.last_top = top;

        modal.css('top', top + "px");

        clearInterval(this.watchDogHandler);

        this.watchDogHandler = setInterval(this.watchDog, 500);
    },

    watchDog: function () {
        var top = ($(document).height() - this.modalElement.height()) / 2;

        top = Math.floor(top);

        if (top != this.last_top) {
            this.last_top = top;

            //modal.css('top', top + "px");

            this.modalElement.dequeue().animate({
                top: top + "px"
            }, animation_medium);

        }

        console.log("WOOFF!");
    },

    close: function () {
        var _this = this;

        _this.abortRequest();

        _this.modalElement.hide(animation_medium, function () {
            _this.modalLoaderElement.html('');
            _this.loaderGifElement.show();

            clearInterval(this.handleScanner);

            if (_this.onModalClosed != null) {
                _this.onModalClosed();
                _this.onModalClosed = null;
            }
        });

    },

    openPage: function (page) {
        clearInterval(this.handleScanner);
        this.loaderGifElement.show();
        this.modalLoaderElement.html('');

        var _this = this;

        this.modalElement.show(animation_medium, function () {
            _this.abortRequest();

            if (_this.autoPositioned) {
                _this.autoPosition();
            }

            _this.jqxhr = $.ajax(page)
                .done(function (done) {
                    _this.modalLoaderElement.hide();
                    _this.modalLoaderElement.fadeOut(0);
                    _this.modalLoaderElement.html(done);
                    _this.modalLoaderElement.show();

                    _this.modalLoaderElement.dequeue().fadeIn(animation_medium, function () {

                        $('#modalBody').mCustomScrollbar({
                            theme: "dark"
                        });

                        try {
                            if (typeof bindForms !== "undefined")
                                bindForms();
                        } catch (err) {
                            console.warn('Failed to bind forms in the modal');
                        }
                    });

                    _this.loaderGifElement.fadeOut(animation_medium, function () {
                        _this.loaderGifElement.hide();
                    });


                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    _this.modalLoaderElement.html('<div class="modalHeader">Error!</div><div class="modalBody"><strong>' + errorThrown + '<strong></div>');
                    // openModal();
                });
        });

    }
};