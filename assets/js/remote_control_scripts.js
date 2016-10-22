/**
 * Created by Vittorio on 22-Oct-16.
 */
var playerStatus = {};
var albumDetailsCache = [];

var localCurrenTime = 0;

var clockTimerHandle;

function startTimer() {
    clearInterval(clockTimerHandle);
    clockTimerHandle = setInterval(function () {
        localCurrenTime += 0.1;

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
    getAlbumDetails(playerStatus.album_id, function (data) {
        $('#cover').attr('src', data.cover);
        $('#artist').html(data.artist);
        $('#title').html(data.title);
    });
}

function trackChangedEvent() {
    updateTrackProgress();
    localCurrenTime = 0;
    startTimer();

}

function getThings() {
    getRemotePlayerStatus(function (r) {

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

            localCurrenTime = r.currentTime;
            updateTrackProgress();
            //albumChangedEvent();
        }

        $('#log').text(JSON.stringify(r, null, '\n'));
    });

}

getThings();

setInterval(function () {
    getThings();
}, 500);


$(document).ready(function () {
    var height = $('#remote-controls').outerHeight();

    $('#remote-controls-placeholder').outerHeight(height);

    getTimeDelta(function (delta) {
        console.log(delta);
    })
});

function updateTrackProgress() {
    $('#trackProgress').width((localCurrenTime / playerStatus.duration * 100) + "%");
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

function getTimeDelta(callback) {
    var oReq = new XMLHttpRequest();
    oReq.open("POST", url);
    oReq.setRequestHeader('accept', 'text/time');
    oReq.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var now = new Date().getTime();

            var request_time = now - d;

            var delta = now - parseInt(this.responseText) - request_time / 2;

            callback(delta);
        }
    };

    var d = new Date().getTime();

    oReq.send();
}

