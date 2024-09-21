<?php
namespace Core3;
use Core3\Classes\Registry;
use Core3\Classes\Config;
use Core3\Classes\Tools;
use Core3\Classes\Translate;

header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', true);

define('DOC_ROOT',    realpath(__DIR__ . '/..'));
define("DOC_PATH",    substr(DOC_ROOT, strlen($_SERVER['DOCUMENT_ROOT'])) ? : '/');
define("CORE_FOLDER", basename(__DIR__));


$conf_file = DOC_ROOT . "/conf.ini";
if ( ! file_exists($conf_file)) {
    throw new \Exception("Missing configuration file '{$conf_file}'.");
}


if (PHP_SAPI === 'cli') {
    //определяем имя секции для cli режима
    $options = getopt('m:e:p:l:nct:avhd', [
        'module:',
        'method:',
        'param:',
        'cli-methods',
        'worker-start',
        'worker-stop',
        'modules',
        'composer',
        'host',
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

require_once 'autoload.php';

$vendor_autoload_file = __DIR__ . "/vendor/autoload.php";

if ( ! file_exists($vendor_autoload_file)) {
    throw new \Exception("No external libraries. You need to execute in the console: php " . DOC_ROOT . "/index.php --composer -p update");
}

require_once $vendor_autoload_file;


$config = new Config();
$config->addFileIni(__DIR__ . "/conf.ini", 'production');
$config->addFileIni($conf_file,            $_SERVER['SERVER_NAME'] ?? 'production');

$config->system->cache->dir = $config->system?->cache?->dir
    ? Tools::getAbsolutePath($config->system->cache->dir)
    : null;

$config->system->tmp = $config->system?->tmp
    ? Tools::getAbsolutePath($config->system->tmp)
    : sys_get_temp_dir();

if ($config->system?->log?->dir) {
    $config->system->log->dir = Tools::getAbsolutePath($config->system->log->dir);
}

$config->setReadOnly();


// отладка приложения
if ($config?->system?->debug?->on) {
    error_reporting(E_ALL);
    ini_set('display_errors', true);
} else {
    ini_set('display_errors', false);
}


Registry::set('config',    $config);
Registry::set('translate', new Translate($config));
