/**
 * Created by Vittorio on 22-Oct-16.
 */
var playerStatus = {};
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
        getAlbumDetails(playerStatus.album_id, function () {
            var data = albums_storage[playerStatus.album_id];

            $('#cover').attr('src', '/jukebox/' + playerStatus.album_id + '/cover.jpg');
            $('#artist').html(data.artist);
            $('#title').html(data.title);

            populatePlaylist(data);
            trackChangedEvent();
        });
    }
    else
        console.log("Sorry, album id not provided");

    function populatePlaylist(data) {
        var div = $('#playlist-section').find('tbody');

        div.html('');

        data.songs.forEach(function (song, index) {
            var asd = $("<tr><td>" + (index + 1) + "</td><td>" + song.title + "</td><td>" + timestamp(song.length) + "</td></tr>");

            asd.click(function () {
                sendEvent('play_song', {song_no: index});
            });

            div.append(asd);
        })
    }

    function addZero(value) {
        if (value < 10)
            return '0' + value;
        else
            return value;
    }

    function timestamp(time) {
        var minutes = Math.floor(time / 60);
        var seconds = Math.floor(time - minutes * 60);
        return addZero(minutes) + ':' + addZero(seconds);
    }
}

function trackChangedEvent() {
    updateTrackProgress();

    if (playerStatus.playing)
        startTimer();
    else
        stopTimer();

    var div = $('#playlist-section').find('tbody');

    div.find('.active').removeClass('active');

    div.children().eq(playerStatus.track_no).addClass("active");
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

function radioChangeEvent() {
    var radio = radios_storage[playerStatus.isRadio];
    if (radio == false)
        return;
    $('#cover').attr('src', radio.cover);
    $('#artist').html(radio.name);
    $('#title').html("Radio station");
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

    if (oldPlayingStatus.isRadio != r.isRadio && r.isRadio != false) {
        radioChangeEvent();
    }

    if (oldPlayingStatus.track_no != r.track_no) {
        trackChangedEvent();
    }

    if (oldPlayingStatus.currentTime != r.currentTime) {
        updateTrackProgress();
    }
}

function updateTrackProgress() {
    if (typeof playerStatus.duration != "undefined") {
        var percentage = getLocalCurrentTime() / playerStatus.duration * 100;

        if (percentage > 100) {
            percentage = 100;
        }
    } else {
        percentage = 0;
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
    if (id == null || typeof id == "undefined")
        return;

    if (typeof albums_storage[id] == 'undefined') {
        loadAlbumStorage(function () {
            loadAlbumPlaylist(id, callback);
        });
    } else {
        if (typeof albums_storage[id].songs !== "undefined")
            callback();
        else
            loadAlbumPlaylist(id, callback);
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

function loadAlbumStorage(callback) {
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
                return;
            }

            console.log("Loaded", data.length, "albums.");

            if (typeof callback !== "undefined")
                callback();

        })
        .fail(function () {
            error("An error occurred while loading the albums.");
        });
}

function loadRadioStorage(callback) {
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

            if (typeof callback !== "undefined")
                callback();
        })
        .fail(function () {
            error("An error occurred while loading the radios.");
        });
}

function loadAlbumPlaylist(id, callback) {
    var address = '/assets/php/get_playlist.php?id=' + id;

    $.getJSON(address)
        .done(function (data) {
            if (data == null) {
                error("Unable to find album with id: " + id + ". It might have been deleted");
                return;
            }

            console.log("Loaded", data.length, "songs.");

            albums_storage[id].songs = data;

            if (typeof callback !== "undefined")
                callback();
        })
        .fail(function () {
            error("An error occurred while loading the playlist.");
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

    $('#remote-playlist-btn').click(function () {
        var asd = $('#playlist-section');

        asd.toggleClass("open", "close");

        // TODO use a dynamic size instead of 44px
        var property = asd.hasClass("open") ? {left: "44px"} : {left: "100%"};

        asd.animate(property, 200);
    });

    loadAlbumStorage();
    loadRadioStorage();
});