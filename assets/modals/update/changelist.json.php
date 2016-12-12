<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 12/12/2016
 * Time: 16:15
 */
header('Content-Type: application/json');

$count = filter_input(INPUT_GET, "n", FILTER_VALIDATE_INT);

if (!$count) {
    $count = 20;
}

$count *= -1;

$changes = shell_exec("git log $count --pretty=%B");

$changes = explode("\n\n", $changes);

array_pop($changes);

echo json_encode($changes);