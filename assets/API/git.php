<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 10/01/2017
 * Time: 09:56.
 */
header('Content-Type: application/json');

require_once '../../vendor/autoload.php';

use Lib\Git;

$git = filter_input(INPUT_GET, 'git', FILTER_SANITIZE_STRING);

$return = ['status' => 'error'];

$g = new Git();

switch ($git) {
    case 'checkout':
        $branch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING);

        if ($branch === false || $branch === null) {
            $branch = 'master';
        }

        Git::checkout($branch);

        if ($actual_branch = $g->getCurrentBranch() == $branch) {
            $return['status'] = 'success';
        } else {
            $return['status'] = 'error';
            $return['message'] = "Attempted to change branch, but failed still on '$actual_branch'";
        }

        break;
    case 'delete':
        $branch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING);
        $g->delete($branch);

        $branches = Git::branch();

        if (in_array($branch, $branches)) {
            $return['status'] = 'error';
            $return['message'] = 'Attempted to delete the branch, but it\'s still there';
        } else {
            $return['status'] = 'success';
        }

        break;
    case 'pull':
        $g->pull(null, true);
        $return['status'] = 'success';
        break;
    case 'branch':
        $return['status'] = 'success';
        $return['data'] = Git::branch('-a');
        break;
    case 'current_branch':
        $return['status'] = 'success';
        $return['data'] = $g->getCurrentBranch();
        break;
    case 'up_to_date':
        $return['status'] = 'success';
        $return['data'] = $g->isUpToDate();
        break;
    case 'log':
        $return['status'] = 'success';
        $return['data'] = Git::log(20);
        break;
    default:
        $return['message'] = 'Invalid git command';
}

echo json_encode($return);
