<?php
namespace Rowan\Admin\Tables;
use Rowan\Classes\Db\Row;
use Rowan\Classes\Db\Table;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;

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
     * @return self
     */
    public function createRow(string $table_name, string $row_id): self {

        $row = $this->getRowByTableRowId($table_name, $row_id);

        if ( ! $row) {
            $row = $this->create([
                'table_name' => $table_name,
                'row_id'     => $row_id,
                'version'    => 1,
            ]);
        }

        return $row;
    }


    /**
     * @param int $days
     * @return void
     */
    public function deleteOld(int $days = 30): void {

        $rows = $this->newQuery()
            ->where('date_modify', '<', date('Y-m-d H:i:s', strtotime("-{$days} days")))
            ->get();

        foreach ($rows as $row) {
            $row->delete();
        }
    }
}