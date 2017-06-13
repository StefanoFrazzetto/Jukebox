<?php

namespace Lib;

use Exception;
use InvalidArgumentException;

/**
 * Class Git.php.
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 *
 * @version 1.2.0
 * @licence GNU GPL v3 - https://www.gnu.org/licences/gpl-3.0.txt
 */
class Git
{
    /** @var string The current repository branch */
    private $_current_branch;

    /** Constructor */
    public function __construct()
    {
        $cmd = "git branch | sed -n '/\\* /s///p'";
        $branch = trim(shell_exec($cmd));
        if (strpos($branch, 'detached') !== false) {
            $this->forcePull();
        }
        $branch = trim(shell_exec($cmd));
        $this->_current_branch = $branch;
    }

    /**
     * Force the pull from the specified branch (default is origin/master).
     *
     * @param string $branch  the branch to force pull
     */
    private function forcePull($branch = 'origin/master')
    {
        exec('git fetch --all');
        exec("git reset --hard $branch");
    }

    /**
     * Return an array containing the result associated with the flag used.
     * There is no default flag, so the default result will contain only the local arrays.
     *
     * @link https://git-scm.com/book/en/v2/Git-Branching-Branch-Management
     *
     * @param string $flag  the flag and/or additional parameters to pass (default returns the local branches)
     *
     * @return array        the array containing the local or remote branches
     */
    public static function branch($flag = '')
    {
        $branches = shell_exec("git branch $flag");
        $branches = explode("\n", trim($branches));

        $branches_parsed = [];

        foreach ($branches as $key => $branch) {
            if (StringUtils::contains($branch, 'detached')) {
                unset($branches[$key]);
            } else {
                preg_match('/\\w+$/', $branch, $matched);

                if (isset($matched[0]) && !in_array($matched[0], $branches_parsed)) {
                    $branches_parsed[] = $matched[0];
                }
            }
        }

        return $branches_parsed;
    }

    /**
     * Change the current branch to $branch_name forcing the checkout.
     *
     * @param string $branch_name       the branch to checkout
     *
     * @throws InvalidArgumentException if no argument is provided
     *
     * @return bool                     true on success, false if the branch does not exist
     */
    public static function checkout($branch_name)
    {
        if (empty($branch_name)) {
            throw new InvalidArgumentException('The branch name cannot be empty');
        }

        $res = shell_exec("git checkout $branch_name --force");
        return !StringUtils::contains($res, 'error');
    }

    /**
     * Return the last commits message for the current branch.
     *
     * @param int $count    the number of commits
     *
     * @return array        the array of commits messages
     */
    public static function log($count = 5)
    {
        $changes = shell_exec("git log -$count --pretty=%B");
        $changes = explode("\n\n", $changes);
        array_pop($changes);

        return $changes;
    }

    /**
     * Deletes a branch.
     * The branch must be fully merged in its upstream branch, or in HEAD if no upstream was set with
     * --track or --set-upstream.
     *
     * @param string $branch_name       the branch to delete
     * @param bool   $force             whether to force the deletion or not
     *
     * @throws InvalidArgumentException if the branch name is empty
     *
     * @return bool                     true if the branch was deleted, false if the branch does not exist
     */
    public function delete($branch_name, $force = false)
    {
        if (empty($branch_name)) {
            throw new InvalidArgumentException('The branch name cannot be empty');
        }

        if ($force) {
            // -D = Shortcut for --delete --force
            $cmd = "git branch -D $branch_name";
        } else {
            $cmd = "git branch -d $branch_name";
        }

        $res = shell_exec($cmd);
        return !StringUtils::contains($res, 'error');
    }

    /**
     * Prune all unreachable objects from the object database.
     *
     * @return bool - true if something was pruned, false if there was nothing to prune
     */
    public function prune()
    {
        $res = shell_exec('git remote prune origin');
        return StringUtils::contains($res, 'Pruning origin');
    }

    /**
     * Pulls the latest changes from the current repository.
     *
     * @param string $branch - the branch where the changes will be pulled from
     * @param bool   $force  - if set to true, forces the pull to the chosen branch
     *
     * @return bool - true if no error occurs, false otherwise
     */
    public function pull($branch, $force = false)
    {
        $branch = is_null($branch) ? $this->_current_branch : $branch;

        if (!$force) {
            $cmd = "git pull origin $branch";
        } else {
            $cmd = "git fetch --all && git reset --hard origin/$branch";
        }
        $res = shell_exec($cmd);

        return StringUtils::contains($res, ['done', 'up-to-date']);
    }

    /**
     * Checks if the current branch.
     *
     * @throws Exception if cmd returns unexpected values
     *
     * @return bool true if is up to date, false if an update is required
     */
    public function isUpToDate()
    {
        $branch = $this->getCurrentBranch();

        $result = trim(shell_exec("[ \$(git rev-parse HEAD) = \$(git ls-remote origin $branch | cut -f1) ] && echo up to date || echo not up to date"));

        switch ($result) {
            case 'up to date':
                return true;
            case 'not up to date':
                return false;
            default:
                throw new Exception("Invalid input returned by git cmd '$result'");
        }
    }

    /**
     * Returns the current branch.
     *
     * @return string - the current branch
     */
    public function getCurrentBranch()
    {
        return $this->_current_branch;
    }
}
