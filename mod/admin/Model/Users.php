<?php


/**
 * Class Users
 */
class Users extends Zend_Db_Table_Abstract {

	protected string $_name = 'core_users';


    /**
     * @param string $expr
     * @param array  $var
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function exists($expr, $var = []): ?Zend_Db_Table_Row_Abstract {

        $sel = $this->select()->where($expr, $var);
        return $this->fetchRow($sel->limit(1));
    }


    /**
     * Получаем значение одного поля
     * @param       $field
     * @param       $expr
     * @param array $var
     * @return string
     */
    public function fetchOne($field, $expr, $var = []): ?string {

        $sel = $this->select();
        if ($var) {
            $sel->where($expr, $var);
        } else {
            $sel->where($expr);
        }
        $res = $this->fetchRow($sel);

        return $res ? $this->fetchRow($sel)->$field : null;
    }


    /**
     * @param int $id
     * @return array
     */
	public function getUserById(int $id): array {

        $user = $this->_db->fetchRow("
            SELECT u.id, 
                   u.pass, 
                   u.email, 
                   u.login, 
                   u.lastname, 
                   u.firstname, 
                   u.middlename, 
                   u.is_admin_sw,  
                   u.role_id
            FROM `core_users` AS u
            WHERE u.is_active_sw = 'Y' 
              AND u.id = ? 
            LIMIT 1
        ", $id);

        return $user ?: [];
    }


    /**
     * Получаем информацию о пользователе по его логину
     * @param string $login
     * @return array
     */
    public function getUserByLogin(string $login): array {

        $user = $this->_db->fetchRow("
            SELECT u.id, 
                   u.pass, 
                   u.email, 
                   u.login, 
                   u.lastname, 
                   u.firstname, 
                   u.middlename, 
                   u.is_admin_sw,  
                   u.role_id
            FROM `core_users` AS u
            WHERE u.is_active_sw = 'Y' 
              AND (u.login = :login OR u.email = :login) 
            LIMIT 1
        ", [
            'login' => $login
        ]);

        return $user ?: [];
	}


    /**
     * Получаем список всех активных юзеров
     * @return array
     */
	public function getAllUsers(): array {

        $users = $this->_db->fetchAll("
            SELECT u.id, 
                   u.pass, 
                   u.email, 
                   u.login, 
                   u.lastname, 
                   u.firstname, 
                   u.middlename, 
                   u.is_admin_sw,  
                   u.role_id
            FROM `core_users` AS u
            WHERE u.is_active_sw = 'Y' 
        ");

        return $users ?: [];
    }
}