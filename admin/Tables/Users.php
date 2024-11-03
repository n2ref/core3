<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Row;
use Core3\Classes\Db\Table;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;

/**
 *
 */
class Users extends Table {

	protected $table = "core_users";

    private static ?Row $row_old = null;


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


    /**
     * @param Row $row
     * @return void
     */
    public function preInsert(Row $row): void {

        $row->name = trim("{$row->lname} {$row->fname} {$row->mname}");

        $this->event($this->getTable() . '_pre_insert', [
            'user' => $row->toArray(),
        ]);
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Core3\Exceptions\DbException
     * @throws \Core3\Exceptions\Exception
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    protected function preUpdate(Row $row): void {

        self::$row_old = $this->getRowById($row->id);

        $row->name = trim("{$row->lname} {$row->fname} {$row->mname}");

        if ($row->is_active != self::$row_old->is_active) {
            $this->event($this->getTable() . '_pre_active', [
                'user' => $row->toArray(),
            ]);
        }

        $this->event($this->getTable() . '_pre_update', [
            'user' => $row->toArray(),
        ]);
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Core3\Exceptions\DbException
     * @throws \Core3\Exceptions\Exception
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    protected function postUpdate(Row $row): void {

        if ($row->is_active != self::$row_old->is_active) {
            $this->event($this->getTable() . '_post_active', [
                'user' => $row->toArray(),
            ]);
        }

        $this->event($this->getTable() . '_post_update', [
            'user' => $row->toArray(),
        ]);
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Core3\Exceptions\DbException
     * @throws \Core3\Exceptions\Exception
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    protected function preDelete(Row $row): void {

        $this->event($this->getTable() . '_pre_delete', [
            'user' => $row->toArray(),
        ]);
    }


    /**
     * @param Row $row
     * @return void
     * @throws \Core3\Exceptions\DbException
     * @throws \Core3\Exceptions\Exception
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    protected function postDelete(Row $row): void {

        $this->event($this->getTable() . '_post_delete', [
            'user' => $row->toArray(),
        ]);
    }
}