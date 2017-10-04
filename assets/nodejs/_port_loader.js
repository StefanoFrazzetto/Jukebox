module.exports = function portLoader(successCall, errorCall, alwaysCall) {
    const exec = require('child_process').exec;
    exec('php ../php/ports.php', function (error, stdout, stderr) {
        console.log("[@] Spawned ports PHP process");

        if (error !== null) {
            errorCall(stderr);
        } else {
            const data = JSON.parse(stdout);
            successCall(data);
        }

        alwaysCall();
    });
};