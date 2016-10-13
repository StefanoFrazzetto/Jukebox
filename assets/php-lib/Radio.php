<?php

include_once __DIR__ . '/../php/Database.php';

class Radio
{
    /**
     * @var string
     */
    const radio_table = 'radio_stations';
    const relative_path = '/jukebox/radio-covers/';
    const covers_path = '/var/www/html/jukebox/radio-covers/';
    private $name, $url, $created = false, $id;

    function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * Loads a radio from the Database and returns a Radio object
     * @param int $id
     * @return Radio the found radio
     * @return null if not found
     */
    static function loadRadio($id)
    {
        $database = new Database();

        $gne = $database->select('*', Radio::radio_table, "WHERE id = $id LIMIT 1");

        if ($gne == null) {
            return null;
        }

        return self::makeRadioFromDatabaseArray($gne[0]);
    }

    /**
     * @param $radio_database array the object returned from a DB query
     * @return Radio a radio object
     * @return null if fails
     */
    private static function makeRadioFromDatabaseArray($radio_database)
    {
        try {
            $radio = new Radio($radio_database->name, $radio_database->url);

            $radio->created = true;
            $radio->id = $radio_database->id;

            return $radio;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return Radio[]
     * @return null if fails
     */
    public static function getAllRadios()
    {
        $database = new Database();

        $gne = $database->select('*', Radio::radio_table);

        /** @var Radio[] $radios */
        $radios = [];

        if ($gne == null) {
            return null;
        }

        foreach ($gne as $radaisodai) {
            $radios[] = self::makeRadioFromDatabaseArray($radaisodai);
        }

        return $radios;
    }

    function delete()
    {
        self::deleteRadio($this->id);
    }

    /**
     * Deletes a radio given the id
     * @param int $id
     * @return bool true if successful
     */
    static function deleteRadio($id)
    {
        $database = new Database();

        rmdir(self::covers_path . $id);

        return $database->delete(Radio::radio_table, "`id` = $id");
    }

    /**
     * Crates or updates a radio to the database
     *
     * @return bool
     */
    function save()
    {
        $database = new Database();

        if ($this->created) {

        } else {
            $status = $database->insert(Radio::radio_table, ["name" => $this->name, "url" => $this->url]);

            if (!$status) {
                return false;
            }

            $this->id = $database->getLastInsertedID();
            $this->created = true;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function addCover($url)
    {
        require __DIR__ . "/Cover.php";

        $cover = new Cover($url);

        $where = self::covers_path . $this->id;

        mkdir($where);

        $cover->saveAlbumImagesToFolder($where);

    }

    public function getParsedAddressed()
    {
        $parsed_address = parse_url($this->url);

        if (!isset($parsed_address['port'])) {
            $parsed_address['port'] = 80;
        }

        $parsed_address['cover'] = $this->getCover();

        return $parsed_address;
    }

    /**
     * @return string cover location
     */
    public function getCover()
    {
        $where = self::covers_path . $this->id . "/cover.jpg";

        if (file_exists($where)) {
            return self::relative_path . $this->id . "/cover.jpg";
        } else {
            return "/assets/img/album-placeholder.png";
        }


    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}