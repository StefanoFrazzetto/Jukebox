/**
 * Created by Vittorio on 12/12/2016.
 */

function artistSortingFunction(a, b) {
    var x = a.artist.toLowerCase();
    var y = b.artist.toLowerCase();
    var gne = x < y ? -1 : x > y ? 1 : 0;

    if (gne == 0) {
        x = a.title.toLowerCase();
        y = b.title.toLowerCase();
        return x < y ? -1 : x > y ? 1 : 0;
    }

    return gne;
}

function sortByArtist() {
    albums_storage.sort(artistSortingFunction);
}

function sortByTitle() {
    albums_storage.sort(function (a, b) {
        var x = a.title.toLowerCase();
        var y = b.title.toLowerCase();
        var gne = x < y ? -1 : x > y ? 1 : 0;

        if (gne == 0) {
            x = a.artist.toLowerCase();
            y = b.artist.toLowerCase();
            return x < y ? -1 : x > y ? 1 : 0;
        }

        return gne;
    });
}

function sortByHits() {
    albums_storage.sort(function (a, b) {
        var gne = b.hits - a.hits;

        if (gne == 0) {
            var x = a.artist.toLowerCase();
            var y = b.artist.toLowerCase();
            return x < y ? -1 : x > y ? 1 : 0;
        }

        return gne;
    });
}

function sortByLastPlayed() {
    albums_storage.sort(function (a, b) {
        return b.last_played - a.last_played;
    });
}

function sortByLastAdded() {
    albums_storage.sort(function (a, b) {
        return b.id - a.id;
    });
}

function getItSorted(method) {
    switch (method) {
        case '1':
            sortByArtist();
            break;
        case '2':
            sortByTitle();
            break;
        case '3':
            sortByHits();
            break;
        case '4':
            sortByLastPlayed();
            break;
        case '5':
            sortByLastAdded();
            break;
        default:
            sortByTitle();
            sortByArtist();
    }
}