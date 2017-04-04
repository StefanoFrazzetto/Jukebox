/**
 * Created by Vittorio on 04/04/2017.
 */

var imageSelector;

function ImageSelector() {
    this.albumArtist = false;
    this.isRadio = false;
    this.to = null;
    this.from = null;
    this.imageUrl = null;
    this.defaultArtist = '';
    this.defaultAlbum = '';
    this.presetCovers = [];
    this.onDone = null;
}

ImageSelector.prototype.open = function () {
    modal.openPage('assets/modals/image_picker/');
};

ImageSelector.prototype.back = function () {
    modal.openPage(this.from);
};

ImageSelector.prototype.done = function () {
    modal.openPage(this.to);

    if (this.onDone !== null) this.onDone();
};