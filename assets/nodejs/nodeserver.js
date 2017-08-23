console.log("[@] Starting nodeserver.js...");

var ports = {webui: 80, remote: 4202, radio: 4242};

require('./_port_loader')(
    function (data) { // success
        ports = data;
        console.log('[@] Retrieved ports', data);
    }, function (error) { // fail
        console.log("[!] failed to load port! " + error);
    }, function () { // always
        require('./_radio_proxy.js')(ports.radio);
        require('./_remote_server.js')(4201, ports.remote);
    });

//process.__defineGetter__('stderr', function() { return fs.createWriteStream(__dirname + '/error.log', {flags:'a'}) });
//process.__defineGetter__('stdout', function() { return fs.createWriteStream(__dirname + '/access.log', {flags:'a'}) });

// redirect stdout / stderr

// var root_path = "/var/www/html";
//
// var spawn = require('child_process').spawn;
//
// //spawn('nodemon --watch ../ -e scss sass.js');
//
// var watcher = spawn('node', [__dirname + '/_sass_watcher.js'], {
//       detached: false
// });
//
// //nodemon --watch ../ -e scss sass.js