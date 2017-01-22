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
    case 'checkout':
        $branch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING);

        if ($branch === false || $branch === null) {
            $branch = 'origin/master';
        }

        Git::checkout($branch);

        if ((new Git())->getCurrentBranch() == $branch)
            $return['status'] = 'success';
        else
            $return['status'] = 'error';
        break;
    case 'pull':

        (new Git())->pull(null, true);

        $return['status'] = 'success';
        break;
    case 'branch':
        $return['status'] = 'success';
        $return['data'] = Git::branch();
        break;
    case 'current_branch':
        $return['status'] = 'success';
        $return['data'] = (new Git())->getCurrentBranch();
        break;
    case 'up_to_date':
        $return['status'] = 'success';
        $return['data'] = (new Git())->isUpToDate();
        break;
    case 'log':
        $return['status'] = 'success';
        $return['data'] = Git::log(20);
        break;
    default:
        $return['message'] = 'Invalid git command';
}

echo json_encode($return);