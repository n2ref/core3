<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Select;

/**
 *
 */
class UsersFiles extends Table {


	protected $table = "core_users_files";


    /**
     * Получение файлов указанного пользователя
     * @param int         $user_id
     * @param string|null $field_name
     * @param int         $limit
     * @return AbstractRowGateway|null
     */
	public function getRowsByUser(int $user_id, string $field_name = null, int $limit = 0):? AbstractRowGateway {

        $results = $this->select(function (Select $select) use ($user_id, $field_name, $limit) {
            $select->where(['ref_id' => $user_id]);

            if ($field_name) {
                $select->where(['field_name' => $field_name]);
            }

            if ($limit > 0) {
                $select->limit($limit);
            }
        });

        return $results->current();
	}
}