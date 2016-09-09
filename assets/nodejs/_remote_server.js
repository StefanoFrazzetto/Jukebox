var http = require('http');

var globalRes = null;

var jukebox_connected = false;

var keepAliveHandler;

var playerStatus = "{}";

function sendHeaderSuccess(res){
    res.writeHead(200, {
        //'Accept-Ranges': 'none',
        'Access-Control-Allow-Origin': 'http://localhost',
        'Connection': 'keep-alive',
        'Access-Control-Allow-Credentials': 'true',
        'Access-Control-Allow-Headers': 'Origin, Accept, X-Requested-With, Content-Type',
        'Access-Control-Allow-Methods': 'GET, OPTIONS, HEAD',
        'Cache-Control': 'no-cache, no-store',
        'Content-Type': 'text/event-stream',
        //'Transfer-Encoding': '',
        //'Pragma': 'no-cache'
    });
}

function sendTestEvent(res){
    res.write("event: ping\n");
    res.write('data: {"msg": "loool"}');
    res.write("\n\n");
}

function sendEvent (name, data){

    var data_string = JSON.stringify(data);

    sendRawEvent(name, data_string);
}

function sendKeepAlive(){
    globalRes.write(".\n"); 
    //globalRes.write("\n\n"); 
}

function sendRawEvent(name, raw_data){

    globalRes.write("event: "+name+"\n");
    globalRes.write('data: '+raw_data);
    globalRes.write("\n\n"); 
}

function sendTestEventG(){
    sendTestEvent(globalRes);
}

function handleStatusRequest(req, res){
    if(globalRes == null){
        res.end();
        return;
    }

    var jsonString = '';

    req.on('data', function (data) {
        jsonString += data;
    });

    req.on('end', function () {
        res.setHeader('Access-Control-Allow-Origin', '*');

        //res.setHeader('Access-Control-Allow-Origin', 'null');

        res.end();

        playerStatus = jsonString;

        //var data = JSON.parse(jsonString);

        //console.log(data);

        //sendRawEvent(data.name, JSON.stringify(data.data));
    });
}

http.createServer(function (req, res) {

    if(req.headers.accept != 'text/event-stream'){
        if (req.method == 'POST') {
            handleStatusRequest(req, res);
            return;
        }

        res.end('nothing here for you');

        return;
    }

    console.log('Incoming connection...');

    clearInterval(keepAliveHandler);

    keepAliveHandler = setInterval(function (){
        console.log('Sending keep-alive...');
        sendKeepAlive();
    }, 30000);

    //if(!jukebox_connected){
        globalRes = res;
    //}

    sendHeaderSuccess(res);

    jukebox_connected = true;

    req.on('close', function (err) {
        console.log('Connection Closed');
        jukebox_connected = false;
        globalRes = null;
        clearInterval(keepAliveHandler);
    });

}).listen(4201);


http.createServer(function (req, res) {
    //res.end(playerStatus);

    if(globalRes == null){
            res.end();
            return;
        }

    if (req.method == 'POST') {       

        var jsonString = '';

        req.on('data', function (data) {
            jsonString += data;
        });

        req.on('end', function () {
            res.setHeader('Access-Control-Allow-Origin', '*');

            //res.setHeader('Access-Control-Allow-Origin', 'null');

            res.end();

            var data = JSON.parse(jsonString);

            if(typeof data.data === "undefined"){
                data.data = [];
            }

            console.log(data.name, data.data);

            sendRawEvent(data.name, JSON.stringify(data.data));
        });
    }

    if(req.method == 'GET'){


        res.setHeader('Access-Control-Allow-Origin', '*');

        res.setHeader( 'content-type', 'application/json' );

        res.setHeader( 'charset', 'utf8' );

        res.setHeader( 'connection', 'close' );

        var length = new Buffer(playerStatus).length;

        res.setHeader('Content-Length', length);
        
        res.end(playerStatus);
    }
}).listen(4202);