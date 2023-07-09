<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Predicate;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;

/**
 *
 */
class UsersSession extends Table {

    protected $table = 'core_users_sessions';


    /**
     * Количество активных пользователей
     * @param \DateTime $start_at
     * @return int
     */
    public function getCountActive(\DateTime $start_at): int {

        $results = $this->select(function (Select $select) use ($start_at) {

            $select
                ->columns([
                    'id'    => 'id',
                    'count' => new Expression('COUNT(DISTINCT user_id)')
                ])
                ->where(
                    (new Where())->greaterThan('date_last_activity', $start_at->format('Y-m-d H:i:s'))
                )
                ->limit(1);
        });

        return (int)$results->current()->count;
    }


    /**
     * Получаем информацию о сессии по токену
     * @param string $refresh_token
     * @return AbstractRowGateway|null
     */
    public function getRowByRefreshToken(string $refresh_token):? AbstractRowGateway {

        $results = $this->select(function (Select $select) use ($refresh_token) {
            $select
                ->where('refresh_token = ?', $refresh_token)
                ->limit(1);
        });

        return $results->current();
    }
}
