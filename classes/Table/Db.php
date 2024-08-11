<?php
namespace Core3\Classes\Table;
use Core3\Classes\Init\Request;
use Core3\Classes;
use CoreUI\Table;
use CoreUI\Table\Exception;


/**
 *
 */
class Db extends Table\Adapters\Mysql {


    /**
     * @param Request|null $request
     * @throws Exception
     */
    public function __construct(Request $request = null) {

        $core_db = new Classes\Db();

        parent::__construct($core_db->db->getDriver()->getConnection()->getResource());


        if ($request) {
            $page = $request->getQuery('page') ?? 1;
            $page = is_numeric($page) ? max($page, 1) : 1;

            $page_count = $request->getQuery('count') ?? 25;
            $page_count = is_numeric($page_count) ? max($page_count, 1) : 25;

            $this->setPage($page, $page_count);
        }
    }
}