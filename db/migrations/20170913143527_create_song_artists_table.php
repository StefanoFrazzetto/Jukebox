<?php


use Phinx\Migration\AbstractMigration;

class CreateSongArtistsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('song_artists', ['engine' => 'MyISAM', ['id' => false, 'primary_key' => ['user_id', 'follower_id']]]);
        $table->addColumn('song_id', 'integer', ['limit' => 10, 'signed' => false])
            ->addColumn('artist_id', 'integer', ['limit' => 10, 'signed' => false])
            ->create();
    }
}
