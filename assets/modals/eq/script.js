var eqSwitch = $('#eq-switch');
var presetsHolder = $('#presets');

EQ.prototype.drawModalEQ = function (container) {
    this.container = container;
    var EQ = this;

    function drawBar(i) {
        var div = document.createElement('div');
        div.className = 'progressBar slider-eq';

        $(div).slider({
            //Config
            range: "min",
            animate: animation_medium,
            orientation: "vertical",
            value: EQ.defaultGain,
            min: -EQ.maxGain,
            max: EQ.maxGain,
            slide: function (_, ui) {
                EQ.changeGain(ui.value, i);
            },
            change: function (_, ui) {
                EQ.changeGain(ui.value, i);
            }
        });

        return div;
    }

    EQ.bandsList.forEach(function (a, asd) {
        container.appendChild(drawBar(asd));
    });

    // <div id=\"slider-eq-$i\" class=\"slider-eq progressBar\"></div>
};


player.EQ.drawModalEQ(document.getElementById('eq-holder'));

eqSwitch.change(function () {
    if ($(this).is(':checked')) {
        player.EQ.connect();
    } else {
        player.EQ.disconnect();
    }
});

if (player.EQ.connected) {
    eqSwitch.prop("checked", true);
}

// OLD

function set_eq_values(values) {
    values.forEach(function (value, index) {
        $('.slider-eq').eq(index).slider('value', ((value - 50) / 50) * player.EQ.maxGain).stop();
    });
}

$.getJSON('/assets/modals/eq/presets.json')
    .done(function (presets) {
        presets.forEach(function (preset) {
            var li = $('<li>');
            li.html(preset.name);

            li.click(function () {
                set_eq_values(preset.values);
            });

            presetsHolder.append(li);
        })
    })
    .fail(function () {
        error("Failed to load presets.");
    });