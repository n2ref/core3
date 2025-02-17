<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Row;
use Core3\Classes\Db\Table;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Modules extends Table {

    protected $table = 'core_modules';


    /**
     * Получение модуля по имени
     * @param string $name
     * @return Row|null
     */
    public function getRowByName(string $name):? Row {

        $results = $this->select(function (Select $select) use ($name) {
            $select->where([ 'name' => $name ]);
        });

        return $results->current();
    }


    /**
     * @return ResultSetInterface
     */
    public function getRowsByActive(): ResultSetInterface {

        $param_name = "active_modules";

        if ($this->hasStaticCache($param_name)) {
            $result = $this->getStaticCache($param_name);

        } else {
            $result = $this->fetchAll(function (Select $select) {
                $select
                    ->where([
                        'is_active' => true,
                    ])
                    ->order('seq');
            });

            $this->setStaticCache($param_name, $result);
        }

        return $result;
    }


    /**
     * @return int
     */
    public function getCount(): int {

        $result = $this->select(function (Select $select) {
            $select->columns([
                'id'    => new Expression('1'),
                'count' => new Expression('COUNT(1)'),
            ]);
        });

        return (int)($result->current()->count ?? 0);
    }


    /**
     * @return ResultSetInterface
     */
    public function getRowsByActiveVisible(): ResultSetInterface {

        return $this->fetchAll(function (Select $select) {
            $select
                ->where([
                    'is_active'  => true,
                    'is_visible' => true,
                ])
                ->order('seq');
        });
    }


    /**
     * Получение текущего максимального порядкового номера у модуля
     * @return int
     */
    public function getMaxSeq(): int {

        $result = $this->select(function (Select $select) {
            $select->columns([
                'id'  => 'id',
                'seq' => new Expression('MAX(seq)'),
            ]);
        });

        return (int)($result->current()->seq ?? 0);
    }


    /**
     * Проверка повторения названия
     * @param string   $name
     * @param int|null $exclude_id
     * @return bool
     */
    public function isUniqueName(string $name, int $exclude_id = null): bool {

        $results = $this->select(function (Select $select) use ($name, $exclude_id) {
            $select
                ->where([ 'name' => $name ])
                ->limit(1);

            if ($exclude_id) {
                $select->where->notEqualTo('id',  $exclude_id);
            }
        });

        return $results->count() == 0;
    }
}