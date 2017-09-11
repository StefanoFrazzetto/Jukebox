<?php


use Phinx\Migration\AbstractMigration;

class CreateThemesTable extends AbstractMigration
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
        $table = $this->table('themes', ['engine' => 'MyISAM']);
        $table->addColumn('name', 'string', array('limit' => 25))
            ->addColumn('text_color', 'char', array('limit' => 7))
            ->addColumn('background_color', 'char', array('limit' => 7))
            ->addColumn('background_color_highlight', 'char', array('limit' => 7))
            ->addColumn('border_color', 'char', array('limit' => 7))
            ->addColumn('overlays', 'char', array('limit' => 7))
            ->addColumn('highlight_color', 'char', array('limit' => 7))
            ->addColumn('dark_accents', 'boolean')
            ->addColumn('read_only', 'boolean', array('default' => 0))
            ->create();
    }
}
