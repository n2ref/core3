<?php
namespace Rowan\Admin\Tables;
use Rowan\Classes\Db\Table;


/**
 *
 */
class Controls extends Table {

	protected $table = "core_controls";


    /**
     * @param string $table_name
     * @param string $row_id
     * @return self|null
     */
	public function getRowByTableRowId(string $table_name, string $row_id):? self {

        return $this->newQuery()
            ->where('table_name', $table_name)
            ->where('row_id', $row_id)
            ->limit(1)
            ->first();
	}


    /**
     * @param string $table_name
     * @param string $row_id
     * @param bool   $create Создавать запись, если ее нет
     * @return self
     */
    public function createRow(string $table_name, string $row_id, bool $create = true): self {

        $row = $this->getRowByTableRowId($table_name, $row_id);

        if ( ! $row) {
            if ( ! $create) {
                $row = $this->newInstance([
                    'table_name' => $table_name,
                    'row_id'     => $row_id,
                    'version'    => 0,
                ]);

            } else {
                $row = $this->create([
                    'table_name' => $table_name,
                    'row_id'     => $row_id,
                    'version'    => 1,
                ]);
            }
        }

        return $row;
    }


    /**
     * @param int $days
     * @return void
     */
    public function deleteOld(int $days = 30): void {

        $rows = $this->newQuery()
            ->where('updated_at', '<', date('Y-m-d H:i:s', strtotime("-{$days} days")))
            ->get();

        foreach ($rows as $row) {
            $row->delete();
        }
    }
}