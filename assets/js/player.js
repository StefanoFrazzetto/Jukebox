var initialized = false;
// Variables //
var playlist = [],
    playlist_number = 0,
    track_no = 0,
    album_id, repeat = false,
    shuffle = false,
    default_volume = 10,
    isRadio = false;

// SELECTORS //
var player = document.getElementById("player");
var playlistTable = $('#playlistTable');
// -> Buttons
var play_btn = $('#play');
var stop_btn = $('#stop');
var next_btn = $('#fwd');
var prev_btn = $('#bwd');

var seekDiv = $('#seek');
var songTitle = $('#songTitle');
var albumTitle = $('#albumTitle');
var albumCover = $('#albumCover');

// INIT //
var playing = false;
var isReady = false;
setVolume(default_volume);
// Events //

play_btn.click(function () {
    play_pause();
});

stop_btn.click(function () {
    pstop();
});

next_btn.click(function () {
    pnext();
});

prev_btn.click(function () {
    pprevious();
});

player.onwaiting = function () {
    seekDiv.html('loading&hellip;');
};

player.onended = function () {
    var pstatus = pnext();
    if (!pstatus) { // Playlist is over
        getPlaylistSong(0);
        if (!repeat) {
            pstop();
        }
    }
};

player.onerror = function () {
    console.error('Something bad just happened');
};

player.onstalled = function () {
    console.error('Player seems to be stalled');
};

player.addEventListener('timeupdate', updateProgressBar, false);


/*$(window).keypress(function (e) {
 if (e.keyCode == 0 || e.keyCode == 32) {
 play_pause();
 e.preventDefault();
 }
 });*/

// Functions //

albumCover.on("error", function () {
    hideCover();
});

function changeCover(src) {
    albumCover.fadeOut(animation_medium, function () {
        albumCover.attr('src', src).fadeIn(animation_medium);
    });
}

function hideCover() {
    albumCover.fadeOut(animation_medium);
}

function play_pause() {
    if (playing) {
        ppause();
    } else {
        pplay();
    }
}

function pplay() {
    if (!isReady) {
        return;
    }

    ptrackInit();
    player.play();
    playing = true;
    play_btn.css({
        'background-image': 'url(assets/img/buttons/pause.png)'
    });
}

function ppause() {
    player.pause();
    playing = false;
    play_btn.css({
        'background-image': 'url(assets/img/buttons/playButton.png)'
    });
}

function pstop() {
    ppause();
    if (initialized) {
        player.currentTime = 0;
    }
}

function setVolume(value) {
    player.volume = value / 100;
    if (value != sliderv.slider("value"))
        sliderv.slider("value", value);
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

function ptrackInit() {
    if (!initialized) {
        initialized = true;
        pplay();
        //getPlaylistSong(0);
    }
}

function pseek(value) {
    player.currentTime = value / 100 * player.duration;
}

function updateProgressBar() {
    var percentage = (100 / player.duration) * player.currentTime;
    slider.slider('value', percentage);


    seekDiv.text(timestamp(player.currentTime));

}

function pnext() {
    var length = Object.keys(playlist).length;
    if (playlist_number < length - 1) {
        getPlaylistSong(playlist_number + 1);
        return true;
    } else {
        return false;
    }
}

function pprevious() {
    if (playlist_number != 0) {
        getPlaylistSong(playlist_number - 1);
        return true;
    } else {
        return false;
    }
}

function highlightCurrentTrack() {
    $('.playing').removeClass('playing');
    var selector = '[data-album="' + album_id + '"][data-track-no="' + track_no + '"]';

    $(selector).addClass('playing');

    $("#playlistOverlay").mCustomScrollbar("scrollTo", "#playlistOverlay " + selector);
}

function getPlaylistSong(number) {
    if (typeof album_id === 'undefined')
        return error("[album_id] is undefined inside getPlaylistSong()");

    number = parseInt(number);
    playlist_number = number;

    if (typeof playlist[number] == 'undefined') { // Small failsafe
        error("Track [" + number + "] missing from the local database.");
        return;
    }

    track_no = playlist[number].track_no;

    if (playlist[number].album_id != album_id) {

        var _album_id = playlist[number].album_id;

        if (typeof _album_id != 'undefined') // SE QUALCUNO LO LEVA GLI STACCO LO SCACCO
            album_id = _album_id;

        showAlbumsDetails(album_id);
    }

    pgetSong(playlist[number].url);

    songTitle.html(playlist[number].title);
    //if (initialized) {
    pplay();
    //}

    highlightCurrentTrack();
}

function pgetSong(url) {
    pstop();
    player.src = 'jukebox/' + album_id + "/" + url;
}

function getAlbumPlaylist(album_id, song) {

    getPlaylist(album_id, function (data) {
        playlist = data;

        playlist_number = 0;

        if (typeof song === 'undefined') {
            song = 0;
        }

        getPlaylistSong(song);
        initialized = true;

        updatePlaylist();
    });
}

function getPlaylist(_album_id, callback) {
    var url_request = "/assets/API/playlist.php?id=" + _album_id;

    $.getJSON(url_request, function (data) {
        callback(data);
    }).fail(function (uno, due, tre) {
        console.error("error: " + uno + " " + due + " " + tre);
    });
}

function updatePlaylist() {
    var items = '';

    $.each(playlist, function (key, val) {
        if (typeof val != 'undefined')
            items = items + ("<tr><td data-album='" + val.album_id + "' data-track-no='" + val.track_no + "' id='track_" + key + "'>" + val.title + "</td></tr>");
    });
    playlistTable.html(items);

    $('#playlistTable').find('td').click(function () {
        var detected_track = $(this).attr('id').substring(6);
        getPlaylistSong(detected_track);

    });

    if (playlist.length > 0)
        if (!isReady) {
            getPlaylistSong(0);
            isReady = true;
        }

    try {
        updateBigPlaylist();
    } catch (ex) {

    }

    highlightCurrentTrack();
}

function showAlbumsDetails(id) {
    if (typeof id === 'undefined')
        return;

    if (typeof albums_storage[id] === 'undefined') {
        error("Album [" + id + "] missing from local DB. Report to @Vittorio.");
        return;
    }

    var data = albums_storage[id];

    albumTitle.html(data.title);

    if (data.cover != null)
        changeCover("/jukebox/" + id + "/cover.jpg?" + data.cover);
    else
        changeCover(cover_placeholder);
}

function getAlbumDetails(id, callback) {
    if (typeof albums_storage[id] == 'undefined') {
        // var url_request = "assets/php/get_album_details.php?id=" + id;
        // $.getJSON(url_request, callback).done(function (data) {
        //     albumDetailsCache[id] = data;
        // });
        error("Unable to find this album (" + id + "). Report this to @Vittorio.");
    } else {
        var data = albums_storage[id];

        callback(data);
    }
}

function changeAlbum(id, song) {
    if (typeof id == 'undefined')
        return;

    if (typeof song == 'undefined')
        song = 0;

    album_id = id;
    isRadio = false;
    isReady = true;

    showAlbumsDetails(id);

    getAlbumPlaylist(id, song);

    // pstop();
}

function deleteAlbum(id) {
    new Alert({
        message: "Are you sure you want to delete this album?",
        title: "Confirm",
        buttons: [
            {
                text: "Yes, delete it",
                callback: function () {
                    $.ajax('assets/API/delete_album.php?id=' + id).done(function () {
                        if (id == album_id) {
                            resetPlayer();
                        }

                        modal.close();
                        reload();
                    });
                }
            },
            "No, keep it"
        ]
    }).show();
}

function deleteRadio(id) {
    if (confirm("Are you sure?")) {
        $.getJSON('assets/API/delete_radio.php?id=' + id).done(function (response) {
            if (response.status == "success") {
                $('.aRadio[data-id="' + id + '"]').remove();
            }
            else {
                error("Error while deleting Radio. " + response.message + ".");
                console.log(response.message);
            }
            //reload();
        });
    }
}

function addSongsToPlaylist(songs) {
    playlist = playlist.concat(songs);
    updatePlaylist();
}

function addSongToPlaylist(song) {
    playlist.push(song);
    updatePlaylist();
}

function addAlbumToPlaylist(_album_id) {
    getPlaylist(_album_id, function (data) {
        addSongsToPlaylist(data);
    });
}

function addAlbumSongToPlaylist(_album_id, _track_no) {
    getPlaylist(_album_id, function (data) {
        addSongToPlaylist(data[_track_no]);
    });
}

function addAlbumSongsToPlaylist(_album_id, _tracks) {
    getPlaylist(_album_id, function (data) {
        _tracks.forEach(function (track) {
            addSongToPlaylist(data[track]);
        });
    });
}

function addAlbumCDToPlaylist(_album_id, _CD) {
    addAlbumSongsToPlaylist(_album_id, CDMap[_CD]);
}

function removeSongFromPlaylist(_playlist_no) {
    playlist.splice(_playlist_no, 1);
    updatePlaylist();
}

function removeAllTracksFromPlaylist() {
    resetPlayer();
    //updatePlaylist();
}


function playerError() {
    songTitle.text('Error!');
    albumTitle.text('');
    hideCover();
}

function playRadio(url_object, name) {
    isReady = true;
    album_id = null;
    track_no = null;

    url_object = $.parseJSON(url_object);

    try {
        isRadio = parseInt(url_object.id);
    } catch (e) {
        isRadio = true;
    }

    var port = url_object.port;

    var request = url_object.path;

    var address = url_object.host;

    player.src = 'http://' + window.location.hostname + ':4242/?address=' + address + '&request=' + request + '&port=' + port;
    songTitle.html(name);

    if (typeof url_object.cover !== "undefined")
        changeCover(url_object.cover);

    pplay();
}

function resetPlayer() {
    pstop();

    if (typeof ctx !== 'undefined') {
        ctx.clearRect(0, 0, 354, 95);
    }

    hideCover();

    albumTitle.html('');

    songTitle.html('');

    seekDiv.html('');

    playlist = [];

    updatePlaylist();


    player.src = '';

    player.load();

    isReady = false;
}

function getPlayerStatus() {
    if (isRadio)
        return {
            isRadio: isRadio,
            playing: playing,
            volume: player.volume,
            currentTime: player.currentTime,
            timestamp: new Date().getTime()
        };
    else
        return {
            album_id: parseInt(album_id),
            track_no: parseInt(track_no),
            playing: playing,
            repeat: repeat,
            shuffle: shuffle,
            currentTime: player.currentTime,
            duration: player.duration,
            volume: player.volume,
            isRadio: isRadio,
            timestamp: new Date().getTime()
        }
}