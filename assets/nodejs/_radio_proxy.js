var http = require('http');

function getParameterByName(name, url) {
    if (!url) url = req.url;
    name = name.replace(/[\[\]]/g, "\\$&");
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

console.log('Starting server...');

http.createServer(function (req, res) {
    console.log('Client connecting...');

    var port = parseInt(getParameterByName('port', req.url));
    var address = getParameterByName('address', req.url);
    var request = getParameterByName('request', req.url);

    if (!(port && address && request)){
        port = 80;
        address = 'media-sov.musicradio.com';
        request = "/HeartPlymouthMP3";
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
    });

    client.on('error', function(err) {
        console.log(err);
        res.end();
    });

    req.on('close', function () {
        console.log('Connection Closed');
        client.destroy();
    });

}).listen(4242);