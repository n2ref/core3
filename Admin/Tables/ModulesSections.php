<?php
namespace Rowan\Admin\Tables;
use Rowan\Classes\Db\Table;
use Illuminate\Database\Eloquent\Collection;


/**
 * @property int id
 * @property int module_id
 * @property int name
 * @property int title
 * @property int is_active
 * @property int seq
 * @property int created_at
 * @property int updated_at
 */
class ModulesSections extends Table {

    protected $table = 'core_modules_sections';


    /**
     * @return Collection
     */
    public function getRowsByActive(): Collection {

        return $this->newQuery()
            ->where('is_active', true)
            ->orderBy('seq')
            ->get();
    }


    /**
     * @param int $module_id
     * @return Collection
     */
    public function getRowsByModuleId(int $module_id): Collection {

        return $this->newQuery()
            ->where('module_id', $module_id)
            ->orderBy('seq')
            ->get();
    }


    /**
     * Получение количества записей с $module_id
     * @param int $module_id
     * @return int
     */
    public function getCountByModuleId(int $module_id): int {

        return $this->newQuery()
            ->where('module_id', $module_id)
            ->count();
    }
}