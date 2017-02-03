'use strict';

//region Player
function Player() {
    this.audioApiSupported = true;

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
}

Player.prototype.setVolume = function (value) {
    this.outputNode.gain.value = value;
};
//endregion

//region EQ
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
        if (i == 0) {
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


    if (this.container != null) {
        this.container.innerHTML = '';

        this.drawEQ(this.container);
    }
};

EQ.prototype.changeBands = function (index) {
    this.setBands(this.getBands(index));
    console.log(this.getBands(index));
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

        if (EQ.currentBandId == index) {
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
//endregion

//region Visualiser
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
    this.barHeightMultiplier = 0.5; //3
    this.smoothingTimeConstant = 0.7;
    this.shownSpectrum = 1;
    this.reflectEQ = false;

    // -------------
    // Initialisation

    this.javascriptNode.connect(this.context.destination);

    // setup a analyzer
    this.analyser.smoothingTimeConstant = this.smoothingTimeConstant;
    this.analyser.fftSize = this.fftSize;

    this.input.connect(this.analyser);
    this.analyser.connect(this.javascriptNode);

    var V = this;

    this.javascriptNode.onaudioprocess = function () {
        try {
            if (V.reflectEQ) {
                V.barsCount = player.EQ.bandsList.length;
            }

            var waveWidth = Math.floor(V.canvas.width / V.barsCount - V.wavePadding);
            var waveHeight = Math.floor(V.canvas.height);


            // Fail safe in case the bars gets too small
            if (waveWidth < 1) {
                V.wavePadding = 1;
                V.barsCount = 10;

                waveWidth = Math.floor(V.canvas.width / V.barsCount - V.wavePadding);
            }

            var length = Math.floor(V.analyser.frequencyBinCount * V.shownSpectrum);
            var frequencyStep = Math.floor(length / V.barsCount);


            var barSetWidth = V.barsCount * (waveWidth + V.wavePadding) - V.wavePadding;

            var xOffset = (V.canvas.width - barSetWidth) / 2;
            var yOffset = 5;

            var array = new Uint8Array(length);
            V.analyser.getByteFrequencyData(array);

            V.canvasContext.clearRect(0, 0, V.canvas.width, V.canvas.height);

            for (var i = 0; i < V.barsCount; i++) {

                var value = 0;

                if (V.reflectEQ) {
                    // Maybe they should be evaluated?
                    var o = Math.floor(player.EQ.bandsList[i] / V.context.sampleRate * V.analyser.fftSize);
                    value = array[o];
                    //f = i * V.context.sampleRate / V.analyser.fftSize;
                } else {
                    // gets the average value in the frequency range
                    for (var j = i * frequencyStep; j < (i + 1) * frequencyStep; j++) {
                        value = value + array[j];
                    }

                    value = value / frequencyStep;
                }

                if (value < 0) {
                    value = 0;
                }


                var x = Math.floor(xOffset + i * (waveWidth + V.wavePadding));
                var y = Math.floor(-yOffset + waveHeight - value * V.barHeightMultiplier);

                var w = waveWidth;
                var h = Math.floor(waveHeight + value * V.barHeightMultiplier);

                V.canvasContext.fillRect(x, y, w, h);
                //  console.log([i,value])
            }

            V.canvasContext.fillRect(array.length * (waveWidth + V.wavePadding), 0, 1, waveHeight);
            //endregion

        } catch (e) {
            console.error(e);
        }
    };
}

//endregion