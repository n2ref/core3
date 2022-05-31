<?php
namespace Core3\Mod\Admin\Model;

/**
 *
 */
class UsersSession extends \Zend_Db_Table_Abstract {

    protected $_name = 'core_users_sessions';

    /**
     * Получаем информацию о сессии по токену
     * @param string $refresh_token
     * @return \Zend_Db_Table_Row_Abstract|null
     */
    public function getRowByRefreshToken(string $refresh_token): ?\Zend_Db_Table_Row_Abstract {

        $select = $this->select()
            ->where('refresh_token = ?', $refresh_token)
            ->limit(1);

        return $this->fetchRow($select);
    }
}