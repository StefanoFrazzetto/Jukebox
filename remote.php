<!DOCTYPE html>
<html>
<head>
    <title>Jukebox Remote</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <link href="assets/css/main_remote.css?v5" rel="stylesheet" type="text/css"/>
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css"/>
    <link rel="icon" type="image/png" href="assets/img/icons/vinyl1.png">
    <meta name="theme-color" content="#2a2a2a">
</head>
<body>

<div id="container">
    <div id="cover-container">
        <img src="/assets/img/album-placeholder.png" id="cover" class="cover-picture"/>
    </div>

    <div id="remote-controls-placeholder"></div>

    <div id="remote-controls">

        <div id="log"></div>

        <button onclick="sendEvent('refresh')">REFRESH</button>
        <input type="text" name="" id="album_id" placeholder="album_id" value="642">
        <button onclick="
		sendEvent('play_album', {
			album_id: parseInt(document.getElementById('album_id').value)
		})

		">PLAY album
        </button>


        <div id="cover-label">
            <div id="artist">-</div>
            <div id="title">-</div>
        </div>

        <div class="progressBar thin">
            <div class="progress" id="trackProgress" style="width: 50%;"></div>
        </div>

        <div class="holo-btn" onclick="sendEvent('previous')"><i class="fa fa-step-backward"></i></div>

        <div class="holo-btn big" onclick="sendEvent('play/pause')" id="play-pause"><i class="fa fa-pause"></i></div>

        <div class="holo-btn" onclick="sendEvent('next')"><i class="fa fa-step-forward"></i></div>


    </div>


    <!--

    <button onclick="sendEvent('previous')">previous</button>

    <button onclick="sendEvent('stop')">stop</button>

    <button onclick="sendEvent('play')">PLAY</button>

    <button onclick="sendEvent('pause')">PAUSE</button>

    <button>PLAY/PAUSE</button>

    <button >next</button>

    <br/><br/>




    <br/><br/>





-->


</div>


<script type="text/javascript" src="assets/js/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/remote_client.js"></script>
<script type="text/javascript">
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


            setAlbumDetails();

            //console.log(r);

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


</script>
</body>
</html>