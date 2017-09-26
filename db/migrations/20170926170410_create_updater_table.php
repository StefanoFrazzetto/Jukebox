<?php


use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateUpdaterTable extends AbstractMigration
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
        $table = $this->table('updaterlog', ['engine' => 'MyISAM', ['id' => false, 'primary_key' => 'version']]);
        $table->addColumn('version', 'integer', ['limit' => MysqlAdapter::INT_BIG])
            ->addColumn('file_name', 'string')
            ->addColumn('start_time', 'datetime')
            ->addColumn('end_time', 'datetime')
            ->create();
    }
}
