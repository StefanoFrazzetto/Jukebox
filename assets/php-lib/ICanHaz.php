<?php

namespace Lib;

use Exception;
use InvalidArgumentException;

/**
 * Includes html elements such us CSS and JS, keeping them cached according to the changes and optimising requests
 * User: Vittorio
 * Date: 05/02/2017
 * Time: 14:43.
 */
abstract class ICanHaz
{
    /**
     * YOU CAN HAZ JavaScript!
     *
     * @param $filez array|string the file(s) to include
     * @param $merge bool merges more file in one for reducing the number of requests
     * @param $hard bool writes the file directly inside the tag instead of using a link (src or href)
     */
    public static function js($filez, $merge = false, $hard = false)
    {
        $taggify = function ($src = '', $content = '', $close_tag = true, $open_tag = true) {
            $tag = '';

            if ($open_tag) {
                $tag = '<script';

                if ($src) {
                    $tag .= " src='$src'";
                }

                $tag .= '>';

                if ($content) {
                    $tag .= $content;
                }
            }

            if ($close_tag) {
                $tag .= '</script>';
            }

            return $tag;
        };

        self::file($filez, 'js', $taggify, $merge, $hard);
    }

    /**
     * Evil function that does most of the hard job. Don't mess with it. Seriously. I know where you sleep.
     *
     * @param string $files
     * @param $extension string
     * @param $taggify callable
     * @param bool $merge
     * @param bool $hard
     *
     * @throws Exception if something really weird happens.
     */
    private static function file($files, $extension, $taggify, $merge = false, $hard = false)
    {
        $files = self::normalise($files);

        if (count($files) == 1) {
            $merge = false;
        }

        if ($merge) {
            if ($hard) {
                echo $taggify();
            } else {
                $times = [];
            }

            $merged_content = [];
        }

        // Scary thing that will inject a variable with the remote port
        $requiring_ports = [
            '/var/www/html/assets/js/remote_client.js',
            '/var/www/html/assets/js/Player.js',
        ];

        if (count(array_intersect($files, $requiring_ports)) > 0) {
            $ports = json_encode((new Config())->get('ports'));
            echo $taggify(null, "window.ports = JSON.parse('$ports');", true, true);
        }

        foreach ($files as &$file) {
            if (!file_exists($file)) {
                continue;
            }

            if (!$merge) {
                if (!$hard) {
                    self::versionify($file);

                    echo $taggify(str_replace($_SERVER['DOCUMENT_ROOT'], '', $file));
                } else {
                    $content = file_get_contents($file);

                    echo $taggify('', $content);
                }
            } else {
                $merged_content[] = file_get_contents($file);
                $merged_content[] = "\n/** $file **/\n";

                if (!$hard) {
                    $times[] = filemtime($file);
                }
            }
        }

        if ($merge) {
            if (!isset($merged_content)) {
                throw new Exception("Undefined variable 'merged content'");
            }
            if (!isset($times)) {
                throw new Exception("Undefined variable 'times");
            }
            if ($hard) {
                echo implode($merged_content), $taggify(false, false, true, false);
            } else {
                $cache_file = md5(implode($files)).'.'.$extension;

                $last_time = max($times);

                if (!file_exists(self::getCacheFolder().$cache_file) or filemtime(self::getCacheFolder().$cache_file) != $last_time) {
                    file_put_contents(self::getCacheFolder().$cache_file, $merged_content);
                    touch(self::getCacheFolder().$cache_file, $last_time);
                }

                echo $taggify("/assets/cached_resource/$cache_file?$last_time");
            }
        }
    }

    /**
     * Converts a single string to an array of strings.
     * Return an InvalidArgumentException if the argument is neither a string or an array.
     *
     * @param $files array|string
     *
     * @throws Exception if the file is not found
     *
     * @return array|string
     */
    public static function normalise($files)
    {
        if (!is_array($files)) {
            if (is_string($files)) {
                $files = [$files];
            } else {
                throw new InvalidArgumentException('The file argument should be either a string or an array');
            }
        }

        foreach ($files as &$file) {
            if ($file[0] === '/') {
                $file = $_SERVER['DOCUMENT_ROOT'].$file;
            }

            if (!file_exists($file)) {
                throw new Exception("File $file not found.");
            }

            $file = realpath($file);
        }

        return $files;
    }

    /**
     * Append the time of the last edit to the file name.
     * <p>
     * [/path/to/file/filename.ext]?[lastedit].
     *
     * @param $file string the file to add version
     */
    public static function versionify(&$file)
    {
        $file = $file.'?'.filemtime($file);
    }

    /**
     * Returns the folder where the cached files are stored.
     *
     * @return string path to the cache folder
     */
    public static function getCacheFolder()
    {
        return $_SERVER['DOCUMENT_ROOT'].'/assets/cached_resource/';
    }

    /**
     * YOU CAN HAZ CSS!
     *
     * @param $filez array|string the file(s) to include
     * @param $merge bool merges more file in one for reducing the number of requests
     * @param $hard bool writes the file directly inside the tag instead of using a link (src or href)
     */
    public static function css($filez, $merge = false, $hard = false)
    {
        $taggify = function ($src = '', $content = '', $close_tag = true, $open_tag = true) {
            if ($src) {
                return "<link href='$src' rel='stylesheet' type='text/css'/>";
            }

            $tag = '';

            if ($open_tag) {
                $tag = '<style>';

                if ($content) {
                    $tag .= $content;
                }
            }

            if ($close_tag) {
                $tag .= '</style>';
            }

            return $tag;
        };

        self::file($filez, 'css', $taggify, $merge, $hard);
    }
}
