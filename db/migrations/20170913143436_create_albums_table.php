<?php


use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateAlbumsTable extends AbstractMigration
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
        $table = $this->table('albums', ['engine' => 'MyISAM', 'signed' => false]);
        $table->addColumn('title', 'string', ['limit' => 255, 'collation' => 'utf8_unicode_ci'])
            ->addColumn('last_played', 'integer', ['limit' => MysqlAdapter::INT_MEDIUM, 'signed' => false, 'default' => null])
            ->addColumn('hits', 'integer', ['limit' => 11, 'signed' => false, 'default' => 0])
            ->addColumn('added_on', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('genre', 'integer', ['limit' => 10, 'signed' => false])
            ->create();
    }
}
