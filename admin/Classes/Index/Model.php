<?php
namespace Core3\Mod\Admin\Classes\Index;
use Core3\Classes\Common;
use Core3\Classes\Tools;
use Core3\Mod\Admin\Classes\Index\SysInfo\Database;
use Core3\Mod\Admin\Classes\Index\SysInfo\Server;


/**
 *
 */
class Model extends Common {


    /**
     * @return array
     */
    public function getSystem(): array {

        $server = new Server();

        $uptime = $server->getUptime();
        $days   = floor($uptime / 60 / 60 / 24);
        $hours  = floor(($uptime - ($days * 24 * 60 * 60)) / 60 / 60);
        $mins   = floor(($uptime - ($days * 24 * 60 * 60) - ($hours * 60 * 60)) / 60);

        return [
            'osName'       => $server->getOSName(),
            'loadAvg'      => $server->getLoadAvg(),
            'cpuName'      => $server->getCpuName(),
            'cpuLoad'      => $server->getCpuLoad(),
            'memory'       => $server->getMemory(),
            'systemTime'   => $server->getTime(),
            'network'      => $server->getNetworkInfo(),
            'uptime'       => [
                'days'  => $days,
                'hours' => $hours,
                'min'   => $mins,
            ],
        ];
    }


    /**
     * @return array
     */
    public function getDisksInfo(): array {

        $server = new Server();

        return $server->getDiskInfo();
    }


    /**
     * @return array
     */
    public function getNetworkInfo(): array {

        $server = new Server();

        return $server->getNetworkInterfaces();
    }


    /**
     * @return array
     */
    public function getPhpInfo(): array {

        $php_info = (new SysInfo\PhpInfo())->getPhpStatistics();

        return [
            'version'           => $php_info['version'],
            'memLimit'          => Tools::convertSizeToBytes($php_info['memory_limit']),
            'maxExecutionTime'  => $php_info['max_execution_time'],
            'uploadMaxFilesize' => Tools::convertSizeToBytes($php_info['upload_max_filesize']),
            'extensions'        => $php_info['extensions'],
        ];
    }


    /**
     * @return array
     */
    public function getDbInfo(): array {

        $database_statistics = (new Database())->getStatistics();

        return [
            'type'    => $database_statistics['type'],
            'version' => $database_statistics['version'],
            'size'    => $database_statistics['size'],
            'host'    => $this->config?->system?->db?->base?->params?->hostname,
            'name'    => $this->config?->system?->db?->base?->params?->database,
        ];
    }
}