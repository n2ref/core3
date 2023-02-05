<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Select;

/**
 *
 */
class UsersSession extends Table {

    protected $table = 'core_users_sessions';


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