/**
 * Created by Vittorio on 22-Oct-16.
 */
var playerStatus = {};
var albumDetailsCache = [];
var deltaTime;
var latency;
var clockTimerHandle;

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
    getAlbumDetails(playerStatus.album_id, function (data) {
        $('#cover').attr('src', data.cover);
        $('#artist').html(data.artist);
        $('#title').html(data.title);
    });
}

function trackChangedEvent() {
    updateTrackProgress();

    if (playerStatus.playing)
        startTimer();
    else
        stopTimer();
}

function getLocalCurrentTime() {
    var value = playerStatus.playing ?
    playerStatus.currentTime + (((new Date().getTime()) - (playerStatus.timestamp + deltaTime)) / 1000)
        : playerStatus.currentTime;
    $('#debug-time').text(timestamp(value));
    return value;
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
            updateTrackProgress();
        }

        $('#log').text(JSON.stringify(r, null, '\n'));
    });

}

$(document).ready(function () {
    var height = $('#remote-controls').outerHeight();

    $('#remote-controls-placeholder').outerHeight(height);

    getDeltaTime(function (delta) {
        console.log(delta);

        deltaTime = delta;
        //       getThings();

        setInterval(function () {
            getThings();
        }, 500);

    })
});

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
    oReq.open("POST", url);
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

function setDeltaTime() {
    getDeltaTime(function (delta) {
        deltaTime = delta;
    })
}