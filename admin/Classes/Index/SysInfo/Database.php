<?php
namespace Core3\Mod\Admin\Classes\Index\SysInfo;
use Core3\Classes\Db;


/**
 *
 */
class Database extends Db {


    /**
     * @return array
     */
	public function getStatistics(): array {

        return [
            'type'    => $this->getType(),
            'version' => $this->databaseVersion(),
            'size'    => $this->databaseSize(),
        ];
	}


    /**
     * @return array
     */
    public function getConnections(): array {

        $connections = [];

        switch ($this->getType()) {
            case 'Pdo_Mysql':
            case 'mysql':
            case 'pgsql':
                $connections = $this->db->fetchAll('SHOW FULL PROCESSLIST');
                break;
        }

        return $connections;
    }


    /**
     * @return array
     */
    public function getVariables(): array {

        $variables = [];

        switch ($this->getType()) {
            case 'Pdo_Mysql':
            case 'mysql':
            case 'pgsql':
                $variables_raw = $this->db->fetchAll('SHOW VARIABLES');

                foreach ($variables_raw as $variable) {
                    $variables[] = [
                        'name'  => $variable['Variable_name'] ?? null,
                        'value' => $variable['Value'] ?? null,
                    ];
                }
                break;
        }

        return $variables;
    }


    /**
     * @return mixed
     */
    protected function getType(): string {

        return (string)$this->config?->system?->database?->adapter ?: 'mysql';
    }


    /**
     * @return string
     */
	protected function databaseVersion(): string {

		switch ($this->getType()) {
			case 'sqlite':
			case 'sqlite3':
				$sql = "SELECT sqlite_version() AS version";
				break;

			case 'oci':
				$sql = "SELECT VERSION FROM PRODUCT_COMPONENT_VERSION";
				break;

			case 'Pdo_Mysql':
			case 'mysql':
			case 'pgsql':
			default:
				$sql = "SELECT VERSION() AS version";
				break;
		}

		try {
            $version = $this->db->fetchOne($sql);


			if ($version) {
				return $this->cleanVersion($version);
			}

		} catch (\Exception $e) {
            // ignore
		}

		return 'N/A';
	}


	/**
	 * Copy of phpBB's get_database_size()
	 * @link https://github.com/phpbb/phpbb/blob/release-3.1.6/phpBB/includes/functions_admin.php#L2908-L3043
	 * @return float|null
	 * @copyright (c) phpBB Limited <https://www.phpbb.com>
	 * @license GNU General Public License, version 2 (GPL-2.0)
	 */
	protected function databaseSize():? float {

		$database_size = false;

		// This code is heavily influenced by a similar routine in phpMyAdmin 2.2.0
		switch ($this->getType()) {
			case 'mysql':
			case 'Pdo_Mysql':
                $mysqlEngine = ['MyISAM', 'InnoDB', 'Aria'];
                $db_name     = $this->config->system->database->params->database;

				$result        = $this->db->query("SHOW TABLE STATUS FROM `{$db_name}`")->execute();
				$database_size = 0;


                foreach ($result as $row) {
                    if (isset($row['Engine']) && in_array($row['Engine'], $mysqlEngine)) {
                        $database_size += $row['Data_length'] + $row['Index_length'];
                    }
                }
				break;

			case 'sqlite':
			case 'sqlite3':
                $host = $this->config->system->database->params->host;

				if (file_exists($host)) {
					$database_size = filesize($host);

				} else {
                    // FIXME Не будет работать, исправить
					$params = $this->db->getInner()->getParams();

					if (file_exists($params['path'])) {
						$database_size = filesize($params['path']);
					}
				}
				break;

			case 'pgsql':
				$sql = "
                    SELECT proname
					FROM pg_proc
					WHERE proname = 'pg_database_size'
                ";
                $proname = $this->db->fetchOne($sql);

				if ($proname === 'pg_database_size') {
                    $db_name = $this->config->system->database->params->database;

					if (strpos($db_name, '.') !== false) {
						[$db_name, ] = explode('.', $db_name);
					}

                    $oid = $this->db->fetchOne("
                        SELECT oid
						FROM pg_database
						WHERE datname = ?
                    ", $db_name);

					$database_size = $this->db->fetchOne("SELECT pg_database_size({$oid}) as size");
				}
				break;

			case 'oci':
                $database_size = $this->db->fetchOne("
                    SELECT SUM(bytes) as dbsize
					FROM user_segments
                ");
				break;
		}

		return ($database_size !== false)
            ? (float)$database_size
            : null;
	}


	/**
	 * Try to strip away additional information
	 *
	 * @param string $version E.g. `5.6.27-0ubuntu0.14.04.1`
	 * @return string `5.6.27`
	 */
	protected function cleanVersion(string $version): string {

		$matches = [];
		preg_match('/^(\d+)(\.\d+)(\.\d+)/', $version, $matches);

        if (isset($matches[0])) {
			return $matches[0];
		}

        return $version;
	}
}
