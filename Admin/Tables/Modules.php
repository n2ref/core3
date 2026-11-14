<?php
namespace Rowan\Admin\Tables;
use Rowan\Classes\Db\Row;
use Rowan\Classes\Db\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;


/**
 * @property int id
 * @property int name
 * @property int vendor
 * @property int title
 * @property int version
 * @property int icon
 * @property int description
 * @property int is_active
 * @property int is_visible
 * @property int is_visible_index
 * @property int install_type
 * @property int seq
 * @property int isset_updates
 * @property int created_at
 * @property int updated_at
 */
class Modules extends Table {

    protected $table = 'core_modules';


    /**
     * Получение модуля по имени
     * @param string $name
     * @return self|null
     */
    public function getRowByName(string $name):? self {

        return $this->fetchRow([ 'name' => $name ]);
    }


    /**
     * @return Collection
     */
    public function getRowsByActive(): Collection {

        $param_name = "active_modules";

        if ($this->hasStaticCache($param_name)) {
            $result = $this->getStaticCache($param_name);

        } else {
            $result = $this->fetchAll(function (Builder $select) {
                $select
                    ->where([
                        'is_active' => true,
                    ])
                    ->orderBy('seq');
            });

            $this->setStaticCache($param_name, $result);
        }

        return $result;
    }


    /**
     * @return int
     */
    public function getCount(): int {

        return $this->newQuery()
            ->count();
    }


    /**
     * @return Collection
     */
    public function getRowsByActiveVisible(): Collection {

        return $this->newQuery()
            ->where('is_active', 1)
            ->where('is_visible', 1)
            ->orderBy('seq')
            ->get();
    }


    /**
     * Получение текущего максимального порядкового номера у модуля
     * @return int
     */
    public function getMaxSeq(): int {

        $max = $this->newQuery()
            ->max('seq')
            ->first();

        return $max?->seq ?: 0;
    }


    /**
     * Проверка повторения названия
     * @param string   $name
     * @param int|null $exclude_id
     * @return bool
     */
    public function isUniqueName(string $name, int|null $exclude_id = null): bool {

        $query = $this->newQuery()
            ->where('name', $name);

        if ($exclude_id) {
            $query->where('id', '!=', $exclude_id);
        }

        return $query->count() === 0;
    }
}