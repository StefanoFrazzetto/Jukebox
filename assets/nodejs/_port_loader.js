const request = require('request');

const url = 'http://localhost/assets/API/ports.php';

module.exports = function portLoader(success, error, always) {
    console.log("[@] Retrieving ports from " + url);
    request.get({
        url: url,
        json: true,
        headers: {'User-Agent': 'request'}
    }, function (err, res, data) {
        if (err) {
            error(err)
        } else if (res.statusCode !== 200) {
            error(res.statusCode);
        } else {
            success(data);
        }
        always();
    });
};

