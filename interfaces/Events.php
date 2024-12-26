<?php
namespace Core3\Interfaces;

/**
 * Определяет возможность подписки на события, возникающие в модулях
 */
interface Events {

    /**
     * Обработка событий
     * @param string $module модуль, в котором произошло событие
     * @param string $event  код события
     * @param array  $data   данные события
     */
    public function events(string $module, string $event, array $data): void;
}