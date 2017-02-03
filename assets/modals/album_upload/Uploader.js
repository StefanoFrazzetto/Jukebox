/**
 * Created by Vittorio on 03/02/2017.
 */

function Uploader() {
    this.stage = 0;
    this.uploadMethod = 0;
}

Uploader.prototype.nextPage = function () {
    this.changePage(this.stage + 1);
};

Uploader.prototype.previousPage = function () {
    this.changePage(this.stage - 1);
};

Uploader.prototype.changePage = function (page) {
    this.stage = page;

    switch (page) {
        case 0: // Intro
            openModalPage('/assets/modals/album_upload/1-Intro.php');
            break;
        case 1: // Upload
            switch (this.uploadMethod) {
                case 0: // Upload local files
                    openModalPage('/assets/modals/album_upload/uploaders/upload.php');
                    break;
                case 1: // Rip a cd in the jukebox
                    openModalPage('/assets/modals/album_upload/uploaders/rip.php');
                    break;
                case 2: // Browse USB drive plugged in the jukebox
                    openModalPage('/assets/modals/album_upload/uploaders/usb.php');
                    break;
                case 3: // Import from jukebox
                    openModalPage('/assets/modals/album_upload/uploaders/jukebox.php');
                    break;
                default: // Error
                    var msg1 = "Uploader method not defined";
                    error(msg1);
                    console.error(msg1);
                    break;
            }
            break;
        case 2: // Edit names
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

Uploader.prototype.uploadMethods = [
    {name: "Browse Local", description: "Upload songs from the current device.", icon: "file-audio-o"},
    {name: "Rip CD", description: "Store a disc in the Jukebox.", icon: "download"},
    {name: "From USB", description: "Store songs from a USB plugged in the Jukebox.", icon: "usb"},
    {name: "Remote Jukebox", description: "Import an album from a remote Jukebox via network.", icon: "music"}
];