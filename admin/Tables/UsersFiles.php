<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Row;
use Core3\Classes\Db\TableFiles;
use Laminas\Db\Sql\Select;

/**
 *
 */
class UsersFiles extends TableFiles {


	protected $table = "core_users_files";


    /**
     * Получение файлов указанного пользователя
     * @param int         $user_id
     * @param string|null $field_name
     * @param int         $limit
     * @return Row|null
     */
	public function getRowsByUser(int $user_id, string $field_name = null, int $limit = 0):? Row {

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