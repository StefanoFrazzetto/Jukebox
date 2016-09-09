var url = 'http://'+document.domain+':4202/';

function sendEvent(name, data){
	if(typeof data === 'undefined'){
		var data = [];
	}

	var data = {name: name,  data: data};

	var data_string = JSON.stringify(data);

	function reqListener () {
		console.log('sending', name, data_string);
	}

	var oReq = new XMLHttpRequest();
	oReq.addEventListener("load", reqListener);
	oReq.open("POST", url);
	oReq.send(data_string);
}

function getRemotePlayerStatus(callback){
	$.getJSON(url, callback);
}