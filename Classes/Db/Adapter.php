<?php
namespace Rowan\Classes\Db;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

/**
 *
 */
class Adapter {

    private Manager $manager;

    /**
     * @param array $config
     * @param string $name
     */
    public function __construct(array $config, string $name = 'default') {

        $this->manager = new Manager();
        $this->manager->addConnection($config, $name);
        $this->manager->setEventDispatcher(new Dispatcher(new Container));
        $this->manager->setAsGlobal();
        $this->manager->bootEloquent();
    }


    /**
     * @param string            $sql
     * @param array|string|null $parameters
     * @return array
     */
    public function fetchAll(string $sql, array|string|null $parameters = null): array {

        return $this->manager->getConnection()->select($sql, $parameters);
    }


    /**
     * @param string $sql
     * @param array|string|null $parameters
     * @return array|null
     */
    public function fetchRow(string $sql, array|string|null $parameters = null):? array {

        $result = $this->fetchAll($sql, $parameters);
        return current($result) ?: null;
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


    /**
     * @param $query
     * @param array $bindings
     * @return bool
     */
    public function query($query, array $bindings = []): bool {

        return $this->manager->getConnection()->statement($query, $bindings);
    }


    /**
     * @return void
     * @throws \Throwable
     */
    public function beginTransaction(): void {

        $this->manager->getConnection()->beginTransaction();
    }


    /**
     * @return void
     * @throws \Throwable
     */
    public function commit(): void {

        $this->manager->getConnection()->commit();
    }


    /**
     * @return void
     * @throws \Throwable
     */
    public function rollback(): void {

        $this->manager->getConnection()->rollback();
    }


    /**
     * @return \PDO
     */
    public function getPdo(): \PDO {

        return $this->manager->getConnection()->getPdo();
    }


    /**
     * @return void
     * @throws \Throwable
     */
    public function disconnect(): void {

        $this->manager->getConnection()->disconnect();
    }
}