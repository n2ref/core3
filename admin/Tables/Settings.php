<?php
namespace Core3\Mod\Admin\Tables;

use Core3\Classes\Db\Table;
use Laminas\Db\ResultSet\ResultSetInterface;


/**
 *
 */
class Settings extends Table {

	protected $table = 'core_settings';


    /**
     * @return ResultSetInterface
     */
    public function getRowsSystem(): ResultSetInterface {

        return $this->fetchAll(['data_group' => 'system']);
    }


    /**
     * @return ResultSetInterface
     */
    public function getExtra(): ResultSetInterface {

        return $this->fetchAll(['data_group' => 'extra']);
    }
}