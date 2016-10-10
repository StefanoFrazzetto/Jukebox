<?php

include_once __DIR__ . '/../php/Database.php';

class Radio
{
    /**
     * @var string
     */
    const radio_table = 'radio_stations';
    private $name, $url, $created = false, $id;

    function __construct($name, $url)
    {
        $this->name = $name;
        $this->url = $name;
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

        $radio_database = $gne[0];

        $radio = new Radio($radio_database->name, $radio_database->url);

        $radio->created = true;
        $radio->id = $radio_database->id;

        return $radio;
    }

    /**
     * Deletes a radio given the id
     * @param int $id
     * @return bool true if successful
     */
    static function deleteRadio($id)
    {
        $database = new Database();

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

}