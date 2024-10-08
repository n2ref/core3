<?php
namespace Core3\Mod\Admin\Models;
use Core3\Classes\Common;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Mod\Admin;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 * @property Admin\Controller $modAdmin
 */
class Users extends Common {


    /**
     * Получение пользователя по id
     * @param int $user_id
     * @return array|null
     */
    public function getUserById(int $user_id):? array {

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        return $user?->toArray();
    }


    /**
     * Получение пользователя по login
     * @param string $login
     * @return array|null
     */
    public function getUserByLogin(string $login):? array {

        $user = $this->modAdmin->tableUsers->getRowById($login);

        return $user?->toArray();
    }


    /**
     * Получение пользователя по email
     * @param string $email
     * @return array|null
     */
    public function getUserByEmail(string $email):? array {

        $user = $this->modAdmin->tableUsers->getRowById($email);

        return $user?->toArray();
    }


    /**
     * Получение списка админов
     * @return array
     */
    public function getUsersAdmins(): array {

        $users = $this->modAdmin->tableUsers->fetchAll([
            'is_admin' => '1'
        ]);

        return $users->toArray();
    }


    /**
     * Удаление пользователя
     * @param int $user_id
     * @return void
     * @throws DbException
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function delete(int $user_id): void {

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if ($user) {
            $this->event($this->modAdmin->tableUsers->getTable() . '_pre_delete', [
                'user' => $user,
            ]);

            $user->delete();

            $this->event($this->modAdmin->tableUsers->getTable() . '_post_delete', [
                'user' => $user,
            ]);
        }
    }


    /**
     * Переключение активности пользователя
     * @param int  $user_id
     * @param bool $is_active
     * @return void
     * @throws DbException
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function switchActive(int $user_id, bool $is_active): void {

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if ($user && ($user->is_active == '1') != $is_active) {
            $user->is_active = $is_active ? '1' : '0';
            $user->save();

            $this->event($this->modAdmin->tableUsers->getTable() . '_switch_active', [
                'user' => $user,
            ]);
        }
    }
}