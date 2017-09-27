/**
 * Created by Vittorio on 22/01/2017.
 */

$(function () {
    var updateTried = false;

    function checkForUpdates() {
        $('#up-to-date, #not-up-to-date, #error').hide();
        $('#loader').show();

        $.getJSON('/assets/API/git.php?action=up_to_date')
            .done(function (data) {
                console.log(data);
                if (data.success) {
                    if (data.upToDate) { // up to date
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
        update();
    });

    function update() {
        $('#up-to-date, #not-up-to-date, #error').hide();
        var updating = $('#updating');
        updating.show();

        $.getJSON('/assets/API/updater.php?action=update')
            .done(function (done) {
                if (done.success) {
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
    }

    function loadChangeList() {
        $.getJSON('/assets/API/git.php?action=log')
            .done(function (data) {
                var cont = $('#changes').html('');
                data.logs.forEach(function (entry) {
                    cont.append("<li>" + entry + "</li>");
                })
            })
            .fail(function () {
                error("Failed to load change list");
            });
    }

    function error(error) {
        $('#errorMessage').html(error);
        $('#up-to-date').hide();
        $('#not-up-to-date').hide();
        $('#error').show();
    }

    $('.check-update-btn').click(function () {
        checkForUpdates();
    });

    function getSelectedBranch() {
        return $('#branch').val();
    }

    function changeBranch(branch_name) {
        $.getJSON('/assets/API/git.php?action=checkout&branch=' + branch_name)
            .done(function (data) {
                if (data.success) {
                    alert("Checked out to " + branch_name + " successfully!");
                    checkForUpdates();
                } else {
                    error("Failed to checkout branch " + branch_name);
                }
            })
            .fail(function () {
                error("Failed to contact the git server while checking out " + branch_name);
            });
    }

    function deleteBranch(branch_name) {
        $.getJSON('/assets/API/git.php?action=delete&branch=' + branch_name)
            .done(function (data) {
                if (data.success) {
                    alert("Deleted " + branch_name + " successfully!");
                    //noinspection JSValidateTypes
                    $("#branch").children().filter(function () {
                        return $(this).text() === branch_name;
                    }).remove();
                } else {
                    error(data.message);
                }
            })
            .fail(function () {
                error("Failed to contact the git server while deleting " + branch_name);
            });
    }

    $('#rebase_button').click(function () {
        changeBranch(getSelectedBranch());
    });

    $('#delete_button').click(function () {
        deleteBranch(getSelectedBranch());
    });

    $('#spam_update_button').click(function () {
        setInterval(function () {
            $('#up-to-date, #not-up-to-date, #error').hide();
            $('#loader').show();

            $.getJSON('/assets/API/git.php?action=up_to_date')
                .done(function (data) {
                    if (data.success) {
                        if (data.upToDate) { // up to date

                        } else { // not up to date
                            update();
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
        }, 10000)

    });


    loadChangeList();
    checkForUpdates();
});
