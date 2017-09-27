<?php

// Handles git actions

header('Content-Type: application/json');

require_once '../../vendor/autoload.php';


use Lib\Git;

$action = filter_input(INPUT_GET, 'git', FILTER_SANITIZE_STRING);
$branch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING);
$return = ['success' => true, 'message' => ''];

switch ($action) {
    case 'checkout':
        if (empty($branch)) {
            $branch = 'master';
        }

        Git::checkout($branch);
        if ($actual_branch = $git->getCurrentBranch() != $branch) {
            $return['success'] = false;
            $return['message'] = "Attempted to change branch, but failed still on '$actual_branch'";
        }

        break;

    case 'delete':
        $branch = filter_input(INPUT_GET, 'branch', FILTER_SANITIZE_STRING);
        $git = new Git();
        $git->delete($branch);

        $branches = Git::branch();

        if (in_array($branch, $branches)) {
            $return['success'] = false;
            $return['message'] = 'Attempted to delete the branch, but it\'s still there';
        }

        break;


    case 'pull':
        $git = new Git();
        if (!$git->pull(null, true)) {
            $return['success'] = false;
            $return['message'] = 'Failed to retrieve the updates.';
            break;
        }
        break;

    case 'up_to_date':
        $git = new Git();
        $return['upToDate'] = $git->isUpToDate();
        break;

    case 'log':
        $return['logs'] = Git::log(20);
        break;

    default:
        $return = ['success' => false, 'message' => 'Invalid git command'];
}

echo json_encode($return);