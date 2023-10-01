<?php
namespace Core3\Mod\Admin\Classes\Index\SysInfo;
use Core3\Mod\Admin\Classes\Index;

/**
 *
 */
class Server {

    private Index\SysInfo\Os\OperatingSystem $os;

    /**
     * Os constructor.
     */
    public function __construct() {

        if (PHP_OS === 'FreeBSD') {
            $this->os = new Index\SysInfo\Os\FreeBSD();
        } else {
            $this->os = new Index\SysInfo\Os\Linux();
        }
    }


    /**
     * @return string
     */
    public function getHostname(): string {
        return (string)gethostname();
    }


    /**
     * Get name of the operating system.
     */
    public function getOSName(): string {
        return PHP_OS . ' ' . php_uname('r') . ' ' . php_uname('m');
    }


    /**
     * @return array
     */
    public function getMemory(): array {
        return $this->os->getMemory();
    }


    /**
     * @return string
     */
    public function getCpuName(): string {
        return $this->os->getCpuName();
    }


    /**
     * @return string
     */
    public function getCpuLoad(): string {
        return $this->os->getCpuLoad();
    }


    /**
     * @return array|false
     */
    public function getLoadAvg(): bool|array {

        return sys_getloadavg();
    }


    /**
     * @return string
     */
    public function getTime(): string {
        return $this->os->getTime();
    }


    /**
     * @return int
     */
    public function getUptime(): int {
        return $this->os->getUptime();
    }


    /**
     * @return array
     */
    public function getDiskInfo(): array {
        return $this->os->getDiskInfo();
    }

    /**
     * Get diskdata will return a numerical list with two elements for each disk (used and available) where all values are in gigabyte.
     * [
     *        [used => 0, available => 0],
     *        [used => 0, available => 0],
     * ]
     *
     * @return array<array-key, array>
     */
    public function getDiskData(): array {
        $data = [];

        foreach ($this->os->getDiskInfo() as $disk) {
            $data[] = [
                round($disk->getUsed() / 1024 , 1),
                round($disk->getAvailable() / 1024, 1)
            ];
        }

        return $data;
    }


    /**
     * @return array
     */
    public function getNetworkInfo(): array {

        return $this->os->getNetworkInfo();
    }


    /**
     * @return array
     */
    public function getNetworkInterfaces(): array {

        return $this->os->getNetworkInterfaces();
    }


    /**
     * @return array
     */
    public function getThermalZones(): array {

        return $this->os->getThermalZones();
    }


    /**
     * Список процессов
     * @return array
     */
    public function getProcessList(): array {

        $output = [];
        $cmd    = sprintf("ps -o pid,ruser,rgroup,lstart,pcpu,pmem,size,command");
        exec($cmd, $output);

        $result = [];

        if (count($output) > 1) {
            $first = true;
            foreach ($output as $output_line) {
                if ($first) {
                    $first = false;
                    continue;
                }

                $regex = implode('', [
                    "~(?P<pid>[0-9]+)\s+",
                    "(?P<user>[0-9a-z_\-]+)\s+",
                    "(?P<group>[0-9a-z_\-]+)\s+",
                    "(?P<start>.{24})\s+",
                    "(?P<cpu>[0-9\.]+)\s+",
                    "(?P<mem>[0-9\.]+)\s+",
                    "(?P<size>[0-9]+)\s+",
                    "(?P<command>.+)$~"
                ]);

                if (preg_match($regex, $output_line, $matches)) {
                    $result[] = $matches;
                }
            }
        }

        return $result;
    }
}