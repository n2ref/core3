<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Expression;


/**
 *
 */
class ModulesSections extends Table {

    protected $table = 'core_modules_sections';


    /**
     * @return ResultSetInterface
     */
    public function getRowsByActive(): ResultSetInterface {

        return $this->fetchAll(function (Select $select) {
            $select
                ->where([
                    'is_active' => true,
                ])
                ->order('seq');
        });
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


    /**
     * Получение количества записей с $module_id
     * @param int $module_id
     * @return int
     */
    public function getCountByModuleId(int $module_id): int {

        $result = $this->select(function (Select $select) use ($module_id) {
            $select
                ->columns(['count' => new Expression('COUNT(1)')])
                ->where(["module_id" => $module_id]);
        });

        return (int)($result->getDataSource()->current()['count'] ?? 0);
    }
}