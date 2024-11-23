<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Row;
use Core3\Classes\Db\Table;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Roles extends Table {

    protected $table = 'core_roles';


    /**
     * Проверка повторения названия
     * @param string   $title
     * @param int|null $exception_id
     * @return bool
     */
    public function isUniqueTitle(string $title, int $exception_id = null): bool {

        $results = $this->select(function (Select $select) use ($title, $exception_id) {
            $select
                ->where([ 'title' => $title ])
                ->limit(1);

            if ($exception_id) {
                $select->where->notEqualTo('id', $exception_id);
            }
        });

        return $results->count() == 0;
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Core3\Exceptions\DbException
     * @throws \Core3\Exceptions\Exception
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function postUpdate(Row $row): void {

        $this->event('role_update', [
            'role_id' => $row->id
        ]);
    }
}