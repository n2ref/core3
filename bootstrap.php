<?php
namespace Core3;
use Core3\Classes\Registry;

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', true);

define('DOC_ROOT', realpath(__DIR__ . '/..'));
define("DOC_PATH", substr(DOC_ROOT, strlen($_SERVER['DOCUMENT_ROOT'])) ? : '/');


$conf_file = DOC_ROOT . "/conf.ini";
if ( ! file_exists($conf_file)) {
    throw new \Exception("Missing configuration file '{$conf_file}'.");
}


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

$vendor_autoload_file = "vendor/autoload.php";

if ( ! file_exists($vendor_autoload_file)) {
    throw new \Exception("No external libraries. You need to execute in the console: php " . DOC_ROOT . "/index.php --composer -p update");
}

require_once $vendor_autoload_file;
require_once 'autoload.php';


// Конфиг приложения
$config_inline = [
    'system' => [
        'name'     => 'CORE3',
        'https'    => false,
        'cache'    => [
            'dir'     => realpath(__DIR__ . '/../../') . '/cache',
            'options' => [],
        ],
        'debug'    => ['on' => false,],
        'database' => [
            'adapter' => 'Pdo_Mysql',
            'params'  => [
                'charset'          => 'utf8',
                'adapterNamespace' => '\\Core3\\Classes\\Db_Adapter'
            ],
        ],
        'temp' => sys_get_temp_dir() ?: "/tmp",
    ],
];

$core_conf_file = __DIR__ . "/../conf.ini";

$config = new Classes\Config();
$config->addArray($config_inline);
$config->addFileIni($core_conf_file, $_SERVER['SERVER_NAME'] ?? 'production');
$config->addFileIni($conf_file,      $_SERVER['SERVER_NAME'] ?? 'production');
$config->setReadOnly();


// отладка приложения
if ($config->system->debug->on) {
    error_reporting(E_ALL);
    ini_set('display_errors', true);
} else {
    ini_set('display_errors', false);
}


Registry::set('config',    $config);
Registry::set('translate', new Classes\Translate($config));
