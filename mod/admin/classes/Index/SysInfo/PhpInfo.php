<?php
namespace Core3\Mod\Admin\Index\SysInfo;


class PhpInfo {

    /**
     * @return array
     */
    public function getPhpStatistics(): array {

        return [
            'version'             => phpversion(),
            'memory_limit'        => self::convertSizeToBytes(ini_get('memory_limit')),
            'max_execution_time'  => self::convertSizeToBytes(ini_get('max_execution_time')),
            'upload_max_filesize' => $this->getUploadMaxFileSize(),
            'extensions'          => $this->getLoadedPhpExtensions(),
        ];
    }


    /**
     * Получение php info
     * @return string
     */
    function getPhpinfo(): string {

        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
        $phpinfo = "
        <style>
            #phpinfo {}
            #phpinfo pre {margin: 0; font-family: monospace;}
            #phpinfo a:link {color: #009; text-decoration: none; background-color: #fff;}
            #phpinfo a:hover {text-decoration: underline;}
            #phpinfo table {border-collapse: collapse; border: 0; width: 934px; max-width: 100%;  box-shadow: 1px 2px 3px #ccc;}
            #phpinfo .center {text-align: center;}
            #phpinfo .center table {margin: 1em auto; text-align: left;}
            #phpinfo .center th {text-align: center !important;}
            #phpinfo td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px; word-break: break-word;}
            #phpinfo h1 {font-size: 150%;}
            #phpinfo h2 {font-size: 125%;}
            #phpinfo .p {text-align: left;}
            #phpinfo .e {background-color: #ccf; width: 35%; font-weight: bold;}
            #phpinfo .h {background-color: #99c; font-weight: bold;}
            #phpinfo .v {background-color: #ddd; max-width: 35%; overflow-x: auto; word-wrap: break-word;}
            #phpinfo .v i {color: #999;}
            #phpinfo img {float: right; border: 0;}
            #phpinfo hr {width: 934px; max-width: 100%; background-color: #ccc; border: 0; height: 1px;}
        </style>
        <div id=\"phpinfo\">
            {$phpinfo}
        </div>
        ";

        return $phpinfo;
    }


    /**
     * Получение максимально возможного размера файла,
     * который можно загрузить на сервер. Размер в байтах.
     * @return int
     */
    public function getUploadMaxFileSize(): int {

        $ini = $this->convertSizeToBytes(trim(ini_get('post_max_size')));
        $max = $this->convertSizeToBytes(trim(ini_get('upload_max_filesize')));
        $min = max($ini, $max);

        if ($ini > 0) {
            $min = min($min, $ini);
        }

        if ($max > 0) {
            $min = min($min, $max);
        }

        return $min >= 0 ? $min : 0;
    }


    /**
     * Конвертирует размер из ini формата в байты
     * @param  string $size
     * @return int
     */
    private static function convertSizeToBytes(string $size): int {

        if ( ! is_numeric($size)) {
            $type = strtoupper(substr($size, -1));
            $size = (int)substr($size, 0, -1);

            switch ($type) {
                case 'K' : $size *= 1024; break;
                case 'M' : $size *= 1024 * 1024; break;
                case 'G' : $size *= 1024 * 1024 * 1024; break;
                default : break;
            }
        }

        return (int)$size;
    }


    /**
     * Get all loaded php extensions
     * @return array of strings with the names of the loaded extensions
     */
    private function getLoadedPhpExtensions(): ?array {
        return (function_exists('get_loaded_extensions') ? get_loaded_extensions() : null);
    }
}