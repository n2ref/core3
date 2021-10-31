<?php
namespace Core3;

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', true);

define('DOC_ROOT', realpath(__DIR__ . '/..'));
define("DOC_PATH", substr(DOC_ROOT, strlen($_SERVER['DOCUMENT_ROOT'])) ? : '/');


$conf_file = DOC_ROOT . "/conf.ini";
if ( ! file_exists($conf_file)) {
    throw new \Exception("Missing configuration file '{$conf_file}'.");
}


require_once 'classes/Error.php';
require_once 'classes/Init.php';



if (PHP_SAPI === 'cli') {
    //определяем имя секции для cli режима
    $options = getopt('m:a:p:s:ndcvh', [
        'module:',
        'action:',
        'param:',
        'scan-cli-actions',
        'info-installed-modules',
        'composer',
        'section:',
        'version',
        'help',
    ]);
    if (( ! empty($options['section']) && is_string($options['section'])) ||
        ( ! empty($options['s']) && is_string($options['s']))
    ) {
        $_SERVER['SERVER_NAME'] = ! empty($options['section']) ? $options['section'] : $options['s'];
    }

    // если выполняется действие с кампоузером, то дальше исполнять код не нужно
    if (isset($options['c']) || isset($options['composer'])) {
        return '';
    }
}

$autoload_file = DOC_ROOT . "/core3/vendor/autoload.php";
if ( ! file_exists($autoload_file)) {
    throw new \Exception("No external libraries. You need to execute in the console: php " . DOC_ROOT . "/index.php --composer -p update");
}

require_once $autoload_file;


$config_inline = array(
    'system'     => [
        'name'   => 'CORE',
        'https'  => false,
        'theme'  => 'material',
        'cache'  => [
            'dir'     => 'cache',
            'options' => [],
        ],
        'debug'  => [
            'on'      => false,
            'firephp' => false
        ],
        'database'     => [
            'adapter'                    => 'Pdo_Mysql',
            'params'                     => [
                'charset'          => 'utf8',
                'adapterNamespace' => '\\Core\\Db_Adapter'
            ],
            'isDefaultTableAdapter'      => true,
            'profiler'                   => [
                'enabled' => false,
                'class'   => 'Zend_Db_Profiler_Firebug'
            ],
            'caseFolding'                => true,
            'autoQuoteIdentifiers'       => true,
            'allowSerialization'         => true,
            'autoReconnectOnUnserialize' => true
        ],
        'include_path' => '',
        'temp'         => sys_get_temp_dir() ?: "/tmp"
    ]
);


$config = new \Zend_Config($config_inline, true);

if ( ! empty($_SERVER['SERVER_NAME'])) {
    $config_ini = new \Zend_Config_Ini($conf_file, $_SERVER['SERVER_NAME']);
} else {
    $config_ini = new \Zend_Config_Ini($conf_file, 'production');
}
$config->merge($config_ini);


// отладка приложения
if ($config->system->debug->on) {
    ini_set('display_errors', true);
} else {
    ini_set('display_errors', false);
}

// устанавливаем шкурку
define('THEME', $config->system->theme);

// определяем путь к папке кеша
if (strpos($config->system->cache->dir, '/') !== 0) {
    $config->system->cache->dir = DOC_ROOT . trim($config->system->cache->dir, "/");
}


//конфиг стал только для чтения
$config->setReadOnly();

if (isset($config->system->include_path) && $config->system->include_path) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $config->system->include_path);
}


require_once "classes/Db_Adapter_{$config->system->database->adapter}.php";
require_once 'classes/Tools.php';
require_once 'classes/Translate.php';


$translate = new Translate($config);
\Zend_Registry::set('translate', $translate);


if (isset($config->system->auth) && $config->system->auth->on) {
    $realm = $config->system->auth->params->realm;
    $users = $config->system->auth->params->users;
    if ($code = Tools::httpAuth($realm, $users)) {
        if ($code == 1) throw new \Exception($translate->tr("Неверный пользователь."));
        if ($code == 2) throw new \Exception($translate->tr("Неверный пароль."));
    }
}

//MPDF PATH
define("_MPDF_TEMP_PATH", rtrim($config->system->cache->dir, "/") . '/');
define("_MPDF_TTFONTDATAPATH", rtrim($config->system->cache->dir, "/") . '/');

//сохраняем параметры сессии
if ($config->system->session) {
    \Zend_Session::setOptions($config->system->session->toArray());
}

//сохраняем конфиг
\Zend_Registry::set('config', $config);

//обрабатываем конфиг ядра
$core_conf_file = __DIR__ . "/../../conf.ini";
if (file_exists($core_conf_file)) {
    $core_config = new \Zend_Config_Ini($core_conf_file, 'production');
    \Zend_Registry::set('core_config', $core_config);
}