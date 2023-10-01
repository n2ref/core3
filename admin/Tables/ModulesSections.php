<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;


/**
 *
 */
class ModulesSections extends Table {

    protected $table = 'core_modules_sections';


    /**
     * @return ResultSetInterface
     */
    public function getRowsByActive(): ResultSetInterface {

        $result = $this->fetchAll(function (Select $select) {
            $select
                ->where([
                    'is_active_sw' => 'Y',
                ])
                ->order('seq');
        });

        return $result;
    }


    /**
     * @param int $module_id
     * @return ResultSetInterface
     */
    public function getRowsByModuleId(int $module_id): ResultSetInterface {

        $result = $this->select(function (Select $select) use ($module_id) {
            $select->where(["module_id" => $module_id])
                ->order('seq');
        });

        return $result;
    }
}