<?php
namespace Core3\Mod\Admin\Index\SysInfo\Os;


/**
 *
 */
class Linux implements OperatingSystem {

    /**
     * @return array
     */
	public function getMemory(): array {

		$data = [];

		try {
			$meminfo = $this->readContent('/proc/meminfo');
		} catch (\RuntimeException $e) {
			return $data;
		}

		$matches = [];
		$pattern = '/(?<Key>(?:MemTotal|MemFree|MemAvailable|SwapTotal|SwapFree)+):\s+(?<Value>\d+)\s+(?<Unit>\w{2})/';

		$result = preg_match_all($pattern, $meminfo, $matches);
		if ($result === 0 || $result === false) {
			return $data;
		}

		foreach ($matches['Key'] as $i => $key) {
			// Value is always in KB: https://github.com/torvalds/linux/blob/c70672d8d316ebd46ea447effadfe57ab7a30a50/fs/proc/meminfo.c#L58-L60
			$value = (int)((int)$matches['Value'][$i] / 1024);

			switch ($key) {
				case 'MemTotal':     $data['mem_total']     = $value; break;
				case 'MemFree':      $data['mem_free']      = $value; break;
				case 'MemAvailable': $data['mem_available'] = $value; break;
				case 'SwapTotal':    $data['swap_total']    = $value; break;
				case 'SwapFree':     $data['swap_free']     = $value; break;
			}
		}

		return $data;
	}


    /**
     * @return string
     */
	public function getCpuName(): string {

        $data = 'Unknown Processor';

		try {
			$cpuinfo = $this->readContent('/proc/cpuinfo');
		} catch (\RuntimeException $e) {
			return $data;
		}

		$matches = [];
		$pattern = '/model name\s:\s(.+)/';

		$result = preg_match_all($pattern, $cpuinfo, $matches);
		if ($result === 0 || $result === false) {
			// For Raspberry Pi 4B
			$pattern = '/Model\s+:\s(.+)/';
			$result = preg_match_all($pattern, $cpuinfo, $matches);
			if ($result === 0 || $result === false) {
				return $data;
			}
		}

        $model   = $matches[1][0];
        $pattern = '/processor\s+:\s(.+)/';

		$result = preg_match_all($pattern, $cpuinfo, $matches);
		$cores  = count($matches[1]);

		if ($cores === 1) {
			$data = $model . ' (1 core)';
		} else {
			$data = $model . ' (' . $cores . ' cores)';
		}

		return $data;
	}


    /**
     * @return string
     */
	public function getTime(): string {
		return (string)shell_exec('date');
	}


    /**
     * @return int
     */
	public function getUptime(): int {
		$data = -1;

		try {
			$uptime = $this->readContent('/proc/uptime');
		} catch (\RuntimeException $e) {
			return $data;
		}

		[$uptimeInSeconds,] = array_map('intval', explode(' ', $uptime));

		return $uptimeInSeconds;
	}


    /**
     * @return array
     */
	public function getNetworkInfo(): array {

        $result             = [];
        $result['hostname'] = \gethostname();
        $result['dns']      = shell_exec('cat /etc/resolv.conf |grep -i \'^nameserver\'|head -n1|cut -d \' \' -f2');
        $result['gateway']  = shell_exec('ip route | awk \'/default/ { print $3 }\'');;
        return $result;
	}


    /**
     * @return array
     */
	public function getNetworkInterfaces(): array {

		$interfaces = glob('/sys/class/net/*') ?: [];
		$result = [];

        foreach ($interfaces as $interface) {
            $iface              = [];
            $iface['interface'] = basename($interface);
            $iface['mac']       = shell_exec('ip addr show dev ' . $iface['interface'] . ' | grep "link/ether " | cut -d \' \' -f 6  | cut -f 1 -d \'/\'');
            $iface['ipv4']      = shell_exec('ip addr show dev ' . $iface['interface'] . ' | grep "inet " | cut -d \' \' -f 6  | cut -f 1 -d \'/\'');
            $iface['ipv6']      = shell_exec('ip -o -6 addr show ' . $iface['interface'] . ' | sed -e \'s/^.*inet6 \([^ ]\+\).*/\1/\'');

            if ($iface['interface'] !== 'lo') {
                $iface['status'] = shell_exec('cat /sys/class/net/' . $iface['interface'] . '/operstate');
                $iface['speed']  = (int)shell_exec('cat /sys/class/net/' . $iface['interface'] . '/speed');
                if (isset($iface['speed']) && $iface['speed'] > 0) {
                    if ($iface['speed'] >= 1000) {
                        $iface['speed'] = $iface['speed'] / 1000 . ' Gbps';
                    } else {
                        $iface['speed'] = $iface['speed'] . ' Mbps';
                    }
                } else {
                    $iface['speed'] = 'unknown';
                }

                $duplex = shell_exec('cat /sys/class/net/' . $iface['interface'] . '/duplex');

                if (isset($duplex) && $duplex !== '') {
                    $iface['duplex'] = 'Duplex: ' . $duplex;
                } else {
                    $iface['duplex'] = '';
                }

            } else {
                $iface['status'] = 'up';
                $iface['speed']  = 'unknown';
                $iface['duplex'] = '';
            }
            $result[] = $iface;
        }

        return $result;
	}


    /**
     * @return array
     */
	public function getDiskInfo(): array {

		$data = [];

		try {
			$disks = $this->executeCommand('df -TPk');
		} catch (\RuntimeException $e) {
			return $data;
		}

        $matches = [];
        $pattern = '/^(?<Filesystem>[\S]+)\s*(?<Type>[\S]+)\s*(?<Blocks>\d+)\s*(?<Used>\d+)\s*(?<Available>\d+)\s*(?<Capacity>\d+%)\s*(?<Mounted>[\w\/-]+)$/m';
        $result  = preg_match_all($pattern, $disks, $matches);

		if ($result === 0 || $result === false) {
			return $data;
		}

		foreach ($matches['Filesystem'] as $i => $filesystem) {
			if (in_array($matches['Type'][$i], ['tmpfs', 'devtmpfs', 'squashfs', 'overlay'], false)) {
				continue;
            } elseif (in_array($matches['Mounted'][$i], ['/etc/hostname', '/etc/hosts'], false)) {
                continue;
            }

            $data[] = [
                'device'    => $filesystem,
                'fs'        => $matches['Type'][$i],
                'used'      => (int)((int)$matches['Used'][$i] / 1024),
                'available' => (int)((int)$matches['Available'][$i] / 1024),
                'percent'   => $matches['Capacity'][$i],
                'mount'     => $matches['Mounted'][$i],
            ];
        }

		return $data;
	}


    /**
     * @return array
     */
	public function getThermalZones(): array {

        $thermalZones = glob('/sys/class/thermal/thermal_zone*') ?: [];
        $result       = [];

        foreach ($thermalZones as $thermalZone) {
            $tzone = [];
            try {
                $tzone['hash'] = md5($thermalZone);
                $tzone['type'] = $this->readContent($thermalZone . '/type');
                $tzone['temp'] = (float)((int)($this->readContent($thermalZone . '/temp')) / 1000);
            } catch (\RuntimeException $e) {
                continue;
            }
            $result[] = $tzone;
        }

        return $result;
    }


    /**
     * @param string $filename
     * @return string
     */
	protected function readContent(string $filename): string {

		$data = @file_get_contents($filename);

		if ($data === false || $data === '') {
			throw new \RuntimeException('Unable to read: "' . $filename . '"');
		}

		return $data;
	}


    /**
     * @param string $command
     * @return string
     */
	protected function executeCommand(string $command): string {

		$output = @shell_exec(escapeshellcmd($command));

		if ($output === false || $output === null || $output === '') {
			throw new \RuntimeException('No output for command: "' . $command . '"');
		}

		return $output;
	}
}
