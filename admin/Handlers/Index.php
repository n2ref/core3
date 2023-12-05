<?php
namespace Core3\Mod\Admin\Handlers;
use Core3\Classes\Handler;
use Core3\Classes\Http\Request;
use Core3\Mod\Admin\Classes;


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
    public function getDbVariables(Request $request): array {

        return (new Classes\Index\View())->getTableDbVariables();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getSystemProcessList(Request $request): array {

        return (new Classes\Index\View())->getTableProcessList();
    }
}