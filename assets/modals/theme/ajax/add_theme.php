<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 27-Nov-16
 * Time: 15:51
 */

include '../../../php-lib/Theme.php';

$json = file_get_contents('php://input');

$obj = json_decode($json);

try {
    $theme = Theme::makeThemeFromObject($obj, false);
    $theme->saveTheme();
    echo "success";
} catch (Exception $e) {
    echo $e->getMessage();
}



