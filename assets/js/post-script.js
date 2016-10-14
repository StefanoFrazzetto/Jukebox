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
var box_size_x = 172;
var box_size_y = box_size_x;

//Getting the window size. Look, it's sunny outisde! No, not anymore, I'm in Scotland.
//var height = container.height();
//var width = container.width();

//This should set the request
var request = 'type=all';
var sort_by = '1';
var search_field = 'artist';

var show, show_x, show_y;
var page = 1;

//noinspection JSUnusedGlobalSymbols
var answer_to_life_universe_and_everything = 42; //That's probably why something still works.


var imageSelector;

var albums_storage = [];

function initImageSelectorObject() {
    imageSelector = {
        albumArtist: false,
        isRadio: false,
        to: null,
        from: null,
        imageUrl: null,
        defaultArtist: '',
        defaultAlbum: '',

        open: function () {
            openModalPage('assets/modals/image_picker/');
        },

        back: function () {
            openModalPage(this.from)
        },

        done: function () {
            openModalPage(this.to);
        }
    }
}

initImageSelectorObject();

function paginate() {
    var lastPage = Math.ceil(albums_storage.length / show);

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
    albums_storage.forEach(function (data) {
        i++;

        if (i >= show * (page - 1) && i < show * page) {
            var html = makeAlbumHtmlFromObject(data);
            loader.append(html);
        }
    });

    $(".album:not(.filler)").click(function () {
        var detected_id = $(this).attr('id');
        openModalPage('assets/modals/album_details.php?id=' + detected_id);
    });
    $(".album .moar").click(function (e) {
        var detected_id = $(this).parent().attr('id');
        changeAlbum(detected_id);
        e.stopPropagation();
    });
}

function reload() {
    var address = '/assets/php/get_all_album.json.php';

    $.getJSON(address)
        .done(function (data) {
            albums_storage = [];

            data.forEach(function (data, index) {
                albums_storage[index] = data;
            });

            paginate();

            console.log("loaded", memorySizeOf(albums_storage), "of albums storage.");

        })
        .fail(function () {
            error("An error occurred while loading the albums.");
        });
}

// TODO remove this in production
function memorySizeOf(obj) {
    var bytes = 0;

    function sizeOf(obj) {
        if (obj !== null && obj !== undefined) {
            switch (typeof obj) {
                case 'number':
                    bytes += 8;
                    break;
                case 'string':
                    bytes += obj.length * 2;
                    break;
                case 'boolean':
                    bytes += 4;
                    break;
                case 'object':
                    var objClass = Object.prototype.toString.call(obj).slice(8, -1);
                    if (objClass === 'Object' || objClass === 'Array') {
                        for (var key in obj) {
                            if (!obj.hasOwnProperty(key)) continue;
                            sizeOf(obj[key]);
                        }
                    } else bytes += obj.toString().length * 2;
                    break;
            }
        }
        return bytes;
    };

    function formatByteSize(bytes) {
        if (bytes < 1024) return bytes + " bytes";
        else if (bytes < 1048576) return (bytes / 1024).toFixed(3) + " KiB";
        else if (bytes < 1073741824) return (bytes / 1048576).toFixed(3) + " MiB";
        else return (bytes / 1073741824).toFixed(3) + " GiB";
    };

    return formatByteSize(sizeOf(obj));
};

function makeAlbumHtmlFromObject(object) {
    var album_container = $("<div class='album'>");
    album_container.attr("id", object.id);
    album_container.append("<div class=\"moar\"><i class=\"fa fa-play\"></i></div>");

    var img = $("<img>");
    img.attr("src", "jukebox/" + object.id + "/thumb.jpg");

    var details = $("<div class='albumDetails'>");

    var artist = $("<p>");
    var title = $("<p>");

    artist.addClass("albumArtist");
    title.addClass("albumTitle");

    artist.html(object.artist);
    title.html(object.title);

    details.append(artist);
    details.append(title);

    album_container.append(img);
    album_container.append(details);
    album_container.fadeOut(0);

    img.on("load", function () {
        album_container.fadeIn(animation_short);
    });

    return album_container;

    /*
     <div class="album" id="21">
     <div class="moar"><i class="fa fa-play"></i></div>
     <img src="jukebox/21/thumb.jpg">
     <div class="albumDetails">
     <p class="albumArtist">Alanis Morissette</p>
     <p class="albumTitle">Jagged Little pill</p>
     </div>
     </div>
     */
}

function getHowManyAlbumsToShow() { //Longest Name Ever. No comment needed here I guess
    var height = $(window).height() - container.offset().top; //297 //Dovrebbe essere 287 + 10    --- 1019 - 297 = 722!! //Inline calculations...
    var width = $(container).width();

    if (width < 850) {
        width = 850;
    }

    //Some random math to get how many albums are to be showed
    show_x = (Math.floor(width / box_size_x));

    show_y = (Math.floor(height / box_size_y));

    if (show_y < 2)
        show_y = 2;
    if (show_x < 3) {
        show_x = 5;
    }

    show = show_x * show_y;
    return show;
}

function alphabet(value) {
    // TODO update this
    page = 1;
    request = "type=alphabet&alphabet=" + value;
    reload();
}

function search(value) {
    // TODO update this
    page = 1;
    request = "type=search&searchField=" + search_field + "&search=" + value;
    reload();
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
    request = 'type=all';
    sort_by = '1';
    search_field = 'title';
    page = 1;

    var sorter = $('#sorter');

    sorter.find('.by.active').removeClass('active');
    sorter.find('.by:first-of-type').addClass('active');

    reload();
});

add_btn.click(function () {
    $.ajax('assets/php/prepare_upload.php');
    openModalPage('assets/modals/add_album/1.upload_album.php');
});

buttonsLetter.click(function (event) { // Previous button click event
    var letter = event.target.innerHTML;
    alphabet(letter);
});

pwr_btn.click(function () {
    $.ajax('assets/cmd/exec.php?cmd=shutdown'); // How many times I pressed it by mistake, and realized I am stupid.
});

$('#albumCover').click(function () {
    if (typeof(album_id) !== 'undefined')
        openModalPage('assets/modals/album_details.php?id=' + album_id);
});

//alert(show); //Just for debugging
previous.hide(); // This will hide the previous button, since we are at page 1 for reasons.

//Smart stuff
$(document).ready(function () {
    getHowManyAlbumsToShow(); //Pretty much self self explanatory
    reload(); //We now load the remote script via ajax
});

$(document).mouseup(function (e) {
    if (!dropdownModal.is(e.target) // if the target of the click isn't the container...
    // && dropdownModal.has(e.target).length === 0 // ... nor a descendant of the container
    ) {
        hideDropdownMenu();
    }
});

/* this should be included in a separated file to be loaded only in the local version of the app */

$(window).bind('resize', function () { //Good job! :)
    //window.resizeEvt;
    $(window).resize(function () {
        clearTimeout(window.resizeEvt);
        window.resizeEvt = setTimeout(function () {
            var _show = show;
            getHowManyAlbumsToShow();

            if (_show != show) {
                paginate();
            }
        }, animation_short);
    });
});

