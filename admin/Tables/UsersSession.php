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
                    'id'    => new Expression('0'),
                    'count' => new Expression('COUNT(DISTINCT user_id)')
                ])
                ->where(
                    (new Where())->greaterThan('date_last_activity', $start_at->format('Y-m-d H:i:s'))
                )
                ->limit(1);
        });

        return (int)$results->current()?->count;
    }


    /**
     * Количество сессий у пользователя
     * @param int $user_id
     * @return int
     */
    public function getCountUser(int $user_id): int {

        $results = $this->select(function (Select $select) use ($user_id) {

            $select
                ->columns([
                    'id'    => new Expression('0'),
                    'count' => new Expression('COUNT(1)')
                ])
                ->where(
                    (new Where())->equalTo('user_id', $user_id)
                )
                ->limit(1);
        });

        return (int)$results->current()?->count;
    }
}
