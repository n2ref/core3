<?php
namespace Rowan\Admin\Tables;
use Rowan\Classes\Db\Table;


/**
 * @property int    id
 * @property string title
 * @property string description
 * @property string privileges
 * @property string author
 * @property string author_modify
 * @property string created_at
 * @property string updated_at
 */
class Roles extends Table {

    protected $table = 'core_roles';

    /**
     * @return void
     */
    protected static function booted(): void {

        // Событие ПОСЛЕ сохранения
        static::updated(function (Roles $row) {

            $this->event('role_update', [
                'role_id' => $row->id
            ]);
        });
    }


    /**
     * Проверка повторения названия
     * @param string   $title
     * @param int|null $exception_id
     * @return bool
     */
    public function isUniqueTitle(string $title, int $exception_id = null): bool {


        $query = $this->newQuery()
            ->where('title', $title);

        if ($exception_id) {
            $query->where('id', '!=', $exception_id);
        }

        return $query->count() === 0;
    }
}