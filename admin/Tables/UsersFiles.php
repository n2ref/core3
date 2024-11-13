<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\RowFile;
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
     * @param string|null $object_type
     * @param int         $limit
     * @return RowFile|null
     */
	public function getRowsByUser(int $user_id, string $object_type = null, int $limit = 0):? RowFile {

        $results = $this->select(function (Select $select) use ($user_id, $object_type, $limit) {
            $select->where(['ref_id' => $user_id]);

            if ($object_type) {
                $select->where(['object_type' => $object_type]);
            }

            if ($limit > 0) {
                $select->limit($limit);
            }
        });

        return $results->current();
	}
}