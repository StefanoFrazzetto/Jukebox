"use strict";

$(function () {
    const submit = $('#portsFormSubmit');
    const form = $('#portsForm');

    submit.click(function () {
        const values = form.serializeArray();
        const valuesLikeTheOthersButBetter = {};

        values.forEach(function (t) {
            const keyRing = Object.keys(t);
            valuesLikeTheOthersButBetter[t[keyRing[0]]] = parseInt(t[keyRing[1]]);
        });

        $.ajax({
            url: '/assets/API/ports.php',
            type: 'POST',
            data: JSON.stringify(valuesLikeTheOthersButBetter),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function () {
                alert("Ports assigned successfully!");
            }
        });
    });

});