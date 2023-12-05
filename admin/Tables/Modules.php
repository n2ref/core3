<?php
namespace Core3\Mod\Admin\Tables;

use Core3\Classes\Db\Table;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Modules extends Table {

    protected $table = 'core_modules';


    /**
     * @return ResultSetInterface
     */
    public function getRowsByActive(): ResultSetInterface {

        $param_name = "active_modules";

        if ($this->hasStaticCache($param_name)) {
            $result = $this->getStaticCache($param_name);

        } else {
            $result = $this->fetchAll(function (Select $select) {
                $select
                    ->where([
                        'is_active_sw' => 'Y',
                    ])
                    ->order('seq');
            });

            $this->setStaticCache($param_name, $result);
        }

        return $result;
    }


    /**
     * @return int
     */
    public function getCount(): int {

        $result = $this->select(function (Select $select) {
            $select->columns([
                'id'    => 'id',
                'count' => new Expression('COUNT(1)'),
            ]);
        });

        return (int)($result->current()->count ?? 0);
    }


    /**
     * @return ResultSetInterface
     */
    public function getRowsByActiveVisible(): ResultSetInterface {

        $result = $this->fetchAll(function (Select $select) {
            $select
                ->where([
                    'is_active_sw'  => 'Y',
                    'is_visible_sw' => 'Y',
                ])
                ->order('seq');
        });

        return $result;
    }
}