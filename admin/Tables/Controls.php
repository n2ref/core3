<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Row;
use Core3\Classes\Db\Table;
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
     * @return AbstractRowGateway|null
     */
	public function getRowByTableRowId(string $table_name, string $row_id): ?AbstractRowGateway {

        $results = $this->select(function (Select $select) use ($table_name, $row_id) {
            $select
                ->where(['table_name' => $table_name])
                ->where(['row_id' => $row_id])
                ->limit(1);
        });

        return $results->current();
	}


    /**
     * @param string $table_name
     * @param string $row_id
     * @return AbstractRowGateway
     */
    public function createRow(string $table_name, string $row_id): AbstractRowGateway {

        $row = $this->getRowByTableRowId($table_name, $row_id);

        if ( ! $row) {
            $this->insert([
                'table_name' => $table_name,
                'row_id'     => $row_id,
                'version'    => 1,
            ]);

            $row = $this->getRowById($this->getLastInsertValue());
        }

        return $row;
    }


    /**
     * @param int $days
     * @return void
     */
    public function deleteOld(int $days = 30): void {

        $this->delete([
            'date_modify < ?' => date('Y-m-d H:i:s', strtotime("-{$days} days"))
        ]);
    }
}