'use strict';

//region PLAYER
function Player() {
    this.audioApiSupported = true;

    //region Web Audio API
    if (!window.AudioContext) {
        if (!window.webkitAudioContext) {
            console.warn("No audio API found");
            this.audioApiSupported = false;
        }
        window.AudioContext = window.webkitAudioContext;
    }

    if (this.audioApiSupported) {
        this.context = new AudioContext();
        this.mediaElement = document.getElementById('player');
        this.inputNode = this.context.createMediaElementSource(this.mediaElement);
        this.outputNode = this.context.createGain();

        this.EQ = new EQ(this.context, this.inputNode, this.outputNode);

        this.inputNode.connect(this.outputNode);

        this.outputNode.connect(this.context.destination);

        this.visualiser = null;
    }
    //endregion Web Audio API

    //region Playlist
    this.tracks = [];
    this.currentTrackNumber = null;
    this.currentRadio = null;
    //endregion

    //region Play Modes
    this.shuffle = false;
    this.repeat = false;
    this.isRadio = false;
    //endregion

    //region Events
    this.onPlayPause = null;
    this.onPlay = null;
    this.onPause = null;
    this.onTrackChange = null;
    this.onAlbumChange = null;
    this.onPlaylistChange = null;
    this.onRadioChange = null;
    this.onEnded = null;
    this.onWaiting = null;
    this.onError = null;
    this.onStalled = null;
    this.onTimeUpdate = null;
    this.onChange = null;

    var self = this;

    this.mediaElement.onended = function () {
        self.next();
        self.callback(self.onEnded);
    };

    this.mediaElement.onerror = function () {
        self.callback(self.onError);
    };

    this.mediaElement.onstalled = function () {
        self.callback(self.onStalled);
    };

    this.mediaElement.onplay = function () {
        self.callback(self.onPlay);
        self.callback(self.onPlayPause);
    };

    this.mediaElement.onpause = function () {
        self.callback(self.onPause);
        self.callback(self.onPlayPause);
    };

    this.mediaElement.ontimeupdate = function () {
        self.callback(self.onTimeUpdate, false);
    };
    //endregion Events
}

//region Playback
Player.prototype.play = function () {
    if (this.tracks !== null && this.tracks.length !== 0 && this.currentTrackNumber === null)
        this.playSongAtIndex(0);
    else
        this.mediaElement.play();
};

Player.prototype.pause = function () {
    this.mediaElement.pause();
};

Player.prototype.stop = function () {
    this.mediaElement.pause();
    this.mediaElement.currentTime = 0;
};

Player.prototype.playPause = function () {
    if (this.mediaElement.paused)
        this.play();
    else
        this.pause();
};

Player.prototype.next = function () {
    if (this.repeat === 2) { // Play same song again
        this.seek(0);
        this.play();
        return;
    }

    var index = this.currentTrackNumber + 1;
    if (index > this.tracks.length - 1) {
        if (this.repeat) { // Start album over
            this.playSongAtIndex(0);
        } else { // Start over and stop
            // this.playSongAtIndex(0);
            // this.stop();
        }
    } else {
        this.playSongAtIndex(index);
    }
};

Player.prototype.previous = function () {
    if (this.currentTrackNumber === 0)
        return;

    this.playSongAtIndex(this.currentTrackNumber - 1);
};

Player.prototype.seek = function (time) {
    this.mediaElement.currentTime = time;
};

Player.prototype.setVolume = function (value) {
    value = parseFloat(value);

    if (value < 0)
        value = 0;

    if (value > 1)
        value = 1;

    return this.mediaElement.volume = value;
};

Player.prototype.getVolume = function () {
    return this.mediaElement.volume;
};

Player.prototype.reset = function () {
    this.stop();
    this.currentTrackNumber = null;
    this.tracks = [];
    this.isRadio = false;

    this.callback(this.onPlaylistChange);
    this.callback(this.onTrackChange);
    this.callback(this.onAlbumChange);
    this.callback(this.onTimeUpdate);
};
//endregion Playback

//region Tracks Handling
Player.prototype.getAlbumPlaylist = function (albumId, callback) {
    this.getJSON('/assets/API/playlist.php?id=' + albumId,
        function (songs) {
            callback(Song.readMany(songs));
        },
        function (data) {
            console.error(data);
        });
};

Player.prototype.playAlbum = function (albumId, songNumber) {
    var _player = this;

    if (typeof songNumber === "undefined")
        songNumber = 0;

    if (_player.getCurrentAlbumId() === albumId) {
        _player.playSongAtIndex(songNumber);
        return;
    }

    this.getAlbumPlaylist(albumId, function (songs) {
        _player.tracks = [];

        _player.addSongsToPlaylist(songs);

        _player.playSongAtIndex(songNumber);

        _player.callback(_player.onAlbumChange);
        _player.callback(_player.onTrackChange);
    });
};

Player.prototype.playUrl = function (url) {
    this.mediaElement.src = url;
    this.play();
};

Player.prototype.playSong = function (song) {
    if (!song instanceof Song) {
        console.warn("Not a song passed to Player.playSong()");
        return;
    }

    this.isRadio = false;
    this.currentRadio = null;
    this.playUrl(song.getUrl());
};

Player.prototype.playSongAtIndex = function (index) {
    index = parseInt(index);

    if (index < 0) {
        console.warn("Index less than zero passed to Player.playSongAtIndex()");
        return;
    }

    if (index > this.tracks.length - 1) {
        console.warn("Index was out of playlist bound at Player.playSongAtIndex()");
        return;
    }

    var oldSong = this.getCurrentSong();

    this.currentTrackNumber = index;

    var newSong = this.getCurrentSong();

    this.playSong(newSong);

    if (typeof oldSong === "undefined" || oldSong.id !== newSong.id) {
        this.callback(this.onTrackChange);
    }

    if (typeof oldSong !== "undefined" && oldSong.album_id != newSong.album_id) {
        this.callback(this.onAlbumChange)
    }
};

Player.prototype.playRadio = function (radio) {
    this.reset();
    this.isRadio = true;

    var url = 'http://' + window.location.hostname + ':4242/?address=' + radio.url.host + '&request=' + radio.url.path + '&port=' + radio.url.port;

    this.currentRadio = radio;
    this.callback(this.onRadioChange);
    this.playUrl(url);
};
//endregion Tracks Handling

//region Playlist Handling
Player.prototype.addSongToPlaylist = function (song) {
    if (!song instanceof Song)
        return;

    this.tracks.push(song);

    this.callback(this.onPlaylistChange);
};

Player.prototype.addSongsToPlaylist = function (songs) {
    if (typeof songs !== "object") {
        console.warn("Non-object passed to Player.addSongsToPlaylist()");
        return;
    }

    this.tracks = this.tracks.concat(songs);

    this.callback(this.onPlaylistChange);
};

//noinspection JSUnusedGlobalSymbols
Player.prototype.removeSongFromPlaylistAtIndex = function (trackNumber) {
    this.tracks.splice(trackNumber, 1);
    this.callback(this.onPlaylistChange);
};

Player.prototype.removeAllTracksFromPlaylist = function () {
    this.reset();
};

Player.prototype.addAlbumToPlaylist = function (albumId) {
    var self = this;

    this.getAlbumPlaylist(albumId, function (data) {
        self.addSongsToPlaylist(data);
    });
};

Player.prototype.addAlbumSongToPlaylist = function (albumId, trackNumber) {
    var self = this;

    this.getAlbumPlaylist(albumId, function (songs) {
        if (typeof songs[trackNumber] != "undefined")
            self.addSongToPlaylist(songs[trackNumber]);
        else
            console.warn("Track " + trackNumber + " not found in album " + albumId);
    });
};

//noinspection JSUnusedGlobalSymbols
Player.prototype.addAlbumSongsToPlaylist = function (albumId, trackNumbers) {
    var self = this;

    if (typeof albumId != "number")
        return;

    if (typeof trackNumbers != "object")
        return;

    this.getAlbumPlaylist(albumId, function (songs) {
        trackNumbers.forEach(function (trackNumber) {
            if (songs[trackNumber])
                self.addSongToPlaylist(songs[trackNumber]);
        })
    });
};

Player.prototype.addAlbumCdToPlaylist = function (albumId, cdNumber) {
    var self = this;
    this.getAlbumPlaylist(albumId, function (songs) {
        // Gets the songs that has the correct cd property
        songs = songs.filter(function (song) {
            return song.cd === cdNumber;
        });

        self.addSongsToPlaylist(songs);
    });
};
//endregion Playlist Handling

//region Getters
Player.prototype.isPlaying = function () {
    return !this.mediaElement.paused;
};

Player.prototype.getCurrentTime = function () {
    return this.mediaElement.currentTime;
};

Player.prototype.getCurrentSong = function () {
    return this.tracks[this.currentTrackNumber];
};

Player.prototype.getCurrentSongDuration = function () {
    return this.mediaElement.duration;
};

Player.prototype.getCurrentAlbumId = function () {
    var song = this.getCurrentSong();

    if (song)
        return song.album_id;
    else
        return null;
};
//endregion Getters

//region Utils
Player.getAlbumPath = function (albumId) {
    return '/jukebox/' + albumId + '/';
};

Player.prototype.getJSON = function (url, successHandler, errorHandler) {
    var xhr = typeof XMLHttpRequest != 'undefined'
        ? new XMLHttpRequest()
        : new ActiveXObject('Microsoft.XMLHTTP');
    xhr.open('get', url, true);
    xhr.onreadystatechange = function () {
        var status;
        var data;
        // https://xhr.spec.whatwg.org/#dom-xmlhttprequest-readystate
        if (xhr.readyState === 4) { // `DONE`
            status = xhr.status;
            if (status === 200) {
                data = JSON.parse(xhr.responseText);
                successHandler && successHandler(data);
            } else {
                errorHandler && errorHandler(status);
            }
        }
    };
    xhr.send();
};

Player.prototype.callback = function (callback, doesTriggerOnChange) {
    if (typeof callback === "function")
        callback();
    if (typeof doesTriggerOnChange === "undefined")
        doesTriggerOnChange = false;

    if (doesTriggerOnChange)
        this.callback(this.onChange, false);
};

Player.prototype.export = function () {

    if (this.isRadio)
        return {
            isRadio: this.isRadio,
            playing: this.isPlaying(),
            volume: this.getVolume(),
            currentTime: this.getCurrentTime(),
            timestamp: new Date().getTime()
        };
    else
        return {
            album_id: parseInt(this.getCurrentSong().album_id),
            track_no: parseInt(this.track_no),
            track_id: parseInt(this.getCurrentSong().id),
            playing: this.isPlaying(),
            repeat: this.repeat,
            shuffle: this.shuffle,
            currentTime: this.getCurrentTime(),
            duration: this.getCurrentSongDuration(),
            volume: this.getVolume(),
            isRadio: this.isRadio,
            timestamp: new Date().getTime()
        }

};
//endregion Utils

//endregion Player

//region EQUALISER
function EQ(context, input, output) {
    this.context = context;

    this.input = input;
    this.output = output;

    this.gainDb = 0;
    this.bandsList = this.getBands(2);
    this.filteredBands = this.getFilteredBands(this.bandsList);
    this.connected = false;
    // CONTAINER NODE
    this.container = null;
}

EQ.prototype.getBands = function (type) {
    if (type === undefined)
        type = 0;

    this.currentBandId = type;

    if (type === 0 || typeof this.getBandsList(type).bands === "undefined") {
        var baseBand = 32;
        var bandsCount = 10;

        var bands = [];

        for (var i = 0; i < bandsCount; i++)
            bands.push(baseBand * (Math.pow(2, i)));

        return bands;
    }

    return this.getBandsList(type).bands;

};

EQ.prototype.getBandsList = function (index) {
    var bands = [
        {
            name: "10 Bands #1"
        },
        {
            name: "10 Bands #2",
            bands: [20, 50, 100, 200, 500, 1000, 2000, 5000, 10000, 16000]
        },
        {
            name: "9 Bands",
            bands: [63, 125, 250, 500, 1000, 2000, 4000, 8000, 16000]
        },
        {
            name: "31 Bands",
            bands: [20, 25, 31.5, 40, 50, 63, 80, 100, 125, 160, 200, 250, 315, 400, 500, 630, 800, 1000, 1250, 1600, 2000, 2500, 3150, 4000, 5000, 6300, 8000, 10000, 12500, 16000, 20000]
        }
    ];

    if (typeof index !== "undefined")
        return bands[index];
    else
        return bands;
};

EQ.prototype.getFilteredBands = function () {
    var filteredBands = [];

    for (var i = 0; i < this.bandsList.length; i++) {
        var band = this.context.createBiquadFilter();
        if (i === 0) {
            band.type = "lowshelf";
        } else if (i === this.bandsList.length - 1) {
            band.type = "highshelf";
        } else {
            band.type = "peaking";
        }

        band.frequency.value = this.bandsList[i];
        band.gain.value = this.gainDb;
        band.Q.value = 1;

        filteredBands.push(band);
    }

    return filteredBands;
};

EQ.prototype.connect = function () {
    this.input.disconnect();

    this.filteredBands.reduce(function (prev, curr) {
        prev.connect(curr);
        return curr;
    }, this.input).connect(this.output);

    this.connected = true;
};

EQ.prototype.disconnect = function () {
    this.input.disconnect();

    this.filteredBands.forEach(function (band) {
        band.disconnect();
    });

    this.input.connect(this.output);

    this.connected = false;
};

EQ.prototype.changeGain = function (value, band) {
    this.filteredBands[band].gain.value = parseFloat(value);
};

EQ.prototype.setBands = function (bands) {
    if (typeof bands != "object")
        return;

    this.disconnect();

    this.bandsList = bands;
    this.filteredBands = this.getFilteredBands();

    this.connect();


    if (this.container !== null) {
        this.container.innerHTML = '';

        this.drawEQ(this.container);
    }
};

EQ.prototype.changeBands = function (index) {
    this.setBands(this.getBands(index));
};

EQ.prototype.drawEQ = function (container) {
    this.container = container;
    var EQ = this;

    function createBar(i) {

        var control = document.createElement("div");
        control.className = 'controls';
        control.style.display = 'inline-block';

        var label = document.createElement("label");
        label.innerText = EQ.bandsList[i] + " Hz";

        var gain = document.createElement("label");
        gain.innerText = "0 dB";

        var input = document.createElement("input");

        input.setAttribute("type", "range");
        input.setAttribute("value", "0");
        input.setAttribute("step", "1");
        input.setAttribute("max", "12");
        input.setAttribute("min", "-12");

        input.setAttribute('orient', 'vertical');
        input.addEventListener('input', function () {
            EQ.changeGain(input.value, i);
            gain.innerText = input.value + " dB";
        });

        control.appendChild(gain);
        control.appendChild(input);
        control.appendChild(label);

        return control;
    }

    function createEQOption(index, option) {
        var _option = document.createElement("option");
        _option.innerText = option.name;
        _option.setAttribute("value", index);

        if (EQ.currentBandId === index) {
            _option.setAttribute("selected", "selected");
        }
        return _option;
    }

    this.bandsList.forEach(function (a, asd) {
        container.appendChild(createBar(asd));
    });

    var eq_switch = document.createElement("input");

    var controls = document.createElement("div");
    controls.setAttribute("id", "eq_controls");

    eq_switch.setAttribute("type", "checkbox");
    eq_switch.setAttribute("id", "enable_eq");
    eq_switch.setAttribute("checked", "checked");
    eq_switch.addEventListener('change', function () {
        if (EQ.connected) {
            EQ.disconnect();
        } else {
            EQ.connect();
        }
    });

    var eq_switch_label = document.createElement("label");
    eq_switch_label.innerText = 'Enable EQ';
    eq_switch_label.setAttribute("for", "enable_eq");

    controls.appendChild(eq_switch);
    controls.appendChild(eq_switch_label);


    var eq_select_label = document.createElement("label");
    eq_select_label.innerText = 'EQ Type';
    eq_switch_label.setAttribute("for", "select_eq");

    var eq_select = document.createElement("select");
    eq_select.setAttribute("id", "select_eq");

    this.getBandsList().forEach(function (band, index) {
        eq_select.appendChild(createEQOption(index, band));
    });

    eq_select.addEventListener('change', function () {
        var id = this.options[this.selectedIndex].value;
        EQ.changeBands(id);
    });

    controls.appendChild(eq_select);
    controls.appendChild(eq_select_label);

    container.appendChild(controls);
};
//endregion Equaliser

//region VISUALISER
function Visualiser(context, input, canvas) {
    // AUDIO APIs
    this.context = context;
    this.input = input;
    this.analyser = this.context.createAnalyser();
    this.javascriptNode = this.context.createScriptProcessor(2048, 1, 1);
    this.fftSize = 2048;

    // Elements
    this.canvas = canvas;
    this.canvasContext = canvas.getContext("2d");

    // Parameters
    this.barsCount = 50;
    this.wavePadding = 2;
    this.barHeightMultiplier = 0.3;     // 0.3
    this.smoothingTimeConstant = 0.7;   // 0.7
    this.maxFrequency = 16000;          // 16.000 Hz
    this.reflectEQ = false;

    // Initialisation
    this.javascriptNode.connect(this.context.destination);

    // Setup a analyzer
    this.analyser.smoothingTimeConstant = this.smoothingTimeConstant;
    this.analyser.fftSize = this.fftSize;

    // Connect Nodes
    this.input.connect(this.analyser);
    this.analyser.connect(this.javascriptNode);

    // Self variable
    var V = this;

    this.javascriptNode.onaudioprocess = function () {
        try {
            if (V.reflectEQ) {
                V.barsCount = player.EQ.bandsList.length;
                V.maxFrequency = player.EQ.bandsList[player.EQ.bandsList.length - 1];
            }

            var waveWidth = Math.floor(V.canvas.width / V.barsCount - V.wavePadding);
            var waveHeight = Math.floor(V.canvas.height);

            // Fail safe in case the bars gets too small
            if (waveWidth < 1) {
                V.wavePadding = 1;
                V.barsCount = 10;

                waveWidth = Math.floor(V.canvas.width / V.barsCount - V.wavePadding);
            }

            var length = V.getIndexFromFrequency(V.maxFrequency) + 1;
            var frequencyStep = Math.floor(length / V.barsCount);

            var barSetWidth = V.barsCount * (waveWidth + V.wavePadding) - V.wavePadding;

            var xOffset = (V.canvas.width - barSetWidth) / 2;
            var yOffset = 2;

            var array = new Uint8Array(length);
            V.analyser.getByteFrequencyData(array);

            V.clearCanvas();

            for (var i = 0; i < V.barsCount; i++) {

                var value = 0;

                if (V.reflectEQ) {
                    value = array[V.getIndexFromFrequency(player.EQ.bandsList[i])];
                } else {
                    // gets the average value in the frequency range
                    for (var j = i * frequencyStep; j < (i + 1) * frequencyStep; j++) {
                        value += array[j];
                    }

                    value /= frequencyStep;
                }

                if (value < 0) {
                    value = 0;
                }

                var x = Math.floor(xOffset + i * (waveWidth + V.wavePadding));
                var y = Math.floor(-yOffset + waveHeight - value * V.barHeightMultiplier);

                var w = waveWidth;
                var h = Math.floor(waveHeight + value * V.barHeightMultiplier);

                V.canvasContext.fillRect(x, y, w, h);
            }

            V.canvasContext.fillRect(array.length * (waveWidth + V.wavePadding), 0, 1, waveHeight);

        } catch (e) {
            console.error(e);
        }
    };
}

Visualiser.prototype.getIndexFromFrequency = function (f) {
    // index = frequency / sampleRate * fftSize
    return Math.round(f / this.context.sampleRate * this.analyser.fftSize);
};

Visualiser.prototype.clearCanvas = function () {
    this.canvasContext.clearRect(0, 0, this.canvas.width, this.canvas.height);
};
//endregion Visualiser