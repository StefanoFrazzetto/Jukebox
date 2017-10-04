console.log("[@] Starting nodeserver.js...");

var ports = {http: 80, ssh: 22, remote: 4202, radio: 4242};
const internalPort = 4201;

console.log("[@] Retrieving services ports...");

require('./_port_loader')(
    function (data) { // success
        ports = data;
        console.log('[@] Retrieved ports', data);
    }, function (error) { // fail
        console.log("[!] failed to load port! " + error);
    }, function () { // always
        require('./_radio_proxy.js')(ports.radio);
        require('./_remote_server.js')(internalPort, ports.remote);
    });