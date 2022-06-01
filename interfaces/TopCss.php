<?php
namespace Core3\Interfaces;


/**
 * Определяет возможность подключения глобальных css скриптов из модулей
 */
interface TopCss {

    /**
     * Возвращает массив адресов к css скриптам
     * @return array
     */
    public function topCss();
}