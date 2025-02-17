<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;


/**
 *
 */
class ModulesAvailableVersions extends Table {

    protected $table = 'core_modules_available_versions';


    /**
     * @param array $modules_id
     * @return int
     */
    public function getCountByModulesId(array $modules_id): int {

        $result = $this->select(function (Select $select) use ($modules_id) {
            $select->columns([
                'id'    => new Expression('0'),
                'count' => new Expression('COUNT(1)'),
            ]);
            $select->where([
                'module_id' => $modules_id
            ]);
        });

        return (int)($result->current()->count ?? 0);
    }
}