var http = require('http');

var jukebox_connection = null;

var jukebox_connected = false;

var jukebox_status = [];

var clients = [];

// Functions to send Sever Sent Events
// <editor-fold desc="Events" defaultstate="collapsed">
function sendEventJson(res, name, data) {

    var data_string = JSON.stringify(data);
    sendVeryRawEvent(res, name, data_string);
}

function sendRawEvent(name, raw_data) {
    sendVeryRawEvent(jukebox_connection, name, raw_data);
}

function sendVeryRawEvent(res, name, raw_data) {
    if (res == null)
        return;
    res.write("event: " + name + "\n");
    res.write('data: ' + raw_data);
    res.write("\n\n");
}
// </editor-fold>

// Server where the jukebox interface connects to
// <editor-fold desc="4201 Server" defaultstate="collapsed">
http.createServer(function (req, res) {
    var keepAliveHandler;

    function sendKeepAliveJukebox() {
        sendKeepAlive(jukebox_connection);
    }

    function broadcastStatusToClients() {
        clients.forEach(function (client) {
            if (client == null)
                return;

            sendEventJson(client, "status", jukebox_status);
        })
    }

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

            try {
                jukebox_status = JSON.parse(jsonString);
            } catch (e) {
                console.log("Failed to parse json");
                sendError(res, 400, "Bad request. Unable to parse JSON.");
                return;
            }

            res.end();

            broadcastStatusToClients();

            console.log("Finished handleSetStatusRequest.");
        });
    }

    function handleInternalEventRequest() {
        console.log('Incoming connection...');

        clearInterval(keepAliveHandler);

        keepAliveHandler = setInterval(function () {
            sendKeepAliveJukebox();
        }, 30000);

        jukebox_connection = res;

        headers['Access-Control-Allow-Origin'] = "http://localhost";
        sendHeaderEventSuccess(res);

        jukebox_connected = true;

        req.on('close', function (err) {
            console.log('Connection Closed');
            console.log(err);
            jukebox_connected = false;
            jukebox_connection = null;
            clearInterval(keepAliveHandler);
        });
    }

}).listen(4201);
// </editor-fold>

// Server where the remote controls connect to
// <editor-fold desc="4202 Server" defaultstate="collapsed">
http.createServer(function (req, res) {
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
        case 'sse':
            handleExternalEventRequest();
            return;
        default:
            sendError(res, 400, "Bad request");
            return;
    }

    function handleGetStatusRequest() {
        res.setHeader('content-type', 'application/json');

        res.setHeader('charset', 'utf8');

        res.setHeader('connection', 'close');

        var buff = new Buffer(JSON.stringify(jukebox_status));

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

    function handleExternalEventRequest() {
        console.log('Incoming remote connection...');

        clearInterval(res.keepAliveHandler);

        res.keepAliveHandler = setInterval(function () {
            sendKeepAlive(res);
        }, 1000);

        // send headers
        headers['Access-Control-Allow-Origin'] = req.headers.origin;
        res.writeHead(200, headers);

        // add this client to the list
        clients.push(res);

        // sends the first jukebox status
        sendEventJson(res, "status", jukebox_status);

        req.on('close', function () {
            console.log('Remote connection Closed');

            var i = clients.indexOf("b");

            if (i != -1) {
                clients.splice(i, 1);
            }

            clearInterval(res.keepAliveHandler);
        });
    }
}).listen(4202);
// </editor-fold>

function sendKeepAlive(res) {
    if (res != null)
        res.write(".\n");
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
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.writeHead(code, message);
    res.end(message);
}

var headers = {
    'Connection': 'keep-alive',
    'Access-Control-Allow-Credentials': 'true',
    'Access-Control-Allow-Headers': 'Origin, Accept, X-Requested-With, Content-Type',
    'Access-Control-Allow-Methods': 'GET, OPTIONS, HEAD',
    'Cache-Control': 'no-cache, no-store',
    'Content-Type': 'text/event-stream'
};

function sendHeaderEventSuccess(res) {
    res.writeHead(200, headers);
}