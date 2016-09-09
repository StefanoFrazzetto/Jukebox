var http = require('http');
//var fs = require('fs');
//http://205.164.62.21:8045/;*.mp3

function getParameterByName(name, url) {
    if (!url) url = req.url;
    url = url.toLowerCase(); // This is just to avoid case sensitiveness  
    name = name.replace(/[\[\]]/g, "\\$&").toLowerCase();// This is just to avoid case sensitiveness for query parameter name
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function sendHeaderSuccess(res){
    res.writeHead(200, {
        'Accept-Ranges': 'none',
        'Content-Type': 'audio/mpeg',
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Origin, Accept, X-Requested-With, Content-Type',
        'Access-Control-Allow-Methods': 'GET, OPTIONS, HEAD',
        'Cache-Control': 'no-cache, no-store',
        'Transfer-Encoding': '',
        'Pragma': 'no-cache',
        'icy-notice': 'this is the notice',
        'icy-name': 'Radio name',
        'icy-genre': 'Genre',
        'icy-pub': '1',
        'icy-br': '128'        
    });
}

http.createServer(function (req, res) {
    var port = 8045;
    var address = '205.164.62.21';
    var request = "/;*mp3";

    port = parseInt(getParameterByName('port', req.url));
    address = getParameterByName('address', req.url);
    request = getParameterByName('request', req.url);

    if (!(port && address && request)){
        port = 8045;
        address = '205.164.62.21';
        request = "/;*mp3";
    }

    var client, net = require('net');

    client = new net.Socket();

    
    client.connect(port, address, function () {
        //console.log("req");
        console.log("Sending request");
        var raw_request = "GET " + request + " HTTP/1.0 \n \n\n";
        // Icy-MetaData: 1 // use that to get metadata. Metadata shouldn't be sent to the audio stream, or it will be read as music, and the some nice noises will come out on the other side.
        return client.write(raw_request.toString('utf-8'));
    });

    //res.removeHeader('Date');

    sendHeaderSuccess(res);

    //res.setHeader('Content-Type', 'audio/mpeg');

    //res.setHeader('Transfer-Encoding', '');

    client.on("data", function (data) {
        //console.log(".");
        //console.log(data);

        //var textChunk = data.toString('utf8');

        //console.log(textChunk);

        res.write(data);

        return;
    });

    client.on('error', function(err) {
        console.log(err);
        res.end();
    });

    req.on('close', function (err) {
        console.log('Connection Closed');
        client.destroy();
    });

}).listen(4242);