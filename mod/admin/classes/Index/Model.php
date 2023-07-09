<?php
namespace Core3\Mod\Admin\Index;
use Core3\Classes\Common;
use Core3\Classes\Tools;
use Core3\Mod\Admin\Index\SysInfo\Database;
use Core3\Mod\Admin\Index\SysInfo\Server;


/**
 *
 */
class Model extends Common {


    /**
     * @return array
     */
    public function getServerInfo(): array {

        $server              = new Server();
        $database_statistics = (new Database())->getStatistics();

        $server_info = [
            'core_version'       => $this->getSystemVersion(),
            'os_name'            => $server->getOSName(),
            'loadavg'            => $server->getLoadAvg(),
            'cpu_name'           => $server->getCpuName(),
            'cpu_load'           => $server->getCpuLoad(),
            'memory'             => $server->getMemory(),
            'date_time'          => $server->getTime(),
            'uptime'             => $server->getUptime(),
            'disk_info'          => $server->getDiskInfo(),
            'network_info'       => $server->getNetworkInfo(),
            'network_interfaces' => $server->getNetworkInterfaces(),
            'database'           => [
                'type'    => $database_statistics['type'],
                'version' => $database_statistics['version'],
                'size'    => $database_statistics['size'],
            ],
            'php' => [
                'version'            => phpversion(),
                'mem_limit'          => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_limit'       => Tools::getUploadMaxFileSize(),
                'extensions'         => Tools::getUploadMaxFileSize(),
            ],
        ];


        return $server_info;
    }
}