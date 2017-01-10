/**
 * Created by Vittorio on 10/01/2017.
 */

var albums_storage = [];
var radios_storage = [];
var artists_storage = [];

var cover_placeholder;

var album_store_secondary;
var is_storage_partial = false;

function load_storages(callback) {
    function pack(what) {
        var where = [];

        what.forEach(function (elem) {
            where[elem.id] = elem;
        });

        return where;
    }

    $.getJSON('/assets/API/storage.php')
        .done(function (data) {
            artists_storage = pack(data.artists);
            albums_storage = pack(data.albums);
            radios_storage = pack(data.radios);

            cover_placeholder = data.placeholder;

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
}

function restoreStorage() {
    if (is_storage_partial) {
        albums_storage = album_store_secondary;
        is_storage_partial = false;
    }
}

function backupStorage() {
    restoreStorage();
    album_store_secondary = albums_storage.slice(0); // clones database
    is_storage_partial = true;
}

