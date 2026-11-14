<?php
namespace Rowan\Classes\Abstract;

use Rowan\Classes\Db;

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