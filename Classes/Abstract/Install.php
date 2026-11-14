<?php
namespace Rowan\Classes\Abstract;
use Rowan\Classes\Db;


/**
 *
 */
abstract class Install extends Db {

    /**
     * Установка
     * @return void
     */
    abstract public function install(): void;


    /**
     * Деинсталляция
     * @return void
     */
    abstract public function uninstall(): void;


    /**
     * @param string $file_sql
     * @return void
     */
    protected function executeFileSQL(string $file_sql) {

    }
}