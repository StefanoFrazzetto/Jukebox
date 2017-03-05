var sass = require('node-sass');
var fs = require('fs');

var outFilez = __dirname + '/../css/main.css';
var inFilez = __dirname + '/../scss/main.scss';

var result = sass.renderSync({
    file: inFilez,
    outFile: outFilez,
    //sourceMap: true,
    outputStyle: 'compressed'
});

// No errors during the compilation, write this result on the disk
fs.writeFile(outFilez, result.css, function (err) {
    if (err) {
        console.log('[!] Failed to write main.css\n', err);
    }
});

outFilez = __dirname + '/../css/main_remote.css';
inFilez = __dirname + '/../scss/main_remote.scss';

result = sass.renderSync({
    file: inFilez,
    outFile: outFilez,
    //sourceMap: true,
    outputStyle: 'compressed'
});

fs.writeFile(outFilez, result.css, function (err) {
    if (err) {
        console.log('[!] Failed to write main_remote.css\n', err);
    }
});

console.log("[@] Finished writing css files.");