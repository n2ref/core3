<?php
namespace Core3\Mod\Admin\Tables;

use Core3\Classes\Db\Table;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Modules extends Table {

    protected $table = 'core_modules';


    /**
     * @return ResultSetInterface
     */
    public function getRowsByActiveVisible(): ResultSetInterface {

        $result = $this->fetchAll(function (Select $select) {
            $select
                ->where([
                    'is_active_sw' => 'Y',
                    'is_visible_sw' => 'Y',
                ])
                ->order('seq');
        });

        return $result;
    }
}