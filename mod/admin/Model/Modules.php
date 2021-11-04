<?php

namespace Core3\Mod\Admin;

/**
 *
 */
class Modules extends \Zend_Db_Table_Abstract {

    protected string $_name = 'core_modules';


    /**
     * @param string $expr
     * @param array  $var
     * @return null|\Zend_Db_Table_Row_Abstract
     */
    public function exists($expr, $var = []) {

        $sel = $this->select();
        if ($var) {
            $sel->where($expr, $var);
        } else {
            $sel->where($expr);
        }

        return $this->fetchRow($sel->limit(1));
    }


    /**
     * Получаем идентификаторы всех активных модулей
     * @return array
     */
    public function getIds() {

        $sel  = $this->select()->from($this->_name, ['m_id', 'module_id'])->where("visible='Y'");
        $res  = $this->fetchAll($sel);
        $data = [];
        foreach ($res as $val) {
            $data[$val->m_id] = $val->module_id;
        }

        return $data;
    }


    /**
     * получаем список активных модулей
     * @return array
     */
    public function getModuleList(): array {

        $mods = $this->_db->fetchAll("
			SELECT m.*,
				   sm_id,
				   sm_name,
				   sm_key,
				   m.is_active_sw
			FROM core_modules AS m
				LEFT JOIN core_modules_actions AS ma ON m.id = ma.module_id
			WHERE m.is_active_sw = 'Y'
			ORDER BY m.seq, 
			         ma.seq
		");

        return $mods ?: [];
    }
}