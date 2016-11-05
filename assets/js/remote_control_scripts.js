/**
 * Created by Vittorio on 22-Oct-16.
 */
var playerStatus = {};
var albumDetailsCache = [];
var deltaTime;
var latency;
var clockTimerHandle;

var albums_storage = [];
var radios_storage = [];

function startTimer() {
    clearInterval(clockTimerHandle);
    clockTimerHandle = setInterval(function () {
        updateTrackProgress();
    }, 100);
}

function stopTimer() {
    clearInterval(clockTimerHandle);
}

function playingStatusChangedEvent() {
    updatePlayButton();
}

function albumChangedEvent() {
    if (typeof playerStatus.album_id !== "undefined" && playerStatus.album_id != null) {
        getAlbumDetails(playerStatus.album_id, function (data) {
            $('#cover').attr('src', data.cover);
            $('#artist').html(data.artist);
            $('#title').html(data.title);
        });
    }
    else
        console.log("Sorry, album id not provided");
}

function trackChangedEvent() {
    updateTrackProgress();

    if (playerStatus.playing)
        startTimer();
    else
        stopTimer();
}

function getLocalCurrentTime() {
    // TODO remove if no improvements are shown
    var now;

    try {
        now = performance.timing.navigationStart + performance.now();
    } catch (e) {
        now = new Date().getTime();
    }

    var value = playerStatus.playing ?
    playerStatus.currentTime + (((now) - (playerStatus.timestamp + deltaTime)) / 1000)
        : playerStatus.currentTime;
    $('#debug-time').text(timestamp(value));
    return value;
}

function getThings(r) {
    var oldPlayingStatus = playerStatus;

    playerStatus = r;

    if (oldPlayingStatus.playing != r.playing) {
        playingStatusChangedEvent();
    }

    if (oldPlayingStatus.album_id != r.album_id) {
        albumChangedEvent();
    }

    if (oldPlayingStatus.track_no != r.track_no) {
        trackChangedEvent();
    }

    if (oldPlayingStatus.currentTime != r.currentTime) {
        updateTrackProgress();
    }

    $('#log').text(JSON.stringify(r, null, '\n'));
}

function updateTrackProgress() {
    var percentage = getLocalCurrentTime() / playerStatus.duration * 100;

    if (percentage > 100) {
        percentage = 100;
    }

    $('#trackProgress').width(percentage + "%");
}

function updatePlayButton() {
    if (playerStatus.playing == true) {
        $('#play-pause').find('i').removeClass('fa-play').addClass('fa-pause');
        startTimer();
    } else {
        $('#play-pause').find('i').addClass('fa-play').removeClass('fa-pause');
        stopTimer();
    }
}

function getAlbumDetails(id, callback) {
    // TODO use the local storage instead calling the details again
    if (typeof albumDetailsCache[id] == 'undefined') {
        var url_request = "assets/php/get_album_details.php?id=" + id;
        $.getJSON(url_request, callback).done(function (data) {
            albumDetailsCache[id] = data;
        });
    } else {
        var data = albumDetailsCache[id];
        callback(data);
    }
}

function timestamp(time) {
    function addZero(value) {
        if (value < 10)
            return '0' + value;
        else
            return value;
    }

    var minutes = Math.floor(time / 60);
    var seconds = Math.floor(time - minutes * 60);
    return addZero(minutes) + ':' + addZero(seconds);
}

function getDeltaTime(callback) {
    var oReq = new XMLHttpRequest();
    oReq.open("POST", getRemoteServerUrl());
    oReq.setRequestHeader('accept', 'text/time');
    oReq.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var now = new Date().getTime();

            latency = now - d;

            var delta = now - parseInt(this.responseText) - latency / 2;

            callback(delta);

            $('#debug-delta-time').text(delta + "ms");
            $('#debug-latency').text(latency + "ms");
        }
    };

    var d = new Date().getTime();

    oReq.send();
}

var results_container = $('#results');

$('#remote-search-field').on("focus keyup", function () {
    var value = $(this).val().toLowerCase();

    results_container.html('');

    if (value.length < 3)
        return;

    if (typeof albums_storage === 'undefined' || albums_storage.length == 0) {
        console.error('Album storage is empty');
        return;
    }

    albums_storage.forEach(function (album) {
            if (album.title.toLowerCase().indexOf(value) !== -1 || album.artist.toLowerCase().indexOf(value) !== -1) {
                results_container.append(makeSearchResult(album));
            }
        }
    );

    if (typeof radios_storage !== 'undefined' && radios_storage.length != 0)
        radios_storage.forEach(function (radio) {
            if (radio.name.toLowerCase().indexOf(value) !== -1) {
                results_container.append(makeSearchResult(radio, true));
            }
        });

    function makeSearchResult(album, is_radio) {
        if (typeof is_radio === "undefined")
            is_radio = false;

        var div = $('<div>');
        div.addClass('result');

        div.click(function () {
            if (!is_radio)
                sendEvent('play_album', {
                    album_id: parseInt(album.id)
                });
            else
                sendEvent('play_radio', {
                    radio_id: parseInt(album.id),
                    radio_url: album.url,
                    radio_name: album.name
                });
        });


        var img = $('<img>');

        var title = $('<div>');

        if (!is_radio)
            title.html(album.title);
        else
            title.html(album.name);
        title.addClass('title');

        var artist = $('<div>');

        if (!is_radio)
            artist.html(album.artist);
        else
            artist.text("Radio Station");

        artist.addClass('artist');

        if (!is_radio)
            img.attr('src', '/jukebox/' + album.id + '/thumb.jpg');
        else
            img.attr('src', album.thumb);

        div.append(img);

        div.append(title);
        div.append(artist);

        return div;
    }
}).blur(function () {
    setTimeout(function () {
        results_container.html('');
    }, 200);
});

function loadAlbumStorage() {
    var address = '/assets/php/get_all_album.json.php';

    $.getJSON(address)
        .done(function (data) {
            albums_storage = [];

            try {
                if (data != null)
                    data.forEach(function (data) {
                        albums_storage[data.id] = data;
                    });
            } catch (e) {

            }

            console.log("Loaded", data.length, "albums.");
        })
        .fail(function () {
            error("An error occurred while loading the albums.");
        });
}

function loadRadioStorage() {
    var address = '/assets/php/get_all_radios.json.php';

    $.getJSON(address)
        .done(function (data) {
            radios_storage = [];

            try {
                if (data != null)
                    data.forEach(function (data) {
                        radios_storage[data.id] = data;
                    });
            } catch (e) {

            }

            console.log("Loaded", data.length, "radios.");
        })
        .fail(function () {
            error("An error occurred while loading the radios.");
        });
}

$(document).ready(function () {
    var height = $('#remote-controls').outerHeight();

    $('#remote-controls-placeholder').outerHeight(height);

    getDeltaTime(function (delta) {
        deltaTime = delta;

        //noinspection JSUnresolvedFunction
        var evtSource = new EventSource(getRemoteServerUrl(), {withCredentials: true});

        evtSource.addEventListener("status", function (lol) {
            getThings($.parseJSON(lol.data));
        });
    });

    loadAlbumStorage();
    loadRadioStorage();
});

// function setDeltaTime() {
//     getDeltaTime(function (delta) {
//         deltaTime = delta;
//     })
// }