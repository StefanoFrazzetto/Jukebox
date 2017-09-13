<?php


use Phinx\Migration\AbstractMigration;

class CreateRadioStationsTable extends AbstractMigration
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
        $table = $this->table('radio_stations', ['engine' => 'MyISAM', 'signed' => false]);
        $table->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('url', 'text')
            ->addColumn('cover_cached_token', 'integer', ['limit' => 11, 'default' => 0])
            ->create();
    }
}
