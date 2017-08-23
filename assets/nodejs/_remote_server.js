module.exports = function (private_server_port, public_server_port) {
    const http = require('http');

    var jukebox_connection = null;

    var jukebox_connected = false;

    var jukeboxStatus = {};

    var clients = [];

    console.log("[@] Starting remote server...");

    // Functions to send Sever Sent Events
    // <editor-fold desc="Events" defaultstate="collapsed">
    function sendEventJson(res, name, data) {
        const data_string = JSON.stringify(data);
        sendVeryRawEvent(res, name, data_string);
    }

    function sendRawEvent(name, raw_data) {
        sendVeryRawEvent(jukebox_connection, name, raw_data);
    }

    function sendVeryRawEvent(res, name, raw_data) {
        if (res === null)
            return;
        res.write("event: " + name + "\n");
        res.write('data: ' + raw_data);
        res.write("\n\n");
    }

    // </editor-fold>

    // Server where the jukebox interface connects to
    // <editor-fold desc="Private Server" defaultstate="collapsed">
    http.createServer(function (req, res) {
        var keepAliveHandler;

        function sendKeepAliveJukebox() {
            sendKeepAlive(jukebox_connection);
        }

        function broadcastStatusToClients(oldJukeboxStatus, jukeboxStatus) {
            clients.forEach(function (client) {
                if (client === null)
                    return;

                sendEventJson(client, "status", optimiseStatusPayload(oldJukeboxStatus, jukeboxStatus));
            })
        }

        function optimiseStatusPayload(oldJukeboxStatus, jukeboxStatus) {
            return compareObjects(oldJukeboxStatus, jukeboxStatus);
        }

        function compareObjects(a, b, partial) {
            if (typeof a !== "object") {
                throw new TypeError("First parameter must be an object, found " + typeof a)
            }

            if (typeof b !== "object") {
                throw new TypeError("Second parameter must be an object, found " + typeof b);
            }

            if (a === null && b === null) {
                return {};
            }

            if (a === null) {
                return b;
            }

            if (typeof partial === "undefined") {
                partial = false;
            }

            if (typeof partial !== "boolean") {
                throw new TypeError("Third parameter must be a boolean, found " + typeof partial);
            }

            // that was some bad ass error avoidance, mate!

            const diff = {};
            const bKeys = Object.keys(b);

            if (partial) {
                return JSON.stringify(b) !== JSON.stringify(a) ? b : null;
            }

            function da(fuq) {
                return typeof fuq === "object" && fuq !== null;
            }

            // Recursive compare of objects. Worst code ever.
            // You better look away for you own sanity.
            bKeys.forEach(function (key) {
                if (da(a[key]) && da(b[key])) {
                    const comp = compareObjects(a[key], b[key], true);
                    if (comp !== null)
                        diff[key] = comp;
                } else if (b[key] !== a[key])
                    diff[key] = b[key];
            });

            // Differential?
            // const aKeys = Object.keys(a);
            //
            // const missingKeys = aKeys.filter(function (n) {
            //     return bKeys.indexOf(n) > -1;
            // });
            //
            // missingKeys.forEach(function (t) {
            //     diff[t] = null;
            // });

            return diff;
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
                    const oldJukeboxStatus = jukeboxStatus;

                    jukeboxStatus = JSON.parse(jsonString);

                    res.end();

                    try {
                        broadcastStatusToClients(oldJukeboxStatus, jukeboxStatus);
                    } catch (e) {
                        console.error("[!] Failed to broadcast new status to clients.");
                        console.error(e.stack);
                    }


                } catch (e) {
                    console.error("[!] Failed to parse json", e);
                    console.error("   ", jsonString);
                    sendError(res, 400, "Bad request. Unable to parse JSON.");
                }
            });
        }

        function handleInternalEventRequest() {
            console.log('[@] Incoming jukebox connection from ' + req.connection.remoteAddress + '...');

            clearInterval(keepAliveHandler);

            keepAliveHandler = setInterval(function () {
                sendKeepAliveJukebox();
            }, 30000);

            jukebox_connection = res;

            headers['Access-Control-Allow-Origin'] = "http://localhost";
            sendHeaderEventSuccess(res);

            jukebox_connected = true;

            req.on('close', function (err) {
                console.log('[@] Connection Closed');
                console.log(err);
                jukebox_connected = false;
                jukebox_connection = null;
                clearInterval(keepAliveHandler);
            });

            console.log("[@] Jukebox connected!");
        }

    }).listen(private_server_port);
    console.log("[@] Started private remote server on port", private_server_port);
    // </editor-fold>

    // Server where the remote controls connect to
    // <editor-fold desc="Public Server" defaultstate="collapsed">
    http.createServer(function (req, res) {
        res.setHeader('Access-Control-Allow-Origin', '*');

        if (!jukebox_connected) {
            console.log("[~] No jukebox connected");
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

            var buff = new Buffer(JSON.stringify(jukeboxStatus));

            res.setHeader('Content-Length', buff.length);

            res.end(buff);

            console.log("[@] Finished handleGetStatusRequest.");
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
                    console.log("[!] Failed to parse json");
                    sendError(res, 422, "Unprocessable Entity. Given an invalid json.");
                    return;
                }

                if (typeof data.data === "undefined") {
                    data.data = [];
                }

                sendRawEvent(data.name, JSON.stringify(data.data));

                console.log('[@] Received \'', data.name, data.data, "' from", req.connection.remoteAddress);
            });
        }

        function handleGetTimeRequest() {
            res.end((new Date().getTime()).toString());
        }

        function handleExternalEventRequest() {
            console.log('[@] Incoming remote SSE request from ' + req.connection.remoteAddress + '...');

            // noinspection JSUnresolvedVariable
            if (typeof res.keepAliveHandler !== "undefined") { // noinspection JSUnresolvedVariable
                clearInterval(res.keepAliveHandler);
            }

            // noinspection JSUndefinedPropertyAssignment
            res.keepAliveHandler = setInterval(function () {
                sendKeepAlive(res);
            }, 1000);

            // send headers
            // noinspection JSUnresolvedVariable
            headers['Access-Control-Allow-Origin'] = req.headers.origin;
            res.writeHead(200, headers);

            // add this client to the list
            clients.push(res);

            // sends the first jukebox status
            sendEventJson(res, "status", jukeboxStatus);

            req.on('close', function () {
                console.log('[@] Remote client connection closed from ' + req.connection.remoteAddress);

                var i = clients.indexOf("b");

                if (i !== -1) {
                    clients.splice(i, 1);
                }

                // noinspection JSUnresolvedVariable
                clearInterval(res.keepAliveHandler);
            });
        }
    }).listen(public_server_port);
    console.log("[@] Started public  remote server on port", public_server_port);

    // </editor-fold>

    function sendKeepAlive(res) {
        if (res !== null)
            res.write(".\n");
    }

    function getConnectionType(req) {
        if (req.headers.accept === 'text/event-stream') {
            return 'sse';
        } else if (req.headers.accept === 'text/time') {
            return 'time';
        } else if (req.method === 'POST') {
            return 'post';
        } else if (req.method === 'GET') {
            return 'get';
        } else {
            return 'other';
        }
    }

    function sendError(res, code, message) {
        try {
            res.setHeader('Access-Control-Allow-Origin', '*');
            res.writeHead(code, message);
            res.end(message);
        } catch (e) {
            console.error("[!] Failed to send error message.", e);
        }
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
};