var searchbox = $('#searchbox');

var searchbar = $('#searchBar');

var buttonsbar = $('#buttonsBar');

var search_input = $('#searchbox-input');

var search_items = $('#search-items');

var searchbox_icon = $('#searchbox-icon');

var searchbox_icon_bis = $('#searchbox-icon-bis');

var searchbox_icons = $('.searchbox-icon');

var artist_icon = $('#artist-icon');

var album_icon = $('#album-icon');

var song_icon = $('#song-icon');

var sorter = $('#sorter');

var sort_by_btn = $('#sort-icon');

var sort_by = $('#sorter .by');

var search_by = $('#search-by');

search_by.on("mousedown", function (e) {
    e.preventDefault();
    e.stopPropagation();
});

//search_items.hide();
var opened = false;

// If an event gets to the body
/*$("body").click(function () {
 
 });
 
 $('.searchbox').click(function (e) {
 e.stopPropagation();
 });*/

function slideToggle() {
    if (!slideClose()) {
        slideOpen();
    }
}

function slideOpen() {
    if (!opened) {
        opened = true;
        buttonsbar.clearQueue().fadeOut(200, function () {
            searchbar.fadeIn(200);

            search_input.focus();
        });
    }
}

function slideClose() {
    if (opened) {
        opened = false;
        searchbar.fadeOut(200, function () {
            buttonsbar.fadeIn(200);
        });
        return true;
    } else {
        return false;
    }
}



search_input.blur(function () {
    slideClose();
});

searchbox_icon.click(function () {
    slideToggle();
});

artist_icon.click(function (e) {
    changeSearchField('artist', artist_icon);
    e.preventDefault();
});

album_icon.click(function (e) {
    changeSearchField('title', album_icon);
    e.preventDefault();
});

song_icon.click(function (e) {
    e.preventDefault();
    changeSearchField('tracks', song_icon);
    return false;
});

searchbox.submit(function (event) {
    event.preventDefault();

    var search_query = search_input.val();
    search(search_query);
    slideClose();
});

sort_by.click(function (event) {
    $('#sorter .by.active').removeClass('active');
    $(this).addClass('active');
    sort_by = $(this).attr('data-value');
    page = 1;
    reload();
    sorter.toggle(200);
});

searchbox_icon_bis.click(function () {
    searchbox.submit();
});

sort_by_btn.click(function (e){
    e.preventDefault();
    sorter.toggle();
});




//$("#searchbox-input").focus && $('#Return_Button').click(function(){		// not sure why this activates on all inputs after i use the searchbar
//		      $("#searchbox-input").blur();		
//});


/*searchbox.keydown(function(){
 var search_query = search_input.val();
 search(search_query);
 });*/
