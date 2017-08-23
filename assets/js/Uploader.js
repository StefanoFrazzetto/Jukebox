/**
 * Created by Vittorio on 03/02/2017.
 */

var uploader = undefined;

function Uploader() {
    this.stage = 0;
    this.uploadMethod = null;

    this.uploaderID = null;
    this.uploadingCD = null;

    this.title = null;
    this.titles = [];

    this.tracks = [];

    this.cover = null;

    this.onDone = null;
}

Uploader.prototype.continue = function () {
    this.changePage(this.stage);
};

Uploader.prototype.nextPage = function () {
    this.changePage(this.stage + 1);
};

Uploader.prototype.previousPage = function () {
    this.changePage(this.stage - 1);
};

Uploader.prototype.changePage = function (page) {
    this.stage = page;
    var self = this;

    switch (page) {
        case 0: // Intro
            if (self.uploaderID === null) {
                self.getUploaderId(function (id) {
                    self.uploaderID = id;
                    modal.openPage('/assets/modals/album_upload/1-Intro.php');
                });
            } else {
                modal.openPage('/assets/modals/album_upload/1-Intro.php');
            }
            break;
        case 1: // Upload
            switch (self.uploadMethod) {
                case 0: // Upload local files
                    modal.openPage('/assets/modals/album_upload/uploaders/upload.php');
                    break;
                case 1: // Rip a cd in the jukebox
                    modal.openPage('/assets/modals/album_upload/uploaders/rip.php');
                    break;
                case 2: // Browse USB drive plugged in the jukebox
                    error("Not implemented.");
                    this.stage--;
                    return;
                    modal.openPage('/assets/modals/album_upload/uploaders/usb.php');
                    break;
                case 3: // Import from jukebox
                    error("Not implemented.");
                    this.stage--;
                    return;
                    modal.openPage('/assets/modals/album_upload/uploaders/jukebox.php');
                    break;
                default: // Error
                    var msg1 = "Uploader method not defined";
                    error(msg1);
                    console.error(msg1);
                    break;
            }
            break;
        case 2: // Edit names
            modal.enableStatusLoading();
            $.getJSON(this.getDataJsonUrl())
                .done(function (data) {
                    uploader.title = data.title;

                    uploader.titles = data.titles;

                    uploader.tracks = mapCD(data.tracks);

                    // uploader.cover = data.cover;

                    uploader.covers = data.covers;

                    function extract(obj) {
                        if (typeof obj === "object" && obj !== undefined && obj !== null)
                            return Object.keys(obj).map(function (k) {
                                return obj[k]
                            });
                        return [];
                    }

                    function mapCD(CD) {
                        var arr = [];

                        for (var x in CD) {
                            //noinspection JSUnfilteredForInLoop
                            var cd_no = parseInt(x.replace(/^CD/, ''));
                            //noinspection JSUnfilteredForInLoop
                            arr[cd_no] = extract(CD[x]);
                        }

                        return arr;
                    }

                    modal.openPage('/assets/modals/album_upload/2-Metadata.php');
                })
                .fail(function (a, b) {
                    error(b);
                })
                .always(function () {
                    modal.disableStatusLoading();
                });

            break;
        case 3: // Add cover
            uploader.defragmentArtists();

            //noinspection JSUndeclaredVariable
            imageSelector = new ImageSelector();

            imageSelector.from = '/assets/modals/album_upload/2-Metadata.php';
            imageSelector.to = '/assets/modals/album_upload/4-Confirm.php';
            imageSelector.albumArtist = true;
            imageSelector.isRadio = false;

            imageSelector.defaultAlbum = this.title;
            imageSelector.defaultArtist = this.getAllArtists();
            imageSelector.presetCovers = this.covers;

            imageSelector.open();
            break;
        case 4: // Confirm
            modal.openPage('/assets/modals/album_upload/4-Confirm.php');
            break;
        default: // Error
            var msg2 = "Uploader page out of bound.";
            error(msg2);
            console.error(msg2);
            break;
    }
};

Uploader.prototype.getUploaderId = function (callback) {
    $.getJSON('/assets/API/uploader.php?action=get_new_id')
        .done(function (data) {
            //noinspection JSUnresolvedVariable
            if (data.status === "success" && typeof data.uploader_id !== "undefined") { //noinspection JSUnresolvedVariable
                callback(data.uploader_id);
                //noinspection JSUnresolvedVariable
                console.log("The new uploader ID is", data.uploader_id);
            }
            else
                error("Failed to retrieve the uploader id");
        })
        .fail(function (error) {
            error("Failed to retrieve the uploader id");
        });
};

Uploader.prototype.uploadMethods = [
    {
        name: "Browse Local",
        description: "Upload songs from the current device.",
        icon: "file-audio-o",
        codeName: "files"
    },
    {name: "Rip CD", description: "Store a disc in the Jukebox.", icon: "download", codeName: "ripper"},
    {name: "From USB", description: "Store songs from a USB plugged in the Jukebox.", icon: "usb", codeName: "usb"},
    {
        name: "Remote Jukebox",
        description: "Import an album from a remote Jukebox via network.",
        icon: "music",
        codeName: "import"
    }
];

Uploader.prototype.getDataJsonUrl = function () {
    var url = '/assets/API/uploader.php?action=get_tracks_json&media_source=';

    var codeName = this.uploadMethods[this.uploadMethod].codeName;

    return url + codeName + '&uploader_id=' + this.uploaderID;
};

Uploader.prototype.createSongsTable = function (container, editable) {
    function createArtistChip(index, track) {
        var name = track.artists[index];

        var artistChip = $('<div class="chip closable">' + name + '</div>');
        var closeChip = $('<i class="fa fa-close delete"></i>');

        closeChip.click(function () {
            delete track.artists[index];
            artistChip.remove();
        });

        artistChip.append(closeChip);

        return artistChip;
    }

    uploader.tracks.forEach(function (cd, cdNo) {
        if (uploader.tracks.length > 1)
            $("<tr><th colspan='4'>CD" + cdNo + "</th></tr>").appendTo(container);

        cd.forEach(function (track, no) {
            if (typeof track.title !== "string" || track.title === "") {
                track.title = "Track " + (no + 1);
            }

            var tr = $("<tr>");

            var td1 = $("<td>" + (no + 1) + "</td>");
            var td2 = $("<td></td>");

            if (editable) {
                var input = $("<input class='full-wide'/>");
                input.val(track.title);

                td2.append(input);

                td2.find('input').change(function () {
                    //noinspection JSUndefinedPropertyAssignment
                    track.title = $(this).val();
                });
            } else {
                td2.html(track.title);
            }

            var td3;

            if (editable) {
                td3 = $("<td></td>");

                if (track.artists !== null)
                    track.artists.forEach(function (_, index) {
                        td3.append(createArtistChip(index, track));
                    });

                var add = $('<div class="chip round clickable"><i class="fa fa-plus"></i></div>');

                add.click(function () {
                    var dialog = new Alert({
                        title: "Add Artist",
                        showInput: true,
                        inputPlaceholder: "Artist",
                        buttons: [
                            "Cancel",
                            {
                                text: "Add to all",
                                callback: function () {
                                    var artist = dialog.getInputValue();
                                    cd.forEach(function (track, no) {
                                        track.artists.push(artist);
                                        createArtistChip(track.artists.length - 1, track)
                                            .insertBefore(container.find('tr:eq(' + no + ')').find('.chip.round'));
                                    })
                                }
                            },
                            {
                                text: "Add",
                                callback: function () {
                                    var artist = dialog.getInputValue();
                                    track.artists.push(artist);
                                    createArtistChip(track.artists.length - 1, track).insertBefore(add);
                                }
                            }
                        ]
                    });

                    dialog.show();
                });

                td3.append(add);
            } else {
                td3 = $("<td>" + track.artists.join(', ') + "</td>");
            }

            tr.append(td1);
            tr.append(td2);
            tr.append(td3);

            container.append(tr);
        })
    });
};

Uploader.prototype.getAllArtists = function () {
    var artists = [];

    uploader.tracks.forEach(function (cd) {
        cd.forEach(function (track) {
            if (track.artists !== null && typeof track.artists === "object")
                track.artists.forEach(function (artist) {
                    if (artists.indexOf(artist) === -1) {
                        artists.push(artist);
                    }
                });
        });
    });

    return artists;
};

Uploader.prototype.done = function () {
    $.ajax({
        url: '/assets/API/uploader.php?action=create_album&uploader_id=' + uploader.uploaderID,
        type: 'POST',
        data: JSON.stringify({
            title: uploader.title,
            tracks: uploader.tracks,
            cover: uploader.cover
        }),
        contentType: 'application/json; charset=utf-8',
        dataType: 'json'
    })
        .done(function (data) {
            if (data.status === "success") {
                modal.close();
                uploader = undefined;
                reload();
                new Alert({
                    message: "Album uploaded successfully!",
                    title: "Success",
                    buttons: [
                        {
                            text: "Add another",
                            callback: function () {
                                Uploader.restart();
                            }
                        },
                        {
                            text: "Open Album",
                            class: "danger",
                            callback: function () {
                                modal.openPage('/assets/modals/album_details?id=' + data.album_id);
                            }
                        },
                        "Okay"
                    ]

                }).show();

                if (typeof sendEvent === "function") sendEvent("reload");
            } else {
                error("Failed to upload the album. " + data.message);
            }
        })
        .fail(function (error) {
            alert("Unable to upload album. " + JSON.parse(error.responseText).message);
            console.log(error);
        })
        .always(function () {
            if (typeof uploader.onDone === "function")
                uploader.onDone();
        });
};

Uploader.prototype.incrementCD = function () {
    if (this.uploadingCD === null)
        this.uploadingCD = 2;
    else
        this.uploadingCD++;
};

Uploader.abort = function () {
    uploader = undefined;

    modal.close();
};

Uploader.start = function () {
    if (uploader === undefined) {
        uploader = new Uploader();
    }

    uploader.continue();
};

Uploader.restart = function () {
    uploader = new Uploader();

    Uploader.start();
};

Uploader.prototype.defragmentArtists = function () {
    uploader.tracks.forEach(function (cd) {
        cd.forEach(function (track) {
            var _artists = [];
            track.artists.forEach(function (artist) {
                if (typeof artist !== "undefined" && artist !== null) _artists.push(artist);
            });
            //noinspection JSUndefinedPropertyAssignment
            track.artists = _artists;
        })
    });
};