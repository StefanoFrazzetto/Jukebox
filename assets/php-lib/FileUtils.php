<?php

require_once __DIR__ . '/StringUtils.php';
require_once __DIR__ . '/OS.php';

/**
 * FileUtils provide static methods to access, create, modify, and delete
 * files and directories.
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 */
abstract class FileUtils
{
    /**
     * Create a file.
     *
     * @param string $file_path The file path
     * @param mixed $content The content to be written
     * @param bool $append If set to true, appends the content rather than deleting the
     * previous file content
     * @param bool $json If set to true, the content is encoded to json format
     * @return bool true if the operation succeeds, false otherwise.
     */
    public static function createFile($file_path, $content = "", $append = false, $json = false)
    {
        if (empty($file_path)) {
            throw new InvalidArgumentException('The file path cannot be empty.');
        }

        if ($json) {
            $content = json_encode($content);
        }

        if ($append) {
            $res = file_put_contents($file_path, $content, FILE_APPEND);
        } else {
            $res = file_put_contents($file_path, $content);
        }

        return $res !== false;
    }

    /**
     * Return true if a directory is empty.
     *
     * @param string $dir The directory to check
     * @return bool true if the directory is empty, false otherwise.
     */
    public static function isDirEmpty($dir)
    {
        if (count(glob($dir . "/*", GLOB_NOSORT)) === 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Removes the content of the directory recursively.
     *
     * @param string $dir_path the directory to empty
     * @return bool true if it was possible to empty the directory, false
     * otherwise.
     */
    public static function emptyDirectory($dir_path)
    {
        $hidden_files = OS::execute("find $dir_path -name ._\\* -print0 | xargs -0 rm -f");
        $files_and_directories = OS::execute("rm -rf $dir_path/*");

        $res = StringUtils::contains($hidden_files, 'error') && StringUtils::contains($files_and_directories, 'error');

        return $res;
    }

    /**
     * Move (rename) file(s).
     *
     * @param string $source The source file or directory to move.
     * @param string $destination The destination file or directory.
     * @param bool $force If set to true, do not prompt before overriding.
     * The default value is false.
     * @return bool True if the operation is successful, false otherwise.
     */
    public static function move($source, $destination, $force = false)
    {
        $cmd = "mv ";

        // Use the force.
        if ($force) {
            $cmd .= "-f";
        }

        $args = "$source $destination";

        return OS::executeWithResult($cmd, $args);
    }

    /**
     * Copy SOURCE to DEST, or multiple SOURCE(s) to DIRECTORY.
     *
     * @param string $source The source file or directory.
     * @param string $destination The destination file or directory.
     * @param bool $force If an existing destination file cannot be opened,
     * remove it and try again.
     * @return bool True if the operation is successful, false otherwise.
     */
    public static function copy($source, $destination, $force = false)
    {
        $cmd = "cp ";

        if ($force) {
            $cmd .= "-f";
        }

        $args = "$source $destination";

        return OS::executeWithResult($cmd, $args);
    }

    /**
     * Remove files that match an extension.
     *
     * @param string $path The directory where the file(s) are located.
     * @param string $ext The file(s) extension.
     * @param bool $recursive If set to true, remove the files recursively.
     * @return bool true if the operation was successful, false otherwise.
     */
    public static function removeByExtension($path, $ext, $recursive = false)
    {
        $path = "$path/*.$ext";
        return self::remove($path, $recursive);
    }

    /**
     * Remove files or directories.
     *
     * @param string $path The file or directory path.
     * @param bool $recursive If set to true, remove directories and their contents
     * recursively.
     * @return bool true if the operation was successful, false otherwise.
     */
    public static function remove($path, $recursive = false)
    {
        $cmd = "rm -f ";

        if ($recursive) {
            $cmd .= "-r";
        }

        return OS::executeWithResult($cmd, $path);
    }

    public static function getDirectories($path)
    {
        if (empty($path)) {
            throw new InvalidArgumentException('You must pass a valid path.');
        }

        if (!file_exists($path)) {
            return null;
        }

        $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        $dirs = [];

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $dirs[] = $item->getFilename();
            }
        }

        return $dirs;
    }

    /**
     * Get a JSON file as an associative array.
     *
     * @param string $file_path The JSON file path.
     * @return mixed|null The decoded content of the file is returned if
     * the operation succeeds, otherwise null is returned.
     */
    public static function getJson($file_path)
    {
        $content = file_get_contents($file_path);

        if ($content === false) {
            return null;
        }

        return json_decode($content, true);
    }

    /**
     * Count files in a directory.
     * If a file extension is passed as second parameter, only the files
     * with that extension will be counted.
     *
     * @param string $directory The directory where the files should be counted.
     * @param string $ext The optional extension of the files.
     * @return int The number of files.
     */
    public static function countFiles($directory, $ext = "")
    {
        $directory = rtrim($directory, '/');

        if (empty($ext)) {
            $ext = "*";
        }

        return count(glob($directory . "/*.$ext", GLOB_NOSORT));
    }

    /**
     * Return the size in KB of files and directories.
     *
     * @param string $path The path to the file or directory.
     * @return int $size The size of the file/directory in KB.
     */
    public static function getSize($path)
    {
        $cmd = "du -sc $path | grep total | grep -o '[0-9]*'";
        $size = OS::execute($cmd);

        return intval($size);
    }

    /**
     * Return the track length in seconds.
     *
     * If the track does not exists, 0 is returned.
     *
     * @param string $track_path The track path.
     * @return int The track length in seconds.
     */
    public static function getTrackLength($track_path)
    {
        $cmd = "mp3info -p '%S' $track_path | grep -o '[0-9]*'";
        return intval(OS::execute($cmd));
    }

    /**
     * Remove all files in the directory that are older than the
     * specified time in seconds.
     *
     * @param string $dir The directory containing the files to remove.
     * @param int $seconds The time in seconds.
     */
    public static function deleteFilesOlderThan($dir, $seconds)
    {
        foreach (glob($dir . "/*") as $file) {
            if (filemtime($file) < time() - $seconds && !is_dir($file)) {
                unlink($file);
            }
        }
    }
}