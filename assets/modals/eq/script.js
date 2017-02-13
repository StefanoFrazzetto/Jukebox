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
            stop: function (asd, ui) {
                EQ.changeGain(ui.value, i);
                console.log(ui.value);
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


// OLD

function set_eq_values(values) {
    values.forEach(function (value, index) {
        $('.slider-eq').eq(index).slider('value', value);
    });
}

function serialise_string(string) {
    return string.split(' ');
}

function deserialise_values(values) {
    return values.join(' ');
}