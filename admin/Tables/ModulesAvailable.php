<?php
namespace Core3\Mod\Admin\Tables;
use Core3\Classes\Db\Table;
use Laminas\Db\ResultSet\AbstractResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;


/**
 *
 */
class ModulesAvailable extends Table {

    protected $table = 'core_modules_available';


    /**
     * @param string      $name
     * @param string|null $vendor
     * @return AbstractResultSet
     */
    public function getRowsByNameVendor(string $name, string $vendor = null): AbstractResultSet {

        return $this->select(function (Select $select) use ($name, $vendor) {
            $select->where([ 'name'   => $name ]);

            if ($vendor) {
                $select->where([ 'vendor' => $vendor ]);
            }
        });
    }


    /**
     * @return int
     */
    public function getCount(): int {

        $result = $this->select(function (Select $select) {
            $select->columns([
                'id'    => new Expression('0'),
                'count' => new Expression('COUNT(1)'),
            ]);
        });

        return (int)($result->current()->count ?? 0);
    }
}