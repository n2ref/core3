<?php
namespace Core3\Interfaces;

/**
 * Определяет возможность подписки на события, возникающие в других модулях
 */
interface Subscribe {

    /**
     * Будет выполнено модулем, который является источником события
     *
     * @param $module_id - идентификатор модуля, инициировавшего событие
     * @param $event - код события
     * @param $data - данные события
     * @return mixed
     */
    public function listen($module_id, $event, $data);
}