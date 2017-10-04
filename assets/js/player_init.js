var player = new Player();

player.EQ.connect();

if (!isJukebox) {
    player.visualiser = new Visualiser(player.context, player.outputNode, document.getElementById('eq_canvas'));
}

function initPlayer() {
    //region Selectors
    var playlistTable = $('#playlistTable');
    var seekDiv = $('#seek');
    var seekDivElement = seekDiv.get(0);
    var lastTimeUpdate = 0;
    var songTitle = $('#songTitle');
    var albumTitle = $('#albumTitle');
    var albumCover = $('#albumCover');

    // Buttons
    var play_btn = $('#play');
    var stop_btn = $('#stop');
    var next_btn = $('#fwd');
    var prev_btn = $('#bwd');
    //endregion Selectors

    //region Clicks
    play_btn.click(function () {
        player.playPause();
    });

    stop_btn.click(function () {
        player.stop();
    });

    next_btn.click(function () {
        player.next();
    });

    prev_btn.click(function () {
        player.previous();
    });
    // endregion Clicks

    //region Event Bindings
    player.onWaiting = function () {
        seekDiv.html('loading&hellip;');
    };

    player.onTimeUpdate = function () {
        updateProgressBar();
    };

    player.onTrackChange = function () {
        highlightCurrentTrack();
        if (player.getCurrentSong()) {
            songTitle.html(player.getCurrentSong().getArtistsNames());
        }

    };

    player.onPlaylistChange = function () {
        updatePlaylist();
    };

    player.onAlbumChange = function () {
        if (typeof player.getCurrentSong() !== "undefined" && player.getCurrentSong() !== null)
            showAlbumsDetails(player.getCurrentSong().album_id);
    };

    player.onRadioChange = function () {
        if (typeof player.getCurrentSong() === "undefined" || player.getCurrentSong() === null)
            return;

        songTitle.html(player.currentRadio.name);
        changeCover(player.currentRadio.cover);
    };

    player.onError = function () {
        playerError();
    };

    player.onPlayPause = function () {
        play_btn.find('.material-icons').html(player.isPlaying() ? 'pause' : 'play_arrow');
    };

    player.onVolumeChange = function () {
        sliderv.slider('value', player.getVolume() * 100);
    };

    sliderv.slider('value', player.getVolume() * 100);

    if (typeof localStorage !== "undefined") {
        player.EQ.onUpdate = function (bands) {
            localStorage.setItem("eqGains", JSON.stringify(bands));
        };

        var gains = JSON.parse(localStorage.getItem("eqGains"));

        if (gains) player.EQ.changeGains(gains);
    }
    //endregion Event Bindings

    //region Random Functions
    function changeCover(src) {
        albumCover.fadeOut(animation_medium, function () {
            albumCover.attr('src', src).fadeIn(animation_medium);
        });
    }

    function hideCover() {
        albumCover.fadeOut(animation_medium);
    }

    function updateProgressBar() {
        var time = Math.floor(player.getCurrentTime());

        // Updates the UI only if the track time has changed of one second
        if (time === lastTimeUpdate)
            return;
        else
            lastTimeUpdate = time;

        // Converts seconds to a mm:ss string format
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

        // Updates the seek bar
        var percentage = (100 / player.getCurrentSongDuration()) * time;
        slider.slider('value', percentage);

        // Updates the UI time
        seekDivElement.innerText = timestamp(time);
    }

    function highlightCurrentTrack() {
        if (typeof player.getCurrentSong() === "undefined")
            return;

        var track_to_highlight = '[data-track-id="' + player.getCurrentSong().id + '"]';

        $('.playing').removeClass('playing');
        $(track_to_highlight).addClass('playing');

        $("#playlistOverlay").mCustomScrollbar("scrollTo", "#playlistOverlay " + track_to_highlight);
    }

    function updatePlaylist() {
        var items = '';

        $.each(player.tracks, function (key, val) {
            if (typeof val !== 'undefined')
                items = items + (
                    "<tr>" +
                    "<td data-album='" + val.album_id + "' data-track-no='" + val.track_no + "' " +
                    "id='track_" + key + "' data-track-id='" + val.id + "'>" + val.title + "</td>" +
                    "</tr>"
                );
        });

        playlistTable.html(items);

        $('#playlistTable').find('td').click(function () {
            var detected_track = $(this).attr('id').substring(6);
            player.playSongAtIndex(detected_track);
        });

        try {
            if (typeof updateBigPlaylist !== 'undefined')
                updateBigPlaylist();
        } catch (ex) {
            console.warn('Watch out, error here!');
        }

        highlightCurrentTrack();
    }

    function showAlbumsDetails(id) {
        if (typeof id === 'undefined')
            return;

        if (storage.getAlbum(id) === null) {
            error("Album [" + id + "] missing from local DB. Report to @Vittorio.");
            return;
        }

        var data = storage.getAlbum(id);

        albumTitle.html(data.title);

        if (data.cover !== null)
            changeCover(data.getFullCoverUrl());
        else
            changeCover(storage.cover_placeholder);
    }

    function playerError() {
        songTitle.text('Error!');
        albumTitle.text('');
        hideCover();
    }

    //endregion Random Functions
}