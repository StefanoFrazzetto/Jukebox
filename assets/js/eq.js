var context;
//var audioBuffer;
var sourceNode;
var analyser;
var javascriptNode;
var divided_by = 15;
var waveWidth = 0;

// Settiable variables

var barsCount = 50;
var waveHeight = 94;
var waveHeightMultiplier = 0.3; //3 
var smoothingTimeConstant = 0.7;
var wavePadding = 2;
var shownSpectrum = 0.7;


// get the context from the canvas to draw on
var ctx = document.getElementById('eq_canvas').getContext("2d");

setupGradient(ctx);

// load the sound
setupAudioNodes();
//disconnectNodes();

function setupAudioNodes() {
    // create the audio context (chrome only for now)
    if (! window.AudioContext) {
        if (! window.webkitAudioContext) {
            alert('no audiocontext found');
            return;
        }
        window.AudioContext = window.webkitAudioContext;
    }

    context = new AudioContext();

    // setup a javascript node
    javascriptNode = context.createScriptProcessor(2048, 1, 1);
    
    // connect to destination, else it isn't called
    javascriptNode.connect(context.destination);

    // setup a analyzer
    analyser = context.createAnalyser();
    analyser.smoothingTimeConstant = smoothingTimeConstant;
    analyser.fftSize = 512;

    // create a buffer source node
    //sourceNode = context.createBufferSource();
    var player = document.getElementById('player');
    player.crossOrigin = "Anonymous";
    sourceNode = context.createMediaElementSource(player);
    sourceNode.connect(analyser);
    analyser.connect(javascriptNode);

    sourceNode.connect(context.destination);

    player.onerror = function (){
        ctx.clearRect(0, 0, 354, 95);
    };

    javascriptNode.onaudioprocess = function() {
    
        try {
            // get the average for the first channel
            var length = Math.round(analyser.frequencyBinCount * shownSpectrum);

            waveWidth = (354 / length) - wavePadding;

            divided_by = Math.floor(length / barsCount);

            //length = 5;

            var array =  new Uint8Array(length);
            analyser.getByteFrequencyData(array);

            // set the fill style
            
            //ctx.fillStyle="rgba(255, 25, 0, 1)";
            //drawSpectrum2(array);
            //ctx.fillStyle="rgba(0, 25, 255, 1)";


            drawSpectrum(array);
        } catch (e){
            console.log("Gotcha!");
        }    
    }

}

function disconnectNodes(){
	//context.close();
	console.log('Disconnecting the audio nodes');
	javascriptNode.disconnect();
	
	analyser.disconnect();
	
	//sourceNode.disconnect();

	//sourceNode.disconnect();

	//sourceNode.connect(context.destination);
	
	

}

// when the javascript node is called
// we use information from the analyzer node
// to draw the volume

function drawSpectrum(array) {
    
    // clear the current state
    ctx.clearRect(0, 0, 354, 95);

    // bars style

    for ( var i = 0; i < (array.length); i = i + divided_by ){

        var value = 0;

        for (var j = 0; j < divided_by; j ++) {
            value = value + array[i+j];                
        }

        value = value / (divided_by);

        ctx.fillRect(
            i*(waveWidth + wavePadding), // x
            waveHeight-value*waveHeightMultiplier, // y
            waveWidth*divided_by + (divided_by * wavePadding) -wavePadding, // w
            waveHeight + value *waveHeightMultiplier // h
            );
        //  console.log([i,value])
    }

    ctx.fillRect(array.length * (waveWidth + wavePadding), 0, 1, waveHeight);
};

function drawSpectrum2(array) {
    for ( var i = 0; i < (array.length); i ++ ){
        var value = array[i];

        ctx.fillRect(
            i*(waveWidth + wavePadding),
            waveHeight-value*waveHeightMultiplier,
            waveWidth,
            waveHeight + value *waveHeightMultiplier // h
            );
        //  console.log([i,value])
    }
};

function setupGradient(ctx){
    // create a gradient for the fill. Note the strange
    // offset, since the gradient is calculated based on
    // the canvas, not the specific element we draw
    var gradient = ctx.createLinearGradient(0,0,0,100);
    //gradient.addColorStop(1,'#a0a0ff');
    //gradient.addColorStop(0.75,'#a0c0ff');
    //gradient.addColorStop(0.25,'#ffffff');
    //gradient.addColorStop(0,'#ff0000');

    gradient.addColorStop(1,'#027FF2');
    gradient.addColorStop(0.90,'#212121');
    gradient.addColorStop(0.25,'#212121');
    gradient.addColorStop(0, '#F44336');

    ctx.fillStyle=gradient;
}