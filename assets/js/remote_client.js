var url = 'http://'+document.domain+':4202/';

function sendEvent(name, data){
	if(typeof data === 'undefined'){
        data = [];
	}

    data = {name: name, data: data};

	var data_string = JSON.stringify(data);

	var oReq = new XMLHttpRequest();
	oReq.open("POST", url);
	oReq.send(data_string);
}