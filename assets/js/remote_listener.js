var url = 'http://localhost:4201/';

var evtSource = new EventSource(url, {withCredentials: true});

var errorCount = 0;

evtSource.onerror = function (e) {
    if (errorCount === 0)
        alert("EventSource failed." + JSON.stringify(e));
    errorCount++
};

function handleSSE(name, handler) {
    evtSource.addEventListener(name, function (e) {
        var obj = JSON.parse(e.data);

        handler(obj, e);
    }, false);
}

handleSSE('play', function () {
    player.play();
});

handleSSE('pause', function () {
    player.play();
});

handleSSE('play/pause', function () {
    player.playPause();
});

handleSSE('next', function () {
    player.next();
});

handleSSE('previous', function () {
    player.previous();
});

handleSSE('stop', function () {
    player.stop();
});

handleSSE('refresh', function () {
    document.location.reload(true);
});

handleSSE('reload', function () {
    reload();
});

handleSSE('play_album', function (data) {
    player.playAlbum(parseInt(data.album_id));
});

handleSSE('play_song', function (data) {
    player.playSongAtIndex(parseInt(data.song_no));
});

handleSSE('play_radio', function (data) {
    try {
        player.playRadio(data.radio_id);
    } catch (e) {
        try {
            player.playRadio(Radio.read(data.radio))
        } catch (e) {
            error("Failed to play radio!");
            console.error(data);
        }
    }
});

handleSSE('set_volume', function (value) {
    var volume = parseFloat(value.value);

    if (volume && volume !== player.getVolume())
        player.setVolume(volume);
});

function sendPlayerStatus() {
    var oReq = new XMLHttpRequest();
    oReq.open("POST", url);
    oReq.send(JSON.stringify(player.export()));
}

player.onChange = function () {
    updateStatusHandler()
};

function updateStatusHandler() {
    sendPlayerStatus();
}