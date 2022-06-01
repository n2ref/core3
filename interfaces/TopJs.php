<?php
namespace Core3\Interfaces;

/**
 * Определяет возможность подключения глобальных javascript скриптов из модулей
 */
interface TopJs {

    /**
     * Возвращает массив адресов к js скриптам
     * @return array
     */
    public function topJs();
}