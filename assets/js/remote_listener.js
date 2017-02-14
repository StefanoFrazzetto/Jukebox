var url = 'http://localhost:4201/';

var evtSource = new EventSource(url, {withCredentials: true});

var errorCount = 0;

evtSource.onerror = function (e) {
    if (errorCount == 0)
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

handleSSE('play_album', function (data) {
    player.playAlbum(parseInt(data.album_id));
});

handleSSE('play_song', function (data) {
    player.playSongAtIndex(parseInt(data.song_no));
});

handleSSE('play_radio', function () {
    alert("Hey, this needs to be fixed!");
    // TODO FIX RADIO
    //player.playRadio(JSON.stringify(data.radio_url), data.radio_name);
    //player.playRadio(storage.getRadio(data.radio_id));
});

handleSSE('set_volume', function (value) {
    var volume = parseFloat(value.value);

    if (volume)
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

setInterval(function () {
    sendPlayerStatus();
}, 5000);

sendPlayerStatus();