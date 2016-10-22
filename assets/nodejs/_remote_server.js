var http = require('http');

var jukebox_connection = null;

var jukebox_connected = false;

var playerStatus = [];

// <editor-fold desc="Events" >
//noinspection JSUnusedLocalSymbols
function sendEventJson(name, data) {

    var data_string = JSON.stringify(data);

    sendRawEvent(name, data_string);
}

function sendRawEvent(name, raw_data) {

    jukebox_connection.write("event: " + name + "\n");
    jukebox_connection.write('data: ' + raw_data);
    jukebox_connection.write("\n\n");
}
// </editor-fold>

// Server where the jukebox interface connects to
// <editor-fold desc="4201 Server" >
http.createServer(function (req, res) {
    var keepAliveHandler;

    switch (getConnectionType(req)) {
        case 'post':
            handleSetStatusRequest(req, res);
            return;
        case 'sse':
            handleInternalEventRequest(req, res);
            return;
        default:
            sendError(res, 400, "Bad request");
            return;
    }

    function handleSetStatusRequest() {
        var jsonString = '';

        req.on('data', function (data) {
            jsonString += data;
        });

        req.on('end', function () {
            res.setHeader('Access-Control-Allow-Origin', '*');

            //res.setHeader('Access-Control-Allow-Origin', 'null');

            try {
                playerStatus = JSON.parse(jsonString);
            } catch (e) {
                console.log("Failed to parse json");
                sendError(res, 400, "Bad request. Unable to parse JSON.");
                return;
            }

            res.end();

            console.log("Finished handleSetStatusRequest.");
        });
    }

    function handleInternalEventRequest() {
        console.log('Incoming connection...');

        clearInterval(keepAliveHandler);

        keepAliveHandler = setInterval(function () {
            sendKeepAlive();
        }, 30000);

        //if(!jukebox_connected){
        jukebox_connection = res;
        //}

        sendHeaderEventSuccess(res);

        jukebox_connected = true;

        req.on('close', function () {
            console.log('Connection Closed');
            jukebox_connected = false;
            jukebox_connection = null;
            clearInterval(keepAliveHandler);
        });
    }

}).listen(4201);
// </editor-fold>

// <editor-fold desc="4202 Server">
http.createServer(function (req, res) {
    //res.end(playerStatus);
    res.setHeader('Access-Control-Allow-Origin', '*');

    if (!jukebox_connected) {
        console.log("No jukebox connected");
        sendError(res, 412, "No jukebox currently connected.");
        return;
    }

    switch (getConnectionType(req)) {
        case 'post':
            handleSendEventRequest();
            return;
        case 'time':
            handleGetTimeRequest();
            return;
        case 'get':
            handleGetStatusRequest();
            return;
        default:
            sendError(res, 400, "Bad request");
            return;
    }

    function handleGetStatusRequest() {
        res.setHeader('content-type', 'application/json');

        res.setHeader('charset', 'utf8');

        res.setHeader('connection', 'close');

        var buff = new Buffer(JSON.stringify(playerStatus));

        res.setHeader('Content-Length', buff.length);

        res.end(buff);

        console.log("Finished handleGetStatusRequest.");
    }

    function handleSendEventRequest() {
        var jsonString = '';

        req.on('data', function (data) {
            jsonString += data;
        });

        req.on('end', function () {
            res.end();

            try {
                var data = JSON.parse(jsonString);
            } catch (e) {
                console.log("Failed to parse json");
                sendError(res, 422, "Unprocessable Entity. Given an invalid json.");
                return;
            }


            if (typeof data.data === "undefined") {
                data.data = [];
            }

            console.log(data.name, data.data);

            sendRawEvent(data.name, JSON.stringify(data.data));

            console.log("Finished handleSendEventRequest.");
        });
    }

    function handleGetTimeRequest() {
        res.end((new Date().getTime()).toString());
    }
}).listen(4202);
// </editor-fold>

function sendKeepAlive() {
    if (jukebox_connection != null)
        jukebox_connection.write(".\n");
    //jukebox_connection.write("\n\n");
}

function getConnectionType(req) {
    if (req.headers.accept == 'text/event-stream') {
        return 'sse';
    } else if (req.headers.accept == 'text/time') {
        return 'time';
    } else if (req.method == 'POST') {
        return 'post';
    } else if (req.method == 'GET') {
        return 'get';
    } else {
        return 'other';
    }
}

function sendError(res, code, message) {
    res.writeHead(code, message);
    res.end(message);
}

function sendHeaderEventSuccess(res) {
    res.writeHead(200, {
        //'Accept-Ranges': 'none',
        'Access-Control-Allow-Origin': 'http://localhost',
        'Connection': 'keep-alive',
        'Access-Control-Allow-Credentials': 'true',
        'Access-Control-Allow-Headers': 'Origin, Accept, X-Requested-With, Content-Type',
        'Access-Control-Allow-Methods': 'GET, OPTIONS, HEAD',
        'Cache-Control': 'no-cache, no-store',
        'Content-Type': 'text/event-stream'
        //'Transfer-Encoding': '',
        //'Pragma': 'no-cache'
    });
}