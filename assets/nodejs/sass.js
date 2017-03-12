var sass = require(__dirname + '/../../node_modules/node-sass');
var fs = require('fs');

function compileFile(file) {
    var outFolder = __dirname + '/../css/';
    var inFolder = __dirname + '/../scss/';

    var outFile = outFolder + file + '.css';
    var inFile = inFolder + file + '.scss';

    var result = sass.renderSync({
        file: inFile,
        outFile: outFile,
        //sourceMap: true,
        outputStyle: 'compressed'
    });

    fs.readFile(outFile, 'utf8', function (err, data) {

        if (err || data != result.css) {
            fs.writeFile(outFile + ".tmp", result.css, function (err) {
                if (err) {
                    console.log("[@] Error while reading " + file + ".");
                } else {
                    console.log("[@] File " + file + " needs updating.");
                }

                if (err) {
                    console.log('[!] Failed to write ' + file + '.\n', err);
                } else {
                    console.log("[@] Finished writing " + file + ".");
                }

                fs.renameSync(outFile + ".tmp", outFile);
            });
        } else {
            console.log("[@] File " + file + " already up to date.");
        }
    });
}

compileFile('main');
compileFile('main_remote');