<?php
namespace Core3;
use Core3\Classes\Registry;
use Core3\Classes\Config;
use Core3\Classes\Translate;

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
    $options = getopt('m:e:p:l:nctavh', [
        'module:',
        'method:',
        'param:',
        'cli-methods',
        'modules',
        'composer',
        'host',
        'openapi',
        'version',
        'help',
    ]);
    if (( ! empty($options['host']) && is_string($options['host'])) ||
        ( ! empty($options['t']) && is_string($options['t']))
    ) {
        $_SERVER['SERVER_NAME'] = ! empty($options['host']) ? $options['host'] : $options['t'];
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



$core_conf_file = __DIR__ . "/conf.ini";

$config = new Config();
$config->addFileIni($core_conf_file, 'production');
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
Registry::set('translate', new Translate($config));
