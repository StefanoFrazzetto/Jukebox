var $write;
var $well = $("#keyboardWell");

function bindForms() {
    $('input:not([type="submit"])').focus(function () {
        $well.clearQueue(); // This will stop any occuring animation (like the keyboard going down)

        var window_height = $(window).height();
        var window_width = $(window).width();

        //alert('hello');

        $write = $(this);
        var topPosition = parseInt($write.offset().top) + parseInt($write.outerHeight()) + 2;

        if(topPosition + $well.outerHeight() > window_height){ // Thise will move the keyboard up if there is not enough space
            var topPositionAlternative = parseInt(($write.offset().top) - $well.outerHeight()) - 2;
            if(topPositionAlternative > 0){
                topPosition = topPositionAlternative;
            }
        }

        $well.show(0, function () {
            $well.css({
                position: 'fixed'
            });
            $well.animate({
                left: (window_width - $well.outerWidth()) / 2,
                top: topPosition
            }, animation_medium);
        });
    });

    $('input').blur(function () {
        $well.animate({
            top: "100%"
        }, animation_medium);
        $well.hide(0);
    });
}

$(function () {
    var shift = false,
        capslock = false;

    $('#keyboardWell').drags();

    $('#keyboard').find('li').click(function (event) {
        var $this = $(this),
            character = $this.html(); // If it's a lowercase letter, nothing happens to this variable

        // Shift keys
        if ($this.hasClass('left-shift') || $this.hasClass('right-shift')) {
            $('.letter').toggleClass('uppercase');
            $('.symbol span').toggle();

            shift = (shift !== true);
            capslock = false;
            return false;
        }

        // Caps lock
        if ($this.hasClass('capslock')) {
            $('.letter').toggleClass('uppercase');
            capslock = true;
            return false;
        }

        // Delete
        if ($this.hasClass('delete')) {
            var html = $write.val();

            $write.val(html.substr(0, html.length - 1));
            return false;
        }

        // Special characters
        if ($this.hasClass('symbol')) character = $('span:visible', $this).html();
        if ($this.hasClass('space')) character = ' ';
        if ($this.hasClass('tab')) character = "\t";
        if ($this.hasClass('return')) {
            character = '';
            //simulateKeyPress();

            var focused = document.activeElement;

            // IF NOT TEXTAREA

            $(focused.form).submit();

            $(focused).blur();

            /*var e = jQuery.Event("keypress");
            e.which = 13; //choose the one you want
            e.keyCode = 13;

            focused.trigger(e);*/

        } //character = "\n";



        // Uppercase letter
        if ($this.hasClass('uppercase')) character = character.toUpperCase();

        // Remove shift once a key is clicked.
        if (shift === true) {
            $('.symbol span').toggle();
            if (capslock === false) $('.letter').toggleClass('uppercase');

            shift = false;
        }

        // Add the character
        $write.val($write.val() + character);
        event.preventDefault();

        event.stopPropagation();
    }).on("mousedown", function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    bindForms();


});
