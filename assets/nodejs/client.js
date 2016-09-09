var http = require('http');
var fs = require('fs');
//http://205.164.62.21:8045/;*.mp3

var client, net, raw_request;

var port = 8045;
var address = '205.164.62.21';
var request = "/;*mp3";

var response;

net = require('net');

raw_request = "GET " + request + " HTTP/1.0 \n Icy-MetaData: 1\n\n";

client = new net.Socket();

client.connect(port, address, function () {
    console.log("Sending request");
    return client.write(raw_request.toString('utf-8'));
});


var has_header = false;

var m;

client.on("data", function (data) {
    //console.log(".");
    //console.log(data);

    var textChunk = data.toString('utf8');

    //console.log(textChunk);

    response += textChunk;

    var re = /^([^:]*):(.*)$/gmi;

    myArray = re.exec(textChunk);

    res.setHeader('Access-Control-Allow-Origin', "*");
    res.setHeader('Access-Control-Allow-Headers', "X-Requested-With");

    while ((m = re.exec(textChunk)) !== null) {
        if (m.index === re.lastIndex) {
            re.lastIndex++;
        }
    }

    //if()

    //console.log(response);

    if (response.length > 1) {
        client.destroy();
        console.log(response);
        console.log(myArray[1]);
    }

    return;
    //return console.log(data);
});
