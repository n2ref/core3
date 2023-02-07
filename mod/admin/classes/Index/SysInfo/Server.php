<?php
namespace Core3\Mod\Admin\Index\SysInfo;


/**
 *
 */
class Server {

    private Os\OperatingSystem $os;

    /**
     * Os constructor.
     */
    public function __construct() {

        if (PHP_OS === 'FreeBSD') {
            $this->os = new Os\FreeBSD();
        } else {
            $this->os = new Os\Linux();
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
}