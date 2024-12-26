<?php
namespace Core3\Interfaces;

/**
 * Определяет возможность ответа на запросы, возникающие в модулях
 */
interface Answer {

    /**
     * Ответ на запрос модуля
     * @param string $module        модуль, в котором произошло событие
     * @param string $request_name  имя запроса
     * @param array  $data          данные события
     */
    public function answer(string $module, string $request_name, array $data = []): mixed;
}