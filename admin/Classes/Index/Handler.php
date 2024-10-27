<?php
namespace Core3\Mod\Admin\Classes\Index;
use Core3\Classes;
use Core3\Classes\Request;
use Core3\Classes\Tools;
use CoreUI\Table;
use CoreUI\Table\Adapters\Data\Search;
use CoreUI\Panel\Control;


/**
 *
 */
class Handler extends Classes\Handler {


    /**
     * @param Request $request
     * @return array
     * @throws \Core3\Exceptions\DbException
     */
    public function getDashboard(Request $request): array {

        $service_info = (new Model())->getServerInfo();
        $view         = new View();


        $content = [
            $this->getCssModule('admin', 'assets/index/css/admin.css'),
            $this->getJsModule('admin', 'assets/index/js/admin.index.js'),
        ];

        $panel_admin = new \CoreUI\Panel();
        $panel_admin->setTitle('Общие сведения');
        $panel_admin->addControls([
            (new Control\Button('<i class="bi bi-arrow-clockwise"></i>'))->setOnClick('Core.menu.reload()')
        ]);
        $panel_admin->setContent($view->getTableCommon($service_info));
        $content[] = $panel_admin->toArray();




        $layout_sys = new \CoreUI\Layout();
        $layout_sys->setJustify($layout_sys::JUSTIFY_AROUND);
        $layout_sys->setDirection($layout_sys::DIRECTION_ROW);
        $layout_sys->addItems()->setWidth(200)->setContent($view->getChartCpu($service_info));
        $layout_sys->addItems()->setWidth(200)->setContent($view->getChartMem($service_info));
        $layout_sys->addItems()->setWidth(200)->setContent($view->getChartSwap($service_info));
        $layout_sys->addItems()->setWidth(200)->setContent($view->getChartDisks($service_info));

        $panel_system = new \CoreUI\Panel();
        $panel_system->setTitle('Системная информация');
        $panel_system->addControls([
            (new Control\Button('<i class="bi bi-list-ul"></i>'))->setOnClick('adminIndex.showSystemProcessList()')
        ]);
        $panel_system->setContent([
            $layout_sys->toArray(),
            '<br><br>',
            $view->getTableSystem($service_info)
        ]);

        $content[] = $panel_system->toArray();




        $panel_php = new \CoreUI\Panel();
        $panel_php->setTitle('Php');
        $panel_php->addControls([
            (new Control\Button('<i class="bi bi-info"></i>'))->setOnClick('adminIndex.showPhpInfo()')
        ]);
        $panel_php->setContent($view->getPhp());


        $panel_db = new \CoreUI\Panel();
        $panel_db->setTitle('База данных');
        $panel_db->addControls([
            (new Control\Button('<i class="bi bi-info"></i>'))->setOnClick('adminIndex.showDbVariablesList()'),
            (new Control\Button('<i class="bi bi-plugin"></i>'))->setOnClick('adminIndex.showDbProcessList()')
        ]);

        $panel_db->setContent($view->getDbInfo($service_info));



        $layout_php_db = new \CoreUI\Layout();
        $item = $layout_php_db->addItems();
        $item->setWidthColumn(12);
        $item->addSize('lg')->setWidthColumn(6);
        $item->setContent($panel_php->toArray());

        $item = $layout_php_db->addItems();
        $item->setWidthColumn(12);
        $item->addSize('lg')->setWidthColumn(6);
        $item->setContent($panel_db->toArray());

        $content[] = $layout_php_db->toArray();




        $panel_disks = new \CoreUI\Panel();
        $panel_disks->setTitle('Использование дисков');
        $panel_disks->setContent($view->getTableDisks($service_info));
        $content[] = $panel_disks->toArray();


        $panel_network = new \CoreUI\Panel();
        $panel_network->setTitle('Сеть');
        $panel_network->setContent($view->getTableNetworks($service_info));
        $content[] = $panel_network->toArray();


        $layout = new \CoreUI\Layout();
        $layout->addSize('sm')->setJustify($layout::JUSTIFY_START);
        $layout->addSize('md')->setJustify($layout::JUSTIFY_CENTER);
        $layout->addItems()
            ->setWidth(1024)
            ->setWidthMax('100%')
            ->setWidthMin(400)
            ->setContent($content);

        return $layout->toArray();
    }


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

        return (new SysInfo\PhpInfo())->getPhpinfo();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getDbConnections(Request $request): array {

        return (new View())->getTableDbConnections();
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

        $connections = (new SysInfo\Database())->getConnections();

        $table->setData($connections);

        return $table->getResult();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getDbVariables(Request $request): array {

        return (new View())->getTableDbVariables();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getSystemProcess(Request $request): array {

        return (new View())->getTableProcess();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getSystemProcessRecords(Request $request): array {

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


        $connections = (new SysInfo\Server())->getProcessList();
        $connections = Tools::arrayMultisort($connections, [ [ 'field' => 'cpu', 'order' => 'desc' ] ]);

        $table->setData($connections);

        return $table->getResult();
    }
}