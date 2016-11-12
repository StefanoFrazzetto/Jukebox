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
    pplay();
});

handleSSE('pause', function () {
    ppause();
});

handleSSE('play/pause', function () {
    play_pause();
});

handleSSE('next', function () {
    pnext();
});

handleSSE('previous', function () {
    pprevious();
});

handleSSE('stop', function () {
    pstop();
});

handleSSE('refresh', function () {
    document.location.reload(true);
});

handleSSE('play_album', function (data) {
    changeAlbum(parseInt(data.album_id));
    pplay();
});

handleSSE('play_song', function (data) {
    getPlaylistSong(parseInt(data.song_no));
    pplay();
});

handleSSE('play_radio', function (data) {
    playRadio(JSON.stringify(data.radio_url), data.radio_name);
});

handleSSE('set_volume', function (value) {
    var volume = parseInt(value.value);

    if (volume != null)
        setVolume(volume);
});

function sendPlayerStatus() {
    var oReq = new XMLHttpRequest();
    oReq.open("POST", url);
    oReq.send(JSON.stringify(getPlayerStatus()));
}

player.addEventListener('play', updateStatusHandler, false);
player.addEventListener('pause', updateStatusHandler, false);
player.addEventListener('canplay', updateStatusHandler, false);

function updateStatusHandler() {
    sendPlayerStatus();
}

setInterval(function () {
    sendPlayerStatus();
}, 30000);

sendPlayerStatus();