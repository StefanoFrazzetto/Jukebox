<?php


use Phinx\Migration\AbstractMigration;

class CreateDefaultThemes extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     */
    public function change()
    {
        $table = $this->table("themes");

        $themes = [
            [
                "name" => "Dark & Blue",
                "text_color" => "#f5f5f5",
                "background_color" => "#2a2a2a",
                "background_color_highlight" => "#1e1e1e",
                "border_color" => "#48485a",
                "overlays" => "#323232",
                "highlight_color" => "#03a9f4",
                "dark_accents" => 0,
                "read_only" => 1
            ], [
                "name" => "Dark & Green",
                "text_color" => "#f5f5f5",
                "background_color" => "#2a2a2a",
                "background_color_highlight" => "#1e1e1e",
                "border_color" => "#48485a",
                "overlays" => "#323232",
                "highlight_color" => "#1db954",
                "dark_accents" => 0,
                "read_only" => 1
            ], [
                "name" => "Dark & Red",
                "text_color" => "#F0F0F0",
                "background_color" => "#141414",
                "background_color_highlight" => "#050505",
                "border_color" => "#282828",
                "overlays" => "#141414",
                "highlight_color" => "#EB1400",
                "dark_accents" => 1,
                "read_only" => 1
            ], [
                "name" => "Deep Red",
                "text_color" => "#ffffff",
                "background_color" => "#3C1518",
                "background_color_highlight" => "#69140E",
                "border_color" => "#D58936",
                "overlays" => "#4D5061",
                "highlight_color" => "#3C1518",
                "dark_accents" => 0,
                "read_only" => 1
            ], [
                "name" => "Arctic",
                "text_color" => "#303030",
                "background_color" => "#ffffff",
                "background_color_highlight" => "#C4C4C4",
                "border_color" => "#a8a8a8",
                "overlays" => "#C4C4C4",
                "highlight_color" => "#4c77a9",
                "dark_accents" => 0,
                "read_only" => 1
            ], [
                "name" => "Navy",
                "text_color" => "#9ed060",
                "background_color" => "#444444",
                "background_color_highlight" => "#212121",
                "border_color" => "#323232",
                "overlays" => "#676767",
                "highlight_color" => "#009427",
                "dark_accents" => 1,
                "read_only" => 1
            ]
        ];

        $table->insert($themes)->save();
    }
}
