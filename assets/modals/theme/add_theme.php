<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 27-Nov-16
 * Time: 16:10
 */
?>
<div class="modalHeader">Theme Creator</div>
<div class="modalBody center">
    <form id="themeForm">
        <div class="col-left mCustomScrollbar">
            <div class="col-row">
                <div class="col-left">
                    <label>Name <input type="text" required name="name" placeholder="Theme name"
                                       class="full-wide"></label>
                </div>
                <div class="col-right">
                    Stronger Shadows
                    <div class="onoffswitch" id="dhcp_div">
                        <input type="checkbox" name="dark_accents" class="onoffswitch-checkbox" id="dhcp">
                        <label class="onoffswitch-label" for="dhcp">
                            <span class="onoffswitch-inner"></span>
                            <span class="onoffswitch-switch"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-right ">
            <div class="col-row">
                <div class="col-left">
                    <label>
                        Text Color
                        <input name="text_color" type="color" class="full-wide"/>
                    </label>
                </div>
                <div class="col-right">
                    <label>
                        Highlight Color
                        <input name="highlight_color" type="color" class="full-wide"/>
                    </label>
                </div>
            </div>
            <div class="col-row">
                <div class="col-left">
                    <label>
                        Background Color
                        <input name="background_color" type="color" class="full-wide"/>
                    </label>
                </div>
                <div class="col-right">
                    <label title="Background Color Alternative">
                        Background Color Alt
                        <input name="background_color_highlight" type="color" class="full-wide"/>
                    </label>
                </div>
            </div>
            <div class="col-row">
                <div class="col-left">
                    <label>
                        Border Color
                        <input name="border_color" type="color" class="full-wide"/>
                    </label>
                </div>
                <div class="col-right">
                    <label>
                        Overlays Color
                        <input name="overlays" type="color" class="full-wide"/>
                    </label>
                </div>
            </div>
        </div>
        <input type="submit" class="hidden invisible"/>
    </form>
</div>
<div class="modalFooter">
    <button class="right" id="addThemeSave">Save</button>
</div>
<script>
    var themeForm = $('#themeForm');

    $('#addThemeSave').click(function () {
        themeForm.submit();
    });

    themeForm.submit(function (e) {
        e.preventDefault();

        var values = $(this).serializeArray();

        var obj = {};

        values.forEach(function (el) {
                obj[el.name] = el.value;
            }
        );

        if (obj.name == '') {
            error('The theme name must be specified.');
            return;
        }

        obj['dark_accents'] = obj['dark_accents'] == "on";

        $.ajax({
            url: '/assets/modals/theme/ajax/add_theme.php',
            method: "POST",
            data: JSON.stringify(obj)
        })
            .done(function (data) {
                if (data == "success") {
                    alert("Theme saved successfully.");
                    openModalPage('/assets/modals/theme/index.php')
                } else {
                    error(data);
                }
            })
            .fail(function () {
                error("Failed to save the theme.");
            });
    });
</script>
