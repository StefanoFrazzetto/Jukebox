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
    if (typeof playerStatus.album_id !== "undefined" && playerStatus.album_id != null) {
        getAlbumDetails(playerStatus.album_id, function () {
            var album = storage.getAlbum(playerStatus.album_id);

            cover.attr(album.getFullCoverUrl());
            $('#artist').html(album.getArtistsNames());
            $('#title').html(album.title);

            populatePlaylist(album);
            trackChangedEvent();
        });
    }
    else
        console.log("Sorry, album id not provided");

    function populatePlaylist(data) {
        var div = $('#playlist-section').find('tbody');

        div.html('');

        data.songs.forEach(function (song, index) {
            var asd = $("<tr data-track-id='" + song.id + "'><td>" + (index + 1) + "</td><td>" + song.title + "</td><td>" + timestamp(song.length) + "</td></tr>");

            asd.click(function (e) {
                sendEvent('play_song', {song_no: index});
                e.preventDefault();
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

    div.children('.active').removeClass('active');

    div.children('[data-track-id="' + playerStatus.track_id + '"]').addClass("active");
}

function radioChangeEvent() {
    var radio = storage.getRadio(playerStatus.isRadio);
    if (radio == false)
        return;
    cover.attr('src', radio.cover);
    $('#artist').html(radio.name);
    $('#title').html("Radio station");
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

    if (typeof storage.radios !== 'undefined' && storage.radios.length != 0)
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

function loadAlbumPlaylist(id, callback) {
    var address = '/assets/API/playlist.php?id=' + id;

    $.getJSON(address)
        .done(function (data) {
            if (data == null) {
                error("Unable to find album with id: " + id + ". It might have been deleted");
                return;
            }

            console.log("Loaded", data.length, "songs.");

            storage.getAlbum(id).songs = data;

            if (typeof callback !== "undefined")
                callback();
        })
        .fail(function () {
            error("An error occurred while loading the playlist.");
        });
}

function getAlbumDetails(id, callback) {
    if (id == null || typeof id == "undefined")
        return;

    if (storage.getAlbum(id) == null) {
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

    if (oldPlayingStatus.volume != r.volume) {
        volumeChangeEvent();
    }

    function volumeChangeEvent() {
        $('#debug-volume').val(r.volume * 100);
        $('#volume-slider').slider('value', r.volume * 100);

        var icon = $('#volume-icon');

        icon.removeClass();
        if (r.volume == 0) {
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
    var height = remoteControls.outerHeight();
    margin = parseInt(remoteControls.css('padding-left'));

    $('#remote-controls-placeholder').outerHeight(height);

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
    var height = remoteControls.outerHeight();
    margin = parseInt(remoteControls.css('padding-left'));

    $('#remote-controls-placeholder').outerHeight(height);

    cover.height(cover.width());
});