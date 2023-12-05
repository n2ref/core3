<?php
namespace Core3\Interfaces;

/**
 * Определяет возможность подписки на события, возникающие в других модулях
 */
interface Events {

    /**
     * Обработка событий
     * @param string $module_name модуль, в котором произошло событие
     * @param string $event_name  код события
     * @param array  $data        данные события
     */
    public function listener(string $module_name, string $event_name, array $data);
}