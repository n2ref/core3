<?php
namespace Core3\Mod\Admin\Handlers;
use Core3\Classes\Handler;
use Core3\Classes\Init\Request;
use Core3\Classes\Tools;
use Core3\Mod\Admin\Classes;
use CoreUI\Table;
use CoreUI\Table\Adapters\Data\Search;



/**
 *
 */
class Index extends Handler {

    /**
     * @param Request $request
     * @return string[]
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function clearCache(Request $request): array {

        $this->cache->clear();

        return [ 'status' => 'success' ];
    }


    /**
     * @param Request $request
     * @return string
     */
    public function getPhpInfo(Request $request): string {

        return (new Classes\Index\SysInfo\PhpInfo())->getPhpinfo();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getDbConnections(Request $request): array {

        return (new Classes\Index\View())->getTableDbConnections();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getDbConnectionsRecords(Request $request): array {

        $table = new Table\Adapters\Data();
        $table->setPageCount(0);

        $sort = $request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort);
        }

        $connections = (new Classes\Index\SysInfo\Database())->getConnections();

        $table->setData($connections);

        return $table->getResult();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getDbVariables(Request $request): array {

        return (new Classes\Index\View())->getTableDbVariables();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getSystemProcess(Request $request): array {

        return (new Classes\Index\View())->getTableProcess();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getSystemProcessList(Request $request): array {

        $table = new Table\Adapters\Data();
        $table->setPageCount(0);

        $sort = $request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort);
        }


        $search = $request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'command' => (new Search\Text()),
            ]);
        }


        $connections = (new Classes\Index\SysInfo\Server())->getProcessList();
        $connections = Tools::arrayMultisort($connections, [ [ 'field' => 'cpu', 'order' => 'desc' ] ]);

        $table->setData($connections);

        return $table->getResult();
    }
}