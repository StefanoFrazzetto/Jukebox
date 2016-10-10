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
var box_size_x = 170;
var box_size_y = 170;

//Getting the window size. Look, it's sunny outisde! No, not anymore, I'm in Scotland.
//var height = container.height();
//var width = container.width();

//This should set the request
var request = 'type=all';
var sort_by = '1';
var search_field = 'artist';

var show, show_x, show_y;

//noinspection JSUnusedGlobalSymbols
var answer_to_life_universe_and_everything = 42; //That's probably why something still works.
var page = 1;

var imageSelector;

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

function reload() { //This function will load the page with AJAX!
    var full_request = 'assets/php/get_albums.php?' + request + '&page=' + page + '&x=' + show_x + '&y=' + show_y + '&orderBy=' + sort_by;
    $.ajax({
        url: full_request,
        contentType: "text/html;charset=utf8"
    }).done(function (response) {

        var re = /<!--(.*)-->$/;
        var m;

        if ((m = re.exec(response)) !== null) {
            if (m.index === re.lastIndex) {
                re.lastIndex++;
            }
        }

        var json_response = $.parseJSON(m[1]);

        if (json_response.code === 1) {
            //No results :(
            alert('No albums found matching the search criteria.');
        } else {

            if (json_response.isLastPage) {
                next.hide();
            } else {
                next.show();
            }

            if (json_response.isFirstPage) {
                previous.hide();
            } else {
                previous.show();
            }


            loader.fadeOut(animation_medium, function () {
                loader.html(response);
                $(".album:not(.filler)").click(function () {
                    var detected_id = $(this).attr('id');
                    openModalPage('assets/modals/album_details.php?id=' + detected_id);

                });
                $(".album .moar").click(function (e) {
                    var detected_id = $(this).parent().attr('id');
                    changeAlbum(detected_id);
                    e.stopPropagation();
                });
            });

            /* BOXES RESIZING WATCHDOG */
            divH = divW = 0;
            var element = $("#mainContentAjax").find("td");

            divW = element.width();
            divH = element.height();

            function checkResize() {
                element = $("#mainContentAjax").find("td");
                var w = element.width();
                var h = element.height();
                if (w != divW || h != divH) {
                    /*what ever*/
                    element.height(0);
                    element.width(0);
                    element.height('100%');
                    element.width('100%');
                    divH = h;
                    divW = w;
                }
            }

            checkResize();
            //var timer = setInterval(checkResize, animation_medium);
            //Woff!

            loader.fadeIn();


        }
    });
}

function getHowManyAlbumsToShow() { //Longest Name Ever. No comment needed here I guess :P
    var height = $(window).height() - container.offset().top; //297 //Dovrebbe essere 287 + 10    --- 1019 - 297 = 722!! //Inline calculations...
    var width = $(window).width() - 100;

    //Some random math to get how many albums are to be showed
    show_x = (Math.round(width / box_size_x));
    var resto_x = (width / box_size_x) - show_x; //Don't ask

    /* this one below need some fixin' */
    show_y = (Math.floor(((height - (resto_x * box_size_y)) / box_size_y))); //Don't ask really. It works. No matter WHY.

    if (show_y < 2)
        show_y = 2;
    if (show_x < 3) {
        show_x = 5;
        //if (show_y <= 2)
        // show_y = 2;
    }

    show = show_x * show_y;

    //alert(show);
}

function alphabet(value) {
    page = 1;
    request = "type=alphabet&alphabet=" + value;
    reload();
}

function search(value) {
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
    reload();
});

previous.click(function () { // Previous button click event
    if (page > 1) {
        page--;
        reload();
    }
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
    window.resizeEvt;
    $(window).resize(function () {
        clearTimeout(window.resizeEvt);
        window.resizeEvt = setTimeout(function () {
            getHowManyAlbumsToShow();
            reload();
        }, animation_short);
    });
});

