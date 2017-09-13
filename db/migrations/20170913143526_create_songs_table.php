<?php


use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateSongsTable extends AbstractMigration
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
        $table = $this->table('songs', ['engine' => 'MyISAM', 'signed' => false]);
        $table->addColumn('album_id', 'integer', ['limit' => 10, 'signed' => false])
            ->addColumn('cd', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false, 'default' => 1])
            ->addColumn('track_no', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false])
            ->addColumn('length', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'signed' => false])
            ->addColumn('title', 'string', ['limit' => 255, 'collation' => 'utf8_unicode_ci'])
            ->addColumn('url', 'string', ['limit' => 255, 'collation' => 'utf8_unicode_ci'])
            ->create();
    }
}
