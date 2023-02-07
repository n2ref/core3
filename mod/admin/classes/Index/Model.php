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
            'memory'             => $server->getMemory(),
            'date_time'          => $server->getTime(),
            'uptime'             => $server->getUptime(),
            'disk_info'          => $server->getDiskInfo(),
            'network_info'       => $server->getNetworkInfo(),
            'network_interfaces' => $server->getNetworkInterfaces(),
            'database'           => [
                'type'    => $database_statistics['type'],
                'version' => $database_statistics['version'],
                'size'    => Tools::convertBytes($database_statistics['size'], 'm') . ' Mb',
            ],
            'php'                => [
                'version'            => phpversion(),
                'mem_limit'          => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_limit'       => Tools::convertBytes(Tools::getUploadMaxFileSize(), 'm') . 'Mb',
            ],
        ];


        return $server_info;
    }


    /**
     * @return array|array[]
     */
    public function getPhpInfo(): array {

        $entities_to_utf8 = function ($input) {
            // http://php.net/manual/en/function.html-entity-decode.php#104617
            return preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
            }, $input);
        };

        $plain_text = function ($input) use ($entities_to_utf8) {
            return trim(html_entity_decode($entities_to_utf8(strip_tags($input))));
        };

        $title_plain_text = function ($input) use ($plain_text) {
            return '# ' . $plain_text($input);
        };

        ob_start();
        phpinfo(-1);

        // Strip everything after the <h1>Configuration</h1> tag (other h1's)
        if ( ! preg_match('#(.*<h1[^>]*>\s*Configuration.*)<h1#s', ob_get_clean(), $matches)) {
            return [];
        }

        $phpinfo = ['phpinfo' => []];
        $input   = $matches[1];
        $matches = [];

        if (preg_match_all(
            '#(?:<h2.*?>(?:<a.*?>)?(.*?)(?:<\/a>)?<\/h2>)|' .
            '(?:<tr.*?><t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>(?:<t[hd].*?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
            $input,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $fn = strpos($match[0], '<th') === false
                    ? $plain_text
                    : $title_plain_text;

                if (strlen($match[1])) {
                    $phpinfo[$match[1]] = [];
                } elseif (isset($match[3])) {
                    $keys1                                = array_keys($phpinfo);
                    $phpinfo[end($keys1)][$fn($match[2])] = isset($match[4])
                        ? array($fn($match[3]), $fn($match[4]))
                        : $fn($match[3]);
                } else {
                    $keys1                  = array_keys($phpinfo);
                    $phpinfo[end($keys1)][] = $fn($match[2]);
                }
            }
        }

        return $phpinfo;
    }
}