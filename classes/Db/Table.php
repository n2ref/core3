<?php
namespace Core3\Classes\Db;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\TableGateway\Feature;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManager;


/**
 * @method preInsert(Row $event)
 * @method postInsert(Row $event)
 * @method preUpdate(Row $event)
 * @method postUpdate(Row $event)
 * @method preDelete(Row $event)
 * @method postDelete(Row $event)
 */
abstract class Table extends AbstractTableGateway {

    protected string        $primary_key   = 'id';
    protected static array  $static_cache  = [];
    protected ?EventManager $event_manager = null;


    /**
     *
     */
    public function __construct() {

        $global_adapter_feature = new Feature\GlobalAdapterFeature();
        $this->event_manager    = new EventManager();


        $this->featureSet = new Feature\FeatureSet();
        $this->featureSet->addFeature($global_adapter_feature);
        $this->featureSet->addFeature(
            new Feature\RowGatewayFeature(
                new Row($this->primary_key, $this->table, $global_adapter_feature->getStaticAdapter(), $this->event_manager)
            )
        );


        if (method_exists($this, 'preInsert'))  { $this->event_manager->attach('preInsert',  function (Event $event) { $target = $event->getTarget(); $this->preInsert($target); }); }
        if (method_exists($this, 'postInsert')) { $this->event_manager->attach('postInsert', function (Event $event) { $target = $event->getTarget(); $this->postInsert($target); }); }
        if (method_exists($this, 'preUpdate'))  { $this->event_manager->attach('preUpdate',  function (Event $event) { $target = $event->getTarget(); $this->preUpdate($target); }); }
        if (method_exists($this, 'postUpdate')) { $this->event_manager->attach('postUpdate', function (Event $event) { $target = $event->getTarget(); $this->postUpdate($target); }); }
        if (method_exists($this, 'preDelete'))  { $this->event_manager->attach('preDelete',  function (Event $event) { $target = $event->getTarget(); $this->preDelete($target); }); }
        if (method_exists($this, 'postDelete')) { $this->event_manager->attach('postDelete', function (Event $event) { $target = $event->getTarget(); $this->postDelete($target); }); }


        $this->initialize();

        // Снимает ограничение перебора данных
        $this->resultSetPrototype->buffer();
    }


    /**
     * Получение всех строк
     * @param array|string|\Closure|null $where
     * @return ResultSetInterface
     */
    public function fetchAll(array|string|\Closure $where = null): ResultSetInterface {

        if ( ! empty($where) && ! $where instanceof \Closure) {
            $where = function (Select $select) use ($where) {
                $select
                    ->where($where);
            };
        }

        return $this->select($where);
    }


    /**
     * Получение строки
     * @param array|string|\Closure|null $where
     * @return AbstractRowGateway|null
     */
    public function fetchRow(array|string|\Closure $where = null):? AbstractRowGateway {

        if ( ! empty($where) && ! $where instanceof \Closure) {
            $where = function (Select $select) use ($where) {
                $select
                    ->where($where)
                    ->limit(1);
            };
        }

        return $this->select($where)->current();
    }


    /**
     * Получение колонки
     * @param string            $column_key
     * @param string            $column_value
     * @param array|string|null $where
     * @return array
     */
    public function fetchPairs(string $column_key, string $column_value, array|string $where = null): array {

        $results      = $this->select($where);
        $result_array = $results->toArray();


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
    public function fetchCol(string $column, array|string $where = null): array {

        $results = $this->select($where);

        return array_column($results->toArray(), $column);
    }


    /**
     * Получение строки по id
     * @param int $id
     * @return AbstractRowGateway|null
     */
    public function getRowById(int $id):? AbstractRowGateway {

        $primary_key = $this->primary_key;

        $results = $this->select(function (Select $select) use ($primary_key, $id) {
            $select
                ->where([$primary_key => $id])
                ->limit(1);
        });

        return $results->current();
    }


    /**
     * Получение списка строк по одному или нескольким id
     * @param array|int $id
     * @return ResultSetInterface
     */
    public function find(array|int $id): ResultSetInterface {

        return $this->select([$this->primary_key => $id]);
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