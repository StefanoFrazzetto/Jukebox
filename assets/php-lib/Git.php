<?php

/**
 * Class Git.php
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 * @version 1.2.0
 * @licence GNU AGPL v3 - https://www.gnu.org/licences/agpl-3.0.txt
 */
class Git
{
    /** @var string The current repository branch */
    private $_current_branch;

    /** Constructor */
    function __construct()
    {
        $cmd = "git branch | sed -n '/\\* /s///p'";
        $branch = trim(shell_exec($cmd));
        if (strpos($branch, "detached") !== false) {
            $this->forcePull();
        }
        $branch = trim(shell_exec($cmd));
        $this->_current_branch = $branch;
    }

    /**
     * Forces the pull from the specified branch (default is origin/master).
     *
     * @param string $branch - the branch to force pull
     */
    private function forcePull($branch = "origin/master")
    {
        exec("git fetch --all");
        exec("git reset --hard $branch");
    }

    /**
     * Returns an array containing the result associated with the flag used.
     * There is no default flag, so the default result will contain only the local arrays.
     * @link https://git-scm.com/book/en/v2/Git-Branching-Branch-Management
     *
     * @param string $flag - the flag and/or additional parameters to pass (default returns the local branches)
     * @return array - the array containing the local or remote branches
     */
    public static function branch($flag = "")
    {
        $branches = shell_exec("git branch $flag");
        $branches = explode("\n", trim($branches));

        $branches_parsed = [];

        foreach ($branches as $key => $branch) {
            if (strpos($branch, "detached") !== false) {
                unset($branches[$key]);
            } else {
                preg_match("/\\w+$/", $branch, $matched);

                if (isset($matched[0]) && !in_array($matched[0], $branches_parsed)) {
                    $branches_parsed[] = $matched[0];
                }

                echo $matched[0];
            }
        }

        return $branches_parsed;
    }

    /**
     * Changes the current branch to $branch_name forcing the checkout.
     *
     * @param string $branch_name - the branch to checkout
     * @return boolean - true on success, false otherwise
     * @throws InvalidArgumentException if no argument is provided
     */
    public static function checkout($branch_name)
    {
        $res = shell_exec("git checkout $branch_name --force");
        if (strpos($res, "error") !== false && isset($branch_name)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Returns the last commits message for the current branch.
     *
     * @param int $count - the number of commits
     * @return array - the array of commits messages
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
     * @param string $branch_name - the branch to delete
     * @param bool $force - set whether to force the deletion or not
     * @return bool - true if the process was successful, false if the branch does not exist
     */
    public function delete($branch_name, $force = false)
    {
        $cmd = "git branch -d $branch_name";

        if ($force) {
            // -D = Shortcut for --delete --force
            $cmd = "git branch -D $branch_name";
        }

        $res = shell_exec($cmd);
        if (strpos($res, "error") !== false && isset($branch_name)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Prune all unreachable objects from the object database.
     *
     * @return bool - true if something was pruned, false if there was nothing to prune
     */
    public function prune()
    {
        $res = shell_exec("git remote prune origin");
        if (strpos($res, "Pruning origin") !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Pulls the latest changes from the current repository.
     *
     * @param string $branch - the branch where the changes will be pulled from
     * @param bool $force - if set to true, forces the pull to the chosen branch
     * @return bool - true if no error occurs, false otherwise
     */
    public function pull($branch = "", $force = false)
    {
        $branch = $branch == "" ? $this->_current_branch : $branch;

        if (!$force) {
            $cmd = "git pull origin $branch";
        } else {
            $cmd = "git fetch --all && git reset --hard origin/$branch";
        }

        $res = shell_exec($cmd);
        if (strpos($res, "done") !== false || strpos($res, "up-to-date") !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the current branch
     *
     * @return bool TRUE if is up to date and FALSE if an update is required
     * @throws Exception if cmd returns unexpected values
     */
    public function isUpToDate()
    {
        $branch = $this->getCurrentBranch();

        $result = trim(shell_exec("[ \$(git rev-parse HEAD) = \$(git ls-remote origin $branch | cut -f1) ] && echo up to date || echo not up to date"));

        switch ($result) {
            case "up to date":
                return true;
            case "not up to date":
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