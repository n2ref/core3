<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Row;
use Core3\Classes\Db\Table;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Select;

/**
 *
 */
class UsersData extends Table {


	protected $table = "core_users_data";


    /**
     * Получение данных пользователя
     * @param int $user_id
     * @param string $name
     * @return Row|null
     */
    public function getRowByUserName(int $user_id, string $name):? Row {

        $results = $this->select(function (Select $select) use ($user_id, $name) {
            $select
                ->where([
                    'user_id' => $user_id,
                    'name' => $name
                ])
                ->limit(1);
        });

        return $results->current();
    }
}