<?php
namespace Core3\Mod\Admin\Model;


/**
 *
 */
class Users extends \Zend_Db_Table_Abstract {

	protected string $_name = 'core_users';


    /**
     * Получаем информацию о пользователе по его логину
     * @param string $login
     * @return \Zend_Db_Table_Row_Abstract|null
     */
	public function getRowByLogin(string $login): ?\Zend_Db_Table_Row_Abstract {

        $select = $this->select()
            ->where('login = ?', $login)
            ->limit(1);

        return $this->fetchRow($select);
	}


    /**
     * Получаем информацию о пользователе по его email
     * @param string $email
     * @return \Zend_Db_Table_Row_Abstract|null
     */
	public function getRowByEmail(string $email): ?\Zend_Db_Table_Row_Abstract {

        $select = $this->select()
            ->where('email = ?', $email)
            ->limit(1);

        return $this->fetchRow($select);
	}


    /**
     * Получаем информацию о пользователе по его логину или email
     * @param string $login_email
     * @return \Zend_Db_Table_Row_Abstract|null
     */
	public function getRowByLoginEmail(string $login_email): ?\Zend_Db_Table_Row_Abstract {

        $select = $this->select()
            ->where('login = ?', $login_email)
            ->orWhere('email = ?', $login_email)
            ->limit(1);

        return $this->fetchRow($select);
	}
}