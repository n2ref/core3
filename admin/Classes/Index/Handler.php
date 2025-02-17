<?php
namespace Core3\Mod\Admin\Classes\Index;
use Core3\Classes;
use Core3\Classes\Tools;
use CoreUI\Table;
use CoreUI\Table\Adapters\Data\Search;
use CoreUI\Panel\Control;


/**
 *
 */
class Handler extends Classes\Handler {


    /**
     * @return array
     * @throws \Core3\Exceptions\DbException|\Exception
     */
    public function getDashboardInfo(): array {

        $modules_count    = $this->modAdmin->tableModules->getRowsByActive()->count();
        $count_users      = $this->modAdmin->tableUsers->getCount();
        $count_active_day = $this->modAdmin->tableUsersSession->getCountActive(new \DateTime(date('Y-m-d 00:00:00')));
        $count_active_now = $this->modAdmin->tableUsersSession->getCountActive(new \DateTime('-5 min'));

        $model = new Model();

        return [
            'sys'    => $model->getSystem(),
            'php'    => $model->getPhpInfo(),
            'db'     => $model->getDbInfo(),
            'disks'  => $model->getDisksInfo(),
            'net'    => $model->getNetworkInfo(),
            'common' => [
                'version'             => $this->getSystemVersion(),
                'countModules'        => $modules_count,
                'countUsers'          => $count_users,
                'countUsersActiveDay' => $count_active_day,
                'countUsersActiveNow' => $count_active_now,
                'cacheType'           => $this->config?->system?->cache?->adapter ?: '-',
            ],
        ];
    }


    /**
     * @return string[]
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     */
    public function clearCache(): array {

        $this->cache->clear();

        return [ 'status' => 'success' ];
    }


    /**
     * @return string
     */
    public function getPhpInfo(): string {

        return (new SysInfo\PhpInfo())->getPhpinfo();
    }


    /**
     * @return array
     */
    public function getDbConnections(): array {

        $table = new Table\Adapters\Data();
        $table->setPageCount(0);

        $sort = $this->request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort);
        }

        $connections = (new SysInfo\Database())->getConnections();
        $connections = Tools::arrayMultisort($connections, [ 'Info' => 'desc' ]);

        $table->setData($connections);

        return $table->getResult();
    }


    /**
     * @return array
     */
    public function getDbVariables(): array {

        $table = new Table\Adapters\Data();
        $table->setPageCount(0);

        $sort = $this->request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort);
        }


        $search = $this->request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'search' => (new Search\Text()),
            ]);
        }


        $variables = (new SysInfo\Database())->getVariables();
        foreach ($variables as $key => $variable) {
            $variables[$key]['search'] = "{$variable['name']}|{$variable['value']}";
        }

        $variables = Tools::arrayMultisort($variables, [ 'name' => 'asc' ]);

        $table->setData($variables);
        return $table->getResult();
    }


    /**
     * @return array
     */
    public function getSystemProcessRecords(): array {

        $table = new Table\Adapters\Data();
        $table->setPageCount(0);

        $sort = $this->request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort);
        }


        $search = $this->request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'command' => (new Search\Text()),
            ]);
        }


        $connections = (new SysInfo\Server())->getProcessList();
        $connections = Tools::arrayMultisort($connections, [
            'cpu' => 'desc'
        ]);

        $table->setData($connections);

        return $table->getResult();
    }
}