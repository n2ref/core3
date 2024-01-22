<?php
namespace Core3\Classes;


/**
 *
 */
class Thread extends System {

    protected $php_path     = '';
    protected $host         = '';
    protected $tmp          = '';
    protected $file_execute = '';
    protected $module_name  = '';
    protected $method       = '';
    protected $params       = [];


    /**
     * @param array|null $options
     * @throws \Exception
     */
    public function __construct(array $options = null) {

        if ( ! empty($options['php'])) {
            $php_path = $options['php'];
        } else {
            $php_path = $this->config->php && $this->config->php->path ? $this->config->php->path : '';
        }

        if ( ! empty($options['tmp'])) {
            $tmp = $options['tmp'];
        } else {
            $tmp = $this->config->tmp ? $this->config->tmp : '';
        }

        if ( ! empty($options['host'])) {
            $host = $options['host'];
        } else {
            $host = $this->config->system ? $this->config->system->host : '';
        }

        if ( ! function_exists('exec')) {
            throw new \Exception("function 'exec' not found");
        }

        $this->host         = $host;
        $this->tmp          = $tmp;
        $this->file_execute = realpath(__DIR__ . '/../index.php');

        if ($php_path) {
            $this->php_path = $php_path;
        } else {
            $system_php_path = exec('which php');
            if ( ! empty($system_php_path)) {
                $this->php_path = $system_php_path;
            } else {
                throw new \Exception('php not found');
            }
        }
    }


    /**
     * @param string $module
     */
    public function setModule(string $module): void {

        $this->module_name = $module;
    }


    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void {

        $this->method = $method;
    }


    /**
     * @param array $params
     * @return void
     */
    public function setParams(array $params): void {

        $this->params = $params;
    }


    /**
     * @return string
     */
    public function getCmd(): string {

        $params = '';
        if ( ! empty($this->params)) {
            foreach ($this->params as $param) {
                $params .= " -p " . escapeshellarg($param);
            }
        }

        if ($this->tmp && ($tmp_dir = realpath($this->tmp))) {
            if (is_writeable($tmp_dir)) {
                $out_file = " > {$tmp_dir}/core_" . crc32($this->host) . "_{$this->module_name}_{$this->method}.out";
            }
        }

        return sprintf(
            '%s %s --module %s --method %s --host %s %s %s 2>&1 & echo $!',
            $this->php_path,
            $this->file_execute,
            $this->module_name,
            $this->method,
            $this->host,
            $params,
            $out_file,
        );
    }


    /**
     * @return int|null PID
     */
    public function start():? int {

        if ( ! empty($this->file_execute)) {
            return null;
        }

        $cmd = $this->getCmd();
        exec($cmd, $output);

        return end($output);
    }
}