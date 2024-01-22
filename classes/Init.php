<?php
namespace Core3\Classes;
use Core3\Classes\Http\Request;
use Core3\Classes\Http\Response;
use Core3\Exceptions\AppException;
use Core3\Exceptions\HttpException;
use Core3\Exceptions\Exception;
use Core3\Mod\Admin;
use Laminas\Cache\Exception\ExceptionInterface;


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
     * @return string
     * @throws Exception
     * @throws ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function dispatch(): string {

        if (PHP_SAPI === 'cli') {
            return $this->dispatchCli();
        }

        return $this->dispatchHttp();
    }


    /**
     * @return bool
     * @throws HttpException
     * @throws \Exception
     */
    public function auth(): bool {

        if (PHP_SAPI === 'cli') {
            return false;
        }

        $this->auth = (new Http())->getAuth();

        if ($this->auth) {
            Registry::set('auth', $this->auth);
            return true;
        }

        return false;
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
     * @throws \ReflectionException
     * @throws \Exception
     * @throws ExceptionInterface
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

            } catch (Exception $e) {
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

            $params = $options['param'] ?? (isset($options['p']) ? $options['p'] : false);
            $params = $params === false ? [] : (is_array($params) ? $params : array($params));

            $result = $cli->runCliMethod($module, $method, $params);

        // Start daemon worker manager
        } elseif (isset($options['worker-start'])) {
            $is_daemonize = isset($options['d']);
            $cli->startWorkerManager($is_daemonize);

        // Start daemon worker manager
        } elseif (isset($options['worker-stop'])) {
            $params = $options['param'] ?? (isset($options['p']) ? $options['p'] : false);
            $params = $params === false ? [] : (is_array($params) ? $params : array($params));

            $force  = isset($params[0]) && (bool)$params[0];
            $result = $cli->stopWorkerManager($force)
                ? "Worker stopped"
                : "Failed to stop the process";

        // Restart daemon worker manager
        } elseif (isset($options['worker-restart'])) {
            $params = $options['param'] ?? (isset($options['p']) ? $options['p'] : false);
            $params = $params === false ? [] : (is_array($params) ? $params : array($params));

            $force  = isset($params[0]) && (bool)$params[0];
            $result = $cli->restartWorkerManager($force)
                ? "Worker restarted"
                : "Failed restarted the process";
        }

        return $result . PHP_EOL;
    }


    /**
     * @return string
     * @throws \Core3\Exceptions\Exception
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    private function dispatchHttp(): string {

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
                ob_start();
                $result = (new Http())->dispatch();
                $buffer = ob_get_clean();
            }

            if (is_array($result)) {
                if (isset($buffer) && is_string($buffer) && $buffer != '' && ! array_key_exists('_buffer', $result)) {
                    $result['_buffer'] = $buffer;
                }

                $response = new Response();
                $response->setContentTypeJson();
                $response->setContentJson($result);

            } elseif (is_scalar($result)) {
                if (isset($buffer) && is_string($buffer) && $buffer != '') {
                    $result = $buffer . $result;
                }

                $response = new Response();
                $response->setContentTypeHtml();
                $response->setContent($result);

            } elseif ($result instanceof Response) {
                if (isset($buffer) && is_string($buffer) && $buffer != '') {
                    $result->appendContent($buffer);
                }

                $response = $result;

            } else {
                $response = new Response();
                $response->setHeader('Content-Type', 'text/plain');
            }

            if ( ! is_array($result)) {
                if (Registry::has('js')) {
                    foreach (Registry::get('js') as $src) {
                        $response->appendContent("<script type=\"text/javascript\" src=\"{$src}\"></script>'");
                    }
                }

                if (Registry::has('css')) {
                    foreach (Registry::get('css') as $src) {
                        $response->appendContent("<link href=\"{$src}\" type=\"text/css\" rel=\"stylesheet\"/>");
                    }
                }
            }


        } catch (HttpException $e) {
            $response = Response::errorJson($e->getMessage(), $e->getErrorCode(), $e->getCode());

        } catch (AppException $e) {
            $response = Response::errorJson($e->getMessage(), $e->getCode(), 400);

        } catch (\Exception $e) {
            $response = Response::errorJson($e->getMessage(), $e->getCode(), 500);
        }

        $this->logResponse($response);

        $response->printHeaders();
        return $response->getContent();
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
                    'ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
                    'sid'    => $this->auth->getSessionId(),
                    'uid'    => $this->auth->getUserId(),
                    'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                    'uri'    => $_SERVER['REQUEST_URI'] ?? '',
                    'query'  => $_SERVER['QUERY_STRING'] ?? '',
                ]);
        }
    }


    /**
     * @param Response $response
     * @return void
     * @throws \Exception
     */
    private function logResponse(Response $response): void {

        if ($this->config?->system?->log?->on &&
            $this->config?->system?->log?->output_file
        ) {
            $this->log
                ->file($this->config?->system?->log?->output_file)
                ->info($response->getContent());
        }
    }
}