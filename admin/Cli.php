<?php
namespace Core3\Mod\Admin;
use Core3\Classes\Common;


/**
 *
 */
class Cli extends Common {

    /**
     * Очистка загруженных файлов из временной директории
     * @param int $days За какое количество дней очищать файлы
     * @return void
     */
    public function clearUploadFiles(int $days = 1): void {

    }


    /**
     * Очистка старых данных в таблице core_controls
     * @param int $days За какое количество дней очищать
     * @return void
     */
    public function clearOldControls(int $days = 30): void {

        $this->modAdmin->tableControls->deleteOld($days);
    }
}