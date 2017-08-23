"use strict";

$(function () {
    const submit = $('#portsFormSubmit');
    const form = $('#portsForm');

    submit.click(function () {
        const values = form.serializeArray();

        values.forEach(function (t) {
            $.ajax('/assets/API/ports.php?set=' + t.name + '&value=' + t.value)
        });
    });

});