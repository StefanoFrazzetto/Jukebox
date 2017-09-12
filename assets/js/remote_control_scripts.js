/**
 * Created by Vittorio on 22-Oct-16.
 */
var playerStatus = {};
var deltaTime;
var latency;
var clockTimerHandle;

var remoteControls = $('#remote-controls');
var playlistSection = $('#playlist-section');
var menuSection = $('#menu-section');
var results_container = $('#results');
var cover = $('#cover');
var trackDiv = $('.track');
var artistDiv = $('.artist');
var titleDiv = $('.title');

// The margin imposed by the css layout
var margin = parseInt(remoteControls.css('padding-left'));

//region Timer
function startTimer() {
    clearInterval(clockTimerHandle);
    clockTimerHandle = setInterval(function () {
        updateTrackProgress();
    }, 100);
}

function stopTimer() {
    clearInterval(clockTimerHandle);
}

//endregion

//region Event Handlers
function playingStatusChangedEvent() {
    updatePlayButton();
}

function albumChangedEvent() {
    if (typeof playerStatus.album_id !== "undefined" && playerStatus.album_id !== null && playerStatus.album_id === parseInt(playerStatus.album_id, 10) && playerStatus.album_id > 0) {
        var album = storage.getAlbum(playerStatus.album_id);

        cover.attr('src', album.getFullCoverUrl());
        artistDiv.html(album.getArtistsNames());
        titleDiv.html(album.title);
    }
    else console.log("Sorry, album id not provided");
}

function playlistChangeEvent(songs) {
    var div = $('#playlist-section').find('tbody');

    div.html('');

    songs.forEach(function (song, index) {
        var tr = $("<tr>").attr("data-track-id", song.id);

        var td1 = $("<td>").html(song.track_no);
        var td2 = $("<td>").html(song.title);
        var td3 = $("<td>").html(song.getHHMMSS());

        tr.append(td1, td2, td3);

        tr.click(function (e) {
            sendEvent('play_song', {song_no: index});
            e.preventDefault();
        });

        div.append(tr);
    });

    trackChangedEvent();
}

function trackChangedEvent() {
    updateTrackProgress();

    if (playerStatus.playing)
        startTimer();
    else
        stopTimer();

    var tbody = $('#playlist-section').find('tbody');

    tbody.children('.active').removeClass('active');

    var row = tbody.children('[data-track-id="' + playerStatus.track_id + '"]').addClass("active");

    var text = row.find("td:eq(1)").html();

    trackDiv.html(text);
}

function radioChangeEvent() {
    var radio = storage.getRadio(playerStatus.isRadio);
    if (radio === false)
        return;
    cover.attr('src', radio.cover);
    artistDiv.html(radio.name);
    titleDiv.html("Radio station");
}

function storageChangedEvent() {
    console.log("Detected changes in albums storage.");
    storage.loadAll();
}

function updateTrackProgress() {
    if (typeof playerStatus.duration !== "undefined" && playerStatus.duration !== null) {
        var percentage = getLocalCurrentTime() / playerStatus.duration * 100;

        if (percentage > 100) {
            percentage = 100;
        }
    } else percentage = 0;

    $('#trackProgress').width(percentage + "%");
}

function updatePlayButton() {
    if (playerStatus.playing === true) {
        $('#play-pause').find('i').removeClass('fa-play').addClass('fa-pause');
        startTimer();
    } else {
        $('#play-pause').find('i').addClass('fa-play').removeClass('fa-pause');
        stopTimer();
    }
}

function handleSearch() {
    var value = $(this).val().toLowerCase();

    results_container.html('');

    if (value.length < 3)
        return;

    var artists = [];

    storage.artists.forEach(function (artist) {
            if (artist.name.toLowerCase().indexOf(value) !== -1) {
                artists.push(artist.id);
            }
        }
    );

    var result = [];

    storage.albums.forEach(function (album) {
        if (album.title.toLowerCase().indexOf(value) !== -1 || (storage.intersect(album.artists, artists)).length > 0) {
                result.push(album);
            }
        }
    );

    result.sort(storage.artistSortingFunction);

    result.forEach(function (album) {
        results_container.append(makeSearchResult(album))
    });

    if (typeof storage.radios !== 'undefined' && storage.radios.length !== 0)
        storage.radios.forEach(function (radio) {
            if (radio.name.toLowerCase().indexOf(value) !== -1) {
                results_container.append(makeSearchResult(radio, true));
            }
        });

    function makeSearchResult(album, is_radio) {
        if (typeof is_radio === "undefined")
            is_radio = false;

        var div = $('<div>');
        div.addClass('result');

        div.click(function (e) {
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

            e.preventDefault();
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
            artist.html(storage.makeArtistsString(album.artists));
        else
            artist.text("Radio Station");

        artist.addClass('artist');

        if (!is_radio)
            img.attr('src', '/jukebox/' + album.id + '/thumb.jpg');
        else
        //noinspection JSUnresolvedVariable
            img.attr('src', album.thumb);


        div.append(img);

        div.append(title);
        div.append(artist);

        return div;
    }
}

//endregion

//region Time formatter
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

//endregion

//region Loaders
function getDeltaTime(callback) {
    var oReq = new XMLHttpRequest();
    oReq.open("POST", getRemoteServerUrl());
    oReq.setRequestHeader('accept', 'text/time');
    oReq.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
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

function loadAlbumPlaylist(id, callback) {
    var address = '/assets/API/playlist.php?id=' + id;

    $.getJSON(address)
        .done(function (data) {
            if (data === null) {
                error("Unable to find album with id: " + id + ". It might have been deleted");
                return;
            }

            console.log("Loaded", data.length, "songs.");

            if (typeof callback !== "undefined")
                callback(data);
        })
        .fail(function () {
            error("An error occurred while loading the playlist.");
        });
}

//noinspection JSUnusedGlobalSymbols
function getAlbumDetails(id, callback) {
    if (id === null || typeof id === "undefined")
        return;

    if (storage.getAlbum(id) === null) {
        storage.loadAll(function () {
            loadAlbumPlaylist(id, callback);
        });
    }
}

function getLocalCurrentTime() {
    // TODO remove if no improvements are shown
    var now;

    try {
        //noinspection JSUnresolvedVariable
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

function updateRemoteStatus(r) {
    var oldPlayingStatus = playerStatus;

    function checkChange(key) {
        if (typeof r[key] !== "undefined" && oldPlayingStatus[key] !== r[key]) {
            playerStatus[key] = r[key];
            return true;
        } else {
            return false;
        }
    }

    if (checkChange('playing')) {
        playingStatusChangedEvent();
    }

    if (checkChange('album_id')) {
        albumChangedEvent();
    }

    if (checkChange('isRadio') && r.isRadio !== false) {
        radioChangeEvent();
    }

    if (checkChange('track_id')) {
        trackChangedEvent();
    }

    if (checkChange('current_time')) {
        updateTrackProgress();
    }

    if (oldPlayingStatus.storageId !== undefined && checkChange('storageId')) {
        storageChangedEvent();
    }

    if (checkChange('volume')) {
        volumeChangeEvent();
    }

    if (checkChange('playlist')) {
        try {
            r.playlist = Song.readMany(r.playlist);
            playlistChangeEvent(r.playlist);
        } catch (e) {
            console.error("Error while parsing songs.");
        }
    }

    Object.keys(r).forEach(function (t) {
        playerStatus[t] = r[t];
    });

    function volumeChangeEvent() {
        $('#debug-volume').val(r.volume);
        $('#volume-slider').slider('value', r.volume);

        var icon = $('#volume-icon');

        icon.removeClass();
        if (r.volume === 0) {
            icon.addClass("fa fa-volume-off");
        } else if (r.volume < .5) {
            icon.addClass("fa fa-volume-down");
        } else {
            icon.addClass("fa fa-volume-up");
        }
    }
}

//endregion

//region Touch events
var xDown = null;
var yDown = null;

function handleTouchStart(evt) {
    xDown = evt.touches[0].clientX;
    yDown = evt.touches[0].clientY;
}

function handleTouchMove(evt) {
    if (!xDown || !yDown) {
        return;
    }

    var xUp = evt.touches[0].clientX;
    var yUp = evt.touches[0].clientY;

    var xDiff = xDown - xUp;
    var yDiff = yDown - yUp;
    if (Math.abs(xDiff) + Math.abs(yDiff) > 120) { //to deal with to short swipes

        if (Math.abs(xDiff) > Math.abs(yDiff)) {/*most significant*/
            if (xDiff > 0) {/* left swipe */
                if (menuSection.hasClass("open"))
                    menuSection.slider.close();
                else
                    playlistSection.slider.open();
            } else {/* right swipe */
                if (playlistSection.hasClass("open"))
                    playlistSection.slider.close();
                else
                    menuSection.slider.open();
            }
        }
        /* reset values */
        xDown = null;
        yDown = null;
    }
}

document.body.addEventListener('touchstart', handleTouchStart, false);
document.body.addEventListener('touchmove', handleTouchMove, false);
//endregion

//region Playlist Slider
playlistSection.slider = {};

playlistSection.slider.openValue = margin;
playlistSection.slider.closeValue = "100%";

playlistSection.slider.open = function () {
    playlistSection
        .removeClass("close")
        .addClass("open")
        .animate({"left": playlistSection.slider.openValue}, 200);
};

playlistSection.slider.close = function () {
    playlistSection
        .removeClass("open")
        .addClass("close")
        .animate({"left": playlistSection.slider.closeValue}, 200);
};

playlistSection.slider.toggle = function () {
    if (playlistSection.hasClass("open"))
        playlistSection.slider.close();
    else
        playlistSection.slider.open();
};
//endregion

//region Menu Slider
menuSection.slider = {};

menuSection.slider.openValue = -(margin - 1);
menuSection.slider.closeValue = "-100%";

menuSection.slider.open = function () {
    menuSection
        .removeClass("close")
        .addClass("open")
        .animate({"left": menuSection.slider.openValue}, 200);
};

menuSection.slider.close = function () {
    menuSection
        .removeClass("open")
        .addClass("close")
        .animate({"left": menuSection.slider.closeValue}, 200);
};

menuSection.slider.toggle = function () {
    if (menuSection.hasClass("open"))
        menuSection.slider.close();
    else
        menuSection.slider.open();
};
//endregion

$(document).ready(function () {
    margin = parseInt(remoteControls.css('padding-left'));

    cover.height(cover.width());

    $('#remote-playlist-btn').click(function (e) {
        playlistSection.slider.toggle();
        e.preventDefault();
    });

    $('#remote-menu-btn').click(function (e) {
        menuSection.slider.toggle();
        e.preventDefault();
    });

    $('#remote-search-field').on("focus keyup", handleSearch).blur(function () {
        setTimeout(function () {
            results_container.html('');
        }, 250);
    });

    $('#debug-volume').change(function () {
        sendEvent("set_volume", {value: parseFloat($(this).val())});
    });

    $('#volume-slider').slider({
        //Config
        range: "min",
        animate: "fast",
        min: 0,
        step: 0.01,
        max: 1,
        change: function (event, ui) {
            sendEvent("set_volume", {value: ui.value});
        }
    });

    $('#volume-icon').click(function () {
        sendEvent("set_volume", {value: 0});
    });

    $.getScript('/assets/js/storage.js', function () {
        storage.loadAll(function () {
            getDeltaTime(function (delta) {
                deltaTime = delta;

                //noinspection JSUnresolvedFunction
                var evtSource = new EventSource(getRemoteServerUrl(), {withCredentials: true});

                evtSource.addEventListener("status", function (lol) {
                    updateRemoteStatus($.parseJSON(lol.data));
                });
            });
        });
    });
});

$(window).resize(function () {
    cover.height(cover.width());
});