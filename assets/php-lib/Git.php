<?php

/**
 * Class Git.php
 *
 * @author Stefano Frazzetto - https://github.com/StefanoFrazzetto
 * @version 1.0.0
 * @licence GNU AGPL v3 - https://www.gnu.org/licences/agpl-3.0.txt
 */
class Git
{
    /** @var string The current repository branch */
    private $_current_branch;

    /** Constructor */
    function __construct()
    {
        $branch = shell_exec("git branch");
        $this->_current_branch = preg_replace("/[^A-Za-z0-9 ]/", '', $branch);
    }

    /**
     * Returns the current branch.
     *
     * @return string - the current branch.
     */
    public function getCurrentBranch()
    {
        return $this->_current_branch;
    }

    /**
     * Returns the last commits message for the current branch.
     *
     * @param int $count - the number of commits.
     * @return array - the array of commits messages
     */
    public static function getChanges($count = 5)
    {
        $changes = shell_exec("git log -$count --pretty=%B");
        $changes = explode("\n\n", $changes);
        array_pop($changes);

        return $changes;
    }

    /**
     * Pulls the latest changes from the current repository.
     *
     * @param string $branch - the branch where the changes will be pulled from.
     * @return bool - true if no error occurs, false otherwise.
     */
    public function pull($branch = "")
    {
        $branch = $branch == "" ? $this->_current_branch : $branch;

        $res = shell_exec("git pull origin $branch");
        if (strpos($res, "done") !== false || strpos($res, "up-to-date") !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Pushes the changes to the branch.
     *
     * @param string $branch - the branch where the changes will be pushed.
     * @return bool - true on success, false otherwise.
     */
    private function push($branch = "")
    {
        if ($branch != "") {
            //return shell_exec("git push $branch");
        } else {
            return false;
        }
    }

}