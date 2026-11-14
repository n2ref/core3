<?php
namespace Rowan\Admin\Tables;
use Rowan\Classes\Db\Table;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int    id
 * @property int    role_id
 * @property string login
 * @property string email
 * @property string pass
 * @property string pass_reset_token
 * @property string pass_reset_date
 * @property string name
 * @property string fname
 * @property string lname
 * @property string mname
 * @property string avatar_type
 * @property int    is_active
 * @property int    is_admin
 * @property int    deleted_at
 * @property string created_at
 * @property string updated_at
 */
class Users extends Table {

    use SoftDeletes;

	protected $table = "core_users";


    /**
     * @return void
     */
    protected static function booted(): void {

        // Событие ПЕРЕД сохранением
        static::updating(function (Users $row) {

            if ($row->isDirty(['lname', 'fname', 'mname'])) {
                $row->name = trim("{$row->lname} {$row->fname} {$row->mname}");
            }

            if ($row->isDirty('is_active')) {
                $this->event($this->getTable() . '_pre_active', [
                    'user' => $row->toArray(),
                ]);
            }

            $this->event($this->getTable() . '_pre_update', [
                'user' => $row->toArray(),
            ]);
        });


        // Событие ПОСЛЕ сохранения
        static::updated(function (Users $row) {

            if ($row->isDirty('is_active')) {
                $this->event($this->getTable() . '_post_active', [
                    'user' => $row->toArray(),
                ]);
            }

            $this->event($this->getTable() . '_post_update', [
                'user' => $row->toArray(),
            ]);
        });


        // Событие ПЕРЕД удалением
        static::deleting(function (Users $row) {
            $this->event($this->getTable() . '_pre_delete', [
                'user' => $row->toArray(),
            ]);
        });


        // Событие ПОСЛЕ удалением
        static::deleting(function (Users $row) {

            $this->event($this->getTable() . '_post_delete', [
                'user' => $row->toArray(),
            ]);
        });


        // Событие ПОСЛЕ удалением
        static::creating(function (Users $row) {

            $row->name = trim("{$row->lname} {$row->fname} {$row->mname}");

            $this->event($this->getTable() . '_pre_insert', [
                'user' => $row->toArray(),
            ]);
        });
    }



    /**
     * Количество пользователей
     * @return int
     */
	public function getCount(): int {

        return $this->newQuery()
            ->count();
	}


    /**
     * Получаем информацию о пользователе по его логину
     * @param string $login
     * @return self|null
     */
	public function getRowByLogin(string $login):? self {

        return $this->newQuery()
            ->where('login', $login)
            ->limit(1)
            ->first();
	}


    /**
     * Получаем информацию о пользователе по его email
     * @param string $email
     * @return self|null
     */
	public function getRowByEmail(string $email):? self {

        return $this->newQuery()
            ->where('email', $email)
            ->limit(1)
            ->first();
	}


    /**
     * Получаем информацию о пользователе по его логину или email
     * @param string $login_email
     * @return Users|null
     */
	public function getRowByLoginEmail(string $login_email):? self {

        return $this->newQuery()
            ->orWhere([
                'login' => $login_email,
                'email' => $login_email
            ])
            ->limit(1)
            ->first();
	}


    /**
     * Проверка повторения логина
     * @param string   $login
     * @param int|null $exception_user_id
     * @return bool
     */
    public function isUniqueLogin(string $login, int|null $exception_user_id = null): bool {

        $query = $this->newQuery()
            ->where([
                  'login' => $login,
              ]);

        if ($exception_user_id) {
            $query->where('id', '!=', $exception_user_id);
        }

        $count = $query
            ->limit(1)
            ->count();

        return $count == 0;
    }


    /**
     * Проверка повторения email
     * @param string   $email
     * @param int|null $exception_user_id
     * @return bool
     */
    public function isUniqueEmail(string $email, int $exception_user_id = null): bool {

        $query = $this->newQuery()
            ->where([
                'email' => $email,
            ]);

        if ($exception_user_id) {
            $query->where('id', '!=', $exception_user_id);
        }

        $count = $query
            ->limit(1)
            ->count();

        return $count == 0;
    }
}