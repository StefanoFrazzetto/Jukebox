<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 25-Nov-16
 * Time: 12:30
 */

include '../../php-lib/Theme.php';

$themes = Theme::getAllThemes();
?>
<div class="modalHeader">Themes</div>
<div class="modalBody">
    <div class="col-left mCustomScrollbar">
        <ul class="multiselect" id="themes-list">
            <?php
            foreach ($themes as $theme) {
                $id = $theme->getId();
                echo "<li data-id='$id'>", $theme->getName(), "</li>";
            }
            ?>
        </ul>
    </div>
</div>
<script>
    function bindClicks() {
        $('#themes-list').find('li').click(function () {
            var id = $(this).attr('data-id');

            $.ajax('/assets/modals/theme/ajax/set_theme.php?id=' + id)
                .done(function (data) {
                    if (data == 'success') {
                        alert("Theme applied successfully");
                        setTimeout(function () {
                            realoadCSS();
                        }, 500);

                    } else {
                        error(data);
                    }
                })
                .fail(function (x, xx) {
                    error("Failed to change theme. " + xx);
                })
                .always()
            ;
        });
    }

    function realoadCSS() {
        var queryString = '?reload=' + new Date().getTime();
        $('link[rel="stylesheet"]').each(function () {
            this.href = this.href.replace(/\?.*|$/, queryString);
        });
    }

    bindClicks();
</script>
