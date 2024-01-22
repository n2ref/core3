<?php
namespace Core3;
use Core3\Classes\Registry;
use Core3\Classes\Config;
use Core3\Classes\Tools;
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

$config->system->cache->dir = $config->system?->cache?->dir
    ? Tools::getAbsolutePath($config->system->cache->dir)
    : null;

$config->system->tmp = $config->system?->tmp
    ? Tools::getAbsolutePath($config->system->tmp)
    : sys_get_temp_dir();

if ($config->system?->log?->dir) {
    $config->system->log->dir = Tools::getAbsolutePath($config->system->log->dir);
}

if ($config->system?->log?->file) {
    $config->system->log->file = str_starts_with($config->system->log->file, '/')
        ? $config->system->log->file
        : "{$config->system->log->dir}/{$config->system->log->file}";
}

if ($config->system?->log?->access_file) {
    $config->system->log->access_file = str_starts_with($config->system->log->access_file, '/')
        ? $config->system->log->access_file
        : "{$config->system->log->dir}/{$config->system->log->access_file}";
}

if ($config->system?->log?->profile?->file) {
    $config->system->log->profile->file = str_starts_with($config->system->log->profile->file, '/')
        ? $config->system->log->profile->file
        : "{$config->system->log->dir}/{$config->system->log->profile->file}";
}

if ($config->system?->worker?->log_file) {
    $config->system->worker->log_file = str_starts_with($config->system->worker->log_file, '/')
        ? $config->system->worker->log_file
        : "{$config->system->log->dir}/{$config->system->worker->log_file}";
} else {
    $config->system->worker->log_file = $config->system?->log?->file;
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
