<?php
namespace Core3\Classes\Db;

use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\RowGateway\RowGateway;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\TableGateway\Feature;


/**
 *
 */
class Table extends AbstractTableGateway {

    protected string       $primary_key  = 'id';
    protected static array $static_cache = [];


    /**
     *
     */
    public function __construct() {

        $global_adapter_feature = new Feature\GlobalAdapterFeature();

        $this->featureSet = new Feature\FeatureSet();
        $this->featureSet->addFeature($global_adapter_feature);
        $this->featureSet->addFeature(
            new Feature\RowGatewayFeature(
                new RowGateway($this->primary_key, $this->table, $global_adapter_feature->getStaticAdapter())
            )
        );

        $this->initialize();

        // Снимает ограничение перебора данных
        $this->resultSetPrototype->buffer();
    }


    /**
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
     * @param array|string|null $where
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
     * @param string            $column
     * @param array|string|null $where
     * @return array
     */
    public function fetchCol(string $column, array|string $where = null): array {

        $results = $this->select($where);

        return array_column($results->toArray(), $column);
    }


    /**
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
     * @param array|int $id
     * @return ResultSetInterface
     */
    public function find(array|int $id): ResultSetInterface {

        return $this->select([$this->primary_key => $id]);
    }


    /**
     * @param string $name
     * @return bool
     */
    protected function hasStaticCache(string $name): bool {

        return array_key_exists($name, self::$static_cache);
    }


    /**
     * @param string $name
     * @return mixed
     */
    protected function getStaticCache(string $name): mixed {

        return $this->hasStaticCache($name) ? self::$static_cache[$name] : null;
    }


    /**
     * @param string $name
     * @param mixed  $value
     * @return void
     */
    protected function setStaticCache(string $name, mixed $value): void {

        self::$static_cache[$name] = $value;
    }
}