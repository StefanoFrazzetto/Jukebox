<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 25-Nov-16
 * Time: 13:09
 */
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id < 1 && !is_int($id)) {
    echo "Invalid theme id provided '$id'.";
    exit;
}

include '../../../php-lib/Theme.php';

$theme = !Theme::applyThemeById($id);

echo $theme ? "Theme not found." : "success";