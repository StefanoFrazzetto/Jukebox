function alert(message) {
    var anAlerta = new Alert();

    anAlerta.message = message;

    anAlerta.show();
}

var Alert = function (opt) {
    this.alert = this;

    // Options

    this.message = '&nbsp;';

    this.title = '';

    this.buttons = ["OK"];

    this.showHeader = true;

    this.class = null;

    // Loads custom options
    if (typeof  opt === 'object')
        this.options(opt);
    else if (typeof opt === 'string') {
        this.message = opt;
    }

    // Variables

    this.container = $('<div class="alert_container"><div class="alert"></div></div>');

    this.alert = $('<div class="alert"></div>');

    this.header = $('<div class="header">Dialog</div>');

    this.body = $('<div class="body"></div>');

    this.buttons_container = $('<div class="buttons"></div>');
};

Alert.prototype.show = function () {
    this.render();
    var temp = this;
    this.buttons_container.find('div').click(function () {
        temp.destroy();
    });

    this.container.fadeTo(0, 0);
    $('body').append(this.container);
    this.container.fadeTo(animation_short, 1);
};

Alert.prototype.hide = function (callback) {
    if (typeof callback === 'undefined') {
        callback = null;
    }

    this.container.fadeTo(animation_short, 0, callback);
};

Alert.prototype.destroy = function () {
    var container = this.container;

    this.hide(function () {
        container.remove();
    });

};

Alert.prototype.render = function () {
    var lol = this;

    this.body.html(this.message);
    this.header.html(this.title);

    this.buttons.forEach(function (button) {
        var html_button;

        if (typeof button === 'string') {
            html_button = new Button(button, null, null);
        } else if (typeof button === 'object') {
            html_button = new Button(button);
        }

        lol.buttons_container.append(html_button);
    });


    if (this.showHeader) {
        this.alert.append(this.header);
    }

    this.alert.append(this.body);

    this.alert.append(this.buttons_container);

    this.container.addClass(this.class);

    this.container.append(this.alert);
};

Alert.prototype.options = function (opt) {
    if (typeof opt !== 'object') {
        throw {
            name: "Illegal Argument Exception",
            message: "Provided argument is not an object."
        };
    }
    var temp = this;
    $.each(opt, function (key, val) {
        temp[key] = val;
    });

    //this.render();
};

var Button = function (opt, classes, callback) {
    this.html = $('<div tabindex="0" class="box-btn"></div>');

    this.isFinal = true;

    if (typeof opt === 'string') {
        this.text = opt;
        this.class = classes;
        this.callback = callback;
    } else if (typeof opt === 'object') {
        var temp = this;
        $.each(opt, function (key, val) {
            temp[key] = val;
        });
    }

    this.html.addClass(this.class);

    this.html.text(this.text);

    this.html.click(this.callback);

    return this.html;
};



function lavuoinapizza(){
    new Alert({
        message: "Are you sure you want to eat pizza?",
        title: "Pizza tonight?",
        buttons: [
        "Vittorio IS STUPID",
        {
            text: "Sure!",
            callback: function () {
                lavuoinapizza();
            }
        },
        {
            text: "No",
            class: "danger",
            callback: function () {
                lavuoinapizza();
            }
        }
        ]
    }).show();
}