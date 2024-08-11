<?php
namespace core3\classes\Abstract;

use Core3\Classes\Db;

/**
 *
 */
abstract class Migrate extends Db {

    /**
     * Обновление до версии
     * @return void
     */
    abstract public function up(): void;


    /**
     * Откат изменений
     * @return void
     */
    abstract public function down(): void;
}