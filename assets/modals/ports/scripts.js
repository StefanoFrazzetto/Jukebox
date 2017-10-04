"use strict";

$(function () {
    const submit = $('#portsFormSubmit');
    const form = $('#portsForm');

    submit.click(function () {
        const values = form.serializeArray();

        $.ajax({
            url: '/assets/API/ports.php',
            type: 'POST',
            data: JSON.stringify(values),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function () {
                alert("Ports assigned successfully!");
            }
        });
    });

});