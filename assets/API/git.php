<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 10/01/2017
 * Time: 09:56
 */

header('Content-Type: application/json');

require_once '../php-lib/Git.php';

$git = filter_input(INPUT_GET, 'git', FILTER_SANITIZE_STRING);

$return = ["status" => "error"];

switch ($git) {
    case 'change_branch':
        $branch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING);

        if ($branch === false || $branch === null) {
            $branch = 'origin/master';
        }

        //(new Git())->setBranch($branch);

        $return['status'] = 'success';
        break;
    default:
        $return['message'] = 'Invalid git command';
}

echo json_encode($return);