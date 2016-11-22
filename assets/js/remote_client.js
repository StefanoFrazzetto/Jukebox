function sendEvent(name, data) {
    if (typeof data === 'undefined') {
        data = [];
    }

    data = {name: name, data: data};

    var data_string = JSON.stringify(data);

    var oReq = new XMLHttpRequest();
    oReq.open("POST", getRemoteServerUrl());
    oReq.send(data_string);
}

function getRemoteServerUrl() {
    return 'http://' + document.domain + ':4202/';
}