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

    protected string $primary_key = 'id';


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
     * @param array                      $options
     * @return ResultSetInterface
     */
    public function fetchAll(array|string|\Closure $where = null, array $options = []): ResultSetInterface {

        if ( ! empty($where) && ! $where instanceof \Closure) {
            $where = function (Select $select) use ($where, $options) {
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
    public function fetchRow(array|string $where = null):? AbstractRowGateway {

        $results = $this->select(function (Select $select) use ($where) {
            $select
                ->where($where)
                ->limit(1);
        });

        return $results->current();
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

        $results = parent::select([$this->primary_key => $id]);

        return $results;
    }
}