/**
 * Created by Vittorio on 03/02/2017.
 */

var uploader = undefined;

function Uploader() {
    this.stage = 0;
    this.uploadMethod = null;

    this.uploaderID = null;

    this.title = null;
    this.titles = [];

    this.tracks = [];
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
                    modal.openPage('/assets/modals/album_upload/uploaders/usb.php');
                    break;
                case 3: // Import from jukebox
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
            $.getJSON(this.getDataJsonUrl())
                .done(function (data) {
                    uploader.title = data.title;

                    uploader.titles = data.titles;

                    uploader.tracks = mapCD(data.tracks);

                    function mapCD(CD) {
                        var arr = [];

                        for (var x in CD) {
                            //noinspection JSUnfilteredForInLoop
                            var cd_no = parseInt(x.replace(/^CD/, ''));
                            //noinspection JSUnfilteredForInLoop
                            arr[cd_no] = CD[x];
                        }

                        return arr;
                    }

                    modal.openPage('/assets/modals/album_upload/2-Metadata.php');
                })
                .fail(function (a, b) {
                    error(b);
                });

            break;
        case 3: // Add cover
            break;
        case 4: // Confirm
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

Uploader.start = function () {
    if (uploader === undefined) {
        uploader = new Uploader();
    }

    uploader.continue();
};