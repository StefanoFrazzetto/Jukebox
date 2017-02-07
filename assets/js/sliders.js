//Store frequently elements in variables
var slider = $('#slider'),
    tooltip = $('.tooltip'),
    sliderv = $('#slider-vertical');

//Hide the Tooltip at first
tooltip.hide();

// VOLUME //
sliderv.slider({
    //Config
    range: "min",
    animate: "fast",
    orientation: "vertical",
    min: 0,
    start: function () {
        tooltip.fadeIn('fast');
    },
    slide: function (event, ui) { //When the slider is sliding
        var value = sliderv.slider('value');
        tooltip.css('top', 170 - value / 100 * 170 - 7).text(ui.value);  //Adjust the tooltip accordingly
        player.setVolume(ui.value / 100);
    },
    stop: function () {
        tooltip.fadeOut('fast');
    },
    change: function (event, ui) {
        //var value = slider.slider('value'),
        player.setVolume(ui.value);
    }
});


// SEEKBAR //
slider.slider({
    //Config
    range: "min",
    min: 0,
    step: 0.1,
    value: 0,
    //Slider Event
    slide: function (event, ui) { //When the slider is sliding
        var value = slider.slider('value'),
            volume = $('.volume');

        if (value <= 5) {
            volume.css('background-position', '0 0');
        }
        else if (value <= 25) {
            volume.css('background-position', '0 -25px');
        }
        else if (value <= 75) {
            volume.css('background-position', '0 -50px');
        }
        else {
            volume.css('background-position', '0 -75px');
        }

        player.seek(ui.value / 100 * player.getCurrentSongDuration());
    },
    change: function (event, ui) {
        //var value = slider.slider('value'),
        if (event.eventPhase === 3) {
            player.seek(ui.value / 100 * player.getCurrentSongDuration());
        }
    }
});

