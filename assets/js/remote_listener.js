//var url = 'http://'+document.domain+':4201/';

var url = 'http://localhost:4201/';

var evtSource = new EventSource(url, { withCredentials: true });

var eventList = [];

function handleSSE(name, handler){
	evtSource.addEventListener(name, function(e){
		var obj = JSON.parse(e.data);

		handler(obj, e);
	}, false);
};

handleSSE('play', function(){
	pplay();
});

handleSSE('pause', function(){
	ppause();
});

handleSSE('play/pause', function(){
	play_pause();
});

handleSSE('next', function(){
	pnext();
});

handleSSE('previous', function(){
	pprevious();
});

handleSSE('stop', function(){
	pstop();
});

handleSSE('refresh', function(){
	document.location.reload(true);
});

handleSSE('play_album', function(data){
	changeAlbum(parseInt(data.album_id));
	console.log("chaning album to "+data.album_id);
	pplay();
});

function sendPlayerStatus(){
	function reqListener () {
		console.log('sending', name, data_string);
	}

	var oReq = new XMLHttpRequest();
	//oReq.addEventListener("load", reqListener);
	oReq.open("POST", url);
	oReq.send(JSON.stringify(getPlayerStatus()));
}

player.addEventListener('play', updateStatusHandler, false);
player.addEventListener('pause', updateStatusHandler, false);
player.addEventListener('canplay', updateStatusHandler, false);

function updateStatusHandler(e) {
	console.log(getPlayerStatus());
	sendPlayerStatus();
}