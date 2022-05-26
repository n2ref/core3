<?php
namespace Core3\Mod\Admin\Model;

/**
 *
 */
class Modules extends \Zend_Db_Table_Abstract {

    protected string $_name = 'core_modules';



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
     * Получаем активные модули
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function getActive(): \Zend_Db_Table_Rowset_Abstract {

        $select = $this->select()
            ->where("is_active_sw = 'Y'");

        return $this->fetchAll($select);
    }
}