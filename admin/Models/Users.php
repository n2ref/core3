<?php
namespace Core3\Mod\Admin\Models;
use Core3\Classes\Common;
use Core3\Classes\Tools;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Core3\Mod\Admin;
use Core3\Mod\Admin\Classes\Users\Files;
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
     */
    public function deleteById(int $user_id): void {

        $this->modAdmin->tableUsers->getRowById($user_id)
            ?->delete();
    }


    /**
     * Удаление пользователя
     * @param string $user_login
     * @return void
     */
    public function deleteByLogin(string $user_login): void {

        $this->modAdmin->tableUsers->getRowByLogin($user_login)
            ?->delete();
    }


    /**
     * Добавление пользователя
     * @param array $data
     * @return int
     * @throws HttpException
     * @throws Exception
     */
    public function create(array $data): int {

        if (empty($data['login'])) {
            throw new Exception($this->_('Не указано обязательное поле %s', ['login']));
        }

        if (empty($data['role_id'])) {
            throw new Exception($this->_('Не указано обязательное поле %s', ['role_id']));
        }

        if (empty($data['pass'])) {
            throw new Exception($this->_('Не указано обязательное поле %s', ['pass']));
        }

        if ( ! $this->modAdmin->tableUsers->isUniqueLogin($data['login'])) {
            throw new Exception($this->_("Пользователь с таким логином уже существует"));
        }

        if ( ! empty($data['email']) &&
             ! $this->modAdmin->tableUsers->isUniqueEmail($data['email'])
        ) {
            throw new Exception($this->_("Пользователь с таким email уже существует"));
        }

        $data['pass'] = Tools::passSalt(md5($data['pass']));

        $this->db->beginTransaction();
        try {
            $this->modAdmin->tableUsers->insert($data);

            $user_id = $this->modAdmin->tableUsers->getLastInsertValue();

            if ( ! empty($data['avatar_type']) && $data['avatar_type'] == 'generate') {
                $user = $this->modAdmin->tableUsers->getRowById($user_id);
                (new Files())->generateAvatar($user);
            }

            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        return $user_id;
    }


    /**
     * Обновление данных пользователя
     * @param int   $user_id
     * @param array $data
     * @return void
     */
    public function update(int $user_id, array $data): void {

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if ($user) {
            $fields  = [
                'role_id', 'email', 'fname', 'lname', 'mname',
                'pass', 'is_active', 'avatar_type', 'is_admin'
            ];
            $is_save = false;

            foreach ($fields as $field) {
                if (array_key_exists($field, $data) &&
                    (is_string($data[$field]) || is_numeric($data[$field]) || is_null($data[$field]))
                ) {
                    $user->{$field} = $data[$field];

                    $is_save = true;
                }
            }

            if ($is_save) {
                $user->name = trim(implode(' ', [
                    trim($user->lname ?: ''),
                    trim($user->fname ?: ''),
                    trim($user->mname ?: ''),
                ])) ?: null;

                $user->save();
            }
        }
    }


    /**
     * Сохранение персональных данных для пользователя
     * @param int        $user_id
     * @param string     $name
     * @param array|null $data
     * @return void
     */
    public function saveData(int $user_id, string $name, array $data = null): void {

        $row = $this->modAdmin->tableUsersData->getRowByUserName($user_id, $name);

        if ($row) {
            if (is_null($data)) {
                $row->delete();
            } else {
                $row->value = json_encode($data);
                $row->save();
            }

        } else {
            $this->modAdmin->tableUsersData->insert([
                'user_id' => $user_id,
                'name'    => $name,
                'value'   => json_encode($data),
            ]);
        }
    }


    /**
     * Получение персональных данных для пользователя
     * @param int    $user_id
     * @param string $name
     * @return array|null
     */
    public function getData(int $user_id, string $name):? array {

        $data = null;
        $row  = $this->modAdmin->tableUsersData->getRowByUserName($user_id, $name);

        if ($row) {
            $data = $row->value ? json_decode($row->value, true) : [];
        }

        return $data;
    }
}