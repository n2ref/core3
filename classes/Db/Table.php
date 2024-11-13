<?php
namespace Core3\Classes\Db;
use Laminas\Db\TableGateway\Feature;


/**
 *
 */
abstract class Table extends TableAbstract {


    /**
     *
     */
    public function __construct() {

        parent::__construct();

        $global_adapter_feature = new Feature\GlobalAdapterFeature();

        $this->featureSet = new Feature\FeatureSet();
        $this->featureSet->addFeature($global_adapter_feature);
        $this->featureSet->addFeature(
            new Feature\RowGatewayFeature(
                new Row($this->primary_key, $this->getTable(), $global_adapter_feature->getStaticAdapter(), $this->event_manager)
            )
        );

        $this->initialize();

        // Снимает ограничение перебора данных
        $this->resultSetPrototype->buffer();
    }
}