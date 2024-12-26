<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Row;
use Core3\Classes\Db\Table;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Settings extends Table {

	protected $table = 'core_settings';

    private static ?Row $row_old = null;


    /**
     * @param string      $code
     * @param string|null $module
     * @return Row|null
     */
    public function getRowByCodeModule(string $code, ?string $module):? Row {

        $results = $this->select(function (Select $select) use ($code, $module) {
            $select
                ->where(['code' => $code])
                ->limit(1);

            if ($module) {
                $select->where(['module' => $module]);
            }
        });

        return $results->current();
    }


    /**
     * Проверка повторения кода
     * @param string      $code
     * @param string|null $module
     * @param int|null    $exception_id
     * @return bool
     */
    public function isUniqueCode(string $code, string $module = null, int $exception_id = null): bool {

        $results = $this->select(function (Select $select) use ($code, $module, $exception_id) {
            $select
                ->where([ 'code' => $code ])
                ->limit(1);

            if ($module) {
                $select->where(['module' => $module]);
            }

            if ($exception_id) {
                $select->where->notEqualTo('id', $exception_id);
            }
        });

        return $results->count() == 0;
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    protected function preUpdate(Row $row): void {

        self::$row_old = $this->getRowById($row->id);

        if ($row->is_active != self::$row_old->is_active) {
            $this->event($this->getTable() . '_pre_active', [
                'setting' => $row->toArray(),
            ]);
        }
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    protected function postUpdate(Row $row): void {

        if ($row->is_active != self::$row_old->is_active) {
            $this->event($this->getTable() . '_post_active', [
                'setting' => $row->toArray(),
            ]);
        }
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    protected function preDelete(Row $row): void {

        $this->event($this->getTable() . '_pre_delete', [
            'setting' => $row->toArray(),
        ]);
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    protected function postDelete(Row $row): void {

        $this->event($this->getTable() . '_post_delete', [
            'setting' => $row->toArray(),
        ]);
    }
}