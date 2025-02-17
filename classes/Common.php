<?php
namespace Core3\Classes;
use Core3\Exceptions\AppException;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Core3\Mod\Admin;
use Core3\Sys\Auth;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Monolog\Handler\MissingExtensionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @property-read Admin\Controller $modAdmin
 * @property-read Worker\Client    $worker
 * @property-read Auth             $auth
 */
class Common extends Db {

    protected string $module   = '';
    protected mixed  $section  = '';
    protected string $resource = '';

    /**
     *
     */
	public function __construct() {

        $child_class_name = preg_match('~^Core3\\\Mod\\\([a-zA-Z0-9\_]+)\\\~', get_class($this), $match)
            ? $match[1]
            : '';

        $this->module   = strtolower($child_class_name);
        $this->section  = Registry::has('section') ? Registry::get('section') : null;
        $this->resource = $this->module && $this->section ? "{$this->module}_{$this->section}" : $this->module;
    }


    /**
     * @param string $param_name
     * @return self|Cache|Log|\Laminas\Db\Adapter\Adapter|AbstractTableGateway|Worker\Client|mixed|null
     * @throws ContainerExceptionInterface
     * @throws DbException
     * @throws ExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function __get(string $param_name) {

        if ($this->hasStaticCache($param_name)) {
            $result = $this->getStaticCache($param_name);

        } else {
            if (strpos($param_name, 'table') === 0) {
                $table_name = substr($param_name, 5);
                $result     = $this->getModuleTable($this->module, $table_name);

            } elseif (strpos($param_name, 'model') === 0) {
                $model_name = strtolower(substr($param_name, 5));
                $result     = $this->getModuleModel($this->module, $model_name);

            } elseif (strpos($param_name, 'mod') === 0) {
                $module_name = strtolower(substr($param_name, 3));
                $result      = $this->getModuleController($module_name);

            } elseif ($param_name === 'worker') {
                $result = new Worker\Client();

            } elseif ($param_name === 'auth') {
                $result = Registry::get('auth');
            }

            if ( ! empty($result)) {
                $this->setStaticCache($param_name, $result);

            } else {
                $result = parent::__get($param_name);
            }
        }

        return $result;
	}


    /**
     * Запуск задачи на воркере
     * @param string $job_name
     * @param array  $arguments
     * @return int|null
     * @throws AppException
     * @throws \Exception
     */
    protected function startWorkerJob(string $job_name, array $arguments):? int {

        if ( ! $this->worker->isStart()) {
            if ( ! $this->worker->start()) {
                throw new AppException($this->_('Не удалось запустить процесс обработки'));
            }
        }

        return $this->worker->startJob($this->module, $job_name, $arguments);
    }


    /**
     * Запуск метода из роутера
     * @param Router $router
     * @param Request $request
     * @return int|null
     * @throws Exception
     * @throws MissingExtensionException
     * @throws \Exception
     */
    protected function runRouterMethod(Router $router, Request $request): mixed {

        $route_method = $router->getRouteMethod($request->getMethod(), $request->getUri());

        if ( ! $route_method) {
            return Response::httpCode(404);
        }

        try {
            return $route_method->run($request);

        } catch (HttpException $e) {
            $this->log->info($e->getMessage());
            return Response::errorJson($e->getCode(), $e->getErrorCode(), $e->getMessage());

        } catch (AppException $e) {
            $this->log->info($e->getMessage());
            return \CoreUI\Info::danger($e->getMessage(), $this->_('Ошибка'));

        } catch (DbException $e) {
            $this->log->error("Database error - {$this->resource}", $e);
            $is_access = $this->config?->system->debug?->on || $this->auth?->isAdmin();

            return Response::errorJson(500, 'error',
                $is_access ? $e->getMessage() : $this->_('Ошибка базы данных. Обновите страницу или попробуйте позже'),
                $is_access ? $e->getTrace() : null
            );

        } catch (\Exception $e) {
            $this->log->error("Fatal error - {$this->resource}", $e);
            $is_access = $this->config?->system->debug?->on || $this->auth?->isAdmin();

            return Response::errorJson(500, 'error',
                $is_access ? $e->getMessage() : $this->_('Ошибка. Обновите страницу или попробуйте позже'),
                $is_access ? $e->getTrace() : null
            );
        }
    }


    /**
     * @param string $src
     * @return void
     */
    protected function addJs(string $src): void {

        $src = trim($src);
        $src = Tools::addSrcHash($src);

        if (Registry::has('js')) {
            $js = Registry::get('js');

            if (is_array($js)) {
                $js[] = $src;

                Registry::set('js', $js);

            } else {
                Registry::set('js', [ $src ]);
            }

        } else {
            Registry::set('js', [ $src ]);
        }
    }


    /**
     * @param string $src
     * @return void
     */
    protected function addCss(string $src): void {

        $src = trim($src);
        $src = Tools::addSrcHash($src);

        if (Registry::has('css')) {
            $css = Registry::get('css');

            if (is_array($css)) {
                $css[] = $src;

                Registry::set('css', $css);

            } else {
                Registry::set('css', [ $src ]);
            }

        } else {
            Registry::set('css', [ $src ]);
        }
    }


    /**
     * @param string $module
     * @param string $src
     * @return void
     * @throws DbException
     */
    protected function addCssModule(string $module, string $src): void {

        $module_folder = $this->getModuleFolder($module);

        $this->addCss("{$module_folder}/{$src}");
    }


    /**
     * @param string $module
     * @param string $src
     * @return void
     * @throws DbException
     * @throws ExceptionInterface
     */
    protected function addJsModule(string $module, string $src): void {

        $module_folder = $this->getModuleFolder($module);

        $this->addJs("{$module_folder}/{$src}");
    }


    /**
     * @param string $module
     * @param string $src
     * @return string
     * @throws DbException
     */
    protected function getJsModule(string $module, string $src): string {

        $module_folder = $this->getModuleFolder($module);

        $src = trim("{$module_folder}/{$src}");
        $src = Tools::addSrcHash($src);

        return "<script type=\"text/javascript\" src=\"{$src}\"></script>";
    }


    /**
     * @param string $module
     * @param string $src
     * @return string
     * @throws DbException
     */
    protected function getCssModule(string $module, string $src): string {

        $module_folder = $this->getModuleFolder($module);

        $src = trim("{$module_folder}/{$src}");
        $src = Tools::addSrcHash($src);

        return "<link href=\"{$src}\" type=\"text/css\" rel=\"stylesheet\"/>";
    }


    /**
     * Вызов обработки события
     * @param string $event_name
     * @param array  $data
     * @return void
     * @throws ExceptionInterface
     */
    public function event(string $event_name, array $data): void {

        $key            = 'modules_events';
        $modules_events = [];

        if ($this->hasStaticCache($key)) {
            $modules_events = $this->getStaticCache($key);
        }

        if (empty($modules_events)) {
            $modules_events   = [];
            $modules_events[] = $this->getModuleController('admin');

            $modules = $this->modAdmin->tableModules->getRowsByActive();

            foreach ($modules as $module) {

                $controller = $this->getModuleController($module->name);

                if (in_array('\\Core3\\Interfaces\\Events', class_implements($controller))) {
                    $modules_events[] = $controller;
                }
            }

            $this->setStaticCache($key, $modules_events);
        }


        foreach ($modules_events as $module) {
            $module->events($this->module, $event_name, $data);
        }
    }


    /**
     * Запрос для получения ответов от модулей
     * @param string        $request_name
     * @param array         $data
     * @param \Closure|null $callback
     * @return mixed
     * @throws ExceptionInterface
     */
    public function ask(string $request_name, array $data = [], \Closure $callback = null): mixed {

        $key            = 'modules_answer';
        $modules_answer = [];

        if ($this->hasStaticCache($key)) {
            $modules_answer = $this->getStaticCache($key);
        }

        if (empty($modules_answer)) {
            $modules_answer = [];

            $modules = $this->modAdmin->tableModules->getRowsByActive();

            foreach ($modules as $module) {
                $controller = $this->getModuleController($module->name);

                if (in_array('\\Core3\\Interfaces\\Answer', class_implements($controller))) {
                    $modules_answer[] = $controller;
                }
            }

            $this->setStaticCache($key, $modules_answer);
        }


        $result = null;

        foreach ($modules_answer as $module) {
            $response = $module->answer($this->module, $request_name, $data);

            if ( ! is_null($response)) {
                if ($callback) {
                    $response_callback = $callback($response);

                    if ( ! is_null($response_callback)) {
                        $result = $response_callback;
                        break;
                    }

                } else {
                    $result = $response;
                    break;
                }
            }
        }

        return $result;
    }


    /**
     * Запуск cli метода
     * @param string     $module_name
     * @param string     $method_name
     * @param string[]   $params
     * @param array|null $options
     * @return bool
     * @throws \Exception
     * @throws ExceptionInterface
     */
    protected function startCli(string $module_name, string $method_name, array $params = [], array $options = null): bool {

        $cli = new Cli();
        return $cli->startCliMethod($module_name, $method_name, $params);
    }


    /**
     * @param string $module_name
     * @return mixed
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Exception
     */
    protected function getModuleController(string $module_name): mixed {

        $key_name = "mod_controller_{$module_name}";

        if ($this->hasStaticCache($key_name)) {
            $result = $this->getStaticCache($key_name);

        } else {
            $location = $this->getModuleLocation($module_name);

            if ( ! $location) {
                throw new Exception($this->_("Модуль \"%s\" не найден", [$module_name]));
            }


            if ($module_name === 'admin') {
                require_once "{$location}/Controller.php";
                $result = new \Core3\Mod\Admin\Controller();

            } else {
                if ( ! $this->isModuleActive($module_name)) {
                    throw new Exception($this->_("Модуль \"%s\" не активен", [$module_name]));
                }

                $controller_file = "{$location}/Controller.php";

                if ( ! file_exists($controller_file)) {
                    throw new Exception($this->_("Модуль \"%s\" сломан. Не найден файл контроллера.", [$module_name]));
                }

                $this->loadVendorDir($location);

                require_once $controller_file;

                $module_class_name = "\\Core3\\Mod\\" . ucfirst($module_name) . "\\Controller";

                if ( ! class_exists($module_class_name)) {
                    throw new Exception($this->_("Модуль \"%s\" сломан. Не найден класс контроллера.", [$module_name]));
                }

                $result = new $module_class_name();
            }

            $this->setStaticCache($key_name, $result);
        }

        return $result;
    }


    /**
     * @param string $module_name
     * @param string $table_name
     * @return AbstractTableGateway
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getModuleTable(string $module_name, string $table_name): AbstractTableGateway {

        $key_name = "mod_table_{$module_name}_{$table_name}";

        if ($this->hasStaticCache($key_name)) {
            $result = $this->getStaticCache($key_name);

        } else {
            $module_name = strtolower($module_name);
            $table_name  = ucfirst($table_name);

            $location = $this->getModuleLocation($module_name);

            if ( ! $location) {
                throw new Exception($this->_('Модуль "%s" не найден', [$module_name]));
            }

            if ( ! $this->isModuleActive($module_name)) {
                throw new Exception($this->_('Модуль "%s" не активен', [$module_name]));
            }

            $table_class = '\\Core3\\Mod\\' . ucfirst($module_name). '\\Tables\\' . $table_name;
            $table_file  = "{$location}/Tables/{$table_name}.php";

            if ( ! file_exists($table_file)) {
                throw new Exception($this->_('Не найден файл таблицы: %s', [$table_file]));
            }

            $this->loadVendorDir($location);

            require_once $table_file;

            if ( ! class_exists($table_class)) {
                throw new Exception($this->_('Не найден класс таблицы %s', [$table_class]));
            }


            if ( ! $this->issetConnection()) {
                $this->initConnection();
            }

            $result = new $table_class();

            if ( ! $result instanceof AbstractTableGateway) {
                throw new Exception($this->_('Некорректный класс таблицы %s', [$table_class]));
            }

            $this->setStaticCache($key_name, $result);
        }

        return $result;
    }


    /**
     * @param string $module_name
     * @param string $model_name
     * @return mixed
     * @throws \Laminas\Cache\Exception\ExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getModuleModel(string $module_name, string $model_name): mixed {

        $key_name = "mod_model_{$module_name}_{$model_name}";

        if ($this->hasStaticCache($key_name)) {
            $result = $this->getStaticCache($key_name);

        } else {
            $module_name = strtolower($module_name);
            $model_name  = ucfirst($model_name);

            $location = $this->getModuleLocation($module_name);

            if ( ! $location) {
                throw new Exception($this->_('Модуль "%s" не найден', [$module_name]));
            }

            if ( ! $this->isModuleActive($module_name)) {
                throw new Exception($this->_('Модуль "%s" не активен', [$module_name]));
            }

            $model_class = '\\Core3\\Mod\\' . ucfirst($module_name). '\\Models\\' . $model_name;
            $model_file  = "{$location}/Models/{$model_name}.php";

            if ( ! file_exists($model_file)) {
                throw new Exception($this->_('Не найден файл модели: %s', [$model_file]));
            }

            $this->loadVendorDir($location);

            require_once $model_file;

            if ( ! class_exists($model_class)) {
                throw new Exception($this->_('Не найден класс модели %s', [$model_class]));
            }


            if ( ! $this->issetConnection()) {
                $this->initConnection();
            }

            $result = new $model_class();

            $this->setStaticCache($key_name, $result);
        }

        return $result;
    }


    /**
     * @param string $location
     * @return void
     */
    private function loadVendorDir(string $location): void {

        $autoload_file = "{$location}/vendor/autoload.php";

        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }
    }
}