<?php
namespace Rowan\Classes\Db;
use Rowan\Classes\Common;
use \Illuminate\Database\Eloquent\Collection;
use \Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Builder;


/**
 * @mixin Builder
 */
abstract class TableAbstract extends Model {

    protected $guarded = false;

    protected static array $static_cache = [];


    /**
     * Получение всех строк
     * @param array|string|\Closure|null $where
     * @return Collection
     */
    public function fetchAll(array|string|\Closure|null $where = null): Collection {

        $query = $this->newQuery();

        if ($where) {
            $query->where($where);
        }

        return $query->get();
    }


    /**
     * Получение строки
     * @param array|string|\Closure|null $where
     * @return mixed
     */
    public function fetchRow(array|string|\Closure|null $where = null): mixed {

        $query = $this->newQuery();

        if ($where) {
            $query->where($where);
        }

        return $query->limit(1)->first();
    }


    /**
     * Получение колонки
     * @param string $column_key
     * @param string $column_value
     * @param array|string|\Closure|null $where
     * @return array
     */
    public function fetchPairs(string $column_key, string $column_value, array|string|\Closure|null $where = null): array {

        $query = $this->newQuery();

        if ($where) {
            $query->where($where);
        }

        $result_array = $query->get()->toArray();


        return array_combine(
            array_column($result_array, $column_key),
            array_column($result_array, $column_value)
        );
    }


    /**
     * Получение списка пары полей ключ-значение
     * @param string            $column
     * @param array|string|null $where
     * @return array
     */
    public function fetchCol(string $column, array|string|null $where = null): array {

        $query = $this->newQuery();

        if ($where) {
            $query->where($where);
        }

        $results = $query->get()->toArray();

        return array_column($results, $column);
    }


    /**
     * Получение строки по id
     * @param int $id
     * @return self|null
     */
    public function getRowById(int $id):? self {

        $results = $this->newQuery()
            ->where([$this->primaryKey => $id])
            ->limit(1);

        return $results->first();
    }


    /**
     * Получение списка строк по одному или нескольким id
     * @param array|int $id
     * @return ResultSetInterface
     */
    public function find(array|int $id): ResultSetInterface {

        return $this->select([$this->primaryKey => $id]);
    }


    /**
     * Вызов обработки события
     * @param string $event_name
     * @param array $data
     * @return void
     * @throws ExceptionInterface
     */
    protected function event(string $event_name, array $data): void {

        $key = '_common';

        if ($this->hasStaticCache($key)) {
            $common = $this->getStaticCache($key);
        }

        if (empty($common)) {
            $common = new Common();
            $this->setStaticCache($key, $common);
        }


        $common->event($event_name, $data);
    }


    /**
     * Наличие ключа в статическим кэше
     * @param string $name
     * @return bool
     */
    protected function hasStaticCache(string $name): bool {

        return array_key_exists($name, self::$static_cache);
    }


    /**
     * Получение значения по ключу из статического кэша
     * @param string $name
     * @return mixed
     */
    protected function getStaticCache(string $name): mixed {

        return $this->hasStaticCache($name) ? self::$static_cache[$name] : null;
    }


    /**
     * Установка значения по ключу в статический кэш
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    protected function setStaticCache(string $name, mixed $value): void {

        self::$static_cache[$name] = $value;
    }
}