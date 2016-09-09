
//require('./_sass_watcher.js');
require('./_radio_proxy.js');

require('./_remote_server.js');

//process.__defineGetter__('stderr', function() { return fs.createWriteStream(__dirname + '/error.log', {flags:'a'}) });
//process.__defineGetter__('stdout', function() { return fs.createWriteStream(__dirname + '/access.log', {flags:'a'}) });

// redirect stdout / stderr

var root_path = "/var/www/html";

var spawn = require('child_process').spawn;

//spawn('nodemon --watch ../ -e scss sass.js');

var watcher = spawn('node', [__dirname + '/_sass_watcher.js'], {
      detached: false
});

//nodemon --watch ../ -e scss sass.js