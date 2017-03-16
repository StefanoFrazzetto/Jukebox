<?php

namespace Lib;

use Exception;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 23-Nov-16
 * Time: 19:05.
 */
require __DIR__.'/../php-lib/Database.php';

class Theme implements JsonSerializable
{
    const THEME_FILE = '/var/www/html/assets/scss/_colors_theme.scss';

    private $name = 'Theme';
    /**
     * @var string
     */
    private $text_color;
    private $background_color;
    private $background_color_highlight;
    private $border_color;
    private $overlays;
    private $highlight_color;
    /**
     * @var bool
     */
    private $dark_accents;
    /**
     * @var bool
     */
    private $isStored;
    /**
     * @var int
     */
    private $id;
    /**
     * @var bool
     */
    private $isReadOnly = false;

    /**
     * Theme constructor.
     *
     * @param $name string name of the theme
     * @param $text_color string
     * @param $background_color string
     * @param $background_color_highlight string
     * @param $border_color string
     * @param $overlays string
     * @param $highlight_color string
     * @param $dark_accents bool
     */
    public function __construct($name, $text_color, $background_color_highlight, $background_color, $border_color, $overlays, $highlight_color, $dark_accents)
    {
        // Am I seriously validating the parameters? I am getting to serious.
        $args = func_get_args();

        $args = array_splice($args, 1, count($args) - 2);

        foreach ($args as $color) {
            if (!$this->validate_color_string($color)) {
                throw new InvalidArgumentException("$color is not a valid color");
            }
        }

        if (!is_bool($dark_accents)) {
            throw new InvalidArgumentException("$dark_accents is not a valid boolean for the dark accents");
        }
        $this->name = $name;
        $this->text_color = $text_color;
        $this->background_color = $background_color;
        $this->background_color_highlight = $background_color_highlight;
        $this->border_color = $border_color;
        $this->overlays = $overlays;
        $this->highlight_color = $highlight_color;
        $this->dark_accents = $dark_accents;
    }

    /**
     * Validates a color string according to the 6 hex digit with heading '#' symbol.
     *
     * @param $color string representation of a color
     *
     * @return bool {@code true} if a valid color | {@code false} if not
     */
    private function validate_color_string($color)
    {
        return preg_match('/^#[a-f0-9]{6}$/i', $color);
    }

    /**
     * Returns all the themes stored in the database.
     *
     * @return Theme[] or null
     */
    public static function getAllThemes()
    {
        $db = new Database();
        $results = $db->select('*', 'themes');

        if ($results == null) {
            return;
        }

        /** @var Theme[] $themes */
        $themes = [];

        foreach ($results as $result) {
            $themes[] = self::makeThemeFromObject($result);
        }

        return $themes;
//        return [
//            new Theme("Dark & Blue", '#f5f5f5', '#1e1e1e', '#2a2a2a', '#48485a', '#323232', '#03a9f4', false),
//            new Theme("Dark & Green", '#f5f5f5', '#1e1e1e', '#2a2a2a', '#48485a', '#323232', '#1db954', false),
//            new Theme("Dark & Red", '#F0F0F0', '#050505', '#141414', '#282828', '#141414', '#EB1400', true),
//            new Theme("Deep Red", '#ffffff', '#69140E', '#3C1518', '#D58936', '#4D5061', '#3C1518', false),
//            new Theme("Arctic", '#303030', '#C4C4C4', '#ffffff', '#a8a8a8', '#C4C4C4', '#4c77a9', false)
//        ];
    }

    public static function makeThemeFromObject($db_object, $stored = true)
    {

        /** @noinspection PhpUndefinedFieldInspection */
        $theme = new self($db_object->name, $db_object->text_color, $db_object->background_color_highlight, $db_object->background_color, $db_object->border_color, $db_object->overlays, $db_object->highlight_color, boolval($db_object->dark_accents));

        $theme->isReadOnly = boolval($db_object->read_only);

        $theme->isStored = $stored;

        if ($stored) {
            if (!isset($db_object->id)) {
                throw new Exception("The theme was supposed to be stored, but it's lacking of id");
            }
            $theme->id = $db_object->id;
        }

        return $theme;
    }

    /**
     * Apply a theme, given the id. Will return false if the theme is not found.
     *
     * @param $id int
     *
     * @return bool
     */
    public static function applyThemeById($id)
    {
        $theme = self::getThemeById($id);

        if ($theme == null) {
            return false;
        }

        $theme->applyTheme();

        return true;
    }

    /**
     * Loads a theme from the database.
     *
     * @param $id int the id of the theme
     *
     * @return Theme or null
     */
    public static function getThemeById($id)
    {
        $db = new Database();

        $result = $db->select('*', 'themes', "WHERE id = $id")[0];

        if ($result == null) {
            return;
        }

        return self::makeThemeFromObject($result);
    }

    /**
     * Applies the theme to the scss.
     */
    public function applyTheme()
    {
        $dark_accents = $this->dark_accents ? 'true' : 'false';

        $theme = "        //$this->id
        \$text_color: $this->text_color;
        \$background_color: $this->background_color;
        \$background_color_highlight: $this->background_color_highlight;
        \$border_color: $this->border_color;
        \$overlays: $this->overlays;
        \$highlight_color: $this->highlight_color;
        \$dark_accents: $dark_accents;";

        file_put_contents(self::THEME_FILE, $theme);

        $script = __DIR__.'/../nodejs/sass.js';

        exec("node $script");

        ob_start();
        ICanHaz::css(['/assets/css/main.css', '/assets/css/font-awesome.min.css', '/assets/css/jquery.mCustomScrollbar.min.css'], true);
        ob_end_clean();
    }

    /**
     * Returns the currently applied theme object. Even if it shouldn't happen, will return null in case of failure.
     *
     * @return Theme | null on failure
     */
    public static function getAppliedTheme()
    {
        if ($id = self::getAppliedId()) {
            return self::getThemeById($id);
        } else {
            return;
        }
    }

    /**
     * @return int
     */
    private static function getAppliedId()
    {
        $line = fgets(fopen(self::THEME_FILE, 'r'));

        $line = str_replace('//', '', $line);

        return intval($line);
    }

    /**
     * Saves the theme to the database.
     *
     * @return bool true on success
     */
    public function saveTheme()
    {
        $db = new Database();

        if ($this->isStored) {
            return $this->updateToDb($db);
        } else {
            if (!$this->insertToDb($db)) {
                return false;
            }
            $this->id = $db->getLastInsertedID();
            $this->isStored = true;

            return true;
        }
    }

    /**
     * @param $db Database
     *
     * @return bool true on success
     */
    private function updateToDb($db)
    {
        return $db->update('themes', [
            'name'                       => $this->name,
            'text_color'                 => $this->text_color,
            'background_color'           => $this->background_color,
            'background_color_highlight' => $this->background_color_highlight,
            'border_color'               => $this->border_color,
            'overlays'                   => $this->overlays,
            'highlight_color'            => $this->highlight_color,
            'dark_accents'               => $this->dark_accents,
        ], " WHERE id = $this->id");
    }

    /**
     * @param $db Database
     *
     * @return bool true on success
     */
    private function insertToDb($db)
    {
        return $db->insert('themes',
            [
                'name'                       => $this->name,
                'text_color'                 => $this->text_color,
                'background_color'           => $this->background_color,
                'background_color_highlight' => $this->background_color_highlight,
                'border_color'               => $this->border_color,
                'overlays'                   => $this->overlays,
                'highlight_color'            => $this->highlight_color,
                'dark_accents'               => $this->dark_accents,
            ]
        );
    }

    public function jsonSerialize()
    {
        return [
            'id'                         => $this->id,
            'name'                       => $this->name,
            'text_color'                 => $this->text_color,
            'background_color'           => $this->background_color,
            'background_color_highlight' => $this->background_color_highlight,
            'border_color'               => $this->border_color,
            'overlays'                   => $this->overlays,
            'highlight_color'            => $this->highlight_color,
            'dark_accents'               => $this->dark_accents,
            'read_only'                  => $this->isReadOnly, ];
    }

    //<editor-fold desc="Getters and Setters" defaultstate="collapsed">

    /**
     * @return string
     */
    public function getTextColor()
    {
        return $this->text_color;
    }

    /**
     * @param string $text_color
     */
    public function setTextColor($text_color)
    {
        $this->text_color = $text_color;
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->background_color;
    }

    /**
     * @param string $background_color
     */
    public function setBackgroundColor($background_color)
    {
        $this->background_color = $background_color;
    }

    /**
     * @return string
     */
    public function getBackgroundColorHighlight()
    {
        return $this->background_color_highlight;
    }

    /**
     * @param string $background_color_highlight
     */
    public function setBackgroundColorHighlight($background_color_highlight)
    {
        $this->background_color_highlight = $background_color_highlight;
    }

    /**
     * @return string
     */
    public function getBorderColor()
    {
        return $this->border_color;
    }

    /**
     * @param string $border_color
     */
    public function setBorderColor($border_color)
    {
        $this->border_color = $border_color;
    }

    /**
     * @return string
     */
    public function getOverlays()
    {
        return $this->overlays;
    }

    /**
     * @param string $overlays
     */
    public function setOverlays($overlays)
    {
        $this->overlays = $overlays;
    }

    /**
     * @return string
     */
    public function getHighlightColor()
    {
        return $this->highlight_color;
    }

    /**
     * @param string $highlight_color
     */
    public function setHighlightColor($highlight_color)
    {
        $this->highlight_color = $highlight_color;
    }

    /**
     * @return bool
     */
    public function isDarkAccents()
    {
        return $this->dark_accents;
    }

    /**
     * @param bool $dark_accents
     */
    public function setDarkAccents($dark_accents)
    {
        $this->dark_accents = $dark_accents;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isIsReadOnly()
    {
        return $this->isReadOnly;
    }

    //</editor-fold>
}
