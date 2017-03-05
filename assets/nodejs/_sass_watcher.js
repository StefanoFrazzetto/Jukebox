var nodemon = require(__dirname + '/../../node_modules/nodemon');

var fs = require('fs');

nodemon({
    script: __dirname + '/sass.js',
    ext: 'scss',
    watch: __dirname + '/../'
});

nodemon.on('start', function () {
    console.log('[@] Node SASS watcher has started.');
}).on('quit', function () {
    process.exit();
    console.log('[@] Node SASS watcher has quit.');
}).on('restart', function (files) {
    console.log('[@] Node SASS watcher restarted due to: ', files, '.');
});