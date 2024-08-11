<?php
namespace Core3\Install;
use \Core3\Classes\Abstract;


/**
 *
 */
class Install extends Abstract\Install {

    /**
     * Установка
     * @return void
     */
    public function install(): void {

        $this->executeFileSQL(__DIR__ . '/install.sql');
    }


    /**
     * Деинсталляция
     * @return void
     */
    public function uninstall(): void {

    }
}