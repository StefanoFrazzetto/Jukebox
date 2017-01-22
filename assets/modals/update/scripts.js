/**
 * Created by Vittorio on 22/01/2017.
 */

var updateTried = false;

function checkForUpdates() {
    $('#up-to-date, #not-up-to-date, #error').hide();
    $('#loader').show();

    $.getJSON('/assets/API/git.php?git=up_to_date')
        .done(function (data) {
            if (data.status == "success") {
                if (data.data) { // up to date
                    $('#up-to-date').show();
                    updateTried = false;
                } else { // not up to date
                    if (updateTried)
                        error("Failed to update #2");
                    else
                        $('#not-up-to-date').show();
                }
            } else {
                error("Oh, snap! The update checker gave a bad output.");
            }

        })
        .fail(function () {
            error("Failed to contact the update checker.");
        })
        .always(function () {
            $('#loader').hide();
            loadChangeList();
        });
}

$('.update-btn').click(function () {
    $('#up-to-date, #not-up-to-date, #error').hide();
    var updating = $('#updating');
    updating.show();

    $.getJSON('/assets/API/git.php?git=pull')
        .done(function (done) {
            if (done.status == "success") {
                updateTried = true;
                checkForUpdates();
            } else {
                error("Failed to update #1");
            }

        })
        .fail(function () {
            error("Failed to contact the update server.")
        })
        .always(function () {
            updating.hide();
        });
});

function loadChangeList() {
    $.getJSON('/assets/API/git.php?git=log')
        .done(function (data) {
            var cont = $('#changes').html('');
            data.data.forEach(function (entry) {
                cont.append("<li>" + entry + "</li>");
            })
        })
        .fail(function () {
            error("Failed to load change list");
        });
}

function error(error) {
    $('#errorMessage').html(error);
    $('#error').show();
}

$('.check-update-btn').click(function () {
    checkForUpdates();
});

function changeBranch(branch_name) {
    $.getJSON('/assets/API/git.php?git=checkout&branch=' + branch_name)
        .done(function (data) {
            if (data.status === 'success') {
                alert("Checked out to " + branch_name + " successfully!");
                checkForUpdates();
            } else {
                error("Failed to checkout branch " + branch_name);
            }
        })
        .fail(function () {
            error("Failed to checkout branch " + branch_name);
        });
}

$('#branch').change(function () {
    var branch = $(this).val();
    changeBranch(branch);
});

$('#branch_button').click(function () {
    changeBranch($('#branch').val())
});


loadChangeList();
checkForUpdates();
