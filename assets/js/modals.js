var modalElement = $('#modal');

var modal = {
    modalElement: modalElement,
    modalLoaderElement: $('#modalAjaxLoader'),
    loaderGifElement: modalElement.find('.modalContainer'),
    watchDogHandler: -1,
    last_top: null,
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

        if (top !== this.last_top) {
            this.last_top = top;

            //modal.css('top', top + "px");

            this.modalElement.dequeue().animate({
                top: top + "px"
            }, animation_medium);

        }

        console.log("Watchdog executed!");
    },

    close: function () {
        var _this = this;

        _this.abortRequest();

        _this.modalElement.hide(animation_medium, function () {
            _this.modalLoaderElement.html('');
            _this.loaderGifElement.show();

            if (_this.onModalClosed !== null) {
                _this.onModalClosed();
                _this.onModalClosed = null;
            }
        });

    },

    open: function (page, options) {
        this.openPage('/assets/modals/' + page, options);
    },

    openPage: function (page, options) {
        this.enableStatusLoading();
        this.modalLoaderElement.html('');

        if (typeof options !== "undefined") {
            if (options.isSettings = true)
                this.enableSettingStatus();
            else
                this.disableSettingsStatus();

            if (options.sidebar !== undefined) {
                this.sidebar = this.generateSidebar(options.sidebar);
            }
        } else {
            this.disableSettingsStatus();
            this.sidebar = undefined;
        }

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
                        const modalBody = $('.modalBody');

                        if (typeof _this.sidebar !== 'undefined') {
                            var div = $('<div class="barsContainer">');

                            _this.sidebar.appendTo(div);
                            modalBody.detach().appendTo(div);

                            $('.modalHeader').after(div);
                        }


                        modalBody.find('.mCustomScrollbar').mCustomScrollbar({
                            theme: "dark"
                        }).on('remove', function () {
                            $(this).mCustomScrollbar('destroy');
                        });

                        try {
                            if (typeof bindForms !== "undefined")
                                bindForms();
                        } catch (err) {
                            console.warn('Failed to bind forms in the modal');
                        }
                    });

                    _this.disableStatusLoading();
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    _this.modalLoaderElement.html('<div class="modalHeader">Error!</div><div class="modalBody"><strong>' + errorThrown + '<strong></div>');
                    // openModal();
                });
        });

    },

    disable: function () {
        modalElement.addClass("disabled");
    },

    enable: function () {
        modalElement.removeClass("disabled");
    },

    enableStatusLoading: function () {
        this.loaderGifElement.show();
        this.modalLoaderElement.hide();
    },

    disableStatusLoading: function () {
        this.loaderGifElement.fadeOut(animation_medium, function () {
            modal.loaderGifElement.hide();
            modal.modalLoaderElement.show();
        });
    },

    enableSettingStatus: function () {
        modalElement.addClass("bigger sidebar");
    },

    disableSettingsStatus: function () {
        modalElement.removeClass("bigger sidebar");
    },

    openSettings: function (page) {
        const sidebar = [
            {
                name: "Home",
                openSettings: "settings.php",
                icon: "home"
            },
            {
                name: "Stats",
                openSettings: "stats",
                icon: "heartbeat"
            },
            {
                name: "EQ",
                openSettings: "eq",
                icon: "sliders fa-rotate-90"
            },
            {
                name: "Network",
                openSettings: "network_settings/",
                icon: "wifi"
            },
            {
                name: "Bluetooth",
                icon: "bluetooth"
            },

            {
                name: "Updates",
                openSettings: "update",
                icon: "cloud-download"

            },
            {
                name: "Themes",
                openSettings: "theme",
                icon: "paint-brush"
            },
            {
                name: "Ports",
                openSettings: "ports",
                icon: "plug"
            }


        ];

        this.open(page, {sidebar: sidebar, isSettings: true});
    },

    generateSidebar: function (obj) {
        const sidebar = $('<ul class="multiselect" data-mcs-theme="dark">');

        obj.forEach(function (t) {
            if (typeof t === "string") {
                sidebar.append($('<li>').html(t));
            } else if (typeof t === 'object') {
                var element = $('<li>').html(t.name);

                if (typeof t.openSettings !== 'undefined')
                    element.click(function () {
                        modal.openSettings(t.openSettings);
                    });

                if (typeof t.icon !== 'undefined')
                    element.prepend("<i class='fa fa-" + t.icon + "'>");

                sidebar.append(element);
            } else {
                throw new Error("Invalid sidebar element provided");
            }

        });

        return $('<div class="modalSidebar">').append(sidebar);
    }
};