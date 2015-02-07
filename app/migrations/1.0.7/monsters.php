<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

class MonstersMigration_107 extends Migration
{

    public function up()
    {
        $this->morphTable(
            'monsters',
            array(
            'columns' => array(
                new Column(
                    'id',
                    array(
                        'type' => Column::TYPE_INTEGER,
                        'unsigned' => true,
                        'notNull' => true,
                        'autoIncrement' => true,
                        'size' => 10,
                        'first' => true
                    )
                ),
                new Column(
                    'name',
                    array(
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 70,
                        'after' => 'id'
                    )
                ),
                new Column(
                    'cr',
                    array(
                        'type' => Column::TYPE_FLOAT,
                        'notNull' => true,
                        'size' => 6,
                        'scale' => 3,
                        'after' => 'name'
                    )
                ),
                new Column(
                    'terrains',
                    array(
                        'type' => Column::TYPE_VARCHAR,
                        'size' => 70,
                        'after' => 'cr'
                    )
                ),
                new Column(
                    'sourceBook',
                    array(
                        'type' => Column::TYPE_VARCHAR,
                        'size' => 70,
                        'after' => 'terrains'
                    )
                ),
                new Column(
                    'page',
                    array(
                        'type' => Column::TYPE_INTEGER,
                        'size' => 10,
                        'after' => 'sourceBook'
                    )
                ),
                new Column(
                    'plural',
                    array(
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 70,
                        'after' => 'page'
                    )
                )
            ),
            'indexes' => array(
                new Index('PRIMARY', array('id'))
            ),
            'options' => array(
                'TABLE_TYPE' => 'BASE TABLE',
                'AUTO_INCREMENT' => '1542',
                'ENGINE' => 'InnoDB',
                'TABLE_COLLATION' => 'utf8_general_ci'
            )
        )
        );
    }
}
