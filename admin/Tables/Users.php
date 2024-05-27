<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;

/**
 *
 */
class Users extends Table {


	protected $table = "core_users";


    /**
     * Количество пользователей
     * @return int
     */
	public function getCount(): int {

        $results = $this->select(function (Select $select) {
            $select
                ->columns([
                    'id'    => 'id',
                    'count' => new Expression('COUNT(1)')
                ])
                ->limit(1);
        });

        return (int)$results->current()->count;
	}


    /**
     * Получаем информацию о пользователе по его логину
     * @param string $login
     * @return AbstractRowGateway|null
     */
	public function getRowByLogin(string $login): ?AbstractRowGateway {

        $results = $this->select(function (Select $select) use ($login) {
            $select
                ->where(['login' => $login])
                ->limit(1);
        });

        return $results->current();
	}


    /**
     * Получаем информацию о пользователе по его email
     * @param string $email
     * @return AbstractRowGateway|null
     */
	public function getRowByEmail(string $email): ?AbstractRowGateway {

        $results = $this->select(function (Select $select) use ($email) {
            $select
                ->where(['email' => $email])
                ->limit(1);
        });

        return $results->current();
	}


    /**
     * Получаем информацию о пользователе по его логину или email
     * @param string $login_email
     * @return AbstractRowGateway|null
     */
	public function getRowByLoginEmail(string $login_email): ?AbstractRowGateway {

        $results = $this->select(function (Select $select) use ($login_email) {
            $select
                ->where([
                    'login' => $login_email,
                    'email' => $login_email
                ], 'OR')
                ->limit(1);
        });

        return $results->current();
	}


    /**
     * Проверка повторения логина
     * @param string   $login
     * @param int|null $exception_user_id
     * @return bool
     */
    public function isUniqueLogin(string $login, int $exception_user_id = null): bool {

        $results = $this->select(function (Select $select) use ($login, $exception_user_id) {
            $select
                ->where([ 'login' => $login ])
                ->limit(1);

            if ($exception_user_id) {
                $select->where->notEqualTo('id', $exception_user_id);
            }
        });

        return $results->count() == 0;
    }


    /**
     * Проверка повторения email
     * @param string   $email
     * @param int|null $exception_user_id
     * @return bool
     */
    public function isUniqueEmail(string $email, int $exception_user_id = null): bool {

        $results = $this->select(function (Select $select) use ($email, $exception_user_id) {
            $select
                ->where([ 'email' => $email ])
                ->limit(1);

            if ($exception_user_id) {
                $select->where->notEqualTo('id', $exception_user_id);
            }
        });

        return $results->count() == 0;
    }
}