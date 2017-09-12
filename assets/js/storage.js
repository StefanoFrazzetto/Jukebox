/**
 * Created by Vittorio on 10/01/2017.
 */

var storage = {
    //region Init
    albums: [],
    albumsFiltered: [],
    radios: [],
    artists: [],
    cover_placeholder: null,
    //endregion Init

    //region Loader
    loadAll: function (callback) {
        $.getJSON('/assets/API/storage.php')
            .done(function (data) {
                storage.artists = pack(data.artists, Artist);
                storage.albums = pack(data.albums, Album);
                storage.radios = pack(data.radios, Radio);
                storage.albumsFiltered = storage.albums.slice();

                //noinspection JSUnresolvedVariable
                storage.cover_placeholder = data.placeholder;

                console.log("Loaded", memorySizeOf(data), "of storage.");
            })
            .fail(function (a, b, c) {
                error("Failed to load album storage. " + b);
                console.log(c)
            })
            .always(function () {
                if (typeof callback === "function")
                    callback();
            });

        function pack(what, Type) {
            var where = [];

            what.forEach(function (elem) {
                if (typeof Type !== "undefined")
                    elem = Type.read(elem);

                where[elem.id] = elem;
            });

            return where;
        }

        function memorySizeOf(obj) {
            var bytes = 0;

            function sizeOf(obj) {
                if (obj !== null && obj !== undefined) {
                    switch (typeof obj) {
                        case 'number':
                            bytes += 8;
                            break;
                        case 'string':
                            bytes += obj.length * 2;
                            break;
                        case 'boolean':
                            bytes += 4;
                            break;
                        case 'object':
                            var objClass = Object.prototype.toString.call(obj).slice(8, -1);
                            if (objClass === 'Object' || objClass === 'Array') {
                                for (var key in obj) {
                                    if (!obj.hasOwnProperty(key)) continue;
                                    sizeOf(obj[key]);
                                }
                            } else bytes += obj.toString().length * 2;
                            break;
                    }
                }
                return bytes;
            }

            function formatByteSize(bytes) {
                if (bytes < 1024) return bytes + " bytes";
                else if (bytes < 1048576) return (bytes / 1024).toFixed(3) + " KiB";
                else if (bytes < 1073741824) return (bytes / 1048576).toFixed(3) + " MiB";
                else return (bytes / 1073741824).toFixed(3) + " GiB";
            }

            return formatByteSize(sizeOf(obj));
        }
    },
    //endregion Loader

    //region Getters
    getAlbum: function (id) {
        return this.albums[id];
    },

    getArtist: function (id) {
        if (typeof this.artists[id] !== "undefined")
            return this.artists[id];
        else
            return null;
    },

    getRadio: function (id) {
        if (typeof this.radios[id] !== "undefined")
            return this.radios[id];
        else
            return null;
    },

    getVersionId: function () {
        var count = 0;

        this.albums.forEach(function (t) {
            if (t !== undefined) count++;
        });

        const last = this.albums.length - 1;
        return this.albums[last].id + "-" + count;
    },
    //endregion Getters

    //region Stringifiers
    getArtistName: function (artistId) {
        if (this.getArtist(artistId) === null)
            return "[NO ARTIST]";
        else
            return this.getArtist(artistId).name;
    },

    makeArtistsString: function (artists) {
        if (typeof artists !== "object")
            return '';

        var artists_names = [];

        artists.forEach(function (art) {
            artists_names.push(storage.getArtistName(art));
        });

        return artists_names.join(', ');
    },
    //endregion Stringifiers

    //region Sorting
    getItSorted: function (method) {
        switch (method) {
            case '1':
                this.sortByArtist();
                break;
            case '2':
                this.sortByTitle();
                break;
            case '3':
                this.sortByHits();
                break;
            case '4':
                this.sortByLastPlayed();
                break;
            case '5':
                this.sortByLastAdded();
                break;
            default:
                this.sortByArtist();
        }
    },

    artistSortingFunction: function (a, b) {
        var x = storage.getArtistName(a.artists[0]).toLowerCase();
        var y = storage.getArtistName(b.artists[0]).toLowerCase();
        var gne = x < y ? -1 : x > y ? 1 : 0;

        if (gne === 0) {
            x = a.title.toLowerCase();
            y = b.title.toLowerCase();
            return x < y ? -1 : x > y ? 1 : 0;
        }

        return gne;
    },

    sortByArtist: function () {
        storage.albumsFiltered.sort(this.artistSortingFunction);
    },

    sortByTitle: function () {
        this.albumsFiltered.sort(function (a, b) {
            var x = a.title.toLowerCase();
            var y = b.title.toLowerCase();
            var gne = x < y ? -1 : x > y ? 1 : 0;

            if (gne === 0) {
                x = this.getArtistName(a.artists[0]).toLowerCase();
                y = this.getArtistName(b.artists[0]).toLowerCase();
                return x < y ? -1 : x > y ? 1 : 0;
            }

            return gne;
        });
    },

    sortByHits: function () {
        storage.albumsFiltered.sort(function (a, b) {
            //noinspection JSUnresolvedVariable
            var gne = b.hits - a.hits;

            if (gne === 0) {
                //noinspection JSUnresolvedVariable
                return b.last_played - a.last_played;
            }

            return gne;
        });
    },

    sortByLastPlayed: function () {
        storage.albumsFiltered.sort(function (a, b) {
            //noinspection JSUnresolvedVariable
            return b.last_played - a.last_played;
        });
    },

    sortByLastAdded: function () {
        storage.albumsFiltered.sort(function (a, b) {
            return b.id - a.id;
        });
    },
    //endregion Sorting

    //region Delete
    deleteAlbum: function (id) {
        new Alert({
            message: "Are you sure you want to delete this album?",
            title: "Confirm",
            buttons: [
                {
                    text: "Yes, delete it",
                    callback: function () {
                        $.ajax('assets/API/delete_album.php?id=' + id).done(function () {
                            modal.close();
                            delete storage.albums[id];
                            storage.albumsFiltered = storage.albums.slice();
                            paginate();
                        });
                    }
                },
                "No, keep it"
            ]
        }).show();
    },

    deleteRadio: function (id) {
        if (confirm("Are you sure?")) {
            $.getJSON('assets/API/delete_radio.php?id=' + id).done(function (response) {
                if (response.status === "success") {
                    $('.aRadio[data-id="' + id + '"]').remove();
                }
                else {
                    error("Error while deleting Radio. " + response.message + ".");
                    console.log(response.message);
                }
                delete storage.radios[id];
            });
        }
    },
    //endregion Delete

    //region Array Intersect
    intersect: function (a, b) {
        var t;
        if (b.length > a.length) { //noinspection CommaExpressionJS
            t = b, b = a, a = t;
        } // indexOf to loop over shorter
        return a.filter(function (e) {
            return b.indexOf(e) !== -1;
        });
    }
    //endregion Array Intersect
};

//region Song
function Song() {
    this.id = null;
    this.album_id = null;
    this.cd = null;
    this.artists = [];
    this.track_no = null;
    this.title = null;
    this.url = null;
    this.length = null;
}

Song.prototype.read = function (data) {
    this.id = data.id;
    this.album_id = data.album_id;
    this.cd = data.cd;
    this.artists = data.artists;
    this.track_no = data.track_no;
    this.title = data.title;
    this.url = data.url;
    this.length = data.length;
};

Song.prototype.getUrl = function () {
    return '/jukebox/' + this.album_id + '/CD' + this.cd + '/' + this.url;
};

Song.prototype.getArtistsNames = function () {
    return storage.makeArtistsString(this.artists);
};

Song.prototype.getHHMMSS = function () {
    return Song.toHHMMSS(this.length);
};

Song.readMany = function (rawSongs) {
    var songs = [];

    Object.keys(rawSongs).forEach(function (key) {
        songs[parseInt(key)] = Song.read(rawSongs[key]);
    });

    return songs;
};

Song.read = function (rawSong) {
    var song = new Song();
    song.read(rawSong);
    return song;
};

Song.toHHMMSS = function (num) {
    var sec_num = parseInt(num, 10); // don't forget the second param
    var hours = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours < 10) {
        hours = "0" + hours;
    }
    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    if (seconds < 10) {
        seconds = "0" + seconds;
    }
    var time = minutes + ':' + seconds;

    if (hours !== "00") {
        time = hours + ':' + time;
    }

    return time;
};
//endregion SONG

//region Artist
function Artist(id, name) {
    this.id = id;
    this.name = name;
}

Artist.read = function (data) {
    return new Artist(data.id, data.name);
};
//endregion Artist

//region Album
function Album(id, title, cover, artists, hits, last_played) {
    this.id = id;
    this.title = title;
    this.cover = cover;
    this.artists = artists;
    this.hits = hits;
    this.last_played = last_played;
}

Album.read = function (data) {
    return new Album(data.id, data.title, data.cover, data.artists, data.hits, data.last_played);
};

Album.prototype.getArtistsNames = function () {
    return storage.makeArtistsString(this.artists);
};

Album.prototype.getCoverUrl = function () {
    if (this.cover !== null)
        return "/jukebox/" + this.id + "/thumb.jpg?" + this.cover;
    else
        return storage.cover_placeholder;
};

Album.prototype.getFullCoverUrl = function () {
    if (this.cover !== null)
        return "/jukebox/" + this.id + "/cover.jpg?" + this.cover;
    else
        return storage.cover_placeholder;
};

Album.prototype.play = function () {
    player.playAlbum(this.id);
};
//endregion Artist

//region Radio
function Radio(id, name, url, cover) {
    this.id = id;
    this.name = name;
    this.url = url;
    this.cover = cover;
}

Radio.read = function (data) {
    return new Radio(data.id, data.name, data.url, data.cover);
};

Radio.prototype.getCoverUrl = function () {
    if (this.cover !== null)
        return "/jukebox/radio-covers/" + this.id + "/thumb.jpg?" + this.cover;
    else
        return storage.cover_placeholder;
};

Radio.prototype.getCoverUrl = function () {
    if (this.cover !== null)
        return "/jukebox/radio-covers/" + this.id + "/cover.jpg?" + this.cover;
    else
        return storage.cover_placeholder;
};

Radio.prototype.play = function () {
    player.playRadio(this);
};
//endregion Artist