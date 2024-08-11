<?php
namespace core3\classes\Abstract;
use Core3\Classes\Common;

/**
 *
 */
abstract class Worker extends Common {

    /**
     * @var resource|null
     */
    private $stream = null;


    /**
     * @param $stream
     */
    public function __construct($stream) {
        parent::__construct();

        if (is_resource($stream)) {
            $this->stream = $stream;
        }
    }


    /**
     * @param string $state_text
     * @return bool
     * @throws \Exception
     */
    protected function setJobState(string $state_text): bool {

        if ( ! is_resource($this->stream)) {
            return false;
        }

        fwrite($this->stream, json_encode([
            'command' => 'set_state',
            'job_pid' => getmypid(),
            'state'   => $state_text,
        ]));

        return true;
    }
}