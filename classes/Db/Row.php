<?php
namespace Core3\Classes\Db;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\TableGateway\Feature\EventFeatureEventsInterface;
use Laminas\EventManager\EventManagerInterface;


/**
 *
 */
class Row extends \Laminas\Db\RowGateway\RowGateway {

    private EventManagerInterface $event_manager;


    /**
     * @param string                 $primaryKeyColumn
     * @param string|TableIdentifier $table
     * @param AdapterInterface|Sql   $adapterOrSql
     * @param EventManagerInterface  $event_manager
     */
    public function __construct($primaryKeyColumn, $table, $adapterOrSql, EventManagerInterface $event_manager) {

        parent::__construct($primaryKeyColumn, $table, $adapterOrSql);

        $this->event_manager = $event_manager;
    }


    /**
     * Удаление записи
     * @return int
     */
    public function delete(): int {

        $this->event_manager->trigger(EventFeatureEventsInterface::EVENT_PRE_DELETE, $this);

        $affected_rows = parent::delete();

        $this->event_manager->trigger(EventFeatureEventsInterface::EVENT_POST_DELETE, $this);

        return $affected_rows;
    }


    /**
     * Сохранение записи
     * @return int
     */
    public function save(): int {

        $isset_row = $this->rowExistsInDatabase();


        $pre_event = $isset_row
            ? EventFeatureEventsInterface::EVENT_PRE_UPDATE
            : EventFeatureEventsInterface::EVENT_PRE_INSERT;

        $this->event_manager->trigger($pre_event, $this);


        $affected_rows = parent::save();


        $post_event = $isset_row
            ? EventFeatureEventsInterface::EVENT_POST_UPDATE
            : EventFeatureEventsInterface::EVENT_POST_INSERT;

        $this->event_manager->trigger($post_event, $this);

        return $affected_rows;
    }
}