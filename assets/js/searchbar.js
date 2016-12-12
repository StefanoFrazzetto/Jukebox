var searchbox = $('#searchbox');

var searchbar = $('#searchBar');

var buttonsbar = $('#buttonsBar');

var search_input = $('#searchbox-input');

var searchbox_icon = $('#searchbox-icon');

var searchbox_icon_bis = $('#searchbox-icon-bis');

var artist_icon = $('#artist-icon');

var album_icon = $('#album-icon');

var song_icon = $('#song-icon');

var sorter = $('#sorter');

var sort_by_btn = $('#sort-icon');

var sort_by_menu = sorter.find('.by');

var search_by = $('#search-by');

search_by.on("mousedown", function (e) {
    e.preventDefault();
    e.stopPropagation();
});

var opened = false;

function slideToggle() {
    if (!slideClose()) {
        slideOpen();
    }
}

function slideOpen() {
    if (!opened) {
        opened = true;
        buttonsbar.clearQueue().fadeOut(animation_short, function () {
            searchbar.fadeIn(animation_short);

            search_input.focus();
        });
    }
}

function slideClose() {
    if (opened) {
        opened = false;
        searchbar.fadeOut(animation_short, function () {
            buttonsbar.fadeIn(animation_short);
        });
        return true;
    } else {
        return false;
    }
}

$(document).ready(function () {
    $.getScript('/assets/js/storageSorter.js', function () {
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

        sort_by_menu.click(function () {
            $('#sorter').find('.by.active').removeClass('active');
            $(this).addClass('active');
            sort_by = $(this).attr('data-value');

            getItSorted(sort_by);

            page = 1;
            paginate();
            sorter.toggle(animation_short);
        });

        searchbox_icon_bis.click(function () {
            searchbox.submit();
        });

        sort_by_btn.click(function (e) {
            e.preventDefault();
            sorter.toggle();
        });
    });
});

