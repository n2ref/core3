<?php
namespace Core3\Mod\Admin\Model;


/**
 *
 */
class Settings extends \Zend_Db_Table_Abstract {

	protected string $_name = 'core_settings';


    /**
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function getRowsSystem(): \Zend_Db_Table_Rowset_Abstract {

        $select = $this->select()
            ->where("data_group = 'system'");

        return $this->fetchAll($select);
    }


    /**
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function getExtra(): \Zend_Db_Table_Rowset_Abstract {

        $select = $this->select()
            ->where("data_group = 'extra'");

        return $this->fetchAll($select);
    }
}