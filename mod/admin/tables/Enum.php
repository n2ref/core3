<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\TableGateway\AbstractTableGateway;

/**
 *
 */
class Enum extends Table {

    protected $table = 'core_enum';

    private array $_enum = [];


    /**
     * @param string $code_name
     * @return array
     */
    public function getEnum(string $code_name): array {

        if ( ! isset($this->_enum[$code_name])) {

            $enum_items = $this->adapter->query("
                SELECT e2.id, 
                       e2.name, 
                       e2.custom_fields, 
                       CASE e.is_active_sw 
                           WHEN 'N' THEN 'N' 
                           ELSE e2.is_active_sw 
                       END AS is_active_sw
				FROM core_enum AS e
				    INNER JOIN core_enum AS e2 ON e.id = e2.parent_id
				WHERE e.code_name = ?
				ORDER BY e2.seq
            ", [ $code_name ]);

            $enum_list = [];

            foreach ($enum_items as $enum_item) {
                $enum_list[$enum_item['id']] = [
                    'value'        => $enum_item['name'],
                    'is_active_sw' => $enum_item['is_active_sw'],
                    'custom'       => $enum_item['custom_field'] ? json_decode($enum_item['custom_field'], true) : []
                ];
            }
            $this->_enum[$code_name] = $enum_list;
        }

        return $this->_enum[$code_name];
    }
}