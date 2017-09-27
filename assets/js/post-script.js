//Button id
var next = $('#next'); //Set id accordingly
var previous = $('#previous'); //Same here
//Alphabet ID
var buttonsLetter = $('#alphabet').find('> a'); //Same here
//loader div
var container = $('#mainContentAjax');
var loader = $('#mainContentAjaxLoader');

//Menu button and modal
var menu_btn = $('#menu-btn');
var home_btn = $('#home-btn');
var add_btn = $('#add');
var pwr_btn = $('#power');
var dropdownModal = $('#dropdownModal');

//Sizes of the album divs
var box_size = 172;

//Getting the window size. Look, it's sunny outside! No, not anymore, I'm in Scotland.
//var height = container.height();
//var width = container.width();

//This should set the request
var sort_by = '1';
var search_field = 'artist';

var show, show_x, show_y;
var page = 1;

//noinspection JSUnusedGlobalSymbols
var answer_to_life_universe_and_everything = 42; //That's probably why something still works.

function paginate() {
    var lastPage = Math.ceil(storage.albumsFiltered.filter(Object).length / show);

    if (page < 1) {
        page = 1;
    }

    if (page > lastPage) {
        page = lastPage;
    }

    if (page <= 1) {
        previous.hide();
    } else {
        previous.show();
    }

    if (page >= lastPage) {
        next.hide();
    } else {
        next.show();
    }

    loader.html("");
    var i = -1;
    storage.albumsFiltered.forEach(function (data) {
        if (typeof data === 'undefined')
            return;

        i++;

        if (i >= show * (page - 1) && i < show * page) {
            var html = makeAlbumHtmlFromObject(data);
            loader.append(html);
        }
    });
}

function reload(callback) {
    storage.loadAll(function () {
        paginate();
        if (typeof callback === "function") callback();
    });
}

function makeAlbumHtmlFromObject(album) {
    var album_container = $("<div class='album'>");
    var play = $("<div class=\"moar\"><i class=\"fa fa-play\"></i></div>");

    album_container.attr("id", album.id);
    album_container.append(play);

    var img = $("<img>");

    img.attr("src", album.getCoverUrl());

    var details = $("<div class='albumDetails'>");

    var artist = $("<p>");
    var title = $("<p>");

    artist.addClass("albumArtist");
    title.addClass("albumTitle");

    artist.html(album.getArtistsNames());
    title.html(album.title);

    details.append(artist);
    details.append(title);

    album_container.append(img);
    album_container.append(details);
    album_container.fadeOut(0);

    play.click(function (e) {
        e.stopPropagation();
        e.preventDefault();
        album.play();
    });

    album_container.click(function () {
        modal.openPage('assets/modals/album_details?id=' + album.id);
    });

    img.on("load", function () {
        album_container.fadeIn(animation_short);
    });

    img.on("error", function () {
        img.attr('src', '/assets/img/album-placeholder.png');
        album_container.fadeIn(animation_short);
    });

    return album_container;
}

function getHowManyAlbumsToShow() { //Longest Name Ever. No comment needed here I guess
    var height = $(window).height() - container.offset().top; //297 //Dovrebbe essere 287 + 10    --- 1019 - 297 = 722!! //Inline calculations...
    var width = $(container).width();

    if (width < 850) {
        width = 850;
    }

    //Some random math to get how many albums are to be showed
    show_x = (Math.floor(width / box_size));

    show_y = (Math.floor(height / box_size));

    if (show_y < 2)
        show_y = 2;
    if (show_x < 3) {
        show_x = 5;
    }

    show = show_x * show_y;
    return show;
}

function alphabet(value) {
    var artists = []; // Lists the artists ids beginning with the chosen letter

    storage.artists.forEach(function (element) {
        if (value !== 0) {
            if (element.name.charAt(0).toLowerCase() === value.toLowerCase()) {
                artists.push(element.id);
            }
        }

        else if ((/[^a-zA-Z]/.test(element.name.charAt(0))))
            artists.push(element.id);
    });

    var results = []; // List of albums with the given artists

    storage.albums.forEach(function (element, index) {
        if ((storage.intersect(element.artists, artists)).length > 0)
            results[index] = element;
    });

    results = results.filter(function (n) {
        return n !== undefined
    }); // remove undefined from array

    if (results.length === 0) {
        error("No artists found starting with '" + value + "'.");
        return;
    }

    storage.albumsFiltered = results;

    page = 1;
    paginate();
}

function search(value) {
    var t0 = performance.now();

    var results = [];

    if (search_field === "tracks") {
        error("not implemented");
        // TODO load results from database
        return;
    }

    if (search_field === "artist") {
        var artists = [];

        storage.artists.forEach(function (artist) {
            if (artist.name.toLowerCase().includes(value.toLowerCase()))
                artists.push(artist.id);
        });

        storage.albums.forEach(function (album, id) {
            if (storage.intersect(album.artists, artists).length > 0)
                results[id] = album;
        });

    } else {
        storage.albums.forEach(function (element, index) {
            if (element[search_field].toLowerCase().includes(value.toLowerCase()))
                results[index] = element;
        });
    }


    results = results.filter(function (n) {
        return n !== undefined
    });

    var t1 = performance.now();
    console.log('Took', (t1 - t0).toFixed(4), 'milliseconds to perform search.');

    if (results.length === 0) {
        error("No albums found with '" + value + "' in " + search_field + ".");
        return;
    }

    storage.albumsFiltered = results;

    page = 1;
    paginate();
}

function changeSearchField(value, div) {
    page = 1;
    $('.searchbox-icon.active').removeClass('active');
    div.addClass('active');
    search_field = value;
    search_input.focus();
}

function showDropdownMenu() {
    menu_btn.addClass('active');
    dropdownModal.offset({
        top: 0
    });
}

function hideDropdownMenu() {
    menu_btn.removeClass('active');
    dropdownModal.css('top', '-150px');
}

function toggleDropdownMenu() {
    if (dropdownModal.css('top') === '0px') {
        hideDropdownMenu();
    } else if (dropdownModal.css('top') === '-150px') {
        showDropdownMenu();
    }
}

next.click(function () { //Next button click event
    page++;
    paginate();
});

previous.click(function () { // Previous button click event
    page--;
    paginate();
});

menu_btn.click(function () {
    toggleDropdownMenu();
});

home_btn.click(function () {
    storage.albumsFiltered = storage.albums.slice();

    sort_by = '1';
    search_field = 'title';
    page = 1;

    var sorter = $('#sorter');

    sorter.find('.by.active').removeClass('active');
    sorter.find('.by:first-of-type').addClass('active');

    paginate();
});

add_btn.click(function () {
    Uploader.start();
});

buttonsLetter.click(function (event) { // Previous button click event
    var letter = event.target.innerHTML;
    alphabet(letter);
});

pwr_btn.click(function () {
    new Alert({
        message: "Select an option:",
        title: "Shutdown",
        buttons: [
            {
                text: "Shutdown",
                callback: function () {
                    $.ajax('assets/API/system.php?action=shutdown');
                    // How many times I pressed it by mistake, and realized I was stupid.
                }
            },
            {
                text: "Restart",
                callback: function () {
                    $.ajax('assets/API/system.php?action=reboot');
                }
            },
            "Cancel"
        ]
    }).show();
});

$('#albumCover').click(function () {
    if (player.isRadio) {
        modal.openPage('/assets/modals/radio');
        return;
    }
    if (typeof(player.getCurrentAlbumId()) !== 'undefined')
        modal.openPage('assets/modals/album_details?id=' + player.getCurrentAlbumId());
    else
        console.error("Unable to retrieve current album id.");
});

previous.hide(); // This will hide the previous button, since we are at page 1 for reasons.

$(document).mouseup(function (e) {
    if (!dropdownModal.is(e.target) // if the target of the click isn't the container...
    // && dropdownModal.has(e.target).length === 0 // ... nor a descendant of the container
    ) {
        hideDropdownMenu();
    }
});

//Smart stuff
$(document).ready(function () {
    getHowManyAlbumsToShow(); //Pretty much self self explanatory

    // We now load the remote script via ajax
    $.getScript('/assets/js/storage.js', function () {
        // Load the albums
        reload(function () {
            // And initialize the player after all the albums are loaded.
            initPlayer();

            if (isJukebox) {
                setInterval(function () {
                    sendPlayerStatus();
                }, 5000);

                sendPlayerStatus();
            }
        });
    });

    // Handles window resize event to paginate the view.
    // Must be disabled in the jukebox for performance reasons.
    if (!isJukebox) {
        $(window).bind('resize', function () { //Good job! :)
            //window.resizeEvt;
            $(window).resize(function () {
                clearTimeout(window.resizeEvt);
                window.resizeEvt = setTimeout(function () {
                    var _show = show;
                    getHowManyAlbumsToShow();

                    if (_show !== show) {
                        paginate();
                    }
                }, animation_short);
            });
        });
    }
});