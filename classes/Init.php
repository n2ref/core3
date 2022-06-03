<?php
namespace Core3\Classes;
use Core3\Exceptions\HttpException;
use Core3\Mod\Admin;


/**
 * @property Admin\Controller $modAdmin
 */
class Init extends Db {

    /**
     * @var Auth|null
     */
    private Auth|null $auth = null;


    /**
     *
     */
    public function __construct() {

        if (PHP_SAPI != 'cli') {
            if (empty($_SERVER['HTTPS']) && $this->config?->system?->https) {
                header('Location: https://' . $_SERVER['SERVER_NAME']);
            }
        }

        $tz = $this->config?->system?->timezone;

        if ( ! empty($tz)) {
            date_default_timezone_set($tz);
        }
    }


    /**
     * @return bool
     * @throws HttpException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function auth(): bool {

        if (PHP_SAPI === 'cli') {
            return false;
        }

        $this->auth = (new Rest())->getAuth();

        if ($this->auth) {
            Registry::set('auth', $this->auth);
            return true;
        }

        return false;
    }


    /**
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function dispatch(): string {

        if (PHP_SAPI === 'cli') {
            return $this->dispatchCli();
        }


        try {
            if ($this->auth) {
                $this->logRequest();

                (new Acl())->setupAcl();
            }


            // Disable
            if ($this->config?->system?->disable?->on && ! $this->auth?->isAdmin()) {
                $result = [
                    'core_type'   => 'disable_page',
                    'title'       => $this->config?->system?->disable?->title ?? $this->_('Система на профилактике'),
                    'description' => $this->config?->system?->disable?->description ?? '',
                ];

            } else {
                $result = (new Rest())->dispatch();
            }

            $output = HttpResponse::dataJson($result);


        } catch (HttpException $e) {
            $output = HttpResponse::errorJson($e->getMessage(), $e->getErrorCode(), $e->getCode());

        } catch (\Exception $e) {
            $output = HttpResponse::errorJson($e->getMessage(), $e->getCode(), 500);
        }


        $this->logOutput($output);

        return $output;
    }


    /**
     * @throws \Exception
     */
    public function __destruct() {

        if ($this->config?->system?->log?->profile?->on &&
            $this->config?->system?->log?->profile?->file
        ) {
            $sql_queries = $this->db->fetchAll("show profiles");
            $total_time  = 0;
            $max_slow    = [];

            if ( ! empty($sql_queries)) {
                foreach ($sql_queries as $sql_query) {

                    if ( ! empty($sql_query['Duration'])) {
                        $total_time += $sql_query['Duration'];

                        if (empty($max_slow['Duration']) || $max_slow['Duration'] < $sql_query['Duration']) {
                            $max_slow = $sql_query;
                        }
                    }
                }
            }

            $request_method = ! empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'none';
            $query_string   = ! empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

            if ($total_time >= 1 || count($sql_queries) >= 100 || count($sql_queries) == 0) {
                $function_log = 'warning';
            } else {
                $function_log = 'info';
            }


            $profile_file = $this->config->system->log->profile->file;

            $this->log->file($profile_file)->{$function_log}('request', [$request_method, round($total_time, 5), count($sql_queries), $query_string]);
            $this->log->file($profile_file)->{$function_log}('  | max slow', $max_slow);
            $this->log->file($profile_file)->{$function_log}('  | queries ', $sql_queries);
        }
    }


    /**
     * Cli
     * @return string
     * @throws \Exception
     */
    private function dispatchCli(): string {

        $result  = '';
        $cli     = new Cli();
        $options = $cli->getOptions();

        // Help info
        if (empty($options) || isset($options['h']) || isset($options['help'])) {
            $result = $cli->getHelp();

        // Version info
        } elseif (isset($options['v']) || isset($options['version'])) {
            $result = $this->config?->system?->version ?? '--';

        // Getting information about installed modules
        } elseif (isset($options['n']) || isset($options['modules'])) {
            $result = $cli->getInstalledModules();

        // Control composer
        } elseif (isset($options['c']) || isset($options['composer'])) {
            try {
                $params = $options['param'] ?? (isset($options['p']) ? $options['p'] : false);
                $params = $params === false ? [] : (is_array($params) ? $params : array($params));
                $cli->cliComposer($params);

            } catch (\Exception $e) {
                $result = $e->getMessage() . PHP_EOL;
            }

        // Getting information about available system methods
        } elseif (isset($options['l']) || isset($options['cli-methods'])) {
            $result = $cli->getCliMethods();

        // Getting information about available system methods
        } elseif (isset($options['a']) || isset($options['openapi'])) {
            $result = $cli->getGenerateOpenapi();

        // Module run method
        } elseif ((isset($options['m']) || isset($options['module'])) &&
                  (isset($options['e']) || isset($options['method']))
        ) {
            $module = $options['module'] ?? $options['m'];
            $method = $options['method'] ?? $options['e'];
            $result = $cli->runCliMethod($module, $method);
        }

        return $result . PHP_EOL;
    }


    /**
     * Логирование активности пользователей
     * @throws \Exception|\Psr\Container\ContainerExceptionInterface
     */
    private function logRequest(): void {

        if ($this->config?->system?->log?->on &&
            $this->config?->system?->log?->access_file
        ) {
            $this->log->file($this->config->system->log->access_file)
                ->info($this->auth->getUserLogin(), [
                    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
                    'sid'     => $this->auth->getSessionId(),
                    'method'  => $_SERVER['REQUEST_METHOD'] ?? '',
                    'port'    => $_SERVER['REMOTE_PORT'] ?? '',
                    'query'   => $_SERVER['QUERY_STRING'] ?? '',
                    'user_id' => $this->auth->getUserId()
                ]);
        }
    }


    /**
     * @param string $output
     * @return void
     * @throws \Exception
     */
    private function logOutput(string $output): void {

        if ($this->config?->system?->log?->on &&
            $this->config?->system?->log?->output_file
        ) {
            $this->log->file($this->config?->system?->log?->output_file)->info($output);
        }
    }
}