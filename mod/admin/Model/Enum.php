<?php
namespace Core3\Mod\Admin\Model;


/**
 *
 */
class Enum extends \Zend_Db_Table_Abstract {

	protected string $_name = 'core_enum';
	private          $_enum = [];


    /**
     * @param string $expr
     * @param mixed  $var
     * @return bool
     */
    public function isExists(string $expr, $var = []): bool {

        $select = $this->select();

        if ($var) {
            $select->where($expr, $var);
        } else {
            $select->where($expr);
        }

        $select->limit(1);

        return !! $this->fetchRow($select);
    }


    /**
     * @param string $global_name
     * @return array
     */
    public function getEnum(string $global_name): array {

        if ( ! isset($this->_enum[$global_name])) {
            $enum_items = $this->_db->fetchAll("
                SELECT e2.id, 
                       e2.name, 
                       e2.custom_fields, 
                       CASE e.is_active_sw 
                           WHEN 'N' THEN 'N' 
                           ELSE e2.is_active_sw 
                       END AS is_active_sw
				FROM core_enum AS e
				    INNER JOIN core_enum AS e2 ON e.id = e2.parent_id
				WHERE e.global_name = ?
				ORDER BY e2.seq
            ", $global_name);

            $enum_list = [];

            foreach ($enum_items as $enum_item) {
                $enum_list[$enum_item['id']] = [
                    'value'        => $enum_item['name'],
                    'is_active_sw' => $enum_item['is_active_sw'],
                    'custom'       => $enum_item['custom_field'] ? json_decode($enum_item['custom_field'], true) : []
                ];
            }
            $this->_enum[$global_name] = $enum_list;
        }

        return $this->_enum[$global_name];
    }
}