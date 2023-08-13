<?php
namespace Core3\Classes\Db;

use Laminas\Db\Adapter\Driver\Pdo\Result;
use Laminas\Db\Adapter\Driver\StatementInterface;

/**
 *
 */
class Adapter extends \Laminas\Db\Adapter\Adapter {

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        parent::__construct($config);

        // Снимает ограничение перебора данных
        $this->getQueryResultSetPrototype()->buffer();
    }


    /**
     * @param string            $sql
     * @param array|string|null $parameters
     * @return array
     */
    public function fetchAll(string $sql, array|string $parameters = null): array {

        $query_result = $this->query($sql, $parameters ?: self::QUERY_MODE_PREPARE);
        $result_set   = $query_result instanceof StatementInterface
            ? $query_result->execute()
            : $query_result;

        if ($result_set instanceof Result) {
            $result = iterator_to_array($result_set);

        } else {
            $result = $result_set->toArray();
        }

        return $result;
    }


    /**
     * @param string            $sql
     * @param array|string|null $parameters
     * @return array|null
     */
    public function fetchRow(string $sql, array|string $parameters = null):? array {

        $query_result = $this->query($sql, $parameters ?: self::QUERY_MODE_PREPARE);
        $result_set   = $query_result instanceof StatementInterface
            ? $query_result->execute()
            : $query_result;

        $result = $result_set->current();

        if ($result instanceof \ArrayObject) {
            return $result->getArrayCopy();
        } else {
            return $result;
        }
    }


    /**
     * @param string            $sql
     * @param array|string|null $parameters
     * @return array
     */
    public function fetchCol(string $sql, array|string $parameters = null): array {

        $result = $this->fetchAll($sql, $parameters);

        if (empty($result)) {
            return [];
        }

        $first_row = current($result);
        $first_key = key($first_row);

        return array_column($result, $first_key);
    }


    /**
     * @param string            $sql
     * @param array|string|null $parameters
     * @return array
     */
    public function fetchPairs(string $sql, array|string $parameters = null): array {

        $result = $this->fetchAll($sql, $parameters);

        if (empty($result)) {
            return [];
        }

        $first_row = current($result);
        $first_key = key($first_row);

        next($first_row);
        $second_key = key($first_row);

        return array_combine(
            array_column($result, $first_key),
            array_column($result, $second_key)
        );
    }


    /**
     * @param string            $sql
     * @param array|string|null $parameters
     * @return string|null
     */
    public function fetchOne(string $sql, array|string $parameters = null):? string {

        $result = $this->fetchRow($sql, $parameters);

        if (empty($result)) {
            return null;
        }

        return current($result);
    }
}