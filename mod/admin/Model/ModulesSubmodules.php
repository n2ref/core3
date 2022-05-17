<?php
namespace Core3\Mod\Admin\Model;

/**
 *
 */
class ModulesSubmodules extends \Zend_Db_Table_Abstract {

	protected string $_name         = 'core_modules_submodules';
	protected        $_referenceMap = [
		'Module' => [
			'columns'       => 'id',
			'refTableClass' => 'Modules'
		]
	];


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
     * @param int $module_id
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function getRowsByModuleId(int $module_id): \Zend_Db_Table_Rowset_Abstract {

        $select = $this->select()
            ->where("module_id = ?", $module_id);

        return $this->fetchAll($select);
    }
}