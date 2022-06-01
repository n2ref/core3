<?php
namespace Core3\Interfaces;

/**
 * Управляет файлами модуля
 */
interface File {

    /**
     * Перехват запросов на отображение файла
     * @param $context - контекст отображения (fileid, thumbid, tfile)
     * @param $table - имя таблицы, с которой связан файл
     * @param $id - id файла
     *
     * @return bool
     */
    public function filehandler($context, $table, $id);
}